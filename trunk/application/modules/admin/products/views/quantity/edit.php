<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('product_edit_header') ?>
            <small><?php echo lang('product_quantity_header') ?></small>
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
                    <a href="<?php echo site_url($this->lang->lang() . '/products/quantity/index/' . $product_id) ?>"><?php echo lang('bc_product_quantity') ?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <?php echo lang('bc_product_quantity_edit') ?>
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
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('product_quantity_edit')?></span>
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
							                            
                            <div class="form-group <?php echo form_error('color_id') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('product_color')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                	<select class="form-control" name="color_id">
                                	<?php if(isset($color) && !empty($color)):?>
                                		<?php foreach($color as $c):?>
                                			<?php if($c->id == $record->color_id):?>
                                				<option value="<?php echo $c->id;?>" selected><?php echo $c->color;?></option>
                                			<?php else:?>
                                				<option value="<?php echo $c->id;?>"><?php echo $c->color;?></option>
                                			<?php endif;?>
                                		<?php endforeach;?>
                                	<?php endif;?>
                                	</select>
                                    <?php echo form_error('color_id')?>
                                </div>
                            </div>
                            
							<div class="form-group <?php echo form_error('size_id') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('product_size')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <select class="form-control" name="size_id">
                                	<?php if(isset($size) && !empty($size)):?>
                                		<?php foreach($size as $s):?>
                                			<?php if($s->id == $record->size_id):?>
                                				<option value="<?php echo $s->id;?>" selected><?php echo $s->size;?></option>
                                			<?php else:?>
                                				<option value="<?php echo $s->id;?>"><?php echo $s->size;?></option>
                                			<?php endif;?>
                                		<?php endforeach;?>
                                	<?php endif;?>
                                	</select>
                                    <?php echo form_error('size_id')?>
                                </div>
                            </div>
                                                        
							<div class="form-group <?php echo form_error('quantity') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('product_quantity')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <?php echo form_input('quantity',set_value('quantity',isset($record->quantity) ? $record->quantity : ''), ' class="form-control"')?>
                                    <?php echo form_error('quantity')?>
                                </div>
                            </div>
                            
                            <div class="form-group last <?php echo form_error('sell_quantity') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('product_sell_quantity')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <?php echo form_input('sell_quantity',set_value('sell_quantity',isset($record->sell_quantity) ? $record->sell_quantity : ''), ' class="form-control"')?>
                                    <?php echo form_error('sell_quantity')?>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <?php echo form_hidden('product_id', $product_id);?>
                                    <button type="submit" class="btn blue"><i class="fa fa-check"></i> <?php echo lang('product_button_update')?></button>
                                    <a href="<?php echo site_url($this->lang->lang() . '/products/quantity/index/' . $product_id) ?>"><button type="button" class="btn default"><?php echo lang('product_button_cancel')?></button></a>
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