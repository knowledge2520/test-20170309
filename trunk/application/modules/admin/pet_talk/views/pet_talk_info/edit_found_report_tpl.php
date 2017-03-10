<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);

$current_url = site_url().$this->uri->uri_string().'.html';
$query_url = $_SERVER['QUERY_STRING'];
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <h3 class="page-title">
                <?php echo lang('pet_talk_info_header') ?>
                <small><?php echo lang('pet_talk_info_detail') ?></small>
            </h3>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang().'/pet_talk/pet_talk_info/')?>"><?php echo lang('bc_pet_talk_info')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_pet_talk_info_detail')?></li>
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
                <div class="tabbable tabbable-custom tabbable-full-width">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#">
                                General</a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pet_talk/pet_talk_info/mediaList/'.$record->id)?>">
                                Photos</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="portlet light bordered form-fit">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-comments font-blue-hoki"></i>
                                    <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('pet_talk_info_information')?></span>
                                </div>
                            </div>
                            <div class="portlet-body form">
                                <!-- BEGIN FORM-->
                                <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                                <div class="form-body">
                                    <div class="form-group">
                                        <label class="control-label col-md-3"><?php echo lang('pet_talk_photo')?></label>
                                        <div class="col-md-9">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail" style="max-width: 155px; max-height: 155px;">
                                                    <?php if(isset($cover_image) && !empty($cover_image)):?>
                                                        <img src="<?php echo CMSHelper::output_media($cover_image->source)?>" alt=""/>
                                                    <?php else:?>
                                                        <img src="http://www.placehold.it/155x155/EFEFEF/AAAAAA&amp;text=no+image" alt=""/>
                                                    <?php endif;?>
                                                </div>
                                                <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 155px; max-height: 155px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('name') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('pet_talk_info_name')?></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="name" value="<?php echo (isset($record) && !empty($record->first_name)) ? $record->name : "";?>">
                                            <?php echo form_error('name')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('when') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('pet_talk_info_when')?></label>
                                        <div class="col-md-9">
                                             <input type="text" class="form-control" name="when" value="<?php echo (isset($record) && !empty($record)) ? $record->when : "";?>" readonly>
                                            <?php echo form_error('when')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('where') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('pet_talk_info_where')?></label>
                                        <div class="col-md-9">
                                             <input type="text" class="form-control" name="where" value="<?php echo (isset($record) && !empty($record)) ? $record->where : "";?>" readonly>
                                            <?php echo form_error('where')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('type') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('pet_talk_info_type')?></label>
                                        <div class="col-md-9">
                                             <input type="text" class="form-control" name="type" value="<?php echo (isset($record) && !empty($record)) ? $record->type : "";?>" readonly>
                                            <?php echo form_error('type')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('breed') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('pet_talk_info_breed')?></label>
                                        <div class="col-md-9">
                                             <input type="text" class="form-control" name="breed" value="<?php echo (isset($record) && !empty($record)) ? $record->breed : "";?>" readonly>
                                            <?php echo form_error('breed')?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3"><?php echo lang('pet_talk_info_sex')?></label>
                                        <div class="col-md-9">
                                            <?php
                                            $data_status = array(
                                                'male' => lang('pet_talk_info_male'),
                                                'female' => lang('pet_talk_info_female'),
                                            );
                                            echo form_dropdown('sex',$data_status,set_value('sex',$record->sex),'class="form-control"');
                                            ?>
                                        </div>
                                    </div>

                                    <div class="form-group <?php echo form_error('color') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('pet_talk_info_color')?></label>
                                        <div class="col-md-9">
                                             <input type="text" class="form-control" name="color" value="<?php echo (isset($record) && !empty($record)) ? $record->color : "";?>">
                                            <?php echo form_error('color')?>
                                        </div>
                                    </div>
                                    <div class="form-group last <?php echo form_error('contact') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('pet_talk_info_contact')?></label>
                                        <div class="col-md-9">
                                             <input type="text" class="form-control" name="contact" value="<?php echo (isset($record) && !empty($record)) ? $record->contact : "";?>">
                                            <?php echo form_error('contact')?>
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
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>