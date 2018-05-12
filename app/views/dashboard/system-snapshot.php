<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * System Snapshot Report View
 *  
 * @license GPLv3
 * 
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/dashboard');
$app->view->block('dashboard');
TinyC\Config::set('screen_parent', 'admin');
TinyC\Config::set('screen_child', 'snapshot');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('System Snapshot Report'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('System Snapshot Report'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?>
        
        <div class="box box-default">
            <pre>
                <?php
                    $report = '';
                    // add filter for adding to report opening
                    $report	.= $app->hook->{'apply_filter'}( 'system_snapshot_report_before', '' );
                    
                    $report .= "\n\t" . '** tinyC DATA **' . PHP_EOL . PHP_EOL;
                    $report .= 'Site URL:'."\t\t\t\t\t\t" . get_base_url() . PHP_EOL;
                    $report .= 'tinyC Release:' . "\t\t\t\t\t\t" . CURRENT_RELEASE . PHP_EOL;
                    $report .= 'API Key:' . "\t\t\t\t\t\t" . (preg_match('/\s/',get_option('api_key')) ? '<font color="red">'._t('No').'</font>' : '<font color="green">'._t('Yes').'</font>') . PHP_EOL;
                    $report .= "Active User Count:"."\t\t\t\t\t".(int)$user.PHP_EOL;
                    $report .= sprintf("DB Errors:"."\t\t\t\t\t\t".((int)$error <= 0 ? '<font color="green">0</font>' : '<font color="red">'.(int)$error.'</font> (<a href="%s"><strong>Click Here</strong></a>)'), get_base_url() . 'error/').PHP_EOL;
                    $report .= "\n\t".'** tinyC CONFIG **'.PHP_EOL . PHP_EOL;
                    $report .= 'Environment:'."\t\t\t\t\t\t".(APP_ENV == 'PROD' ? '<font color="green">'._t('Production').'</font>' : '<font color="red">'._t('Development').'</font>').PHP_EOL;
                    $report .= 'Base Path:'."\t\t\t\t\t\t".BASE_PATH.PHP_EOL;
                    $report .= 'Application Path:'."\t\t\t\t\t".APP_PATH.PHP_EOL;

                    $report .= "\n\t".'** SERVER DATA **'.PHP_EOL . PHP_EOL;
                    $report .= 'PHP Version:'."\t\t\t\t\t\t".PHP_VERSION.PHP_EOL;
                    $report .= 'PHP Handler:'."\t\t\t\t\t\t".PHP_SAPI.PHP_EOL;
                    $report .= 'MySQL Version:'."\t\t\t\t\t\t".$db->version.PHP_EOL;
                    $report .= 'Server Software:'."\t\t\t\t\t".$app->req->server['SERVER_SOFTWARE'].PHP_EOL;

                    $report .= "\n\t".'** PHP CONFIGURATION **'.PHP_EOL . PHP_EOL;
                    $report .= 'Memory Limit:'."\t\t\t\t\t\t".ini_get( 'memory_limit' ).PHP_EOL;
                    $report .= 'Upload Max:'."\t\t\t\t\t\t".ini_get( 'upload_max_filesize' ).PHP_EOL;
                    $report	.= 'Post Max:'."\t\t\t\t\t\t".ini_get( 'post_max_size' ).PHP_EOL;
                    $report	.= 'Time Limit:'."\t\t\t\t\t\t".ini_get( 'max_execution_time' ).PHP_EOL;
                    $report	.= 'Max Input Vars:'."\t\t\t\t\t\t".ini_get( 'max_input_vars' ).PHP_EOL;
                    $report	.= 'Cookie Path:'."\t\t\t\t\t\t".(is_writable($app->config('cookies.savepath')) ? '<font color="green">'.$app->config('cookies.savepath').'</font>' : '<font color="red">'.$app->config('cookies.savepath').'</font>').PHP_EOL;
                    $report	.= 'Regular Cookie TTL:'."\t\t\t\t\t".tc_seconds_to_time($app->config('cookies.lifetime')).PHP_EOL;
                    $report	.= 'Secure Cookie TTL:'."\t\t\t\t\t".tc_seconds_to_time(get_option('cookieexpire')).PHP_EOL;
                    $report	.= 'File Save Path:'."\t\t\t\t\t\t".(is_writable($app->config('file.savepath')) ? '<font color="green">'.$app->config('file.savepath').'</font>' : '<font color="red">'.$app->config('file.savepath').'</font>').PHP_EOL;
                    $report	.= 'Nodes Save Path:'."\t\t\t\t\t".(is_writable($app->config('cookies.savepath').'nodes') ? '<font color="green">'.$app->config('cookies.savepath').'nodes/</font>' : '<font color="red">'.$app->config('cookies.savepath').'nodes/</font>') . PHP_EOL;
                    $report	.= 'tinyC Node:'."\t\t\t\t\t\t".(is_writable($app->config('cookies.savepath').'nodes/tinyc') ? '<font color="green">'.$app->config('cookies.savepath').'nodes/tinyc/</font>' : '<font color="red">'.$app->config('cookies.savepath').'nodes/tinyc/</font>') . PHP_EOL;
                    $report	.= 'cURL Enabled:'."\t\t\t\t\t\t".(function_exists('curl_version') ? '<font color="green">'._t('Yes').'</font>' : '<font color="red">'._t('No').'</font>').PHP_EOL;
                    
                    // add filter for end of report
                    $report	.= $app->hook->{'apply_filter'}( 'system_snapshot_report_after', '' );
                    // end it all
                    $report	.= PHP_EOL;

                    echo $report;
                ?>
            </pre>
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
$app->view->stop();
