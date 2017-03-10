<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-login">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-6">
                            <a href="#" id="login-form-link" class="active" >Login</a>
                        </div>
                        <div class="col-xs-6">
                            <a href="<?php echo site_url($this->lang->lang().'/login/register')?>" id="register-form-link">Register</a>
                        </div>
                    </div>
                    <hr>
                </div>
                <div class="panel panel-success">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <form id="login-form" action="<?php echo site_url($this->uri->uri_string())?>" method="post" role="form" style="display: block;">
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
                                <div class="form-group">
                                    <input type="text" name="identity" id="identity" tabindex="1" class="form-control" placeholder="Username" value="">
                                    <?php echo form_error('identity')?>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder="Password">
                                    <?php echo form_error('password')?>
                                </div>
                                <div class="form-group text-center">
                                    <input type="checkbox" tabindex="3" class="" name="remember" id="remember">
                                    <label for="remember"> Remember Me</label>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 col-sm-offset-3">
                                            <input type="submit" name="login-submit" id="login-submit" tabindex="4" class="form-control btn btn-login" value="Log In">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="text-center">
                                                <a href="<?php echo site_url($this->lang->lang().'/login/forgot_password')?>" tabindex="5" class="forgot-password">Forgot Password?</a>
                                            </div>
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
</div>