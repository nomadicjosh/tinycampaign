<?php $app = \Liten\Liten::getInstance();
ob_start();
ob_implicit_flush(0);
?>
<!DOCTYPE html>
<html>
<head>
  <base href="<?=get_base_url();?>">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?=_t('Login');?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta name="theme-color" content="#ffffff">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="static/assets/css/bootstrap/lumen-bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="static/assets/css/AdminLTE.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="static/assets/css/iCheck/square/blue.css">
  <!-- Favicon Package -->
  <link rel="apple-touch-icon" sizes="180x180" href="static/assets/img/apple-touch-icon.png">
  <link rel="icon" type="image/png" href="static/assets/img/favicon-32x32.png" sizes="32x32">
  <link rel="icon" type="image/png" href="static/assets/img/favicon-16x16.png" sizes="16x16">
  <link rel="manifest" href="static/assets/img/manifest.json">
  <link rel="mask-icon" href="static/assets/img/safari-pinned-tab.svg" color="#5bbad5">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition login-page">

<?= $app->view->show('login'); ?>

<!-- jQuery 2.2.3 -->
<script src="static/assets/js/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="static/assets/js/bootstrap/bootstrap.min.js"></script>
<!-- iCheck -->
<script src="static/assets/js/iCheck/icheck.min.js"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
  });
</script>
</body>
</html>
<?php print_gzipped_page(); ?>