<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;
use app\src\Exception\Exception;
use Cascade\Cascade;
use PDOException as ORMException;

/**
 * tinyCampaign NodeQ Functions
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();

function set_queued_message_is_sent($node, $id)
{
    $now = Jenssegers\Date\Date::now();
    try {
        $queue = Node::table("$node")->find($id);
        $queue->timestamp_sent = (string) $now;
        $queue->is_sent = (string) 'true';
        $queue->save();
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (InvalidArgumentException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function process_queued_campaign()
{
    $app = \Liten\Liten::getInstance();

    try {
        Node::dispense('queued_campaign');
        $count = Node::table('queued_campaign')->where('complete', '=', 0)->findAll();
        $node = Node::table('queued_campaign')->where('complete', '=', 0)->find();

        if ($count->count() == 0) {
            Node::table('queued_campaign')->delete();
        }

        if ($count->count() > 0) {
            try {
                $campaign = $app->db->campaign_list()
                    ->select('campaign_list.cid, campaign_list.lid')
                    ->where('campaign_list.cid = ?', $node->mid)
                    ->find();
                /**
                 * Instantiate the message queue.
                 */
                $queue = new app\src\tc_Queue();
                $queue->node = $node->node;
                $send_date = explode(' ', $node->sendstart);
                $throttle = _h(get_option('mail_throttle'));
                foreach ($campaign as $cpgn) {
                    $subscriber = $app->db->subscriber()
                        ->select('DISTINCT subscriber.id,subscriber.fname,subscriber.lname,subscriber.email')
                        ->_join('subscriber_list', 'subscriber.id = subscriber_list.sid')
                        ->where('subscriber_list.lid = ?', $cpgn->lid)->_and_()
                        ->where('subscriber.allowed = "true"')->_and_()
                        ->where('subscriber_list.confirmed = "1"')->_and_()
                        ->where('subscriber_list.unsubscribe = "0"')
                        ->groupBy('subscriber.email')
                        ->find();
                    $numItems = count($subscriber);
                    $i = 0;
                    foreach ($subscriber as $sub) {
                        $time = date('H:i:s', time());
                        /**
                         * Create new tc_QueueMessage object.
                         */
                        $new_message = new app\src\tc_QueueMessage();
                        $new_message->setListId($cpgn->lid);
                        $new_message->setMessageId($cpgn->cid);
                        $new_message->setSubscriberId($sub->id);
                        $new_message->setToEmail($sub->email);
                        $new_message->setToName($sub->fname . ' ' . $sub->lname);
                        $new_message->setTimestampCreated(\Jenssegers\Date\Date::now());
                        $new_message->setTimestampToSend(new \Jenssegers\Date\Date("$send_date[0] $time + $throttle seconds"));
                        /**
                         * Add message to the queue.
                         */
                        $queue->addMessage($new_message);

                        if (++$i === $numItems) {
                            $upd = Node::table('queued_campaign')->find(_h($node->id));
                            $upd->complete = (int) 1;
                            $upd->save();
                        }

                        sleep($throttle);
                    }
                }
            } catch (NotFoundException $e) {
                Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
            } catch (Exception $e) {
                Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
            } catch (ORMException $e) {
                Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
            }
        }
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (InvalidArgumentException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function send_confirm_email()
{
    $domain = get_domain_name();
    $site = _h(get_option('system_name'));

    try {
        Node::dispense('confirm_email');

        $queue = Node::table('confirm_email')->where('sent', '=', 0)->findAll();

        if ($queue->count() == 0) {
            Node::table('confirm_email')->delete();
        }

        if ($queue->count() > 0) {
            foreach ($queue as $q) {
                $list = get_list_by('code', $q->lcode);
                $sub = get_subscriber_by('id', $q->sid);

                $message = _escape($list->confirm_email);
                $message = str_replace('{list_name}', _h($list->name), $message);
                $message = str_replace('{confirm_url}', confirm_subscription_button($q), $message);
                $message = str_replace('{system_name}', $site, $message);
                $headers = "From: $site <auto-reply@$domain>\r\n";
                if (_h(get_option('tc_smtp_status')) == 0) {
                    $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE;
                    $headers .= "MIME-Version: 1.0" . "\r\n";
                }
                try {
                    _tc_email()->tc_mail($sub->email, _t('Confirm Subscription for') . ' ' . _h($list->name), $message, $headers);
                } catch (phpmailerException $e) {
                    Cascade::getLogger('error')->error(sprintf('PHPMailer[%s]: %s', $e->getCode(), $e->getMessage()));
                }
                $upd = Node::table('confirm_email')->find(_h($q->id));
                $upd->sent = 1;
                $upd->save();
            }
        }
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (InvalidArgumentException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function send_subscribe_email()
{
    $domain = get_domain_name();
    $site = _h(get_option('system_name'));

    try {
        Node::dispense('subscribe_email');

        $queue = Node::table('subscribe_email')->where('sent', '=', 0)->findAll();

        if ($queue->count() == 0) {
            Node::table('subscribe_email')->delete();
        }

        if ($queue->count() > 0) {
            foreach ($queue as $q) {
                $list = get_list_by('code', $q->lcode);
                $sub = get_subscriber_by('id', $q->sid);

                $message = _escape($list->subscribe_email);
                $message = str_replace('{list_name}', _h($list->name), $message);
                $message = str_replace('{personal_preferences}', update_preferences_button($sub), $message);
                $message = str_replace('{system_name}', $site, $message);
                $headers = "From: $site <auto-reply@$domain>\r\n";
                if (_h(get_option('tc_smtp_status')) == 0) {
                    $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE;
                    $headers .= "MIME-Version: 1.0" . "\r\n";
                }
                try {
                    _tc_email()->tc_mail($sub->email, _t('Welcome to') . ' ' . _h($list->name), $message, $headers);
                } catch (phpmailerException $e) {
                    Cascade::getLogger('error')->error(sprintf('PHPMailer[%s]: %s', $e->getCode(), $e->getMessage()));
                }
                $upd = Node::table('subscribe_email')->find(_h($q->id));
                $upd->sent = 1;
                $upd->save();
            }
        }
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (InvalidArgumentException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function send_unsubscribe_email()
{
    $domain = get_domain_name();
    $site = _h(get_option('system_name'));

    try {
        Node::dispense('unsubscribe_email');

        $queue = Node::table('unsubscribe_email')->where('sent', '=', 0)->findAll();

        if ($queue->count() == 0) {
            Node::table('unsubscribe_email')->delete();
        }

        if ($queue->count() > 0) {
            foreach ($queue as $q) {
                $list = get_list_by('code', $q->lcode);
                $sub = get_subscriber_by('id', $q->sid);

                $message = _escape($list->unsubscribe_email);
                $message = str_replace('{list_name}', _h(get_option('system_name')), $message);
                $message = str_replace('{personal_preferences}', update_preferences_button($sub), $message);
                $headers = "From: $site <auto-reply@$domain>\r\n";
                if (_h(get_option('tc_smtp_status')) == 0) {
                    $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE;
                    $headers .= "MIME-Version: 1.0" . "\r\n";
                }
                try {
                    _tc_email()->tc_mail($sub->email, _t('Confirm Removal from') . ' ' . _h($list->name), $message, $headers);
                } catch (phpmailerException $e) {
                    Cascade::getLogger('error')->error(sprintf('PHPMailer[%s]: %s', $e->getCode(), $e->getMessage()));
                }
                $upd = Node::table('unsubscribe_email')->find(_h($q->id));
                $upd->sent = 1;
                $upd->save();
            }
        }
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (InvalidArgumentException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}
