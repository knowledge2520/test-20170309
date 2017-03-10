<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
<div class="page-content">
<!-- BEGIN PAGE HEADER-->
<h3 class="page-title">
    <?php echo lang('faq_header') ?>
    <small><?php echo lang('faq_edit_header') ?></small>
</h3>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li><i class="fa fa-home"></i> <a
                href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
            <i class="fa fa-angle-right"></i></li>
        <li><a href="<?php echo site_url($this->lang->lang().'/faqs/')?>"><?php echo lang('bc_faqs')?></a>
            <i class="fa fa-angle-right"></i></li>
        <li><?php echo lang('bc_faq_edit')?></li>
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
<div class="portlet light bordered form-fit">
    <div class="portlet-title">
        <div class="caption">
            <i class="icon-user font-blue-hoki"></i>
                                <span
                                    class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('bc_faq_edit') ?></span>
        </div>
    </div>
    <div class="portlet-body form">
        <!-- BEGIN FORM-->
        <?php echo form_open_multipart($this->uri->uri_string(), 'method="POST" class="form-horizontal form-bordered form-label-stripped"', $hiddens) ?>
        <div class="form-body">
            <div class="form-group <?php echo form_error('question') ? 'has-error' : ''; ?>">
                <label class="control-label col-md-3"><?php echo lang('faq_question')?> <span class="required" aria-required="true"> * </span></label>
                <div class="col-md-9">
                    <?php
                        $option = array(
                            'name'      => 'question',
                            'value'     => isset($record->question) ? $record->question : '',
                            'rows'      => 5,
                            'class'     => 'form-control'
                        );
                        echo form_textarea($option)
                    ?>
                    <?php echo form_error('question')?>
                </div>
            </div>
            <div class="form-group <?php echo form_error('answer') ? 'has-error' : ''; ?>">
                <label class="control-label col-md-3"><?php echo lang('faq_answer')?> <span class="required" aria-required="true"> * </span></label>
                <div class="col-md-9">
                    <?php
                        $option = array(
                            'name'      => 'answer',
                            'value'     => isset($record->answer) ? $record->answer : '',
                            'rows'      => 5,
                            'class'     => 'form-control'
                        );
                        echo form_textarea($option)?>
                    <?php echo form_error('answer')?>
                </div>
            </div>
            <div class="form-group <?php echo form_error('order') ? 'has-error' : ''; ?>">
                <label class="control-label col-md-3"><?php echo lang('faq_order')?> <span class="required" aria-required="true"> * </span></label>
                <div class="col-md-9">
                    <?php echo form_input('order',set_value('order',isset($record->order) ? $record->order : ''), 'class="form-control"')?>
                    <?php echo form_error('order')?>
                </div>
            </div>
            <div class="form-group last">
                <label
                    class="control-label col-md-3"><?php echo lang('faq_status') ?></label>

                <div class="col-md-9">
                    <?php
                    $data_status = array(
                        '1' => lang('faq_status_active'),
                        '0' => lang('faq_status_deactivate'),
                    );
                    echo form_dropdown('status', $data_status, set_value('status', isset($record->status) ? $record->status : 1), 'class="form-control"');
                    ?>
                </div>
            </div>
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
<!-- END PAGE CONTENT-->
</div>
</div>