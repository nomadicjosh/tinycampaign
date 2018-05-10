<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * tinyCampaign Logging Functions
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */
use Cascade\Cascade;

$config = [
    'version' => 1,
    'disable_existing_loggers' => false,
    'formatters' => [
        'spaced' => [
            'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'include_stacktraces' => true
        ],
        'dashed' => [
            'format' => "%datetime%-%channel%.%level_name% - %message% - %context% - %extra%\n"
        ],
        'exception' => [
            'format' => "[%datetime%] %message% %context% %extra%\n",
            'include_stacktraces' => true
        ]
    ],
    'handlers' => [
        'console' => [
            'class' => 'Monolog\Handler\StreamHandler',
            'level' => 'DEBUG',
            'formatter' => 'exception',
            'stream' => 'php://stdout'
        ],
        'info_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'INFO',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => APP_PATH . 'tmp' . DS . 'logs' . DS . 'tc-info.txt'
        ],
        'error_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'ERROR',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => APP_PATH . 'tmp' . DS . 'logs' . DS . 'tc-error.txt'
        ],
        'notice_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'NOTICE',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => APP_PATH . 'tmp' . DS . 'logs' . DS . 'tc-notice.txt'
        ],
        'critical_file_handler' => [
            'class' => 'Monolog\Handler\RotatingFileHandler',
            'level' => 'CRITICAL',
            'formatter' => 'exception',
            'maxFiles' => 10,
            'filename' => APP_PATH . 'tmp' . DS . 'logs' . DS . 'tc-critical.txt'
        ],
        'alert_file_handler' => [
            'class' => 'app\src\tc_MailHandler',
            'level' => 'ALERT',
            'formatter' => 'exception',
            'mailer' => new app\src\tc_Email(),
            'message' => 'This message will be replaced with the real one.',
            'email_to' => _escape(app()->hook->{'get_option'}('system_email')),
            'subject' => _t('tinyCampaign System Alert!')
        ]
    ],
    'processors' => [
        'tag_processor' => [
            'class' => 'Monolog\Processor\TagProcessor'
        ]
    ],
    'loggers' => [
        'info' => [
            'handlers' => ['console', 'info_file_handler']
        ],
        'error' => [
            'handlers' => ['console', 'error_file_handler']
        ],
        'notice' => [
            'handlers' => ['console', 'notice_file_handler']
        ],
        'critical' => [
            'handlers' => ['console', 'critical_file_handler']
        ],
        'system_email' => [
            'handlers' => ['console', 'alert_file_handler']
        ]
    ]
];

Cascade::fileConfig(app()->hook->{'apply_filter'}('monolog_cascade_config', $config));

/**
 * Default Error Handler
 * 
 * Sets the default error handler to handle
 * PHP errors and exceptions.
 *
 * @since 2.0.0
 */
function tc_error_handler($type, $string, $file, $line)
{
    $logger = _tc_logger();
    $logger->logError($type, $string, $file, $line);
}

/**
 * Set Error Log for Debugging.
 * 
 * @since 2.0.0
 * @param string|array $value The data to be catched.
 */
function tc_error_log($value)
{
    if (is_array($value)) {
        error_log(var_export($value, true));
    } else {
        error_log($value);
    }
}

/**
 * Write Activity Logs to Database.
 *
 * @since 2.0.0
 */
function tc_logger_activity_log_write($action, $process, $record, $uname)
{
    $logger = _tc_logger();
    $logger->writeLog($action, $process, $record, $uname);
}

/**
 * Purges the error log of old records.
 *
 * @since 2.0.0
 */
function tc_logger_error_log_purge()
{
    $logger = _tc_logger();
    $logger->purgeErrorLog();
}

/**
 * Purges the activity log of old records.
 *
 * @since 2.0.0
 */
function tc_logger_activity_log_purge()
{
    $logger = _tc_logger();
    $logger->purgeActivityLog();
}

/**
 * Custom error log function for better PHP logging.
 * 
 * @since 2.0.0
 * @param string $name
 *            Log channel and log file prefix.
 * @param string $message
 *            Message printed to log.
 * @param string $level The logging level.
 */
function tc_monolog($name, $message, $level = 'addInfo')
{
    $log = new \Monolog\Logger(_trim($name));
    $log->pushHandler(new \Monolog\Handler\StreamHandler(APP_PATH . 'tmp' . DS . 'logs' . DS . _trim($name) . '.' . date('m-d-Y') . '.txt'));
    $log->$level($message);
}

/**
 * Set the system environment.
 * 
 * @since 2.0.0
 */
function tc_set_environment()
{
    /**
     * Error log setting
     */
    if (APP_ENV == 'DEV') {
        /**
         * Print errors to the screen.
         */
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 'On');
    } else {
        /**
         * Log errors to a file.
         */
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', APP_PATH . 'tmp' . DS . 'logs' . DS . 'tc-error-' . date('Y-m-d') . '.txt');
        set_error_handler('tc_error_handler', E_ALL & ~E_NOTICE);
    }
}
