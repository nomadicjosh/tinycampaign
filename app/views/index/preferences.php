<?php 
/**
 * Subscriber Preferences View
 *  
 * @license GPLv3
 * 
 * @since       2.0.0
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
        <h1><?= _t('My Preferences'); ?></h1>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>preferences/<?=_h($subscriber->code);?>/subscriber/<?=_h($subscriber->id);?>/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= _t('First Name'); ?></label>
                                <input type="text" class="form-control" name="fname" value="<?=_h($subscriber->fname);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Last Name'); ?></label>
                                <input type="text" class="form-control" name="lname" value="<?=_h($subscriber->lname);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Email'); ?></label>
                                <input type="text" class="form-control" name="email" value="<?=_h($subscriber->email);?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Address1'); ?></label>
                                <input type="text" class="form-control" name="address1" value="<?=_h($subscriber->address1);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Address2'); ?></label>
                                <input type="text" class="form-control" name="address2" value="<?=_h($subscriber->address2);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('City'); ?></label>
                                <input type="text" class="form-control" name="city" value="<?=_h($subscriber->city);?>" >
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('State'); ?></label>
                                <select class="form-control select2" name="state" style="width: 100%;">
                                    <option>&nbsp;</option>
                                    <?php table_dropdown('state',null,'code','code','name',_h($subscriber->state)); ?>
                                </select>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><?= _t('Postal Code'); ?></label>
                                <input type="text" class="form-control" name="zip" value="<?=_h($subscriber->zip);?>" >
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Country'); ?></label>
                                <select class="form-control select2" name="country" style="width: 100%;">
                                    <option>&nbsp;</option>
                                    <?php table_dropdown('country',null,'iso2','iso2','short_name',_h($subscriber->country)); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Lists'); ?></label><br />
                                <ul><?php get_user_lists($subscriber->id); ?></ul>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Date Added'); ?></label>
                                <input type="text" class="form-control" value="<?= Jenssegers\Date\Date::parse(_h($subscriber->addDate))->format('M. d, Y @ h:i A');?>" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Last Modified'); ?></label>
                                <input type="text" class="form-control" value="<?=(_h($subscriber->LastUpdate) > '0000-00-00 00:00:00' ? Jenssegers\Date\Date::parse(_h($subscriber->LastUpdate))->format('M. d, Y @ h:i A') : '');?>" readonly>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        
                    </div>
                    <!-- /.row -->
                </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary"><?=_t('Save');?></button>
            </div>
        </form>
        <!-- form end -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>
