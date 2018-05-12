<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Plugins Router
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
/**
 * Before route check.
 */
$app->before('GET|POST', '/plugins.*', function () {
    if (!hasPermission('manage_plugins')) {
        _tc_flash()->error(_t('Permission denied to view requested screen.'), get_base_url() . 'dashboard' . '/');
    }
});

$app->group('/plugins', function () use($app) {

    $app->get('/', function () use($app) {

        tc_register_style('datatables');
        tc_register_style('select2');
        tc_register_style('iCheck');
        tc_register_style('datetime');
        tc_register_script('select2');
        tc_register_script('moment.js');
        tc_register_script('datetime');
        tc_register_script('iCheck');
        tc_register_script('datatables');

        $app->view->display('plugins/index', [
            'title' => _t('Plugins')
        ]);
    });

    $app->before('GET|POST', '/activate/', function () {
        if (!hasPermission('activate_deactivate_plugins')) {
            _tc_flash()->error(_t('Permission denied to activate a plugin.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/activate/', function () use($app) {
        ob_start();

        $plugin_name = _trim(_filter_input_string(INPUT_GET, 'id'));

        /**
         * This function will validate a plugin and make sure
         * there are no errors before activating it.
         *
         * @since 2.0.0
         */
        tc_validate_plugin($plugin_name);

        if (ob_get_length() > 0) {
            $output = ob_get_clean();
            $error = new \TinyC\tc_Error('unexpected_output', _t('The plugin generated unexpected output.'), $output);
            $app->flash('error_message', $error);
        }
        ob_end_clean();

        redirect($app->req->server['HTTP_REFERER']);
    });

    $app->before('GET|POST', '/deactivate/', function () {
        if (!hasPermission('activate_deactivate_plugins')) {
            _tc_flash()->error(_t('Permission denied to deactivate a plugin.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/deactivate/', function () use($app) {
        $pluginName = _filter_input_string(INPUT_GET, 'id');
        /**
         * Fires before a specific plugin is deactivated.
         *
         * $pluginName refers to the plugin's
         * name (i.e. smtp.plugin.php).
         *
         * @since 2.0.0
         * @param string $pluginName
         *            The plugin's base name.
         */
        $app->hook->{'do_action'}('deactivate_plugin', $pluginName);

        /**
         * Fires as a specifig plugin is being deactivated.
         *
         * $pluginName refers to the plugin's
         * name (i.e. smtp.plugin.php).
         *
         * @since 2.0.0
         * @param string $pluginName
         *            The plugin's base name.
         */
        $app->hook->{'do_action'}('deactivate_' . $pluginName);

        deactivate_plugin($pluginName);

        /**
         * Fires after a specific plugin has been deactivated.
         *
         * $pluginName refers to the plugin's
         * name (i.e. smtp.plugin.php).
         *
         * @since 2.0.0
         * @param string $pluginName
         *            The plugin's base name.
         */
        $app->hook->{'do_action'}('deactivated_plugin', $pluginName);

        redirect($app->req->server['HTTP_REFERER']);
    });

    $app->match('GET|POST', '/options/', function () use($app) {
        
        tc_register_style('datatables');
        tc_register_style('select2');
        tc_register_style('iCheck');
        tc_register_style('datetime');
        tc_register_script('select2');
        tc_register_script('moment.js');
        tc_register_script('datetime');
        tc_register_script('iCheck');
        tc_register_script('datatables');

        $app->view->display('plugins/options', [
            'title' => _t('Plugin Options')
        ]);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/install/', function () {
        if (!hasPermission('install_plugins')) {
            _tc_flash()->error(_t('Permission denied to install plugins.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/install/', function () use($app) {

        if ($app->req->isPost()) {
            $name = explode(".", $_FILES["plugin_zip"]["name"]);
            $accepted_types = [
                'application/zip',
                'application/x-zip-compressed',
                'multipart/x-zip',
                'application/x-compressed'
            ];

            foreach ($accepted_types as $mime_type) {
                if ($mime_type == $type) {
                    $okay = true;
                    break;
                }
            }

            $continue = strtolower($name[1]) == 'zip' ? true : false;

            if (!$continue) {
                _tc_flash()->error(_t('The file you are trying to upload is not the accepted file type (.zip). Please try again.'));
            }
            $target_path = APP_PATH . 'plugins' . DS . $_FILES["plugin_zip"]["name"];
            if (move_uploaded_file($_FILES["plugin_zip"]["tmp_name"], $target_path)) {
                $zip = new \ZipArchive();
                $x = $zip->open($target_path);
                if ($x === true) {
                    $zip->extractTo(APP_PATH . 'plugins' . DS);
                    $zip->close();
                    unlink($target_path);
                }
                _tc_flash()->success(_t('Your plugin was uploaded and installed properly.'), $app->req->server['HTTP_REFERER']);
            } else {
                _tc_flash()->error(_t('There was a problem uploading your plugin. Please try again or check the plugin package.'), $app->req->server['HTTP_REFERER']);
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

        $app->view->display('plugins/install', [
            'title' => _t('Install Plugins')
        ]);
    });

    $app->setError(function () use($app) {

        $app->res->_format('json', 404);
    });
});
