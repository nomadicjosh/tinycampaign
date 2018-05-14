<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Edit Subscriber View
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
TinyC\Config::set('screen_parent', 'subs');
TinyC\Config::set('screen_child', 'sub');
$tags = "{tag: '".implode("'},{tag: '", get_subscriber_tag_list())."'}";
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('View/Edit Subscriber'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>subscriber/"><i class="fa fa-group"></i> <?= _t('Subscribers'); ?></a></li>
            <li class="active"><?= _t('View/Edit Subscriber'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _tc_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>subscriber/<?=_escape((int)$subscriber->id);?>/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= _t('First Name'); ?></label>
                                <input type="text" class="form-control" name="fname" value="<?=_escape($subscriber->fname);?>" />
                            </div>

                            <div class="form-group">
                                <label><?= _t('Last Name'); ?></label>
                                <input type="text" class="form-control" name="lname" value="<?=_escape($subscriber->lname);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Email'); ?></label>
                                <input type="email" class="form-control" name="email" value="<?=_escape($subscriber->email);?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Address 1'); ?></label>
                                <input type="text" class="form-control" name="address1" value="<?=_escape($subscriber->address1);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Address 2'); ?></label>
                                <input type="text" class="form-control" name="address2" value="<?=_escape($subscriber->address2);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('City'); ?></label>
                                <input type="text" class="form-control" name="city" value="<?=_escape($subscriber->city);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('State'); ?></label>
                                <select class="form-control select2" name="state" style="width: 100%;">
                                    <option value="NULL">&nbsp;</option>
                                    <?php table_dropdown('state','code <> "NULL"','code','code','name',_escape($subscriber->state)); ?>
                                </select>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            
                            <div class="form-group">
                                <label><?= _t('Postal Code'); ?></label>
                                <input type="text" class="form-control" name="postal_code" value="<?=_escape($subscriber->postal_code);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Country'); ?></label>
                                <select class="form-control select2" name="country" style="width: 100%;">
                                    <option value="NULL">&nbsp;</option>
                                    <?php table_dropdown('country', 'iso2 <> "NULL"', 'iso2', 'iso2', 'short_name', _escape($subscriber->country)); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Tags'); ?></label>
                                <input type="text" id="input-tags" name="tags" value="<?=_escape($subscriber->tags);?>" />
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Spammer'); ?>  <a href="#spammer" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="hidden" name="spammer" value="0" />
                                <input type="checkbox" name="spammer" class="minimal-red" value="1" <?= checked('1', _escape($subscriber->spammer), false);?>/>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Exception'); ?>  <a href="#exception" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="hidden" name="exception" value="0" />
                                <input type="checkbox" name="exception" class="flat-red" value="1" <?= checked('1', _escape($subscriber->exception), false);?>/>
                            </div>
                            
                            <div class="form-group">
                                <label><?= _t('Lists'); ?></label><br />
                                <ul><?php get_subscription_email_lists(_escape((int)$subscriber->id)); ?></ul>
                            </div>
                            
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button<?=ie('subscriber_inquiry_only');?> type="submit" class="btn btn-primary"><?=_t('Submit');?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>subscriber/'"><?=_t( 'Cancel' );?></button>
            </div>
        </form>
        <!-- form end -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
    
    <!-- modal -->
    <div class="modal" id="spammer">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Spammer' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "The system will do an automatic check to see if email is to be marked as a spammer. If subscriber is not a spammer and should receive all emails, then check out the Exception option. Or if you feel the subscriber is a spammer, then use this option to mark the subscriber as so." );?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
    
    <!-- modal -->
    <div class="modal" id="exception">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Exception' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "If user keeps getting marked as spam but should receive emails nonetheless, check this option." );?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
    
</div>
<script src="static/assets/js/selectize/js/standalone/selectize.min.js" type="text/javascript"></script>
<script type="text/javascript">
$('#input-tags').selectize({
    plugins: ['remove_button','drag_drop'],
    delimiter: ',',
    persist: false,
    maxItems: null,
    valueField: 'tag',
    labelField: 'tag',
    searchField: ['tag'],
    options: [
        <?=$tags;?>
    ],
    render: {
        item: function(item, escape) {
            return '<div>' +
                (item.tag ? '<span class="tag">' + escape(item.tag) + '</span>' : '') +
            '</div>';
        },
        option: function(item, escape) {
            var caption = item.tag ? item.tag : null;
            return '<div>' +
                (caption ? '<span class="caption">' + escape(caption) + '</span>' : '') +
            '</div>';
        }
    },
    create: function(input) {
        return {
            tag: input
        };
    }
});
</script>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>
