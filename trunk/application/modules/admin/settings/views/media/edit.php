<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('setting_header')?>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                 <li><i class="fa fa-home"></i> <a
                         href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
                         <i class="fa fa-angle-right"></i></li>
                 <li><a
                         href="<?php echo site_url($this->lang->lang() . '/settings/') ?>"><?php echo lang('bc_setting') ?></a>
                         <i class="fa fa-angle-right"></i>
                 </li>
                  <li><a
                         href="<?php echo site_url($this->lang->lang() . '/settings/media') ?>"><?php echo lang('bc_setting_media') ?></a>
                          <i class="fa fa-angle-right"></i>
                 </li>
                 <li><?php echo lang('bc_setting_media_edit') ?>
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
		                    <strong>Error!</strong><?php echo $error;?>
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
                <div class="portlet light bordered form-fit">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-user font-blue-hoki"></i>
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('media_edit')?></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                        <div class="form-body">
                            <div class="form-group last">
                                <label class="control-label col-md-3"><?php echo lang('business_media_profilePhoto')?></label>
                                <div class="col-md-9">
                                    <div class="fileinput fileinput-new" data-provides="fileinput">
                                        <div class="fileinput-new thumbnail" style="max-width: 155px; max-height: 155px;">
                                            <?php if(isset($record[$name]) && $record[$name]!= null):?>
                                                <img src="<?php echo CMSHelper::output_media($record[$name])?>" alt=""/>
                                            <?php else:?>
                                                <img src="http://www.placehold.it/155x155/EFEFEF/AAAAAA&amp;text=no+image" alt=""/>
                                            <?php endif;?>
                                        </div>
                                        <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 155px; max-height: 155px;">
                                        </div>
                                        <div>
                                            <span class="btn default btn-file">
                                            <span class="fileinput-new">
                                            <?php echo lang('media_lbl_select_image')?> </span>
                                            <span class="fileinput-exists">
                                            <?php echo lang('media_lbl_change')?> </span>
                                            <input type="file" name="path">
                                           
                                            </span>
                                            <a href="#" class="btn red fileinput-exists" data-dismiss="fileinput">
                                                <?php echo lang('media_lbl_remove')?> </a>
                                        </div>
                                    </div>
                                     <input type="hidden" name="status" value="1">
                                    <div class="clearfix margin-top-10">
                                        <span class="label label-danger">
                                        <?php echo lang('media_lbl_note')?></span>&nbsp;
                                        <?php echo lang('media_lbl_image_upload_note')?>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <button type="submit" class="btn blue"><i class="fa fa-check"></i> Submit</button>
                                    <a href="<?php echo site_url('settings/media');?>" type="reset" class="btn default">Reset</a>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close()?>
                        <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>