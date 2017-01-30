<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use Jenssegers\Date\Date;

/**
 * Audit Trail View
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
define('SCREEN_PARENT', 'admin');
define('SCREEN', 'audit');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Audit Trail'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Audit Trail'); ?></li>
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
                            <th class="text-center"><?= _t('Action'); ?></th>
                            <th class="text-center"><?= _t('Process'); ?></th>
                            <th class="text-center"><?= _t('Record'); ?></th>
                            <th class="text-center"><?= _t('Username'); ?></th>
                            <th class="text-center"><?= _t('Action Date'); ?></th>
                            <th class="text-center"><?= _t('Expire Date'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audit as $aud) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= _h($aud->action); ?></td>
                                <td class="text-center"><?= _h($aud->process); ?></td>
                                <td class="text-center"><?= _h($aud->record); ?></td>
                                <td class="text-center"><?= _h($aud->uname); ?></td>
                                <td class="text-center"><?= Date::parse(_h($aud->created_at))->format('D, M d, o'); ?></td>
                                <td class="text-center"><?= Date::parse(_h($aud->expires_at))->format('D, M d, o'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Action'); ?></th>
                            <th class="text-center"><?= _t('Process'); ?></th>
                            <th class="text-center"><?= _t('Record'); ?></th>
                            <th class="text-center"><?= _t('Username'); ?></th>
                            <th class="text-center"><?= _t('Action Date'); ?></th>
                            <th class="text-center"><?= _t('Expire Date'); ?></th>
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