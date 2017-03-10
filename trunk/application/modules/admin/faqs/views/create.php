<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('faq_header') ?>
            <small><?php echo lang('faq_create_header') ?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang().'/faqs/')?>"><?php echo lang('bc_faqs')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_faq_create')?></li>
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
                            <?php echo $error;?>
                        </div>
                    <?php endforeach;}?>
                <div class="portlet light bordered form-fit">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-user font-blue-hoki"></i>
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('faq_create')?></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <?php echo form_open($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                        <div class="form-body">

                            <div class="form-group <?php echo form_error('question') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('faq_question')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <?php echo form_textarea('question',set_value('question',isset($record->question) ? $record->question : ''), ' class="form-control"')?>
                                    <?php echo form_error('question')?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('address') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('faq_answer')?> <span class="required" aria-required="true"> * </span></label>
                                <div class="col-md-9">
                                    <?php echo form_textarea('answer',set_value('answer',isset($record->answer) ? $record->answer : ''), 'class="form-control"')?>
                                    <?php echo form_error('answer')?>
                                </div>
                            </div>
                            <div class="form-group last">
                                <label class="control-label col-md-3"><?php echo lang('faq_status')?></label>
                                <div class="col-md-9">
                                    <?php
                                    $data_status = array(
                                        '1' => lang('faq_status_active'),
                                        '0' => lang('faq_status_deactivate'),
                                    );
                                    echo form_dropdown('status',$data_status,set_value('status',isset($record->status) ? $record->status : 1 ),'class="form-control"');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <button type="submit" class="btn blue"><i class="fa fa-check"></i> Create</button>
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