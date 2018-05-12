<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Bounce Email Setting View
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
TinyC\Config::set('screen_child', 'bounce');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Bounce Email Settings'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Bounce Email Settings'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?>
        
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>setting/bounce/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Host'); ?></label>
                                <input type="text" class="form-control" name="tc_bmh_host" value="<?= _h(get_option('tc_bmh_host')); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Username'); ?></label>
                                <input type="text" class="form-control" name="tc_bmh_username" value="<?= _h(get_option('tc_bmh_username')); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Password'); ?></label>
                                <input type="password" class="form-control" name="tc_bmh_password" value="<?=$password;?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Mailbox'); ?></label>
                                <input type="text" class="form-control" name="tc_bmh_mailbox" value="<?= _h(get_option('tc_bmh_mailbox')); ?>" />
                            </div>
                        </div>
                        <!-- /.col -->
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Port'); ?></label>
                                <input type="text" class="form-control" name="tc_bmh_port" value="<?= _h(get_option('tc_bmh_port')); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Service'); ?></label>
                                <select class="form-control select2" name="tc_bmh_service" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="imap"<?=selected('imap', _h(get_option('tc_bmh_service')), false);?>><?=_t('Imap');?></option>
                                    <option value="pop3"<?=selected('pop3', _h(get_option('tc_bmh_service')), false);?>><?=_t('Pop3');?></option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Service Option'); ?></label>
                                <select class="form-control select2" name="tc_bmh_service_option" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="none"<?=selected('none', _h(get_option('tc_bmh_service_option')), false);?>><?=_t('None');?></option>
                                    <option value="tls"<?=selected('tls', _h(get_option('tc_bmh_service_option')), false);?>><?=_t('TLS');?></option>
                                    <option value="notls"<?=selected('notls', _h(get_option('tc_bmh_service_option')), false);?>><?=_t('NOTLS');?></option>
                                    <option value="ssl"<?=selected('ssl', _h(get_option('tc_bmh_service_option')), false);?>><?=_t('SSL');?></option>
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

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
$app->view->stop();
