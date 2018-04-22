<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Setting View
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
define('SCREEN_PARENT', 'admin');
define('SCREEN', 'general');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('General Settings'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('General Settings'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?>
        
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>setting/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('System / Company Name'); ?></label>
                                <input type="text" class="form-control" name="system_name" value="<?= _h(get_option('system_name')); ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('System / Company Email'); ?></label>
                                <input type="text" class="form-control" name="system_email" value="<?= _h(get_option('system_email')); ?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Skin'); ?></label>
                                <select class="form-control select2" name="backend_skin" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="skin-blue"<?=selected('skin-blue', _h(get_option('backend_skin')), false);?>><?=_t('Blue');?></option>
                                    <option value="skin-black"<?=selected('skin-black', _h(get_option('backend_skin')), false);?>><?=_t('Black');?></option>
                                    <option value="skin-purple"<?=selected('skin-purple', _h(get_option('backend_skin')), false);?>><?=_t('Purple');?></option>
                                    <option value="skin-green"<?=selected('skin-green', _h(get_option('backend_skin')), false);?>><?=_t('Green');?></option>
                                    <option value="skin-red"<?=selected('skin-green', _h(get_option('backend_skin')), false);?>><?=_t('Red');?></option>
                                    <option value="skin-blue-light"<?=selected('skin-blue-light', _h(get_option('backend_skin')), false);?>><?=_t('Blue Light');?></option>
                                    <option value="skin-black-light"<?=selected('skin-black-light', _h(get_option('backend_skin')), false);?>><?=_t('Black Light');?></option>
                                    <option value="skin-purple-light"<?=selected('skin-purple-light', _h(get_option('backend_skin')), false);?>><?=_t('Purple Light');?></option>
                                    <option value="skin-green-light"<?=selected('skin-green-light', _h(get_option('backend_skin')), false);?>><?=_t('Green Light');?></option>
                                    <option value="skin-red-light"<?=selected('skin-red-light', _h(get_option('backend_skin')), false);?>><?=_t('Red Light');?></option>
                                    <option value="skin-yellow-light"<?=selected('skin-yellow-light', _h(get_option('backend_skin')), false);?>><?=_t('Yellow Light');?></option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Language'); ?></label>
                                <select class="form-control select2" name="tc_core_locale" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php tc_dropdown_languages(_h(get_option( 'tc_core_locale' ))); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Timezone'); ?></label>
                                <select class="form-control select2" name="system_timezone" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="">&nbsp;</option>
                                    <?php foreach(generate_timezone_list() as $k => $v) : ?>
                                    <option value="<?=$k;?>"<?=selected( _h(get_option( 'system_timezone' )), $k, false ); ?>><?=$v;?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Mail Throttle'); ?></label>
                                <input type="text" class="form-control" name="mail_throttle" value="<?= _h(get_option('mail_throttle')); ?>" required/>
                                <p class="help-block"><?=_t('Value in seconds between each email to be sent.');?></p>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Spam Tolerance'); ?> <a href="#spam" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" name="spam_tolerance" value="<?= _h(get_option('spam_tolerance')); ?>" required/>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><?= _t('Mailing Address'); ?></label>
                                <textarea class="form-control" rows="3" name="mailing_address"><?=_h(get_option('mailing_address'));?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Cookie TTL'); ?></label>
                                <input type="text" class="form-control" name="cookieexpire" value="<?= _h(get_option('cookieexpire')); ?>" required/>
                                <p class="help-block"><?=_t('Value in seconds of how long secure cookies should live.');?></p>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Cookie Path'); ?></label>
                                <input type="text" class="form-control" name="cookiepath" value="<?= _h(get_option('cookiepath')); ?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('API Key'); ?></label>
                                <input type="text" class="form-control" name="api_key" value="<?= _h(get_option('api_key')); ?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Collapse Sidebar'); ?></label>
                                <select class="form-control select2" name="collapse_sidebar" style="width: 100%;">
                                    <option>&nbsp;</option>
                                    <option value="yes"<?=selected('yes',_h(get_option('collapse_sidebar')),false);?>><?=_t('Yes');?></option>
                                    <option value="no"<?=selected('no',_h(get_option('collapse_sidebar')),false);?>><?=_t('No');?></option>
                                </select>
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
    
    <!-- modal -->
    <div class="modal" id="spam">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Spam Tolerance' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "Use this option to set the spam tolerance when emails are subscribed to a list. Note that <em>test@example.com</em> has a tolerance of <em>0.39</em>." );?></p>
                    <p><?=_t( "So, set at <em>15</em>, <em>test@example.com</em> will be seen as valid. However, set at <em>0.3</em>, it will be marked as spam." );?></p>
                    <p><?=_t( "The higher the value, the higher the tolerance and vice versa." );?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
    
</div>
<!-- /.content-wrapper -->
<?php
$app->view->stop();
