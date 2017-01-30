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
            $roleID = $app->req->post['roleID'];
            $roleName = $app->req->post['roleName'];
            $rolePerm = maybe_serialize($app->req->post['permission']);

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
        $roleID = $app->req->post['roleID'];
        $roleName = $app->req->post['roleName'];
        $rolePerm = maybe_serialize($app->req->post['permission']);

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

$app->match('GET|POST', '/confirm/(\w+)/lid/(\d+)/sid/(\d+)/', function ($code, $lid, $sid) use($app) {

    $list = get_list_by('id', $lid);

    try {
        $subscriber = $app->db->subscriber_list()
            ->select('subscriber_list.lid,subscriber_list.sid')
            ->select('subscriber_list.code,subscriber_list.confirmed,subscriber.email')
            ->_join('subscriber', 'subscriber_list.sid = subscriber.id')
            ->where('subscriber_list.lid = ?', $lid)->_and_()
            ->where('subscriber_list.sid = ?', $sid)->_and_()
            ->where('subscriber_list.code = ?', $code)->_and_()
            ->where('subscriber_list.confirmed = "0"')
            ->findOne();
        /**
         * Check if subscriber has already confirmed subscription.
         */
        if ($subscriber->confirmed == 1) {
            _tc_flash()->error(sprint(_t("Your subscription to <strong>%s</strong> has already been confirmed."), $list->name));
        }
        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */ elseif ($subscriber == false) {

            _tc_flash()->error(_tc_flash()->notice(404));
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($subscriber) == true) {

            _tc_flash()->error(_tc_flash()->notice(404));
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (count($subscriber->sid) <= 0) {

            _tc_flash()->error(_tc_flash()->notice(404));
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query.
         */ else {
            $sub = $app->db->subscriber_list();
            $sub->set([
                    'confirmed' => (int) 1
                ])
                ->where('lid = ?', $lid)->_and_()
                ->where('sid = ?', $sid)->_and_()
                ->where('code = ?', $code)
                ->update();
            subscribe_email_node($list->code, $subscriber);
            _tc_flash()->success(sprintf(_t("Your subscription to <strong>%s</strong> has been confirmed. Thank you."), $list->name));
        }
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }

    $app->view->display('index/status', [
        'title' => 'Email Confirmed'
        ]
    );
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/subscribe/', function() use($app) {
    if (!$app->req->server['HTTP_REFERER']) {
        $app->res->_format('json', 204);
    }

    if ($app->req->isPost()) {
        if ($app->req->post['m6qIHt4Z5evV'] != '' || !empty($app->req->post['m6qIHt4Z5evV'])) {
            _tc_flash()->error(_t('Spam is not allowed.'), get_base_url() . 'spam' . '/');
            exit();
        }

        if ($app->req->post['YgexGyklrgi1'] != '' || !empty($app->req->post['YgexGyklrgi1'])) {
            _tc_flash()->error(_t('Spam is not allowed.'), get_base_url() . 'spam' . '/');
            exit();
        }
    }
});

$app->post('/subscribe/', function () use($app) {

    $list = get_list_by('code', $app->req->post['code']);

    if ($app->req->isPost()) {
        try {
            $subscriber = $app->db->subscriber();
            $subscriber->insert([
                'fname' => $app->req->post['fname'],
                'lname' => $app->req->post['lname'],
                'email' => $app->req->post['email'],
                'code' => _random_lib()->generateString(50, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                'ip' => $app->req->server['REMOTE_ADDR'],
                'addedBy' => (int) 1,
                'addDate' => Jenssegers\Date\Date::now()
            ]);
            $sid = $subscriber->lastInsertId();

            $sub_list = $app->db->subscriber_list();
            $sub_list->insert([
                'lid' => $list->id,
                'sid' => $sid,
                'addDate' => Jenssegers\Date\Date::now(),
                'code' => _random_lib()->generateString(200, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                'confirmed' => ($list->optin == 1 ? 0 : 1)
            ]);

            $sub = $app->db->subscriber_list()
                ->where('lid = ?', $list->id)->_and_()
                ->where('sid = ?', $sid)->_and_()
                ->findOne();

            tc_logger_activity_log_write('New Record', 'Subscriber', $app->req->post['fname'] . ' ' . $app->req->post['lname'], get_user_value('1', 'uname'));
            check_custom_success_url($app->req->post['code'], $sub);
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage(), get_base_url() . 'status' . '/');
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), get_base_url() . 'status' . '/');
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), get_base_url() . 'status' . '/');
        }
    }
});

$app->get('/unsubscribe/(\w+)/lid/(\d+)/sid/(\d+)/', function ($code, $lid, $sid) use($app) {

    $list = get_list_by('id', $lid);

    try {
        $subscriber = $app->db->subscriber_list()
            ->select('subscriber_list.lid,subscriber_list.sid')
            ->select('subscriber_list.code,subscriber_list.confirmed,subscriber.email')
            ->_join('subscriber', 'subscriber_list.sid = subscriber.id')
            ->where('subscriber_list.lid = ?', $lid)->_and_()
            ->where('subscriber_list.sid = ?', $sid)->_and_()
            ->where('subscriber_list.code = ?', $code)->_and_()
            ->where('subscriber_list.unsubscribe = "0"')
            ->findOne();
        /**
         * Check if subscriber has already unsubscribed from list.
         */
        if ($subscriber->unsubscribe == 1) {
            _tc_flash()->error(sprint(_t("You have already been removed from the mailing list <strong>%s</strong>."), $list->name));
        }
        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */ elseif ($subscriber == false) {

            _tc_flash()->error(_tc_flash()->notice(404));
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($subscriber) == true) {

            _tc_flash()->error(_tc_flash()->notice(404));
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (count($subscriber->sid) <= 0) {

            _tc_flash()->error(_tc_flash()->notice(404));
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query.
         */ else {
            $sub = $app->db->subscriber_list();
            $sub->set([
                    'unsubscribe' => (int) 1
                ])
                ->where('lid = ?', $lid)->_and_()
                ->where('sid = ?', $sid)->_and_()
                ->where('code = ?', $code)
                ->update();
            subscribe_email_node($list->code, $subscriber);
            _tc_flash()->success(sprintf(_t("Unsubscribing to mailing list <strong>%s</strong> was successful."), $list->name));
        }
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage());
    }

    $app->view->display('index/status', [
        'title' => 'Unsubscribe Confirmed'
        ]
    );
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/status/', function() use($app) {
    if (!$app->req->server['HTTP_REFERER']) {
        $app->res->_format('json', 204);
    }
});

$app->get('/status/', function () use($app) {

    $app->view->display('index/status', [
        'title' => 'Status'
        ]
    );
});

$app->before('GET|POST', '/spam/', function() use($app) {
    if (!$app->req->server['HTTP_REFERER']) {
        $app->res->_format('json', 204);
    }
});

$app->get('/spam/', function () use($app) {

    $app->view->display('index/status', [
        'title' => 'No Spamming!'
        ]
    );
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

$app->match('GET|POST', '/preferences/', function () use($app) {

    $app->res->_format('json', 404);
});

$app->match('GET|POST', '/preferences/(\w+)/subscriber/(\d+)/', function ($code, $id) use($app) {

    if ($app->req->isPost()) {
        try {
            $subscriber = $app->db->subscriber();
            $subscriber->set([
                'fname' => $app->req->post['fname'],
                'lname' => $app->req->post['lname'],
                'email' => $app->req->post['email'],
                'address1' => $app->req->post['address1'],
                'address2' => $app->req->post['address2'],
                'city' => $app->req->post['city'],
                'state' => $app->req->post['state'],
                'zip' => $app->req->post['zip'],
                'country' => $app->req->post['country']
            ]);
            $subscriber->where('id = ?', $id)
                ->update();

            $data = [];
            $data['lid'] = $app->req->post['lid'];

            foreach ($app->req->post['id'] as $list) {
                $sub = $app->db->subscriber_list()
                    ->where('sid = ?', $id)->_and_()
                    ->where('lid = ?', $list)
                    ->findOne();

                if ($sub == false && $list == $data['lid'][$list]) {
                    $sub_list = $app->db->subscriber_list();
                    $sub_list->insert([
                        'lid' => $list,
                        'sid' => $id,
                        'addDate' => Jenssegers\Date\Date::now(),
                        'code' => _random_lib()->generateString(100, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                        'confirmed' => 1
                    ]);
                } else {
                    $sub_list = $app->db->subscriber_list();
                    $sub_list->set([
                            'lid' => $list,
                            'sid' => $id,
                            'unsubscribe' => ($list > $data['lid'][$list] ? (int) 1 : (int) 0)
                        ])
                        ->where('sid = ?', $id)->_and_()
                        ->where('lid = ?', $list)
                        ->update();
                }
            }

            tc_cache_delete($id, 'subscriber');
            _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    }

    try {
        $get_sub = $app->db->subscriber()
            ->where('code = ?', $code)->_and_()
            ->where('id = ?', $id)
            ->findOne();
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
    if ($get_sub == false) {

        $app->res->_format('json', 404);
    }
    /**
     * If the query is legit, but there
     * is no data in the table, then 404
     * will be shown.
     */ elseif (empty($get_sub) == true) {

        $app->res->_format('json', 404);
    }
    /**
     * If data is zero, 404 not found.
     */ elseif ($get_sub->id <= 0) {

        $app->res->_format('json', 404);
    }
    /**
     * If we get to this point, the all is well
     * and it is ok to process the query and print
     * the results in a html format.
     */ else {

        tc_register_style('select2');
        tc_register_style('iCheck');
        tc_register_script('select2');
        tc_register_script('iCheck');

        $app->view->display('index/preferences', [
            'title' => 'My Preferences',
            'subscriber' => $get_sub
            ]
        );
    }
});

$app->setError(function () use($app) {

    $app->view->display('error/404', [
        'title' => '404 Error'
    ]);
});
