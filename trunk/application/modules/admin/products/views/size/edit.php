<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('product_edit_header') ?>
            <small><?php echo lang('product_size_header') ?></small>
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
                    <a href="<?php echo site_url($this->lang->lang() . '/products/edit/' . $product_id) ?>"><?php echo lang('bc_product_edit') ?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/products/size/index/' . $product_id) ?>"><?php echo lang('bc_product_size') ?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <?php echo lang('bc_product_size_edit') ?>
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
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('product_size_edit')?></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
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

                            <div class="form-group last <?php echo form_error('size') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('product_size')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <?php echo form_input('size',set_value('size',isset($record->size) ? $record->size : ''), ' class="form-control"')?>
                                    <?php echo form_error('size')?>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <?php echo form_hidden('product_id', $product_id);?>
                                    <button type="submit" class="btn blue"><i class="fa fa-check"></i> <?php echo lang('product_button_update')?></button>
                                    <a href="<?php echo site_url($this->lang->lang() . '/products/size/index/' . $product_id) ?>"><button type="button" class="btn default"><?php echo lang('product_button_cancel')?></button></a>
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