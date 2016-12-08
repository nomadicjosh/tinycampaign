<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;

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
$app->before('GET|POST', '/permission.*', function() {
    if (!hasPermission('access_permission_screen')) {
        _tc_flash()->error(_t("You don't have permission to access the Permission screen."), get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/permission/', function () use($app) {


    $app->view->display('permission/index', [
        'title' => 'Manage Permissions'
        ]
    );
});

$app->match('GET|POST', '/permission/(\d+)/', function ($id) use($app, $json_url) {
    if ($app->req->isPost()) {
        try {
            $perm = $app->db->permission();
            foreach (_filter_input_array(INPUT_POST) as $k => $v) {
                $perm->$k = $v;
            }
            $perm->where('id = ?', $id);
            if ($perm->update()) {
                tc_logger_activity_log_write('Update Record', 'Permission', _filter_input_string(INPUT_POST, 'permName'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200));
            } else {
                _tc_flash()->error(_tc_flash()->notice(409));
            }
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    }
    
    try {
        $perm = $app->db->permission()->where('id = ?', $id)->findOne();
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }

    /**
     * If the database table doesn't exist, then it
     * is false and a 404 should be sent.
     */
    if ($perm == false) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If the query is legit, but there
     * is no data in the table, then 404
     * will be shown.
     */ elseif (empty($perm) == true) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If data is zero, 404 not found.
     */ elseif (count($perm->id) <= 0) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If we get to this point, the all is well
     * and it is ok to process the query and print
     * the results in a html format.
     */ else {

        $app->view->display('permission/view', [
            'title' => 'Edit Permission',
            'perm' => $perm
            ]
        );
    }
});

$app->match('GET|POST', '/permission/add/', function () use($app) {

    if ($app->req->isPost()) {
        try {
            $perm = $app->db->permission();
            foreach (_filter_input_array(INPUT_POST) as $k => $v) {
                $perm->$k = $v;
            }
            if ($perm->save()) {
                tc_logger_activity_log_write('New Record', 'Permission', _filter_input_string(INPUT_POST, 'permName'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'permission' . '/');
            } else {
                _tc_flash()->error(_tc_flash()->notice(409));
            }
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    }

    $app->view->display('permission/add', [
        'title' => 'Add New Permission'
        ]
    );
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/role.*', function() {
    if (!hasPermission('access_role_screen')) {
        _tc_flash()->error(_t("You don't have permission to access the Role screen."), get_base_url() . 'dashboard' . '/');
    }
});

$app->match('GET|POST', '/role/', function () use($app) {

    $app->view->display('role/index', [
        'title' => 'Manage Roles'
        ]
    );
});

$app->match('GET|POST', '/role/(\d+)/', function ($id) use($app, $json_url) {
    try {
        $role = $app->db->role()->where('id = ?', $id)->findOne();
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }

    /**
     * If the database table doesn't exist, then it
     * is false and a 404 should be sent.
     */
    if ($role == false) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If the query is legit, but there
     * is no data in the table, then 404
     * will be shown.
     */ elseif (empty($role) == true) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If data is zero, 404 not found.
     */ elseif (count($role->id) <= 0) {

        $app->view->display('error/404', ['title' => '404 Error']);
    }
    /**
     * If we get to this point, the all is well
     * and it is ok to process the query and print
     * the results in a html format.
     */ else {

        tc_register_style('iCheck');
        tc_register_style('iCheck_blue');
        tc_register_script('iCheck');

        $app->view->display('role/view', [
            'title' => 'Edit Role',
            'role' => $role
            ]
        );
    }
});

$app->match('GET|POST', '/role/add/', function () use($app) {

    if ($app->req->isPost()) {
        try {
            $roleID = $_POST['roleID'];
            $roleName = $_POST['roleName'];
            $rolePerm = maybe_serialize($_POST['permission']);

            $strSQL = $app->db->query(sprintf("REPLACE INTO `role` SET `id` = %u, `roleName` = '%s', `permission` = '%s'", $roleID, $roleName, $rolePerm));
            if ($strSQL) {
                $ID = $strSQL->lastInsertId();
                _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'role' . '/' . $ID . '/');
            } else {
                _tc_flash()->error(_tc_flash()->notice(409));
            }
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    }

    $app->view->display('role/add', [
        'title' => 'Add Role'
        ]
    );
});

$app->post('/role/editRole/', function () use($app) {
    try {
        $roleID = $_POST['roleID'];
        $roleName = $_POST['roleName'];
        $rolePerm = maybe_serialize($_POST['permission']);

        $strSQL = $app->db->query(sprintf("REPLACE INTO `role` SET `id` = %u, `roleName` = '%s', `permission` = '%s'", $roleID, $roleName, $rolePerm));
        if ($strSQL) {
            _tc_flash()->success(_tc_flash()->notice(200));
        } else {
            _tc_flash()->error(_tc_flash()->notice(409));
        }
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }

    redirect($app->req->server['HTTP_REFERER']);
});

$app->get('/switchUserTo/(\d+)/', function ($id) use($app) {

    if (isset($_COOKIE['TC_COOKIENAME'])) {
        $switch_cookie = [
            'key' => 'SWITCH_USERBACK',
            'personID' => get_userdata('personID'),
            'uname' => get_userdata('uname'),
            'remember' => (_h(get_option('cookieexpire')) - time() > 86400 ? _t('yes') : _t('no')),
            'exp' => _h(get_option('cookieexpire')) + time()
        ];
        $app->cookies->setSecureCookie($switch_cookie);
    }

    $vars = [];
    parse_str($app->cookies->get('TC_COOKIENAME'), $vars);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file = $app->config('cookies.savepath') . 'cookies.' . $vars['data'];
    try {
        if (tc_file_exists($file)) {
            unlink($file);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
    }

    /**
     * Delete the old cookie.
     */
    $app->cookies->remove("TC_COOKIENAME");

    $auth_cookie = [
        'key' => 'TC_COOKIENAME',
        'personID' => $id,
        'uname' => get_user_value($id, 'uname'),
        'remember' => (_h(get_option('cookieexpire')) - time() > 86400 ? _t('yes') : _t('no')),
        'exp' => _h(get_option('cookieexpire')) + time()
    ];

    $app->cookies->setSecureCookie($auth_cookie);

    redirect(get_base_url() . 'dashboard' . '/');
});

$app->get('/switchUserBack/(\d+)/', function ($id) use($app) {
    $vars1 = [];
    parse_str($app->cookies->get('TC_COOKIENAME'), $vars1);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file1 = $app->config('cookies.savepath') . 'cookies.' . $vars1['data'];
    try {
        if (tc_file_exists($file1)) {
            unlink($file1);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
    }

    $app->cookies->remove("TC_COOKIENAME");

    $vars2 = [];
    parse_str($app->cookies->get('SWITCH_USERBACK'), $vars2);
    /**
     * Checks to see if the cookie is exists on the server.
     * It it exists, we need to delete it.
     */
    $file2 = $app->config('cookies.savepath') . 'cookies.' . $vars2['data'];
    try {
        if (tc_file_exists($file2)) {
            unlink($file2);
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error(sprintf('FILESTATE[%s]: File not found: %s', $e->getCode(), $e->getMessage()));
    }

    $app->cookies->remove("SWITCH_USERBACK");

    /**
     * After the login as user cookies have been
     * removed from the server and the browser,
     * we need to set fresh cookies for the
     * original logged in user.
     */
    $switch_cookie = [
        'key' => 'TC_COOKIENAME',
        'personID' => $id,
        'uname' => get_user_value($id, 'uname'),
        'remember' => (_h(get_option('cookieexpire')) - time() > 86400 ? _t('yes') : _t('no')),
        'exp' => _h(get_option('cookieexpire')) + time()
    ];
    $app->cookies->setSecureCookie($switch_cookie);
    redirect(get_base_url() . 'dashboard' . '/');
});

$app->get('/logout/', function () {

    tc_logger_activity_log_write('Authentication', 'Logout', get_name(get_userdata('id')), get_userdata('uname'));
    /**
     * This function is documented in app/functions/auth-function.php.
     * 
     * @since 6.2.0
     */
    tc_clear_auth_cookie();

    redirect(get_base_url());
});

$app->setError(function () use($app) {

    $app->view->display('error/404', [
        'title' => '404 Error'
    ]);
});
