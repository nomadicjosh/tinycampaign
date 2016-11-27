<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Dashboard Router
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
$app->before('GET|POST', '/dashboard(.*)', function () {
    if (!hasPermission('access_dashboard')) {
        //redirect(get_base_url());
    }
});

$flashNow = new \app\src\tc_Messages();

$app->get('/dashboard', function () use($app) {
    
    $app->view->display('dashboard/index', [
        'title' => 'Dashboard'
    ]);
});

$app->post('/dashboard/search/', function () use($app) {
    $acro = $_POST['screen'];
    $screen = explode(" ", $acro);

    if (get_screen($screen[0]) == '') {
        redirect(get_base_url() . 'err/screen-error?code=' . _h($screen[0]));
    } else {
        redirect(get_base_url() . get_screen($screen[0]) . '/');
    }
});

$app->get('/dashboard/support/', function () use($app) {
    $app->view->display('dashboard/support', [
        'title' => 'Online Support'
    ]);
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/dashboard/update/', function () {
    if (!hasPermission('edit_settings')) {
        redirect(get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/dashboard/update/', function () use($app) {
    $app->view->display('dashboard/update', [
        'title' => 'tinyCampaign Update'
    ]);
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/dashboard/core-update/', function () {
    if (!hasPermission('edit_settings')) {
        redirect(get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/dashboard/core-update/', function () use($app) {
    $app->view->display('dashboard/core-update', [
        'title' => 'tinyCampaign Update'
    ]);
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/dashboard/upgrade/', function () {
    if (!hasPermission('edit_settings')) {
        redirect(url('/dashboard/'));
    }
});

$app->match('GET|POST', '/dashboard/upgrade/', function () use($app) {
    $app->view->display('dashboard/upgrade', [
        'title' => 'Database Upgrade'
    ]);
});

/**
 * Before route check.
 */
$app->before('GET|POST','/dashboard/system-snapshot/', function () {
    if (!hasPermission('edit_settings')) {
        redirect(get_base_url() . 'dashboard' . '/');
    }
});

$app->get('/dashboard/system-snapshot/', function () use($app) {
    $app->view->display('dashboard/system-snapshot', [
        'title' => 'System Snapshot Report'
    ]);
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/dashboard/modules/', function () {
    if (!hasPermission('access_plugin_screen')) {
        redirect(get_base_url() . 'dashboard' . '/');
    }
});

$app->get('/dashboard/modules/', function () use($app) {
    $css = [
        'css/admin/module.admin.page.form_elements.min.css',
        'css/admin/module.admin.page.tables.min.css'
    ];
    $js = [
        'components/modules/admin/forms/elements/bootstrap-select/assets/lib/js/bootstrap-select.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-select/assets/custom/js/bootstrap-select.init.js?v=v2.1.0',
        'components/modules/admin/forms/elements/select2/assets/lib/js/select2.js?v=v2.1.0',
        'components/modules/admin/forms/elements/select2/assets/custom/js/select2.init.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-datepicker/assets/lib/js/bootstrap-datepicker.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-datepicker/assets/custom/js/bootstrap-datepicker.init.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-timepicker/assets/lib/js/bootstrap-timepicker.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-timepicker/assets/custom/js/bootstrap-timepicker.init.js?v=v2.1.0',
        'components/modules/admin/tables/datatables/assets/lib/js/jquery.dataTables.min.js?v=v2.1.0',
        'components/modules/admin/tables/datatables/assets/lib/extras/TableTools/media/js/TableTools.min.js?v=v2.1.0',
        'components/modules/admin/tables/datatables/assets/custom/js/DT_bootstrap.js?v=v2.1.0',
        'components/modules/admin/tables/datatables/assets/custom/js/datatables.init.js?v=v2.1.0',
        'components/modules/admin/forms/elements/jCombo/jquery.jCombo.min.js'
    ];

    $app->view->display('dashboard/modules', [
        'title' => 'System Modules',
        'cssArray' => $css,
        'jsArray' => $js
    ]);
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/dashboard/install-module/', function () {
    if (!hasPermission('access_plugin_admin_page')) {
        redirect(get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/dashboard/install-module/', function () use($app) {

    $css = [
        'css/admin/module.admin.page.form_elements.min.css'
    ];
    $js = [
        'components/modules/admin/forms/elements/bootstrap-select/assets/lib/js/bootstrap-select.js?v=v2.1.0',
        'components/modules/admin/forms/elements/bootstrap-select/assets/custom/js/bootstrap-select.init.js?v=v2.1.0',
        'components/modules/admin/forms/elements/select2/assets/lib/js/select2.js?v=v2.1.0',
        'components/modules/admin/forms/elements/select2/assets/custom/js/select2.init.js?v=v2.1.0',
        'components/modules/admin/forms/elements/jasny-fileupload/assets/js/bootstrap-fileupload.js?v=v2.1.0'
    ];

    if ($app->req->isPost()) {
        $name = explode(".", $_FILES["module_zip"]["name"]);
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
            $app->flash('error_message', _t('The file you are trying to upload is not the accepted file type. Please try again.'));
        }
        $target_path = BASE_PATH . $_FILES["module_zip"]["name"];
        if (move_uploaded_file($_FILES["module_zip"]["tmp_name"], $target_path)) {
            $zip = new \ZipArchive();
            $x = $zip->open($target_path);
            if ($x === true) {
                $zip->extractTo(BASE_PATH);
                $zip->close();
                unlink($target_path);
            }
            $app->flash('success_message', _t('The module was uploaded and installed properly.'));
        } else {
            $app->flash('error_message', _t('There was a problem uploading the module. Please try again or check the module package.'));
        }
        redirect($app->req->server['HTTP_REFERER']);
    }

    $app->view->display('dashboard/install-module', [
        'title' => 'Install Modules',
        'cssArray' => $css,
        'jsArray' => $js
    ]);
});

$app->get('/dashboard/flushCache/', function () use($app) {
    etsis_cache_flush();
    redirect($app->req->server['HTTP_REFERER']);
});

$app->match('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/dashboard/connector/', function () use($app) {
    error_reporting(0);
    $opts = [
        // 'debug' => true,
        'locale' => 'es_ES.UTF-8',
        'roots' => [
            /*[
                'driver' => 'LocalFileSystem',
                'path' => $app->config('cookies.savepath') . 'nodes' . DS . 'etsis' . DS,
                'alias' => 'Nodes',
                'mimeDetect' => 'auto',
                'accessControl' => 'access',
                'attributes' => [
                    [
                        'read' => true,
                        'write' => true,
                        'locked' => false
                    ],
                    [
                    	'pattern' => '/\.tmb/',
                    	'read' => false,
                    	'write' => false,
                    	'hidden' => true,
                    	'locked' => false
                    ],
                    [
                    	'pattern' => '/\.quarantine/',
                    	'read' => false,
                    	'write' => false,
                    	'hidden' => true,
                    	'locked' => false
                    ],
                    [
                    	'pattern' => '/\.DS_Store/',
                    	'read' => false,
                    	'write' => false,
                    	'hidden' => true,
                    	'locked' => false
                    ],
                    [
                    	'pattern' => '/\.json$/',
                    	'read' => true,
                    	'write' => true,
                    	'hidden' => false,
                    	'locked' => false
                    ]
                ],
                'uploadMaxSize' => '500M',
                'uploadAllow' => ['text/plain'],
                'uploadOrder' => ['allow','deny']
            ],*/
            [
                'driver' => 'LocalFileSystem',
                'path' => $app->config('cookies.savepath') . 'nodes',
                'alias' => 'Nodes',
                'mimeDetect' => 'auto',
                'accessControl' => 'access',
                'attributes' => [
                    [
                        'read' => true,
                        'write' => true,
                        'locked' => false
                    ],
                    [
                    	'pattern' => '/\.tmb/',
                    	'read' => false,
                    	'write' => false,
                    	'hidden' => true,
                    	'locked' => false
                    ],
                    [
                    	'pattern' => '/\.quarantine/',
                    	'read' => false,
                    	'write' => false,
                    	'hidden' => true,
                    	'locked' => false
                    ],
                    [
                    	'pattern' => '/\.DS_Store/',
                    	'read' => false,
                    	'write' => false,
                    	'hidden' => true,
                    	'locked' => false
                    ],
                    [
                    	'pattern' => '/\.json$/',
                    	'read' => true,
                    	'write' => true,
                    	'hidden' => false,
                    	'locked' => false
                    ]
                ],
                'uploadMaxSize' => '500M',
                'uploadAllow' => ['text/plain'],
                'uploadOrder' => ['allow','deny']
            ]
        ]
    ];
    // run elFinder
    $connector = new elFinderConnector(new elFinder($opts));
    $connector->run();
});
	
/**
 * Before route middleware check.
 */
$app->before('GET|POST', '/dashboard/ftp/', function() {
    if (!hasPermission('access_dashboard')) {
        redirect(get_base_url());
    }
});

$app->get('/dashboard/ftp/', function () use($app) {
	$css = [
	    'css/admin/module.admin.page.form_elements.min.css',
	    'css/admin/module.admin.page.tables.min.css',
	    'plugins/elfinder/css/elfinder.min.css',
	    'plugins/elfinder/css/theme.css'
	];
	
	$js = [
	    'components/modules/admin/forms/elements/bootstrap-select/assets/lib/js/bootstrap-select.js?v=v2.1.0',
	    'components/modules/admin/forms/elements/bootstrap-select/assets/custom/js/bootstrap-select.init.js?v=v2.1.0',
	    'components/modules/admin/forms/elements/select2/assets/lib/js/select2.js?v=v2.1.0',
	    'components/modules/admin/forms/elements/select2/assets/custom/js/select2.init.js?v=v2.1.0',
	    'components/modules/admin/forms/elements/bootstrap-datepicker/assets/lib/js/bootstrap-datepicker.js?v=v2.1.0',
	    'components/modules/admin/forms/elements/bootstrap-datepicker/assets/custom/js/bootstrap-datepicker.init.js?v=v2.1.0',
	    'components/modules/admin/forms/elements/bootstrap-timepicker/assets/lib/js/bootstrap-timepicker.js?v=v2.1.0',
	    'components/modules/admin/forms/elements/bootstrap-timepicker/assets/custom/js/bootstrap-timepicker.init.js?v=v2.1.0',
	    'components/modules/admin/tables/datatables/assets/lib/js/jquery.dataTables.min.js?v=v2.1.0',
	    'components/modules/admin/tables/datatables/assets/lib/extras/TableTools/media/js/TableTools.min.js?v=v2.1.0',
	    'components/modules/admin/tables/datatables/assets/custom/js/DT_bootstrap.js?v=v2.1.0',
	    'components/modules/admin/tables/datatables/assets/custom/js/datatables.init.js?v=v2.1.0'
	];

    $app->view->display('dashboard/ftp', [
        'title' => 'FTP',
        'cssArray' => $css,
        'jsArray' => $js
        ]
    );
});

$app->setError(function () use($app) {

    $app->view->display('error/404', [
        'title' => '404 Error'
    ]);
});
