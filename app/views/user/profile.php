<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Edit Profile View
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
define('SCREEN_PARENT', 'users');
define('SCREEN', 'profile');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('View/Edit Profile'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>user/"><i class="fa fa-group"></i> <?= _t('Users'); ?></a></li>
            <li class="active"><?= _t('View/Edit Profile'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>user/profile/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= _t('Username'); ?></label>
                                <input type="text" class="form-control" value="<?=_h($user->uname);?>" readonly required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('First Name'); ?></label>
                                <input type="text" class="form-control" name="fname" value="<?=_h($user->fname);?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Last Name'); ?></label>
                                <input type="text" class="form-control" name="lname" value="<?=_h($user->lname);?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Email'); ?></label>
                                <input type="text" class="form-control" name="email" value="<?=_h($user->email);?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Address1'); ?></label>
                                <input type="text" class="form-control" name="address1" value="<?=_h($user->address1);?>" >
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Address2'); ?></label>
                                <input type="text" class="form-control" name="address2" value="<?=_h($user->address2);?>" >
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('City'); ?></label>
                                <input type="text" class="form-control" name="city" value="<?=_h($user->city);?>" >
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('State'); ?></label>
                                <select class="form-control select2" name="state" style="width: 100%;">
                                    <option>&nbsp;</option>
                                    <?php table_dropdown('state',null,'code','code','name',_h($user->state)); ?>
                                </select>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><?= _t('Postal Code'); ?></label>
                                <input type="text" class="form-control" name="postal_code" value="<?=_h($user->postal_code);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Country'); ?></label>
                                <select class="form-control select2" name="country" style="width: 100%;">
                                    <option>&nbsp;</option>
                                    <?php table_dropdown('country',null,'iso2','iso2','short_name',_h($user->country)); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Password'); ?></label>
                                <input type="text" class="form-control" name="password" />
                                <p class="help-block"><?=_t('Leave blank if you are not changing your password.');?></p>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Date Created'); ?></label>
                                <input type="text" class="form-control" value="<?= Jenssegers\Date\Date::parse(_h($user->date_added))->format('M. d, Y @ h:i A');?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Last Login'); ?></label>
                                <input type="text" class="form-control" value="<?=(_h($user->LastLogin) > '0000-00-00 00:00:00' ? Jenssegers\Date\Date::parse(_h($user->LastLogin))->format('M. d, Y @ h:i A') : '');?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Modified'); ?></label>
                                <input type="text" class="form-control" value="<?=(_h($user->LastUpdate) > '0000-00-00 00:00:00' ? Jenssegers\Date\Date::parse(_h($user->LastUpdate))->format('M. d, Y @ h:i A') : '');?>" readonly>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        
                    </div>
                    <!-- /.row -->
                </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary"><?=_t('Save');?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>user/'"><?=_t( 'Cancel' );?></button>
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
