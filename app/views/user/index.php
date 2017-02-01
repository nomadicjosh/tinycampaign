<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * User's List View
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
define('SCREEN_PARENT', 'users');
define('SCREEN', 'user');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Manage Users'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Manage Users'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Username'); ?></th>
                            <th class="text-center"><?= _t('First Name'); ?></th>
                            <th class="text-center"><?= _t('Last Name'); ?></th>
                            <th class="text-center"><?= _t('Status'); ?></th>
                            <th class="text-center"><?= _t('Role'); ?></th>
                            <th class="text-center"><?= _t('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= _h($user->uname); ?></td>
                                <td class="text-center"><?= _h($user->fname); ?></td>
                                <td class="text-center"><?= _h($user->lname); ?></td>
                                <td class="text-center"><?=(_h($user->status) == 1 ? _t('Active') : _t('Inactive')); ?></td>
                                <td class="text-center"><?= _h($user->roleName); ?></td>
                                <td class="text-center">
                                    <a href="<?= get_base_url(); ?>user/<?= _h($user->id); ?>/" data-toggle="tooltip" data-placement="top" title="View/Edit"><button class="btn bg-yellow"><i class="fa fa-eye"></i></button></a>
                                    <a href="<?= get_base_url(); ?>user/<?= _h($user->id); ?>/perm/" data-toggle="tooltip" data-placement="top" title="Edit Permissions"><button class="btn bg-purple"><i class="fa fa-key"></i></button></a>
                                    <?php if(!isset($_COOKIE['SWITCH_USERBACK']) && _h($user->id) != get_userdata('id')) : ?>
                                    <a<?=ae('switch_user');?> href="<?= get_base_url(); ?>user/<?= _h($user->id); ?>/switch-to/" data-toggle="tooltip" data-placement="top" title="Switch to"><button class="btn bg-blue"><i class="fa fa-exchange"></i></button></a>
                                    <?php endif; ?>
                                    <?php if($user->id != 1) : ?>
                                    <a<?=ae('delete_user');?> href="#" data-toggle="modal" data-target="#delete-<?= _h($user->id); ?>"><button class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>
                                    <?php endif; ?>

                                    <div class="modal" id="delete-<?= _h($user->id); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= get_name(_h($user->id)); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?=_t('Are you sure you want to delete this user?');?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>user/<?= _h($user->id); ?>/d/'"><?= _t('Confirm'); ?></button>
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
                            <th class="text-center"><?= _t('Username'); ?></th>
                            <th class="text-center"><?= _t('First Name'); ?></th>
                            <th class="text-center"><?= _t('Last Name'); ?></th>
                            <th class="text-center"><?= _t('Status'); ?></th>
                            <th class="text-center"><?= _t('Role'); ?></th>
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