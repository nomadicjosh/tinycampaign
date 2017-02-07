<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Edit Subscriber View
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
define('SCREEN_PARENT', 'subs');
define('SCREEN', 'sub');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('View/Edit Subscriber'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>subscriber/"><i class="fa fa-group"></i> <?= _t('Subscribers'); ?></a></li>
            <li class="active"><?= _t('View/Edit Subscriber'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>subscriber/<?=_h((int)$subscriber->id);?>/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= _t('First Name'); ?></label>
                                <input type="text" class="form-control" name="fname" value="<?=_h($subscriber->fname);?>" />
                            </div>

                            <div class="form-group">
                                <label><?= _t('Last Name'); ?></label>
                                <input type="text" class="form-control" name="lname" value="<?=_h($subscriber->lname);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Email'); ?></label>
                                <input type="email" class="form-control" name="email" value="<?=_h($subscriber->email);?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Address 1'); ?></label>
                                <input type="text" class="form-control" name="address1" value="<?=_h($subscriber->address1);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Address 2'); ?></label>
                                <input type="text" class="form-control" name="address2" value="<?=_h($subscriber->address2);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('City'); ?></label>
                                <input type="text" class="form-control" name="city" value="<?=_h($subscriber->city);?>" />
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><?= _t('State'); ?></label>
                                <select class="form-control select2" name="state" style="width: 100%;">
                                    <option value="NULL">&nbsp;</option>
                                    <?php table_dropdown('state','code <> "NULL"','code','code','name',_h($subscriber->state)); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Postal Code'); ?></label>
                                <input type="text" class="form-control" name="postal_code" value="<?=_h($subscriber->postal_code);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Country'); ?></label>
                                <select class="form-control select2" name="country" style="width: 100%;">
                                    <option value="NULL">&nbsp;</option>
                                    <?php table_dropdown('country', 'iso2 <> "NULL"', 'iso2', 'iso2', 'short_name', _h($subscriber->country)); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Lists'); ?></label><br />
                                <ul><?php get_user_lists(_h((int)$subscriber->id)); ?></ul>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button<?=ie('subscriber_inquiry_only');?> type="submit" class="btn btn-primary"><?=_t('Submit');?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>subscriber/'"><?=_t( 'Cancel' );?></button>
            </div>
        </form>
        <!-- form end -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>
