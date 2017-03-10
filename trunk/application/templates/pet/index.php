<!DOCTYPE html>
<html lang="en">
<head>
<!-- Meta, title, CSS, favicons, etc. -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $template['title'] ?></title>

<!-- Social meta ================================================== -->
<meta property="og:type" content="website">
<meta property="og:title" content="Pet Widget badge">
<meta property="og:url" content="https://badgeid.petwidget.com/">
<meta property="og:description" content="badge is a NFC & QR code enabled intelligent pet ID tag, which is linked to pet profiles created in the Pet Widget app. badge and its features are designed and developed to help lost pets reunite with their families.">
<meta property="og:site_name" content="Pet Widget">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="Pet Widget badge">  
<meta name="twitter:description" content="badge is a NFC & QR code enabled intelligent pet ID tag, which is linked to pet profiles created in the Pet Widget app. badge and its features are designed and developed to help lost pets reunite with their families.">

 <?php echo $template['metadata'] ?>

<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
  <script src="<?php echo base_url('themes/pet/js/html5shiv.js');?>"></script>
  <script src="<?php echo base_url('themes/pet/js/respond.min.js');?>"></script>
<![endif]-->

<!-- Favicons -->
<link rel="shortcut icon" href="<?php echo base_url('themes/pet/img/favicon.ico');?>">

<!-- Custom Google Font : Montserrat and Droid Serif -->

<link href='https://fonts.googleapis.com/css?family=Lato:400,100,300,700' rel='stylesheet' type='text/css'>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-68481489-1', 'auto');
  ga('send', 'pageview');

</script>
</head>
<body>
<?php echo $template['partials']['header'];?>

<?php echo $template['body'];?>

<?php echo $template['partials']['footer'];?>

<!-- // END --> 

<!-- Bootstrap core JavaScript
================================================== --> 
<!-- Placed at the end of the document so the pages load faster --> 
<?php echo $template['scriptdata'] ?>
<script type="text/javascript">
    $(window).load(function(){
      $('.flexslider').flexslider({
        slideshow: false,
        start: function(slider){
          $('body').removeClass('loading');
        }
      });
    });
  </script>
</body>
</html>



