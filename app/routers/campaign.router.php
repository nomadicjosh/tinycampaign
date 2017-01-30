<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use Cascade\Cascade;
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\elFinder\elFinderConnector;
use app\src\elFinder\elFinder;
use PDOException as ORMException;

$app->group('/campaign', function() use ($app) {

    /**
     * Before route check.
     */
    $app->before('GET', '/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
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
        if (!hasPermission('create_campaign')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/create/', function () use($app) {

        if ($app->req->isPost()) {
            try {
                $msg = $app->db->campaign();
                $msg->owner = get_userdata('id');
                $msg->node = $app->req->post['node'];
                $msg->subject = $app->req->post['subject'];
                $msg->from_name = $app->req->post['from_name'];
                $msg->from_email = $app->req->post['from_email'];
                $msg->html = $app->req->post['html'];
                $msg->footer = $app->req->post['footer'];
                $msg->status = 'ready';
                $msg->sendstart = $app->req->post['sendstart'];
                $msg->archive = $app->req->post['archive'];
                $msg->save();

                $ID = $msg->lastInsertId();

                foreach ($app->req->post['lid'] as $list) {
                    $cpgn_list = $app->db->campaign_list();
                    $cpgn_list->insert([
                        'cid' => $ID,
                        'lid' => $list
                    ]);
                }

                try {
                    Node::create($app->req->post['node'], [
                        'mid' => 'integer',
                        'to_email' => 'string',
                        'to_name' => 'string',
                        'message_html' => 'boolean',
                        'message_plain_text' => 'boolean',
                        'timestamp_created' => 'string',
                        'timestamp_to_send' => 'string',
                        'timestamp_sent' => 'string',
                        'is_sent' => 'boolean',
                        'serialized_headers' => 'string'
                    ]);
                } catch (\app\src\NodeQ\NodeQException $e) {
                    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
                }
                tc_logger_activity_log_write('New Record', 'Campaign', _filter_input_string(INPUT_POST, 'subject'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'campaign' . '/' . $ID . '/');
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
        tc_register_style('datetime');
        tc_register_script('select2');
        tc_register_script('moment.js');
        tc_register_script('datetime');
        tc_register_script('iCheck');

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
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/(\d+)/', function ($id) use($app) {

        if ($app->req->isPost()) {
            try {
                $msg = $app->db->campaign();
                $msg->subject = $app->req->post['subject'];
                $msg->from_name = $app->req->post['from_name'];
                $msg->from_email = $app->req->post['from_email'];
                $msg->html = $app->req->post['html'];
                $msg->footer = $app->req->post['footer'];
                $msg->sendstart = $app->req->post['sendstart'];
                $msg->archive = $app->req->post['archive'];
                $msg->where('id = ?', $id)->_and_()
                    ->where('owner = ?', get_userdata('id'));
                $msg->update();

                tc_logger_activity_log_write('Update Record', 'Campaign', _filter_input_string(INPUT_POST, 'subject'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (NotFoundException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            } catch (ORMException $e) {
                _tc_flash()->error($e->getMessage());
            }
        }

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

            tc_register_style('select2');
            tc_register_style('iCheck');
            tc_register_style('datetime');
            tc_register_script('select2');
            tc_register_script('moment.js');
            tc_register_script('datetime');
            tc_register_script('iCheck');

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
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/(\d+)/d/', function ($id) use($app) {
        try {
            $msg = $app->db->campaign()
                ->where('owner = ?', get_userdata('id'));

            try {
                $msg->findOne($id);
                Node::remove($msg->node);
            } catch (\app\src\NodeQ\NodeQException $e) {
                Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
            }

            if ($msg->reset()->findOne($id)->delete()) {
                tc_cache_delete($id, 'list');
                _tc_flash()->success(_tc_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } else {
                _tc_flash()->error(_tc_flash()->notice(409), $app->req->server['HTTP_REFERER']);
            }
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        }
    });

    $app->get('/(\d+)/queue/', function ($id) use($app) {

        $cpgn = get_campaign_by_id($id);
        if($cpgn->status == 'processing') {
            _tc_flash()->error(_t('Message is already queued.'), $app->req->server['HTTP_REFERER']);
            exit();
        }
        
        try {
            $subscriber = $app->db->subscriber()
                ->select('DISTINCT subscriber.fname,subscriber.lname,subscriber.email')
                ->_join('subscriber_list', 'subscriber.id = subscriber_list.sid')
                ->_join('list', 'subscriber_list.lid = list.id')
                ->_join('campaign_list', 'list.id = campaign_list.lid')
                ->_join('campaign', 'campaign_list.lid = campaign.id')
                ->where('subscriber.allowed = "true"')->_and_()
                ->where('subscriber_list.confirmed = "1"')->_and_()
                ->where('subscriber_list.unsubscribe = "0"')->_and_()
                ->where('campaign.id = ?', $id)->_and_()
                ->where('campaign_list.cid = ?', $id)
                ->find();
            /**
             * Instantiate the message queue.
             */
            $queue = new app\src\tc_Queue();
            $queue->node = $cpgn->node;
            $send_date = explode(' ', $cpgn->sendstart);
            $throttle = _h(get_option('mail_throttle'));
            foreach ($subscriber as $sub) {
                $time = date('H:i:s', time());
                /**
                 * Create new tc_QueueMessage object.
                 */
                $new_message = new app\src\tc_QueueMessage();
                $new_message->setMessageId($cpgn->id);
                $new_message->setFromEmail($cpgn->from_email);
                $new_message->setFromName($cpgn->from_name);
                $new_message->setToEmail($sub->email);
                $new_message->setToName($sub->fname . ' ' . $sub->lname);
                //$new_message->setSubject($cpgn->subject);
                $new_message->setTimestampCreated(\Jenssegers\Date\Date::now());
                $new_message->setTimestampToSend(new \Jenssegers\Date\Date("$send_date[0] $time+$throttle seconds"));
                /**
                 * Add message to the queue.
                 */
                $queue->addMessage($new_message);
            }

            $upd = $app->db->campaign();
            $upd->set([
                    'status' => 'processing'
                ])
                ->where('id = ?', $cpgn->id)
                ->update();

            tc_logger_activity_log_write('New Record', 'Campaign Queued', $cpgn->subject, get_userdata('uname'));
            _tc_flash()->success(_t('Campaign was successfully sent to the queue.'), $app->req->server['HTTP_REFERER']);
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
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
});

$app->setError(function() use($app) {

    $app->view->display('error/404', ['title' => '404 Error']);
});
