<?php if ( ! defined('BASE_PATH') ) exit('No direct script access allowed');
/**
 * Login View
 *  
 * @license GPLv3
 * 
 * @since       2.0.0
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */

$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/login');
$app->view->block('login');
?>

<div class="login-box">
  <div class="login-logo">
      <a href="<?=get_base_url();?>"><b><?=_h(get_option('system_name'));?></b></a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg"><?=_t('Sign in');?></p>

    <?php 
    /**
     * Prints scripts or data at the top
     * of the login form
     * 
     * @since 2.0.0
     */
    $app->hook->{'do_action'}('login_form_top'); 
    ?>
    
    <form action="<?=get_base_url();?>" method="post" data-toggle="validator" autocomplete="off">
      <div class="form-group has-feedback">
          <input type="text" class="form-control" placeholder="Username or Email" name="uname" required>
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Password" name="password" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-8">
          <div class="checkbox icheck">
            <label>
              <input type="checkbox" name="rememberme" value="yes"> <?=_t('Remember Me');?>
            </label>
          </div>
        </div>
        <!-- /.col -->
        <div class="col-xs-4">
          <button type="submit" class="btn btn-primary btn-block btn-flat"><?=_t('Sign In');?></button>
        </div>
        <!-- /.col -->
      </div>
    </form>

    <a href="#reset" data-toggle="modal"><?=_t('Reset Password');?></a>
    
    <!-- modal -->
    <form method="post" action="<?= get_base_url(); ?>reset-password/" data-toggle="validator" autocomplete="off">
    <div class="modal" id="reset">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Enter Email' );?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><font color="red">*</font> <?= _t('Email'); ?></label>
                        <input type="email" class="form-control" id="email" name="email" required/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                    <button type="submit" class="btn btn-primary pull-right"><?=_t('Submit');?></button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    </form>
    <!-- /.modal -->
    
    <?php 
    /**
     * Prints scripts or data at the bottom
     * of the login form.
     * 
     * @since 2.0.0
     */
    $app->hook->{'do_action'}('login_form_bottom');
    ?>

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
<?php $app->view->stop(); ?>