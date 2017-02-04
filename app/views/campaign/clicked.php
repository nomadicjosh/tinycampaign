<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * URLs Clicked Campaign Report View
 *  
 * @license GPLv3
 * 
 * @since       2.0.1
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
        <h1><?= _t('URLs Clicked Campaign Report'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>campaign/"><i class="fa fa-envelope"></i> <?= _t('Campaigns'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>campaign/<?=_h($cpgn->id);?>/report/"><i class="fa fa-flag"></i> <?=_h($cpgn->subject);?> <?= _t('Campaign Report'); ?></a></li>
            <li class="active"><?= _t('URLs Clicked Campaign Report'); ?></li>
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
                            <th class="text-center"><?= _t('URL'); ?></th>
                            <th class="text-center"><?= _t('Subscriber'); ?></th>
                            <th class="text-center"><?= _t('First Clicked'); ?></th>
                            <th class="text-center"><?= _t('Times Clicked'); ?></th>
                            <th class="text-center"><?= _t('Last Clicked'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clicks as $clicked) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= _h($clicked->url); ?></td>
                                <td class="text-center"><a href="<?=get_base_url();?>subscriber/<?=_h($clicked->sid);?>/"><?= _h($clicked->email); ?></a></td>
                                <td class="text-center"><?= _h($clicked->addDate); ?></td>
                                <td class="text-center"><?= _h($clicked->clicked); ?></td>
                                <td class="text-center"><?=_h($clicked->LastUpdate); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('URL'); ?></th>
                            <th class="text-center"><?= _t('Subscriber'); ?></th>
                            <th class="text-center"><?= _t('First Clicked'); ?></th>
                            <th class="text-center"><?= _t('Times Clicked'); ?></th>
                            <th class="text-center"><?= _t('Last Clicked'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
            <!-- Form actions -->
            <div class="box-footer">
                <button type="submit" class="btn btn-icon btn-primary glyphicons circle_ok" onclick="window.location = '<?= get_base_url(); ?>campaign/<?=_h($cpgn->id);?>/report/'"><i></i><?= _t('Cancel'); ?></button>
            </div>
            <!-- // Form actions END -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>