<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use Cascade\Cascade;
use app\src\NodeQ\tc_NodeQ as Node;
use \app\src\elFinder\elFinderConnector;
use \app\src\elFinder\elFinder;
use PDOException as ORMException;

$app->group('/campaign', function() use ($app) {

    /**
     * Before route check.
     */
    $app->before('GET', '/', function() {
        if (!hasPermission('manage_campaigns')) {
            redirect(get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/', function () use($app) {

        $msgs = $app->db->campaign()
            ->where('owner = ?', get_userdata('id'))
            ->find();

        tc_register_style('datatables');
        tc_register_script('datatables');

        $app->view->display('campaign/index', [
            'title' => _t('My Campaigns'),
            'msgs' => $msgs
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/create/', function() {
        if (!hasPermission('create_campaigns')) {
            redirect(get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/create/', function () use($app) {

        if ($app->req->isPost()) {
            try {
                $msg = $app->db->campaign();
                $msg->owner = get_userdata('id');
                $msg->node = $app->req->_post('node');
                $msg->subject = $app->req->_post('subject');
                $msg->from_name = $app->req->_post('from_name');
                $msg->from_email = $app->req->_post('from_email');
                $msg->html = $app->req->_post('html');
                $msg->footer = $app->req->_post('footer');
                $msg->status = 'ready';
                $msg->sendstart = $app->req->_post('sendstart');
                $msg->archive = $app->req->_post('archive');
                $msg->save();

                $ID = $msg->lastInsertId();
                try {
                    Node::create($app->req->_post('node'), [
                        'mid' => 'integer',
                        'to_email' => 'string',
                        'to_name' => 'string',
                        'message_html' => 'boolean',
                        'message_plain_text' => 'boolean',
                        'timestamp_created' => 'string',
                        'timestamp_to_send' => 'string',
                        'timestamp_sent' => 'string',
                        'is_sent' => 'boolean',
                        'headers' => 'string'
                    ]);
                } catch (\Exception $e) {
                    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
                }
                tc_logger_activity_log_write('New Record', 'Campaign', _filter_input_string(INPUT_POST, 'subject'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200),get_base_url() . 'campaign' . '/' . $ID . '/');
            } catch (NotFoundException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            } catch (ORMException $e) {
                _tc_flash()->error($e->getMessage());
            }
        }

        tc_register_style('select2');
        tc_register_style('datetime');
        tc_register_script('select2');
        tc_register_script('moment.js');
        tc_register_script('datetime');

        $app->view->display('campaign/create', [
            'title' => _t('Create Campaign')
            ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/', function() {
        if (!hasPermission('manage_campaigns')) {
            redirect(get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/(\d+)/', function ($id) use($app) {

        if ($app->req->isPost()) {
            try {
                $msg = $app->db->campaign();
                $msg->subject = $app->req->_post('subject');
                $msg->from_name = $app->req->_post('from_name');
                $msg->from_email = $app->req->_post('from_email');
                $msg->html = $app->req->_post('html');
                $msg->footer = $app->req->_post('footer');
                $msg->sendstart = $app->req->_post('sendstart');
                $msg->archive = $app->req->_post('archive');
                $msg->where('id = ?', $id)->_and_()
                    ->where('owner = ?', get_userdata('id'));
                $msg->update();

                tc_cache_delete($id, 'list');
                tc_logger_activity_log_write('Update Record', 'Email List', _filter_input_string(INPUT_POST, 'subject'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200));
            } catch (NotFoundException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            } catch (ORMException $e) {
                _tc_flash()->error($e->getMessage());
            }
        }

        tc_register_style('select2');
        tc_register_style('datetime');
        tc_register_script('select2');
        tc_register_script('moment.js');
        tc_register_script('datetime');

        $msg = $app->db->campaign()
            ->where('owner = ?', get_userdata('id'))->_and_()
            ->where('id = ?', $id)
            ->findOne();

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($msg == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($msg) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (count($msg->id) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            $app->view->display('campaign/view', [
                'title' => _t('View/Edit Campaign'),
                'cpgn' => $msg
                ]
            );
        }
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
            $msg = $app->db->campaign()
                ->where('owner = ?', get_userdata('id'))->_and_()
                ->where('id = ?', $id);

            if ($msg->delete()) {
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
        _mkdir($app->config('file.savepath') . get_userdata('uname') . '/');
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

        tc_register_style('elfinder');
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
