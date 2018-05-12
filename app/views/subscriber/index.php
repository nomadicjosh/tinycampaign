<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Subscriber List View
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
define('SCREEN_PARENT', 'subs');
define('SCREEN', 'sub');
?>

<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Lookup Subscribers'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Lookup Subscribers'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= _tc_flash()->showMessage(); ?>

        <div class="box box-default">
            <form method="get" action="<?= get_base_url(); ?>subscriber/" data-toggle="validator" autocomplete="off" id="form">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= _t('Spammer'); ?></label>
                                <input type="hidden" name="lookup" value="true" />
                                <select class="form-control select2" name="spammer" style="width: 100%;">
                                    <option value="">&nbsp;</option>
                                    <option value="1"<?=selected(if_not_null($app->req->get['spammer']), '1', false);?>><?= _t('Yes'); ?></option>
                                    <option value="0"<?=selected(if_not_null($app->req->get['spammer']), '0', false);?>><?= _t('No'); ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><?= _t('Exception'); ?></label>
                                <select class="form-control select2" name="exception" style="width: 100%;">
                                    <option value="">&nbsp;</option>
                                    <option value="1"<?=selected(if_not_null($app->req->get['exception']), '1', false);?>><?= _t('Yes'); ?></option>
                                    <option value="0"<?=selected(if_not_null($app->req->get['exception']), '0', false);?>><?= _t('No'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= _t('Show only unconfirmed'); ?></label>
                                <input type="checkbox" name="uncomfirmed" class="minimal" value="0" />
                            </div>

                            <div class="form-group">
                                <label><?= _t('Show only blacklisted'); ?></label>
                                <input type="checkbox" name="blacklisted" class="minimal" value="3" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Email'); ?></label>
                                <input type="text" name="email" class="form-control" value="<?= if_not_null($app->req->get['email']);?>" />
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><?= _t('Search'); ?></button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Name'); ?></th>
                            <th class="text-center"><?= _t('Email'); ?></th>
                            <th class="text-center"><?= _t('Blacklisted'); ?></th>
                            <th class="text-center"><?= _t('Add Date'); ?></th>
                            <th class="text-center"><?= _t('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($subscribers != '') : foreach ($subscribers as $subscriber) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= _h($subscriber->fname); ?> <?= _h($subscriber->lname); ?></td>
                                <td class="text-center"><?= _h($subscriber->email); ?></td>
                                <td class="text-center">
                                    <span class="label <?= tc_blacklist_status_label(_h($subscriber->allowed)); ?>" style="font-size:1em;font-weight: bold;">
                                        <?= (_h($subscriber->allowed) == 'true' ? 'No' : 'Yes'); ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= Jenssegers\Date\Date::parse(_h($subscriber->addDate))->format('M. d, Y @ h:i A'); ?></td>
                                <td class="text-center">
                                    <a href="<?= get_base_url(); ?>subscriber/<?= _h((int) $subscriber->id); ?>/" data-toggle="tooltip" data-placement="top" title="View/Edit"><button type="button" class="btn bg-yellow"><i class="fa fa-edit"></i></button></a>
                                    <a<?= ae('delete_subscriber'); ?> href="#" data-toggle="modal" data-target="#delete-<?= _h((int) $subscriber->id); ?>"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>

                                    <!-- modal -->
                                    <div class="modal" id="delete-<?= _h((int) $subscriber->id); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= _h($subscriber->email); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= _t('Are you sure you want to delete this subscriber?'); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>subscriber/<?= _h((int) $subscriber->id); ?>/d/'"><?= _t('Confirm'); ?></button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <!-- /.modal -->
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Name'); ?></th>
                            <th class="text-center"><?= _t('Email'); ?></th>
                            <th class="text-center"><?= _t('Blacklisted'); ?></th>
                            <th class="text-center"><?= _t('Add Date'); ?></th>
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