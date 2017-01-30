<?php $app = \Liten\Liten::getInstance();
ob_start();
ob_implicit_flush(0);

/**
 * Subscribe Success View
 *  
 * @license GPLv3
 * 
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app->view->extend('_layouts/dashboard');
$app->view->block('index');
?>
<!DOCTYPE html>
<html>
<head>
  <base href="<?=get_base_url();?>">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?=_t('Subscribe');?><?=' - ' . _h(get_option('system_name'));?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="static/assets/css/bootstrap/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
<?php tc_dashboard_head(); ?>
<!-- Theme style -->
<link rel="stylesheet" href="static/assets/css/AdminLTE.min.css">
<!-- AdminLTE Skins. Choose a skin from the css/skins
     folder instead of downloading all of them to reduce the load. -->
<link rel="stylesheet" href="static/assets/css/skins/_all-skins.min.css">
</head>
<body class="hold-transition <?=_h(get_option('backend_skin'));?> sidebar-collapse sidebar-mini">
<div class="wrapper">

    <header class="main-header">
      <!-- Logo -->
      <div class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><?=get_logo_mini();?></span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><?=get_logo_large();?></span>
      </div>
      <!-- Header Navbar: style can be found in header.less -->
      <nav class="navbar navbar-static-top">
          <div class="break"></div>
      </nav>
    </header>
    
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
      <!-- sidebar: style can be found in sidebar.less -->
      <section class="sidebar">
        <!-- sidebar menu: : style can be found in sidebar.less -->
      </section>
      <!-- /.sidebar -->
    </aside>
    
    <?= $app->view->show('index'); ?>
  
  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b><?=_t('Release');?></b> <?=CURRENT_RELEASE;?>
    </div>
    <strong>Copyright &copy; 2016 <a href="https://codecanyon.net/item/tinycampaign/4755189"><?=_t('tinyCampaign');?></a>.</strong>
  </footer>
</div>
<!-- ./wrapper -->

<script>
var basePath = '<?=get_base_url();?>';
</script>

<!-- jQuery 2.2.3 -->
<script src="static/assets/js/jQuery/jquery-2.2.3.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="static/assets/js/bootstrap/bootstrap.min.js"></script>
<!-- Bootstrap Validator 0.11.7 -->
<script src="static/assets/plugins/bootstrap-validator/validator.js"></script>
<!-- AdminLTE App -->
<script src="static/assets/js/app.min.js"></script>
<?php tc_dashboard_footer(); ?>
</body>
</html>
<?php print_gzipped_page(); ?>