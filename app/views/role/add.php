<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Add Role View
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
TinyC\Config::set('screen_parent', 'admin');
TinyC\Config::set('screen_child', 'arole');

?>

<script type="text/javascript">
    $(".panel").show();
    setTimeout(function () {
        $(".panel").hide();
    }, 10000);
</script>     

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Add Role'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>role/"><i class="fa fa-user-circle"></i> <?= _t('Roles'); ?></a></li>
            <li class="active"><?= _t('Add Role'); ?></li>
        </ol>
    </section> 

    <!-- Main content -->
    <section class="content">

        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>role/add/" data-toggle="validator" autocomplete="off">
                <div class="box-body">

                    <!-- Group -->
                    <div class="form-group">
                        <label class="col-md-3 control-label" for="roleName"><font color="red">*</font> <?= _t('Role Name'); ?></label>
                        <div class="col-md-12"><input class="form-control" name="roleName" type="text" required/></div>
                    </div>
                    <!-- // Group END -->

                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><?= _t('Permission'); ?></th>
                                <th class="text-center"><?= _t('Allow'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php role_perm(); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><?= _t('Permission'); ?></th>
                                <th class="text-center"><?= _t('Allow'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.box-body -->
                <!-- Form actions -->
                <div class="box-footer">
                    <button type="submit" name="Submit" class="btn btn-primary"><i></i><?= _t('Save'); ?></button>
                    <button type="button" class="btn btn-icon btn-primary glyphicons circle_ok" onclick="window.location = '<?= get_base_url(); ?>role/'"><?= _t('Cancel'); ?></button>
                </div>
            </form>
            <!-- // Form actions END -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>