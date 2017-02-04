<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Dashboard View
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
define('SCREEN', 'dash');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?=_t('Dashboard');?>
            <small><?=_t('Control panel');?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> <?=_t('Home');?></a></li>
            <li class="active"><?=_t('Dashboard');?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?>
        
        <div class="row">
            <?php dashboard_top_widgets(); ?>
        </div>
        <!-- /.row -->
        
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-6 connectedSortable">

                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs pull-right">
                        <li class="pull-left header"><i class="fa fa-address-book"></i> <?= _t('Subscribers / List'); ?></li>
                    </ul>
                    <div class="tab-content no-padding">
                        <!-- Highchart subscribers/list -->
                        <div class="chart tab-pane active" id="subList" style="position: relative; height: 300px;"></div>
                    </div>
                </div>
                
                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs pull-right">
                        <li class="pull-left header"><i class="fa fa-paper-plane"></i> <?= _t('Emails Sent / List'); ?></li>
                    </ul>
                    <div class="tab-content no-padding">
                        <!-- Highchart email sent/list -->
                        <div class="chart tab-pane active" id="sentEmail" style="position: relative; height: 300px;"></div>
                    </div>
                </div>

            </section>
            <!-- /.Left col -->
            <!-- right col (We are only adding the ID to make the widgets sortable)-->
            <section class="col-lg-6 connectedSortable">

                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs pull-right">
                        <li class="pull-left header"><i class="fa fa-envelope"></i> <?= _t('Campaigns / List'); ?></li>
                    </ul>
                    <div class="tab-content no-padding">
                        <!-- Highchart campaigns/list -->
                        <div class="chart tab-pane active" id="cpgnList" style="position: relative; height: 300px;"></div>
                    </div>
                </div>
                
                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs pull-right">
                        <li class="pull-left header"><i class="fa fa-retweet"></i> <?= _t('Bounces / List'); ?></li>
                    </ul>
                    <div class="tab-content no-padding">
                        <!-- Highchart email sent/list -->
                        <div class="chart tab-pane active" id="bouncedEmail" style="position: relative; height: 300px;"></div>
                    </div>
                </div>

            </section>
            <!-- right col -->
        </div>
        <!-- /.row (main row) -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
$app->view->stop();
