<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;

$email = _tc_email();

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

        tc_register_style('select2');
        tc_register_script('select2');

        $app->view->display('user/index', [
            'title' => 'Manage Users',
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
            try {
                $pass = $app->req->_post('password');

                $user = $app->db->user();
                $user->uname = $app->req->_post('uname');
                $user->fname = $app->req->_post('fname');
                $user->lname = $app->req->_post('lname');
                $user->email = $app->req->_post('email');
                $user->address1 = $app->req->_post('address1');
                $user->address2 = $app->req->_post('address2');
                $user->city = $app->req->_post('city');
                $user->state = $app->req->_post('state');
                $user->postal_code = $app->req->_post('postal_code');
                $user->country = $app->req->_post('country');
                $user->password = tc_hash_password($pass);
                $user->status = $app->req->_post('status');
                $user->roleID = $app->req->_post('roleID');
                $user->date_added = \Jenssegers\Date\Date::now()->format('Y-m-d h:i:s');
                $user->save();
                $id = $user->lastInsertId();

                if ($_POST['new_user_email'] == '1') {
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
            'title' => 'Add New User'
            ]
        );
    });

    $app->match('GET|POST', '/(\d+)/', function ($id) use($app) {

        if ($id == (int)'1') {
            _tc_flash()->error(_t('You are not allowed to edit the super administrator account.'), get_base_url() . 'user/');
            return;
        }

        if ($app->req->isPost()) {
            try {
                $user = $app->db->user();
                $user->fname = $app->req->_post('fname');
                $user->lname = $app->req->_post('lname');
                $user->email = $app->req->_post('email');
                $user->address1 = $app->req->_post('address1');
                $user->address2 = $app->req->_post('address2');
                $user->city = $app->req->_post('city');
                $user->state = $app->req->_post('state');
                $user->postal_code = $app->req->_post('postal_code');
                $user->country = $app->req->_post('country');
                $user->status = $app->req->_post('status');
                $user->roleID = $app->req->_post('roleID');
                $user->LastUpdate = \Jenssegers\Date\Date::now()->format('Y-m-d h:i:s');
                $user->where('id = ?', $id)
                    ->update();

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

        $user = get_user_by('id', $id);

        tc_register_style('select2');
        tc_register_script('select2');

        $app->view->display('user/view', [
            'title' => 'View/Edit User',
            'user' => $user
            ]
        );
    });

    $app->match('GET|POST', '/profile/', function () use($app) {

        if ($app->req->isPost()) {
            try {
                $user = $app->db->user();
                $user->fname = $app->req->_post('fname');
                $user->lname = $app->req->_post('lname');
                $user->email = $app->req->_post('email');
                $user->address1 = $app->req->_post('address1');
                $user->address2 = $app->req->_post('address2');
                $user->city = $app->req->_post('city');
                $user->state = $app->req->_post('state');
                $user->postal_code = $app->req->_post('postal_code');
                $user->country = $app->req->_post('country');
                $user->LastUpdate = \Jenssegers\Date\Date::now()->format('Y-m-d h:i:s');
                $user->where('id = ?', get_userdata('id'))
                    ->update();

                if (isset($_POST['password'])) {
                    try {
                        $user = $app->db->user();
                        $user->password = tc_hash_password($app->req->_post('password'));
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

        $user = get_user_by('id', get_userdata('id'));

        tc_register_style('select2');
        tc_register_script('select2');

        $app->view->display('user/profile', [
            'title' => 'View/Edit Profile',
            'user' => $user
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/(\d+)/d/', function() {
        if (!hasPermission('delete_user')) {
            redirect(get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/(\d+)/d/', function ($id) use($app) {
        try {
            $list = $app->db->subscriber()
                ->where('owner = ?', get_userdata('id'))->_and_()
                ->where('id = ?', $id);

            if ($list->delete()) {
                tc_cache_delete($id, 'list');
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
});

$app->setError(function() use($app) {

    $app->view->display('error/404', ['title' => '404 Error']);
});
