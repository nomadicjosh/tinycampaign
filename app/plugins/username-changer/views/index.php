<?php if ( ! defined('BASE_PATH') ) exit('No direct script access allowed');
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/dashboard');
$app->view->block('dashboard');
define('SCREEN_PARENT', 'plugins');
define('SCREEN', 'uchanger');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('You are here', 'username-changer'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard', 'username-changer'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>plugins/"><i class="fa fa-cog"></i> <?= _t('Plugins List', 'username-changer'); ?></a></li>
            <li class="active"><?= _t('You are here', 'username-changer'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>username-changer/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Current Username', 'username-changer'); ?></label>
                                <select name="old_uname" id="term" class="form-control" required>
                                    <option value="">&nbsp;</option>
                                    <?php uc_get_username_list(); ?>
                                </select>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                           <div class="form-group">
                                <label><font color="red">*</font> <?= _t('New Username', 'username-changer'); ?></label>
                                <input id='input01' class="form-control" name="new_uname" type="text" required/>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        
                    </div>
                    <!-- /.row -->
                </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary"><?= _t('Submit', 'username-changer'); ?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>plugins/'"><?=_t( 'Cancel', 'username-changer' );?></button>
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