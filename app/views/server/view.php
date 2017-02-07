<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Edit Server View
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
define('SCREEN', 'server');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Edit Server'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>server/"><i class="fa fa-server"></i> <?= _t('Servers'); ?></a></li>
            <li class="active"><?= _t('Edit Server'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

<?= _tc_flash()->showMessage(); ?>

        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>server/<?=(int)_h($server->id);?>/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Name'); ?></label>
                                <input type="text" class="form-control" name="name" value="<?=_h($server->name);?>" required/>
                            </div>
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Host'); ?></label>
                                <input type="text" class="form-control" name="hname" value="<?=_h($server->hname);?>" required/>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Username'); ?></label>
                                <input type="text" class="form-control" name="uname" value="<?=_h($server->uname);?>" required/>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Password'); ?></label>
                                <input type="password" class="form-control" name="password" value="<?=_h($password);?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Port'); ?></label>
                                <input type="text" class="form-control" name="port" value="<?=_h($server->port);?>" required/>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Protocol'); ?></label>
                                <select class="form-control select2" name="protocol" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="none"<?= selected('none', _h($server->protocol), false); ?>><?= _t('None'); ?></option>
                                    <option value="tls"<?= selected('tls', _h($server->protocol), false); ?>><?= _t('TLS'); ?></option>
                                    <option value="notls"<?= selected('notls', _h($server->protocol), false); ?>><?= _t('NOTLS'); ?></option>
                                    <option value="starttls"<?= selected('starttls', _h($server->protocol), false); ?>><?= _t('STARTTLS'); ?></option>
                                    <option value="ssl"<?= selected('ssl', _h($server->protocol), false); ?>><?= _t('SSL'); ?></option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Throttle'); ?>  <a href="#throttle" data-toggle="modal"><img src="<?=get_base_url();?>static/assets/img/help.png" /></a></label>
                                <input type="text" class="form-control" name="throttle" value="<?=_h($server->throttle);?>" required/>
                            </div>
                        </div>
                        <!-- /.col -->

                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Sender Email'); ?></label>
                                <input type="email" class="form-control" name="femail" value="<?=_h($server->femail);?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Sender Name'); ?></label>
                                <input type="text" class="form-control" name="fname" value="<?=_h($server->fname);?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Reply Email'); ?></label>
                                <input type="email" class="form-control" name="remail" value="<?=_h($server->remail);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Reply Name'); ?></label>
                                <input type="text" class="form-control" name="rname" value="<?=_h($server->rname);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Added'); ?></label>
                                <input type="text" class="form-control" value="<?=Jenssegers\Date\Date::parse(_h($server->addDate))->format('M. d, Y @ h:i A');?>" readonly/>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Modified'); ?></label>
                                <input type="text" class="form-control" value="<?=Jenssegers\Date\Date::parse(_h($server->LastUpdate))->format('M. d, Y @ h:i A');?>" readonly/>
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

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= _t('Send Test Email'); ?></h3>
            </div>
            <!-- form start -->
            <form method='post' action='<?= get_base_url(); ?>server/<?=(int)_h($server->id);?>/test/'>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Subject'); ?></label>
                                <input type="text" class="form-control" name="subject" required/>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('To Email'); ?></label>
                                <input type="email" class="form-control" name="to_email" required/>
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
                    <button<?=ie('email_list_inquiry_only');?> type="submit" class="btn btn-primary"><?=_t('Submit');?></button>
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
