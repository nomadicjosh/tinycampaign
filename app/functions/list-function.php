<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use Respect\Validation\Validator as v;

/**
 * tinyCampaign List Functions
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();

/**
 * Retrieve a list of email lists to show
 * in the menu.
 * 
 * @since 2.0.0
 */
function get_email_lists()
{
    $app = \Liten\Liten::getInstance();
    $lists = $app->db->list()
        ->where('owner = ?', get_userdata('id'))
        ->find();
    foreach ($lists as $list) {
        echo '<li' . (SCREEN === $list->code ? ' class="active"' : "") . '><a href="' . get_base_url() . 'list/' . $list->id . '/"><i class="fa fa-circle-o"></i> ' . $list->name . '</a></li>';
    }
}

function check_custom_success_url($code)
{
    $app = \Liten\Liten::getInstance();
    $email = _tc_email();

    $list = get_list_by('code', $code);
    if ($list->redirect_success != null && v::url()->validate($list->redirect_success) && $list->optin == 1) {
        // send confirm email and redirect.
        $url = _h($list->redirect_success);
    } elseif ($list->redirect_success != null && v::url()->validate($list->redirect_success) && $list->optin == 0) {
        // send success email and redirect.
        $url = _h($list->redirect_success);
    } elseif ($list->redirect_success == null && $list->optin == 1) {
        // send confirm email and redirect to default success.
        $url = get_base_url() . 'success' . '/' . $list->code . '/';
    } elseif ($list->redirect_success == null && $list->optin == 0) {
        // send confirm success and redirect to default success.
        $url = get_base_url() . 'success' . '/' . $list->code . '/';
    }
}

function check_custom_error_url($code)
{
    $app = \Liten\Liten::getInstance();

    $list = get_list_by('code', $code);
    if ($list->redirect_unsuccess != null && v::url()->validate($list->redirect_unsuccess)) {
        $url = _h($list->redirect_unsuccess);
    } elseif ($list->redirect_unsuccess == null) {
        $url = get_base_url() . 'unsuccess' . '/' . $list->code . '/';
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
    $app = \Liten\Liten::getInstance();

    $list = $app->db->list()
        ->where("list.$field = ?", $value)
        ->findOne();

    return $list;
}

/**
 * Retrieve campaign based on id.
 *
 * @since 2.0.0
 * @param int $id The unique id of the campaign.
 */
function get_campaign_by_id($id)
{
    $app = \Liten\Liten::getInstance();

    $msg = $app->db->campaign()
        ->where("campaign.id = ?", $id)
        ->findOne();

    return $msg;
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
 * @since 6.0.0
 * @param int $id Email list id.
 * @return int Number of subscribers in a particular list.
 */
function get_list_subscribers_count($id)
{
    $app = \Liten\Liten::getInstance();
    $count = $app->db->subscriber_list()
        ->where('subscriber_list.lid = ?', $id)
        ->count('subscriber_list.sid');
    return $count;
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
    if ($list instanceof \app\src\tc_List) {
        $_list = $list;
    } elseif (is_array($list)) {
        if (empty($list['id'])) {
            $_list = new \app\src\tc_List($list);
        } else {
            $_list = \app\src\tc_List::get_instance($list['id']);
        }
    } else {
        $_list = \app\src\tc_List::get_instance($list);
    }

    if (!$_list) {
        return null;
    }

    if ($object == true) {
        $_list = array_to_object($_list);
    }

    return $_list;
}
