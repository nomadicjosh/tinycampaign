<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * My Campaigns View
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
define('SCREEN_PARENT', 'cpgns');
define('SCREEN', 'cpgn');
?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Campaigns'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Campaigns'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= _tc_flash()->showMessage(); ?>
        
        <div class="box box-default">
            <div class="box-body">
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Subject'); ?></th>
                            <th class="text-center"><?= _t('Status'); ?></th>
                            <th class="text-center"><?= _t('Send Start'); ?></th>
                            <th class="text-center"><?= _t('Send Finish'); ?></th>
                            <th class="text-center"><?= _t('Recipients'); ?></th>
                            <th class="text-center"><?= _t('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($msgs as $msg) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= _h($msg->subject); ?></td>
                                <td class="text-center"><?= ucfirst(_h($msg->status)); ?></td>
                                <td class="text-center"><?= Jenssegers\Date\Date::parse(_h($msg->sendstart))->format('M. d, Y @ h:i A'); ?></td>
                                <td class="text-center"><?=(_h($msg->sendfinish) != '' ? Jenssegers\Date\Date::parse(_h($msg->sendfinish))->format('M. d, Y @ h:i A') : ''); ?></td>
                                <td class="text-center"></td>
                                <td class="text-center">
                                    <div class="btn-group dropdown">
                                        <button class="btn btn-default btn-xs" type="button"><?= _t('Actions'); ?></button>
                                        <button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
                                            <span class="caret"></span>
                                            <span class="sr-only"><?= _t('Toggle Dropdown'); ?></span>
                                        </button>
                                        <ul role="menu" class="dropdown-menu dropup-text pull-right">
                                            <li><a href="<?= get_base_url(); ?>campaign/<?= _h($msg->id); ?>/"><?= _t('Edit'); ?></a></li>
                                            <li<?=(is_status_ready($msg->id) == false ? ' style="display:none !important;"' : '');?>><a href="<?= get_base_url(); ?>campaign/<?= _h($msg->id); ?>/queue/"><?= _t('Send to Queue'); ?></a></li>
                                            <li<?=(is_status_processing($msg->id) == true ? '' : ' style="display:none !important;"');?>><a href="<?= get_base_url(); ?>campaign/<?= _h($msg->id); ?>/pause/"><?= _t('Pause Queue'); ?></a></li>
                                            <li<?=(is_status_paused($msg->id) == true ? '' : ' style="display:none !important;"');?>><a href="<?= get_base_url(); ?>campaign/<?= _h($msg->id); ?>/resume/"><?= _t('Resume Queue'); ?></a></li>
                                            <li><a href="<?= get_base_url(); ?>campaign/<?= _h($msg->id); ?>/report/"><?= _t('Report'); ?></a></li>
                                            <li<?=(is_status_processing($msg->id) == false ? '' : ' style="display:none !important;"');?>><a href="#" data-toggle="modal" data-target="#delete-<?= _h($msg->id); ?>"><?= _t('Delete'); ?></a></li>
                                        </ul>
                                    </div>

                                    <div class="modal" id="delete-<?= _h($msg->id); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= _h($msg->subject); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?=_t('Are you sure you want to delete this campaign?');?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>campaign/<?= _h($msg->id); ?>/d/'"><?= _t('Confirm'); ?></button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <!-- /.modal -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Subject'); ?></th>
                            <th class="text-center"><?= _t('Status'); ?></th>
                            <th class="text-center"><?= _t('Send Start'); ?></th>
                            <th class="text-center"><?= _t('Send Finish'); ?></th>
                            <th class="text-center"><?= _t('Recipients'); ?></th>
                            <th class="text-center"><?= _t('Action'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>