<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Error Log View
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
$logger = new \app\src\tc_Logger();
define('SCREEN_PARENT', 'admin');
define('SCREEN', 'error');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Error Log'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Error Log'); ?></li>
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
                            <th class="text-center"><?= _t('Error Type'); ?></th>
                            <th class="text-center"><?= _t('String'); ?></th>
                            <th class="text-center"><?= _t('File'); ?></th>
                            <th class="text-center"><?= _t('Line Number'); ?></th>
                            <th class="text-center"><?= _t('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($errors as $error) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= $logger->error_constant_to_name(_h($error->type)); ?></td>
                                <td class="text-center"><?= _h($error->string); ?></td>
                                <td class="text-center"><?=_h($error->file); ?></td>
                                <td class="text-center"><?=_h($error->line); ?></td>
                                <td class="text-center">
                                    <a href="<?= get_base_url(); ?>error/deleteLog/<?= _h($error->id); ?>/" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Error Type'); ?></th>
                            <th class="text-center"><?= _t('String'); ?></th>
                            <th class="text-center"><?= _t('File'); ?></th>
                            <th class="text-center"><?= _t('Line Number'); ?></th>
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