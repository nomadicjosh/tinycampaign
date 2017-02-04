<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Create Server View
 *  
 * @license GPLv3
 * 
 * @since       2.0.1
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/dashboard');
$app->view->block('dashboard');
define('SCREEN_PARENT', 'servers');
define('SCREEN', 'cserver');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Create Server'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>server/"><i class="fa fa-server"></i> <?= _t('Servers'); ?></a></li>
            <li class="active"><?= _t('Create Server'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

<?= _tc_flash()->showMessage(); ?>

        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>server/create/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Name'); ?></label>
                                <input type="text" class="form-control" name="name" value="<?=(_h($app->req->post['name']) != '' ? _h($app->req->post['name']) : '');?>" required>
                            </div>
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Host'); ?></label>
                                <input type="text" class="form-control" name="hname" value="<?=(_h($app->req->post['hname']) != '' ? _h($app->req->post['hname']) : '');?>" required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Username'); ?></label>
                                <input type="text" class="form-control" name="uname" value="<?=(_h($app->req->post['uname']) != '' ? _h($app->req->post['uname']) : '');?>" required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Password'); ?></label>
                                <input type="password" class="form-control" name="password" value="<?=(_h($app->req->post['password']) != '' ? _h($app->req->post['password']) : '');?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Port'); ?></label>
                                <input type="text" class="form-control" name="port" value="<?=(_h($app->req->post['port']) != '' ? _h($app->req->post['port']) : '');?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Protocol'); ?></label>
                                <select class="form-control select2" name="protocol" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="none"<?= selected('none', (_h($app->req->post['protocol']) != '' ? _h($app->req->post['protocol']) : ''), false); ?>><?= _t('None'); ?></option>
                                    <option value="tls"<?= selected('tls', (_h($app->req->post['protocol']) != '' ? _h($app->req->post['protocol']) : ''), false); ?>><?= _t('TLS'); ?></option>
                                    <option value="notls"<?= selected('notls', (_h($app->req->post['protocol']) != '' ? _h($app->req->post['protocol']) : ''), false); ?>><?= _t('NOTLS'); ?></option>
                                    <option value="starttls"<?= selected('starttls', (_h($app->req->post['protocol']) != '' ? _h($app->req->post['protocol']) : ''), false); ?>><?= _t('STARTTLS'); ?></option>
                                    <option value="ssl"<?= selected('ssl', (_h($app->req->post['protocol']) != '' ? _h($app->req->post['protocol']) : ''), false); ?>><?= _t('SSL'); ?></option>
                                </select>
                            </div>
                        </div>
                        <!-- /.col -->

                        <div class="col-md-6">                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Throttle'); ?>  <a href="#throttle" data-toggle="modal"><img src="<?=get_base_url();?>static/assets/img/help.png" /></a></label>
                                <input type="text" class="form-control" name="throttle" value="<?=(_h($app->req->post['throttle']) != '' ? _h($app->req->post['throttle']) : '');?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Sender Email'); ?></label>
                                <input type="email" class="form-control" name="femail" value="<?=(_h($app->req->post['femail']) != '' ? _h($app->req->post['femail']) : '');?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Sender Name'); ?></label>
                                <input type="text" class="form-control" name="fname" value="<?=(_h($app->req->post['fname']) != '' ? _h($app->req->post['fname']) : '');?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Reply Email'); ?></label>
                                <input type="email" class="form-control" name="remail" value="<?=(_h($app->req->post['remail']) != '' ? _h($app->req->post['remail']) : '');?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Reply Name'); ?></label>
                                <input type="text" class="form-control" name="rname" value="<?=(_h($app->req->post['rname']) != '' ? _h($app->req->post['rname']) : '');?>" />
                            </div>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><?=_t('Submit');?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>server/'"><?=_t( 'Cancel' );?></button>
                </div>
            </form>
            <!-- form end -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
    
    <!-- modal -->
    <div class="modal" id="throttle">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Throttle' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "Some servers have as stipulation of how many emails can be sent per hour. To determine what the mail throttle should be, use this formular:" );?></p>
                    <p><em><?=_t('seconds in an hour / # of emails per hour =  throttle');?></em></p>
                    <p><?=_t('Let’s say that your hosting provider only allows 100 emails to be sent from your account per hour. Then our formula would be:');?></p>
                    <p><strong><?=_t('3600/100 = 36');?></strong></p>
                    <p><?=_t('The number you would need to enter into the mail throttle field would be <strong>36</strong>. To give yourself a buffer, you may want to use 40 or 45.');?></p>
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
<?php
$app->view->stop();
