<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use Respect\Validation\Validator as v;
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

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
    try {
        $lists = $app->db->list()
            ->where('owner = ?', get_userdata('id'))
            ->find();
        foreach ($lists as $list) {
            echo '<li' . (SCREEN === $list->code ? ' class="active"' : "") . '><a href="' . get_base_url() . 'list/' . $list->id . '/"><i class="fa fa-circle-o"></i> ' . $list->name . '</a></li>';
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
        _tc_flash()->info(sprintf(_t('You were added to the list <strong>%s</strong>, but you will need to check your email in a few minutes in order to confirm your subscription.'), $list->name), _h($list->redirect_success));
    } elseif ($list->redirect_success != NULL && v::url()->validate($list->redirect_success) && $list->optin == 0) {
        // send success email and redirect to default success.
        subscribe_email_node($code, $sub);
        _tc_flash()->success(sprintf(_t('Thank you for subscribing to the mailing list <strong>%s</strong>.'), $list->name), _h($list->redirect_success));
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
        $url = _h($list->redirect_unsuccess);
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
    $app = \Liten\Liten::getInstance();

    try {
        $list = $app->db->list()
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
    $app = \Liten\Liten::getInstance();

    try {
        $msg = $app->db->campaign()
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
    $app = \Liten\Liten::getInstance();
    try {
        $count = $app->db->subscriber_list()
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
 * @return mixed
 */
function tc_link_tracking($body, $cid, $sid)
{
    $link = get_base_url() . 'lt' . '/';
    return preg_replace_callback('#(<a.*?href=")([^"]*)("[^>]*?>)#i', function($match) use ($cid, $sid, $link) {
        $old_url = $match[2];
        if (strpos($old_url, '?') === false) {
            $url = '?';
        } else {
            $url .= '&';
        }
        $url .= 'utm_source=email' . '&utm_medium=email' . '&utm_term=' . $sid . '&utm_campaign=' . urlencode($cid);
        return $match[1] . $link . $url . '&url=' . $match[2] . $match[3];
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
    $app = \Liten\Liten::getInstance();
    try {
        $q = $app->db->template()
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
    $app = \Liten\Liten::getInstance();
    try {
        $count = $app->db->subscriber_list()
            ->where('confirmed = "1"')->_and_()
            ->where('unsubscribe = "0"')
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
 * Function used for multiple sending servers.
 * 
 * @since 2.0.1
 * @param object $server Server info.
 * @param string $to Email recipient.
 * @param string $subject Email subject.
 * @param string $message Email message.
 */
function tinyc_email($server, $to, $subject, $message)
{
    if (is_object($server)) {
        try {
            $node = app\src\NodeQ\tc_NodeQ::table('php_encryption')->find(1);
        } catch (app\src\NodeQ\NodeQException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (NotFoundException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->{'error'}($e->getMessage());
        }

        try {
            $password = Crypto::decrypt(_h($server->password), Key::loadFromAsciiSafeString($node->key));
        } catch (Defuse\Crypto\Exception\BadFormatException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (app\src\Exception\Exception $e) {
            _tc_flash()->{'error'}($e->getMessage());
        }

        try {
            $tcMailer = _tc_phpmailer(true);
            $tcMailer->Mailer = "smtp";
            $tcMailer->ContentType = "text/html";
            $tcMailer->CharSet = "UTF-8";
            $tcMailer->XMailer = 'tinyCampaign ' . CURRENT_RELEASE;
            $tcMailer->ReturnPath = (_h(get_option('tc_bmh_username')) == '' ? _h($server->remail) : _h(get_option('tc_bmh_username')));
            $tcMailer->From = _h($server->femail);
            $tcMailer->FromName = _h($server->fname);
            $tcMailer->Sender = $tcMailer->From; // Return-Path
            $tcMailer->AddReplyTo(_h($server->remail), _h($server->rname)); // Reply-To
            $tcMailer->addAddress($to);
            $tcMailer->Subject = $subject;
            $tcMailer->Body = $message;
            $tcMailer->Host = _h($server->hname);
            $tcMailer->SMTPSecure = _h($server->protocol);
            $tcMailer->Port = _h($server->port);
            $tcMailer->SMTPAuth = true;
            $tcMailer->isHTML(true);
            $tcMailer->Username = _h($server->uname);
            $tcMailer->Password = $password;
            $tcMailer->send();
            _tc_flash()->success(_t('Email Sent.'));
        } catch (phpmailerException $e) {
            _tc_flash()->{'error'}($e->getMessage());
        } catch (\app\src\Exception\Exception $e) {
            _tc_flash()->{'error'}($e->getMessage());
        }
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
