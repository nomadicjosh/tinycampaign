<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;

/**
 * Before route check.
 */
$app->before('GET', '/list/', function() {
    if (!hasPermission('manage_email_lists')) {
        _tc_flash()->error(_t("You don't have permission to access the Email List route"), get_base_url() . 'dashboard' . '/');
    }
});

$app->group('/list', function() use ($app) {

    /**
     * Before route check.
     */
    $app->before('GET', '/', function() {
        if (!hasPermission('manage_email_lists')) {
            _tc_flash()->error(_t("You don't have permission to access the Manage Email List screen"), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/', function () use($app) {
        try {
            $lists = $app->db->list()
                ->where('owner = ?', get_userdata('id'))
                ->find();
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }

        tc_register_style('datatables');
        tc_register_script('datatables');

        $app->view->display('list/index', [
            'title' => _t('My Email Lists'),
            'lists' => $lists
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/create/', function() {
        if (!hasPermission('create_email_list')) {
            _tc_flash()->error(_t("You don't have permission to create email lists."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/create/', function () use($app) {

        if ($app->req->isPost()) {
            try {
                $list = $app->db->list();
                $list->code = $app->req->_post('code');
                $list->name = $app->req->_post('name');
                $list->description = $app->req->_post('description');
                $list->created = Jenssegers\Date\Date::now();
                $list->owner = get_userdata('id');
                $list->redirect_success = (empty($app->req->post['redirect_success']) ? NULL : $app->req->post['redirect_success']);
                $list->redirect_unsuccess = (empty($app->req->post['redirect_unsuccess']) ? NULL : $app->req->post['redirect_unsuccess']);
                $list->confirm_email = _file_get_contents(APP_PATH . 'views/setting/tpl/confirm_email.tpl');
                $list->subscribe_email = _file_get_contents(APP_PATH . 'views/setting/tpl/subscribe_email.tpl');
                $list->unsubscribe_email = _file_get_contents(APP_PATH . 'views/setting/tpl/unsubscribe_email.tpl');
                $list->optin = $app->req->_post('optin');
                $list->status = $app->req->_post('status');
                $list->save();

                $ID = $list->lastInsertId();
                tc_logger_activity_log_write('New Record', 'Email List', _filter_input_string(INPUT_POST, 'name'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'list' . '/' . $ID . '/');
            } catch (NotFoundException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            } catch (ORMException $e) {
                _tc_flash()->error($e->getMessage());
            }
        }

        tc_register_style('select2');
        tc_register_script('select2');

        $app->view->display('list/create', [
            'title' => _t('Create Email List')
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/', function() {
        if (!hasPermission('create_email_list')) {
            _tc_flash()->error(_t("You don't have permission to access create/edit email lists."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/(\d+)/', function ($id) use($app) {

        if ($app->req->isPost()) {
            try {
                $list = $app->db->list();
                $list->name = $app->req->_post('name');
                $list->description = $app->req->_post('description');
                $list->redirect_success = (empty($app->req->post['redirect_success']) ? NULL : $app->req->post['redirect_success']);
                $list->redirect_unsuccess = (empty($app->req->post['redirect_unsuccess']) ? NULL : $app->req->post['redirect_unsuccess']);
                $list->confirm_email = $app->req->_post('confirm_email');
                $list->subscribe_email = $app->req->_post('subscribe_email');
                $list->unsubscribe_email = $app->req->_post('unsubscribe_email');
                $list->optin = $app->req->_post('optin');
                $list->status = $app->req->_post('status');
                $list->where('id = ?', $id)->_and_()
                    ->where('owner = ?', get_userdata('id'));
                $list->update();

                tc_cache_delete($id, 'list');
                tc_logger_activity_log_write('Update Record', 'Email List', _filter_input_string(INPUT_POST, 'name'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (NotFoundException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            } catch (ORMException $e) {
                _tc_flash()->error($e->getMessage());
            }
        }

        tc_register_style('select2');
        tc_register_script('select2');

        $list = get_list($id);

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($list == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($list) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (count($list->id) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            $app->view->display('list/view', [
                'title' => _t('View/Edit Email List'),
                'list' => $list
                ]
            );
        }
    });

    $app->get('/(\d+)/subscriber/', function ($id) use($app) {
        try {
            $subs = $app->db->subscriber()
                ->select('subscriber.id,subscriber.fname,subscriber.lname,subscriber.email')
                ->select('subscriber.addDate,subscriber.id as Subscriber')
                ->select('subscriber_list.unsubscribe')
                ->select('list.id as ListID')
                ->_join('subscriber_list', 'subscriber.id = subscriber_list.sid')
                ->_join('list', 'subscriber_list.lid = list.id')
                ->where('list.owner = ?', get_userdata('id'))->_and_()
                ->where('list.id = ?', $id)
                ->find();

            $list = $app->db->list()
                ->select('list.name,list.id')
                ->where('list.id = ?', $id)
                ->where('list.owner = ?', get_userdata('id'))
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
        if ($list == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($list) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (count($list) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            tc_register_style('datatables');
            tc_register_script('datatables');

            $app->view->display('list/subscriber', [
                'title' => _t('Subscribers'),
                'subs' => $subs,
                'list' => $list
                ]
            );
        }
    });

    $app->match('GET|POST', '/(\d+)/import/', function ($id) use($app) {
        if ($app->req->isPost()) {
            try {
                $filename = $_FILES["csv_import"]["tmp_name"];
                if ($_FILES["csv_import"]["size"] > 0) {
                    $handle = fopen($filename, "r");
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $subscriber = $app->db->subscriber();
                        $subscriber->insert([
                            'fname' => $data[0],
                            'lname' => $data[1],
                            'email' => $data[2],
                            'code' => _random_lib()->generateString(50, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                            'ip' => $app->req->server['REMOTE_ADDR'],
                            'addedBy' => get_userdata('id'),
                            'addDate' => Jenssegers\Date\Date::now()
                        ]);
                        $sid = $subscriber->lastInsertId();
                        $slist = $app->db->subscriber_list();
                        $slist->insert([
                            'lid' => $id,
                            'sid' => $sid,
                            'addDate' => Jenssegers\Date\Date::now(),
                            'code' => _random_lib()->generateString(200, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                            'confirmed' => 1
                        ]);
                    }
                    fclose($handle);
                    _tc_flash()->success(_t('Subscribers were imported successfully.'));
                } else {
                    _tc_flash()->error(_t('Your .csv file was empty or missing.'));
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

            $list = $app->db->list()
                ->where('list.id = ?', $id)->_and_()
                ->where('list.owner = ?', get_userdata('id'))
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
        if ($list == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($list) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (count($list->id) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            tc_register_style('select2');
            tc_register_script('select2');

            $app->view->display('list/import', [
                'title' => _t('Import Subscribers'),
                'list' => $list
                ]
            );
        }
    });

    $app->match('GET|POST', '/(\d+)/export/', function ($id) use($app) {
        try {

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=data.csv');
            $output = fopen("php://output", "w");
            fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Add Date']);
            $csv = $app->db->subscriber()
                ->select('subscriber.id,subscriber.fname,subscriber.lname,subscriber.email,subscriber.addDate')
                ->_join('subscriber_list', 'subscriber.id = subscriber_list.sid')
                ->where('subscriber_list.lid = ?', $id);
            $q = $csv->find(function ($data) {
                $array = [];
                foreach ($data as $d) {
                    $array[] = $d;
                }
                return $array;
            });
            foreach ($q as $r) {
                fputcsv($output, $r);
            }
            fclose($output);
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
    });

    $app->match('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/connector/', function () use($app) {
        error_reporting(0);
        try {
            _mkdir($app->config('file.savepath') . get_userdata('uname') . '/');
        } catch (\app\src\Exception\IOException $e) {
            Cascade\Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Unable to create directory: %s', $e->getCode(), $e->getMessage()));
        }
        $opts = [
            // 'debug' => true,
            'roots' => [
                [
                    'driver' => 'LocalFileSystem',
                    'path' => $app->config('file.savepath') . get_userdata('uname') . '/',
                    'alias' => 'Files',
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
                    'uploadAllow' => [
                        'image/png', 'image/gif', 'image/jpeg',
                        'application/pdf', 'application/msword',
                        'application/zip', 'audio/mpeg', 'audio/x-m4a',
                        'audio/x-wav', 'text/css', 'text/plain',
                        'text/x-comma-separated-values', 'video/mpeg',
                        'video/mp4', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-powerpoint', 'application/vnd.ms-excel'
                    ],
                    'uploadOrder' => ['allow', 'deny']
                ]
            ]
        ];
        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    });

    $app->match('GET|POST', '/elfinder/', function () use($app) {

        tc_register_script('elfinder');

        $app->view->display('campaign/elfinder', [
            'title' => 'elfinder 2.0'
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/(\d+)/d/', function() {
        if (!hasPermission('delete_email_list')) {
            _tc_flash()->error(_t('You lack the proper permission to delete a list.'), get_base_url() . 'list' . '/');
            exit();
        }
    });

    $app->get('/(\d+)/d/', function ($id) use($app) {
        try {
            $list = $app->db->list()
                ->where('owner = ?', get_userdata('id'))->_and_()
                ->where('id = ?', $id)
                ->findOne();

            $cpgn_list = $app->db->campaign_list()
                ->where('lid = ?', $list->id)
                ->find();

            foreach ($cpgn_list as $cl) {
                $app->db->campaign()
                    ->reset()
                    ->findOne($cl->cid)
                    ->delete();
            }

            $app->db->list()
                ->reset()
                ->findOne($list->id)
                ->delete();
            
            tc_cache_delete($id, 'list');
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
