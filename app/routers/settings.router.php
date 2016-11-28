<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Cascade\Cascade;
use app\src\NodeQ\tc_NodeQ as Node;

$email = _tc_email();

/**
 * Before route check.
 */
$app->before('GET', '/setting/', function() {
    if (!hasPermission('edit_settings')) {
        //redirect(get_base_url() . 'dashboard' . '/');
    }
});


$app->match('GET|POST', '/setting/', function () use($app) {

    if ($app->req->isPost()) {
        $options = [
            'system_name', 'system_email', 'mail_throttle',
            'cookieexpire', 'cookiepath'
        ];

        foreach ($options as $option_name) {
            if (!isset($_POST[$option_name]))
                continue;
            $value = $_POST[$option_name];
            update_option($option_name, $value);
        }
        // Update more options here
        $app->hook->{'do_action'}('update_options');
        /* Write to logs */
        tc_logger_activity_log_write('Update', 'Settings', 'General Settings', get_userdata('uname'));
        redirect($app->req->server['HTTP_REFERER']);
    }

    $app->view->display('setting/index', [
        'title' => 'General Settings'
        ]
    );
});

/**
 * Before route check.
 */
$app->before('GET', '/setting/smtp/', function() {
    if (!hasPermission('edit_settings')) {
        //redirect(get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/setting/smtp/', function () use($app) {
    $node = Node::table('php_encryption')->find(1);

    if ($app->req->isPost()) {
        update_option('tc_smtp_host', $app->req->_post('tc_smtp_host'));
        update_option('tc_smtp_username', $app->req->_post('tc_smtp_username'));
        update_option('tc_smtp_password', Crypto::encrypt($app->req->_post('tc_smtp_password'), Key::loadFromAsciiSafeString($node->key)));
        update_option('tc_smtp_port', $app->req->_post('tc_smtp_port'));
        update_option('tc_smtp_service', $app->req->_post('tc_smtp_service'));
        update_option('tc_smtp_smtpsecure', $app->req->_post('tc_smtp_smtpsecure'));
        update_option('tc_smtp_mailbox', $app->req->_post('tc_smtp_mailbox'));

        // Update more options here
        $app->hook->do_action('update_smtp_options');
        /* Write to logs */
        tc_logger_activity_log_write('Update', 'Settings', 'SMTP Settings', get_userdata('uname'));
        
        redirect($app->req->server['HTTP_REFERER']);
    }

    $app->view->display('setting/smtp', [
        'title' => 'SMTP Settings',
        'node' => $node
        ]
    );
});

/**
 * Before route check.
 */
$app->before('POST', '/setting/smtp/test/', function() {
    if (!hasPermission('edit_settings')) {
        //redirect(get_base_url() . 'dashboard' . '/');
    }
});

$app->post('/setting/smtp/test/', function () use($app, $email) {

    if ($app->req->isPost()) {
        try {
            $email->tc_mail($app->req->_post('to_email'), $app->req->_post('subject'), $app->req->_post('message'));
            //tc_logger_activity_log_write('Update', 'Settings', 'SMTP Settings', get_userdata('uname'));
        } catch (\phpmailerException $e) {
            Cascade::getLogger('error')->error(sprintf('EMAILSTATE[%s]: Email sending error: %s', $e->getCode(), $e->getMessage()));
        }
    }

    redirect(get_base_url() . 'setting' . '/smtp/');
});

/**
 * Before route checks to make sure the logged in user
 * us allowed to manage options/settings.
 */
$app->before('GET', '/email/', function() {
    if (!hasPermission('edit_settings')) {
        redirect(get_base_url() . 'dashboard' . '/');
    }

    /**
     * If user is logged in and the lockscreen cookie is set, 
     * redirect user to the lock screen until he/she enters 
     * his/her password to gain access.
     */
    if (isset($_COOKIE['SCREENLOCK'])) {
        redirect(get_base_url() . 'lock' . '/');
    }
});

$app->match('GET|POST', '/email/', function () use($app) {
    $css = [ 'css/admin/module.admin.page.form_elements.min.css'];
    $js = [
        'components/modules/admin/forms/elements/bootstrap-select/assets/lib/js/bootstrap-select.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-select/assets/custom/js/bootstrap-select.init.js?v=v2.1.0',
        'components/modules/admin/forms/elements/select2/assets/lib/js/select2.js?v=v2.1.0',
        'components/modules/admin/forms/elements/select2/assets/custom/js/select2.init.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-datepicker/assets/lib/js/bootstrap-datepicker.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-datepicker/assets/custom/js/bootstrap-datepicker.init.js?v=v2.1.0',
        'components/modules/admin/forms/editors/wysihtml5/assets/lib/js/wysihtml5-0.3.0_rc2.min.js?v=v2.1.0',
        'components/modules/admin/forms/editors/wysihtml5/assets/lib/js/bootstrap-wysihtml5-0.0.2.js?v=v2.1.0',
        'components/modules/admin/forms/editors/wysihtml5/assets/custom/wysihtml5.init.js?v=v2.1.0'
    ];

    if ($app->req->isPost()) {
        $options = [ 'system_email', 'contact_email', 'room_request_email', 'registrar_email_address', 'admissions_email'];

        foreach ($options as $option_name) {
            if (!isset($_POST[$option_name]))
                continue;
            $value = $_POST[$option_name];
            update_option($option_name, $value);
        }
        // Update more options here
        $app->hook->do_action('update_options');
        /* Write to logs */
        tc_logger_activity_log_write('Update', 'Settings', 'Email Settings', get_userdata('uname'));
    }

    $app->view->display('setting/email', [
        'title' => 'Email Settings',
        'cssArray' => $css,
        'jsArray' => $js
        ]
    );
});

/**
 * Before route checks to make sure the logged in user
 * us allowed to manage options/settings.
 */
$app->before('GET|POST', '/templates/', function() {
    if (!hasPermission('edit_settings')) {
        redirect(get_base_url() . 'dashboard' . '/');
    }

    /**
     * If user is logged in and the lockscreen cookie is set, 
     * redirect user to the lock screen until he/she enters 
     * his/her password to gain access.
     */
    if (isset($_COOKIE['SCREENLOCK'])) {
        redirect(get_base_url() . 'lock' . '/');
    }
});

$app->match('GET|POST', '/templates/', function () use($app) {
    $css = [ 'css/admin/module.admin.page.form_elements.min.css', 'css/admin/module.admin.page.tables.min.css'];
    $js = [
        'components/modules/admin/forms/elements/bootstrap-select/assets/lib/js/bootstrap-select.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-select/assets/custom/js/bootstrap-select.init.js?v=v2.1.0',
        'components/modules/admin/forms/elements/select2/assets/lib/js/select2.js?v=v2.1.0',
        'components/modules/admin/forms/elements/select2/assets/custom/js/select2.init.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-datepicker/assets/lib/js/bootstrap-datepicker.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-datepicker/assets/custom/js/bootstrap-datepicker.init.js?v=v2.1.0',
        'components/modules/admin/forms/editors/wysihtml5/assets/lib/js/wysihtml5-0.3.0_rc2.min.js?v=v2.1.0',
        'components/modules/admin/forms/editors/wysihtml5/assets/lib/js/bootstrap-wysihtml5-0.0.2.js?v=v2.1.0',
        'components/modules/admin/forms/editors/wysihtml5/assets/custom/wysihtml5.init.js?v=v2.1.0'
    ];

    if ($app->req->isPost()) {
        $options = [
            'coa_form_text', 'reset_password_text', 'room_request_text', 'room_booking_confirmation_text',
            'student_acceptance_letter', 'person_login_details', 'update_username'
        ];

        foreach ($options as $option_name) {
            if (!isset($_POST[$option_name]))
                continue;
            $value = $_POST[$option_name];
            update_option($option_name, $value);
        }
        // Update more options here
        $app->hook->do_action('update_options');
        /* Write to logs */
        tc_logger_activity_log_write('Update', 'Settings', 'Email Templates', get_userdata('uname'));
    }

    $app->view->display('setting/templates', [
        'title' => 'Email Templates',
        'cssArray' => $css,
        'jsArray' => $js
        ]
    );
});

$app->setError(function() use($app) {

    $app->view->display('error/404', ['title' => '404 Error']);
});
