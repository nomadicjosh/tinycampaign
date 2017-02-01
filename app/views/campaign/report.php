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
                            <td class="text-center"><?=_t('Subject');?></td>
                            <td class="text-center"><?=_h($cpgn->subject);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><?=_t('Date Entered');?></td>
                            <td class="text-center"><?=_h($cpgn->addDate);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><?=_t('Date Sent');?></td>
                            <td class="text-center"><?=_h($cpgn->sendstart);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><?=_t('Date Finished');?></td>
                            <td class="text-center"><?=_h($cpgn->sendfinish);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><?=_t('Sent');?></td>
                            <td class="text-center"><?=_h($cpgn->recipients);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><?=_t('Bounces');?></td>
                            <td class="text-center"><?=_h($cpgn->bounces);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><?=_t('Opened');?></td>
                            <td class="text-center"><?=_h($cpgn->viewed);?></td>
                        </tr>
                        
                        <tr class="gradeX">
                            <td class="text-center"><?=_t('% Opened');?></td>
                            <td class="text-center"><?=($count > 0 ? percent($count, _h($cpgn->recipients)) : 0);?>%</td>
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