<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Manage Roles View
 *  
 * @license GPLv3
 * 
 * @since       3.0.0
 * @package     eduTrac SIS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/dashboard');
$app->view->block('dashboard');
$roles = new app\src\ACL();
define('SCREEN_PARENT', 'admin');
define('SCREEN', 'role');

?>            

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Roles'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Roles'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Name'); ?></th>
                            <th class="text-center"><?= _t('Edit'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $listRoles = $roles->getAllRoles('full');
                        if ($listRoles != '') {
                            foreach ($listRoles as $k => $v) {
                                echo '<tr class="gradeX">' . "\n";
                                echo '<td class="text-center">' . _h($v['Name']) . '</td>' . "\n";
                                echo '<td class="text-center"><a href="' . get_base_url() . 'role/' . _h($v['ID']) . '/" title="View Role" class="btn btn-default"><i class="fa fa-edit"></i></a></td>';
                                echo '</tr>';
                            }
                        }

                        /* if (count($listRoles) < 1) {
                          _e( "No roles yet.<br />" );
                          } */

                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Name'); ?></th>
                            <th class="text-center"><?= _t('Edit'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
            <!-- Form actions -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>role/add/'"><i></i><?= _t('New Role'); ?></button>
            </div>
            <!-- // Form actions END -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>