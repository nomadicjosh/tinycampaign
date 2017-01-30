<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;
use app\src\Exception\Exception;
use Cascade\Cascade;

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
        $queue = Node::table("$node")->where('timestamp_to_send', '>=', $now)->find($id);
        $queue->timestamp_sent = (string) $now;
        $queue->is_sent = (bool) true;
        $queue->save();
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
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
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('QUEUESTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}
