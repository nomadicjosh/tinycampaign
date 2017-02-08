<?php 
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

class View
{
    /* var $app Liten */

    public $app;

    public function __construct()
    {
        /**
         * Instantiate a new object.
         */
        $this->app = new \Liten\Liten(
            [
            'view_dir' => APP_PATH . 'plugins' . DS
            ]
        );
        /**
         * Automatically load the layout view to be used
         * for the username changer plugin.
         */
        $this->app->view->extend('../views/_layouts/dashboard');
    }

    public function display($viewName, $data = null)
    {
        $this->app->view->display($viewName, $data);
    }
}
