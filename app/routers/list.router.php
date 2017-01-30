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
        _tc_flash()->error(_t("You don't have permission to access the Email List route"),get_base_url() . 'dashboard' . '/');
    }
});

$app->group('/list', function() use ($app) {

    /**
     * Before route check.
     */
    $app->before('GET', '/', function() {
        if (!hasPermission('manage_email_lists')) {
             _tc_flash()->error(_t("You don't have permission to access the Manage Email List screen"),get_base_url() . 'dashboard' . '/');
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
             _tc_flash()->error(_t("You don't have permission to create email lists."),get_base_url() . 'dashboard' . '/');
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

                if ($list->save()) {
                    $ID = $list->lastInsertId();
                    tc_logger_activity_log_write('New Record', 'Email List', _filter_input_string(INPUT_POST, 'name'), get_userdata('uname'));
                    _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'list' . '/' . $ID . '/');
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
             _tc_flash()->error(_t("You don't have permission to access create/edit email lists."),get_base_url() . 'dashboard' . '/');
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

                if ($list->update()) {
                    tc_cache_delete($id, 'list');
                    tc_logger_activity_log_write('Update Record', 'Email List', _filter_input_string(INPUT_POST, 'name'), get_userdata('uname'));
                    _tc_flash()->success(_tc_flash()->notice(200),$app->req->server['HTTP_REFERER']);
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
                ->select('subscriber.fname,subscriber.lname,subscriber.email')
                ->select('subscriber.addDate,subscriber.id as Subscriber')
                ->select('list.id as ListID')
                ->_join('subscriber_list', 'subscriber.id = subscriber_list.sid')
                ->_join('list', 'subscriber_list.lid LIKE CONCAT("%", list.id, "%")')
                ->where('list.owner = ?', get_userdata('id'))->_and_()
                ->where('list.id = ?', $id)
                ->find();
            
            $list = $app->db->list()
                ->select('list.name,list.id')
                ->where('list.id = ?', $id)
                ->findOne();
            
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }

        tc_register_style('datatables');
        tc_register_script('datatables');

        $app->view->display('list/subscriber', [
            'title' => _t('Subscribers'),
            'subs' => $subs,
            'list' => $list
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET', '/(\d+)/d/', function() {
        if (!hasPermission('manage_email_lists')) {
            redirect(get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/(\d+)/d/', function ($id) use($app) {
        try {
            $list = $app->db->list()
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
});

$app->setError(function() use($app) {

    $app->view->display('error/404', ['title' => '404 Error']);
});
