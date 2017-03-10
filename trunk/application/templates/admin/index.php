<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $template['title'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo $template['metadata'] ?>
  </head>

	<body class="<?php echo $body_class ?> page-header-fixed page-quick-sidebar-over-content">
		<?php echo $template['partials']['header'];?>
  		<div class="clearfix"></div>
  		<div class="page-container">
  		
  			<!-- BEGIN SIDEBAR -->
  			<?php echo $template['partials']['aside'];?>
  			<!-- END SIDEBAR -->
  			
  			<!-- BEGIN CONTENT -->
  			<?php echo $template['body'];?>
  			<!-- END CONTENT -->
  			
  		</div>
  		<!-- END CONTAINER -->
  		
  		<!-- BEGIN FOOTER -->
  		<?php echo $template['partials']['footer'];?>
  		<!-- END FOOTER -->
  		<?php echo $template['scriptdata'] ?>
  </body>  
</html>
