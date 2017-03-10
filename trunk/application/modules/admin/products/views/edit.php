<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id' => $id);
?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
<div class="page-content">
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
    <?php echo lang('product_edit_header') ?>
</h3>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <a href="<?php echo site_url($this->lang->lang() . '/products/') ?>"><?php echo lang('bc_product') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?php echo lang('bc_product_edit') ?>
        </li>
    </ul>
    <div class="page-toolbar">
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown"
                    data-hover="dropdown" data-delay="1000" data-close-others="true">
                <?php echo lang('product_action') ?> <i class="fa fa-angle-down"></i>
            </button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/products/color/create/' . $record->id) ?>"><?php echo lang('product_color_create') ?></a>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/products/size/create/' . $record->id) ?>"><?php echo lang('product_size_create') ?></a>
                </li>
                 <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/products/quantity/create/' . $record->id) ?>"><?php echo lang('product_quantity_create') ?></a>
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
        <div class="portlet">

            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-shopping-cart"></i><?php echo lang('product_result_records');?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="tabbable">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#tab_1" data-toggle="tab">
                                <?php echo lang('product_tab_general');?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang() . '/products/color/index/' . $record->id) ?>">
                                <?php echo lang('product_tab_color');?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang() . '/products/size/index/' . $record->id) ?>">
                                <?php echo lang('product_tab_size');?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang() . '/products/quantity/index/' . $record->id) ?>">
                                <?php echo lang('product_tab_quantity');?> </a>
                        </li>
                    </ul>
                    <div class="tab-content no-space">
                        <div class="tab-pane active" id="tab_1">
                            <?php echo form_open_multipart($this->uri->uri_string(), 'method="POST" class="form-horizontal form-row-seperated"', $hiddens) ?>
                            <div class="form-body">
                                <div class="form-group">
                                    <label
                                        class="control-label col-md-2"><?php echo lang('product_photo') ?>:</label>

                                    <div class="col-md-10">
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
                                                    <?php echo lang('product_lbl_select_image') ?> </span>
                                                    <span class="fileinput-exists">
                                                    <?php echo lang('product_lbl_change') ?> </span>
                                                    <input type="file" name="photo">
                                                    </span>
                                                <a href="#" class="btn red fileinput-exists"
                                                   data-dismiss="fileinput">
                                                    <?php echo lang('product_lbl_remove') ?> </a>
                                            </div>
                                        </div>
                                        <div class="clearfix margin-top-10">
                                                <span class="label label-danger">
                                                <?php echo lang('product_lbl_note') ?></span>&nbsp;
                                            <?php echo lang('product_lbl_image_upload_note') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label"><?php echo lang('product_name') ?>
                                        <span class="required" aria-required="true"> * </span> :</label>

                                    <div class="col-md-10">
                                        <?php echo form_input('name', set_value('name', isset($record->name) ? $record->name : ''), ' class="form-control"') ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label"><?php echo lang('product_description') ?>
                                        :</label>

                                    <div class="col-md-10">
                                        <?php echo form_textarea('description', set_value('name', isset($record->description) ? $record->description : ''), ' class="form-control"') ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label"><?php echo lang('product_category') ?>
                                        :</label>

                                    <div class="col-md-10">
                                        <select class="form-control" name="category">
                                            <?php if (isset($categories) && !empty($categories)): ?>
                                                <?php foreach ($categories as $item): ?>
                                                    <?php if ($item->id == $record->category_id): ?>
                                                        <option value="<?php echo $item->id ?>"
                                                                selected><?php echo $item->name ?></option>
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
                                    <label class="col-md-2 control-label"><?php echo lang('product_price') ?>
                                        :</label>

                                    <div class="col-md-10">
                                        <?php echo form_input('price', set_value('price', isset($record->price) ? $record->price : ''), ' class="form-control"') ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label"><?php echo lang('product_cost') ?>
                                        :</label>

                                    <div class="col-md-10">
                                        <?php echo form_input('cost', set_value('cost', isset($record->cost) ? $record->cost : ''), ' class="form-control"') ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label
                                        class="col-md-2 control-label"><?php echo lang('product_price_on_sale') ?>
                                        :</label>

                                    <div class="col-md-10">
                                        <?php echo form_input('price_on_sale', set_value('price_on_sale', isset($record->price_on_sale) ? $record->price_on_sale : ''), ' class="form-control"') ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label
                                        class="col-md-2 control-label"><?php echo lang('product_free_shipping') ?>
                                        :</label>

                                    <div class="col-md-10">
                                        <?php
                                        $data = array(
                                            'name'      => 'free_shipping',
                                            'value'     => (isset($record->free_shipping)) ? $record->free_shipping : '',
                                            'checked'   => (isset($record->free_shipping) && $record->free_shipping != 0) ? TRUE : FALSE,
                                            'style'     => 'checkbox-inline',
                                            'class'     => 'checkbox-inline'
                                        );

                                        echo form_checkbox($data);
                                        ?>
                                    </div>
                                </div>
                                <div class="form-group <?php echo form_error('stock') ? 'has-error' : ''; ?>">
                                    <label class="col-md-2 control-label"><?php echo lang('product_stock') ?>
                                        :</label>

                                    <div class="col-md-10">
                                        <?php echo form_input('stock', set_value('stock', isset($record->stock) ? $record->stock : ''), ' class="form-control"') ?>
                                        <?php echo form_error('stock')?>
                                    </div>
                                </div>
                                <div class="form-group <?php echo form_error('sort') ? 'has-error' : ''; ?>">
                                    <label class="col-md-2 control-label"><?php echo lang('product_sort') ?> <span class="required" aria-required="true"> * </span>
                                        :</label>

                                    <div class="col-md-10">
                                        <?php echo form_input('sort', set_value('sort', isset($record->sort) ? $record->sort : ''), ' class="form-control"') ?>
                                        <?php echo form_error('sort')?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-2 control-label"><?php echo lang('product_status') ?>
                                        :</label>

                                    <div class="col-md-10">
                                        <?php
                                        $data_status = array(
                                            '1' => lang('product_status_active'),
                                            '0' => lang('product_status_deactivate'),
                                        );
                                        echo form_dropdown('status', $data_status, set_value('status', isset($record->status) ? $record->status : 1), 'class="form-control"');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="actions btn-set">
                                <div class="row">
                                    <div class="col-md-offset-2 col-md-10">
                                        <button type="submit" class="btn blue"><i class="fa fa-check"></i> Save
                                        </button>
                                        <button type="button" name="back" class="btn default">Cancel</button>
                                    </div>
                                </div>
                            </div>
                            <?php echo form_close() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->
</div>
</div>
<!-- END CONTENT -->