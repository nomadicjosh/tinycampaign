<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Add New Subscriber View
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
define('SCREEN', 'asub');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Add Subscriber'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Add Subscriber'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>subscriber/add/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= _t('First Name'); ?></label>
                                <input type="text" class="form-control" name="fname" value="<?=(_h($app->req->post['fname']) != '' ? _h($app->req->post['fname']) : '');?>" />
                            </div>

                            <div class="form-group">
                                <label><?= _t('Last Name'); ?></label>
                                <input type="text" class="form-control" name="lname" value="<?=(_h($app->req->post['lname']) != '' ? _h($app->req->post['lname']) : '');?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Email'); ?></label>
                                <input type="text" class="form-control" name="email" value="<?=(_h($app->req->post['email']) != '' ? _h($app->req->post['email']) : '');?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Address 1'); ?></label>
                                <input type="text" class="form-control" name="address1" value="<?=(_h($app->req->post['address1']) != '' ? _h($app->req->post['address1']) : '');?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Address 2'); ?></label>
                                <input type="text" class="form-control" name="address2" value="<?=(_h($app->req->post['address2']) != '' ? _h($app->req->post['address2']) : '');?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('City'); ?></label>
                                <input type="text" class="form-control" name="city" value="<?=(_h($app->req->post['city']) != '' ? _h($app->req->post['city']) : '');?>" />
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                             <div class="form-group">
                                <label><?= _t('State'); ?></label>
                                <select class="form-control select2" name="state" style="width: 100%;">
                                    <option>&nbsp;</option>
                                    <?php table_dropdown('state',null,'code','code','name',(_h($app->req->post['state']) != '' ? _h($app->req->post['start']) : '')); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Postal Code'); ?></label>
                                <input type="text" class="form-control" name="postal_code" value="<?=(_h($app->req->post['postal_code']) != '' ? _h($app->req->post['postal_code']) : '');?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Country'); ?></label>
                                <select class="form-control select2" name="country" style="width: 100%;">
                                    <option>&nbsp;</option>
                                    <?php table_dropdown('country', null, 'iso2', 'iso2', 'short_name',(_h($app->req->post['country']) != '' ? _h($app->req->post['country']) : '')); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Lists'); ?></label><br />
                                <ul><?php get_user_lists(); ?></ul>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary"><?=_t('Submit');?></button>
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
