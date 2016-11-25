<?php
if (! defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * tinyCampaign Global Scope Functions.
 *
 * @license GPLv3
 *         
 * @since 2.0.0
 * @package tinyCampaign
 * @author Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Sets up object cache global scope and assigns it based on
 * the type of caching system used.
 *
 * @since 2.0.0
 */
function _tc_cache_init()
{
    $app = \Liten\Liten::getInstance();
    
    $driver = $app->hook->{'apply_filter'}('tc_cache_driver', 'file');
    $cache = new \app\src\Cache\tc_Object_Cache($driver);
    return $cache;
}

/**
 * Sets up custom field global scope.
 *
 * @since 2.0.0
 * @param string $location
 *            Specifies where the custom field will be used.
 */
function _tc_custom_field($location = 'dashboard')
{
    $field = new \app\src\tc_CustomField($location);
    return $field;
}

/**
 * Sets up PHPMailer global scope.
 *
 * @since 2.0.0
 * @param bool $bool
 *            Set whether to use exceptions for error handling. Default: true.
 */
function _tc_phpmailer($bool = true)
{
    $phpMailer = new \PHPMailer($bool);
    return $phpMailer;
}

/**
 * Sets up tinyCampaign Email global scope.
 *
 * @since 2.0.0
 */
function _tc_email()
{
    $email = new \app\src\tc_Email();
    return $email;
}

/**
 * Sets up tinyCampaign Logger global scope.
 *
 * @since 2.0.0
 */
function _etsis_logger()
{
    $logger = new \app\src\tc_Logger();
    return $logger;
}