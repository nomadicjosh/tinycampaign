<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;
use app\src\NodeQ\Helpers\Validate as Validate;
use Cascade\Cascade;
use app\src\Exception\NotFoundException;
use app\src\Exception\Exception;
use PDOException as ORMException;

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
            'title' => 'Cronjob Handlers',
            'jobs' => $jobs,
            'set' => $set
        ]);
    });

    $app->match('GET|POST', '/create/', function () use($app, $css, $js) {
        if ($app->req->isPost()) {
            if (filter_var($app->req->post['url'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                try {
                    $url = Node::table('cronjob_handler')
                        ->where('url', '=', $app->req->_post('url'))
                        ->find();
                } catch (NodeQException $e) {
                    _tc_flash()->error($e->getMessage(), redirect(get_base_url() . 'cron/'));
                    exit();
                }

                $found = false;
                if ($url->count() > 0) {
                    $found = true;
                }

                if ($found == false) {
                    if ($app->req->_post('each') == '') {
                        _tc_flash()->error(_t('Time setting missing, please add time settings.'), redirect(get_base_url() . 'cron/'));
                    } else {

                        try {
                            $cron = Node::table('cronjob_handler');
                            $cron->name = (string) $app->req->_post('name');
                            $cron->url = (string) $app->req->_post('url');
                            $cron->each = (int) $app->req->_post('each');
                            $cron->eachtime = ((isset($app->req->post['eachtime']) && preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/', $app->req->post['eachtime'])) ? $app->req->post['eachtime'] : '');
                            $cron->save();

                            _tc_flash()->success(_tc_flash()->notice(200), redirect(get_base_url() . 'cron/'));
                        } catch (NodeQException $e) {
                            _tc_flash()->error($e->getMessage(), redirect(get_base_url() . 'cron/'));
                        }
                    }
                } else {
                    _tc_flash()->error(_t('Cronjob handler already exists in the system.'), redirect(get_base_url() . 'cron/'));
                }
            } else {
                _tc_flash()->error(_t('Cronjob URL is wrong.'), redirect(get_base_url() . 'cron/'));
            }
        }

        tc_register_style('select2');
        tc_register_script('select2');

        $app->view->display('cron/create', [
            'title' => 'New Cronjob Handler'
        ]);
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
            'title' => 'Cronjob Handler Settings',
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
         */ elseif (count($sql->id) <= 0) {

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
                'title' => 'View Cronjob Handler',
                'cron' => $sql
                ]
            );
        }
    });

    $app->get('/cronjob/', function () use($app) {

        try {
            $setting = Node::table('cronjob_setting')->find(1);
            $cron = Node::table('cronjob_handler')->findAll();
        } catch (NodeQException $e) {
            _tc_flash()->error($e->getMessage());
        }

        if (!isset($_GET['password']) && !isset($argv[1])) {
            Cascade::getLogger('system_email')->alert(sprintf('CRONSTATE[401]: Unauthorized: %s', _t('No cronjob password found, use cronjob?password=<yourpassword>.')));
            exit(_t('No cronjob handler password found, use cronjob?password=<yourpassword>.'));
        } elseif (isset($_GET['password']) && $_GET['password'] != $setting->cronjobpassword) {
            Cascade::getLogger('system_email')->alert(sprintf('CRONSTATE[401]: Unauthorized: %s', _t('Invalid $_GET password')));
            exit(_t('Invalid $_GET password'));
        } elseif ($setting->cronjobpassword == 'changeme') {
            Cascade::getLogger('system_email')->alert(sprintf('CRONSTATE[401]: Unauthorized: %s', _t('Cronjob handler password needs to be changed.')));
            exit(_t('Cronjob handler password needs to be changed.'));
        } elseif (isset($argv[0]) && (substr($argv[1], 0, 8) != 'password' or substr($argv[1], 9) != $setting->cronjobpassword)) {
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

                if (isset($_GET['id']) && $job->id == $_GET['id']) {
                    $run = true;
                } else {
                    $run = false;
                    if ($job->time != '') {
                        if (substr($job->lastrun, 0, 10) != $d) {
                            if (strtotime($d->format('Y-m-d H:i')) > strtotime($d->format('Y-m-d ') . $job->time)) {
                                $run = true;
                            }
                        }
                    } elseif ($job->each > 0) {
                        if (strtotime($job->lastrun) + $job->each < strtotime($d)) {
                            $run = true;
                            // if time set, daily after time...
                            if ($job->each > (60 * 60 * 24) && strlen($job->eachtime) == 5 && strtotime($d->format('Y-m-d H:i')) < strtotime($d->format('Y-m-d') . $job->eachtime)) {
                                // only run 'today' at or after give time.
                                $run = false;
                            }
                        }
                    } elseif (substr($job->lastrun, 0, 10) != $d->format('Y-m-d')) {
                        $run = true;
                    }
                }

                if ($run == true) {
                    // save as executed
                    echo _t('Running: ') . $job->url . PHP_EOL . PHP_EOL;

                    try {
                        $upd = Node::table('cronjob_handler')->find($job->id);
                        $upd->lastrun = $d->format('Y-m-d H:i:s');
                        $upd->runned ++;
                        $upd->save();
                    } catch (NodeQException $e) {
                        _tc_flash()->error($e->getMessage(), redirect(get_base_url() . 'cron/'));
                    }

                    echo _t('Connecting to cronjob') . PHP_EOL . PHP_EOL;

                    // execute cronjob
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $job->url);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, (!empty($setting->timeout) ? $setting->timeout : 5));

                    curl_exec($ch);

                    if (curl_errno($ch)) {
                        Cascade::getLogger('system_email')->alert(sprintf('CRONSTATE[400]: Bad request: %s', curl_error($ch)));
                        echo _t('Cronjob error: ') . curl_error($ch) . PHP_EOL;
                    } else {
                        echo _t('Cronjob data loaded') . PHP_EOL;
                    }

                    curl_close($ch);
                }
            }
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
            _tc_flash()->error($e->getMessage());
        } catch (Exception $e) {
            _tc_flash()->error($e->getMessage());
        } catch (ORMException $e) {
            _tc_flash()->error($e->getMessage());
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
                _mkdir($backupDir);
            }
        } catch (\app\src\Exception\IOException $e) {
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
        $domain = get_domain_name();
        $site = _h(get_option('system_name'));

        try {
            $cpgn = $app->db->campaign()
                ->where('campaign.status = "processing"')
                ->findOne();
            
            if ($cpgn->id > 0) {

                try {
                    // instantiate the message queue
                    $queue = new \app\src\tc_Queue();
                    $queue->node = $cpgn->node;

                    // get messages from the queue
                    $messages = $queue->getEmails();
                    // iterate messages
                    $numItems = $queue->getUnsentEmailCount();
                    $i = 0;
                    $last = Node::table($cpgn->node)->where('id', '=', $numItems)->find();
                    foreach ($messages as $message) {
                        $sub = get_subscriber_by('email', $message->getToEmail());
                        $slist = $app->db->subscriber_list()
                            ->where('subscriber_list.lid = ?', $message->getListId())->_and_()
                            ->where('subscriber_list.sid = ?', $message->getSubscriberId())
                            ->findOne();

                        $footer = _escape($cpgn->footer);
                        $footer = str_replace('{email}', $sub->email, $footer);
                        $footer = str_replace('{from_email}', $cpgn->from_email, $footer);
                        $footer = str_replace('{personal_preferences}', get_base_url() . 'preferences/' . $sub->code . '/subscriber/' . $sub->id . '/', $footer);
                        $footer = str_replace('{unsubscribe_url}', get_base_url() . 'unsubscribe/' . $slist->code . '/lid/' . $slist->lid . '/sid/' . $slist->sid . '/', $footer);

                        $msg = _escape($cpgn->html);
                        $msg = str_replace('{todays_date}', \Jenssegers\Date\Date::now()->format('M d, Y'), $msg);
                        $msg = str_replace('{view_online}', '<a href="' . get_base_url() . 'archive/' . $cpgn->id . '/">' . _t('View this email in your browser') . '</a>', $msg);
                        $msg = str_replace('{first_name}', $sub->fname, $msg);
                        $msg = str_replace('{last_name}', $sub->lname, $msg);
                        $msg = str_replace('{email}', $sub->email, $msg);
                        $msg = str_replace('{address1}', $sub->address1, $msg);
                        $msg = str_replace('{address2}', $sub->address2, $msg);
                        $msg = str_replace('{city}', $sub->city, $msg);
                        $msg = str_replace('{state}', $sub->state, $msg);
                        $msg = str_replace('{postal_code}', $sub->postal_code, $msg);
                        $msg = str_replace('{country}', $sub->country, $msg);
                        $msg = str_replace('{unsubscribe_url}', '<a href="' . get_base_url() . 'unsubscribe/' . $slist->code . '/lid/' . $slist->lid . '/sid/' . $slist->sid . '/">'._t('unsubscribe').'</a>', $msg);
                        $msg = str_replace('{personal_preferences}', '<a href="' . get_base_url() . 'preferences/' . $sub->code . '/subscriber/' . $sub->id . '/">'._t('preferences page').'</a>', $msg);
                        $msg .= $footer;
                        $msg .= campaign_tracking_code($message);
                        $headers = "From: $site <auto-reply@$domain>\r\n";
                        $headers .= "Return-Path: " . (_h(get_option('tc_bmh_username')) == '' ? _h(get_option('system_email')) : _h(get_option('tc_bmh_username'))) . "\r\n";
                        if (_h(get_option('tc_smtp_status')) == 0) {
                            $headers .= "X-Mailer: tinyCampaign " . CURRENT_RELEASE . "\r\n";
                            $headers .= "MIME-Version: 1.0" . "\r\n";
                        }
                        // send email
                        _tc_email()->tc_mail(
                            $message->getToEmail(), $cpgn->subject, $msg, $headers
                        );

                        $q = $app->db->campaign()
                            ->where('node = ?', $queue->getNode())
                            ->findOne();
                        $q->recipients = $q->recipients + 1;
                        if (++$i === 1) {
                            $q->sendfinish = $last->timestamp_to_send;
                        }
                        if (++$i === $numItems) {
                            $q->status = 'sent';
                        }
                        $q->update();

                        // remove message from the queue by updating is_sent value
                        $queue->setMessageIsSent($message);
                    }
                } catch (NodeQException $e) {
                    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
                } catch (InvalidArgumentException $e) {
                    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
                } catch (Exception $e) {
                    Cascade::getLogger('error')->error(sprintf('NODEQSTATE[%s]: Error: %s', $e->getCode(), $e->getMessage()));
                }
            }
        } catch (NotFoundException $e) {
            Cascade::getLogger('system_email')->alert(sprintf('QUEUESTATE[%s]: Conflict: %s', $e->getCode(), $e->getMessage()));
        } catch (Exception $e) {
            Cascade::getLogger('system_email')->alert(sprintf('QUEUESTATE[%s]: Conflict: %s', $e->getCode(), $e->getMessage()));
        } catch (ORMException $e) {
            Cascade::getLogger('system_email')->alert(sprintf('QUEUESTATE[%s]: Conflict: %s', $e->getCode(), $e->getMessage()));
        }
    });

    $app->get('/runBounceHandler/', function () {
        $time_start = microtime_float();

        $bmh = new app\src\tc_BounceHandler();
        $bmh->action_function = 'bounce_callback_action'; // default is 'bounce_callback_action'
        $bmh->verbose = VERBOSE_SIMPLE; //VERBOSE_REPORT; //VERBOSE_DEBUG; //VERBOSE_QUIET; // default is VERBOSE_SIMPLE
        $bmh->use_fetchstructure = true; // true is default, no need to speficy
        $bmh->testmode = false; // false is default, no need to specify
        $bmh->debug_body_rule = false; // false is default, no need to specify
        $bmh->debug_dsn_rule = false; // false is default, no need to specify
        $bmh->purge_unprocessed = false; // false is default, no need to specify
        $bmh->disable_delete = false; // false is default, no need to specify

        /*
         * for remote mailbox
         */
        $bmh->mailhost = _h(get_option('tc_bmh_host')); // your mail server
        $bmh->mailbox_username = _h(get_option('tc_bmh_username')); // your mailbox username
        $bmh->mailbox_password = _h(get_option('tc_bmh_password')); // your mailbox password
        $bmh->port = _h(get_option('tc_bmh_port')); // the port to access your mailbox, default is 143
        $bmh->service = _h(get_option('tc_bmh_service')); // the service to use (imap or pop3), default is 'imap'
        $bmh->service_option = _h(get_option('tc_bmh_service_option')); // the service options (none, tls, notls, ssl, etc.), default is 'notls'
        $bmh->boxname = (_h(get_option('tc_bmh_mailbox')) == '' ? 'INBOX' : _h(get_option('tc_bmh_mailbox'))); // the mailbox to access, default is 'INBOX'
        $bmh->moveHard = true; // default is false
        $bmh->hardMailbox = (_h(get_option('tc_bmh_mailbox')) == '' ? 'INBOX' : _h(get_option('tc_bmh_mailbox'))) . '.hard'; // default is 'INBOX.hard' - NOTE: must start with 'INBOX.'
        $bmh->moveSoft = false; // default is false
        $bmh->softMailbox = ''; // default is 'INBOX.soft' - NOTE: must start with 'INBOX.'
        $bmh->deleteMsgDate = Jenssegers\Date\Date::now()->format('yyyy-mm-dd H:i:s'); // format must be as 'yyyy-mm-dd'
        $bmh->openMailbox();
        $bmh->processMailbox();

        $time_end = microtime_float();
        $time = $time_end - $time_start;

        Cascade::getLogger('info')->info('BOUNCES[401]: ' . sprintf(_t('Seconds to process: '), $time));
    });

    $app->get('/runNodeQ/', function () {
        process_queued_campaign();
        send_confirm_email();
        send_subscribe_email();
        send_unsubscribe_email();
    });
});

$app->setError(function () use($app) {

    $app->view->display('error/404', [
        'title' => '404 Error'
    ]);
});
