<?php
$pagination_total_rows 			= isset($paging) ? $paging->total_rows : 0;
$pagination_total_rows_ppage 	= isset($paging) ? $paging->per_page * $paging->cur_page : 0;
$pagination_start_item 			= isset($paging) && $paging->cur_page > 1 ? ($paging->per_page * ($paging->cur_page - 1) ) : 1;
$pagination 					= isset($paging) ? $paging->create_links() : false;
$id                             = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens                        = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('pet_header') ?>
            <small><?php echo lang('pet_edit_header') ?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang().'/pets')?>"><?php echo lang('bc_pet')?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <?php echo lang('bc_pet_edit')?>
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
                        <strong>Error! </strong><?php echo $error;?>
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
                    <strong>Success! </strong> <?php echo $message?>
                </div>
            <?php endforeach;?>
            <?php endif;?>
                <div class="portlet-body">
                    <!--BEGIN TABS-->
                    <!-- BEGIN FORM-->
                    <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                    <div class="tabbable tabbable-custom tabbable-full-width">
                    <ul class="nav nav-tabs nav-tabs-lg">
                        <li  class="active">
                            <a href="<?php echo site_url($this->lang->lang().'/pets/edit/'.$pet_id)?>">
                                <?php echo lang('pet_information')?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pets/vaccinations/'.$pet_id)?>">
                                <?php echo lang('pet_vaccinations')?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pets/medical_examinations/'.$pet_id)?>">
                                <?php echo lang('pet_medical_examinations')?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pets/physical_exams/'.$pet_id)?>">
                                <?php echo lang('pet_physical_exams')?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pets/medications/'.$pet_id)?>">
                                <?php echo lang('pet_medications')?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pets/surgeries/'.$pet_id)?>">
                                <?php echo lang('pet_surgeries')?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pets/allergies/'.$pet_id)?>">
                                <?php echo lang('pet_allergies')?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pets/weight/'.$pet_id)?>">
                                <?php echo lang('pet_weight')?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pets/estrus/'.$pet_id)?>">
                                <?php echo lang('pet_estrus')?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pets/contact/'.$pet_id)?>">
                                <?php echo lang('pet_contact')?>
                            </a>
                        </li>
                    </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_1">
                                <div class="portlet-body form">
                                    <div class="form-body">
                                        <?php
                                        //error message
                                        $errors = $this->messages->get("error");
                                        if(!empty($errors)){
                                            foreach($errors as $error):
                                                ?>
                                                <div class="alert alert-danger display-hide" style="display: block;">
                                                    <button data-close="alert" class="close"></button>
                                                    <?php echo $error;?>
                                                </div>
                                            <?php endforeach;}?>

                                        <div class="form-group">
                                            <label class="control-label col-md-3"><?php echo lang('pet_profilePhoto')?></label>
                                            <div class="col-md-9">
                                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                                    <div class="fileinput-new thumbnail" style="max-width: 155px; max-height: 155px;">
                                                        <?php if(isset($record->profile_photo) && $record->profile_photo!=''):?>
                                                            <img src="<?php echo CMSHelper::output_media($record->profile_photo)?>" alt=""/>

                                                        <?php else:?>
                                                            <img src="http://www.placehold.it/155x155/EFEFEF/AAAAAA&amp;text=no+image" alt=""/>
                                                        <?php endif;?>
                                                    </div>
                                                    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 155px; max-height: 150px;">
                                                    </div>
                                                    <div>
                                                        <span class="btn default btn-file">
                                                            <span class="fileinput-new">
                                                                <?php echo lang('pet_lbl_select_image')?>
                                                            </span>

                                                            <span class="fileinput-exists">
                                                            <?php echo lang('pet_lbl_change')?> </span>
                                                            <input type="file" name="profile_photo">
                                                        </span>
                                                            <?php if(isset($record->profile_photo) && $record->profile_photo!=''):?><a href="#" class="btn red" data-href="<?php echo site_url($this->lang->lang().'/pets/deleteProfilePhoto/'.$record->id)?>" data-toggle="modal" data-target="#confirm-delete"><?php echo lang('pet_lbl_delete')?></a><?php endif?>
                                                             <a href="#" class="btn red fileinput-exists" data-dismiss="fileinput">
                                                            <?php echo lang('pet_lbl_remove') ?> </a>
                                                    </div>
                                                </div>
                                                <div class="clearfix margin-top-10">
                                                <span class="label label-danger">
                                                <?php echo lang('pet_lbl_note')?></span>&nbsp;
                                                    <?php echo lang('pet_lbl_image_upload_note')?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group <?php echo form_error('name') ? 'has-error' : ''; ?>">
                                            <label class="control-label col-md-3"><?php echo lang('pet_name')?> <span class="required" aria-required="true"> * </span></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" name="name" value="<?php echo isset($record->name) ? $record->name : '';?>">
                                                <?php echo form_error('name')?>
                                            </div>
                                        </div>
                                        <div class="form-group <?php echo form_error('badge_id') ? 'has-error' : ''; ?>">
				                            <label class="control-label col-md-3"><?php echo lang('pet_badge_id')?></label>
				                            <div class="col-md-9">
				                                <input type="text" class="form-control" name="badge_id" value="<?php echo isset($record->code_id) ? $record->code_id : '';?>">
				                               <?php if(isset($record->code_id) && !empty($record->code_id)):?> <span class="help-block"><a href="<?php echo site_url('pets/unlink/'.$record->id);?>">unlink badge ID</a></span><?php endif;?>
				                                <?php echo form_error('badge_id')?>
				                            </div>
				                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3"><?php echo lang('pet_dob')?></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date-format="yyyy/mm/dd" data-date-viewmode="years">
                                                    <input type="text" class="form-control" readonly name="dob" value="<?php echo isset($record->dob) ? date('Y/m/d',$record->dob) : ''?>">
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                                <!-- /input-group -->
                                                <span class="help-block">
                                                    <?php echo lang('pet_select_date') ?> </span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3"><?php echo lang('pet_type') ?></label>

                                            <div class="col-md-9">
                                                <select name="type" class="form-control">
                                                    <?php if (isset($pet_types) && !empty($pet_types)): ?>
                                                        <?php foreach ($pet_types as $item): ?>
                                                            <?php if ($record->type == $item->id): ?>
                                                                <option value="<?php echo $item->id ?>" selected><?php echo $item->name ?></option>
                                                            <?php else: ?>
                                                                <option
                                                                    value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                                                            <?php endif ?>
                                                        <?php endforeach ?>
                                                    <?php endif ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3"><?php echo lang('pet_breed')?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" name="breed" value="<?php echo isset($record->breed) ? $record->breed : '';?>">
                                                <?php echo form_error('breed')?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3"><?php echo lang('pet_sex')?></label>
                                            <div class="col-md-9">
                                                <?php
                                                    $options = array(
                                                        '0'      => 'Male',
                                                        '1'    => 'Female',
                                                        '2'    => 'Unknown',
                                                    );
                                                    echo form_dropdown('sex', $options, $record->sex, 'class="form-control"');
                                                ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3"><?php echo lang('pet_color')?></label>
                                            <div class="col-md-9">
                                               <input type="text" class="form-control" name="color" value="<?php echo isset($record->color) ? $record->color : '';?>">
                                                <?php echo form_error('color')?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3"><?php echo lang('pet_purchase_date')?></label>
                                            <div class="col-md-3">
                                                <div class="input-group input-medium date date-picker" data-date-format="yyyy/mm/dd" data-date-viewmode="years">
                                                    <input type="text" class="form-control" readonly name="purchase_date" value="<?php echo isset($record->purchase_date) ? date('Y/m/d', $record->purchase_date) : '' ?>">
                                                    <span class="input-group-btn">
                                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                    </span>
                                                </div>
                                                <!-- /input-group -->
                                                <span class="help-block">
                                                    <?php echo lang('pet_select_date') ?> </span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3"><?php echo lang('pet_origin')?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" name="origin" value="<?php echo isset($record->origin) ? $record->origin : '';?>">
                                                <?php echo form_error('origin')?>
                                            </div>
                                        </div>
                                        <div class="form-group last">
                                            <label class="control-label col-md-3"><?php echo lang('pet_microchip')?></label>
                                            <div class="col-md-9">
                                                <input type="text" class="form-control" name="microchip" value="<?php echo isset($record->microchip) ? $record->microchip : '';?>">
                                                <?php echo form_error('microchip')?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn blue"><i class="fa fa-check"></i> Submit</button>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-body">
                                    Are you sure?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
                                    <a class="btn btn-danger btn-ok">Delete</a>
                                </div>
                            </div>
                            <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                    </div>
                    <?php echo form_close()?>
                    <!-- END FORM-->
                    <!--END TABS-->
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>