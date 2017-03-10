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
            Create Badge ID Form
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
                <strong>Success!</strong> <?php echo $message?>
            </div>
        <?php endforeach;?>
        <?php endif;?>
        
        <div class="portlet light bordered form-fit">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('create_badge_form')?></span>
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
                    
                     <div class="form-group <?php echo form_error('quantity') ? 'has-error' : ''; ?>">
                        <label class="control-label col-md-3"><?php echo lang('badge_quantity')?></label>
                        <div class="col-md-9">
                            <input type="number" class="form-control" name="quantity" value="<?php echo isset($record->quantity) ? $record->quantity : '';?>">
                            <?php echo form_error('quantity') ?>
                        </div>
                    </div>
                    <div class="form-group <?php echo form_error('category_id') ? 'has-error' : ''; ?>">
                            <label class="control-label col-md-3"><?php echo lang('badge_cate')?></label>
                            <div class="col-md-9">
                                <select name="category_id" class="form-control">
                                    <?php if (isset($badge_category) && !empty($badge_category)): ?>
                                        <?php foreach ($badge_category as $item): ?>
                                            <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                                        <?php endforeach ?>
                                    <?php endif ?>
                                </select>
                                <?php echo form_error('category_id') ?>
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
                <?php echo form_close()?>
                <!-- END FORM-->
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->
</div>
</div>