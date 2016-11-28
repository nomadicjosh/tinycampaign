<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\Helpers\Validate as Validate;
use Cascade\Cascade;

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

$email = _tc_email();
$flashNow = new \app\src\tc_Messages();

$app->group('/cron', function () use($app, $css, $js, $email, $flashNow) {

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET', '/', function () {
        if (!hasPermission('access_cronjob_screen')) {
            redirect(get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/', function () use($app, $css, $js) {
        if (!Validate::table('cronjob_setting')->exists()) {
            Node::dispense('cronjob_setting');
        }

        if (!Validate::table('cronjob_handler')->exists()) {
            Node::dispense('cronjob_handler');
        }

        $set = Node::table('cronjob_setting')->findAll();
        $job = Node::table('cronjob_handler')->findAll();

        if ($app->req->isPost()) {
            foreach ($_POST['cronjobs'] as $job) {
                Node::table('cronjob_handler')->find($job)->delete();
            }
            redirect($app->req->server['HTTP_REFERER']);
        }

        $app->view->display('cron/index', [
            'title' => 'Cronjob Handlers',
            'cssArray' => $css,
            'jsArray' => $js,
            'cron' => $job,
            'set' => $set
        ]);
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET|POST', '/(\d+)/', function () {
        if (!hasPermission('access_cronjob_screen')) {
            redirect(get_base_url() . 'dashboard' . '/');
        }
    });

    $app->match('GET|POST', '/new/', function () use($app, $css, $js, $flashNow) {
        if ($app->req->isPost()) {
            if (filter_var($_POST['url'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
                $url = Node::table('cronjob_handler')->where('url', '=', $app->req->_post('url'))->find();
                $found = false;
                if ($url->count() > 0) {
                    $found = true;
                }

                if ($found == false) {
                    if ($app->req->_post('each') == '') {
                        $app->flash('error_message', _t('Time setting missing, please add time settings.'));
                    } else {

                        $cron = Node::table('cronjob_handler');
                        $cron->name = (string) $app->req->_post('name');
                        $cron->url = (string) $app->req->_post('url');
                        $cron->each = (int) $app->req->_post('each');
                        $cron->eachtime = ((isset($_POST['eachtime']) && preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/', $_POST['eachtime'])) ? $_POST['eachtime'] : '');
                        $cron->save();

                        if ($cron) {
                            $app->flash('success_message', $flashNow->notice(200));
                        } else {
                            $app->flash('error_message', $flashNow->notice(409));
                        }
                    }
                } else {
                    $app->flash('error_message', _t('Cronjob handler already exists in the system.'));
                }
            } else {
                $app->flash('error_message', _t('Cronjob URL is wrong.'));
            }
            redirect(get_base_url() . 'cron/');
        }

        $app->view->display('cron/new', [
            'title' => 'New Cronjob Handler',
            'cssArray' => $css,
            'jsArray' => $js
        ]);
    });

    $app->match('GET|POST', '/setting/', function () use($app, $css, $js, $flashNow) {

        if ($app->req->isPost()) {
            $good = true;

            if (strlen(trim($_POST['cronjobpassword'])) < 2) {
                $app->flash('error_message', _t('Cronjobs cannot run without a password. Your cronjob password contains wrong characters, minimum of 4 letters and numbers.'));
                $good = false;
            }

            if ($good == true) {
                $cron = Node::table('cronjob_setting')->find(1);
                $cron->cronjobpassword = (string) $app->req->_post('cronjobpassword');
                $cron->timeout = (isset($_POST['timeout']) && is_numeric($_POST['timeout']) ? (int) $app->req->_post('timeout') : 30);
                $cron->save();

                if ($cron) {
                    $app->flash('success_message', $flashNow->notice(200));
                } else {
                    $app->flash('error_message', $flashNow->notice(409));
                }
            }
            redirect($app->req->server['HTTP_REFERER']);
        }

        $set = Node::table('cronjob_setting')->find(1);

        $app->view->display('cron/setting', [
            'title' => 'Cronjob Handler Settings',
            'cssArray' => $css,
            'jsArray' => $js,
            'data' => $set
        ]);
    });

    $app->match('GET|POST', '/view/(\d+)/', function ($id) use($app, $css, $js, $flashNow) {
        if ($app->req->isPost()) {
            if (filter_var($_POST['url'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {

                $cron = Node::table('cronjob_handler')->find($id);
                $cron->name = (string) $app->req->_post('name');
                $cron->url = (string) $app->req->_post('url');
                $cron->each = (int) $app->req->_post('each');
                $cron->eachtime = ((isset($_POST['eachtime']) && preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/', $_POST['eachtime'])) ? $_POST['eachtime'] : '');
                $cron->save();

                if ($cron) {
                    $app->flash('success_message', $flashNow->notice(200));
                } else {
                    $app->flash('error_message', $flashNow->notice(409));
                }
            } else {
                $app->flash('error_message', _t('Current URL is not correct; must begin with http(s):// and followed with a path.'));
            }

            redirect($app->req->server['HTTP_REFERER']);
        }

        $sql = Node::table('cronjob_handler')->find($id);

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

            $app->view->display('cron/view', [
                'title' => 'View Cronjob Handler',
                'cssArray' => $css,
                'jsArray' => $js,
                'cron' => $sql
                ]
            );
        }
    });

    $app->get('/cronjob/', function () use($app, $email) {

        $setting = Node::table('cronjob_setting')->find(1);
        $cron = Node::table('cronjob_handler')->findAll();

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

                    $upd = Node::table('cronjob_handler')->find($job->id);
                    $upd->lastrun = $d->format('Y-m-d H:i:s');
                    $upd->runned ++;
                    $upd->save();

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
        $app->db->error()
            ->where('DATE_ADD(error.addDate, INTERVAL 5 DAY) <= ?', Jenssegers\Date\Date::now()->format('Y-m-d'))
            ->delete();

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

    $app->get('/runEmailQueue/', function () use($app, $email) {
        // instantiate the message queue
        $queue = new \app\src\tc_Queue();

        $last = Node::table($queue->getNode())->orderBy('id', 'DESC')->limit(1)->findAll();

        // get messages from the queue
        $messages = $queue->getEmails();

        $numItems = $queue->getUnsentEmailCount();
        $i = 0;

        // iterate messages
        foreach ($messages as $message) {
            // send email
            $email->tc_mail(
                $message->getToEmail(), $message->getSubject(), $message->getMessageHtml(), $message->getHeaders()
            );

            $q = $app->db->message();
            $q->recipients = +1;
            if (++$i === 1) {
                $q->sendfinish = $last->timestamp_to_send;
            }
            if (++$i === $numItems) {
                $q->status = 'sent';
            }
            $q->where('node = ?', $queue->getNode())
                ->update;

            // remove message from the queue by updating is_sent value
            $queue->setMessageIsSent($message);
        }
    });

    $app->get('/runBounceHandler/', function () {
        $time_start = microtime_float();
        
        $bmh = new app\src\tc_BounceHandler();
        $bmh->action_function    = 'bounce_callback_action'; // default is 'bounce_callback_action'
        $bmh->verbose            = VERBOSE_SIMPLE; //VERBOSE_REPORT; //VERBOSE_DEBUG; //VERBOSE_QUIET; // default is VERBOSE_SIMPLE
        $bmh->use_fetchstructure = true; // true is default, no need to speficy
        $bmh->testmode           = false; // false is default, no need to specify
        $bmh->debug_body_rule    = false; // false is default, no need to specify
        $bmh->debug_dsn_rule     = false; // false is default, no need to specify
        $bmh->purge_unprocessed  = false; // false is default, no need to specify
        $bmh->disable_delete     = false; // false is default, no need to specify

        /*
         * for remote mailbox
         */
        $bmh->mailhost          = get_option('tc_smtp_host'); // your mail server
        $bmh->mailbox_username  = get_option('tc_smtp_username'); // your mailbox username
        $bmh->mailbox_password  = get_option('tc_smtp_password'); // your mailbox password
        $bmh->port              = get_option('tc_smtp_port'); // the port to access your mailbox, default is 143
        $bmh->service           = get_option('tc_smtp_service'); // the service to use (imap or pop3), default is 'imap'
        $bmh->service_option    = get_option('tc_smtp_smtpsecure'); // the service options (none, tls, notls, ssl, etc.), default is 'notls'
        $bmh->boxname           = (get_option('tc_smtp_mailbox') == '' ? 'INBOX' : get_option('tc_smtp_mailbox')); // the mailbox to access, default is 'INBOX'
        $bmh->moveHard          = true; // default is false
        $bmh->hardMailbox       = (get_option('tc_smtp_mailbox') == '' ? 'INBOX' : get_option('tc_smtp_mailbox')).'.hard'; // default is 'INBOX.hard' - NOTE: must start with 'INBOX.'
        $bmh->moveSoft          = false; // default is false
        $bmh->softMailbox       = ''; // default is 'INBOX.soft' - NOTE: must start with 'INBOX.'
        $bmh->deleteMsgDate     = Jenssegers\Date\Date::now()->format('yyyy-mm-dd H:i:s'); // format must be as 'yyyy-mm-dd'
        $bmh->openMailbox();
        $bmh->processMailbox();
        
        $time_end = microtime_float();
        $time = $time_end - $time_start;
        
        Cascade::getLogger('info')->info('BOUNCES[401]: ' . sprintf(_t('Seconds to process: '), $time));
    });
});

$app->setError(function () use($app) {

    $app->view->display('error/404', [
        'title' => '404 Error'
    ]);
});
