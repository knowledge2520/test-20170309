<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('pet_type_header')?>
            <small><?php echo lang('pet_type_edit_header')?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang().'/pets/pet_types')?>"><?php echo lang('bc_pet_type')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_pet_type_edit')?></li>
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
                            <strong>Error! </strong><?php echo $error;?>
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
                        <strong>Success! </strong> <?php echo $message?>
                    </div>
                <?php endforeach;?>
                <?php endif;?>

                <div class="portlet light bordered form-fit">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-user font-blue-hoki"></i>
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('pet_type_edit')?></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                        <div class="form-body">

                            <div class="form-group <?php echo form_error('name') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('pet_type_name')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="name" value="<?php echo isset($record->name) ? $record->name : '';?>">
                                    <?php echo form_error('name')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('description') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('pet_type_description')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                     <input type="text" class="form-control" name="description" value="<?php echo isset($record->description) ? $record->description : '';?>">
                                    <?php echo form_error('description')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('order') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('pet_type_order')?></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="order" value="<?php echo isset($record->order) ? $record->order : '';?>">
                                    <?php echo form_error('order')?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('pet_type_status')?></label>
                                <div class="col-md-9">
                                    <?php
                                    $status = array(
                                        '0'         => lang('pet_type_status_deactivate'),
                                        '1'         => lang('pet_type_status_active'),
                                    );
                                    echo form_dropdown('status',$status,set_value('status',isset($record->status) ? $record->status : '0' ),'class="form-control"0');
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