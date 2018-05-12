<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * SMTP Setting View
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
TinyC\Config::set('screen_child', 'smtp');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('SMTP Settings'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('SMTP Settings'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

<?= _tc_flash()->showMessage(); ?>

        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>setting/smtp/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('SMTP Host'); ?></label>
                                <input type="text" class="form-control" name="tc_smtp_host" value="<?= _h(get_option('tc_smtp_host')); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('SMTP Username'); ?></label>
                                <input type="text" class="form-control" name="tc_smtp_username" value="<?= _h(get_option('tc_smtp_username')); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('SMTP Password'); ?></label>
                                <input type="password" class="form-control" name="tc_smtp_password" value="<?=$password;?>" required>
                            </div>
                        </div>
                        <!-- /.col -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('SMTP Port'); ?></label>
                                <input type="text" class="form-control" name="tc_smtp_port" value="<?= _h(get_option('tc_smtp_port')); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Secure Connection'); ?></label>
                                <select class="form-control select2" name="tc_smtp_smtpsecure" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="none"<?= selected('none', _h(get_option('tc_smtp_smtpsecure')), false); ?>><?= _t('None'); ?></option>
                                    <option value="tls"<?= selected('tls', _h(get_option('tc_smtp_smtpsecure')), false); ?>><?= _t('TLS'); ?></option>
                                    <option value="notls"<?= selected('notls', _h(get_option('tc_smtp_smtpsecure')), false); ?>><?= _t('NOTLS'); ?></option>
                                    <option value="ssl"<?= selected('ssl', _h(get_option('tc_smtp_smtpsecure')), false); ?>><?= _t('SSL'); ?></option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Status'); ?></label>
                                <select class="form-control select2" name="tc_smtp_status" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="1"<?= selected('1', _h((int)get_option('tc_smtp_status')), false); ?>><?= _t('Active'); ?></option>
                                    <option value="0"<?= selected('0', _h((int)get_option('tc_smtp_status')), false); ?>><?= _t('Inactive'); ?></option>
                                </select>
                            </div>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><?=_t('Submit');?></button>
                </div>
            </form>
            <!-- form end -->
        </div>
        <!-- /.box -->

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= _t('Send Test Email'); ?></h3>
            </div>
            <!-- form start -->
            <form method='post' action='<?= get_base_url(); ?>setting/smtp/test/'>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Subject'); ?></label>
                                <input type="text" class="form-control" name="subject" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('To Email'); ?></label>
                                <input type="text" class="form-control" name="to_email" required>
                            </div>
                        </div>
                        <!-- /.col -->

                        <div class="col-md-12">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Message'); ?></label>
                                <textarea class="form-control" rows="3" name="message" required></textarea>
                            </div>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><?=_t('Submit');?></button>
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
