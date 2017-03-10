<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-login">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-6">
                            <a href="<?php echo site_url($this->lang->lang().'/login')?>" >Login</a>
                        </div>
                        <div class="col-xs-6">
                            <a href="#" id="register-form-link" class="active">Register</a>
                        </div>
                    </div>
                    <hr>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <form id="register-form" action="<?php echo site_url($this->uri->uri_string())?>" method="post" role="form" style="display: block;">
                                <div class="form-group">
                                    <input type="email" name="email" id="email" tabindex="1" class="form-control" placeholder="Email Address" value="<?php echo $email['value']?>">
                                    <?php echo form_error('email')?>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="first_name" id="first_name" tabindex="1" class="form-control" placeholder="First name" value="<?php echo $first_name['value']?>">
                                    <?php echo form_error('first_name')?>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="last_name" id="last_name" tabindex="1" class="form-control" placeholder="Last name" value="<?php echo $last_name['value']?>">
                                    <?php echo form_error('last_name')?>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="address" id="address" tabindex="1" class="form-control" placeholder="Address" value="<?php echo $address['value']?>">
                                    <?php echo form_error('address')?>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="dob" id="dob" tabindex="1" class="form-control" placeholder="Date of birth" value="">
                                </div>
                                <div class="form-group">
                                    <select name="gender" id="gender" tabindex="1" class="form-control" style="color: #999;opacity: 1;height: 45px; font-size: 16px;">
                                        <option value="0">Male</option>
                                        <option value="1">Female</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder="Password" >
                                    <?php echo form_error('password')?>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password_confirm" id="password_confirm" tabindex="2" class="form-control" placeholder="Confirm Password">
                                    <?php echo form_error('password_confirm')?>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 col-sm-offset-3">
                                            <input type="submit" name="register-submit" id="register-submit" tabindex="4" class="form-control btn btn-register" value="Register Now">
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>