<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('notification_header') ?>
            <small>Country</small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/notification/') ?>"><?php echo lang('bc_notification') ?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                 <li>
                     <span> Country </span>
                </li>  
            </ul>            
        </div>
        <!-- END PAGE HEADER-->
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">           
            <div class="col-md-12">
                <?php
                //error message
                $errors = $this->messages->get("error");
                if(!empty($errors)){
                    foreach($errors as $error):
                        ?>
                        <div class="alert alert-danger display-hide" style="display: block;">
                            <button data-close="alert" class="close"></button>
                            <strong>Error!</strong> <?php echo $error;?>
                        </div>
                    <?php endforeach;}?>

                <?php
                //success message
                $success_msg = $this->messages->get('success');
                if(!empty($success_msg) ):
                    ?>
                    <?php foreach($success_msg as $message):?>
                    <div class="alert alert-success alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button"></button>
                        <strong>Success!</strong> <?php echo $message?>
                    </div>
                <?php endforeach;?>
                <?php endif;?>
                <div class="portlet-body">
                    <!--BEGIN TABS-->
                    <div class="tabbable tabbable-custom tabbable-full-width">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- CONTENT TAB HERE -->
                                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                                <div class="portlet box blue-hoki">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-globe"></i> <?php echo lang('notification_send_push') ?>
                                        </div>
                                        <div class="tools">
                                        </div>
                                    </div>
                                    <div class="portlet-body form">
                                        <!-- BEGIN FORM-->
                                        <?php echo form_open($this->uri->uri_string(), 'method="POST" class="form-horizontal form-bordered form-label-stripped"') ?>
                                        <div class="form-body">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Select Countries</label>
                                                <div class="col-md-9">
                                                    <?php if(!empty($countries)):?>
                                                        <select name="countries[]" multiple="multiple" size="13" class="multi-select" id="my_multi_select1">
                                                        <?php foreach($countries as $item):?>
                                                            
                                                                <option value="<?php echo $item->id?>" > <?php echo $item->countryName?></option>
                                                            
                                                            <?php endforeach;?>
                                                        </select>   
                                                    <?php endif;?><br/>
                                                    
                                                </div>
                                            </div>
                                            <div class="form-group <?php echo form_error('title') ? 'has-error' : ''; ?>">
                                                <label class="control-label col-md-3"><?php echo lang('notification_title') ?> <span class="required" aria-required="true"> * </span></label>
                                                <div class="col-md-9">
                                                    <textarea rows="3" class="form-control" name="title"></textarea>
                                                    <?php echo form_error('title') ?>
                                                </div>
                                            </div>
                                            <div class="form-group last <?php echo form_error('message') ? 'has-error' : ''; ?>">
                                                <label class="control-label col-md-3"><?php echo lang('notification_message') ?> <span class="required" aria-required="true"> * </span></label>
                                                <div class="col-md-9">
                                                    <textarea rows="5" class="form-control" name="message"></textarea>
                                                    <?php echo form_error('message') ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <div class="row">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <input type="hidden" name="options" value="country">
                                                    <a href="<?php echo site_url('notification');?>"><button type="button" name="submit" value="country" class="btn default"> Back</button></a>
                                                    <button type="submit" name="submit" value="country" class="btn blue"><i class="fa fa-check"></i> Publish </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php echo form_close() ?>
                                        <!-- END FORM-->
                                    </div>
                                </div>
                                <!-- END EXAMPLE TABLE PORTLET-->
                            </div>
                        </div>
                    </div>
                    <!--END TABS-->
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>
</div>
