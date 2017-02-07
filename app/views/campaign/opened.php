<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Opened Campaign Report View
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
        <h1><?= _t('Opened Campaign Report'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>campaign/"><i class="fa fa-envelope"></i> <?= _t('Campaigns'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>campaign/<?=(int)_h($cpgn->id);?>/report/"><i class="fa fa-flag"></i> <?=_h($cpgn->subject);?> <?= _t('Campaign Report'); ?></a></li>
            <li class="active"><?= _t('Opened Campaign Report'); ?></li>
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
                            <th class="text-center"><?= _t('Subscriber'); ?></th>
                            <th class="text-center"><?= _t('First Viewed'); ?></th>
                            <th class="text-center"><?= _t('Times Viewed'); ?></th>
                            <th class="text-center"><?= _t('Last Viewed'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($opens as $opened) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><a href="<?=get_base_url();?>subscriber/<?=(int)_h($opened->sid);?>/"><?= _h($opened->email); ?></a></td>
                                <td class="text-center"><?= _h($opened->first_open); ?></td>
                                <td class="text-center"><?= (int)_h($opened->viewed); ?></td>
                                <td class="text-center"><?=_h($opened->LastUpdate); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Subscriber'); ?></th>
                            <th class="text-center"><?= _t('First Viewed'); ?></th>
                            <th class="text-center"><?= _t('Times Viewed'); ?></th>
                            <th class="text-center"><?= _t('Last Viewed'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
            <!-- Form actions -->
            <div class="box-footer">
                <button type="submit" class="btn btn-icon btn-primary glyphicons circle_ok" onclick="window.location = '<?= get_base_url(); ?>campaign/<?=(int)_h($cpgn->id);?>/report/'"><i></i><?= _t('Cancel'); ?></button>
            </div>
            <!-- // Form actions END -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>