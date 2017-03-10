<div class="container">

    <!-- Page Heading/Breadcrumbs -->
    <div class="row">
        <div class="col-lg-12">
                
            </h1>
            <ol class="breadcrumb">
                <li><a href="<?php echo site_url();?>">Home</a>
                </li>
                <li class="active">Contact Us</li>
            </ol>
        </div>
    </div>
    <!-- /.row -->

    <!-- Content Row -->
    <div class="row">
        <!-- Map Column -->
        <div class="col-md-8">
            <!-- Embedded Google Map -->
            <!--<iframe width="100%" height="400px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.google.com/maps/place/Apps+Cyclone+Software+Development+Co.,LTD/@10.80095,106.648085,17z/data=!3m1!4b1!4m2!3m1!1s0x31752948db5f187f:0x691b8c09ab318671"></iframe>-->
            <div id="map-canvas"></div>

        </div>
        <!-- Contact Details Column -->
        <div class="col-md-4">
            <h3>Contact Details</h3>
            <p>
                79 Ayer Rajah Crescent , #01-13<br>Singapore 139955<br>
            </p>
            <p><i class="fa fa-phone"></i>
                <abbr title="Phone"></abbr> +65 9782 3398</p>
            <p><i class="fa fa-envelope-o"></i>
                <abbr title="Email"></abbr> <a href="mailto:enquiries@petwidget.com">enquiries@petwidget.com</a>
            </p>
            <p><i class="fa fa-clock-o"></i>
                <abbr title="Hours"></abbr> Monday - Friday: 9:00 AM to 5:00 PM</p>
            <ul class="list-unstyled list-inline list-social-icons">
                <li>
                    <a href="https://www.facebook.com/petwidget" target="_blank"><i class="fa fa-facebook-square fa-2x"></i></a>
                </li>                
                <li>
                    <a href="https://twitter.com/petwidget" target="_blank"><i class="fa fa-twitter-square fa-2x"></i></a>
                </li>
                <li>
                    <a href="https://instagram.com/pet.widget" target="_blank"><i class="fa fa-instagram fa-2x"></i></a>
                </li>
            </ul>
        </div>
    </div>
    <!-- /.row -->

    <!-- Contact Form -->
    <!-- In order to set the email address and subject line for the contact form go to the bin/contact_me.php file. -->
    <div class="row">
        <div class="col-md-8">
            <h3>Send us a Message</h3>
            <form action="<?php echo site_url($this->uri->uri_string())?>" method="POST" name="sentMessage" id="contactForm" novalidate>


                <div class="control-group form-group">
                    <div class="controls">
                        <label>Name <span style="color: #ff0000"> * </span></label>
                        <input type="text" name="name" value="<?php echo (isset($name) && !empty($name)) ? $name : ''?>" class="form-control" id="name" required data-validation-required-message="Please enter your name.">
                        <p class="help-block"></p>
                        <span style="color: #ff0000"><?php echo form_error('name')?></span>
                    </div>
                </div>
                <div class="control-group form-group">
                    <div class="controls">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo (isset($phone) && !empty($phone)) ? $phone : ''?>" class="form-control" id="phone" >
                        <span style="color: #ff0000"><?php echo form_error('phone')?></span>
                    </div>
                </div>
                <div class="control-group form-group">
                    <div class="controls">
                        <label>Email Address <span style="color: #ff0000"> * </span></label>
                        <input type="email" name="email" value="<?php echo (isset($email) && !empty($email)) ? $email : ''?>" class="form-control" id="email" required data-validation-required-message="Please enter your email address.">
                        <span style="color: #ff0000"><?php echo form_error('email')?></span>
                    </div>
                </div>
                <div class="control-group form-group">
                    <div class="controls">
                        <label>Confirm Email Address <span style="color: #ff0000"> * </span></label>
                        <input type="email" name="email_confirm" value="<?php echo (isset($email) && !empty($email)) ? $email : ''?>" class="form-control" id="email" required data-validation-required-message="Please enter your confirm email address.">
                        <span style="color: #ff0000"><?php echo form_error('email_confirm')?></span>
                    </div>
                </div>
                <div class="control-group form-group">
                    <div class="controls">
                        <label>Message <span style="color: #ff0000"> * </span></label>
                        <textarea rows="10" name="contact_message" value="<?php echo (isset($contact_message) && !empty($contact_message)) ? $contact_message : ''?>" cols="100" class="form-control" id="message" required data-validation-required-message="Please enter your message" maxlength="999" style="resize:none"></textarea>
                        <span style="color: #ff0000"><?php echo form_error('contact_message')?></span>
                    </div>
                </div>
                <div class="control-group form-group">
                    <div class="controls">
                        <label>Please verify that you are human <span style="color: #ff0000"> * </span></label><br>
                        <label for="captcha"><?php echo $captcha['image']; ?></label>
    <br>                    
                        <input type="text" size="50" autocomplete="off" name="captcha" placeholder="Enter the characters you see in the picture above" value="" />
                        <span style="color: #ff0000"><?php echo form_error('captcha')?></span>
                    </div>
                </div>
                <div id="success"></div>
                <!-- For success/fail messages -->
                <?php if(isset($message) && $message!=''):?>
                    <div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span><?php echo $message;?></span>
                    </div>
                <?php endif?>
                <?php if( isset($success) && $success!=''):?>
                    <div class="alert alert-success">
                        <button class="close" data-close="alert"></button>
                        <span><?php echo $success;?></span>
                    </div>
                <?php endif?>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>

    </div>
    <!-- /.row -->

    <hr>

</div>