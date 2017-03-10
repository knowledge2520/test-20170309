<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            Create Badge ID Form
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                <i class="fa fa-home"></i>
                <a href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang().'/badge/index')?>">Badge ID Management</a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a>Badge ID Creation Log</a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    Edit Badge ID Form
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
                        <?php echo $message?>
                    </div>
                <?php endforeach;?>
                <?php endif;?> 
                
                <div class="portlet light bordered form-fit">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-user font-blue-hoki"></i>
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('edit_badge_form')?></span>
                        </div>
                        <!-- Form head icons
                        <div class="actions">
                            <a class="btn btn-circle btn-icon-only btn-default" href="#">
                            <i class="icon-cloud-upload"></i>
                            </a>
                            <a class="btn btn-circle btn-icon-only btn-default" href="#">
                            <i class="icon-wrench"></i>
                            </a>
                            <a href="javascript:;" class="reload">
                            </a>
                            <a class="btn btn-circle btn-icon-only btn-default" href="#">
                            <i class="icon-trash"></i>
                            </a>
                        </div>
                         -->
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                        <div class="form-body">                      
                            <div class="form-group <?php echo form_error('code') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('badge_quantity')?></label>
                                <div class="col-md-9">
                                    <input type="number" class="form-control" name="quantity" value="<?php echo isset($record->quantity) ? $record->quantity : '';?>">
                                    <?php echo form_error('quantity') ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('badge_cate') ?></label>
                                <div class="col-md-9">
                                    <select name="category_id" class="form-control">
                                        <?php if (isset($badge_category) && !empty($badge_category)): ?>
                                            <?php foreach ($badge_category as $item): ?>
                                                <?php if ($record->category_id == $item->id): ?>
                                                    <option value="<?php echo $item->id ?>" selected><?php echo $item->name ?></option>
                                                <?php else: ?>
                                                    <option
                                                        value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                                                <?php endif ?>
                                            <?php endforeach ?>
                                        <?php endif ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <button type="submit" class="btn blue"><i class="fa fa-check"></i> Submit</button>
                                    <button type="reset" class="btn default">Clear</button>
                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-body">
                                        Are you sure?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn default" data-dismiss="modal">Close</button>
                                        <a class="btn btn-danger btn-ok">Delete</a>
                                    </div>
                                </div>
                                <!-- /.modal-content -->
                            </div>
                            <!-- /.modal-dialog -->
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