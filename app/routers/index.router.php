<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use Respect\Validation\Validator as v;
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

        tc_register_style('select2');
        tc_register_style('iCheck');
        tc_register_script('select2');
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

    tc_register_style('select2');
    tc_register_style('iCheck');
    tc_register_script('select2');
    tc_register_script('iCheck');

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

/**
 * Before route check.
 */
$app->before('GET|POST', '/archive/', function() use($app) {
    header('Content-Type: application/json');
    $app->res->_format('json', 404);
    exit();
});

$app->before('GET|POST', '/archive/(\d+)/', function ($id) use($app) {
    try {
        $cpgn = $app->db->campaign()
            ->where('campaign.id = ?', $id)->_and_()
            ->where('campaign.archive = "1"')
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
    if ($cpgn == false) {
        header('Content-Type: application/json');
        $app->res->_format('json', 404);
        exit();
    }
    /**
     * If the query is legit, but there
     * is no data in the table, then 404
     * will be shown.
     */ elseif (empty($cpgn) == true) {
        header('Content-Type: application/json');
        $app->res->_format('json', 404);
        exit();
    }
    /**
     * If data is zero, 404 not found.
     */ elseif (count($cpgn->id) <= 0) {
        header('Content-Type: application/json');
        $app->res->_format('json', 404);
        exit();
    }
    /**
     * If we get to this point, the all is well
     * and it is ok to process the query and print
     * the results in a html format.
     */ else {

        $app->view->display('index/archive', [
            'title' => 'Archive',
            'cpgn' => $cpgn
            ]
        );
    }
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
        header('Content-Type: application/json');
        $app->res->_format('json', 204);
        exit();
    }

    if ($app->req->isPost()) {
        $app->hook->{'do_action'}('validation_check', $app->req->post);
    }
});

$app->post('/subscribe/', function () use($app) {

    /**
     * Check list code is valid.
     */
    $list = get_list_by('code', $app->req->post['code']);
    /**
     * Check if subscriber exists.
     */
    $sub = get_subscriber_by('email', $app->req->post['email']);
    if ($sub->id > 0) {
        _tc_flash()->error(_t('Your email is already in the system.'), get_base_url() . 'status' . '/');
        exit();
    }
    /**
     * Checks if email is valid.
     */
    if (!v::email()->validate($app->req->post['email'])) {
        _tc_flash()->error(_t('Invalid email address.'), get_base_url() . 'status' . '/');
        exit();
    }

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
            unsubscribe_email_node($list->code, $subscriber);
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

$app->get('/tracking/cid/(\d+)/sid/(\d+)/', function ($cid, $sid) use($app) {

    try {
        $tracking = $app->db->tracking()
            ->where('cid = ?', $cid)->_and_()
            ->where('sid = ?', $sid)
            ->count();

        if ($tracking <= 0) {
            $track = $app->db->tracking();
            $track->insert([
                'cid' => $cid,
                'sid' => $sid,
                'first_open' => \Jenssegers\Date\Date::now(),
                'viewed' => +1
            ]);

            $cpgn = $app->db->campaign();
            $cpgn->set([
                    'viewed' => +1
                ])
                ->where('id = ?', $cid)
                ->update();
        } else {
            $track = $app->db->tracking()
                ->where('cid = ?', $cid)->_and_()
                ->where('sid = ?', $sid)
                ->findOne();
            $track->set([
                    'viewed' => $track->viewed + 1
                ])
                ->update();

            $cpgn = $app->db->campaign()
                ->where('id = ?', $cid)
                ->findOne();
            $cpgn->set([
                    'viewed' => $cpgn->viewed + 1
                ])
                ->update();
        }
    } catch (NotFoundException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
    } catch (Exception $e) {
        Cascade::getLogger('error')->error($e->getMessage());
    } catch (ORMException $e) {
        Cascade::getLogger('error')->error($e->getMessage());
    }
});

/**
 * Before route check.
 */
$app->before('GET|POST', '/status/', function() use($app) {
    if (!$app->req->server['HTTP_REFERER']) {
        header('Content-Type: application/json');
        $app->res->_format('json', 204);
        exit();
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
        header('Content-Type: application/json');
        $app->res->_format('json', 204);
        exit();
    }
});

$app->get('/spam/', function () use($app) {

    $app->view->display('index/status', [
        'title' => 'No Spamming!'
        ]
    );
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

    header('Content-Type: application/json');
    $app->res->_format('json', 204);
    exit();
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

        header('Content-Type: application/json');
        $app->res->_format('json', 404);
        exit();
    }
    /**
     * If the query is legit, but there
     * is no data in the table, then 404
     * will be shown.
     */ elseif (empty($get_sub) == true) {

        header('Content-Type: application/json');
        $app->res->_format('json', 404);
        exit();
    }
    /**
     * If data is zero, 404 not found.
     */ elseif ($get_sub->id <= 0) {

        header('Content-Type: application/json');
        $app->res->_format('json', 404);
        exit();
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

$app->post('/reset-password/', function () use($app) {

    $user = get_user_by('email', $app->req->post['email']);

    if ($user->email == '') {
        _tc_flash()->error(_t('The email you entered does not exist.'), get_base_url());
    }

    try {
        $code = _random_lib()->generateString(100, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $pass = $app->db->user();
        $pass->set([
                'code' => $code,
            ])
            ->where('id = ?', $user->id)
            ->update();

        $domain = get_domain_name();
        $site = _h(get_option('system_name'));
        $link = get_base_url() . 'password' . '/' . $code . '/';

        $message = _file_get_contents(APP_PATH . 'views/setting/tpl/reset_password.tpl');
        $message = str_replace('{password_reset}', sprintf('<a href="%s" class="btn-primary" style="font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #348eda; margin: 0; border-color: #348eda; border-style: solid; border-width: 10px 20px;">' . _t('Reset Password') . '</a>', $link), $message);
        $message = str_replace('{system_name}', $site, $message);
        $headers = "From: $site <auto-reply@$domain>\r\n";
        if (_h(get_option('tc_smtp_status')) == 0) {
            $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE . "\r\n";
            $headers .= "MIME-Version: 1.0" . "\r\n";
        }

        try {
            _tc_email()->tc_mail($user->email, get_option('system_name') . ': ' . _t('Password Reset'), $message, $headers);
        } catch (phpmailerException $e) {
            _tc_flash()->error($e->getMessage(), get_base_url());
        }

        _tc_flash()->success(_t('Please check your email for instructions on changing your password.'), get_base_url());
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage(), get_base_url());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage(), get_base_url());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage(), get_base_url());
    }
});

$app->match('GET|POST', '/password/(\w+)/', function ($code) use($app) {

    try {
        $user = $app->db->user()
            ->where('code = ?', $code)
            ->findOne();
    } catch (NotFoundException $e) {
        _tc_flash()->error($e->getMessage(), get_base_url());
    } catch (Exception $e) {
        _tc_flash()->error($e->getMessage(), get_base_url());
    } catch (ORMException $e) {
        _tc_flash()->error($e->getMessage(), get_base_url());
    }

    if ($app->req->isPost()) {
        if ($app->req->post['password'] != $app->req->post['confirm']) {
            _tc_flash()->error(_t('Passwords did not match.'), $app->req->server['HTTP_REFERER']);
            exit();
        }

        $password = $app->req->post['password'];

        try {
            $pass = $app->db->user();
            $pass->set([
                    'code' => NULL,
                    'password' => tc_hash_password($password)
                ])
                ->where('id = ?', $user->id)
                ->update();

            $domain = get_domain_name();
            $site = _h(get_option('system_name'));

            $message = _file_get_contents(APP_PATH . 'views/setting/tpl/new_password.tpl');
            $message = str_replace('{password}', $password, $message);
            $message = str_replace('{system_name}', $site, $message);
            $headers = "From: $site <auto-reply@$domain>\r\n";
            if (_h(get_option('tc_smtp_status')) == 0) {
                $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE . "\r\n";
                $headers .= "MIME-Version: 1.0" . "\r\n";
            }

            try {
                _tc_email()->tc_mail($user->email, get_option('system_name') . ': ' . _t('New Password'), $message, $headers);
            } catch (phpmailerException $e) {
                _tc_flash()->error($e->getMessage(), get_base_url() . 'status' . '/');
            }

            _tc_flash()->success(_t('Your password was updated successfully.'), get_base_url() . 'status' . '/');
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        }
    }

    /**
     * If the database table doesn't exist, then it
     * is false and a 404 should be sent.
     */
    if ($user == false) {

        header('Content-Type: application/json');
        $app->res->_format('json', 404);
        exit();
    }
    /**
     * If the query is legit, but there
     * is no data in the table, then 404
     * will be shown.
     */ elseif (empty($user) == true) {

        header('Content-Type: application/json');
        $app->res->_format('json', 404);
        exit();
    }
    /**
     * If data is zero, 404 not found.
     */ elseif ($user->id <= 0) {

        header('Content-Type: application/json');
        $app->res->_format('json', 404);
        exit();
    }
    /**
     * If we get to this point, the all is well
     * and it is ok to process the query and print
     * the results in a html format.
     */ else {

        tc_register_style('select2');
        tc_register_script('select2');

        $app->view->display('index/password', [
            'title' => 'New Password',
            'user' => $user
            ]
        );
    }
});

$app->setError(function () use($app) {

    header('Content-Type: application/json');
    $app->res->_format('json', 204);
    exit();
});
