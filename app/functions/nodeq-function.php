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

/**
 * Sets is_sent status to `true` once message is sent.
 * 
 * @since 2.0.0
 * @param object $message
 */
function set_queued_message_is_sent($message)
{
    $now = Jenssegers\Date\Date::now();
    try {
        Node::dispense('campaign_queue');
        $queue = Node::table('campaign_queue')->find($message->getId());
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
                $message = str_replace('{list_name}', $site, $message);
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

function new_subscriber_notify_email()
{
    $domain = get_domain_name();
    $site = _h(get_option('system_name'));

    try {
        Node::dispense('new_subscriber_notification');

        $queue = Node::table('new_subscriber_notification')->where('sent', '=', 0)->findAll();

        if ($queue->count() == 0) {
            Node::table('new_subscriber_notification')->delete();
        }

        if ($queue->count() > 0) {
            foreach ($queue as $q) {
                $list = get_list_by('id', _h($q->lid));
                $sub = get_subscriber_by('id', _h($q->sid));
                $user = get_user_by('id', _h($list->owner));

                $message = _file_get_contents(APP_PATH . 'views/setting/tpl/new-subscriber-notification.tpl');
                $message = str_replace('{system_name}', $site, $message);
                $message = str_replace('{system_url}', get_base_url(), $message);
                $message = str_replace('{fname}', (_h($user->fname) != '' ? " " . _h($user->fname) : ''), $message);
                $message = str_replace('{list_name}', _h($list->name), $message);
                $message = str_replace('{sname}', _h($sub->fname) . ' ' . _h($sub->lname), $message);
                $message = str_replace('{semail}', _h($sub->email), $message);
                $message = str_replace('{stotal}', get_list_subscriber_count(_h($list->id)), $message);
                $message = str_replace('{email}', _h($user->email), $message);
                $headers = "From: $site <auto-reply@$domain>\r\n";
                if (_h(get_option('tc_smtp_status')) == 0) {
                    $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE;
                    $headers .= "MIME-Version: 1.0" . "\r\n";
                }
                try {
                    _tc_email()->tc_mail(_h($user->email), _t('New Subscriber to') . ' ' . _h($list->name), $message, $headers);
                } catch (phpmailerException $e) {
                    Cascade::getLogger('error')->error(sprintf('PHPMAILER[%s]: %s', $e->getCode(), $e->getMessage()));
                }
                $upd = Node::table('new_subscriber_notification')->find(_h($q->id));
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

function move_old_nodes_to_queue_node()
{
    $app = \Liten\Liten::getInstance();
    try {
        $campaign = $app->db->campaign()
            ->where('status = "sent"')
            ->find();
        foreach ($campaign as $c) {
            $file = $app->config('cookies.savepath') . 'nodes' . DS . 'tinyc' . DS . _h($c->node) . '.data.node';
            if (file_exists($file)) {
                try {
                    Node::dispense('campaign_queue');
                    $node = Node::table(_h($c->node))->where('is_sent', '=', 'true')->findAll();
                    foreach ($node as $n) {
                        $sent = Node::table('campaign_queue');
                        $sent->lid = (int) _h($n->lid);
                        $sent->cid = (int) _h($n->mid);
                        $sent->sid = (int) _h($n->sid);
                        $sent->to_email = (string) _h($n->to_email);
                        $sent->to_name = (string) _h($n->to_name);
                        $sent->timestamp_created = (string) _h($n->timestamp_created);
                        $sent->timestamp_to_send = (string) _h($n->timestamp_to_send);
                        $sent->timestamp_sent = (string) _h($n->timestamp_sent);
                        $sent->is_unsubscribed = (int) 0;
                        $sent->is_sent = (string) _h($n->is_sent);
                        $sent->save();
                    }
                    Node::remove(_h($c->node));
                } catch (NodeQException $e) {
                    Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
                } catch (InvalidArgumentException $e) {
                    Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
                } catch (Exception $e) {
                    Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
                }
            }
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}
