<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Index Router
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */

$email = _tc_email();
$hasher = new \app\src\PasswordHash(8, FALSE);
$flashNow = new \app\src\tc_Messages();

/**
 * Before route check.
 */
$app->before('GET|POST', '/', function() {
    if (is_user_logged_in()) {
        redirect(get_base_url() . 'dashboard' . '/');
    }
});

$app->before('GET|POST', '/', function () use($app) {

    if ($app->req->isPost()) {
        /**
         * This function is documented in app/functions/auth-function.php.
         * 
         * @since 2.0.0
         */
        tc_authenticate_user($app->req->_post('uname'), $app->req->_post('password'), $app->req->_post('rememberme'));
    }

    $app->view->display('index/index', [
        'title' => 'Login'
        ]
    );
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/login/', function() {
    if (is_user_logged_in()) {
        redirect(get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/login/', function () use($app) {

    if ($app->req->isPost()) {
        /**
         * This function is documented in app/functions/auth-function.php.
         * 
         * @since 2.0.0
         */
        tc_authenticate_user($app->req->_post('uname'), $app->req->_post('password'), $app->req->_post('rememberme'));
    }

    $app->view->display('index/login', [
        'title' => 'Login'
        ]
    );
});

$app->setError(function () use($app) {

    $app->view->display('error/404', [
        'title' => '404 Error'
    ]);
});
