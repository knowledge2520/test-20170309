<header id="header" class="navbar navbar-inverse navbar-fixed-top" role="banner">
    <div class="container">
        <div class="navbar-header">
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <!-- Your Logo -->
            <a href="<?php echo site_url($this->lang->lang() )?>" class="navbar-brand">
                <img src="<?php echo base_url()?>themes/public/images/logo/App_Icon@2x.png" alt="logo" height="60" />
            </a>
        </div>
        <!-- Start Navigation -->
        <nav class="collapse navbar-collapse bs-navbar-collapse navbar-right" role="navigation">
            <ul class="nav navbar-nav">
                <li>
                    <a href="#home">Home</a>
                </li>
                
                <li>
                    <a href="#download">Download</a>
                </li>

                <li>
                    <a href="#features">Features</a>
                </li>

                <li>
                    <a href="#help">Help</a>
                </li>      
            </ul>
        </nav>
    </div>
</header>