<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo base_url();?>themes/public1/images/favicon.png">
    <?php echo $template['metadata'] ?>

    <title>
        <?php echo $template['title'] ?>
    </title>
</head>
<body>

<!-- Start Rocket -->
<!-- ********************* -->

<!-- Parallax Background
================================================== -->
<!-- image is set in the CSS as a background image -->
<div id="parallax"></div>
<!-- End Parallax Background
================================================== -->

<!-- Start Header
================================================== -->
<?php echo $template['partials']['header'];?>
<!-- ==================================================
End Header -->

<!-- Start Content -->
<?php echo $template['body'];?>
<!-- End Content -->

<!-- Start Content -->
<?php echo $template['partials']['footer'];?>
<!-- End Content -->

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<?php echo $template['scriptdata'] ?>
<!-- ********************* -->
<!-- Start Rocket -->

</body>
</html>
