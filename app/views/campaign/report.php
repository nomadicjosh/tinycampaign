<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Campaign Report View
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
app\src\Config::set('screen_parent', 'cpgns');
app\src\Config::set('screen_child', 'cpgn');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?=_h($cpgn->subject);?> <?=_t('Report');?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>campaign/"><i class="fa fa-envelope"></i> <?= _t('Campaigns'); ?></a></li>
            <li class="active"><?=_h($cpgn->subject);?> <?=_t('Report');?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= _tc_flash()->showMessage(); ?>
        
        <div class="box box-default">
            <div class="box-body">
                <div id="domains"></div>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
        
        <div class="box box-default">
            <div class="box-body">
                <table id="example2" class="table table-bordered table-hover">
                    <tbody>
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Subject');?></strong></td>
                            <td class="text-center"><?=_h($cpgn->subject);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Date Entered');?></strong></td>
                            <td class="text-center"><?=\Jenssegers\Date\Date::parse(_h($cpgn->addDate))->format('M. d, Y h:i A');?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Date Sent');?></strong></td>
                            <td class="text-center"><?=\Jenssegers\Date\Date::parse(_h($cpgn->sendstart))->format('M. d, Y h:i A');?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Date Finished');?></strong></td>
                            <td class="text-center"><?=\Jenssegers\Date\Date::parse(_h($cpgn->sendfinish))->format('M. d, Y h:i A');?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Sent');?></strong></td>
                            <td class="text-center"><span class="label bg-gray" style="font-size:1em;font-weight: bold;"><?=_h((int)$cpgn->recipients);?></span></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Bounced');?></strong></td>
                            <td class="text-center"><span class="label bg-gray" style="font-size:1em;font-weight: bold;"><?=(_h((int)$cpgn->bounces) > 0 ? '<a href="'.get_base_url().'campaign'.'/'._h((int)$cpgn->id).'/report/bounced/">'._h($unique_bounces).'</a>' : 0);?></span></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('% Bounced');?></strong></td>
                            <td class="text-center"><span class="label bg-gray" style="font-size:1em;font-weight: bold;"><?=($unique_bounces > 0 ? percent($unique_bounces, _h((int)$cpgn->recipients)) : 0);?>%</span></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Opened');?></strong></td>
                            <td class="text-center"><span class="label bg-gray" style="font-size:1em;font-weight: bold;"><?=(_h((int)$cpgn->viewed) > 0 ? '<a href="'.get_base_url().'campaign'.'/'._h((int)$cpgn->id).'/report/opened/">'._h($unique_opens).'</a>' : 0);?></span></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('% Opened');?></strong></td>
                            <td class="text-center"><span class="label bg-gray" style="font-size:1em;font-weight: bold;"><?=($opened > 0 ? percent($unique_opens, _h((int)$cpgn->recipients)) : 0);?>%</span></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Clicked');?></strong></td>
                            <td class="text-center"><span class="label bg-gray" style="font-size:1em;font-weight: bold;"><?=(_h($clicks) > 0 ? '<a href="'.get_base_url().'campaign'.'/'._h((int)$cpgn->id).'/report/clicked/">'._h($unique_clicks).'</a>' : 0);?></span></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('% Clicked');?></strong></td>
                            <td class="text-center"><span class="label bg-gray" style="font-size:1em;font-weight: bold;"><?=($clicks > 0 ? percent($unique_clicks, _h((int)$cpgn->recipients)) : 0);?>%</span></td>
                        </tr>
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Unsubscribed');?></strong></td>
                            <td class="text-center"><span class="label bg-gray" style="font-size:1em;font-weight: bold;"><?=(_h($unique_unsubs) > 0 ? '<a href="'.get_base_url().'campaign'.'/'._h((int)$cpgn->id).'/report/unsubscribed/">'._h($unique_unsubs).'</a>' : 0);?></span></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('% Unsubscribed');?></strong></td>
                            <td class="text-center"><span class="label bg-gray" style="font-size:1em;font-weight: bold;"><?=(_h($unique_unsubs) > 0 ? percent($unique_unsubs, _h((int)$cpgn->recipients)) : 0);?>%</span></td>
                        </tr>
                    </tbody>
                </table>
                <div class="box-footer">
                <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>campaign/'"><?=_t( 'Cancel' );?></button>
            </div>
            </div>
            <!-- /.box-body -->
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