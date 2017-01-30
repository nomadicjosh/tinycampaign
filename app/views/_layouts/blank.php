<?php $app = \Liten\Liten::getInstance();
ob_start();
ob_implicit_flush(0);
?>
<?= $app->view->show('blank'); ?>
<?php print_gzipped_page(); ?>