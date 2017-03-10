<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id' => $id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <h3 class="page-title">
                <?php echo lang('pet_talk_header') ?>
                <small><?php echo lang('pet_talk_create') ?></small>
            </h3>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/pet_talk/') ?>"><?php echo lang('bc_pet_talk') ?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_pet_talk_add') ?></li>
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
                                class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('pet_talk_add') ?></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <?php echo form_open_multipart($this->uri->uri_string(), 'method="POST" class="form-horizontal form-bordered form-label-stripped"', $hiddens) ?>
                        <div class="form-body">
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_photo') ?></label>

                                <div class="col-md-9">
                                    <div class="fileinput fileinput-new" data-provides="fileinput">
                                        <div class="fileinput-new thumbnail" style="max-width: 155px; max-height: 155px;">
                                            <?php if (isset($record->photo) && $record->photo != ''): ?>
                                                <img src="<?php echo CMSHelper::output_media($record->photo) ?>" alt=""/>
                                            <?php else: ?>
                                                <img
                                                    src="http://www.placehold.it/155x155/EFEFEF/AAAAAA&amp;text=no+image"
                                                    alt=""/>
                                            <?php endif; ?>
                                        </div>
                                        <div class="fileinput-preview fileinput-exists thumbnail"
                                             style="max-width: 155px; max-height: 155px;">
                                        </div>
                                        <div>
                                            <span class="btn default btn-file">
                                                <span class="fileinput-new">
                                                    <?php echo lang('pet_talk_lbl_select_image') ?> </span>
                                                <span class="fileinput-exists">
                                                    <?php echo lang('pet_talk_lbl_change') ?> </span>
                                                <input type="file" name="photo">
                                            </span>
                                            <a href="#" class="btn red fileinput-exists" data-dismiss="fileinput">
                                                <?php echo lang('pet_talk_lbl_remove') ?> </a>
                                        </div>
                                    </div>
                                    <div class="clearfix margin-top-10">
											<span class="label label-danger">
											<?php echo lang('pet_talk_lbl_note') ?></span>&nbsp;
                                        <?php echo lang('pet_talk_lbl_image_upload_note') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group <?php echo form_error('title') ? 'has-error' : ''; ?>" id="topic-title">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_title')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <textarea rows="3" class="form-control" name="title"><?php echo isset($record->title) ? $record->title : '';?></textarea>
                                    <?php echo form_error('title')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('content') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_content')?> <span class="required" aria-required="true" id="span-topic-required"> * </span></label>
                                <div class="col-md-9">
                                     <textarea rows="10" class="form-control" name="content"><?php echo isset($record->content) ? $record->content : '';?></textarea>
                                    <?php echo form_error('content')?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('pet_talk_category') ?></label>

                                <div class="col-md-9">
                                    <?php if (!empty($categories)): ?>
                                        <select name="category_id" class="form-control" id="topic-category">
                                            <option value="<?php echo 'newsfeed_post' ?>"><?php echo 'Newsfeed Post' ?></option>
                                            <?php foreach ($categories as $c): ?>
                                                <option value="<?php echo $c->id ?>"><?php echo $c->name ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    <?php endif ?>
                                </div>
                            </div>
                            <!-- <div class="form-group last">
                                <label class="control-label col-md-3"><?php //echo lang('pet_talk_status') ?></label>

                                <div class="col-md-9">
                                    <?php
                                    // $data_status = array(
                                    //     '1' => lang('pet_talk_status_active'),
                                    //     '0' => lang('pet_talk_status_deactivate'),
                                    // );
                                    // echo form_dropdown('status', $data_status, set_value('status', isset($record->status) ? $record->status : 1), 'class="form-control"');
                                    ?>
                                </div>
                            </div> -->
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
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