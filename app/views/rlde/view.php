<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * View Rule View
 *  
 * @license GPLv3
 * 
 * @since       2.0.6
 * @package     tinyCampaign
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/dashboard');
$app->view->block('dashboard');
app\src\Config::set('screen_parent', 'rules');
app\src\Config::set('screen_child', 'rule');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('View/Edit Rule'); ?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>dashboard/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>rlde/"><i class="fa fa-object-group"></i> <?= _t('Rules'); ?></a></li>
            <li class="active"><?= _t('View/Edit Rule'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

<?= _tc_flash()->showMessage(); ?>
        
        <div class="alert alert-info"><?=_t('If you make any changes, you must click "Load Rule" in order to bring the changes to the screen and then click "Update".');?></div>

        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>rlde/<?= _escape($rule->id); ?>/" data-toggle="validator" autocomplete="off" id="form">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Description'); ?></label>
                                <input type="text" class="form-control" name="description" value="<?= _escape($rule->description); ?>" maxlength="50" required>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Rule Code'); ?>  <a href="#code" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" name="code" value="<?= _escape($rule->code); ?>" maxlength="10" required>
                            </div>

                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">

                            <div class="form-group">
                                <label><?= _t('Comment'); ?></label>
                                <textarea id="comment" style="resize: none;height:8em;" class="form-control" name="comment"><?= _escape($rule->comment); ?></textarea>
                            </div>

                        </div>
                        <!-- /.col -->

                        <div class="col-md-12">

                            <!-- Group -->
                            <div class="form-group">
                                <div id="builder"></div>
                            </div>
                            <!-- // Group END -->

                        </div>
                        <!-- /.col -->

                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <div class="btn-group">
                        <div id="result" class="hide">
                            <textarea id="rldeRule" style="resize: none;height:10em; width:800px;" name="rule" class="rldeRule form-control" readonly="readonly" required></textarea>
                            <button type="submit" class="btn btn-success"><?= _t('Update'); ?></button><br /><br />
                        </div>
                        <a class="btn btn-danger reset"><?= _t('Reset'); ?></a>
                        <a class="btn btn-primary parse-sql" data-stmt="false"><?= _t('Load Rule'); ?></a>
                    </div>
                </div>
            </form>
            <!-- form end -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
    
    <!-- modal -->
    <div class="modal" id="code">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Rule Code' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "Code must be unique and contain no spaces." );?></p>
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
<!-- /.content-wrapper -->

<script type="text/javascript" src="//cdn.jsdelivr.net/npm/jQuery-QueryBuilder/dist/js/query-builder.standalone.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/interact.js/1.2.9/interact.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.4/js/standalone/selectize.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-maxlength/1.7.0/bootstrap-maxlength.min.js"></script>
<script type="text/javascript" src="static/assets/js/pages/maxlength.js"></script>
<script type="text/javascript" src="static/assets/js/pages/sql-parser/browser/sql-parser.min.js"></script>
<script>
$('[data-toggle="tooltip"]').tooltip();
<?php if(!empty(_escape($rule->rule))) : ?>
var sql_import_export = "<?=_escape($rule->rule);?>";
<?php endif; ?>

// Fix for Selectize
$('#builder').on('afterCreateRuleInput.queryBuilder', function(e, rule) {
  if (rule.filter.plugin == 'selectize') {
    rule.$el.find('.rule-value-container').css('min-width', '200px')
      .find('.selectize-control').removeClass('form-control');
  }
});

var options = {
  allow_empty: true,

  //default_filter: 'name',
  sort_filters: true,

  optgroups: {
    sub: {
      en: 'Subscriber'
    }
  },

  plugins: {
    'bt-tooltip-errors': { delay: 100},
    'sortable': null,
    'filter-description': { mode: 'bootbox' },
    'bt-selectpicker': null,
    'unique-filter': null,
    'bt-checkbox': { color: 'primary' },
    'invert': null,
    'not-group': null
  },

  // standard operators in custom optgroups
  operators: [
    {type: 'equal',            optgroup: 'basic'},
    {type: 'not_equal',        optgroup: 'basic'},
    {type: 'in',               optgroup: 'basic'},
    {type: 'not_in',           optgroup: 'basic'},
    {type: 'less',             optgroup: 'numbers'},
    {type: 'less_or_equal',    optgroup: 'numbers'},
    {type: 'greater',          optgroup: 'numbers'},
    {type: 'greater_or_equal', optgroup: 'numbers'},
    {type: 'between',          optgroup: 'numbers'},
    {type: 'not_between',      optgroup: 'numbers'},
    {type: 'begins_with',      optgroup: 'strings'},
    {type: 'not_begins_with',  optgroup: 'strings'},
    {type: 'contains',         optgroup: 'strings'},
    {type: 'not_contains',     optgroup: 'strings'},
    {type: 'ends_with',        optgroup: 'strings'},
    {type: 'not_ends_with',    optgroup: 'strings'},
    {type: 'is_empty'     },
    {type: 'is_not_empty' },
    {type: 'is_null'      },
    {type: 'is_not_null'  }
  ],

  filters: [
  /*
   * Subscriber
   */
  {
    id: 'subscriber.city',
    label: 'City',
    type: 'string',
    optgroup: 'sub'
  },
  {
    id: 'subscriber.state',
    label: 'State',
    type: 'string',
    input: 'select',
    plugin: 'selectize',
    multiple: true,
    plugin_config: {
      valueField: 'id',
      labelField: 'name',
      searchField: 'name',
      sortField: 'name',
      create: true,
      plugins: ['remove_button']
    },
    values: {
        <?php get_rlde_states(); ?>
    },
    valueSetter: function (rule, value) {
        rule.$el.find('.rule-value-container select')[0].selectize.setValue(value);
    },
    optgroup: 'sub'
  },
  {
    id: 'subscriber.postal_code',
    label: 'Postal Code',
    type: 'string',
    optgroup: 'sub'
  },
  {
    id: 'subscriber.country',
    label: 'Country',
    type: 'string',
    input: 'select',
    plugin: 'selectize',
    multiple: true,
    plugin_config: {
      valueField: 'id',
      labelField: 'name',
      searchField: 'name',
      sortField: 'name',
      create: true,
      plugins: ['remove_button']
    },
    values: {
        <?php get_rlde_countries(); ?>
    },
    valueSetter: function (rule, value) {
        rule.$el.find('.rule-value-container select')[0].selectize.setValue(value);
    },
    optgroup: 'sub'
  },
  {
    id: 'subscriber.tags',
    label: 'Tags',
    type: 'string',
    input: 'select',
    plugin: 'selectize',
    multiple: true,
    plugin_config: {
      valueField: 'id',
      labelField: 'name',
      searchField: 'name',
      sortField: 'name',
      create: true,
      plugins: ['remove_button']
    },
    values: {
        <?php get_rlde_subscriber_tags(); ?>
    },
    valueSetter: function (rule, value) {
        rule.$el.find('.rule-value-container select')[0].selectize.setValue(value);
    },
    optgroup: 'sub'
  }
  ]
};

// init
$('#builder').queryBuilder(options);
<?php if(!empty(_escape($rule->rule))) : ?>
$('#builder').queryBuilder('setRulesFromSQL', sql_import_export);
<?php endif; ?>

// reset builder
$('.reset').on('click', function() {
  $('#builder').queryBuilder('reset');
  $('#result').addClass('hide').find('.rldeRule').empty();
});

$('.parse-sql').on('click', function() {
  var res = $('#builder').queryBuilder('getSQL', $(this).data('stmt'), false);
  $('#result').removeClass('hide')
    .find('.rldeRule').val(
      res.sql + (res.params ? '\n\n' + JSON.stringify(res.params, undefined, 2) : '')
    );
});
</script>
<?php $app->view->stop(); ?>
