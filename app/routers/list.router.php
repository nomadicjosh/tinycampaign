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
        _tc_flash()->error(_t("You don't have permission to access the Email Lists."), get_base_url() . 'dashboard' . '/');
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
    $app->before('GET', '/download/', function() {
        if (!hasPermission('manage_email_lists')) {
            _tc_flash()->error(_t("You don't have the proper permission."), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/download/', function () use($app) {
        $download_path = APP_PATH . 'views/list/';
        $file = $app->req->get['f'];

        $args = array(
            'download_path' => $download_path,
            'file' => $file,
            'extension_check' => FALSE,
            'referrer_check' => TRUE,
            'referrer' => get_base_url(),
        );

        $dl = new \app\src\Downloader($args);

        /*
          |-----------------
          | Pre Download Hook
          |------------------
         */

        $download_hook = $dl->get_download_hook();

        /*
          |-----------------
          | Download
          |------------------
         */

        if ($download_hook['download'] == TRUE) {

            /* You can write your logic before proceeding to download */

            /* Let's download file */
            $dl->get_download();
        }
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
                $list->code = $app->req->post['code'];
                $list->name = $app->req->post['name'];
                $list->unsub_mailto = if_null($app->req->post['unsub_mailto']);
                $list->description = $app->req->post['description'];
                $list->created = Jenssegers\Date\Date::now();
                $list->owner = get_userdata('id');
                $list->redirect_success = if_null($app->req->post['redirect_success']);
                $list->redirect_unsuccess = if_null($app->req->post['redirect_unsuccess']);
                $list->confirm_email = _file_get_contents(APP_PATH . 'views/setting/tpl/confirm_email.tpl');
                $list->subscribe_email = _file_get_contents(APP_PATH . 'views/setting/tpl/subscribe_email.tpl');
                $list->unsubscribe_email = _file_get_contents(APP_PATH . 'views/setting/tpl/unsubscribe_email.tpl');
                $list->notify_email = $app->req->post['notify_email'];
                $list->optin = $app->req->post['optin'];
                $list->status = $app->req->post['status'];
                $list->server = if_null($app->req->post['server']);
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
        if (!hasPermission('edit_email_list')) {
            _tc_flash()->error(_t("You don't have permission to access edit Email Lists."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/(\d+)/', function ($id) use($app) {

        if ($app->req->isPost()) {
            try {
                $list = $app->db->list();
                $list->name = $app->req->post['name'];
                $list->unsub_mailto = if_null($app->req->post['unsub_mailto']);
                $list->description = $app->req->post['description'];
                $list->redirect_success = if_null($app->req->post['redirect_success']);
                $list->redirect_unsuccess = if_null($app->req->post['redirect_unsuccess']);
                $list->confirm_email = $app->req->post['confirm_email'];
                $list->subscribe_email = $app->req->post['subscribe_email'];
                $list->unsubscribe_email = $app->req->post['unsubscribe_email'];
                $list->notify_email = $app->req->post['notify_email'];
                $list->optin = $app->req->post['optin'];
                $list->status = $app->req->post['status'];
                $list->server = if_null($app->req->post['server']);
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
         */ elseif (count(_escape($list->id)) <= 0) {

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

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/subscriber/', function() {
        if (!hasPermission('edit_email_list')) {
            _tc_flash()->error(_t("You don't have permission to access subscribers."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/(\d+)/subscriber/', function ($id) use($app) {
        try {
            $subscribers = $app->db->subscriber()
                    ->select('subscriber.id,subscriber.fname,subscriber.lname,subscriber.email')
                    ->select('subscriber.addDate,subscriber.id as Subscriber')
                    ->select('subscriber_list.unsubscribed')
                    ->select('list.id as ListID')
                    ->_join('subscriber_list', 'subscriber.id = subscriber_list.sid')
                    ->_join('list', 'subscriber_list.lid = list.id')
                    ->where('list.owner = ?', get_userdata('id'))->_and_()
                    ->where('list.id = ?', $id);

            $subs = tc_cache_get('list_subscribers_' . $id, 'list_subscribers');
            if (empty($subs)) {
                $subs = $subscribers->find(function ($data) {
                    $array = [];
                    foreach ($data as $d) {
                        $array[] = $d;
                    }
                    return $array;
                });
                tc_cache_add('list_subscribers_' . $id, $subs, 'list_subscribers');
            }

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
         */ elseif (count(_escape($list->id)) <= 0) {

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
                'subs' => array_to_object($subs),
                'list' => $list
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/import/', function() {
        if (!hasPermission('edit_email_list')) {
            _tc_flash()->error(_t("You don't have permission to import subscribers."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/(\d+)/import/', function ($id) use($app) {
        if ($app->req->isPost()) {
            try {
                $delimiter = [
                    'del1' => ",",
                    'del2' => ";",
                    'del3' => "\n",
                    'del4' => "\t"
                ];
                $filename = $_FILES["csv_import"]["tmp_name"];
                if ($_FILES["csv_import"]["size"] > 0) {
                    $handle = fopen($filename, "r");
                    fgetcsv($handle, 10000, $delimiter[$app->req->post['delimiter']]);
                    while (($data = fgetcsv($handle, 1000, $delimiter[$app->req->post['delimiter']])) !== FALSE) {
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
                            'method' => 'import',
                            'addDate' => Jenssegers\Date\Date::now(),
                            'code' => _random_lib()->generateString(200, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                            'confirmed' => $data[3],
                            'unsubscribed' => $data[4]
                        ]);
                    }
                    fclose($handle);
                    tc_cache_flush_namespace('my_subscribers_' . get_userdata('id'));
                    tc_cache_flush_namespace('list_subscribers');
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
         */ elseif (count(_escape($list->id)) <= 0) {

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

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/export/', function() {
        if (!hasPermission('edit_email_list')) {
            _tc_flash()->error(_t("You don't have permission to export subscribers."), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/(\d+)/export/', function ($id) use($app) {
        try {

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=data.csv');
            $output = fopen("php://output", "w");
            fputcsv($output, ['ID', 'First Name', 'Last Name', 'Email', 'Add Date', 'Confirmed', 'Unsubscribed']);
            $csv = $app->db->subscriber()
                    ->select('subscriber.id,subscriber.fname,subscriber.lname,subscriber.email,subscriber.addDate')
                    ->select('CASE WHEN subscriber_list.confirmed = "1" THEN "Yes" ELSE "No" END')
                    ->select('CASE WHEN subscriber_list.unsubscribed = "1" THEN "Yes" ELSE "No" END')
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

    /**
     * Before route check.
     */
    $app->before('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/connector/', function() {
        if (!is_user_logged_in()) {
            redirect(get_base_url());
            exit();
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

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/elfinder/', function() {
        if (!is_user_logged_in()) {
            redirect(get_base_url());
            exit();
        }
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
    $app->before('GET', '/(\d+)/subscriber/(\d+)/d/', function($lid) {
        if (!hasPermission('delete_subscriber')) {
            _tc_flash()->error(_t('You lack the proper permission to delete subscribers.'), get_base_url() . 'list' . '/' . $lid . '/' . 'subscriber' . '/');
            exit();
        }
    });

    $app->get('/(\d+)/subscriber/(\d+)/d/', function ($lid, $sid) use($app) {
        try {
            $sub = $app->db->list()
                    ->select('subscriber_list.id AS slID')
                    ->_join('subscriber_list', 'list.id = subscriber_list.lid')
                    ->where('list.owner = ?', get_userdata('id'))->_and_()
                    ->where('list.id = ?', $lid)->_and_()
                    ->where('subscriber_list.sid = ?', $sid)
                    ->findOne();

            if ($sub->count() > 0) {
                $app->db->subscriber_list()
                        ->reset()
                        ->findOne(_escape($sub->slID))
                        ->delete();
            }

            tc_cache_flush_namespace('my_subscribers_' . get_userdata('id'));
            tc_cache_flush_namespace('list_subscribers');
            tc_cache_delete($lid, 'list');
            _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'list' . '/' . $lid . '/' . 'subscriber' . '/');
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage(), get_base_url() . 'list' . '/' . $lid . '/' . 'subscriber' . '/');
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), get_base_url() . 'list' . '/' . $lid . '/' . 'subscriber' . '/');
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), get_base_url() . 'list' . '/' . $lid . '/' . 'subscriber' . '/');
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/(\d+)/d/', function() {
        if (!hasPermission('delete_email_list')) {
            _tc_flash()->error(_t('You lack the proper permission to delete an email list.'), get_base_url() . 'list' . '/');
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
                    ->where('lid = ?', _escape($list->id))
                    ->find();

            foreach ($cpgn_list as $cl) {
                $app->db->campaign()
                        ->reset()
                        ->findOne(_escape($cl->cid))
                        ->delete();
            }

            try {
                app\src\NodeQ\tc_NodeQ::table('campaign_queue')
                        ->where('lid', '=', $id)
                        ->delete();
            } catch (app\src\NodeQ\NodeQException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            }

            $app->db->list()
                    ->reset()
                    ->findOne(_escape($list->id))
                    ->delete();

            tc_cache_flush_namespace('my_subscribers_' . get_userdata('id'));
            tc_cache_flush_namespace('list_subscribers');
            tc_cache_delete($id, 'list');
            _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'list' . '/' . $id . '/' . 'subscriber' . '/');
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage(), get_base_url() . 'list' . '/' . $id . '/' . 'subscriber' . '/');
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), get_base_url() . 'list' . '/' . $id . '/' . 'subscriber' . '/');
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), get_base_url() . 'list' . '/' . $id . '/' . 'subscriber' . '/');
        }
    });
});

$app->setError(function() use($app) {

    $app->view->display('error/404', ['title' => '404 Error']);
});
