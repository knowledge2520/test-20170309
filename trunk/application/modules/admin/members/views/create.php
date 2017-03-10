<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('create_member_heading');?>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang().'/members/')?>"><?php echo lang('bc_member')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_member_add')?></li>
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
                            <?php echo $error;?>
                        </div>
                    <?php endforeach;}?>
                <div class="portlet light bordered form-fit">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-user font-blue-hoki"></i>
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('create_member_subheading');?></span>
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
                                                <img src="<?php echo CMSHelper::output_media($record->profile_photo)?>" alt=""/>
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
                                    <?php echo form_input('first_name',isset($first_name) ? $first_name : '', 'class="form-control"')?>
                                    <?php echo form_error('first_name')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('last_name') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('member_last_name')?> <span class="required" aria-required="true">
										* </span></label>
                                <div class="col-md-9">
                                    <?php echo form_input('last_name',isset($last_name) ? $last_name : '', 'class="form-control"')?>
                                    <?php echo form_error('last_name')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('company') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('member_company')?></label>
                                <div class="col-md-9">
                                    <?php echo form_input('company',isset($company) ? $company : '', 'class="form-control"')?>
                                    <?php echo form_error('company')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('email') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('member_email')?> <span class="required" aria-required="true">
										* </span></label>
                                <div class="col-md-9">
                                    <?php echo form_input('email',isset($email) ? $email : '', 'class="form-control"')?>
                                    <?php echo form_error('email')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('phone') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('member_phone')?></label>
                                <div class="col-md-9">
                                    <?php echo form_input('phone',isset($phone) ? $phone : '', 'class="form-control"')?>
                                    <?php echo form_error('phone')?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('member_address')?></label>
                                <div class="col-md-9">
                                    <?php echo form_input('address',isset($address) ? $address : '', 'class="form-control"')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('dob') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('member_dob')?> <span class="required" aria-required="true">
										* </span></label>
                                <div class="col-md-3">
                                    <div class="input-group input-medium date date-picker" data-date-format="yyyy/mm/dd" data-date-viewmode="years">
                                        <input type="text" class="form-control" readonly name="dob">
                                        <span class="input-group-btn">
                                        <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                    </div>
                                    <!-- /input-group -->
                                    <span class="help-block">
                                    <?php echo lang('member_select_date')?> </span>
                                    <?php echo form_error('dob')?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('member_gender')?></label>
                                <div class="col-md-9">
                                    <?php
                                        $options = array(
                                            '0'    => 'Male',
                                            '1'    => 'Female',
                                        );
                                        echo form_dropdown('gender', $options, isset($record->gender) ? $record->gender : 0, 'class="form-control"');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('password') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('member_password')?> <span class="required" aria-required="true">
										* </span></label>
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
                        <?php echo form_close()?>
                        <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>