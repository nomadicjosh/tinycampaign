<?php 
/**
 * Subscriber Preferences View
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
    <!-- Main content -->
    <section class="content">
        <?=_tc_flash()->showMessage();?>
    </section>
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>