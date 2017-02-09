<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\NodeQException;

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

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        setInterval(function () {
            $("#example2").load(location.href + " #example2>*", "");
        }, 10000);
    });
</script>

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
                            <?php
                            try {
                                $all = Node::table($msg->node)
                                    ->findAll()
                                    ->count();
                                $sent = Node::table($msg->node)
                                    ->where('is_sent','=','true')
                                    ->findAll()
                                    ->count();
                                $to_send = Node::table($msg->node)
                                    ->where('is_sent','=','false')
                                    ->findAll()
                                    ->count();
                            } catch (NodeQException $e) {
                                _tc_flash()->error($e->getMessage());
                            }

                            ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= _h($msg->subject); ?></td>
                                <td class="text-center">
                                    <span class="label <?=tc_cpgn_status_label(_h($msg->status));?>" style="font-size:1em;font-weight: bold;">
                                        <?= ucfirst(_h($msg->status)); ?>
                                    </span>
                                    <?php if($to_send > 0) : ?>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar" style="width: <?=percent($sent, _h($all));?>%"><?=percent($sent, _h($all));?>%</div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= Jenssegers\Date\Date::parse(_h($msg->sendstart))->format('M. d, Y @ h:i A'); ?></td>
                                <td class="text-center"><?= (_h($msg->sendfinish) != '' ? Jenssegers\Date\Date::parse(_h($msg->sendfinish))->format('M. d, Y @ h:i A') : ''); ?></td>
                                <?php if (_h((int)$msg->recipients) < Node::table(_h($msg->node))->findAll()->count()) : ?>
                                <td class="text-center">
                                    <span class="label bg-gray" style="font-size:1em;font-weight: bold;">
                                        <?= _h((int)$msg->recipients); ?> / <?= Node::table(_h($msg->node))->findAll()->count(); ?>
                                    </span>
                                </td>
                                <?php else : ?>
                                <td class="text-center">
                                    <span class="label bg-gray" style="font-size:1em;font-weight: bold;">
                                        <?= _h((int)$msg->recipients); ?>
                                    </span>
                                </td>
                                <?php endif; ?>
                                <td class="text-center">
                                    <a href="<?= get_base_url(); ?>campaign/<?= _h((int)$msg->id); ?>/" data-toggle="tooltip" data-placement="top" title="View/Edit"><button type="button" class="btn bg-yellow"><i class="fa fa-edit"></i></button></a>
                                    <a<?= (is_status_ready(_h((int)$msg->id)) == false ? ' style="display:none !important;"' : ''); ?> href="<?= get_base_url(); ?>campaign/<?= _h((int)$msg->id); ?>/queue/" data-toggle="tooltip" data-placement="top" title="Send to Queue"><button type="button" class="btn bg-green"><i class="fa fa-arrow-right"></i></button></a>
                                    <a<?= (is_status_processing(_h((int)$msg->id)) == true ? '' : ' style="display:none !important;"'); ?> href="<?= get_base_url(); ?>campaign/<?= _h((int)$msg->id); ?>/pause/" data-toggle="tooltip" data-placement="top" title="Pause Queue"><button type="button" class="btn bg-orange"><i class="fa fa-pause"></i></button></a>
                                    <a<?= (is_status_paused(_h((int)$msg->id)) == true ? '' : ' style="display:none !important;"'); ?> href="<?= get_base_url(); ?>campaign/<?= _h((int)$msg->id); ?>/resume/" data-toggle="tooltip" data-placement="top" title="Resume Queue"><button type="button" class="btn bg-orange"><i class="fa fa-play"></i></button></a>
                                    <a href="<?= get_base_url(); ?>campaign/<?= _h((int)$msg->id); ?>/report/" data-toggle="tooltip" data-placement="top" title="Report"><button type="button" class="btn bg-blue"><i class="fa fa-area-chart"></i></button></a>
                                    <a<?= (is_status_processing(_h((int)$msg->id)) == false ? '' : ' style="display:none !important;"'); ?><?= ae('delete_campaign'); ?> href="#" data-toggle="modal" data-target="#delete-<?= _h((int)$msg->id); ?>" title="Delete"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>

                                    <div class="modal" id="delete-<?= _h((int)$msg->id); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= _h($msg->subject); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= _t('Are you sure you want to delete this campaign?'); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>campaign/<?= _h((int)$msg->id); ?>/d/'"><?= _t('Confirm'); ?></button>
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