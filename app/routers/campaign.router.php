<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TinyC\Exception\NotFoundException;
use TinyC\Exception\Exception;
use Cascade\Cascade;
use TinyC\NodeQ\tc_NodeQ as Node;
use TinyC\NodeQ\NodeQException;
use TinyC\elFinder\elFinderConnector;
use TinyC\elFinder\elFinder;
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

        try {
            $msgs = $app->db->campaign()
                    ->where('owner = ?', get_userdata('id'))
                    ->orderBy('sendstart', 'ASC')
                    ->find();
            $count = $app->db->campaign()
                    ->where('status = "processing"')
                    ->count('id');
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }

        tc_register_style('datatables');
        tc_register_script('datatables');

        $app->view->display('campaign/index', [
            'title' => _t('My Campaigns'),
            'msgs' => $msgs,
            'count' => $count
        ]);
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
                $msg->subject = $app->req->post['subject'];
                $msg->from_name = $app->req->post['from_name'];
                $msg->from_email = $app->req->post['from_email'];
                $msg->ruleid = ($app->req->post['ruleid'] <= 0 ? NULL : $app->req->post['ruleid']);
                $msg->html = $app->req->post['html'];
                $msg->text = if_null($app->req->post['text']);
                $msg->footer = if_null($app->req->post['footer']);
                $msg->status = 'ready';
                $msg->sendstart = $app->req->post['sendstart'];
                $msg->archive = $app->req->post['archive'];
                $msg->addDate = \Jenssegers\Date\Date::now();
                $msg->save();

                $ID = $msg->lastInsertId();

                foreach ($app->req->post['lid'] as $list) {
                    $cpgn_list = $app->db->campaign_list();
                    $cpgn_list->insert([
                        'cid' => $ID,
                        'lid' => $list
                    ]);
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
                $msg->ruleid = ($app->req->post['ruleid'] <= 0 ? NULL : $app->req->post['ruleid']);
                $msg->html = $app->req->post['html'];
                $msg->text = if_null($app->req->post['text']);
                $msg->footer = if_null($app->req->post['footer']);
                $msg->status = $app->req->post['status'];
                $msg->sendstart = $app->req->post['sendstart'];
                $msg->archive = $app->req->post['archive'];
                $msg->where('id = ?', $id)->_and_()
                        ->where('owner = ?', get_userdata('id'));
                $msg->update();

                tc_cache_delete($id, 'cpgn');
                tc_cache_delete($id, 'clist');
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

        try {
            $msg = $app->db->campaign()
                    ->where('owner = ?', get_userdata('id'))->_and_()
                    ->where('id = ?', $id)
                    ->findOne();
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        }

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
         */ elseif (_escape($msg->id) <= 0) {

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
    $app->before('GET|POST', '/(\d+)/queue/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/(\d+)/queue/', function ($id) use($app) {

        $cpgn = get_campaign_by_id($id);
        if (_escape($cpgn->status) == 'processing') {
            _tc_flash()->error(_t('Message is already queued.'), $app->req->server['HTTP_REFERER']);
            exit();
        }

        if (_escape($cpgn->id) <= 0) {
            _tc_flash()->success(_t('Campaign does not exist.'), $app->req->server['HTTP_REFERER']);
            exit();
        }

        $app->hook->{'do_action'}('queue_campaign', $cpgn);

        redirect($app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/pause/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/(\d+)/pause/', function ($id) use($app) {

        $cpgn = get_campaign_by_id($id);
        if (_escape($cpgn->status) == 'paused') {
            _tc_flash()->error(_t('Message is already paused.'), $app->req->server['HTTP_REFERER']);
            exit();
        }

        try {
            $upd = $app->db->campaign();
            $upd->set([
                        'status' => 'paused'
                    ])
                    ->where('id = ?', _escape($cpgn->id))
                    ->update();
            tc_logger_activity_log_write('Update Record', 'Campaign Paused', _escape($cpgn->subject), get_userdata('uname'));
            _tc_flash()->success(_t('Campaign was successfully paused.'), $app->req->server['HTTP_REFERER']);
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
    $app->before('GET|POST', '/(\d+)/resume/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/(\d+)/resume/', function ($id) use($app) {

        $cpgn = get_campaign_by_id($id);
        if (_escape($cpgn->status) == 'processing') {
            _tc_flash()->error(_t('Message is already processing.'), $app->req->server['HTTP_REFERER']);
            exit();
        }

        try {
            $upd = $app->db->campaign();
            $upd->set([
                        'status' => 'processing'
                    ])
                    ->where('id = ?', _escape($cpgn->id))
                    ->update();
            tc_logger_activity_log_write('Update Record', 'Campaign Resumed', _escape($cpgn->subject), get_userdata('uname'));
            _tc_flash()->success(_t('Campaign was successfully resumed.'), $app->req->server['HTTP_REFERER']);
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
    $app->before('GET|POST', '/(\d+)/test/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->post('/(\d+)/test/', function ($id) use($app) {
        $cpgn = get_campaign_by_id($id);
        $sub = get_user_by('id', get_userdata('id'));
        $server = get_server_info($app->req->post['server']);
        $send_to = $app->req->post['email'] != '' ? $app->req->post['email'] : _escape($sub->email);

        $footer = _escape($cpgn->footer);
        $footer = str_replace('{email}', _escape($sub->email), $footer);
        $footer = str_replace('{from_email}', _escape($cpgn->from_email), $footer);
        $footer = str_replace('{personal_preferences}', get_base_url() . 'preferences/{NOID}/subscriber/{NOID}/', $footer);
        $footer = str_replace('{unsubscribe_url}', get_base_url() . 'unsubscribe/{NOID}/lid/{NOID}/sid/{NOID}/', $footer);

        $msg = _escape($cpgn->html);
        $msg = str_replace('{todays_date}', \Jenssegers\Date\Date::now()->format('M d, Y'), $msg);
        $msg = str_replace('{subject}', _escape($cpgn->subject), $msg);
        $msg = str_replace('{view_online}', '<a href="' . get_base_url() . 'archive/' . $id . '/">' . _t('View this email in your browser') . '</a>', $msg);
        $msg = str_replace('{first_name}', _escape($sub->fname), $msg);
        $msg = str_replace('{last_name}', _escape($sub->lname), $msg);
        $msg = str_replace('{email}', _escape($sub->email), $msg);
        $msg = str_replace('{address1}', _escape($sub->address1), $msg);
        $msg = str_replace('{address2}', _escape($sub->address2), $msg);
        $msg = str_replace('{city}', _escape($sub->city), $msg);
        $msg = str_replace('{state}', _escape($sub->state), $msg);
        $msg = str_replace('{postal_code}', _escape($sub->postal_code), $msg);
        $msg = str_replace('{country}', _escape($sub->country), $msg);
        $msg = str_replace('{unsubscribe_url}', '<a href="' . get_base_url() . 'unsubscribe/{NOID}/lid/{NOID}/sid/{NOID}/">' . _t('unsubscribe') . '</a>', $msg);
        $msg = str_replace('{personal_preferences}', '<a href="' . get_base_url() . 'preferences/{NOID}/subscriber/{NOID}/">' . _t('preferences page') . '</a>', $msg);
        $msg .= $footer;
        $msg .= tinyc_footer_logo();
        $app->hook->{'do_action_array'}('tinyc_test_email_init', [$server, $send_to, _escape($cpgn->subject), $msg, _escape($cpgn->text), '']);
        redirect($app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/report/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/(\d+)/report/', function ($id) use($app) {

        try {
            $cpgn = get_campaign_by_id($id);
            $opened = $app->db->tracking()
                    ->where('tracking.cid = ?', $id)
                    ->sum('tracking.viewed');
            $unique_opens = $app->db->tracking()
                    ->where('tracking.cid = ?', $id)
                    ->count('tracking.id');
            $clicks = $app->db->tracking_link()
                    ->where('tracking_link.cid = ?', $id)
                    ->groupBy('tracking_link.cid')
                    ->sum('tracking_link.clicked');
            $unique_clicks = $app->db->tracking_link()
                    ->where('tracking_link.cid = ?', $id)
                    ->count('DISTINCT tracking_link.sid');
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }

        try {
            $unique_unsubs = $app->db->campaign_queue()
                    ->where('cid = ?', $id)->_and_()
                    ->where('is_unsubscribed', 1)
                    ->count();
            Node::dispense('campaign_bounce');
            $unique_bounces = Node::table('campaign_bounce')
                    ->where('cid', '=', $id)
                    ->findAll()
                    ->count();
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (NodeQException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        }

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($cpgn == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($cpgn) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (_escape($cpgn->id) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            tc_register_script('highcharts-3d');
            tc_register_script('campaign-domains');

            $app->view->display('campaign/report', [
                'title' => _escape($cpgn->subject),
                'cpgn' => $cpgn,
                'opened' => $opened,
                'unique_opens' => $unique_opens,
                'clicks' => $clicks,
                'unique_clicks' => $unique_clicks,
                'unique_unsubs' => $unique_unsubs,
                'unique_bounces' => $unique_bounces
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/report/opened/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/(\d+)/report/opened/', function ($id) use($app) {

        try {
            $cpgn = get_campaign_by_id($id);
            $opens = $app->db->subscriber()
                    ->select('subscriber.email,tracking.*')
                    ->_join('tracking', 'tracking.sid = subscriber.id')
                    ->_join('campaign', 'tracking.cid = campaign.id')
                    ->where('campaign.id = ?', _escape($cpgn->id))
                    ->find();

            $sum = $app->db->campaign()
                    ->where('owner = ?', get_userdata('id'))->_and_()
                    ->where('id = ?', $id)
                    ->sum('viewed');
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

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($cpgn) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (_escape($cpgn->id) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            tc_register_script('highcharts-3d');
            tc_register_script('campaign-opened');

            $app->view->display('campaign/opened', [
                'title' => _escape($cpgn->subject),
                'cpgn' => $cpgn,
                'opens' => $opens,
                'sum' => $sum
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/report/clicked/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/(\d+)/report/clicked/', function ($id) use($app) {

        try {
            $cpgn = get_campaign_by_id($id);
            $clicks = $app->db->subscriber()
                    ->select('subscriber.email,tracking_link.*')
                    ->_join('tracking_link', 'tracking_link.sid = subscriber.id')
                    ->_join('campaign', 'tracking_link.cid = campaign.id')
                    ->where('campaign.id = ?', _escape($cpgn->id))
                    ->find();

            $sum = $app->db->subscriber()
                    ->select('subscriber.email,tracking_link.*')
                    ->_join('tracking_link', 'tracking_link.sid = subscriber.id')
                    ->_join('campaign', 'tracking_link.cid = campaign.id')
                    ->where('campaign.id = ?', _escape($cpgn->id))
                    ->sum('tracking_link.clicked');
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

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($cpgn) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (_escape($cpgn->id) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            tc_register_script('highcharts-3d');
            tc_register_script('campaign-clicked');

            $app->view->display('campaign/clicked', [
                'title' => _escape($cpgn->subject),
                'cpgn' => $cpgn,
                'clicks' => $clicks,
                'sum' => $sum
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/report/unsubscribed/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/(\d+)/report/unsubscribed/', function ($id) use($app) {

        try {
            $cpgn = get_campaign_by_id($id);
            $unsubs = $app->db->campaign_queue()
                    ->where('cid = ?', $id)->_and_()
                    ->where('is_unsubscribed', 1)
                    ->find();

            $sum = $app->db->campaign_queue()
                    ->where('cid = ?', $id)->_and_()
                    ->where('is_unsubscribed', 1)
                    ->count();
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        }

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($cpgn == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($cpgn) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (_escape($cpgn->id) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            //tc_register_script('highcharts-3d');
            //tc_register_script('campaign-opened');

            $app->view->display('campaign/unsubscribed', [
                'title' => _escape($cpgn->subject),
                'cpgn' => $cpgn,
                'unsubs' => $unsubs,
                'sum' => $sum
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/connector/', function() {
        if (!is_user_logged_in()) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->match('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/connector/', function () use($app) {
        error_reporting(0);
        try {
            _mkdir(BASE_PATH . 'static' . DS . 'media' . DS . get_userdata('id') . DS);
        } catch (\TinyC\Exception\IOException $e) {
            Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Unable to create directory: %s', $e->getCode(), $e->getMessage()));
        }
        $opts = [
            // 'debug' => true,
            'roots' => [
                [
                    'driver' => 'LocalFileSystem',
                    'startPath' => BASE_PATH . 'static' . DS . 'media' . DS . get_userdata('id') . DS,
                    'path' => BASE_PATH . 'static' . DS . 'media' . DS . get_userdata('id') . DS,
                    'alias' => 'Media Library',
                    'mimeDetect' => 'auto',
                    'accessControl' => 'access',
                    'tmbURL' => get_base_url() . 'static/media/' . get_userdata('id') . '/' . '.tmb',
                    'tmpPath' => BASE_PATH . 'static' . DS . 'media' . DS . '.tmb',
                    'URL' => get_base_url() . 'static/media/' . get_userdata('id') . '/',
                    'attributes' => [
                        [
                            'read' => true,
                            'write' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.gitignore/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
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
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
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
    $app->before('GET|POST', '/getTemplate/(\d+)/', function() {
        if (!hasPermission('create_campaign')) {
            _tc_flash()->{'error'}(_t('You lack the proper permission to create a campaign.'));
        }
    });

    $app->get('/getTemplate/(\d+)/', function($id) use($app) {
        try {
            $template = $app->db->template()
                    ->where('owner = ?', get_userdata('id'))->_and_()
                    ->where('id = ?', $id)
                    ->find();

            foreach ($template as $tpl) {
                echo _escape($tpl->content);
            }
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
    $app->before('GET', '/getDomainReport/(\d+)/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to request this source.'), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/getDomainReport/(\d+)/', function ($id) use($app) {

        try {
            $q = $app->db->query(
                    "SELECT substring_index(subscriber.email, '@', -1) domain, COUNT(subscriber.email) domain_count "
                    . "FROM campaign_list "
                    . "JOIN campaign ON campaign_list.cid = campaign.id "
                    . "JOIN subscriber_list ON campaign_list.lid = subscriber_list.lid "
                    . "JOIN subscriber ON subscriber_list.sid = subscriber.id "
                    . "WHERE campaign.owner = ? AND campaign_list.cid = ? "
                    . "GROUP BY substring_index(subscriber.email, '@', -1) "
                    . "ORDER BY domain_count DESC", [get_userdata('id'), $id]
            );
            // Use closure as callback
            $results = tc_cache_get($id, 'domain_report');
            if (empty($results)) {
                // Use closure as callback
                $results = $q->find(function($data) {
                    $array = [];
                    foreach ($data as $d) {
                        $array[] = $d;
                    }
                    return $array;
                });
                tc_cache_add($id, $results, 'domain_report');
            }
            // Retrieve data passed from query to closure
            $rows = [];
            foreach ($results as $r) {
                $row[0] = _escape($r['domain']);
                $row[1] = _escape($r['domain_count']);
                array_push($rows, $row);
            }
            print json_encode($rows, JSON_NUMERIC_CHECK);
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
    $app->before('GET', '/getOpenedDayReport/(\d+)/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to request this source.'), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/getOpenedDayReport/(\d+)/', function ($id) use($app) {

        try {
            $q = $app->db->query(
                    "SELECT DATE_FORMAT(tracking.first_open, '%W, %M %d, %Y') as open_date, SUM(tracking.viewed) as num_opens "
                    . "FROM tracking "
                    . "JOIN campaign ON tracking.cid = campaign.id "
                    . "WHERE campaign.owner = ? AND campaign.id = ? "
                    . "GROUP BY DATE_FORMAT(tracking.first_open, '%W, %M %d, %Y')", [get_userdata('id'), $id]
            );
            // Use closure as callback
            $results = tc_cache_get($id, 'oday_report');
            if (empty($results)) {
                // Use closure as callback
                $results = $q->find(function($data) {
                    $array = [];
                    foreach ($data as $d) {
                        $array[] = $d;
                    }
                    return $array;
                });
                tc_cache_add($id, $results, 'oday_report');
            }
            // Retrieve data passed from query to closure
            $rows = [];
            foreach ($results as $r) {
                $row[0] = \Jenssegers\Date\Date::parse(_escape($r['open_date']))->format('D d, M Y');
                $row[1] = _escape($r['num_opens']);
                array_push($rows, $row);
            }
            print json_encode($rows, JSON_NUMERIC_CHECK);
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
    $app->before('GET', '/getOpenedHourReport/(\d+)/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to request this source.'), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/getOpenedHourReport/(\d+)/', function ($id) use($app) {

        try {
            $q = $app->db->query(
                    "SELECT tracking.first_open as open_time, COUNT(tracking.id) as opens "
                    . "FROM tracking "
                    . "JOIN campaign ON tracking.cid = campaign.id "
                    . "WHERE campaign.owner = ? AND campaign.id = ? "
                    . "GROUP BY hour(tracking.first_open), day(tracking.first_open)", [get_userdata('id'), $id]
            );
            // Use closure as callback
            $results = tc_cache_get($id, 'ohour_report');
            if (empty($results)) {
                // Use closure as callback
                $results = $q->find(function($data) {
                    $array = [];
                    foreach ($data as $d) {
                        $array[] = $d;
                    }
                    return $array;
                });
                tc_cache_add($id, $results, 'ohour_report');
            }
            // Retrieve data passed from query to closure
            $rows = [];
            foreach ($results as $r) {
                $row[0] = \Jenssegers\Date\Date::parse(_escape($r['open_time']))->format('D d, M Y / h:00 A');
                $row[1] = _escape($r['opens']);
                array_push($rows, $row);
            }
            print json_encode($rows, JSON_NUMERIC_CHECK);
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
    $app->before('GET', '/getClickedDayReport/(\d+)/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to request this source.'), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/getClickedDayReport/(\d+)/', function ($id) use($app) {

        try {
            $q = $app->db->query(
                    "SELECT DATE_FORMAT(tracking_link.addDate, '%W, %M %d, %Y') as click_date, SUM(tracking_link.clicked) as num_clicks "
                    . "FROM tracking_link "
                    . "JOIN campaign ON tracking_link.cid = campaign.id "
                    . "WHERE campaign.owner = ? AND campaign.id = ? "
                    . "GROUP BY DATE_FORMAT(tracking_link.addDate, '%W, %M %d, %Y')", [get_userdata('id'), $id]
            );

            $results = tc_cache_get($id, 'cday_report');
            if (empty($results)) {
                // Use closure as callback
                $results = $q->find(function($data) {
                    $array = [];
                    foreach ($data as $d) {
                        $array[] = $d;
                    }
                    return $array;
                });
                tc_cache_add($id, $results, 'cday_report');
            }
            // Retrieve data passed from query to closure
            $rows = [];
            foreach ($results as $r) {
                $row[0] = \Jenssegers\Date\Date::parse(_escape($r['click_date']))->format('D d, M Y');
                $row[1] = _escape($r['num_clicks']);
                array_push($rows, $row);
            }
            print json_encode($rows, JSON_NUMERIC_CHECK);
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
    $app->before('GET', '/getClickedHourReport/(\d+)/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to request this source.'), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/getClickedHourReport/(\d+)/', function ($id) use($app) {

        try {
            $q = $app->db->query(
                    "SELECT tracking_link.addDate as click_hour, COUNT(tracking_link.id) as clicks "
                    . "FROM tracking_link "
                    . "JOIN campaign ON tracking_link.cid = campaign.id "
                    . "WHERE campaign.owner = ? AND campaign.id = ? "
                    . "GROUP BY hour(tracking_link.addDate), day(tracking_link.addDate)", [get_userdata('id'), $id]
            );

            $results = tc_cache_get($id, 'chour_report');
            if (empty($results)) {
                // Use closure as callback
                $results = $q->find(function($data) {
                    $array = [];
                    foreach ($data as $d) {
                        $array[] = $d;
                    }
                    return $array;
                });
                tc_cache_add($id, $results, 'chour_report');
            }
            // Retrieve data passed from query to closure
            $rows = [];
            foreach ($results as $r) {
                $row[0] = \Jenssegers\Date\Date::parse(_escape($r['click_hour']))->format('D d, M Y / h:00 A');
                $row[1] = _escape($r['clicks']);
                array_push($rows, $row);
            }
            print json_encode($rows, JSON_NUMERIC_CHECK);
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
    $app->before('GET', '/(\d+)/d/', function() {
        if (!hasPermission('delete_campaign')) {
            _tc_flash()->error(_t('You lack the proper permission to delete a campaign.'), get_base_url() . 'campaign' . '/');
            exit();
        }
    });

    $app->get('/(\d+)/d/', function ($id) use($app) {
        try {
            $msg = $app->db->campaign()
                    ->where('owner = ?', get_userdata('id'))->_and_()
                    ->where('id = ?', $id);

            try {
                $app->db->campaign_queue()
                        ->where('cid', $id)
                        ->delete();
            } catch (ORMException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            }

            $msg->reset()->findOne($id)->delete();
            tc_cache_delete($id, 'campaign');
            tc_cache_delete($id, 'cpgn');
            tc_cache_delete($id, 'clist');
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

$app->group('/rss-campaign', function() use ($app) {
    /**
     * Before route check.
     */
    $app->before('GET', '/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/', function () use($app) {

        try {
            $feeds = $app->db->rss_campaign()
                    ->where('owner = ?', get_userdata('id'))
                    ->orderBy('id', 'ASC')
                    ->find();
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        }

        tc_register_style('datatables');
        tc_register_script('datatables');

        $app->view->display('rss-campaign/index', [
            'title' => _t('My RSS Campaigns'),
            'feeds' => $feeds
        ]);
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
                $feed = $app->db->rss_campaign();
                $feed->insert([
                    'owner' => get_userdata('id'),
                    'node' => $app->req->post['node'],
                    'subject' => $app->req->post['subject'],
                    'from_name' => $app->req->post['from_name'],
                    'from_email' => $app->req->post['from_email'],
                    'rss_feed' => $app->req->post['rss_feed'],
                    'lid' => maybe_serialize($app->req->post['lid']),
                    'tid' => $app->req->post['tid'],
                    'status' => 'active',
                    'addDate' => \Jenssegers\Date\Date::now()
                ]);

                $ID = $feed->lastInsertId();

                try {
                    Node::create($app->req->post['node'], [
                        'rcid' => 'integer',
                        'rss_content' => 'string',
                        'is_processed' => 'string'
                    ]);
                } catch (NodeQException $e) {
                    _tc_flash()->error($e->getMessage());
                }

                tc_logger_activity_log_write('New Record', 'RSS Campaign', _filter_input_string(INPUT_POST, 'subject'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'rss-campaign' . '/' . $ID . '/');
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

        $app->view->display('rss-campaign/create', [
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
                $feed = $app->db->rss_campaign();
                $feed->set([
                            'subject' => $app->req->post['subject'],
                            'from_name' => $app->req->post['from_name'],
                            'from_email' => $app->req->post['from_email'],
                            'rss_feed' => $app->req->post['rss_feed'],
                            'lid' => maybe_serialize($app->req->post['lid']),
                            'tid' => $app->req->post['tid'],
                            'status' => $app->req->post['status']
                        ])
                        ->where('id = ?', $id)->_and_()
                        ->where('owner = ?', get_userdata('id'))
                        ->update();

                tc_logger_activity_log_write('Update Record', 'RSS Campaign', _filter_input_string(INPUT_POST, 'subject'), get_userdata('uname'));
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
            $rss_campaign = $app->db->rss_campaign()
                    ->where('owner = ?', get_userdata('id'))->_and_()
                    ->where('id = ?', $id)
                    ->findOne();
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        }

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($rss_campaign == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($rss_campaign) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (_escape($rss_campaign->id) <= 0) {

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

            $app->view->display('rss-campaign/view', [
                'title' => _t('View/Edit RSS Campaign'),
                'rss' => $rss_campaign
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/d/', function() {
        if (!hasPermission('delete_campaign')) {
            _tc_flash()->error(_t('You lack the proper permission to delete an RSS campaign.'), get_base_url() . 'dashboard' . '/');
        }
    });
    
    $app->get('/(\d+)/d/', function ($id) use($app) {
        try {

            $node = app()->db->rss_campaign()
                    ->where('owner = ?', get_userdata('id'))
                    ->findOne($id);

            if (_escape($node->id) > 0) {

                Node::remove(_escape($node->node));

                app()->db->rss_campaign()
                        ->where('owner = ?', get_userdata('id'))
                        ->reset()
                        ->findOne($id)
                        ->delete();
            }

            _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'rss-campaign' . '/');
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (NodeQException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        }
    });
});

$app->group('/rlde', function() use ($app) {
    /**
     * Before route check.
     */
    $app->before('GET', '/', function() {
        if (!hasPermission('manage_campaigns')) {
            _tc_flash()->error(_t('You lack the proper permission to access the requested screen.'), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/', function () use($app) {

        try {
            $rule = Node::table('rlde')
                    ->where('owner', '=', (int) get_userdata('id'))
                    ->findAll();
        } catch (NodeQException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        }

        tc_register_style('datatables');
        tc_register_script('datatables');

        $app->view->display('rlde/index', [
            'title' => _t('Rules'),
            'rules' => $rule
        ]);
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
                $rlde = Node::table('rlde');
                $rlde->owner = (int) get_userdata('id');
                $rlde->description = (string) $app->req->post['description'];
                $rlde->code = (string) $app->req->post['code'];
                $rlde->comment = (string) $app->req->post['comment'];
                $rlde->rule = (string) $app->req->post['rule'];
                $rlde->adddate = (string) Jenssegers\Date\Date::now();
                $rlde->lastupdate = (string) Jenssegers\Date\Date::now();
                $rlde->save();

                $ID = $rlde->lastId();
                tc_logger_activity_log_write('New Record', 'Rule', _filter_input_string(INPUT_POST, 'description'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'rlde' . '/' . $ID . '/');
            } catch (NodeQException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            }
        }

        tc_register_style('select2');
        tc_register_style('iCheck');
        tc_register_style('datetime');
        tc_register_style('querybuilder');
        tc_register_script('select2');
        tc_register_script('moment.js');
        tc_register_script('datetime');
        tc_register_script('iCheck');

        $app->view->display('rlde/create', [
            'title' => _t('Create Rule')
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
                $rlde = Node::table('rlde')->where('owner', '=', (int) get_userdata('id'))->find($id);
                $rlde->description = (string) $app->req->post['description'];
                $rlde->code = (string) $app->req->post['code'];
                $rlde->comment = (string) $app->req->post['comment'];
                $rlde->rule = (string) $app->req->post['rule'];
                $rlde->lastupdate = (string) Jenssegers\Date\Date::now();
                $rlde->save();

                tc_logger_activity_log_write('Update Record', 'Rule', _filter_input_string(INPUT_POST, 'description'), get_userdata('uname'));
                _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'rlde' . '/' . $id . '/');
            } catch (NodeQException $e) {
                _tc_flash()->error($e->getMessage());
            } catch (Exception $e) {
                _tc_flash()->error($e->getMessage());
            }
        }

        try {
            $rule = Node::table('rlde')->where('owner', '=', (int) get_userdata('id'))->find($id);
        } catch (NodeQException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        }

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($rule == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($rule) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (_escape($rule->id) <= 0) {

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
            tc_register_style('querybuilder');
            tc_register_script('select2');
            tc_register_script('moment.js');
            tc_register_script('datetime');
            tc_register_script('iCheck');

            $app->view->display('rlde/view', [
                'title' => _t('Create Rule'),
                'rule' => $rule
                    ]
            );
        }
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/(\d+)/d/', function() {
        if (!hasPermission('delete_campaign')) {
            _tc_flash()->error(_t('You lack the proper permission to delete a rule.'), get_base_url() . 'dashboard' . '/');
        }
    });
    
    $app->get('/(\d+)/d/', function ($id) use($app) {
        try {
            Node::table('rlde')
                    ->where('owner', '=', get_userdata('id'))
                    ->find($id)
                    ->delete();

            $cpgn = $app->db->campaign();
            $cpgn->set([
                        'ruleid' => NULL
                    ])
                    ->where('ruleid = ?', $id)
                    ->update();

            $rss = $app->db->rss_campaign();
            $rss->set([
                        'ruleid' => NULL
                    ])
                    ->where('ruleid = ?', $id)
                    ->update();

            _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'rlde' . '/');
        } catch (NodeQException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage(), $app->req->server['HTTP_REFERER']);
        }
    });
});

$app->setError(function() use($app) {

    $app->view->display('error/404', ['title' => '404 Error']);
});
