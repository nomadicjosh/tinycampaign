<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;
use Respect\Validation\Validator as v;
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;
use Cascade\Cascade;

/**
 * Before route check.
 */
$app->before('GET', '/user(.*)', function() {
    if (!hasPermission('manage_users')) {
        _tc_flash()->error(_t("You don't have permission to access the Manage Users screen."), get_base_url() . 'dashboard' . '/');
    }
});

$app->group('/user', function() use ($app) {

    $app->get('/', function () use($app) {
        try {
            $users = $app->db->user()
                    ->select('user.*, role.roleName,role.permission')
                    ->_join('role', 'user.roleID = role.id')
                    ->where('user.id <> "1"')
                    ->find();
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }

        tc_register_style('datatables');
        tc_register_style('select2');
        tc_register_script('select2');
        tc_register_script('datatables');

        $app->view->display('user/index', [
            'title' => _t('Manage Users'),
            'users' => $users
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/add/', function() {
        if (!hasPermission('create_user')) {
            _tc_flash()->error(_t("You don't have permission to access the Create User screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/add/', function () use($app) {

        if ($app->req->isPost()) {
            if (!v::email()->validate($app->req->post['email'])) {
                _tc_flash()->error(_t('Invalid email address.'));
                exit();
            }

            try {
                $pass = $app->req->post['password'];

                $user = $app->db->user();
                $user->uname = $app->req->post['uname'];
                $user->fname = $app->req->post['fname'];
                $user->lname = $app->req->post['lname'];
                $user->email = $app->req->post['email'];
                $user->address1 = $app->req->post['address1'];
                $user->address2 = $app->req->post['address2'];
                $user->city = $app->req->post['city'];
                $user->state = if_null($app->req->post['state']);
                $user->postal_code = $app->req->post['postal_code'];
                $user->country = if_null($app->req->post['country']);
                $user->password = tc_hash_password($pass);
                $user->status = $app->req->post['status'];
                $user->roleID = $app->req->post['roleID'];
                $user->date_added = \Jenssegers\Date\Date::now()->format('Y-m-d h:i:s');
                $user->save();
                $id = $user->lastInsertId();

                if ($app->req->post['new_user_email'] == '1') {
                    try {
                        _tc_email()->sendNewUserEmail($user, $pass);
                    } catch (phpmailerException $e) {
                        _tc_flash()->error($e->getMessage());
                    } catch (Exception $e) {
                        _tc_flash()->error($e->getMessage());
                    }
                }

                tc_logger_activity_log_write('New Record', 'User', get_name($id), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'user' . '/' . $id . '/');
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

        $app->view->display('user/add', [
            'title' => _t('Add New User')
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/', function() {
        if (!hasPermission('edit_user')) {
            _tc_flash()->error(_t("You don't have permission to access the Edit User screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/(\d+)/', function ($id) use($app) {

        if ($id == (int) '1') {
            _tc_flash()->error(_t('You are not allowed to edit the super administrator account.'), get_base_url() . 'user/');
            exit();
        }

        if ($app->req->isPost()) {
            /**
             * Fires before user record is updated.
             *
             * @since 2.0.3
             * @param int $id
             *            User's id.
             */
            $app->hook->{'do_action'}('pre_update_user', $id);

            try {
                $user = $app->db->user();
                $user->fname = $app->req->post['fname'];
                $user->lname = $app->req->post['lname'];
                $user->email = $app->req->post['email'];
                $user->address1 = $app->req->post['address1'];
                $user->address2 = $app->req->post['address2'];
                $user->city = $app->req->post['city'];
                $user->state = if_null($app->req->post['state']);
                $user->postal_code = $app->req->post['postal_code'];
                $user->country = if_null($app->req->post['country']);
                $user->status = $app->req->post['status'];
                $user->roleID = $app->req->post['roleID'];
                $user->LastUpdate = \Jenssegers\Date\Date::now()->format('Y-m-d h:i:s');
                /**
                 * Fires during the saving/updating of a user record.
                 *
                 * @since 2.0.3
                 * @param object $user
                 *            User data object.
                 */
                $app->hook->do_action('update_user_db_table', $user);
                $user->where('id = ?', $id)
                        ->update();

                /**
                 * Fires after user record has been updated.
                 *
                 * @since 2.0.3
                 * @param int $id
                 *            User's id.
                 */
                $app->hook->{'do_action'}('post_update_user', $id);

                tc_cache_delete($id, 'user');
                tc_logger_activity_log_write('Update Record', 'User', get_name($id), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (NotFoundException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            } catch (ORMException $e) {
                _tc_flash()->error($e->getMessage());
            }
        }

        $user = get_user($id);

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($user == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($user) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (_escape($user->id) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {
            tc_register_style('select2');
            tc_register_script('select2');

            $app->view->display('user/view', [
                'title' => _t('View/Edit User'),
                'user' => $user
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/profile/', function() {
        if (!is_user_logged_in()) {
            _tc_flash()->error(_t("You must be logged in to edit your profile."), get_base_url());
        }
    });

    $app->match('GET|POST', '/profile/', function () use($app) {

        if ($app->req->isPost()) {
            /**
             * Fires before user's profile is updated.
             *
             * @since 2.0.3
             * @param int $id
             *            User's id.
             */
            $app->hook->{'do_action'}('pre_update_profile', get_userdata('id'));

            try {
                $user = $app->db->user();
                $user->fname = $app->req->post['fname'];
                $user->lname = $app->req->post['lname'];
                $user->email = $app->req->post['email'];
                $user->address1 = $app->req->post['address1'];
                $user->address2 = $app->req->post['address2'];
                $user->city = $app->req->post['city'];
                $user->state = if_null($app->req->post['state']);
                $user->postal_code = $app->req->post['postal_code'];
                $user->country = if_null($app->req->post['country']);
                $user->LastUpdate = \Jenssegers\Date\Date::now()->format('Y-m-d h:i:s');
                $user->where('id = ?', get_userdata('id'))
                        ->update();

                if (isset($app->req->post['password'])) {
                    try {
                        $user = $app->db->user();
                        $user->password = tc_hash_password($app->req->post['password']);
                        $user->where('id = ?', get_userdata('id'))
                                ->update();
                    } catch (NotFoundException $e) {
                        _tc_flash()->error($e->getMessage());
                    } catch (Exception $e) {
                        _tc_flash()->error($e->getMessage());
                    } catch (ORMException $e) {
                        _tc_flash()->error($e->getMessage());
                    }
                }

                tc_cache_delete(get_userdata('id'), 'user');
                tc_logger_activity_log_write('Update Record', 'Profile', get_name(get_userdata('id')), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (NotFoundException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            } catch (ORMException $e) {
                _tc_flash()->error($e->getMessage());
            }
        }

        $user = get_user(get_userdata('id'));

        tc_register_style('select2');
        tc_register_script('select2');

        $app->view->display('user/profile', [
            'title' => _t('View/Edit Profile'),
            'user' => $user
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/perm/', function() {
        if (!hasPermission('edit_user')) {
            _tc_flash()->error(_t("You don't have permission to access the Edit User Permission screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/(\d+)/perm/', function ($id) use($app) {

        $user = get_user_by('id', $id);

        if ($app->req->isPost()) {
            try {
                if (count($app->req->post['permission']) > 0) {
                    $q = $app->db->query(sprintf("REPLACE INTO user_perms SET userID = %u, permission = '%s'", $id, maybe_serialize($app->req->post['permission'])));
                } else {
                    $q = $app->db->query(sprintf("DELETE FROM user_perms WHERE userID = %u", $id));
                }
                if ($q) {
                    _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'user/' . $id . '/perm/');
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

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($user == false) {

            $app->view->display('error/404', [
                'title' => '404 Error'
            ]);
        } /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($user) == true) {

            $app->view->display('error/404', [
                'title' => '404 Error'
            ]);
        } /**
         * If data is zero, 404 not found.
         */ elseif (_escape($user->id) <= 0) {

            $app->view->display('error/404', [
                'title' => '404 Error'
            ]);
        } /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            tc_register_style('select2');
            tc_register_style('iCheck');
            tc_register_script('select2');
            tc_register_script('iCheck');

            $app->view->display('user/perm', [
                'title' => get_name($id) . ' Permissions',
                'user' => $user
            ]);
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/(\d+)/switch-to/', function() use($app) {
        if (!hasPermission('switch_user')) {
            _tc_flash()->error(_t("You don't have permission to switch users."), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/(\d+)/switch-to/', function ($id) use($app) {

        if (isset($_COOKIE['TC_COOKIENAME'])) {
            $switch_cookie = [
                'key' => 'SWITCH_USERBACK',
                'id' => get_userdata('id'),
                'uname' => get_userdata('uname'),
                'remember' => (_escape(get_option('cookieexpire')) - time() > 86400 ? _t('yes') : _t('no')),
                'exp' => _escape(get_option('cookieexpire')) + time()
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
            'id' => $id,
            'uname' => get_user_value($id, 'uname'),
            'remember' => (_escape(get_option('cookieexpire')) - time() > 86400 ? _t('yes') : _t('no')),
            'exp' => _escape(get_option('cookieexpire')) + time()
        ];

        $app->cookies->setSecureCookie($auth_cookie);

        redirect(get_base_url() . 'dashboard' . '/');
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/(\d+)/switch-back/', function() use($app) {
        if (!hasPermission('switch_user')) {
            _tc_flash()->error(_t("You don't have permission to switch users."), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/(\d+)/switch-back/', function ($id) use($app) {
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
            'id' => $id,
            'uname' => get_user_value($id, 'uname'),
            'remember' => (_escape(get_option('cookieexpire')) - time() > 86400 ? _t('yes') : _t('no')),
            'exp' => _escape(get_option('cookieexpire')) + time()
        ];
        $app->cookies->setSecureCookie($switch_cookie);
        redirect(get_base_url() . 'dashboard' . '/');
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/(\d+)/d/', function() use($app) {
        if (!hasPermission('delete_user')) {
            _tc_flash()->error(_t("You don't have permission to delete users."), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/(\d+)/d/', function ($id) use($app) {
        if ($id == (int) '1') {
            _tc_flash()->error(_t('You are not allowed to delete the super administrator account.'), get_base_url() . 'user/');
            exit();
        }

        try {
            $cpgns = $app->db->campaign()
                    ->where('owner = ?', $id)
                    ->find();
            foreach ($cpgns as $cpgn) {
                try {
                    Node::table('campaign_queue')
                            ->where('cid', '=', _escape($cpgn->id))
                            ->delete();
                } catch (NodeQException $e) {
                    _tc_flash()->error($e->getMessage());
                } catch (Exception $e) {
                    _tc_flash()->error($e->getMessage());
                }
            }

            $app->db->user()
                    ->where('id = ?', $id)->_and_()
                    ->where('id <> ?', get_userdata('id'))
                    ->reset()
                    ->findOne($id)
                    ->delete();

            tc_cache_delete($id, 'user');
            tc_cache_flush_namespace('list');
            tc_cache_flush_namespace('subscriber');
            _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        }
    });
});

$app->setError(function() use($app) {

    $app->view->display('error/404', ['title' => '404 Error']);
});
