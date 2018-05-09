<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;
use PDOException as ORMException;

/**
 * Before route check.
 */
$app->before('GET', '/setting/', function() {
    if (!hasPermission('access_settings_screen')) {
        _tc_flash()->error(_t("You don't have permission to access the General Settings screen."), get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/setting/', function () use($app) {

    if ($app->req->isPost()) {
        try {
            $options = [
                'system_name', 'system_email', 'mail_throttle',
                'cookieexpire', 'cookiepath', 'backend_skin',
                'tc_core_locale', 'system_timezone', 'api_key',
                'mailing_address', 'collapse_sidebar', 'spam_tolerance'
            ];

            foreach ($options as $option_name) {
                if (!isset($app->req->post[$option_name]))
                    continue;
                $value = $app->req->post[$option_name];
                update_option($option_name, $value);
            }
            // Update more options here
            $app->hook->{'do_action'}('update_general_options');
            tc_logger_activity_log_write('Update', 'Settings', 'General Settings', get_userdata('uname'));
            _tc_flash()->success(_tc_flash()->notice(200));
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    }

    tc_register_style('select2');
    tc_register_script('select2');

    $app->view->display('setting/index', [
        'title' => _t('General Settings')
            ]
    );
});

/**
 * Before route check.
 */
$app->before('GET', '/setting/smtp/', function() {
    if (!hasPermission('access_settings_screen')) {
        _tc_flash()->error(_t("You don't have permission to access the SMTP Settings screen."), get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/setting/smtp/', function () use($app) {
    try {
        $node = Node::table('php_encryption')->find(1);
    } catch (app\src\NodeQ\NodeQException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (NodeQException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }

    if ($app->req->isPost()) {
        try {
            update_option('tc_smtp_host', $app->req->post['tc_smtp_host']);
            update_option('tc_smtp_username', $app->req->post['tc_smtp_username']);
            update_option('tc_smtp_password', Crypto::encrypt($app->req->post['tc_smtp_password'], Key::loadFromAsciiSafeString($node->key)));
            update_option('tc_smtp_port', $app->req->post['tc_smtp_port']);
            update_option('tc_smtp_service', $app->req->post['tc_smtp_service']);
            update_option('tc_smtp_smtpsecure', $app->req->post['tc_smtp_smtpsecure']);
            update_option('tc_smtp_mailbox', $app->req->post['tc_smtp_mailbox']);
            update_option('tc_smtp_status', $app->req->post['tc_smtp_status']);

            // Update more options here
            $app->hook->do_action('update_smtp_options');
            tc_logger_activity_log_write('Update', 'Settings', 'SMTP Settings', get_userdata('uname'));
            _tc_flash()->success(_tc_flash()->notice(200));
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    }

    try {
        if (_escape(get_option('tc_smtp_password')) != '') {
            $password = Crypto::decrypt(_escape(get_option('tc_smtp_password')), Key::loadFromAsciiSafeString($node->key));
        } else {
            $password = _escape(get_option('tc_smtp_password'));
        }
    } catch (Defuse\Crypto\Exception\BadFormatException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (app\src\Exception\Exception $e) {
        _tc_flash()->error($e->getMessage());
    }

    tc_register_style('select2');
    tc_register_script('select2');

    $app->view->display('setting/smtp', [
        'title' => _t('SMTP Settings'),
        'password' => $password
            ]
    );
});

/**
 * Before route check.
 */
$app->before('POST', '/setting/smtp/test/', function() {
    if (!hasPermission('access_settings_screen')) {
        redirect(get_base_url() . 'dashboard' . '/');
    }
});

$app->post('/setting/smtp/test/', function () use($app) {

    if ($app->req->isPost()) {
        try {
            _tc_email()->tc_mail($app->req->post['to_email'], $app->req->post['subject'], $app->req->post['message']);
            tc_logger_activity_log_write('Update', 'Settings', 'SMTP Settings', get_userdata('uname'));
        } catch (\phpmailerException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        }
    }

    redirect(get_base_url() . 'setting' . '/smtp/');
});

/**
 * Before route check.
 */
$app->before('GET', '/setting/bounce/', function() {
    if (!hasPermission('access_settings_screen')) {
        _tc_flash()->error(_t("You don't have permission to access the Bounce Mail Handler screen."), get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/setting/bounce/', function () use($app) {
    try {
        $node = Node::table('php_encryption')->find(1);
    } catch (app\src\NodeQ\NodeQException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (NodeQException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    }

    if ($app->req->isPost()) {
        try {
            update_option('tc_bmh_host', $app->req->post['tc_bmh_host']);
            update_option('tc_bmh_username', $app->req->post['tc_bmh_username']);
            update_option('tc_bmh_password', Crypto::encrypt($app->req->post['tc_bmh_password'], Key::loadFromAsciiSafeString($node->key)));
            update_option('tc_bmh_mailbox', $app->req->post['tc_bmh_mailbox']);
            update_option('tc_bmh_port', $app->req->post['tc_bmh_port']);
            update_option('tc_bmh_service', $app->req->post['tc_bmh_service']);
            update_option('tc_bmh_service_option', $app->req->post['tc_bmh_service_option']);

            // Update more options here
            $app->hook->{'do_action'}('update_bounce_options');
            tc_logger_activity_log_write('Update', 'Settings', 'Bounce Email Settings', get_userdata('uname'));
            _tc_flash()->success(_tc_flash()->notice(200));
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    }

    try {
        if (_escape(get_option('tc_bmh_password')) != '') {
            $password = Crypto::decrypt(_escape(get_option('tc_bmh_password')), Key::loadFromAsciiSafeString($node->key));
        } else {
            $password = _escape(get_option('tc_bmh_password'));
        }
    } catch (Defuse\Crypto\Exception\BadFormatException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (app\src\Exception\Exception $e) {
        _tc_flash()->error($e->getMessage());
    }

    tc_register_style('select2');
    tc_register_script('select2');

    $app->view->display('setting/bounce', [
        'title' => _t('Bounce Email Settings'),
        'password' => $password
            ]
    );
});

$app->setError(function() use($app) {

    $app->view->display('error/404', ['title' => '404 Error']);
});
