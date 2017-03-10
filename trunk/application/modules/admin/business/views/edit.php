<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('business_header') ?>
            <small><?php echo lang('business_edit_header') ?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang().'/business/')?>"><?php echo lang('bc_business')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_business_edit')?></li>
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
                            <a href="<?php echo site_url($this->lang->lang().'/business/edit/'.$record->id)?>">
                                <?php echo lang('business_tab_general')?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/business/mediaList/'.$record->id)?>">
                                <?php echo lang('business_tab_media')?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/business/reviews/index/'.$record->id)?>">
                                <?php echo lang('business_tab_review')?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/business/checkins/index/'.$record->id)?>">
                                <?php echo lang('business_tab_checkin')?> </a>
                        </li>
                        <?php if($is_admin):?>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/business/activity/index/'.$record->id)?>">
                                Activity Log </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <div class="tab-content">
                        <div class="portlet light bordered form-fit">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="icon-user font-blue-hoki"></i>
                                    <span
                                        class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('bc_business_edit') ?></span>
                                </div>
                            </div>
                            <div class="portlet-body form">
                                <!-- BEGIN FORM-->
                                <?php echo form_open_multipart($this->uri->uri_string(), 'method="POST" class="form-horizontal form-bordered form-label-stripped"', $hiddens) ?>
                                <div class="form-body">
                                    <div class="form-group">
                                        <label
                                            class="control-label col-md-3"><?php echo lang('business_profilePhoto') ?></label>

                                        <div class="col-md-9">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-new thumbnail"
                                                     style="max-width: 155px; max-height: 155px;">
                                                    <?php if (isset($record->photo) && $record->photo != ''): ?>
                                                        <img src="<?php echo CMSHelper::output_media($record->photo) ?>"
                                                             alt=""/>
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
                                                    <?php echo lang('business_lbl_select_image') ?> </span>
                                                    <span class="fileinput-exists">
                                                    <?php echo lang('business_lbl_change') ?> </span>
                                                    <input type="file" name="photo">
                                                    </span>
                                                    <a href="#" class="btn red fileinput-exists"
                                                       data-dismiss="fileinput">
                                                        <?php echo lang('business_lbl_remove') ?> </a>
                                                </div>
                                            </div>
                                            <div class="clearfix margin-top-10">
                                                <span class="label label-danger">
                                                <?php echo lang('business_lbl_note') ?></span>&nbsp;
                                                <?php echo lang('business_lbl_image_upload_note') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group <?php echo form_error('name') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('business_edit_name') ?> <span
                                                class="required" aria-required="true"> * </span></label>

                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="name" value="<?php echo isset($record->name) ? $record->name : '';?>">
                                            <?php echo form_error('name') ?>
                                        </div>
                                    </div>                                    
                                    <div class="form-group <?php echo form_error('hour') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('business_edit_hour') ?> </label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="hour" value="<?php echo isset($record->hour) ? $record->hour : '';?>">
                                            <?php echo form_error('hour') ?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('phone') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('business_edit_phone')?> </label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="phone" value="<?php echo isset($record->phone) ? $record->phone : '';?>">
                                            <?php echo form_error('phone')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('website') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('business_website') ?> </label>
                                        <div class="col-md-9">
                                            <input type="url" class="form-control" name="website" value="<?php echo isset($record->website) ? $record->website : '';?>">
                                            <?php echo form_error('website') ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3"></label>

                                        <div class="col-md-9">
                                            <?php echo $map['js']; ?>
                                            <?php echo $map['html']; ?>
                                        </div>
                                    </div>                                    
                                    <div class="form-group <?php echo form_error('address_location') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('business_location')?> <span
                                                class="required" aria-required="true"> * </span></label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="address_location" value="<?php echo isset($record->address) ? $record->address : '';?>">
                                            <span class="help-block"> Generate longitude and latitude base on the location address</span>
                                            <?php echo form_error('address_location')?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('latitude') ? 'has-error' : ''; ?>">
                                        <label
                                            class="control-label col-md-3"><?php echo lang('business_latitude') ?></label>

                                        <div class="col-md-9" id="lat">
                                            <input type="text" class="form-control" id="lat" name="latitude" value="<?php echo isset($record->latitude) ? $record->latitude : LISTING_DEFAULT_LATITUDE;?>">
                                            <?php echo form_error('latitude') ?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('longitude') ? 'has-error' : ''; ?>">
                                        <label
                                            class="control-label col-md-3"><?php echo lang('business_longitude') ?></label>

                                        <div class="col-md-9" id="long">
                                            <input type="text" class="form-control" id="long" name="longitude" value="<?php echo isset($record->longitude) ? $record->longitude : LISTING_DEFAULT_LONGITUDE;?>">
                                            <?php echo form_error('longitude') ?>
                                        </div>
                                    </div>
                                    <div class="form-group <?php echo form_error('categories[]') ? 'has-error' : ''; ?>">
                                        <label class="control-label col-md-3"><?php echo lang('business_categories') ?>
                                            <span class="required" aria-required="true"> * </span></label>

                                        <div class="col-md-9">
                                            <div class="form-control height-auto">
                                                <div class="scroller" style="height:275px;" data-always-visible="1">
                                                    <ul class="list-unstyled">
                                                        <?php if (!empty($categories_items)): ?>
                                                            <?php foreach ($categories_items as $c): $i = 0 ?>
                                                                <?php if (isset($record->categories) && !empty($record->categories)): ?>
                                                                    <?php foreach ($record->categories as $row): ?>
                                                                        <?php if ($c->id == $row->id): ?>
                                                                            <li><label><input type="checkbox"
                                                                                              value="<?php echo $c->id ?>"
                                                                                              name="categories[]"
                                                                                              checked/> <?php echo $c->name ?>
                                                                                </label></li>
                                                                            <?php $i++;
                                                                            break; ?>
                                                                        <?php endif ?>
                                                                    <?php endforeach ?>
                                                                    <?php if ($i == 0): ?>
                                                                        <li><label><input type="checkbox"
                                                                                          value="<?php echo $c->id ?>"
                                                                                          name="categories[]"/> <?php echo $c->name ?>
                                                                            </label></li>
                                                                    <?php endif ?>
                                                                <?php else: ?>
                                                                    <li><label><input type="checkbox"
                                                                                      value="<?php echo $c->id ?>"
                                                                                      name="categories[]"/> <?php echo $c->name ?>
                                                                        </label></li>
                                                                <?php endif ?>
                                                            <?php endforeach ?>
                                                        <?php endif ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        <span class="help-block">
                                        select one or more categories </span>

                                            <?php echo form_error('categories') ?>

                                        </div>
                                    </div>
                                    <?php if($record->status == 0 || $record->status == 1):?>
                                    <div class="form-group last">
                                        <label
                                            class="control-label col-md-3"><?php echo lang('business_status') ?></label>

                                        <div class="col-md-9">
                                            <?php
                                            $data_status = array(
                                                '1' => lang('business_status_active'),
                                                '0' => lang('business_status_deactivate'),
                                            );
                                            echo form_dropdown('status', $data_status, set_value('status', isset($record->status) ? $record->status : 1), 'class="form-control"');
                                            ?>
                                        </div>
                                    </div>
                                    <?php endif;?>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-offset-3 col-md-9">
                                            <button type="submit" class="btn blue"><i class="fa fa-check"></i> Submit
                                            </button>
                                            
                                        </div>
                                    </div>
                                </div>
                                <?php echo form_close() ?>
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