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
define('SCREEN_PARENT', 'cpgns');
define('SCREEN', 'cpgn');
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
                <table id="example2" class="table table-bordered table-hover">
                    <tbody>
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Subject');?></strong></td>
                            <td class="text-center"><?=_h($cpgn->subject);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Date Entered');?></strong></td>
                            <td class="text-center"><?=_h($cpgn->addDate);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Date Sent');?></strong></td>
                            <td class="text-center"><?=_h($cpgn->sendstart);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Date Finished');?></strong></td>
                            <td class="text-center"><?=_h($cpgn->sendfinish);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Sent');?></strong></td>
                            <td class="text-center"><?=_h($cpgn->recipients);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Bounces');?></strong></td>
                            <td class="text-center"><?=_h($cpgn->bounces);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Opened');?></strong></td>
                            <td class="text-center"><?=(_h($cpgn->viewed) > 0 ? '<a href="'.get_base_url().'campaign'.'/'._h($cpgn->id).'/report/opened/">'._h($cpgn->viewed).'</a>' : _h($cpgn->viewed));?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('% Opened');?></strong></td>
                            <td class="text-center"><?=($opened > 0 ? percent($unique_opens, _h($cpgn->recipients)) : 0);?>%</td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('Clicked');?></strong></td>
                            <td class="text-center"><?=(_h($clicks) > 0 ? '<a href="'.get_base_url().'campaign'.'/'._h($cpgn->id).'/report/clicked/">'._h($clicks).'</a>' : _h($clicks));?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><strong><?=_t('% Clicked');?></strong></td>
                            <td class="text-center"><?=($clicks > 0 ? percent($unique_clicks, _h($cpgn->recipients)) : 0);?>%</td>
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
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>