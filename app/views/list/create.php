<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Create Email List View
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
define('SCREEN_PARENT', 'list');
define('SCREEN', 'clist');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Create Email List'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>list/"><i class="ion ion-ios-list"></i> <?= _t('Email Lists'); ?></a></li>
            <li class="active"><?= _t('Create Email List'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>list/create/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Code'); ?></label>
                                <input type="text" class="form-control" name="code" value="<?=_random_lib()->generateString(12,'0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');?>" readonly required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('List Name'); ?></label>
                                <input type="text" class="form-control" name="name" value="<?=(_h($app->req->post['name']) != '' ? _h($app->req->post['name']) : '');?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Short Description'); ?></label>
                                <textarea class="form-control" rows="3" name="description"><?=(_h($app->req->post['description']) != '' ? _h($app->req->post['description']) : '');?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Status'); ?></label>
                                <select class="form-control select2" name="status" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="open"<?=selected('open',(_h($app->req->post['status']) != '' ? _h($app->req->post['status']) : ''),false);?>><?=_t('Open');?></option>
                                    <option value="closed"<?=selected('closed',(_h($app->req->post['status']) != '' ? _h($app->req->post['status']) : ''),false);?>><?=_t('Closed');?></option>
                                </select>
                                <p class="help-block"><?=_t('If Closed, no one will be able to subscribe to the list.');?></p>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                             <div class="form-group">
                                <label><?= _t('Redirect Success'); ?></label>
                                <input type="text" class="form-control" name="redirect_success" value="<?=(_h($app->req->post['redirect_success']) != '' ? _h($app->req->post['redirect_success']) : '');?>" />
                                <p class="help-block"><?=_t('Override the default with your custom url success message.');?></p>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Redirect Error'); ?></label>
                                <input type="text" class="form-control" name="redirect_unsuccess" value="<?=(_h($app->req->post['redirect_unsuccess']) != '' ? _h($app->req->post['redirect_unsuccess']) : '');?>" />
                                <p class="help-block"><?=_t('Override the default with your custom url unsuccess message.');?></p>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Double Opt-in?'); ?></label>
                                <select class="form-control select2" name="optin" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="1"<?=selected('1',(_h($app->req->post['optin']) != '' ? _h($app->req->post['optin']) : ''),false);?>><?=_t('Yes');?></option>
                                    <option value="0"<?=selected('0',(_h($app->req->post['optin']) != '' ? _h($app->req->post['optin']) : ''),false);?>><?=_t('No');?></option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('SMTP Server'); ?></label>
                                <select class="form-control select2" name="server" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php get_user_servers();?>
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
                <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>list/'"><?=_t( 'Cancel' );?></button>
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
