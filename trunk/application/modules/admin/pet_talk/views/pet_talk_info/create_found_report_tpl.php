<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id' => $id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <h3 class="page-title">
                <?php echo lang('pet_talk_info_header')?>
                <small><?php echo lang('pet_talk_info_found_report') . ' ' . lang('pet_talk_info_create') ?></small>
            </h3>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/pet_talk/pet_talk_info/') ?>"><?php echo lang('bc_pet_talk_info') ?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/pet_talk/pet_talk_info?category=Found+Report') ?>"><?php echo lang('bc_pet_talk_info_found_report') ?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_pet_talk_info_add') ?></li>
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
                <div class="portlet light bordered form-fit">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-comments font-blue-hoki"></i>
                            <span
                                class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('pet_talk_info_add') ?></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <?php echo form_open_multipart($this->uri->uri_string(), 'method="POST" class="form-horizontal form-bordered form-label-stripped"', $hiddens) ?>
                        <div class="form-body">
                            <div class="form-group <?php echo form_error('photo') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_info_cover_image') ?> <span class="required" aria-required="true"> * </span></label>

                                <div class="col-md-9">
                                    <div class="fileinput fileinput-new" data-provides="fileinput">
                                        <div class="fileinput-new thumbnail" style="max-width: 155px; max-height: 155px;">
                                            <img src="http://www.placehold.it/155x155/EFEFEF/AAAAAA&amp;text=no+image" alt=""/>
                                        </div>
                                        <div class="fileinput-preview fileinput-exists thumbnail"
                                             style="max-width: 155px; max-height: 155px;">
                                        </div>
                                        <div>
                                            <span class="btn default btn-file">
                                                <span class="fileinput-new">
                                                    <?php echo lang('pet_talk_info_lbl_select_image') ?> </span>
                                                <span class="fileinput-exists">
                                                    <?php echo lang('pet_talk_info_lbl_change') ?> </span>
                                                <input type="file" name="photo">
                                            </span>
                                            <a href="#" class="btn red fileinput-exists" data-dismiss="fileinput">
                                                <?php echo lang('pet_talk_info_lbl_remove') ?> </a>
                                        </div>
                                    </div>
                                    <div class="clearfix margin-top-10">
                                            <span class="label label-danger">
                                            <?php echo lang('pet_talk_info_lbl_note') ?></span>&nbsp;
                                        <?php echo lang('pet_talk_info_lbl_image_upload_note') ?>
                                    </div>
                                    <?php echo form_error('photo')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('when') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3 "><?php echo lang('pet_talk_info_when')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-4">
                                    <div class="input-group date form_datetime" data-date="<?php echo gmdate('Y-m-d',now())?>">
                                        <input type="text" name="when" size="16" class="form-control" id="form_datetime" readonly value="<?php echo isset($record->when) ? $record->when : date("d/m/Y H:i:s");?>">
                                        <span class="input-group-btn">
                                            <button class="btn default date-reset" type="button">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <button class="btn default date-set" type="button">
                                                <i class="fa fa-calendar"></i>
                                            </button>
                                            <?php echo form_error('when')?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('where') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_info_where')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="where" value="<?php echo isset($record->where) ? $record->where : '';?>">
                                    <?php echo form_error('where')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('type') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_info_type')?>  <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <input type="hidden" name="url-get-type" id="url-get-type" value="<?php echo site_url('pet_talk/pet_talk_info/fetch_data_type') ;?>">
                                    <?php 

                                        $types = $this->config->item('type');
                                        $oldType = isset($record->type) ? $record->type : '';
                                        echo form_dropdown('type', $types, $oldType, 'class="form-control" id="pet-type"');    
                                        echo form_error('type')
                                    ?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('breed') ? 'has-error' : ''; ?>" id="pet-breed-selected">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_info_breed')?>  <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <?php 
                                        $breeds = $this->config->item('Dog');
                                        $oldBreed = isset($record->breed) ? $record->breed : '';
                                        echo form_dropdown('breed-selected', $breeds, $oldBreed, 'class="form-control" id="pet-breed"');    
                                        echo form_error('breed')
                                    ?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('breed') ? 'has-error' : ''; ?>" id="pet-breed-input-text">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_info_breed')?>  <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="breed-other" id="pet-breed-other" value="<?php echo isset($record->breed) ? $record->breed : '';?>">
                                    <?php echo form_error('breed')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('sex') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_info_sex')?>  <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <?php 
                                        $sex = $this->config->item('sex');
                                        echo form_dropdown('sex', $sex, '', 'class="form-control" id="pet-breed"');    
                                        echo form_error('sex');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('color') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_info_color')?></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="color" value="<?php echo isset($record->color) ? $record->color : '';?>">
                                    <?php echo form_error('color')?>
                                </div>
                            </div>
                                <div class="form-group <?php echo form_error('contact') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_info_contact')?></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="contact" value="<?php echo isset($record->contact) ? $record->contact : '';?>">
                                    <?php echo form_error('contact')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('information') ? 'has-error' : ''; ?>" >
                               <label class="control-label col-md-3"><?php echo lang('pet_talk_info_additional_information')?></label>
                                <div class="col-md-9">
                                    <textarea rows="5" class="form-control" name="information"><?php echo isset($record->additionalInfo) ? $record->additionalInfo : '';?></textarea>
                                    <?php echo form_error('information')?>
                                </div>
                            </div>
                            <div class="form-group last <?php echo form_error('user_id') ? 'has-error' : ''; ?>" >
                               <label class="control-label col-md-3"><?php echo lang('pet_talk_info_finder')?>  <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <input type="hidden" name="url" id="url" value="<?php echo site_url('notification/getUsers');?>">
                                    <select name="user_id" class="js-example-basic-single form-control input-large input-inline">
                                                        </select>
                                    <?php echo form_error('user_id')?>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" name="infoType" value="<?php echo ADD_PETTALK_FOUND_REPORT ;?>">                                
                                    <button type="submit" class="btn blue"><i class="fa fa-check"></i> Submit</button>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close() ?>
                        <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>