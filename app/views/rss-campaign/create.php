<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Create RSS Campaign Feed View
 *  
 * @license GPLv3
 * 
 * @since       2.0.5
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/dashboard');
$app->view->block('dashboard');
TinyC\Config::set('screen_parent', 'rss');
TinyC\Config::set('screen_child', 'crss');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Create RSS Campaign'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>rss-campaign/"><i class="fa fa-envelope"></i> <?= _t('RSS Campaigns'); ?></a></li>
            <li class="active"><?= _t('Create RSS Campaign'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= _tc_flash()->showMessage(); ?>

        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>rss-campaign/create/" data-toggle="validator" autocomplete="off" id="form">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Node'); ?></label>
                                <input type="text" class="form-control" name="node" readonly value="<?= 'rss_' . _random_lib()->generateString(12, 'abcdefghijklmnopqrstuvwxyzzyxwvutsrqponmlkjihgfedcba'); ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Email Subject'); ?>  <a href="#subject" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" name="subject" value="<?= (_escape($app->req->post['subject']) != '' ? _escape($app->req->post['subject']) : ''); ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('From Name'); ?></label>
                                <input type="text" class="form-control" name="from_name" value="<?= (_escape($app->req->post['from_name']) != '' ? _escape($app->req->post['from_name']) : ''); ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('From Email'); ?></label>
                                <input type="text" class="form-control" name="from_email" value="<?= (_escape($app->req->post['from_email']) != '' ? _escape($app->req->post['from_email']) : ''); ?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Template'); ?></label>
                                <select class="form-control select2" name="tid" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php get_template_list(); ?>
                                </select>
                            </div>

                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('RSS Feed'); ?></label>
                                <input type="text" class="form-control" name="rss_feed" value="<?= if_not_null($app->req->post['rss_feed']); ?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('RSS Items'); ?>  <a href="#items" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" name="rss_items" value="<?= if_not_null($app->req->post['rss_items']); ?>" />
                            </div>

                            <div class="form-group">
                                <label><?= _t('Lists'); ?></label><br />
                                <ul><?php get_rss_campaign_lists(null); ?></ul>
                            </div>

                        </div>
                        <!-- /.col -->

                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary"><?= _t('Submit'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>rss-campaign/'"><?= _t('Cancel'); ?></button>
                </div>
            </form>
            <!-- form end -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
    
    <!-- modal -->
    <div class="modal" id="subject">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Email Subject' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "This is the email subject of every rss campaign that goes out." );?></p>
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
    <div class="modal" id="items">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'RSS Items' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "Number of recent items to retrieve from feed on each call." );?></p>
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
