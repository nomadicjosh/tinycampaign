<?php if ( ! defined('BASE_PATH') ) exit('No direct script access allowed');
/**
 * Error Log View
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
$logger = new \app\src\Core\etsis_Logger();
$screen = 'err';
?>

<ul class="breadcrumb">
	<li><?=_t( 'You are here' );?></li>
	<li><a href="<?=get_base_url();?>dashboard/<?=bm();?>" class="glyphicons dashboard"><i></i> <?=_t( 'Dashboard' );?></a></li>
	<li class="divider"></li>
	<li><?=_t( 'Error Log' );?></li>
</ul>

<h3><?=_t( 'Error Log' );?></h3>
<div class="innerLR">
    
    <?php jstree_sidebar_menu($screen); ?>

	<!-- Widget -->
	<div class="widget widget-heading-simple widget-body-gray <?=($app->hook->has_filter('sidebar_menu')) ? 'col-md-12' : 'col-md-10';?>">
		<div class="widget-body">
		
			<!-- Table -->
			<table class="dynamicTable tableTools table table-striped table-bordered table-condensed table-primary">
			
				<!-- Table heading -->
				<thead>
					<tr>
						<th class="center"><?=_t( 'Error Type' );?></th>
						<th class="center"><?=_t( 'String' );?></th>
						<th class="center"><?=_t( 'File' );?></th>
						<th class="center"><?=_t( 'Line Number' );?></th>
						<th class="center"><?=_t( 'Delete' );?></th>
					</tr>
				</thead>
				<!-- // Table heading END -->
				
				<!-- Table body -->
				<tbody>
				<?php if($error != '') : foreach($error as $k => $v) { ?>
                <tr class="gradeX">
                    <td class="center"><?=$logger->error_constant_to_name(_h($v['type']));?></td>
                    <td class="center"><?=_h($v['string']);?></td>
                    <td class="center"><?=_h($v['file']);?></td>
                    <td class="center"><?=_h($v['line']);?></td>
                    <td class="center">
                    	<a href="<?=get_base_url();?>err/deleteLog/<?=_h($v['ID']);?>" title="Delete Log" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
                    </td>
                </tr>
                <?php } endif; ?>				
				</tbody>
				<!-- // Table body END -->
				
			</table>
			<!-- // Table END -->
			
		</div>
	</div>
	<div class="separator bottom"></div>
	<!-- // Widget END -->
	
</div>	
	
		
		</div>
		<!-- // Content END -->
<?php $app->view->stop(); ?>