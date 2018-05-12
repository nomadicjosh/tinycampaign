<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TinyC\NodeQ\tc_NodeQ as Node;
use TinyC\NodeQ\NodeQException;
use TinyC\NodeQ\Helpers\Validate as Validate;
use Cascade\Cascade;
use TinyC\Exception\NotFoundException;
use TinyC\Exception\Exception;
use PDOException as ORMException;
use TinyC\Exception\IOException;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/**
 * Cron Router
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
$options = [
    30 => '30 seconds',
    60 => 'Minute',
    120 => '2 minutes',
    300 => '5 minutes',
    600 => '10 minutes',
    900 => '15 minutes',
    1800 => 'Half hour',
    2700 => '45 minutes',
    3600 => 'Hour',
    7200 => '2 hours',
    14400 => '4 hours',
    43200 => '12 hours',
    86400 => 'Day',
    172800 => '2 days',
    259200 => '3 days',
    604800 => 'Week',
    209600 => '2 weeks',
    2629743 => 'Month'
];

// From: https://gist.github.com/Xeoncross/1204255
$regions = [
    'Africa' => DateTimeZone::AFRICA,
    'America' => DateTimeZone::AMERICA,
    'Antarctica' => DateTimeZone::ANTARCTICA,
    'Aisa' => DateTimeZone::ASIA,
    'Atlantic' => DateTimeZone::ATLANTIC,
    'Europe' => DateTimeZone::EUROPE,
    'Indian' => DateTimeZone::INDIAN,
    'Pacific' => DateTimeZone::PACIFIC
];

$timezones = [];
foreach ($regions as $name => $mask) {
    $zones = DateTimeZone::listIdentifiers($mask);
    foreach ($zones as $timezone) {
        // Lets sample the time there right now
        $time = new \DateTime(NULL, new \DateTimeZone($timezone));

        // Us dumb Americans can't handle millitary time
        $ampm = $time->format('H') > 12 ? ' (' . $time->format('g:i a') . ')' : '';

        // Remove region name and add a sample time
        $timezones[$name][$timezone] = substr($timezone, strlen($name) + 1) . ' - ' . $time->format('H:i') . $ampm;
    }
}

$app->group('/cron', function () use($app, $css, $js) {

    try {
        if (!Validate::table('cronjob_setting')->exists()) {
            Node::dispense('cronjob_setting');
        }

        if (!Validate::table('cronjob_handler')->exists()) {
            Node::dispense('cronjob_handler');
        }
    } catch (NodeQException $e) {
        _tc_flash()->error($e->getMessage());
    }

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET', '/', function () {
        if (!hasPermission('access_cronjob_screen')) {
            _tc_flash()->error(_t("You don't have permission to view the Cronjob Handler screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/', function () use($app, $css, $js) {

        if ($app->req->isPost()) {
            foreach ($app->req->post['cronjobs'] as $job) {
                try {
                    Node::table('cronjob_handler')->find($job)->delete();
                } catch (NodeQException $e) {
                    _tc_flash()->error($e->getMessage());
                }
            }
            redirect($app->req->server['HTTP_REFERER']);
        }

        try {
            $set = Node::table('cronjob_setting')->findAll();
            $jobs = Node::table('cronjob_handler')->findAll();
        } catch (NodeQException $e) {
            _tc_flash()->error($e->getMessage());
        }

        tc_register_style('datatables');
        tc_register_style('iCheck');
        tc_register_script('datatables');
        tc_register_script('iCheck');

        $app->view->display('cron/index', [
            'title' => _t('Cronjob Handlers'),
            'jobs' => $jobs,
            'set' => $set
        ]);
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET|POST', '/create/', function () {
        if (!hasPermission('access_cronjob_screen')) {
            _tc_flash()->error(_t("You don't have permission to view the Cronjob Handler screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/create/', function () use($app, $css, $js) {
        if ($app->req->isPost()) {
            if (filter_var($app->req->post['url'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                try {
                    $url = Node::table('cronjob_handler')
                            ->where('url', '=', $app->req->_post('url'))
                            ->find();
                } catch (NodeQException $e) {
                    _tc_flash()->error($e->getMessage(), get_base_url() . 'cron/');
                    exit();
                }

                $found = false;
                if ($url->count() > 0) {
                    $found = true;
                }

                if ($found == false) {
                    if ($app->req->_post('each') == '') {
                        _tc_flash()->error(_t('Time setting missing, please add time settings.'), get_base_url() . 'cron/');
                    } else {

                        try {
                            $cron = Node::table('cronjob_handler');
                            $cron->name = (string) $app->req->_post('name');
                            $cron->url = (string) $app->req->_post('url');
                            $cron->each = (int) $app->req->_post('each');
                            $cron->eachtime = ((isset($app->req->post['eachtime']) && preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/', $app->req->post['eachtime'])) ? $app->req->post['eachtime'] : '');
                            $cron->status = (int) $app->req->post['status'];
                            $cron->save();

                            _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'cron/');
                        } catch (NodeQException $e) {
                            _tc_flash()->error($e->getMessage(), get_base_url() . 'cron/');
                        }
                    }
                } else {
                    _tc_flash()->error(_t('Cronjob handler already exists in the system.'), get_base_url() . 'cron/');
                }
            } else {
                _tc_flash()->error(_t('Cronjob URL is wrong.'), get_base_url() . 'cron/');
            }
        }

        tc_register_style('select2');
        tc_register_script('select2');

        $app->view->display('cron/create', [
            'title' => _t('New Cronjob Handler')
        ]);
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET', '/(\d+)/reset/', function () {
        if (!hasPermission('access_cronjob_screen')) {
            _tc_flash()->error(_t("You don't have permission to view the Cronjob Handler screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->get('/(\d+)/reset/', function ($id) {
        try {
            $reset = Node::table('cronjob_handler')->find($id);
            $reset->runned = (int) 0;
            $reset->save();
            _tc_flash()->success(_tc_flash()->notice(200), get_base_url() . 'cron' . '/');
        } catch (NodeQException $e) {
            _tc_flash()->error($e->getMessage(), get_base_url() . 'cron' . '/');
        }
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET|POST', '/setting/', function () {
        if (!hasPermission('access_cronjob_screen')) {
            _tc_flash()->error(_t("You don't have permission to view the Cronjob Handler screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/setting/', function () use($app, $css, $js) {

        if ($app->req->isPost()) {
            $good = true;

            if (strlen(trim($app->req->post['cronjobpassword'])) < 2) {
                _tc_flash()->error(_t('Cronjobs cannot run without a password. Your cronjob password contains wrong characters, minimum of 4 letters and numbers.'));
                $good = false;
            }

            if ($good == true) {
                try {
                    $cron = Node::table('cronjob_setting')->find(1);
                    $cron->cronjobpassword = (string) $app->req->_post('cronjobpassword');
                    $cron->timeout = (isset($app->req->post['timeout']) && is_numeric($app->req->post['timeout']) ? (int) $app->req->_post('timeout') : 30);
                    $cron->save();

                    _tc_flash()->success(_tc_flash()->notice(200));
                } catch (NodeQException $e) {
                    _tc_flash()->error($e->getMessage());
                }
            }
        }

        try {
            $set = Node::table('cronjob_setting')->find(1);
        } catch (NodeQException $e) {
            _tc_flash()->error($e->getMessage());
        }

        tc_register_style('select2');
        tc_register_script('select2');

        $app->view->display('cron/setting', [
            'title' => _t('Cronjob Handler Settings'),
            'data' => $set
        ]);
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET|POST', '/(\d+)/', function () {
        if (!hasPermission('access_cronjob_screen')) {
            _tc_flash()->error(_t("You don't have permission to view the Cronjob Handler screen."), get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/(\d+)/', function ($id) use($app, $css, $js) {
        if ($app->req->isPost()) {
            if (filter_var($app->req->post['url'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {

                try {
                    $cron = Node::table('cronjob_handler')->find($id);
                    $cron->name = (string) $app->req->_post('name');
                    $cron->url = (string) $app->req->_post('url');
                    $cron->each = (int) $app->req->_post('each');
                    $cron->eachtime = ((isset($app->req->post['eachtime']) && preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/', $app->req->post['eachtime'])) ? $app->req->post['eachtime'] : '');
                    $cron->status = (int) $app->req->post['status'];
                    $cron->save();

                    _tc_flash()->success(_tc_flash()->notice(200));
                } catch (NodeQException $e) {
                    _tc_flash()->error($e->getMessage());
                }
            } else {
                _tc_flash()->error(_t('Current URL is not correct; must begin with http(s):// and followed with a path.'));
            }
        }

        try {
            $sql = Node::table('cronjob_handler')->find($id);
        } catch (NodeQException $e) {
            _tc_flash()->error($e->getMessage());
        }

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($sql == false) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($sql) == true) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If data is zero, 404 not found.
         */ elseif (count(_escape($sql->id)) <= 0) {

            $app->view->display('error/404', ['title' => '404 Error']);
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            tc_register_style('select2');
            tc_register_script('select2');

            $app->view->display('cron/view', [
                'title' => _t('View Cronjob Handler'),
                'cron' => $sql
                    ]
            );
        }
    });

    $app->get('/cronjob/', function () use($app) {

        try {
            $setting = Node::table('cronjob_setting')->find(1);
            $cron = Node::table('cronjob_handler')->where('status', '=', (int) 1)->findAll();

            if (!isset($app->req->get['password']) && !isset($argv[1])) {
                Cascade::getLogger('system_email')->alert(sprintf('CRONSTATE[401]: Unauthorized: %s', _t('No cronjob password found, use cronjob?password=<yourpassword>.')));
                exit(_t('No cronjob handler password found, use cronjob?password=<yourpassword>.'));
            } elseif (isset($app->req->get['password']) && $app->req->get['password'] != _escape($setting->cronjobpassword)) {
                Cascade::getLogger('system_email')->alert(sprintf('CRONSTATE[401]: Unauthorized: %s', _t('Invalid $_GET password')));
                exit(_t('Invalid $_GET password'));
            } elseif (_escape($setting->cronjobpassword) == 'changeme') {
                Cascade::getLogger('system_email')->alert(sprintf('CRONSTATE[401]: Unauthorized: %s', _t('Cronjob handler password needs to be changed.')));
                exit(_t('Cronjob handler password needs to be changed.'));
            } elseif (isset($argv[0]) && (substr($argv[1], 0, 8) != 'password' or substr($argv[1], 9) != _escape($setting->cronjobpassword))) {
                Cascade::getLogger('system_email')->alert(sprintf('CRONSTATE[401]: Unauthorized: %s', _t('Invalid argument password (password=yourpassword)')));
                exit(_t('Invalid argument password (password=yourpassword)'));
            }

            if (isset($run) && $run == true) {
                exit(_t('Cronjob already running'));
            }

            $run = true;

            if (is_object($cron) && count($cron) > 0) {
                $d = Jenssegers\Date\Date::now();
                // execute only one job and then exit
                foreach ($cron as $job) {

                    if (isset($app->req->get['id']) && _escape($job->id) == $app->req->get['id']) {
                        $run = true;
                    } else {
                        $run = false;
                        if ($job->time != '') {
                            if (substr(_escape($job->lastrun), 0, 10) != $d) {
                                if (strtotime($d->format('Y-m-d H:i')) > strtotime($d->format('Y-m-d ') . _escape($job->time))) {
                                    $run = true;
                                }
                            }
                        } elseif ($job->each > 0) {
                            if (strtotime(_escape($job->lastrun)) + _escape($job->each) < strtotime($d)) {
                                $run = true;
                                // if time set, daily after time...
                                if (_escape($job->each) > (60 * 60 * 24) && strlen(_escape($job->eachtime)) == 5 && strtotime($d->format('Y-m-d H:i')) < strtotime($d->format('Y-m-d') . _escape($job->eachtime))) {
                                    // only run 'today' at or after give time.
                                    $run = false;
                                }
                            }
                        } elseif (substr(_escape($job->lastrun), 0, 10) != $d->format('Y-m-d')) {
                            $run = true;
                        }
                    }

                    if ($run == true) {
                        // save as executed
                        Cascade::getLogger('alert')->alert(_t('Running: ') . _escape($job->url));

                        try {
                            $upd = Node::table('cronjob_handler')->find(_escape($job->id));
                            $upd->lastrun = $d->format('Y-m-d H:i:s');
                            $upd->runned ++;
                            $upd->save();
                        } catch (NodeQException $e) {
                            Cascade::getLogger('error')->error(sprintf('CRONSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
                        }

                        Cascade::getLogger('alert')->alert(_t('CRONSTATE: Connecting to cronjob'));

                        // execute cronjob
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, _escape($job->url));
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, (!empty($setting->timeout) ? $setting->timeout : 5));

                        curl_exec($ch);

                        if (curl_errno($ch)) {
                            Cascade::getLogger('system_email')->alert(sprintf('CRONSTATE[400]: Bad request: %s', curl_error($ch)));
                        } else {
                            Cascade::getLogger('alert')->alert(_t('Cronjob data loaded'));
                        }

                        curl_close($ch);
                    }
                }
            }
        } catch (NodeQException $e) {
            Cascade::getLogger('system_email')->alert(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('system_email')->alert(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    });

    $app->before('POST|PUT|DELETE|OPTIONS', '/master/', function () use($app) {
        header('Content-Type: application/json');
        $app->res->_format('json', 404);
        exit();
    });

    $app->get('/master/', function () {
        $jobby = new \Jobby\Jobby();

        try {
            $setting = Node::table('cronjob_setting')->find(1);

            // Every job has a name
            $jobby->add('MasterCronJob', [
                // Run a shell command
                'command' => '/usr/bin/curl -s ' . get_base_url() . 'cron/cronjob/?password=' . _escape($setting->cronjobpassword),
                // Ordinary crontab schedule format is supported.
                // This schedule runs every 5 minutes.
                // You could also insert DateTime string in the format of Y-m-d H:i:s.
                'schedule' => '*/5 * * * *',
                // Stdout and stderr is sent to the specified file
                'output' => APP_PATH . 'tmp/logs/tc-error-' . Jenssegers\Date\Date::now()->format('Y-m-d') . '.txt',
                // You can turn off a job by setting 'enabled' to false
                'enabled' => true,
            ]);

            $jobby->run();
        } catch (NodeQException $e) {
            Cascade::getLogger('system_email')->alert(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('system_email')->alert(sprintf('NODEQSTATE[%s]: %s', $e->getCode(), $e->getMessage()));
        }
    });

    $app->get('/purgeActivityLog/', function () {
        tc_logger_activity_log_purge();
    });

    $app->get('/purgeErrorLog/', function () use($app) {
        try {
            $app->db->error()
                    ->where('DATE_ADD(error.addDate, INTERVAL 5 DAY) <= ?', Jenssegers\Date\Date::now()->format('Y-m-d'))
                    ->delete();
        } catch (NotFoundException $e) {
            Cascade::getLogger('error')->error(sprintf('CRONSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        } catch (ORMException $e) {
            Cascade::getLogger('error')->error(sprintf('CRONSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('error')->error(sprintf('CRONSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        }

        tc_logger_error_log_purge();
    });

    $app->get('/runDBBackup/', function () use($app) {
        $dbhost = DB_HOST;
        $dbuser = DB_USER;
        $dbpass = DB_PASS;
        $dbname = DB_NAME;

        try {
            $backupDir = $app->config('file.savepath') . 'backups' . DS;
            if (!tc_file_exists($backupDir, false)) {
                try {
                    _mkdir($backupDir);
                } catch (IOException $e) {
                    Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
                }
            }
        } catch (\TinyC\Exception\IOException $e) {
            Cascade\Cascade::getLogger('system_email')->alert(sprintf('IOSTATE[%s]: Forbidden: %s', $e->getCode(), $e->getMessage()));
        }

        $backupFile = $backupDir . $dbname . '-' . Jenssegers\Date\Date::now()->format("Y-m-d-H-i-s") . '.gz';
        if (!tc_file_exists($backupFile, false)) {
            $command = "mysqldump --opt -h $dbhost -u $dbuser -p$dbpass $dbname | gzip > $backupFile";
            system($command);
        }
        $files = glob($backupDir . "*.gz");
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file) && time() - filemtime($file) >= 20 * 24 * 3600) { // 20 days
                    unlink($file);
                }
            }
        }
    });

    $app->get('/runEmailQueue/', function () use($app) {
        try {
            $cpgn = $app->db->campaign()
                    ->where('campaign.status = "processing"')
                    ->findOne();

            if ($cpgn != false) {
                try {
                    /**
                     * Checks if any unsent emails are left in the queue.
                     * If not, mark campaign as `sent`.
                     */
                    $sent = $app->db->campaign_queue()
                            ->where('is_sent', 'false')->_and_()
                            ->where('is_cancelled', 'false')->_and_()
                            ->where('is_blocked', 'false')->_and_()
                            ->where('cid = ?', _escape($cpgn->id))
                            ->count();
                    if ($sent <= 0 && _escape($cpgn->status) != 'sent') {
                        $complete = $app->db->campaign()
                                ->where('id = ?', _escape($cpgn->id))->_and_()
                                ->where('status <> "sent"')
                                ->findOne();
                        $complete->set([
                                    'status' => 'sent'
                                ])
                                ->update();
                        return true;
                    }

                    // instantiate the message queue
                    $queue = new \TinyC\tc_Queue();

                    // get messages from the queue
                    $messages = $queue->getEmails();
                    $i = 0;
                    $last = $app->db->campaign_queue()->where('cid = ?', _escape($cpgn->id))->orderBy('id', 'DESC')->findOne();
                    //$last = Node::table(_escape($cpgn->node))->where('cid', '=', _escape($cpgn->id))->orderBy('id', 'DESC')->limit(1)->find();
                    // iterate messages
                    foreach ($messages as $message) {
                        $sub = get_subscriber_by('email', $message->getToEmail());
                        $slist = $app->db->subscriber_list()
                                ->where('subscriber_list.lid = ?', $message->getListId())->_and_()
                                ->where('subscriber_list.sid = ?', $message->getSubscriberId())
                                ->findOne();

                        $list = get_list_by('id', $message->getListId());
                        $server = get_server_info(_escape($list->server));

                        /**
                         * Generate slug from subject. Useful for Google Analytics.
                         */
                        $slug = _tc_unique_campaign_slug(_escape($cpgn->subject));
                        /**
                         * Create an array to merge later.
                         */
                        $custom_headers = [
                            'xcampaignid' => $message->getMessageId(),
                            'xlistid' => $message->getListId(),
                            'xsubscriberid' => $message->getSubscriberId(),
                            'xsubscriberemail' => $message->getToEmail(),
                            'slist_code' => $slist->code,
                            'unsub_mailto' => $list->unsub_mailto,
                            'feedbackid' => _escape($cpgn->id) . _escape($sub->id) . ':' . _escape($sub->id) . ':campaign:' . _escape($cpgn->owner),
                            'uniqueid' => $message->getId()
                        ];
                        $footer = _escape($cpgn->footer);
                        $footer = str_replace('{email}', _escape($sub->email), $footer);
                        $footer = str_replace('{from_email}', _escape($cpgn->from_email), $footer);
                        $footer = str_replace('{personal_preferences}', get_base_url() . 'preferences/' . _escape($sub->code) . '/subscriber/' . _escape($sub->id) . '/', $footer);
                        $footer = str_replace('{unsubscribe_url}', get_base_url() . 'unsubscribe/' . _escape($slist->code) . '/lid/' . _escape($slist->lid) . '/sid/' . _escape($slist->sid) . '/rid/' . _escape($message->getId()) . '/cid/' . _escape($cpgn->id) . '/', $footer);

                        $msg = _escape($cpgn->html);
                        $msg = str_replace('{todays_date}', \Jenssegers\Date\Date::now()->format('M d, Y'), $msg);
                        $msg = str_replace('{list_id}', $message->getListId(), $msg);
                        $msg = str_replace('{subscriber_id}', $message->getSubscriberId(), $msg);
                        $msg = str_replace('{subscriber_code}', _escape($sub->code), $msg);
                        $msg = str_replace('{campaign_id}', $message->getMessageId(), $msg);
                        $msg = str_replace('{subject}', _escape($cpgn->subject), $msg);
                        $msg = str_replace('{view_online}', '<a class="view_online" href="' . get_base_url() . 'archive/' . _escape($cpgn->id) . '/">' . _t('View this email in your browser') . '</a>', $msg);
                        $msg = str_replace('{first_name}', _escape($sub->fname), $msg);
                        $msg = str_replace('{last_name}', _escape($sub->lname), $msg);
                        $msg = str_replace('{email}', _escape($sub->email), $msg);
                        $msg = str_replace('{address1}', _escape($sub->address1), $msg);
                        $msg = str_replace('{address2}', _escape($sub->address2), $msg);
                        $msg = str_replace('{city}', _escape($sub->city), $msg);
                        $msg = str_replace('{state}', _escape($sub->state), $msg);
                        $msg = str_replace('{postal_code}', _escape($sub->postal_code), $msg);
                        $msg = str_replace('{country}', _escape($sub->country), $msg);
                        $msg = str_replace('{unsubscribe_url}', '<a class="unsub_url" href="' . get_base_url() . 'unsubscribe/' . _escape($slist->code) . '/lid/' . _escape($slist->lid) . '/sid/' . _escape($slist->sid) . '/rid/' . _escape($message->getId()) . '/cid/' . _escape($cpgn->id) . '/">' . _t('unsubscribe') . '</a>', $msg);
                        $msg = str_replace('{personal_preferences}', '<a class="personal_prefs" href="' . get_base_url() . 'preferences/' . _escape($sub->code) . '/subscriber/' . _escape($sub->id) . '/">' . _t('preferences page') . '</a>', $msg);
                        $msg .= $footer;
                        $msg .= tinyc_footer_logo();
                        $msg .= campaign_tracking_code(_escape($cpgn->id), _escape($sub->id));

                        if (++$i === 1) {
                            $q = $app->db->campaign()
                                    ->where('id = ?', _escape($cpgn->id))
                                    ->findOne();
                            $finish = strtotime($last->timestamp_to_send);
                            $q->set([
                                'sendfinish' => date("Y-m-d H:i:s", strtotime('+10 minutes', $finish))
                            ])->update();
                        }
                        /**
                         * Turn server object to array, join with another 
                         * array, and then merge them back into an object.
                         */
                        $data = [];
                        foreach ($server as $k => $v) {
                            $data[$k] = $v;
                        }
                        $obj_merged = (object) array_merge($custom_headers, $data);
                        // send email
                        $app->hook->{'do_action_array'}('tinyc_email_init', [
                            $obj_merged,
                            $message->getToEmail(),
                            _escape($cpgn->subject),
                            tc_link_tracking($msg, _escape($cpgn->id), _escape($sub->id), $slug),
                            tc_link_tracking(_escape($cpgn->text), _escape($cpgn->id), _escape($sub->id), $slug),
                            $message
                                ]
                        );
                    }
                } catch (ORMException $e) {
                    Cascade::getLogger('system_email')->alert(sprintf('QUEUESTATE[%s]: Conflict: %s', $e->getCode(), $e->getMessage()));
                } catch (Exception $e) {
                    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
                }
            }
        } catch (ORMException $e) {
            Cascade::getLogger('system_email')->alert(sprintf('QUEUESTATE[%s]: Conflict: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('system_email')->alert(sprintf('QUEUESTATE[%s]: Conflict: %s', $e->getCode(), $e->getMessage()));
        }
    });

    $app->get('/runBounceHandler/', function () {
        try {
            $node = Node::table('php_encryption')->find(1);
        } catch (NodeQException $e) {
            Cascade::getLogger('system_email')->alert(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        } catch (NotFoundException $e) {
            Cascade::getLogger('system_email')->alert(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('system_email')->alert(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        }

        try {
            $password = Crypto::decrypt(_escape(get_option('tc_bmh_password')), Key::loadFromAsciiSafeString($node->key));
        } catch (Defuse\Crypto\Exception\BadFormatException $e) {
            Cascade::getLogger('system_email')->alert(sprintf('BOUNCESTATE[%s]: Conflict: %s', $e->getCode(), $e->getMessage()));
        } catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $e) {
            Cascade::getLogger('system_email')->alert(sprintf('BOUNCESTATE[%s]: Conflict: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('system_email')->alert(sprintf('BOUNCESTATE[%s]: Conflict: %s', $e->getCode(), $e->getMessage()));
        }
        $time_start = microtime_float();

        $bmh = new TinyC\tc_BounceHandler();
        $bmh->actionFunction = 'bounce_callback_action'; // default is 'bounce_callback_action'
        $bmh->verbose = TinyC\tc_BounceHandler::VERBOSE_SIMPLE; //TinyC\tc_BounceHandler::VERBOSE_SIMPLE; //TinyC\tc_BounceHandler::VERBOSE_REPORT; //TinyC\tc_BounceHandler::VERBOSE_DEBUG; //TinyC\tc_BounceHandler::VERBOSE_QUIET; // default is BounceMailHandler::VERBOSE_SIMPLE
        //$bmh->useFetchStructure  = true; // true is default, no need to specify
        //$bmh->testMode           = false; // false is default, no need to specify
        //$bmh->debugBodyRule      = false; // false is default, no need to specify
        //$bmh->debugDsnRule       = false; // false is default, no need to specify
        //$bmh->purgeUnprocessed   = false; // false is default, no need to specify
        $bmh->disableDelete = true; // false is default, no need to specify

        /*
         * for remote mailbox
         */
        $bmh->mailhost = _escape(get_option('tc_bmh_host')); // your mail server
        $bmh->mailboxUserName = _escape(get_option('tc_bmh_username')); // your mailbox username
        $bmh->mailboxPassword = $password; // your mailbox password
        $bmh->port = _escape(get_option('tc_bmh_port')); // the port to access your mailbox, default is 143
        $bmh->service = _escape(get_option('tc_bmh_service')); // the service to use (imap or pop3), default is 'imap'
        $bmh->serviceOption = _escape(get_option('tc_bmh_service_option')); // the service options (none, tls, notls, ssl, etc.), default is 'notls'
        $bmh->boxname = (_escape(get_option('tc_bmh_mailbox')) == '' ? 'INBOX' : _escape(get_option('tc_bmh_mailbox'))); // the mailbox to access, default is 'INBOX'
        $bmh->moveHard = true; // default is false
        $bmh->hardMailbox = (_escape(get_option('tc_bmh_mailbox')) == '' ? 'INBOX' : _escape(get_option('tc_bmh_mailbox'))) . '.hard'; // default is 'INBOX.hard' - NOTE: must start with 'INBOX.'
        $bmh->moveSoft = false; // default is false
        $bmh->softMailbox = ''; // default is 'INBOX.soft' - NOTE: must start with 'INBOX.'
        $bmh->openMailbox();
        $bmh->processMailbox();
        $bmh->deleteMsgDate = Jenssegers\Date\Date::now()->format('yyyy-mm-dd H:i:s'); // format must be as 'yyyy-mm-dd'

        $time_end = microtime_float();
        $time = $time_end - $time_start;

        Cascade::getLogger('info')->info('BOUNCES[401]: ' . sprintf(_t('Seconds to process: %s'), $time));
    });

    $app->get('/runNodeQ/', function () {
        send_confirm_email();
        send_subscribe_email();
        send_unsubscribe_email();
        new_subscriber_notify_email();
        move_old_nodes_to_queue_node();
        check_rss_campaigns();
        generate_rss_campaigns();
    });
});

$app->setError(function () use($app) {

    $app->view->display('error/404', [
        'title' => '404 Error'
    ]);
});
