<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Plugins List View
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
TinyC\Config::set('screen_parent', 'plugins');
TinyC\Config::set('screen_child', 'plugins');
$plugins_header = $app->hook->{'get_plugins_header'}(APP_PATH . 'plugins/');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Plugins'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Plugins'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Plugin'); ?></th>
                            <th class="text-center"><?= _t('Description'); ?></th>
                            <th class="text-center"><?= _t('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php         		
                            // Let's read the content of the array
                            foreach($plugins_header as $plugin) {
                            if($app->hook->{'is_plugin_activated'}($plugin['filename']) == true)
                                echo '<tr class="separated gradeX">';
                            else
                                echo '<tr class="separated gradeX">';

                            // Display the plugin information
                            echo '<td class="text-center">'.$plugin['Name'].'</td>';
                            echo '<td>'.$plugin['Description'];
                            echo '<br /><br />';
                            echo 'Version '.$plugin['Version'];
                            echo ' By <a href="'.$plugin['AuthorURI'].'">'.$plugin['Author']. '</a> ';
                            echo ' | <a href="' .$plugin['PluginURI'].'">' . _t( 'Visit plugin site' ) . '</a></td>';

                                if($app->hook->{'is_plugin_activated'}($plugin['filename']) == true) {
                                    echo '<td class="text-center"><a href="'.get_base_url().'plugins/deactivate/?id='.urlencode($plugin['filename']).'" title="Deactivate" class="btn btn-default"><i class="fa fa-minus"></i></a></td>';
                                } else {
                                    echo '<td class="text-center"><a href="'.get_base_url().'plugins/activate/?id='.urlencode($plugin['filename']).'" title="Activate" class="btn btn-default"><i class="fa fa-plus"></i></a></td>';
                                }

                                echo '</tr>';
                        } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Plugin'); ?></th>
                            <th class="text-center"><?= _t('Description'); ?></th>
                            <th class="text-center"><?= _t('Action'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>