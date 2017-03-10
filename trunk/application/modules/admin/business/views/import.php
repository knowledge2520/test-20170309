<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>

<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('business_header')?>
            <small><?php echo lang('business_import_header')?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang().'/business')?>"><?php echo lang('bc_business')?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <?php echo lang('bc_business_import')?>
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
		                    <strong>Error!</strong><?php echo $error;?>
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
                            <i class="fa fa-edit font-blue-hoki"></i>
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('business_import')?></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        <form method="POST" id="uploadForm" class="form-horizontal form-bordered form-label-stripped" action="<?php echo site_url($this->uri->uri_string());?>" target="_blank" enctype="multipart/form-data">
                        <div class="form-body">
                            <div class="form-group last">
                                <label class="control-label col-md-3"><?php echo lang('business_file')?></label>
                                <div class="col-md-9">
                                    <input id="userImage" type="file" name="filename">
                                    <p class="help-block">
                                        <span class="label label-danger">
                                        <?php echo lang('business_lbl_note')?>
                                        </span>&nbsp;
                                        <?php echo lang('business_lbl_csv_upload_note')?>                                    
                                    </p>
                                </div>
                            </div>
                        </div>
<!--                        <div class="form-body">
                            <div class="form-group last">
                                <div id="progress-div"><div id="progress-bar"></div></div>
                                <div id="loader-icon" style="display:none;"><img src="<?php echo base_url('../../themes/admin/images/LoaderIcon.gif');?>" /></div>
                                <div id="targetLayer"></div>
                            </div>
                        </div>-->
                       
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="submit" class="btn blue" id="btnSubmit" value="Submit" name="upload">
                                    
                                </div>
                            </div>
                        </div>
                        </form>
                        <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>