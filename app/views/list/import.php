<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Import Subscribers View
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
define('SCREEN', 'lists');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _h($list->name); ?>: <?= _t('Import Subscribers'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>list/"><i class="ion ion-ios-list"></i> <?= _t('Email Lists'); ?></a></li>
            <li class="active"><?= _t('Import Subscribers'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= _tc_flash()->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form role="form" method="post" action="<?= get_base_url(); ?>list/<?= _h((int)$list->id); ?>/import/" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="box-body">
                                <div class="form-group">
                                    <label><font color="red">*</font> <?= _t('Delimiter'); ?></label>
                                    <select class="form-control select2" name="delimiter" style="width: 100%;" required>
                                        <option>&nbsp;</option>
                                        <option value="del1">, <?= _t('(Comma)'); ?></option>
                                        <option value="del2">; <?= _t('(Semicolon)'); ?></option>
                                        <option value="del3"> <?= _t('(Line Break)'); ?></option>
                                        <option value="del4"> <?= _t('(TAB)'); ?></option>
                                    </select>
                                    <p class="help-block"><?= _t('If Closed, no one will be able to subscribe to the list.'); ?></p>
                                </div>
                            </div>
                            <!-- /.box -->
                        </div>
                        
                        <div class="col-md-6">
                            <div class="box-body">
                                <div class="form-group">
                                    <label for="exampleInputFile"><font color="red">*</font> <?= _t('File input'); ?> <a href="#csv" data-toggle="modal"><img src="<?=get_base_url();?>static/assets/img/help.png" /></a></label>
                                    <input type="file" name="csv_import" required/>
                                </div>
                            </div>
                            <!-- /.box -->
                            <!-- form end -->
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
                    <div class="box-footer">
                        <button<?=ie('email_list_inquiry_only');?> type="submit" class="btn btn-primary"><?= _t('Submit'); ?></button>
                        <button<?=ie('email_list_inquiry_only');?> type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>list/download/?f=tinyCampaign.csv'"><?= _t('Download Template'); ?></button>
                        <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>list/'"><?= _t('Cancel'); ?></button>
                    </div>
            </form>
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
    
    <!-- modal -->
    <div class="modal" id="csv">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'File Input' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "Click the 'Download Template' button to download an example import file in .csv format. Fields in order are first name, last name, email address, confirmed (1, 0), and unsubscribed (1, 0). '1' stands for true/yes and '0' stands for false/no." );?></p>
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