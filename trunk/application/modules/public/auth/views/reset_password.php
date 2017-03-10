<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-success">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center">
                                <h3>
                                    <b><?php echo lang('reset_header')?></b>
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
                                    <label for="newPassword"><?php echo lang('form_password');?></label>
                                    <input id="newPassword" class="form-control" type="password" required="" autocomplete="off" value="" placeholder="<?php echo lang('new_password');?>" tabindex="1" name="newPassword">
                                    <?php echo form_error('newPassword');?>
                                </div>
                                <div class="form-group">
                                    <label for="confirmPassword"><?php echo lang('form_confirm_password');?></label>
                                    <input id="confirmPassword" class="form-control" type="password" required="" autocomplete="off" value="" placeholder="<?php echo lang('confirm_password');?>" tabindex="1" name="confirmPassword">
                                    <?php echo form_error('confirmPassword');?>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3 col-sm-12 col-xs-12">
                                            <input type="hidden" name="userId" value="<?php echo $item->id;?>">
                                            <input id="recover-submit" class="form-control btn btn-success" type="submit" value="<?php echo lang('btn_save');?>" tabindex="2" name="recover-submit">
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
