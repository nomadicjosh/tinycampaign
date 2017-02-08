<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Cronjob Handler Settings View
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
define('SCREEN_PARENT', 'handler');
define('SCREEN', 'hset');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Cronjob Handler Settings'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>cron/"><i class="fa fa-clock-o"></i> <?= _t('Cronjob Handlers'); ?></a></li>
            <li class="active"><?= _t('Cronjob Handler Settings'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            
            <div class="break"></div>

            <!-- Tabs Heading -->            
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li><a href="<?= get_base_url(); ?>cron/"><?= _t('Handler Dashboard'); ?></a></li>
                    <li><a href="<?= get_base_url(); ?>cron/create/"><?= _t('New Cronjob Handler'); ?></a></li>
                    <li class="active"><a href="<?= get_base_url(); ?>cron/setting/" data-toggle="tab"><?= _t('Settings'); ?></a></li>
                </ul>
            </div>
            <!-- // Tabs Heading END -->
            
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>cron/setting/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Cronjob Password'); ?>  <a href="#cronpass" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" id="cronjobpassword" name="cronjobpassword" value="<?=_h($data->cronjobpassword);?>" required/>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                           <div class="form-group">
                                <label><font color="red">*</font> <?=_t( "Cronjob Timeout" );?> <a href="#crontimeout" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" id="timeout" name="timeout" value="<?=(_h($data->timeout) !== null) ? _h($data->timeout) : 30;?>" required/>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        
                    </div>
                    <!-- /.row -->
                </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary"><?=_t('Save');?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>cron/'"><?=_t( 'Cancel' );?></button>
            </div>
        </form>
        <!-- form end -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
    
    <!-- modal -->
    <div class="modal" id="cronpass">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Cronjob Password' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "This password is required in order to run your master cronjob (i.e. http://replace_url/cron/cronjob?password=CRONPASSWORD)." );?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
    
    <!-- modal -->
    <div class="modal" id="crontimeout">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Cronjob Timeout' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "The number in seconds that a cronjob can run before it should time out." );?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>
