<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Error View
 *  
 * @license GPLv3
 * 
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/blank');
$app->view->block('blank');

header('Content-Type: application/json');
$app->res->_format('json', 404);

$app->view->stop();
