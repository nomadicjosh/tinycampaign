<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;

/**
 * Dashboard Router
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
/**
 * Before router check.
 */
$app->before('GET|POST', '/dashboard(.*)', function () {
    if (!hasPermission('access_dashboard')) {
        redirect(get_base_url());
    }
});

$app->group('/dashboard', function () use($app) {

    $app->get('/', function () use($app) {

        tc_register_script('highcharts');
        tc_register_script('dashboard');

        $app->view->display('dashboard/index', [
            'title' => 'Dashboard'
        ]);
    });

    $app->match('GET|POST', '/support/', function () use($app) {
        if ($app->req->isPost()) {
            $name = $app->req->post['name'];
            $email = $app->req->post['email'];
            $topic = $app->req->post['topic'];
            $summary = $app->req->post['summary'];
            $details = $app->req->post['details'];
            $attachment = $name = $app->req->post['attachment'];
        }

        tc_register_style('select2');
        tc_register_script('select2');

        $app->view->display('dashboard/support', [
            'title' => 'Support Ticket'
        ]);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/system-snapshot/', function () {
        if (!hasPermission('access_settings_screen')) {
            _tc_flash()->error(_t("You don't have permission to view the System Snapshot Report screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/system-snapshot/', function () use($app) {
        try {
            $db = $app->db->query("SELECT version() AS version")->findOne();
            $user = $app->db->user()->where("status = '1'")->count('id');
            $error = $app->db->error()->count('ID');
        } catch (NotFoundException $e) {
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
        }
        $app->view->display('dashboard/system-snapshot', [
            'title' => 'System Snapshot Report',
            'db' => $db,
            'user' => $user,
            'error' => $error
        ]);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/flushCache/', function () {
        if (!hasPermission('access_settings_screen')) {
            _tc_flash()->error(_t("You are not allowed to flush the cache."), get_base_url() . 'dashboard' . '/');
            exit();
        }
    });

    $app->get('/flushCache/', function () use($app) {
        tc_cache_flush();
        _tc_flash()->success(_t('Cache was flushed successfully.'), $app->req->server['HTTP_REFERER']);
    });

    $app->get('/getSubList/', function () use($app) {

        try {
            $lists = $app->db->list()
                ->select('list.name')
                ->select('COUNT(subscriber_list.sid) AS count')
                ->_join('subscriber_list', 'list.id = subscriber_list.lid')
                ->where('subscriber_list.confirmed = "1"')->_and_()
                ->where('subscriber_list.unsubscribed <> "1"')->_and_()
                ->where('list.owner = ?', get_userdata('id'))
                ->groupBy('subscriber_list.lid')
                ->orderBy('subscriber_list.addDate', 'DESC')
                ->find();

            $rows = [];
            foreach ($lists as $list) {
                $row[0] = $list->name;
                $row[1] = $list->count;
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

    $app->get('/getSentEmail/', function () use($app) {

        try {
            $emails = $app->db->campaign()
                ->select('list.name')
                ->select('campaign.recipients AS count')
                ->_join('campaign_list', 'campaign.id = campaign_list.cid')
                ->_join('list', 'campaign_list.lid = list.id')
                ->where('campaign.status = "sent"')->_and_()
                ->where('campaign.owner = ?', get_userdata('id'))
                ->groupBy('list.id')
                ->find();

            $rows = [];
            foreach ($emails as $email) {
                $row[0] = $email->name;
                $row[1] = $email->count;
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

    $app->get('/getCpgnList/', function () use($app) {
        try {
            $cpgn = $app->db->campaign()
                ->select('list.name AS List')
                ->select('COUNT(campaign_list.id) AS count')
                ->_join('campaign_list', 'campaign.id = campaign_list.cid')
                ->_join('list', 'campaign_list.lid = list.id')
                ->where('campaign.owner = ?', get_userdata('id'))
                ->groupBy('list.name')
                ->orderBy('campaign.id', 'DESC')
                ->limit(10)
                ->find();

            $rows = [];
            foreach ($cpgn as $c) {
                $row[0] = $c->List;
                $row[1] = $c->count;
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

    $app->get('/getBouncedEmail/', function () use($app) {

        try {
            $cpgns = $app->db->campaign()
                ->select('COUNT(campaign.bounces) as count,list.name')
                ->_join('campaign_list', 'campaign.id = campaign_list.cid')
                ->_join('list', 'campaign_list.lid = list.id')
                ->where('campaign.status = "sent"')->_and_()
                ->where('campaign.bounces > 0')->_and_()
                ->where('campaign.owner = ?', get_userdata('id'))
                ->groupBy('campaign.id')
                ->find();

            $rows = [];
            foreach ($cpgns as $cpgn) {
                $row[0] = $cpgn->name;
                $row[1] = $cpgn->count;
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
});

$app->setError(function () use($app) {

    $app->view->display('error/404', [
        'title' => '404 Error'
    ]);
});
