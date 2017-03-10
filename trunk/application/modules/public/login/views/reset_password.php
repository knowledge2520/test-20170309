<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-success">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center">
                                <h3>
                                    <b>Recover Account</b>
                                </h3>
                            </div>
                            <form id="register-form" autocomplete="off" role="form" method="post" action="<?php echo site_url($this->uri->uri_string())?>">
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
                                    <label for="email">Email Address</label>
                                    <input id="email" class="form-control" type="email" required="" autocomplete="off" value="" placeholder="Email Address" tabindex="1" name="email">
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3 col-sm-12 col-xs-12">
                                            <input id="recover-submit" class="form-control btn btn-success" type="submit" value="Recover Account" tabindex="2" name="recover-submit">
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
