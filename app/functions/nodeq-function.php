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
                $message = str_replace('{fname}', (_h($user->fname) != '' ? " "._h($user->fname) : ''), $message);
                $message = str_replace('{list_name}', _h($list->name), $message);
                $message = str_replace('{sname}', _h($sub->fname).' '._h($sub->lname), $message);
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
