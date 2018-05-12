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
TinyC\Config::set('screen_parent', 'cpgns');
TinyC\Config::set('screen_child', 'cpgn');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Opened Campaign Report'); ?></h1> <br /><span class="label bg-green-gradient" style="font-size:1em;font-weight: bold;"><?=_t('Total Views');?>: <?= _h($sum); ?></span>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>campaign/"><i class="fa fa-envelope"></i> <?= _t('Campaigns'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>campaign/<?=_h((int)$cpgn->id);?>/report/"><i class="fa fa-flag"></i> <?=_h($cpgn->subject);?> <?= _t('Campaign Report'); ?></a></li>
            <li class="active"><?= _t('Opened Campaign Report'); ?></li>
        </ol>
    </section>
    
    <section class="content">

        <?= _tc_flash()->showMessage(); ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-body">
                        <div id="dayopens"></div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-body">
                        <div id="houropens"></div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Subscriber'); ?></th>
                            <th class="text-center"><?= _t('First Viewed'); ?></th>
                            <th class="text-center"><?= _t('Response Time'); ?></th>
                            <th class="text-center"><?= _t('Times Viewed'); ?></th>
                            <th class="text-center"><?= _t('Last Viewed'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($opens as $opened) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><a href="<?=get_base_url();?>subscriber/<?=_h((int)$opened->sid);?>/"><?= _h($opened->email); ?></a></td>
                                <td class="text-center"><?= \Jenssegers\Date\Date::parse(_h($opened->first_open))->format('M. d, Y h:i A'); ?></td>
                                <td class="text-center"><?= tc_response_time(_h((int)$opened->cid), _h((int)$opened->sid), _h($opened->first_open)); ?></td>
                                <td class="text-center"><span class="label bg-gray" style="font-size:1em;font-weight: bold;"><?= _h((int)$opened->viewed); ?></span></td>
                                <td class="text-center"><?= \Jenssegers\Date\Date::parse(_h($opened->LastUpdate))->format('M. d, Y h:i A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Subscriber'); ?></th>
                            <th class="text-center"><?= _t('First Viewed'); ?></th>
                            <th class="text-center"><?= _t('Response Time'); ?></th>
                            <th class="text-center"><?= _t('Times Viewed'); ?></th>
                            <th class="text-center"><?= _t('Last Viewed'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
            <!-- Form actions -->
            <div class="box-footer">
                <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>campaign/<?=_h((int)$cpgn->id);?>/report/'"><i></i><?= _t('Cancel'); ?></button>
            </div>
            <!-- // Form actions END -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<script>
    var did = '<?=_h($cpgn->id);?>';
</script>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>