<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use Respect\Validation\Validator as v;
use app\src\Exception\Exception;
use PDOException as ORMException;

/**
 * Before route check.
 */
$app->before('GET', '/subscriber(.*)', function() {
    if (!hasPermission('manage_subscribers')) {
        _tc_flash()->error(_t("You don't have permission to access the Manage Subscribers screen."), get_base_url() . 'dashboard' . '/');
    }
});

$app->group('/subscriber', function() use ($app) {

    $app->get('/', function () use($app) {
        try {
            $subscribers = $app->db->subscriber()
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

        $app->view->display('subscriber/index', [
            'title' => 'Manage Subscribers',
            'subscribers' => $subscribers
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/add/', function() {
        if (!hasPermission('add_subscriber')) {
            _tc_flash()->error(_t("You don't have permission to access the Add Subscriber screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/add/', function () use($app) {

        if ($app->req->isPost()) {
            if (!v::email()->validate($app->req->post['email'])) {
                _tc_flash()->error(_t('Invalid email address.'));
                exit();
            }

            try {
                $subscriber = $app->db->subscriber();
                $subscriber->insert([
                    'fname' => $app->req->post['fname'],
                    'lname' => $app->req->post['lname'],
                    'email' => $app->req->post['email'],
                    'address1' => $app->req->post['address1'],
                    'address2' => $app->req->post['address2'],
                    'city' => $app->req->post['city'],
                    'state' => $app->req->post['state'],
                    'postal_code' => $app->req->post['postal_code'],
                    'country' => $app->req->post['country'],
                    'code' => _random_lib()->generateString(50, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                    'ip' => $app->req->server['REMOTE_ADDR'],
                    'addedBy' => get_userdata('id'),
                    'addDate' => Jenssegers\Date\Date::now()
                ]);
                $id = $subscriber->lastInsertId();

                foreach ($app->req->post['lid'] as $list) {
                    $sub_list = $app->db->subscriber_list();
                    $sub_list->insert([
                        'lid' => $list,
                        'sid' => $id,
                        'addDate' => Jenssegers\Date\Date::now(),
                        'code' => _random_lib()->generateString(200, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                        'confirmed' => 1
                    ]);
                }

                tc_logger_activity_log_write('New Record', 'Subscriber', $app->req->post['fname'] . ' ' . $app->req->post['lname'], get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'subscriber' . '/' . $id . '/');
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

        $app->view->display('subscriber/add', [
            'title' => 'Add New Subscriber'
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/', function() {
        if (!hasPermission('edit_subscriber')) {
            _tc_flash()->error(_t("You don't have permission to access the Edit Subscriber screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/(\d+)/', function ($id) use($app) {

        $sub = get_subscriber_by('id', $id);

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
                    'postal_code' => $app->req->post['postal_code'],
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
                                'unsubscribed' => ($list > $data['lid'][$list] ? (int) 1 : (int) 0)
                            ])
                            ->where('sid = ?', $id)->_and_()
                            ->where('lid = ?', $list)
                            ->update();
                    }
                }

                tc_cache_delete($id, 'subscriber');
                tc_cache_delete($id, 'slist');
                tc_logger_activity_log_write('Update Record', 'Subscriber', $sub->fname . ' ' . $sub->lname, get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
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
        if ($sub == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($sub) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif ($sub->id <= 0) {

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

            $app->view->display('subscriber/view', [
                'title' => 'View/Edit Subscriber',
                'subscriber' => $sub
                ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/(\d+)/d/', function() use($app) {
        if (!hasPermission('delete_subscriber')) {
            _tc_flash()->error(_t("You don't have permission to delete subscribers."), $app->req->server['HTTP_REFERER']);
            exit();
        }
    });

    $app->get('/(\d+)/d/', function ($id) use($app) {
        try {
            $app->db->subscriber()
                ->where('addedBy = ?', get_userdata('id'))->_and_()
                ->where('id = ?', $id)
                ->reset()
                ->findOne($id)
                ->delete();

            tc_cache_delete($id, 'subscriber');
            tc_cache_delete($id, 'slist');
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
