<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('notification_header') ?>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang().'/notification')?>"><?php echo lang('bc_notification')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_notification_detail')?></li>
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
                <div class="portlet light bordered form-fit">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-user font-blue-hoki"></i>
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('notification_detail')?></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                        <div class="form-body">
                            <?php if($record->type == 'individual'):?>
                            <div class="form-group <?php echo form_error('order') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('notification_receiver')?></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="order" disabled value="<?php echo isset($record->first_name) && isset($record->last_name) ? $record->first_name . ' ' . $record->last_name : '';?>">
                                    <?php echo form_error('order')?>
                                </div>
                            </div>
                            <?php endif;?>
                            <div class="form-group <?php echo form_error('title') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('notification_title')?></label>
                                <div class="col-md-9">
                                    <textarea rows="2" class="form-control" name="title" disabled><?php echo isset(json_decode($record->data)->title) ? json_decode($record->data)->title : '';?></textarea>
                                    <?php echo form_error('title')?>
                                </div>
                            </div>                            
                            <div class="form-group last <?php echo form_error('content') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('notification_content')?></label>
                                <div class="col-md-9">
                                    <textarea rows="5" class="form-control" name="content" disabled><?php echo isset(json_decode($record->data)->alert) ? json_decode($record->data)->alert : '';?></textarea>
                                    <?php echo form_error('content')?>
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