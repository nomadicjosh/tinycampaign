<?php 
/**
 * Archive View
 *  
 * @license GPLv3
 * 
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/index');
$app->view->block('index');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?=_h($cpgn->subject);?></h1>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?>
        
        <?=_escape($cpgn->html);?>

    </section>
    <!-- /.content -->
    <div class="box-footer">
        <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>archive/'"><?=_t( 'Go to Archives' );?></button>
    </div>
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>
