<?php namespace app\src;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use duncan3dc\Sessions\Session;

/**
 * tinyCampaign Flash Messages Library
 *  
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class tc_Messages
{

    public $app;

    public function __construct()
    {
        $this->app = \Liten\Liten::getInstance();
    }

    public function init($name, $value)
    {
        /** Set the session values */
        $this->app->cookies->set($name, $value);
    }

    public function setMessage($key, $value, $name)
    {
        Session::name($name);
        Session::setFlash($key, $value);
    }

    public function showMessage()
    {
        $plugin_success_message[] = $_COOKIE['plugin_success_message'];
        $plugin_error_message[] = $_COOKIE['plugin_error_message'];
        $pnotify[] = $_COOKIE['pnotify'];

        // echo out positive messages
        if (isset($_SESSION['_fs_success']) && $_SESSION['_fs_success'] != 'N') {
            Session::name('system_success');
            return '<section class="flash_message"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . Session::getFlash('success') . '</div></section>';
        }

        // echo out negative messages
        if (isset($_SESSION['_fs_error']) && $_SESSION['_fs_error'] != 'N') {
            Session::name('system_error');
            return '<section class="flash_message"><div class="alert alert-danger alert-dismissible center"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . Session::getFlash('error') . '</div></section>';
        }

        // echo out positive plugin messages
        if (isset($_COOKIE['plugin_success_message'])) {
            foreach ($plugin_success_message as $message) {
                $this->app->cookies->remove('plugin_success_message');
                return '<section class="flash_message"><div class="alert alert-success alert-dismissible center"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . $message . '</div></section>';
            }
        }

        // echo out negative plugin messages
        if (isset($_COOKIE['plugin_error_message'])) {
            foreach ($plugin_error_message as $message) {
                $this->app->cookies->remove('plugin_error_message');
                return '<section class="flash_message"><div class="alert alert-danger alert-dismissible center"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . sprintf(_t('Plugin could not be activated because it triggered a <strong>fatal error</strong>. <br /><br /> %s</div></section>'), $message);
            }
        }

        // return a browser notification if set
        if (isset($_COOKIE['pnotify'])) {
            foreach ($pnotify as $message) {
                $this->app->cookies->remove('pnotify');
                return $message;
            }
        }
    }

    public function notice($num)
    {
        $msg[200] = _t('200 - Success: Ok');
        $msg[201] = _t('201 - Success: Created');
        $msg[204] = _t('204 - Error: No Content');
        $msg[409] = _t('409 - Error: Conflict');
        return $msg[$num];
    }
}
