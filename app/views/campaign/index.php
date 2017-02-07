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
                                    <?= ucfirst(_h($msg->status)); ?>
                                    <?php if($to_send > 0) : ?>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar" style="width: <?=percent($sent, _h($all));?>%"><?=percent($sent, _h($all));?>%</div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= Jenssegers\Date\Date::parse(_h($msg->sendstart))->format('M. d, Y @ h:i A'); ?></td>
                                <td class="text-center"><?= (_h($msg->sendfinish) != '' ? Jenssegers\Date\Date::parse(_h($msg->sendfinish))->format('M. d, Y @ h:i A') : ''); ?></td>
                                <?php if ((int)_h($msg->recipients) < Node::table(_h($msg->node))->findAll()->count()) : ?>
                                    <td class="text-center"><?= (int)_h($msg->recipients); ?> / <?= Node::table(_h($msg->node))->findAll()->count(); ?></td>
                                <?php else : ?>
                                    <td class="text-center"><?= (int)_h($msg->recipients); ?></td>
                                <?php endif; ?>
                                <td class="text-center">
                                    <a href="<?= get_base_url(); ?>campaign/<?= (int)_h($msg->id); ?>/" data-toggle="tooltip" data-placement="top" title="View/Edit"><button class="btn bg-yellow"><i class="fa fa-edit"></i></button></a>
                                    <a<?= (is_status_ready((int)_h($msg->id)) == false ? ' style="display:none !important;"' : ''); ?> href="<?= get_base_url(); ?>campaign/<?= (int)_h($msg->id); ?>/queue/" data-toggle="tooltip" data-placement="top" title="Send to Queue"><button class="btn bg-green"><i class="fa fa-arrow-right"></i></button></a>
                                    <a<?= (is_status_processing((int)_h($msg->id)) == true ? '' : ' style="display:none !important;"'); ?> href="<?= get_base_url(); ?>campaign/<?= (int)_h($msg->id); ?>/pause/" data-toggle="tooltip" data-placement="top" title="Pause Queue"><button class="btn bg-orange"><i class="fa fa-pause"></i></button></a>
                                    <a<?= (is_status_paused((int)_h($msg->id)) == true ? '' : ' style="display:none !important;"'); ?> href="<?= get_base_url(); ?>campaign/<?= (int)_h($msg->id); ?>/resume/" data-toggle="tooltip" data-placement="top" title="Resume Queue"><button class="btn bg-orange"><i class="fa fa-play"></i></button></a>
                                    <a href="<?= get_base_url(); ?>campaign/<?= (int)_h($msg->id); ?>/report/" data-toggle="tooltip" data-placement="top" title="Report"><button class="btn bg-blue"><i class="fa fa-area-chart"></i></button></a>
                                    <a<?= (_h($msg->status) == 'sent' ? ' style="display:none !important;"' : ''); ?> href="#" data-toggle="modal" data-target="#smtp-<?= (int)_h($msg->id); ?>" title="Send Test"><button class="btn bg-purple"><i class="fa fa-paper-plane"></i></button></a>
                                    <a<?= (is_status_processing((int)_h($msg->id)) == false ? '' : ' style="display:none !important;"'); ?><?= ae('delete_campaign'); ?> href="#" data-toggle="modal" data-target="#delete-<?= (int)_h($msg->id); ?>" title="Delete"><button class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>

                                    <div class="modal" id="delete-<?= (int)_h($msg->id); ?>">
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
                                                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>campaign/<?= (int)_h($msg->id); ?>/d/'"><?= _t('Confirm'); ?></button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <!-- /.modal -->

                                    <div class="modal" id="smtp-<?= (int)_h($msg->id); ?>">
                                        <form method="post" action="<?= get_base_url(); ?>campaign/<?= (int)_h($msg->id); ?>/test/" data-toggle="validator" autocomplete="off">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title"><?= _t('Choose Server'); ?></h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <select class="form-control select2" name="server" style="width: 100%;" required>
                                                            <option>&nbsp;</option>
                                                            <?php get_user_servers(); ?>
                                                        </select>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                        <button type="submit" class="btn btn-primary"><?= _t('Send'); ?></button>
                                                    </div>
                                                </div>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </form>
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