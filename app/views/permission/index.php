<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Manage Permissions View
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
$perms = new app\src\ACL();
define('SCREEN_PARENT', 'admin');
define('SCREEN', 'perm');
?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Permissions'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Permissions'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
         <?=_tc_flash()->showMessage();?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Key'); ?></th>
                            <th class="text-center"><?= _t('Name'); ?></th>
                            <th class="text-center"><?= _t('Edit'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $listPerms = $perms->getAllPerms('full');
                        if ($listPerms != '') {
                            foreach ($listPerms as $k => $v) {
                                echo '<tr class="gradeX">';
                                echo '<td class="text-center">' . _h($v['Key']) . '</td>';
                                echo '<td class="text-center">' . _h($v['Name']) . '</td>';
                                echo '<td class="text-center"><a href="' . get_base_url() . 'permission/' . (int)_h($v['ID']) . '/" data-toggle="tooltip" data-placement="top" title="View/Edit" class="btn bg-yellow"><i class="fa fa-edit"></i></a></td>';
                                echo '</tr>' . "\n";
                            }
                        }

                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Key'); ?></th>
                            <th class="text-center"><?= _t('Name'); ?></th>
                            <th class="text-center"><?= _t('Edit'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
            <!-- Form actions -->
            <div class="box-footer">
                <button type="submit" class="btn btn-icon btn-primary glyphicons circle_ok" onclick="window.location = '<?= get_base_url(); ?>permission/add/'"><i></i><?= _t('New Permission'); ?></button>
            </div>
            <!-- // Form actions END -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>