<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Emails Bounced Campaign Report View
 *  
 * @license GPLv3
 * 
 * @since       2.0.5
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
        <h1><?= _t('Emails Bounced Campaign Report'); ?></h1> <br /><span class="label bg-green-gradient" style="font-size:1em;font-weight: bold;"><?=_t('Total Bounces');?>: <?= _escape($sum); ?></span>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>campaign/"><i class="fa fa-envelope"></i> <?= _t('Campaigns'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>campaign/<?=_escape((int)$cpgn->id);?>/report/"><i class="fa fa-flag"></i> <?=_escape($cpgn->subject);?> <?= _t('Campaign Report'); ?></a></li>
            <li class="active"><?= _t('Emails Bounced Campaign Report'); ?></li>
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
                            <th class="text-center"><?= _t('Bounce Count'); ?></th>
                            <th class="text-center"><?= _t('Rule Type'); ?></th>
                            <th class="text-center"><?= _t('Bounce Reason'); ?></th>
                            <th class="text-center"><?= _t('Date Bounced'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bounces as $bounced) : $sub = get_subscriber_by('id', _escape((int)$bounced->sid)); ?>
                            <tr class="gradeX">
                                <td class="text-center"><a href=""><?= get_sub_name(_escape((int)$bounced->sid)); ?></a></td>
                                <td class="text-center"><?=_escape($sub->bounces);?></td>
                                <td class="text-center"><?=_escape($bounced->type);?></td>
                                <td class="text-center"></td>
                                <td class="text-center"><?= \Jenssegers\Date\Date::parse(_escape($bounced->date_added))->format('M. d, Y h:i A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Subscriber'); ?></th>
                            <th class="text-center"><?= _t('Bounce Count'); ?></th>
                            <th class="text-center"><?= _t('Rule Type'); ?></th>
                            <th class="text-center"><?= _t('Bounce Reason'); ?></th>
                            <th class="text-center"><?= _t('Date Bounced'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
            <!-- Form actions -->
            <div class="box-footer">
                <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>campaign/<?=_escape((int)$cpgn->id);?>/report/'"><i></i><?= _t('Cancel'); ?></button>
            </div>
            <!-- // Form actions END -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<script>
    var did = '<?=_escape($cpgn->id);?>';
</script>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>