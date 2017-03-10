<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pet App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $theme_path?>global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $theme_path?>global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $theme_path?>global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $theme_path?>global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="<?php echo $theme_path?>global/plugins/select2/select2.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $theme_path?>admin/pages/css/login-soft.css" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL SCRIPTS -->
    <!-- BEGIN THEME STYLES -->
    <link href="<?php echo $theme_path?>global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $theme_path?>global/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $theme_path?>admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link id="style_color" href="<?php echo $theme_path?>admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $theme_path?>admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
</head>

<body class="<?php echo $body_class ?>">
<!-- BEGIN LOGO -->
<div class="logo">
    <a href="index.html">
        <img src="<?php echo site_url()?>../themes/admin/images/logo.png" alt="logo"/>
    </a>
</div>
<!-- END LOGO -->
<!-- BEGIN LOGIN -->
<div class="content">
    <!-- BEGIN LOGIN FORM -->
    <?php echo form_open('auth/reset_password/' . $code,'class="login-form" method="POST"');?>
    <h3 class="form-title"><?php echo lang('reset_password_heading');?></h3>
    <div class="alert alert-danger <?php echo $this->session->flashdata('error_message')!=''? '' : 'display-hide'?>" style="display: ">
        <button class="close" data-close="alert"></button>
        <span><?php echo $this->session->flashdata('error_message') ;?></span>
    </div>
    <div class="alert alert-success <?php echo $this->session->flashdata('success_message')!=''? '' : 'display-hide'?>" style="display: ">
        <button class="close" data-close="alert"></button>
        <span><?php echo $this->session->flashdata('success_message') ;?></span>
    </div>

    <div class="form-group">
        <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
        <label class="control-label visible-ie8 visible-ie9"><?php echo sprintf(lang('reset_password_new_password_label'), $min_password_length);?></label>
        <div class="input-icon">
            <?php echo form_input($new_password,'','class="form-control placeholder-no-fix" type="password" name="new" autocomplete="off" placeholder="New pasword"');?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label visible-ie8 visible-ie9"><?php echo lang('reset_password_new_password_confirm_label', 'new_password_confirm');?></label>
        <div class="input-icon">
            <?php echo form_input($new_password_confirm,'','class="form-control placeholder-no-fix" type="password" name="new_confirm" autocomplete="off" placeholder="Confirm new pasword" ');?>
        </div>
        <?php echo form_input($user_id);?>
        <?php echo form_hidden($csrf); ?>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn blue pull-right">
            <?php echo lang('reset_password_submit_btn')?> <i class="m-icon-swapright m-icon-white"></i>
        </button>
    </div>
    <?php echo form_close()?>
    <!-- END LOGIN FORM -->
</div>
<!-- END LOGIN -->
<!-- BEGIN COPYRIGHT -->
<div class="copyright">
    2015 &copy; Pet App - Admin Management.
</div>
<!-- END COPYRIGHT -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="../../assets/global/plugins/respond.min.js"></script>
<script src="../../assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<script src="<?php echo $theme_path;?>global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $theme_path;?>global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<script src="<?php echo $theme_path;?>global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $theme_path;?>global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="<?php echo $theme_path;?>global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="<?php echo $theme_path;?>global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="<?php echo $theme_path;?>global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script src="<?php echo $theme_path;?>global/plugins/backstretch/jquery.backstretch.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $theme_path;?>global/plugins/select2/select2.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="<?php echo $theme_path;?>global/scripts/metronic.js" type="text/javascript"></script>
<script src="<?php echo $theme_path;?>admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="<?php echo $theme_path;?>admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="<?php echo $theme_path;?>admin/pages/scripts/login-soft.js" type="text/javascript"></script>
<!-- END PAGE LEVEL SCRIPTS -->
<script>
    jQuery(document).ready(function() {
        Metronic.init(); // init metronic core components
        Layout.init(); // init current layout
        Login.init();
        Demo.init();
        // init background slide images
        $.backstretch([
                "<?php echo $theme_path;?>admin/pages/media/bg/1.jpg",
                "<?php echo $theme_path;?>admin/pages/media/bg/2.jpg",
                "<?php echo $theme_path;?>admin/pages/media/bg/3.jpg",
                "<?php echo $theme_path;?>admin/pages/media/bg/4.jpg"
            ], {
                fade: 1000,
                duration: 8000
            }
        );
    });
</script>
<!-- END JAVASCRIPTS -->
</body>
</html>
