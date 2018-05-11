<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/*
 * Plugin Name: Username Changer
 * Plugin URI: https://codecanyon.net/item/tinycampaign/4755189
 * Version: 1.0.1
 * Description: A simple plugin to make it easier to update/changer a user's username.
 * Author: Joshua Parker
 * Author URI: https://www.joshparker.name/
 * Plugin Slug: username-changer
 */
include( TC_PLUGIN_DIR . 'username-changer/router/username-changer.router.php' );
load_plugin_textdomain('username-changer', 'username-changer/languages');

$app = Liten\Liten::getInstance();

function uc_get_username_list()
{
    $app = Liten\Liten::getInstance();
    $list = $app->db->user()->select('id,uname');
    $q = $list->find(function($data) {
        $array = [];
        foreach ($data as $d) {
            $array[] = $d;
        }
        return $array;
    });
    foreach ($q as $v) {
        echo '<option value="' . _escape($v['uname']) . '">' . _escape($v['uname']) . ' => ' . get_name(_escape($v['id'])) . ' </option>';
    }
}

function uc_send_email_to_user($id)
{
    $app = Liten\Liten::getInstance();
    $user = get_user_by('id', $id);
    $uname = $app->req->post['new_uname'];
    $domain = get_domain_name();
    $site = _escape(get_option('system_name'));

    $message = _file_get_contents(TC_PLUGIN_DIR . 'username-changer/tpl/username-changer-notification.tpl');
    $message = str_replace('{system_name}', $site, $message);
    $message = str_replace('{system_url}', get_base_url(), $message);
    $message = str_replace('{fname}', _escape($user->fname), $message);
    $message = str_replace('{uname}', $uname, $message);
    $message = str_replace('{email}', _escape($user->email), $message);
    $headers = "From: $site <auto-reply@$domain>\r\n";
    if (_escape(get_option('tc_smtp_status')) == 0) {
        $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE . "\r\n";
        $headers .= "MIME-Version: 1.0" . "\r\n";
    }

    try {
        _tc_email()->tc_mail(_escape($user->email), _t("Username Updated"), $message, $headers);
    } catch (phpmailerException $e) {
        _tc_flash()->error($e->getMessage());
    }
}

function uc_username_changer_page_url()
{
    echo '<li' . (SCREEN === 'uchanger' ? ' class="active"' : '') . '><a href="' . get_base_url() . 'username-changer/"><i class="fa fa-circle-o"></i> ' . _t('Username Changer', 'username-changer') . '</a></li>';
}

$app->hook->{'add_action'}('plugin_parent_page', 'uc_username_changer_page_url', 10);
