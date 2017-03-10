<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('member_edit');?>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang().'/members/')?>"><?php echo lang('bc_member')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_member_edit')?></li>
            </ul>
            <div class="page-toolbar">
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown">
                        <?php echo lang('member_actions')?> <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/members/create')?>"><?php echo lang('member_create')?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- END PAGE HEADER-->
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
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
                <div class="tabbable tabbable-custom tabbable-full-width">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="<?php echo site_url($this->lang->lang().'/members/edit/'.$member_id)?>">
                                <?php echo lang('member_tab_general')?> </a> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/members/mediaList/'.$member_id)?>">
                                <?php echo lang('member_tab_media')?> </a> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/members/my_pets/index/'.$member_id)?>">
                                <?php echo lang('member_tab_pet')?> </a> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/members/bookmarks/index/'.$member_id)?>">
                                <?php echo lang('member_tab_bookmark')?> </a> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/members/reviews/index/'.$member_id)?>">
                                <?php echo lang('member_tab_review')?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/members/checkins/index/'.$member_id)?>">
                                <?php echo lang('member_tab_checkin')?> </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="portlet light bordered form-fit">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="icon-user font-blue-hoki"></i>
                                    <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('member_edit');?></span>
                                </div>
                            </div>
                            <div class="portlet-body form">
                                <!-- BEGIN FORM-->
                                <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                                <div class="form-body">
                                    <div class="form-group">
                                        <label class="control-label col-md-3"><?php echo lang('member_profilePhoto')?></label>
                                        <div class="col-md-9">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail" style="max-width: 155px; max-height: 155px;">
                                                    <?php if(isset($record->profile_photo) && $record->profile_photo!=''):?>
                                                        <img src="<?php echo CMSHelper::output_media($record->profile_photo); ?>" alt=""/>
                                                    <?php else:?>
                                                        <img src="http://www.placehold.it/155x155/EFEFEF/AAAAAA&amp;text=no+image" alt=""/>
                                                    <?php endif;?>
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 155px; max-height: 155px;">
                                                </div>
                                                <div>
                                                    <span class="btn default btn-file">
                                                    <span class="fileinput-new">
                                                    <?php echo lang('member_lbl_select_image')?> </span>
                                                    <span class="fileinput-exists">
                                                    <?php echo lang('member_lbl_change')?> </span>
                                                    <input type="file" name="profile_photo">
                                                    </span>
                                                    <?php if(isset($record->profile_photo) && $record->profile_photo!=''):?><a href="#" class="btn red" data-href="<?php echo site_url($this->lang->lang().'/members/deleteProfilePhoto/'.$record->id)?>" data-toggle="modal" data-target="#confirm-delete"><?php echo lang('member_lbl_remove')?></a><?php endif?>
                                                    <a href="#" class="btn red fileinput-exists" data-dismiss="fileinput">
                                                        <?php echo lang('member_lbl_remove')?> </a>
                                                </div>
                                            </div>
                                            <div class="clearfix margin-top-10">
                                                                    <span class="label label-danger">
                                                                    <?php echo lang('member_lbl_note')?></span>&nbsp;
                                                <?php echo lang('member_lbl_image_upload_note')?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('first_name') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('member_first_name')?> <span class="required" aria-required="true">
                                                * </span></label>
                                        <div class="col-md-9">
                                            <?php echo form_input('first_name',isset($record->first_name) ? $record->first_name : '', 'class="form-control"')?>
                                            <?php echo form_error('first_name')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('last_name') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('member_last_name')?> <span class="required" aria-required="true">
                                                * </span></label>
                                        <div class="col-md-9">
                                            <?php echo form_input('last_name',isset($record->last_name) ? $record->last_name : '', 'class="form-control"')?>
                                            <?php echo form_error('last_name')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('display_name') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('member_display_name')?> <span class="required" aria-required="true">
                                                * </span></label>
                                        <div class="col-md-9">
                                            <?php echo form_input('display_name',isset($record->display_name) ? $record->display_name : '', 'class="form-control"')?>
                                            <?php echo form_error('display_name')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('company') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('member_company')?></label>
                                        <div class="col-md-9">
                                            <?php echo form_input('company',isset($record->company) ? $record->company : '', 'class="form-control"')?>
                                            <?php echo form_error('company')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('email') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('member_email')?> <span class="required" aria-required="true">
                                                * </span></label>
                                        <div class="col-md-9">
                                            <?php echo form_input('email',isset($record->email) ? $record->email : '', 'class="form-control" readonly')?>
                                            <?php echo form_error('email')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('phone') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('member_phone')?></label>
                                        <div class="col-md-9">
                                            <?php echo form_input('phone',isset($record->phone) ? $record->phone : '', 'class="form-control"')?>
                                            <?php echo form_error('phone')?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3"><?php echo lang('member_address')?></label>
                                        <div class="col-md-9">
                                            <?php echo form_input('address',isset($record->address) ? $record->address : '', 'class="form-control"')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('dob') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('member_dob')?> <span class="required" aria-required="true">
                                        * </span></label>
                                        <div class="col-md-3">
                                            <div class="input-group input-medium date date-picker" data-date-format="yyyy/mm/dd" data-date-viewmode="years">
                                                <input type="text" class="form-control" readonly name="dob" value="<?php echo isset($record->dob) ? date('Y/m/d',$record->dob): ''?>">
                                                <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>                                            
                                            <!-- /input-group -->
                                            <span class="help-block">
                                            <?php echo lang('member_select_date')?> </span>
                                            <?php echo form_error('dob');?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3"><?php echo lang('member_gender')?></label>
                                        <div class="col-md-9">
                                            <?php
                                            $options = array(
                                                '0'      => 'Male',
                                                '1'    => 'Female',
                                            );
                                            echo form_dropdown('gender', $options, $record->gender, 'class="form-control"');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('password') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('member_password')?></label>
                                        <div class="col-md-9">
                                            <?php echo form_password('password',isset($password) ? $password : '', 'class="form-control"')?>
                                            <?php echo form_error('password')?>
                                            <?php echo form_hidden('groups','2')?>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>