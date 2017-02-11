<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;

/**
 * tinyCampaign Subscriber Functions
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();

/**
 * Retrieve subscriber info by a given field from the subscriber's table.
 *
 * @since 2.0.0
 * @param string $field The field to retrieve the subscriber with.
 * @param int|string $value A value for $field (id or email).
 */
function get_subscriber_by($field, $value)
{
    $app = \Liten\Liten::getInstance();
    try {
        $subscriber = $app->db->subscriber()
            ->where("subscriber.$field = ?", $value)
            ->findOne();

        return $subscriber;
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Returns the name of a particular subscriber.
 *
 * @since 2.0.0
 * @param int $id
 *            Subscriber id.
 * @return string
 */
function get_sub_name($id)
{
    if ('' == _trim($id)) {
        $message = _t('Invalid subscriber id: empty id given.');
        _incorrectly_called(__FUNCTION__, $message, '2.0.0');
        return;
    }

    if (!is_numeric($id)) {
        $message = _t('Invalid subscriber id: subscriber id must be numeric.');
        _incorrectly_called(__FUNCTION__, $message, '2.0.0');
        return;
    }

    $name = get_subscriber_by('id', $id);

    return _h($name->fname) . ' ' . _h($name->lname);
}

/**
 * Checks whether the given subscriber exists.
 *
 * @since 2.0.0
 * @param string $email
 *            Subscriber to check.
 * @return int|false The subscriber's ID on success, and false on failure.
 */
function subcriber_exists($email)
{
    if ($subscriber = get_subscriber_by('email', $email)) {
        return $subscriber->id;
    }
    return false;
}

function confirm_email_node($code, $sub)
{
    Node::dispense('confirm_email');
    try {
        $node = Node::table('confirm_email');
        $node->lcode = (string) $code;
        $node->sid = (int) $sub->sid;
        $node->scode = (string) $sub->code;
        $node->sent = (int) 0;
        $node->save();
    } catch (NodeQException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

function subscribe_email_node($code, $sub)
{
    Node::dispense('subscribe_email');
    try {
        $node = Node::table('subscribe_email');
        $node->lcode = (string) $code;
        $node->sid = (int) $sub->sid;
        $node->scode = (string) $sub->code;
        $node->sent = (int) 0;
        $node->save();
    } catch (NodeQException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

function unsubscribe_email_node($code, $sub)
{
    Node::dispense('unsubscribe_email');
    try {
        $node = Node::table('unsubscribe_email');
        $node->lcode = (string) $code;
        $node->sid = (int) $sub->sid;
        $node->scode = (string) $sub->code;
        $node->sent = (int) 0;
        $node->save();
    } catch (NodeQException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }
}

/**
 * Generates a button for confirm subscription email.
 * 
 * @since 2.0.0
 * @param mixed $data NodeQ data.
 * @return mixed
 */
function confirm_subscription_button($data)
{
    $list = get_list_by('code', $data->lcode);

    $link = get_base_url() . 'confirm' . '/' . $data->scode . '/lid/' . $list->id . '/sid/' . $data->sid . '/';
    return sprintf('<a href="%s" class="btn-primary" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #348eda; margin: 0; border-color: #348eda; border-style: solid; border-width: 10px 20px;">' . _t('Confirm Subscription') . '</a>', $link);
}

/**
 * Generates an updated preferences button for successful
 * email subscription.
 * 
 * @since 2.0.0
 * @param mixed $sub Subscriber's data.
 * @return mixed
 */
function update_preferences_button($sub)
{
    $url = get_base_url() . 'preferences' . '/' . $sub->code . '/subscriber/' . $sub->id . '/';
    return sprintf('<a href="%s" class="btn-primary" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #348eda; margin: 0; border-color: #348eda; border-style: solid; border-width: 10px 20px;">' . _t('Update Preferences') . '</a>', $url);
}

/**
 * Retrieves subscriber data given a subscriber ID or subscriber array.
 *
 * @since 2.0.0
 * @param int|tc_Subscriber|null $subscriber
 *            Subscriber ID or subscriber array.
 * @param bool $object
 *            If set to true, data will return as an object, else as an array.
 */
function get_subscriber($subscriber, $object = true)
{
    if ($subscriber instanceof \app\src\tc_Subscriber) {
        $_subscriber = $subscriber;
    } elseif (is_array($subscriber)) {
        if (empty($subscriber['id'])) {
            $_subscriber = new \app\src\tc_Subscriber($subscriber);
        } else {
            $_subscriber = \app\src\tc_Subscriber::get_instance($subscriber['id']);
        }
    } else {
        $_subscriber = \app\src\tc_Subscriber::get_instance($subscriber);
    }

    if (!$_subscriber) {
        return null;
    }

    if ($object == true) {
        $_subscriber = array_to_object($_subscriber);
    }

    return $_subscriber;
}

/**
 * Adds label to subscriber's status.
 * 
 * @since 2.0.3
 * @param string $status
 * @return string
 */
function tc_subscriber_status_label($status)
{
    $label = [
        0 => 'label-success',
        1 => 'label-danger'
    ];

    return $label[$status];
}

/**
 * Adds label to subscriber's status.
 * 
 * @since 2.0.3
 * @param string $status
 * @return string
 */
function tc_blacklist_status_label($status)
{
    $label = [
        'true' => 'label-success',
        'false' => 'label-danger'
    ];

    return $label[$status];
}
