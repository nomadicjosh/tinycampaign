<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TinyC\Exception\NotFoundException;
use TinyC\Exception\Exception;
use PDOException as ORMException;

/**
 * Error Router
 *  
 * @license GPLv3
 * 
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Before route check.
 */
$app->before('GET', '/error/', function() {
    if (!hasPermission('access_settings_screen')) {
        _tc_flash()->error(_t("You don't have permission to view the Error Log screen."),get_base_url() . 'dashboard' . '/');
    }
});

$app->get('/error/', function () use($app) {

    try {
        $errors = $app->db->error()
            ->find();
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }

    tc_register_style('datatables');
    tc_register_script('datatables');

    $app->view->display('error/index', [
        'title' => _t('Error Logs'),
        'errors' => $errors
        ]
    );
});

/**
 * Before route check.
 */
$app->before('GET', '/audit-trail/', function() {
    if (!hasPermission('access_settings_screen')) {
        _tc_flash()->error(_t("You don't have permission to view the Audit Trail screen."),get_base_url() . 'dashboard' . '/');
    }
});

$app->get('/audit-trail/', function () use($app) {

    try {
        $audit = $app->db->activity()
            ->orderBy('created_at', 'DESC')
            ->find();
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }

    tc_register_style('datatables');
    tc_register_script('datatables');

    $app->view->display('error/audit', [
        'title' => _t('Audit Trail'),
        'audit' => $audit
        ]
    );
});

/**
 * Before route check.
 */
$app->before('GET', '/error/deleteLog/(\d+)/', function() {
    if (!hasPermission('access_settings_screen')) {
        _tc_flash()->error(_t("You don't have permission to delete error logs."), get_base_url() . 'dashboard' . '/');
        exit();
    }
});

$app->get('/error/deleteLog/(\d+)/', function ($id) use($app) {
    try {
        $app->db->error()
            ->where('id = ?', $id)
            ->delete();
        
        _tc_flash()->success(_tc_flash()->notice(200));
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }
    redirect(get_base_url() . 'error' . '/');
});

$app->get('404', function () use($app) {
    $app->view->display('error/404');
});
