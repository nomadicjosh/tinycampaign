<?php namespace app\src;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

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

    public function showMessage()
    {
        // get the message (they are arrays, to make multiple positive/negative messages possible)
        $success_message[] = $_COOKIE['success_message'];
        $error_message[] = $_COOKIE['error_message'];
        $plugin_success_message[] = $_COOKIE['plugin_success_message'];
        $plugin_error_message[] = $_COOKIE['plugin_error_message'];
        $pnotify[] = $_COOKIE['pnotify'];

        // echo out positive messages
        if (isset($_COOKIE['success_message'])) {
            foreach ($success_message as $message) {
                $this->app->cookies->remove('success_message');
                return '<section class="flash_message success-panel"><div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . $message . '</div></section>';
            }
        }

        // echo out negative messages
        if (isset($_COOKIE['error_message'])) {
            foreach ($error_message as $message) {
                $this->app->cookies->remove('error_message');
                return '<section class="flash_message error-panel"><div class="alert alert-danger alert-dismissible center"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . $message . '</div></section>';
            }
        }

        // echo out positive plugin messages
        if (isset($_COOKIE['plugin_success_message'])) {
            foreach ($plugin_success_message as $message) {
                $this->app->cookies->remove('plugin_success_message');
                return '<section class="flash_message success-panel"><div class="alert alert-success alert-dismissible center"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . $message . '</div></section>';
            }
        }

        // echo out negative plugin messages
        if (isset($_COOKIE['plugin_error_message'])) {
            foreach ($plugin_error_message as $message) {
                $this->app->cookies->remove('plugin_error_message');
                return '<section class="flash_message error-panel"><div class="alert alert-danger alert-dismissible center"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . sprintf(_t('Plugin could not be activated because it triggered a <strong>fatal error</strong>. <br /><br /> %s</div></section>'), $message);
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
