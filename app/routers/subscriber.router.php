<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TinyC\Exception\NotFoundException;
use Respect\Validation\Validator as v;
use TinyC\Exception\Exception;
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

        $where = [];

        if ($app->req->get['lookup'] == 'true') {

            try {
                if ($app->req->get['spammer'] == '1' || $app->req->get['blacklisted'] == '1' || $app->req->get['unconfirmed'] == '0' || $app->req->get['email'] != '' || $app->req->get['confirmed'] == '1' || $app->req->get['list'] > 0) {
                    $w = "WHERE subscriber.allowed <> '' AND";
                } else {
                    $w = "WHERE subscriber.allowed <> ''";
                }

                if ($app->req->get['spammer'] == '1') {
                    $where[] = "subscriber.spammer = '1'";
                }
                
                if ($app->req->get['email'] != '') {
                    $email = $app->req->get['email'];
                    $where[] = "subscriber.email = '$email'";
                }

                if ($app->req->get['blacklisted'] == '1') {
                    $where[] = "subscriber.bounces >= '3'";
                }
                
                if ($app->req->get['confirmed'] == '1') {
                    $where[] = "subscriber_list.confirmed = '1'";
                }

                if ($app->req->get['unconfirmed'] == '0') {
                    $where[] = "subscriber_list.confirmed = '0'";
                }
                
                if ($app->req->get['list'] > 0) {
                    $list = $app->req->get['list'];
                    $where[] = "list.id = '$list'";
                }

                $final_where = '';

                if (count($where) > 0) {
                    $final_where = implode(' AND ', $where);
                }

                $subscribers = $app->db->query(
                        "SELECT subscriber.id, subscriber.fname, subscriber.lname, subscriber.email, subscriber.allowed, subscriber.addDate "
                        . "FROM subscriber "
                        . "LEFT JOIN subscriber_list ON subscriber.id = subscriber_list.sid "
                        . "LEFT JOIN list ON subscriber_list.lid = list.id $w $final_where"
                );
                $subs = $subscribers->find(function ($data) {
                    $array = [];
                    foreach ($data as $d) {
                        $array[] = $d;
                    }
                    return $array;
                });
            } catch (ORMException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            }
        }
        
        tc_register_style('datatables');
        tc_register_style('select2');
        tc_register_style('iCheck');
        tc_register_script('select2');
        tc_register_script('datatables');
        tc_register_script('iCheck');

        $app->view->display('subscriber/index', [
            'title' => _t('Lookup Subscribers'),
            'subscribers' => ($subs != '' ? array_to_object($subs) : '')
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

        \TinyC\tc_StopForumSpam::$spamTolerance = _escape(get_option('spam_tolerance'));

        if ($app->req->isPost()) {
            if (!v::email()->validate($app->req->post['email'])) {
                _tc_flash()->error(_t('Invalid email address.'), $app->req->server['HTTP_REFERER']);
                exit();
            }

            if (\TinyC\tc_StopForumSpam::isSpamBotByEmail($app->req->post['email'])) {
                try {
                    $subscriber = $app->db->subscriber();
                    $subscriber->insert([
                        'fname' => $app->req->post['fname'],
                        'lname' => $app->req->post['lname'],
                        'email' => $app->req->post['email'],
                        'address1' => $app->req->post['address1'],
                        'address2' => $app->req->post['address2'],
                        'city' => $app->req->post['city'],
                        'state' => if_null($app->req->post['state']),
                        'postal_code' => $app->req->post['postal_code'],
                        'country' => if_null($app->req->post['country']),
                        'code' => _random_lib()->generateString(50, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                        'ip' => $app->req->server['REMOTE_ADDR'],
                        'spammer' => (int) 1,
                        'tags' => if_null($app->req->post['tags']),
                        'addedBy' => get_userdata('id'),
                        'addDate' => Jenssegers\Date\Date::now()
                    ]);
                    $id = $subscriber->lastInsertId();

                    foreach ($app->req->post['lid'] as $list) {
                        $sub_list = $app->db->subscriber_list();
                        $sub_list->insert([
                            'lid' => $list,
                            'sid' => $id,
                            'method' => 'add',
                            'addDate' => Jenssegers\Date\Date::now(),
                            'code' => _random_lib()->generateString(200, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                            'confirmed' => 1
                        ]);
                    }

                    tc_cache_delete('my_subscribers_' . get_userdata('id'));
                    tc_cache_flush_namespace('list_subscribers');
                    tc_logger_activity_log_write('New Record', 'Subscriber', $app->req->post['fname'] . ' ' . $app->req->post['lname'], get_userdata('uname'));
                    _tc_flash()->warning(_t('Subscriber was added to the list but flagged as spam.'), get_base_url() . 'subscriber' . '/' . $id . '/');
                } catch (NotFoundException $e) {
                    _tc_flash()->error($e->getMessage());
                } catch (Exception $e) {
                    _tc_flash()->error($e->getMessage());
                } catch (ORMException $e) {
                    _tc_flash()->error($e->getMessage());
                }
            } else {
                try {
                    $subscriber = $app->db->subscriber();
                    $subscriber->insert([
                        'fname' => $app->req->post['fname'],
                        'lname' => $app->req->post['lname'],
                        'email' => $app->req->post['email'],
                        'address1' => $app->req->post['address1'],
                        'address2' => $app->req->post['address2'],
                        'city' => $app->req->post['city'],
                        'state' => if_null($app->req->post['state']),
                        'postal_code' => $app->req->post['postal_code'],
                        'country' => if_null($app->req->post['country']),
                        'code' => _random_lib()->generateString(50, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                        'ip' => $app->req->server['REMOTE_ADDR'],
                        'spammer' => (int) 0,
                        'tags' => if_null($app->req->post['tags']),
                        'addedBy' => get_userdata('id'),
                        'addDate' => Jenssegers\Date\Date::now()
                    ]);
                    $id = $subscriber->lastInsertId();

                    foreach ($app->req->post['lid'] as $list) {
                        $sub_list = $app->db->subscriber_list();
                        $sub_list->insert([
                            'lid' => $list,
                            'sid' => $id,
                            'method' => 'add',
                            'addDate' => Jenssegers\Date\Date::now(),
                            'code' => _random_lib()->generateString(200, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                            'confirmed' => 1
                        ]);
                    }

                    tc_cache_delete('my_subscribers_' . get_userdata('id'));
                    tc_cache_flush_namespace('list_subscribers');
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
        }

        tc_register_style('select2');
        tc_register_style('iCheck');
        tc_register_style('selectize');
        tc_register_script('select2');
        tc_register_script('iCheck');

        $app->view->display('subscriber/add', [
            'title' => _t('Add New Subscriber')
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
        \TinyC\tc_StopForumSpam::$spamTolerance = _escape(get_option('spam_tolerance'));

        if ($app->req->isPost()) {
            if (!v::email()->validate($app->req->post['email'])) {
                _tc_flash()->error(_t('Invalid email address.'), $app->req->server['HTTP_REFERER']);
                exit();
            }

            if (\TinyC\tc_StopForumSpam::isSpamBotByEmail($app->req->post['email'])) {
                try {
                    $subscriber = $app->db->subscriber();
                    $subscriber->set([
                        'fname' => $app->req->post['fname'],
                        'lname' => $app->req->post['lname'],
                        'email' => $app->req->post['email'],
                        'address1' => $app->req->post['address1'],
                        'address2' => $app->req->post['address2'],
                        'city' => $app->req->post['city'],
                        'state' => if_null($app->req->post['state']),
                        'postal_code' => $app->req->post['postal_code'],
                        'country' => if_null($app->req->post['country']),
                        'spammer' => (int) 1,
                        'exception' => $app->req->post['exception'],
                        'tags' => if_null($app->req->post['tags'])
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
                                'method' => 'add',
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

                    tc_cache_delete('my_subscribers_' . get_userdata('id'));
                    tc_cache_delete($id, 'subscriber');
                    tc_cache_delete($id, 'slist');
                    tc_cache_flush_namespace('list_subscribers');
                    tc_logger_activity_log_write('Update Record', 'Subscriber', get_sub_name($id), get_userdata('uname'));
                    _tc_flash()->warning(_t('Subscriber was updated but was flagged as spam.'), $app->req->server['HTTP_REFERER']);
                } catch (NotFoundException $e) {
                    _tc_flash()->error($e->getMessage());
                } catch (Exception $e) {
                    _tc_flash()->error($e->getMessage());
                } catch (ORMException $e) {
                    _tc_flash()->error($e->getMessage());
                }
            } else {
                try {
                    $subscriber = $app->db->subscriber();
                    $subscriber->set([
                        'fname' => $app->req->post['fname'],
                        'lname' => $app->req->post['lname'],
                        'email' => $app->req->post['email'],
                        'address1' => $app->req->post['address1'],
                        'address2' => $app->req->post['address2'],
                        'city' => $app->req->post['city'],
                        'state' => if_null($app->req->post['state']),
                        'postal_code' => $app->req->post['postal_code'],
                        'country' => if_null($app->req->post['country']),
                        'spammer' => (int) 0,
                        'exception' => $app->req->post['exception'],
                        'tags' => if_null($app->req->post['tags'])
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
                                'method' => 'add',
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

                    tc_cache_delete('my_subscribers_' . get_userdata('id'));
                    tc_cache_delete($id, 'subscriber');
                    tc_cache_delete($id, 'slist');
                    tc_cache_flush_namespace('list_subscribers');
                    tc_logger_activity_log_write('Update Record', 'Subscriber', get_sub_name($id), get_userdata('uname'));
                    _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
                } catch (NotFoundException $e) {
                    _tc_flash()->error($e->getMessage());
                } catch (Exception $e) {
                    _tc_flash()->error($e->getMessage());
                } catch (ORMException $e) {
                    _tc_flash()->error($e->getMessage());
                }
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
         */ elseif (_escape($sub->id) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            tc_register_style('select2');
            tc_register_style('iCheck');
            tc_register_style('selectize');
            tc_register_script('select2');
            tc_register_script('iCheck');

            $app->view->display('subscriber/view', [
                'title' => _t('View/Edit Subscriber'),
                'subscriber' => $sub
                    ]
            );
        }
    });
    
    /**
     * Before route check.
     */
    $app->before('GET', '/getTags/', function() {
        if(!is_user_logged_in()) {
            redirect(get_base_url());
            exit();
        }
    });

    $app->get('/getTags/', function () use($app) {
        try {
            $tagging = $app->db->subscriber()
                    ->select('tags')
                    ->where('addedBy = ?', get_userdata('id'));
            $q = $tagging->find(function ($data) {
                $array = [];
                foreach ($data as $d) {
                    $array[] = $d;
                }
                return $array;
            });
            $tags = [];
            foreach ($q as $r) {
                $tags = array_merge($tags, explode(",", _escape($r['tags'])));
            }
            $tags = array_unique_compact($tags);
            foreach ($tags as $key => $value) {
                if ($value == "" || strlen($value) <= 0) {
                    unset($tags[$key]);
                }
            }
            return $tags;
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
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

            try {
                TinyC\NodeQ\tc_NodeQ::table('campaign_queue')
                        ->where('sid', '=', $id)
                        ->delete();
            } catch (TinyC\NodeQ\NodeQException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            }

            tc_cache_delete('my_subscribers_' . get_userdata('id'));
            tc_cache_delete($id, 'subscriber');
            tc_cache_delete($id, 'slist');
            tc_cache_flush_namespace('list_subscribers');
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
