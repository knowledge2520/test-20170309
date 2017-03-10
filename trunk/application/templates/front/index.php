<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $template['title'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo base_url();?>../themes/public1/images/favicon.png">
    <?php echo $template['metadata'] ?>
</head>

<body class="<?php echo $body_class ?> page-header-fixed page-quick-sidebar-over-content">

<?php echo $template['partials']['header'];?>

<div class="swapper">
    <!-- BEGIN CONTENT -->
    <?php echo $template['body'];?>
    <!-- END CONTENT -->

    <!-- BEGIN FOOTER -->
    <?php echo $template['partials']['footer'];?>
    <!-- END FOOTER -->
</div>
<!-- END CONTAINER -->

<?php echo $template['scriptdata'] ?>
</body>
</html>


