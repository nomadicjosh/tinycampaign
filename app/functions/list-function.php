<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use Respect\Validation\Validator as v;
use TinyC\Exception\NotFoundException;
use TinyC\Exception\Exception;
use PDOException as ORMException;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Cascade\Cascade;
use TinyC\Config;

/**
 * tinyCampaign List Functions
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Retrieve a list of email lists to show
 * in the menu.
 * 
 * @since 2.0.0
 */
function get_email_lists()
{
    try {
        $lists = app()->db->list()
                ->where('owner = ?', get_userdata('id'))
                ->orderBy('name')
                ->find();
        foreach ($lists as $list) {
            echo '<li' . (Config::get('screen_child') === _escape($list->code) ? ' class="active"' : "") . '><a href="' . get_base_url() . 'list/' . _escape($list->id) . '/"><i class="fa fa-circle-o"></i> ' . _escape($list->name) . '</a></li>';
        }
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

function check_custom_success_url($code, $sub)
{
    $list = get_list_by('code', $code);

    if ($list->redirect_success != NULL && v::url()->validate($list->redirect_success) && $list->optin == 1) {
        // send confirm email and redirect.
        confirm_email_node($code, $sub);
        _tc_flash()->info(sprintf(_t('You were added to the list <strong>%s</strong>, but you will need to check your email in a few minutes in order to confirm your subscription.'), $list->name), _escape($list->redirect_success));
    } elseif ($list->redirect_success != NULL && v::url()->validate($list->redirect_success) && $list->optin == 0) {
        // send success email and redirect to default success.
        subscribe_email_node($code, $sub);
        _tc_flash()->success(sprintf(_t('Thank you for subscribing to the mailing list <strong>%s</strong>.'), $list->name), _escape($list->redirect_success));
    } elseif ($list->redirect_success == NULL && $list->optin == 1) {
        // send confirm email and redirect to default success.
        confirm_email_node($code, $sub);
        _tc_flash()->info(sprintf(_t('You were added to the list <strong>%s</strong>, but you will need to check your email in a few minutes in order to confirm your subscription.'), $list->name), get_base_url() . 'status' . '/');
    } elseif ($list->redirect_success == NULL && $list->optin == 0) {
        // send success email and redirect to default success.
        subscribe_email_node($code, $sub);
        _tc_flash()->success(sprintf(_t('Thank you for subscribing to the mailing list <strong>%s</strong>.'), $list->name), get_base_url() . 'status' . '/');
    }
}

function check_custom_error_url($code)
{
    $list = get_list_by('code', $code);
    if ($list->redirect_unsuccess != null && v::url()->validate($list->redirect_unsuccess)) {
        $url = _escape($list->redirect_unsuccess);
    } elseif ($list->redirect_unsuccess == null) {
        $url = get_base_url() . 'status' . '/';
    }
}

/**
 * Retrieve list info by a given field from the list's table.
 *
 * @since 2.0.0
 * @param string $field The field to retrieve the list with.
 * @param int|string $value A value for $field (id, code).
 */
function get_list_by($field, $value)
{
    try {
        $list = app()->db->list()
                ->where("list.$field = ?", $value)
                ->findOne();

        return $list;
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve campaign based on id.
 *
 * @since 2.0.0
 * @param int $id The unique id of the campaign.
 */
function get_campaign_by_id($id)
{
    try {
        $msg = app()->db->campaign()
                ->where("campaign.id = ?", $id)
                ->findOne();

        return $msg;
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Checks if campaign has a status of processing.
 * 
 * @since 2.0.0
 * @param int $id ID of the campaign.
 * @return bool True if ready, false otherwise.
 */
function is_status_ready($id)
{
    $msg = get_campaign_by_id($id);
    if ($msg->status !== 'ready') {
        return false;
    }
    return true;
}

/**
 * Checks if campaign has a status of processing.
 * 
 * @since 2.0.0
 * @param int $id ID of the campaign.
 * @return bool True if processing, false otherwise.
 */
function is_status_processing($id)
{
    $msg = get_campaign_by_id($id);
    if ($msg->status !== 'processing') {
        return false;
    }
    return true;
}

/**
 * Checks if campaign has a status of paused.
 * 
 * @since 2.0.0
 * @param int $id ID of the campaign.
 * @return bool True if paused, false otherwise.
 */
function is_status_paused($id)
{
    $msg = get_campaign_by_id($id);
    if ($msg->status !== 'paused') {
        return false;
    }
    return true;
}

/**
 * Checks if campaign has a status of sent.
 * 
 * @since 2.0.0
 * @param int $id ID of the campaign.
 * @return bool True if sent, false otherwise.
 */
function is_status_sent($id)
{
    $msg = get_campaign_by_id($id);
    if ($msg->status !== 'sent') {
        return false;
    }
    return true;
}

/**
 * Get count of subscribers from a particular list.
 * 
 * @since 2.0.0
 * @param int $id Email list id.
 * @return int Number of subscribers in a particular list.
 */
function get_list_subscribers_count($id)
{
    try {
        $count = app()->db->subscriber_list()
                ->where('subscriber_list.lid = ?', $id)
                ->count('subscriber_list.sid');
        return $count;
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Converts all links in campaign to become trackable.
 * 
 * @since 2.0.1
 * @param string $body Campaign message.
 * @param int $cid Campaign id.
 * @param int $sid Subscriber id.
 * @param string $campaign Slugified version of campaign subject.
 * @return mixed
 */
function tc_link_tracking($body, $cid, $sid, $campaign)
{
    $link = get_base_url() . 'lt' . '/?cid=' . urlencode($cid) . '&sid=' . urlencode($sid);
    return preg_replace_callback('#(<a.*?href=")([^"]*)("[^>]*?>)#i', function($match) use ($campaign, $link) {
        if (strpos($match[2], '?') === false) {
            $ga = '?';
        } else {
            $ga .= '&';
        }
        $ga .= 'utm_source=tinyc' . '&utm_medium=email' . '&utm_campaign=' . urlencode($campaign);
        return $match[1] . $link . '&url=' . $match[2] . $ga . $match[3];
    }, $body);
}

/**
 * Retrieves a list of user's templates to be used when
 * creating or editing a campaign.
 * 
 * @since 2.0.1
 * @return mixed
 */
function get_user_template()
{
    try {
        $q = app()->db->template()
                ->where('owner = ?', get_userdata('id'))
                ->orderBy('addDate')
                ->find();
        return $q;
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieve active subscriber count based on list id.
 * 
 * @since 2.0.1
 * @param int $id List id.
 * @return int Subscriber count.
 */
function get_list_subscriber_count($id)
{
    try {
        $count = app()->db->subscriber_list()
                ->where('confirmed = "1"')->_and_()
                ->where('unsubscribed = "0"')
                ->where('lid = ?', $id)
                ->count('id');

        return $count;
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Retrieves list data given a list ID or list array.
 *
 * @since 2.0.0
 * @param int|tc_List|null $list
 *            List ID or list array.
 * @param bool $object
 *            If set to true, data will return as an object, else as an array.
 */
function get_list($list, $object = true)
{
    if ($list instanceof \TinyC\tc_List) {
        $_list = $list;
    } elseif (is_array($list)) {
        if (empty($list['id'])) {
            $_list = new \TinyC\tc_List($list);
        } else {
            $_list = \TinyC\tc_List::get_instance($list['id']);
        }
    } else {
        $_list = \TinyC\tc_List::get_instance($list);
    }

    if (!$_list) {
        return null;
    }

    if ($object == true) {
        $_list = array_to_object($_list);
    }

    return $_list;
}

/**
 * Adds label based on status.
 * 
 * @since 2.0.3
 * @param string $status
 * @return string
 */
function tc_list_status_label($status)
{
    $label = [
        'open' => 'label-success',
        'closed' => 'label-danger'
    ];

    return $label[$status];
}

/**
 * Adds custom header called List-Unsubscribe. This allows a user
 * to unsubscribe from a mailing list right from their email client
 * if the email client supports it.
 * 
 * @since 2.0.4
 * @param object $tcMailer Object of PHPMailer.
 * @param object $data Object of merged data.
 * @return string
 */
function list_unsubscribe($tcMailer, $data)
{
    $link = ($data->unsub_mailto != null ? '<mailto:' . $data->unsub_mailto . '>, ' : '') . '<' . get_base_url() . 'xunsubscribe/' . _escape($data->slist_code) . '/lid/' . _escape($data->xlistid) . '/sid/' . _escape($data->xsubscriberid) . '/rid/' . _escape($data->uniqueid) . '>';
    return app()->hook->{'apply_filter'}('list_unsubscribe', $tcMailer->addCustomHeader('List-Unsubscribe', $link));
}

function mark_subscriber_as_spammer($email)
{
    /**
     * Set spam tolerance.
     */
    \TinyC\tc_StopForumSpam::$spamTolerance = _escape(get_option('spam_tolerance'));
    /**
     * Check if subscriber is a spammer.
     */
    if (\TinyC\tc_StopForumSpam::isSpamBotByEmail($email)) {
        try {

            $subscriber = app()->db->subscriber()
                    ->where('email = ?', $email)->_and_()
                    ->where('spammer = "0"')
                    ->findOne();
            $subscriber->set([
                        'spammer' => (int) 1
                    ])
                    ->update();
        } catch (NotFoundException $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (ORMException $e) {
            Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    }
}

/**
 * Retrieve a list of email lists to show
 * in a select dropdown.
 * 
 * @since 2.0.6
 * @param int $id Active list id.
 */
function get_email_list_select($id = null)
{
    try {
        $lists = app()->db->list()
                ->where('owner = ?', get_userdata('id'))
                ->orderBy('name')
                ->find();
        foreach ($lists as $list) {
            echo '<option value="' . _escape($list->id) . '"' . selected(_escape($list->id), $id, false) . '>' . _escape($list->name) . '</option>';
        }
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}
