<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use PDOException as ORMException;

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
TinyC\Config::set('screen_parent', 'cpgns');
TinyC\Config::set('screen_child', 'cpgn');

?>
<?php if($count > 0) : ?>
<script type="text/javascript">
    $(document).ready(function () {
        setInterval(function () {
            <?php foreach($msgs as $j) : ?>
            $("#msg<?=_escape($j->id);?>").load(location.href + " #msg<?=_escape($j->id);?>>*", "");
            <?php endforeach; ?>
        }, 10000);
    });
</script>
<?php endif; ?>

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
                <table id="example1" class="table table-bordered table-hover">
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
                            <?php
                            try {
                                $all = $app->db->campaign_queue()
                                    ->where('is_cancelled', 'false')->_and_()
                                    ->where('cid = ?', _escape($msg->id))
                                    ->count();
                                $sent = $app->db->campaign_queue()
                                    ->where('is_sent', 'true')->_and_()
                                    ->where('is_cancelled', 'false')
                                    ->where('cid = ?', _escape($msg->id))
                                    ->count();
                                $to_send = $app->db->campaign_queue()
                                    ->where('is_sent', 'false')->_and_()
                                    ->where('is_cancelled', 'false')->_and_()
                                    ->where('cid = ?', _escape($msg->id))
                                    ->count();
                            } catch (ORMException $e) {
                                _tc_flash()->error($e->getMessage());
                            }

                            ?>
                            <tr class="gradeX" id="msg<?=_escape($msg->id);?>">
                                <td class="text-center"><?= _escape($msg->subject); ?></td>
                                <td class="text-center">
                                    <span class="label <?=tc_cpgn_status_label(_escape($msg->status));?>" style="font-size:1em;font-weight: bold;">
                                        <?= ucfirst(_escape($msg->status)); ?>
                                    </span>
                                    <?php if($to_send > 0) : ?>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar" style="width: <?=percent($sent, _escape($all));?>%"><?=percent($sent, _escape($all));?>%</div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= Jenssegers\Date\Date::parse(_escape($msg->sendstart))->format('M. d, Y @ h:i A'); ?></td>
                                <td class="text-center"><?= (_escape($msg->sendfinish) != '' ? Jenssegers\Date\Date::parse(_escape($msg->sendfinish))->format('M. d, Y @ h:i A') : ''); ?></td>
                                <?php if (_escape((int)$msg->recipients) < $app->db->campaign_queue()->where('cid = ?', _escape($msg->id))->count()) : ?>
                                <td class="text-center">
                                    <span class="label bg-gray" style="font-size:1em;font-weight: bold;">
                                        <?= _escape((int)$msg->recipients); ?> / <?= $app->db->campaign_queue()->where('cid = ?', _escape($msg->id))->count(); ?>
                                    </span>
                                </td>
                                <?php else : ?>
                                <td class="text-center">
                                    <span class="label bg-gray" style="font-size:1em;font-weight: bold;">
                                        <?= _escape((int)$msg->recipients); ?>
                                    </span>
                                </td>
                                <?php endif; ?>
                                <td class="text-center">
                                    <a href="<?= get_base_url(); ?>campaign/<?= _escape((int)$msg->id); ?>/" data-toggle="tooltip" data-placement="top" title="View/Edit"><button type="button" class="btn bg-yellow"><i class="fa fa-edit"></i></button></a>
                                    <a<?= (is_status_ready(_escape((int)$msg->id)) == false ? ' style="display:none !important;"' : ''); ?> href="<?= get_base_url(); ?>campaign/<?= _escape((int)$msg->id); ?>/queue/" data-toggle="tooltip" data-placement="top" title="Send to Queue"><button type="button" class="btn bg-green"><i class="fa fa-arrow-right"></i></button></a>
                                    <a<?= (is_status_processing(_escape((int)$msg->id)) == true ? '' : ' style="display:none !important;"'); ?> href="<?= get_base_url(); ?>campaign/<?= _escape((int)$msg->id); ?>/pause/" data-toggle="tooltip" data-placement="top" title="Pause Queue"><button type="button" class="btn bg-orange"><i class="fa fa-pause"></i></button></a>
                                    <a<?= (is_status_paused(_escape((int)$msg->id)) == true ? '' : ' style="display:none !important;"'); ?> href="<?= get_base_url(); ?>campaign/<?= _escape((int)$msg->id); ?>/resume/" data-toggle="tooltip" data-placement="top" title="Resume Queue"><button type="button" class="btn bg-orange"><i class="fa fa-play"></i></button></a>
                                    <a href="<?= get_base_url(); ?>campaign/<?= _escape((int)$msg->id); ?>/report/" data-toggle="tooltip" data-placement="top" title="Report"><button type="button" class="btn bg-blue"><i class="fa fa-area-chart"></i></button></a>
                                    <a<?= (is_status_processing(_escape((int)$msg->id)) == false ? '' : ' style="display:none !important;"'); ?><?= ae('delete_campaign'); ?> href="#" data-toggle="modal" data-target="#delete-<?= _escape((int)$msg->id); ?>" title="Delete"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>

                                    <div class="modal" id="delete-<?= _escape((int)$msg->id); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= _escape($msg->subject); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= _t('Are you sure you want to delete this campaign?'); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>campaign/<?= _escape((int)$msg->id); ?>/d/'"><?= _t('Confirm'); ?></button>
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