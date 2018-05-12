<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Support View
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
app\src\Config::set('screen_parent', 'support');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Support'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Support'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?>
        
        <!-- <section class="flash_message"><div class="alert alert-warning center" style="color:#333 !important;"><?=sprintf(_t('If you found a bug or need help with a particular feature, use the form below to contact support. If you need coding help, etc., this type of support/help is handled through the <a href="%s">support forums</a> only.'),'https://codecanyon.7mediaws.org/forums/');?></div></section> -->
        
        <div class="box box-default">
            <!-- form start -->
            <iframe src="https://tinyc.7mediaws.org/" width="100%" height="900" marginwidth="0" marginheight="0" frameborder="0">
			  <p><?=_t( 'Your browser does not support iframes.' );?></p>
			</iframe>
            
            <!-- <form method="post" action="<?= get_base_url(); ?>dashboard/support/" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Email Address'); ?></label>
                                <input type="text" class="form-control" name="email" value="<?=_h(get_userdata('email')); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Name'); ?></label>
                                <input type="text" class="form-control" name="name" value="<?=_h(get_name(get_userdata('id'))); ?>" required>
                            </div>
                            
                        </div>
                        
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Help Topic'); ?></label>
                                <select class="form-control select2" name="topic" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="1"<?=selected('1', $app->req->_post('topic'), false);?>><?=_t('General');?></option>
                                    <option value="12"<?=selected('12', $app->req->_post('topic'), false);?>><?=_t('Billing / Sales');?></option>
                                    <option value="11"<?=selected('11', $app->req->_post('topic'), false);?>><?=_t('Problem / Access Issue');?></option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Issue Summary'); ?></label>
                                <input type="text" class="form-control" name="summary" value="<?=$app->req->_post('summary'); ?>" required>
                            </div>
                            
                        </div>
            
                        <div class="col-md-12">
                            
                            <div class="form-group">
                                <label><?= _t('Issue Details'); ?></label>
                                <textarea class="form-control" rows="10" name="details" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Attachment'); ?></label>
                                <input type="file" class="form-control" name="attachment" />
                            </div>
                            
                        </div>
            
                    </div>
            
                </div>
            
            <div class="box-footer">
                <button type="submit" class="btn btn-primary"><?=_t('Submit');?></button>
            </div>
        </form> -->
        <!-- form end -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php
$app->view->stop();
