<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/*
 * Plugin Name: Username Changer
 * Plugin URI: https://codecanyon.net/item/tinycampaign/4755189
 * Version: 1.0.0
 * Description: A simple plugin to make it easier to update/changer a user's username.
 * Author: Joshua Parker
 * Author URI: https://www.joshparker.name/
 * Plugin Slug: username-changer
 */

$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/dashboard');
$app->view->block('dashboard');
define('SCREEN_PARENT', 'plugins');
define('SCREEN', $app->req->get['page']);

$app->hook->add_action('admin_menu', 'uc_username_changer_page', 10);
load_plugin_textdomain('username-changer', 'username-changer/languages');

function uc_get_username_list()
{
    $app = \Liten\Liten::getInstance();
    $list = $app->db->user()->select('id,uname');
    $q = $list->find(function($data) {
        $array = [];
        foreach ($data as $d) {
            $array[] = $d;
        }
        return $array;
    });
    foreach ($q as $v) {
        echo '<option value="' . $v['uname'] . '">' . $v['uname'] . ' => ' . get_name($v['id']) . ' </option>';
    }
}

function uc_send_email_to_user($id)
{
    $app = \Liten\Liten::getInstance();
    
    $email = _tc_email();
    $user = get_user_by('id', $id);
    $uname = $_POST['new_uname'];
    $url = get_base_url();

    $sitename = strtolower($_SERVER['SERVER_NAME']);
    if (substr($sitename, 0, 4) == 'www.') {
        $sitename = substr($sitename, 4);
    }
    
    $site = _h(get_option('system_name'));
    $body = "<p>Dear $user->fname:</p>
        
    <p>A system administrator has updated your $site username.</p>

    <p>When logging into your account, you will need to use the new username below:</p>

    <p><strong>Username:</strong> $uname</p>

    <p><a href=\"$url\">$url</a></p>

    <p>Thank You</p>

    <p>Administrator<br />
    ______________________________________________________<br />
    THIS IS AN AUTOMATED RESPONSE.<br />
    ***DO NOT RESPOND TO THIS EMAIL****</p>
    ";
    
    $message = process_email_html( $body, _t("Username Updated") );
    $headers = "From: $site <auto-reply@$sitename>\r\n";
    if (_h(get_option('tc_smtp_status')) == 0) {
        $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE . "\r\n";
        $headers .= "MIME-Version: 1.0" . "\r\n";
    }

    $email->tc_mail($user->email, _t("Username Updated"), $message, $headers);
    return $app->hook->{'apply_filter'}('username_changer', $message, $headers);
}

function uc_username_changer_page()
{
    // parameters: page slug, page title, and function that will display the page itself
    register_admin_page('username_changer', 'Username Changer', 'uc_username_changer_do_page');
}

function uc_username_changer_do_page()
{
    $app = \Liten\Liten::getInstance();

    if ($app->req->isPost()) {
        $email = $app->db->user()->where('uname = ?',$_POST['old_uname'])->findOne();
        
        $change = $app->db->user();
        $change->uname = $_POST['new_uname'];
        $change->where('uname = ?', $_POST['old_uname']);
        if ($change->update()) {
            /**
             * @since 1.0.2
             */
            $user = get_user_by('uname', $_POST['new_uname']);
            /**
             * Fires after username has been updated successfully.
             * 
             * @since 1.0.2
             * @param object $person Person data object.
             */
            $app->hook->{'do_action'}('post_update_username', $user);
            _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            tc_logger_activity_log_write('Update', 'Username Changer Plugin', $_POST['old_uname'] . ' => ' . $_POST['new_uname'], get_userdata('uname'));
            /*
             * Send email
             */
            uc_send_email_to_user($email->id);
        } else {
            _tc_flash()->error(_tc_flash()->notice(409), $app->req->server['HTTP_REFERER']);
        }
        tc_cache_flush_namespace('user');
    }

    ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('You are here', 'username-changer'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard', 'username-changer'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>plugins/"><i class="fa fa-cog"></i> <?= _t('Plugins List', 'username-changer'); ?></a></li>
            <li class="active"><?= _t('You are here', 'username-changer'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>plugins/options/?page=username_changer" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Current Username', 'username-changer'); ?></label>
                                <select name="old_uname" id="term" class="form-control" required>
                                    <option value="">&nbsp;</option>
                                    <?php uc_get_username_list(); ?>
                                </select>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                           <div class="form-group">
                                <label><font color="red">*</font> <?= _t('New Username', 'username-changer'); ?></label>
                                <input id='input01' class="form-control" name="new_uname" type="text" required/>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        
                    </div>
                    <!-- /.row -->
                </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary"><?= _t('Submit', 'username-changer'); ?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>plugins/'"><?=_t( 'Cancel', 'username-changer' );?></button>
            </div>
        </form>
        <!-- form end -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); } ?>