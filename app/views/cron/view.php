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
define('SCREEN_PARENT', 'cron');
define('SCREEN', 'Cron');
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
            <li><a href="<?= get_base_url(); ?>cron/"><i class="fa fa-group"></i> <?= _t('Cronjob Handlers'); ?></a></li>
            <li class="active"><?= _t('View/Edit Cronjob Handler'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>cron/<?=$cron->id;?>/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Handler Name'); ?></label>
                                <input type="text" class="form-control" name="name" value="<?=$cron->name;?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Cronjob URL'); ?></label>
                                <input type="text" class="form-control" name="url" value="<?=$cron->url;?>" required>
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
                                        $s = ($cron->each == $each) ? ' selected="selected"' : '';
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

                                        $s = ($cron->eachtime == $time) ? ' selected="selected"' : '';
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
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>
