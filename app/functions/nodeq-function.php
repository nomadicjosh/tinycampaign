<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TinyC\Exception\NotFoundException;
use TinyC\NodeQ\tc_NodeQ as Node;
use TinyC\NodeQ\NodeQException;
use TinyC\Exception\Exception;
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

        $campaign = app()->db->campaign_queue();
        $campaign->set([
                    'timestamp_sent' => (string) $now,
                    'is_sent' => 'true'
                ])
                ->where('id', $message->getId())
                ->update();
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function send_confirm_email()
{
    $domain = get_domain_name();
    $site = _escape(get_option('system_name'));

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
                $message = str_replace('{list_name}', _escape($list->name), $message);
                $message = str_replace('{confirm_url}', confirm_subscription_button($q), $message);
                $message = str_replace('{system_name}', $site, $message);
                $headers = "From: $site <auto-reply@$domain>\r\n";
                if (_escape(get_option('tc_smtp_status')) == 0) {
                    $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE;
                    $headers .= "MIME-Version: 1.0" . "\r\n";
                }
                try {
                    _tc_email()->tc_mail($sub->email, _t('Confirm Subscription for') . ' ' . _escape($list->name), $message, $headers);
                } catch (phpmailerException $e) {
                    Cascade::getLogger('error')->error(sprintf('PHPMailer[%s]: %s', $e->getCode(), $e->getMessage()));
                }
                $upd = Node::table('confirm_email')->find(_escape($q->id));
                $upd->sent = 1;
                $upd->save();
            }
        }
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (InvalidArgumentException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function send_subscribe_email()
{
    $domain = get_domain_name();
    $site = _escape(get_option('system_name'));

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
                $message = str_replace('{list_name}', _escape($list->name), $message);
                $message = str_replace('{personal_preferences}', update_preferences_button($sub), $message);
                $message = str_replace('{system_name}', $site, $message);
                $headers = "From: $site <auto-reply@$domain>\r\n";
                if (_escape(get_option('tc_smtp_status')) == 0) {
                    $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE;
                    $headers .= "MIME-Version: 1.0" . "\r\n";
                }
                try {
                    _tc_email()->tc_mail($sub->email, _t('Welcome to') . ' ' . _escape($list->name), $message, $headers);
                } catch (phpmailerException $e) {
                    Cascade::getLogger('error')->error(sprintf('PHPMailer[%s]: %s', $e->getCode(), $e->getMessage()));
                }
                $upd = Node::table('subscribe_email')->find(_escape($q->id));
                $upd->sent = 1;
                $upd->save();
            }
        }
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (InvalidArgumentException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function send_unsubscribe_email()
{
    $domain = get_domain_name();
    $site = _escape(get_option('system_name'));

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
                $message = str_replace('{system_name}', $site, $message);
                $message = str_replace('{personal_preferences}', update_preferences_button($sub), $message);
                $headers = "From: $site <auto-reply@$domain>\r\n";
                if (_escape(get_option('tc_smtp_status')) == 0) {
                    $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE;
                    $headers .= "MIME-Version: 1.0" . "\r\n";
                }
                try {
                    _tc_email()->tc_mail($sub->email, _t('Confirm Removal from') . ' ' . _escape($list->name), $message, $headers);
                } catch (phpmailerException $e) {
                    Cascade::getLogger('error')->error(sprintf('PHPMailer[%s]: %s', $e->getCode(), $e->getMessage()));
                }
                $upd = Node::table('unsubscribe_email')->find(_escape($q->id));
                $upd->sent = 1;
                $upd->save();
            }
        }
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (InvalidArgumentException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function new_subscriber_notify_email()
{
    $domain = get_domain_name();
    $site = _escape(get_option('system_name'));

    try {
        Node::dispense('new_subscriber_notification');

        $queue = Node::table('new_subscriber_notification')->where('sent', '=', 0)->findAll();

        if ($queue->count() == 0) {
            Node::table('new_subscriber_notification')->delete();
        }

        if ($queue->count() > 0) {
            foreach ($queue as $q) {
                $list = get_list_by('id', _escape($q->lid));
                $sub = get_subscriber_by('id', _escape($q->sid));
                $user = get_user_by('id', _escape($list->owner));

                $message = _file_get_contents(APP_PATH . 'views/setting/tpl/new-subscriber-notification.tpl');
                $message = str_replace('{system_name}', $site, $message);
                $message = str_replace('{system_url}', get_base_url(), $message);
                $message = str_replace('{fname}', (_escape($user->fname) != '' ? " " . _escape($user->fname) : ''), $message);
                $message = str_replace('{list_name}', _escape($list->name), $message);
                $message = str_replace('{sname}', _escape($sub->fname) . ' ' . _escape($sub->lname), $message);
                $message = str_replace('{semail}', _escape($sub->email), $message);
                $message = str_replace('{stotal}', get_list_subscriber_count(_escape($list->id)), $message);
                $message = str_replace('{email}', _escape($user->email), $message);
                $headers = "From: $site <auto-reply@$domain>\r\n";
                if (_escape(get_option('tc_smtp_status')) == 0) {
                    $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE;
                    $headers .= "MIME-Version: 1.0" . "\r\n";
                }
                try {
                    _tc_email()->tc_mail(_escape($user->email), _t('New Subscriber to') . ' ' . _escape($list->name), $message, $headers);
                } catch (phpmailerException $e) {
                    Cascade::getLogger('error')->error(sprintf('PHPMAILER[%s]: %s', $e->getCode(), $e->getMessage()));
                }
                $upd = Node::table('new_subscriber_notification')->find(_escape($q->id));
                $upd->sent = 1;
                $upd->save();
            }
        }
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (InvalidArgumentException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function move_old_nodes_to_queue_node()
{
    try {
        $campaign = app()->db->campaign()
                ->where('status = "sent"')
                ->find();
        foreach ($campaign as $c) {
            $file = app()->config('cookies.savepath') . 'nodes' . DS . 'tinyc' . DS . _escape($c->node) . '.data.node';
            if (file_exists($file)) {
                try {
                    Node::dispense(_escape($c->node));
                    $node = Node::table(_escape($c->node))->where('is_sent', '=', 'true')->findAll();
                    foreach ($node as $n) {
                        $sent = Node::table($c->node);
                        $sent->lid = (int) _escape($n->lid);
                        $sent->cid = (int) _escape($n->mid);
                        $sent->sid = (int) _escape($n->sid);
                        $sent->to_email = (string) _escape($n->to_email);
                        $sent->to_name = (string) _escape($n->to_name);
                        $sent->timestamp_created = (string) _escape($n->timestamp_created);
                        $sent->timestamp_to_send = (string) _escape($n->timestamp_to_send);
                        $sent->timestamp_sent = (string) _escape($n->timestamp_sent);
                        $sent->is_unsubscribed = (int) 0;
                        $sent->is_sent = (string) _escape($n->is_sent);
                        $sent->save();
                    }
                    Node::remove(_escape($c->node));
                } catch (NodeQException $e) {
                    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
                } catch (InvalidArgumentException $e) {
                    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
                } catch (Exception $e) {
                    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
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

function check_rss_campaigns()
{
    try {
        $feeds = app()->db->rss_campaign()->where('status', 'active');
        if ($feeds->count() <= 0) {
            return false;
        }

        foreach ($feeds->find() as $feed) {
            $rss = new SimplePie();
            $rss->set_feed_url(_escape($feed->rss_feed));
            $rss->enable_cache(false);
            //$rss->set_cache_location(APP_PATH . 'tmp' . DS . 'cache' . DS);
            //$rss->set_cache_duration(3600);

            // Init feed
            $rss->init();

            // Make sure the page is being served with the UTF-8 headers.
            $rss->handle_content_type();
            $items = $rss->get_items();

            $accumulatedText = '';
            $accumulatedGuid = [];

            if ($rss->error()) {
                foreach ($rss->error() as $key => $error) {
                    Cascade::getLogger('error')->error(_t('The following feed contains errors:') . ' ' . _escape($feed->rss_feed)[$key] . "\n");
                }
            }

            foreach ($items as $item) {
                $title = $item->get_title();
                //decode HTML entities in title to UTF8
                //run it two times to support double encoding, if for example "&uuml;" is encoded as "&amp;uuml;"
                $nr_entitiy_decode_runs = 2;
                for ($i = 0; $i < $nr_entitiy_decode_runs; $i++) {
                    $title = html_entity_decode($title, ENT_COMPAT | ENT_HTML401, "UTF-8");
                }
                $guid = $item->get_id(true);
                $date = $item->get_date('m/d/y h:i a');
                $link = $item->get_link();
                $description = $item->get_description();

                // check if item has been sent already
                $rss_guid = app()->db->query('SELECT guid FROM rss_guid WHERE guid = ?', [$guid])->findOne();

                // if so, skip
                if ($rss_guid->guid != '') {
                    continue;
                }// if not send it
                else {
                    $text = [];
                    $text[] = '<h2><a href="'.$link.'">' . $title . '</a></h2> ' . '<small><strong>' . $date . '</strong></small>' .  "\n";
                    $text[] = $description;
                    $accumulatedText .= implode("\n", $text) . "\n\n";
                    $accumulatedGuid[] = $guid;
                }
            }

            if (empty($accumulatedText)) {
                //nothing to send
                return false;
            }

            $node = Node::table(_escape($feed->node));
            $node->rcid = (int) _escape($feed->id);
            $node->rss_content = (string) $accumulatedText;
            $node->is_processed = (string) 'false';
            $node->save();

            foreach ($accumulatedGuid as $guid) {
                $rss_guid = app()->db->rss_guid();
                $rss_guid->insert([
                    'guid' => $guid
                ]);
            }
        }
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}

function generate_rss_campaigns()
{
    try {
        $campaigns = app()->db->rss_campaign()->where('status', 'active')->find();
        foreach ($campaigns as $campaign) {
            /**
             * Delete records that have been processed.
             */
            Node::table(_escape($campaign->node))->where('is_processed', '=', 'true')->delete();
            /**
             * Look for records that have not been processed.
             */
            $node = Node::table(_escape($campaign->node))->where('is_processed', '=', 'false');
            if ($node->findAll()->count() > 0) {
                $rss = $node->where('rcid', '=', _escape($campaign->id))->find();

                $template = get_template_by_id(_escape($campaign->tid));

                $message = _escape($template->content);
                $message = str_replace('{rss_feed}', _escape($rss->rss_content), $message);

                $now = Jenssegers\Date\Date::now();

                $cpgn = app()->db->campaign();
                $cpgn->insert([
                    'owner' => _escape($campaign->owner),
                    'subject' => _escape($campaign->subject),
                    'from_name' => _escape($campaign->from_name),
                    'from_email' => _escape($campaign->from_email),
                    'html' => $message,
                    'footer' => _file_get_contents(APP_PATH . 'views/setting/tpl/email_footer.tpl'),
                    'status' => 'ready',
                    'sendstart' => (string) $now->parse('+1 hour'),
                    'addDate' => (string) $now
                ]);

                $ID = $cpgn->lastInsertId();

                $lists = maybe_unserialize(_escape($campaign->lid));

                foreach ($lists as $list) {
                    $cpgn_list = app()->db->campaign_list();
                    $cpgn_list->insert([
                        'cid' => $ID,
                        'lid' => $list
                    ]);
                }

                $_cpgn = get_campaign_by_id($ID);

                if (_escape($_cpgn->status) == 'processing') {
                    _tc_flash()->error(_t('RSS Campaign is already queued.'));
                    exit();
                }

                if (_escape($_cpgn->id) <= 0) {
                    _tc_flash()->success(_t('RSS Campaign does not exist.'));
                    exit();
                }

                app()->hook->{'do_action'}('queue_campaign', $_cpgn);
            }

            $upd = Node::table(_escape($campaign->node))->find($node->id);
            $upd->is_processed = (string) 'true';
            $upd->save();
        }
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    } catch (NodeQException $e) {
        Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
    } catch (Exception $e) {
        Cascade::getLogger('error')->error(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
    }
}
