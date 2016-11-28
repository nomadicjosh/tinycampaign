<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Setting View
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
define('SCREEN_PARENT', 'setting');
define('SCREEN', 'general');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
<?= _t('General Settings'); ?>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('General Settings'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method='post' action='<?= get_base_url(); ?>setting/'>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('System Name'); ?></label>
                                <input type="text" class="form-control" name="system_name" value="<?= _h(get_option('system_name')); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('System Email'); ?></label>
                                <input type="text" class="form-control" name="system_email" value="<?= _h(get_option('system_email')); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Mail Throttle'); ?></label>
                                <input type="text" class="form-control" name="mail_throttle" value="<?= _h(get_option('mail_throttle')); ?>" required>
                                <p class="help-block"><?=_t('Value in seconds between each email to be sent.');?></p>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Cookie TTL'); ?></label>
                                <input type="text" class="form-control" name="cookieexpire" value="<?= _h(get_option('cookieexpire')); ?>" required>
                                <p class="help-block"><?=_t('Value in seconds of how long secure cookies should live.');?></p>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Cookie Path'); ?></label>
                                <input type="text" class="form-control" name="cookiepath" value="<?= _h(get_option('cookiepath')); ?>" required>
                            </div>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
        <!-- form end -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
$app->view->stop();
