<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('member_management')?>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang().'/products/categories')?>"><?php echo lang('bc_category')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_category_edit')?></li>
            </ul>
        </div>
        <!-- END PAGE HEADER-->
        <!-- BEGIN PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
                <?php
                //error message
                $errors = $this->messages->get("error");
                if (!empty($errors)) {
                    foreach ($errors as $error):
                        ?>
                        <div class="alert alert-danger display-hide" style="display: block;">
                            <button data-close="alert" class="close"></button>
                            <strong>Error!</strong> <?php echo $error; ?>
                        </div>
                    <?php endforeach;
                } ?>


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
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('category_edit')?></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                        <div class="form-body">
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('category_photo')?></label>
                                <div class="col-md-9">
                                    <div class="fileinput fileinput-new" data-provides="fileinput">
                                        <div class="fileinput-new thumbnail" style="max-width: 155px; max-height: 155px;">
                                            <?php if(isset($record->photo) && $record->photo!=''):?>
                                                <img src="<?php echo CMSHelper::output_media($record->photo)?>" alt=""/>
                                            <?php else:?>
                                                <img src="http://www.placehold.it/155x155/EFEFEF/AAAAAA&amp;text=no+image" alt=""/>
                                            <?php endif;?>
                                        </div>
                                        <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 155px; max-height: 155px;">
                                        </div>
                                        <div>
                                                        <span class="btn default btn-file">
                                                        <span class="fileinput-new">
                                                        <?php echo lang('category_lbl_select_image')?> </span>
                                                        <span class="fileinput-exists">
                                                        <?php echo lang('category_lbl_change')?> </span>
                                                        <input type="file" name="photo">
                                                        </span>
                                            <a href="#" class="btn red fileinput-exists" data-dismiss="fileinput">
                                                <?php echo lang('category_lbl_remove')?> </a>
                                        </div>
                                    </div>
                                    <div class="clearfix margin-top-10">
                                                    <span class="label label-danger">
                                                    <?php echo lang('category_lbl_note')?></span>&nbsp;
                                        <?php echo lang('category_lbl_image_upload_note')?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group <?php echo form_error('name') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('category_name')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <?php echo form_input('name',set_value('name',isset($record->name) ? $record->name : ''), ' class="form-control"')?>
                                    <?php echo form_error('name')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('sort') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('category_sort')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9" id="long">
                                    <?php echo form_input('sort',set_value('sort',isset($record->sort) ? $record->sort : '' ), 'class="form-control"')?>
                                    <?php echo form_error('sort')?>
                                </div>
                            </div>
                            <div class="form-group last">
                                <label class="control-label col-md-3"><?php echo lang('category_status')?></label>
                                <div class="col-md-9">
                                    <?php
                                    $data_status = array(
                                        '1' => lang('category_status_active'),
                                        '0' => lang('category_status_deactivate'),
                                    );
                                    echo form_dropdown('status',$data_status,set_value('status',isset($record->status) ? $record->status : 1 ),'class="form-control"');
                                    ?>
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