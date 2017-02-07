<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;

/**
 * Username Changer Plugin Router
 *  
 * @license GPLv3
 * 
 * @author  Joshua Parker <joshmac3@icloud.com>
 */

require(TC_PLUGIN_DIR . 'username-changer/classes/View.php');
$view = new View();

/**
 * Before route check.
 */
$app->before('GET|POST', '/username-changer/.*', function () {
    if (!hasPermission('manage_plugins')) {
        _tc_flash()->error(_t('Permission denied to view requested screen.'), get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/username-changer/', function () use($app, $view) {

    if ($app->req->isPost()) {
        try {
            $email = $app->db->user()->where('uname = ?', $app->req->post['old_uname'])->findOne();

            $change = $app->db->user();
            $change->uname = $app->req->post['new_uname'];
            $change->where('uname = ?', $app->req->post['old_uname']);
            if ($change->update()) {
                /**
                 * @since 1.0.2
                 */
                $user = get_user_by('uname', $app->req->post['new_uname']);
                /**
                 * Fires after username has been updated successfully.
                 * 
                 * @since 1.0.2
                 * @param object $person Person data object.
                 */
                $app->hook->{'do_action'}('post_update_username', $user);
                /**
                 * Flush user cache.
                 */
                tc_cache_flush_namespace('user');
                /*
                 * Send email
                 */
                uc_send_email_to_user(_h($email->id));
                tc_logger_activity_log_write(_t('Update'), _t('Username Changer Plugin'), $app->req->post['old_uname'] . ' => ' . $app->req->post['new_uname'], get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } else {
                _tc_flash()->error(_tc_flash()->notice(409), $app->req->server['HTTP_REFERER']);
            }
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    }

    tc_register_style('datatables');
    tc_register_style('select2');
    tc_register_style('iCheck');
    tc_register_style('datetime');
    tc_register_script('select2');
    tc_register_script('moment.js');
    tc_register_script('datetime');
    tc_register_script('iCheck');
    tc_register_script('datatables');

    $view->display('username-changer/views/index', [
        'title' => _t('Username Changer')
    ]);
});
