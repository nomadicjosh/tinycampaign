<?php $app = \Liten\Liten::getInstance();
ob_start();
ob_implicit_flush(0);
$cookie = get_secure_cookie_data('SWITCH_USERBACK');
?>
<!DOCTYPE html>
<html>
<head>
  <base href="<?=get_base_url();?>">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?=(isset($title)) ? $title . ' - ' . _escape(get_option('system_name')) : _escape(get_option('system_name'));?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta name="theme-color" content="#ffffff">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="static/assets/css/bootstrap/lumen-bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Favicon Package -->
  <link rel="apple-touch-icon" sizes="180x180" href="static/assets/img/apple-touch-icon.png">
  <link rel="icon" type="image/png" href="static/assets/img/favicon-32x32.png" sizes="32x32">
  <link rel="icon" type="image/png" href="static/assets/img/favicon-16x16.png" sizes="16x16">
  <link rel="manifest" href="static/assets/img/manifest.json">
  <link rel="mask-icon" href="static/assets/img/safari-pinned-tab.svg" color="#5bbad5">
<!-- jQuery 2.2.3 -->
<script src="static/assets/js/jQuery/jquery-2.2.3.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="static/assets/js/bootstrap/bootstrap.min.js"></script>

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
<body class="hold-transition <?=_escape(get_option('backend_skin'));?> <?=(_escape(get_option('collapse_sidebar')) == 'yes' ? 'sidebar-collapse ' : '');?>sidebar-mini">
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="<?=get_base_url();?>dashboard/" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><?=get_logo_mini();?></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><?=get_logo_large();?></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only"><?=_t('Toggle navigation');?></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <?=get_user_avatar(get_userdata('email'), 160, 'user-image');?>
              <span class="hidden-xs"><?=get_name(get_userdata('id'));?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <?=get_user_avatar(get_userdata('email'), 160, 'image-circle');?>

                <p>
                  <?=get_name(get_userdata('id'));?>
                    <small><?=_t('Member since');?> <?=Jenssegers\Date\Date::parse(get_userdata('date_added'))->format('M Y');?></small>
                </p>
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="<?=get_base_url();?>user/profile/" class="btn btn-default btn-flat"><?=_t('Profile');?></a>
                </div>
                <?php if (isset($_COOKIE['SWITCH_USERBACK'])) : ?>
                <div class="pull-left">
                  <a href="<?=get_base_url();?>user/<?=$cookie->id;?>/switch-back/" class="btn btn-default btn-flat"><?=_t('Switch back');?></a>
                </div>
                <?php endif; ?>
                <div class="pull-right">
                  <a href="<?=get_base_url();?>logout/" class="btn btn-default btn-flat"><?=_t('Logout');?></a>
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
          <li><p>&nbsp;</p></li>
        </ul>
      </div>
    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <?=get_user_avatar(get_userdata('email'), 160, 'img-circle');?>
        </div>
        <div class="pull-left info">
          <p><?=get_name(get_userdata('id'));?></p>
          <a><i class="fa fa-circle text-success"></i> <?=_t('Online');?></a>
        </div>
      </div>
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        <li class="header"><?=_t('MAIN NAVIGATION');?></li>
        <li class="treeview">
          <a href="<?=get_base_url();?>dashboard/">
            <i class="fa fa-tachometer-alt"></i> <span><?=_t('Dashboard');?></span>
          </a>
        </li>
        <li<?=ae('access_settings_screen');?> class="treeview">
          <a href="<?=get_base_url();?>dashboard/flushCache/">
            <i class="fa fa-database"></i> <span><?=_t('Flush Cache');?></span>
          </a>
        </li>
        <li<?=ae('access_settings_screen');?> class="treeview<?=(SCREEN_PARENT === 'handler' ? ' active' : '');?>">
          <a href="#">
            <i class="fa fa-clock-o"></i>
            <span><?=_t('Cronjob Handlers');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'hset' ? ' class="active"' : '');?>><a href="<?=get_base_url();?>cron/setting/"><i class="fa fa-circle-o"></i> <?=_t('Settings');?></a></li>
            <li<?=(SCREEN === 'hnew' ? ' class="active"' : '');?>><a href="<?=get_base_url();?>cron/create/"><i class="fa fa-circle-o"></i> <?=_t('Create Handler');?></a></li>
            <li<?=(SCREEN === 'handlers' ? ' class="active"' : '');?>><a href="<?=get_base_url();?>cron/"><i class="fa fa-circle-o"></i> <?=_t('Handlers');?></a></li>
          </ul>
        </li>
        <li class="treeview<?=(SCREEN_PARENT === 'admin') ? ' active' : '';?>">
          <a href="#">
            <i class="fa fa-th"></i>
            <span><?=_t('Admin');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'general' ? ' class="active"' : '');?><?=ae('access_settings_screen');?>><a href="<?=get_base_url();?>setting/"><i class="fa fa-circle-o"></i> <?=_t('General Settings');?></a></li>
            <li<?=(SCREEN === 'snapshot' ? ' class="active"' : '');?><?=ae('access_settings_screen');?>><a href="<?=get_base_url();?>dashboard/system-snapshot/"><i class="fa fa-circle-o"></i> <?=_t('System Snapshot Report');?></a></li>
            <li<?=(SCREEN === 'smtp' ? ' class="active"' : '');?><?=ae('access_settings_screen');?>><a href="<?=get_base_url();?>setting/smtp/"><i class="fa fa-circle-o"></i> <?=_t('SMTP Settings');?></a></li>
            <li<?=(SCREEN === 'bounce' ? ' class="active"' : '');?><?=ae('access_settings_screen');?>><a href="<?=get_base_url();?>setting/bounce/"><i class="fa fa-circle-o"></i> <?=_t('Bounce Email Settings');?></a></li>
            <li<?=(SCREEN === 'perm' ? ' class="active"' : '');?><?=ae('access_permission_screen');?>><a href="<?=get_base_url();?>permission/"><i class="fa fa-circle-o"></i> <?=_t('Permissions');?></a></li>
            <li<?=(SCREEN === 'aperm' ? ' class="active"' : '');?><?=ae('access_permission_screen');?>><a href="<?=get_base_url();?>permission/add/"><i class="fa fa-circle-o"></i> <?=_t('Add Permission');?></a></li>
            <li<?=(SCREEN === 'role' ? ' class="active"' : '');?><?=ae('access_role_screen');?>><a href="<?=get_base_url();?>role/"><i class="fa fa-circle-o"></i> <?=_t('Roles');?></a></li>
            <li<?=(SCREEN === 'arole' ? ' class="active"' : '');?><?=ae('access_role_screen');?>><a href="<?=get_base_url();?>role/add/"><i class="fa fa-circle-o"></i> <?=_t('Add Role');?></a></li>
            <li<?=(SCREEN === 'error' ? ' class="active"' : '');?><?=ae('access_settings_screen');?>><a href="<?=get_base_url();?>error/"><i class="fa fa-circle-o"></i> <?=_t('Error Logs');?></a></li>
            <li<?=(SCREEN === 'audit' ? ' class="active"' : '');?><?=ae('access_settings_screen');?>><a href="<?=get_base_url();?>audit-trail/"><i class="fa fa-circle-o"></i> <?=_t('Audit Trail');?></a></li>
          </ul>
        </li>
        <li<?=ae('manage_plugins');?> class="treeview<?=(SCREEN_PARENT === 'plugins' ? ' active' : '');?>">
          <a href="#">
            <i class="fa fa-cog"></i>
            <span><?=_t('Plugins');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'plugins' ? ' class="active"' : '');?>><a href="<?=get_base_url();?>plugins/"><i class="fa fa-circle-o"></i> <?=_t('Plugins List');?></a></li>
            <li<?=(SCREEN === 'pinstall' ? ' class="active"' : '');?><?=ae('install_plugins');?>><a href="<?=get_base_url();?>plugins/install/"><i class="fa fa-circle-o"></i> <?=_t('Install Plugin');?></a></li>
            <?php $app->hook->{'do_action'}('plugin_parent_page'); ?>
            <?php $app->hook->{'do_action'}('plugin_child_page'); ?>
          </ul>
        </li>
        <li<?=ae('manage_campaigns');?> class="treeview<?=(SCREEN_PARENT === 'servers' ? ' active' : '');?>">
          <a href="#">
            <i class="fa fa-server"></i>
            <span><?=_t('SMTP Servers');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'cserver' ? ' class="active"' : '');?><?=ae('create_campaign');?>><a href="<?=get_base_url();?>server/create/"><i class="fa fa-circle-o"></i> <?=_t('Create Server');?></a></li>
            <li<?=(SCREEN === 'server' ? ' class="active"' : '');?><?=ae('manage_campaigns');?>><a href="<?=get_base_url();?>server/"><i class="fa fa-circle-o"></i> <?=_t('Manage Servers');?></a></li>
          </ul>
        </li>
        <li<?=ae('manage_email_lists');?> class="treeview<?=(SCREEN_PARENT === 'list' ? ' active' : '');?>">
          <a href="#">
            <i class="fa fa-list-alt"></i>
            <span><?=_t('Email Lists');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'clist' ? ' class="active"' : '');?><?=ae('create_email_list');?>><a href="<?=get_base_url();?>list/create/"><i class="fa fa-circle-o"></i> <?=_t('Create List');?></a></li>
            <li<?=(SCREEN === 'lists' ? ' class="active"' : '');?><?=ae('manage_email_lists');?>><a href="<?=get_base_url();?>list/"><i class="fa fa-circle-o"></i> <?=_t('Manage Email Lists');?></a></li>
            <?php get_email_lists(); ?>
          </ul>
        </li>
        <li<?=ae('manage_campaigns');?> class="treeview<?=(SCREEN_PARENT === 'tpls' ? ' active' : '');?>">
          <a href="#">
            <i class="fa fa-file"></i> <span><?=_t('Templates');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'ctpl' ? ' class="active"' : '');?><?=ae('create_campaign');?>><a href="<?=get_base_url();?>template/create/"><i class="fa fa-circle-o"></i> <?=_t('Create Template');?></a></li>
            <li<?=(SCREEN === 'tpl' ? ' class="active"' : '');?><?=ae('manage_campaigns');?>><a href="<?=get_base_url();?>template/"><i class="fa fa-circle-o"></i> <?=_t('Manage Templates');?></a></li>
          </ul>
        </li>
        <li<?=ae('manage_campaigns');?>  class="treeview<?=(SCREEN_PARENT === 'rules' ? ' active' : '');?>">
          <a href="#">
            <i class="fa fa-ruler-horizontal"></i> <span><?=_t('Rules');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'crule' ? ' class="active"' : '');?><?=ae('create_campaign');?>><a href="<?=get_base_url();?>rlde/create/"><i class="fa fa-circle-o"></i> <?=_t('Create Rule');?></a></li>
            <li<?=(SCREEN === 'rule' ? ' class="active"' : '');?><?=ae('manage_campaigns');?> ><a href="<?=get_base_url();?>rlde/"><i class="fa fa-circle-o"></i> <?=_t('Manage Rules');?></a></li>
          </ul>
        </li>
        <li<?=ae('manage_campaigns');?>  class="treeview<?=(SCREEN_PARENT === 'rss' ? ' active' : '');?>">
          <a href="#">
            <i class="fa fa-rss"></i> <span><?=_t('RSS Campaigns');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'crss' ? ' class="active"' : '');?><?=ae('create_campaign');?>><a href="<?=get_base_url();?>rss-campaign/create/"><i class="fa fa-circle-o"></i> <?=_t('Create RSS Campaign');?></a></li>
            <li<?=(SCREEN === 'rss' ? ' class="active"' : '');?><?=ae('manage_campaigns');?> ><a href="<?=get_base_url();?>rss-campaign/"><i class="fa fa-circle-o"></i> <?=_t('Manage RSS Campaigns');?></a></li>
          </ul>
        </li>
        <li<?=ae('manage_campaigns');?>  class="treeview<?=(SCREEN_PARENT === 'cpgns' ? ' active' : '');?>">
          <a href="#">
            <i class="fa fa-envelope"></i> <span><?=_t('Campaigns');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'ccpgn' ? ' class="active"' : '');?><?=ae('create_campaign');?>><a href="<?=get_base_url();?>campaign/create/"><i class="fa fa-circle-o"></i> <?=_t('Create Campaign');?></a></li>
            <li<?=(SCREEN === 'cpgn' ? ' class="active"' : '');?><?=ae('manage_campaigns');?> ><a href="<?=get_base_url();?>campaign/"><i class="fa fa-circle-o"></i> <?=_t('Manage Campaigns');?></a></li>
          </ul>
        </li>
        <li<?=ae('manage_subscribers');?> class="treeview<?=(SCREEN_PARENT === 'subs' ? ' active' : '');?>">
          <a href="#">
            <i class="fa fa-address-book"></i>
            <span><?=_t('Subscribers');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'asub' ? ' class="active"' : '');?><?=ae('add_subscriber');?>><a href="<?=get_base_url();?>subscriber/add/"><i class="fa fa-circle-o"></i> <?=_t('Add Subscriber');?></a></li>
            <li<?=(SCREEN === 'sub' ? ' class="active"' : '');?><?=ae('manage_subscribers');?>><a href="<?=get_base_url();?>subscriber/"><i class="fa fa-circle-o"></i> <?=_t('Manage Subscribers');?></a></li>
          </ul>
        </li>
        <li class="treeview<?=(SCREEN_PARENT === 'users' ? ' active' : '');?>">
          <a href="#">
            <i class="fa fa-group"></i>
            <span><?=_t('Users');?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(SCREEN === 'auser' ? ' class="active"' : '');?><?=ae('create_user');?>><a href="<?=get_base_url();?>user/add/"><i class="fa fa-circle-o"></i> <?=_t('Add User');?></a></li>
            <li<?=(SCREEN === 'user' ? ' class="active"' : '');?><?=ae('manage_users');?>><a href="<?=get_base_url();?>user/"><i class="fa fa-circle-o"></i> <?=_t('Manage Users');?></a></li>
            <li<?=(SCREEN === 'profile' ? ' class="active"' : '');?>><a href="<?=get_base_url();?>user/profile"><i class="fa fa-circle-o"></i> <?=_t('Your Profile');?></a></li>
          </ul>
        </li>
        <li<?=(SCREEN === 'support' ? ' class="active"' : '');?><?=ae('access_settings_screen');?>>
          <a href="<?=get_base_url();?>dashboard/support/">
            <i class="fa fa-ticket"></i> <span><?=_t('Support');?></span>
          </a>
        </li>
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>

  <?= $app->view->show('dashboard'); ?>
  
  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b><?=_t('Release');?></b> <?=CURRENT_RELEASE;?>
    </div>
    <strong>Copyright &copy; 2016 <a href="https://tinyc.7mediaws.org/"><?=_t('tinyCampaign');?></a>.</strong>
  </footer>
</div>
<!-- ./wrapper -->

<script>
var basePath = '<?=get_base_url();?>';
</script>
<!-- Bootstrap Validator 0.11.7 -->
<script src="static/assets/plugins/bootstrap-validator/validator.js"></script>
<!-- AdminLTE App -->
<script src="static/assets/js/app.min.js"></script>
<?php tc_dashboard_footer(); ?>
</body>
</html>
<?php print_gzipped_page(); ?>