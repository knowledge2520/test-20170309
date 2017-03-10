<?php
$pagination = isset($paging) ? $paging->create_links() : false;
$pagination_total_rows = isset($total) ? $total : 0;
$pagination_total_rows_ppage = (isset($paging) && $paging->cur_page >= 1 && isset($count) && $paging->per_page == $count) ? $paging->per_page * $paging->cur_page : $pagination_total_rows;
$pagination_start_item = $total == 0 ? 0 : (isset($paging) && $paging->cur_page > 1 ? ($paging->per_page * ($paging->cur_page - 1)) : 1);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('notification_header') ?>
            <small><?php echo lang('notification_individual') ?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/notification/') ?>"><?php echo lang('bc_notification') ?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                 <li>
                     <span> <?php echo lang('bc_notification_individual') ?> </span>
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
                        <?php
                    endforeach;
                }
                ?>

                <?php
                //success message
                $success_msg = $this->messages->get('success');
                if (!empty($success_msg)):
                    ?>
                    <?php foreach ($success_msg as $message): ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button"></button>
                            <strong>Success!</strong> <?php echo $message ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="portlet-body">
                    <!--BEGIN TABS-->
                    <div class="tabbable tabbable-custom tabbable-full-width">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- CONTENT TAB HERE -->
                                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                                <div class="portlet box blue-hoki">
                                    <div class="portlet-title">
                                        <div class="caption">
                                            <i class="fa fa-globe"></i> <?php echo lang('notification_send_push') ?>
                                        </div>
                                        <div class="tools">
                                        </div>
                                    </div>
                                    <div class="portlet-body">                                        
                                        <!-- BEGIN FORM-->

                                        <?php echo form_open($this->uri->uri_string(), 'method="POST" class="form-horizontal form-bordered form-label-stripped"') ?>
                                        <div class="form-body">
                                            
                                            <div class="form-group <?php echo form_error('checked') ? 'has-error' : ''; ?>">
                                                <label class="control-label col-md-3">Users <span class="required" aria-required="true"> * </span></label>
                                                <div class="col-md-9">
                                                   <input type="hidden" name="url" id="url" value="<?php echo site_url('notification/getUsers');?>">
                                                    <div id="sample_1_filter" class="dataTables_filter">                                              
                                                        <select name="checked[]" class="js-example-basic-single form-control input-large input-inline"  multiple="multiple" >
                                                        </select>
                                                            <?php //echo form_input('txt_search', isset($txt_search_value) ? $txt_search_value : '', 'type="search" class="form-control input-small input-inline" placeholder=""') ?> 
                                                            <?php echo form_error('checked') ?>                                         
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group <?php echo form_error('title') ? 'has-error' : ''; ?>">
                                                <label class="control-label col-md-3"><?php echo lang('notification_title') ?> <span class="required" aria-required="true"> * </span></label>
                                                <div class="col-md-9">
                                                    <textarea rows="3" class="form-control" name="title"></textarea><?php echo form_error('title') ?>
                                                </div>
                                            </div>
                                            <div class="form-group last <?php echo form_error('message') ? 'has-error' : ''; ?>">
                                                <label class="control-label col-md-3"><?php echo lang('notification_message') ?> <span class="required" aria-required="true"> * </span></label>
                                                <div class="col-md-9">
                                                    <textarea rows="5" class="form-control" name="message"></textarea><?php echo form_error('message') ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <div class="row">
                                                <div class="col-md-offset-3 col-md-9">
                                                    <input type="hidden" name="options" value="individual">
                                                    <a href="<?php echo site_url('notification');?>"><button type="button" name="submit" value="all" class="btn default"> Back</button></a>
                                                    <button type="submit" name="submit" value="individual" class="btn blue"><i class="fa fa-check"></i> Publish</button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php echo form_close() ?>
                                        <!-- END FORM-->
                                    </div>
                                </div>
                                <!-- END EXAMPLE TABLE PORTLET-->
                            </div>
                        </div>
                    </div>
                    <!--END TABS-->
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
</div>
</div>
