<?php 
/**
 * Archive List View
 *  
 * @license GPLv3
 * 
 * @since       2.0.2
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/index');
$app->view->block('index');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Campaign Archives'); ?></h1>
        <ol class="breadcrumb">
            <li class="active"><?= _t('Campaign Archives'); ?></li>
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
                            <th class="text-center"><?= _t('Subject'); ?></th>
                            <th class="text-center"><?= _t('Sent'); ?></th>
                            <th class="text-center"><?= _t('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archives as $archive) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= _h($archive->subject); ?></td>
                                <td class="text-center"><?= \Jenssegers\Date\Date::parse(_h($archive->sendfinish))->format('M d, Y'); ?></td>
                                <td class="text-center">
                                    <a href="<?= get_base_url(); ?>archive/<?= _h($archive->id); ?>/" data-toggle="tooltip" data-placement="top" title="View"><button class="btn bg-yellow"><i class="fa fa-eye"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Subject'); ?></th>
                            <th class="text-center"><?= _t('Sent'); ?></th>
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
