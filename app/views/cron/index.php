<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Cronjob Handlers List View
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
define('SCREEN_PARENT', 'handler');
define('SCREEN', 'handlers');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Cronjob Handlers'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li class="active"><?= _t('Cronjob Handlers'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= _tc_flash()->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            
            <div class="break"></div>

            <!-- Tabs Heading -->            
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="<?= get_base_url(); ?>cron/" data-toggle="tab"><?= _t('Handler Dashboard'); ?></a></li>
                    <li><a href="<?= get_base_url(); ?>cron/create/"><?= _t('New Cronjob Handler'); ?></a></li>
                    <li><a href="<?= get_base_url(); ?>cron/setting/"><?= _t('Settings'); ?></a></li>
                </ul>
            </div>
            <!-- // Tabs Heading END -->

            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>cron/" autocomplete="off">
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center"></th>
                                <th class="text-center"><?= _t('Cronjob'); ?></th>
                                <th class="text-center"><?= _t('Time/Each'); ?></th>
                                <th class="text-center"><?= _t('Last Run'); ?></th>
                                <th class="text-center"><?= _t('# Runs'); ?></th>
                                <th class="text-center"><?= _t('Action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job) { ?>
                                <tr class="gradeX">
                                    <td class="text-center"><input type="checkbox" class="minimal" value="<?= _h((int)$job->id); ?>" name="cronjobs[<?= _h((int)$job->id); ?>]" /></td>
                                    <td class="text-center"><?= _h($job->name); ?></td>
                                    <td class="text-center"><?= _t('Each'); ?> <?= (_h($job->time) != 0) ? "day on " . _h($job->time) . ' hours' : tc_seconds_to_time(_h($job->each)) . (strlen(_h($job->eachtime) > 0) ? ' at ' . _h($job->eachtime) : ''); ?></td>
                                    <td class="text-center"><?= (_h($job->lastrun) !== '' ? Jenssegers\Date\Date::parse(_h($job->lastrun))->format('M. d, Y @ h:i A') : ''); ?></td>
                                    <td class="text-center">
                                        <span class="label bg-gray" style="font-size:1em;font-weight: bold;">
                                            <?= _h((int)$job->runned); ?>
                                        </span>
                                    </td>
                                    <?php foreach ($set as $s) : ?>
                                        <td class="text-center">
                                            <?= isset($s) ? '<a href="' . get_base_url() . 'cron' . '/' . _h((int)$job->id) . '/" data-toggle="tooltip" data-placement="top" title="View/Edit"><button type="button" class="btn bg-yellow"><i class="fa fa-edit"></i></button></a>' : ''; ?>
                                            <?= isset($s) ? '<a target="_blank" href="' . get_base_url() . 'cron/cronjob' . '/' . '?password=' . _h($s->cronjobpassword) . '&id=' . _h((int)$job->id) . '" data-toggle="tooltip" data-placement="top" title="Run"><button type="button" class="btn bg-purple"><i class="fa fa-chevron-right"></i></button></a>' : ''; ?>
                                            <?= isset($s) ? '<a href="' . get_base_url() . 'cron' . '/' . _h((int)$job->id) . '/' . 'reset' . '/" data-toggle="tooltip" data-placement="top" title="Reset Runs"><button type="button" class="btn bg-blue"><i class="fa fa-refresh"></i></button></a>' : ''; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-center"></th>
                                <th class="text-center"><?= _t('Cronjob'); ?></th>
                                <th class="text-center"><?= _t('Time/Each'); ?></th>
                                <th class="text-center"><?= _t('Last Run'); ?></th>
                                <th class="text-center"><?= _t('# Runs'); ?></th>
                                <th class="text-center"><?= _t('Action'); ?></th>
                            </tr>
                        </tfoot>
                    </table>

                    <?php if (isset($job->id)) { ?> 
                        <hr class="separator" />

                        <div class="separator line bottom"></div>

                        <!-- Form actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-icon btn-primary"><?= _t('Delete selected handler(s)'); ?></button>
                        </div>
                        <!-- // Form actions END -->
                    <?php } ?>
                </div>
                <!-- /.box-body -->
            </form>
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>