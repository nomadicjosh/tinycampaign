<?php if ( ! defined('BASE_PATH') ) exit('No direct script access allowed');
/**
 * Error View
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
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?=_t('404 Error Page');?></h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> <?=_t('Home');?></a></li>
        <li class="active"><?=_t('404 error');?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?>
        
      <div class="error-page">
        <h2 class="headline text-yellow"> 404</h2>

        <div class="error-content">
          <h3><i class="fa fa-warning text-yellow"></i> <?=_t('Oops! Page not found.');?></h3>

          <p>
            <?=_t('We could not find the page you were looking for.');?>
            <?=_t(sprintf('Meanwhile, you may <a href="%s">return to dashboard</a>.', get_base_url().'dashboard/'));?>
          </p>
          
        </div>
        <!-- /.error-content -->
      </div>
      <!-- /.error-page -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?php $app->view->stop(); ?>