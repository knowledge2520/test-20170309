<!DOCTYPE html>
<html class=" grunticon" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="description"
          content="The pet community that fits in your pocket. Accessible whenever, wherever.!">


    <link rel="icon" type="image/png" href="<?php echo base_url();?>themes/public1/images/favicon.png">
    <title>Pet Widget</title>
    <meta name="theme-color" content="#ffffff">


    <link media="all" rel="stylesheet" type="text/css"
          href="<?php echo base_url();?>themes/public1/css/icons.css">
    <link rel="stylesheet" type="text/css"
          href="<?php echo base_url();?>themes/public1/css/2ECC0C_datawoff.css">
    <link rel="stylesheet" type="text/css"
          href="<?php echo base_url();?>themes/public1/css/icons.data.svg.css">
    <link rel="stylesheet" type="text/css"
          href="<?php echo base_url();?>themes/public1/css/icons.data.png.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>themes/public1/css/normalize.css">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>themes/public1/css/homepage.css">
    <link rel="stylesheet" type="text/css"
          href="<?php echo base_url();?>themes/public1/css/firebugResetStyles.css">

    <script src="<?php echo base_url();?>themes/public1/js/jquery-match-height-master/jquery.matchHeight.js" type="text/javascript"></script>

    <!-- Latest compiled and minified CSS -->
    <!-- <link rel="stylesheet" href="<?php echo base_url();?>themes/public1/css/bootstrap.min.css"> -->

    <!-- jQuery library -->
    <!-- <script src="<?php echo base_url();?>themes/public1/js/jquery-1.11.3.min.js"></script> -->

    <!-- Latest compiled JavaScript -->
    <!-- <script src="<?php echo base_url();?>themes/public1/js/bootstrap.min.js"></script>  -->



</head>
<body>

<div class="nav-module">

</div>


<div class="community-module">
    <div class="community-reviews">
        <div class="title">
            <h2>Pet App Statistic</h2>
            <p>Download Pet Widget and be part of our community today!</p>
        </div>
        <div class="reviews">
            <div class="review">
                <h4>
                <strong><?php echo $totalApiCall; ?></strong> <br>Total API calls
                </h4>
                <p></p>
            </div>
            <div class="review">
                <h4>
                    <strong><?php echo $totalUserLoginToday; ?></strong> <br>Total User login
                </h4>
                <p></p>
            </div>
            <div class="review">
                <h4>
                    <strong><?php echo $totalRegistered; ?></strong> <br>Total User Registered Today
                </h4>
                <p></p>
            </div>

        </div>
        <div class="review-nav">
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle active"></div>
        </div>
    </div>
</div>
<script src="<?php echo base_url();?>themes/public1/js/jquery.js"></script>
<script src="<?php echo base_url();?>themes/public1/js/jquery_002.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>themes/public1/js/homepage.js"></script>
</body>
</html>