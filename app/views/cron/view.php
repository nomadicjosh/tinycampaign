<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Cronjob Handler View
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
define('SCREEN', 'handlers');
$options = [
                 30        => '30 seconds',
                 60        => 'Minute',
                 120       => '2 minutes',
                 300       => '5 minutes',
                 600       => '10 minutes',
                 900       => '15 minutes',
                 1800      => 'Half hour',
                 2700      => '45 minutes',
                 3600      => 'Hour', 
                 7200      => '2 hours', 
                 14400     => '4 hours', 
                 43200     => '12 hours',
                 86400     => 'Day', 
                 172800    => '2 days', 
                 259200    => '3 days', 
                 604800    => 'Week', 
                 209600    => '2 weeks', 
                 2629743   => 'Month'
];
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('View/Edit Cronjob Handler'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>cron/"><i class="fa fa-clock-o"></i> <?= _t('Cronjob Handlers'); ?></a></li>
            <li class="active"><?= _t('View/Edit Cronjob Handler'); ?></li>
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
                    <li class="active"><a href="<?= get_base_url(); ?>cron/"><?= _t('Handler Dashboard'); ?></a></li>
                    <li><a href="<?= get_base_url(); ?>cron/new/"><?= _t('New Cronjob Handler'); ?></a></li>
                    <li><a href="<?= get_base_url(); ?>cron/setting/"><?= _t('Settings'); ?></a></li>
                </ul>
            </div>
            <!-- // Tabs Heading END -->
            
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>cron/<?=_h($cron->id);?>/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Handler Name'); ?></label>
                                <input type="text" class="form-control" name="name" value="<?=_h($cron->name);?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Cronjob URL'); ?></label>
                                <input type="text" class="form-control" name="url" value="<?=_h($cron->url);?>" required>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                            <div class="form-group col-md-4">
                                <label><?=_t( "Each" );?> <a href="#each" data-toggle="modal"><img src="<?=get_base_url();?>static/assets/img/help.png" /></a></label>
                                <select class="form-control select2" name="each" style="width: 100%;">
                                    <option>&nbsp;</option>
                                    <?php 
                                    foreach ($options as $each => $key) {
                                        $s = (_h($cron->each) == $each) ? ' selected="selected"' : '';
                                    ?>
                                    <option value="<?=$each;?>"<?=$s;?>><?=$key; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-4">
                                <label><?=_t( "Each / Time" );?></label>
                                <select class="form-control select2" name="eachtime" style="width: 100%;">
                                <?php 
                                for ($x = 0; $x < 24;$x++) {
                                    for ($y = 0; $y < 4; $y++) {
                                        $time = ((strlen($x) == 1) ? '0' . $x : $x) . ':' . (($y == 0) ? '00' : ($y * 15));

                                        $s = (_h($cron->eachtime) == $time) ? ' selected="selected"' : '';
                                ?>
                                <option value="<?=$time;?>"<?=$s;?>><?=$time;?></option>
                                <?php } } ?>
                                </select>
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
    <div class="modal" id="each">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Each / Time' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t("Set the time in seconds (Each) of how often the cronjob should run (i.e. 2 minute, Hour or every Day / 07:00.)");?></p>
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
