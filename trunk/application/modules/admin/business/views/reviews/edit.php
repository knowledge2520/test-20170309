<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('review_header') ?>
            <small><?php echo lang('review_edit_header') ?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang().'/business/reviews')?>"><?php echo lang('bc_reviews')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_review_edit')?></li>
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
                                <?php echo lang('review_tab_general')?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/business/reviews/mediaList/'.$record->id)?>">
                                <?php echo lang('review_tab_media')?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/business/reviews/commentList/'.$record->id)?>">
                                <?php echo lang('review_tab_comment')?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/business/activity/review/'.$record->id)?>">
                                <?php echo lang('business_tab_activity_log') ?> </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="portlet light bordered form-fit">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="icon-user font-blue-hoki"></i>
                                    <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('review_edit')?></span>
                                </div>
                            </div>
                            <div class="portlet-body form">
                                <!-- BEGIN FORM-->
                                <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                                <div class="form-body">
                                    <div class="form-group <?php echo form_error('user') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('review_user')?></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="user" value="<?php echo isset($record->user_name) ? $record->user_name : '';?>" readonly="">
                                            <?php echo form_error('user')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('business') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('review_business')?></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="business" value="<?php echo isset($record->business_name) ? $record->business_name : '';?>" readonly="">
                                            <?php echo form_error('business')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('name') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('review_name')?> <span class="required" aria-required="true"> * </span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="name" value="<?php echo isset($record->name) ? $record->name : '';?>">
                                            <?php echo form_error('name')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('description') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('review_description')?> <span class="required" aria-required="true"> * </span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="description" value="<?php echo isset($record->description) ? $record->description : '';?>">
                                            <?php echo form_error('description')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('content') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('review_content')?> <span class="required" aria-required="true"> * </span></label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" name="content"><?php echo isset($record->content) ? $record->content : '';?></textarea>
                                            <?php echo form_error('content')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('rate') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('review_rate')?></label>
                                        <div class="col-md-9">
                                            <?php
                                            $data_status = array(
                                                '1' => '1',
                                                '2' => '2',
                                                '3' => '3',
                                                '4' => '4',
                                                '5' => '5',
                                            );
                                            echo form_dropdown('rate',$data_status,set_value('rate',isset($record->rate) ? $record->rate : 1 ),'class="form-control"');
                                            ?>
                                        </div>
                                    </div>
                                    <div class="form-group last">
                                        <label class="control-label col-md-3"><?php echo lang('review_status')?></label>
                                        <div class="col-md-9">
                                            <?php
                                            $data_status = array(
                                                '1' => lang('review_status_active'),
                                                '0' => lang('review_status_deactivate'),
                                            );
                                            echo form_dropdown('status',$data_status,set_value('status',isset($record->status) ? $record->status : 0 ),'class="form-control"');
                                            ?>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn blue"><i class="fa fa-check"></i> Update</button>
                                            
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