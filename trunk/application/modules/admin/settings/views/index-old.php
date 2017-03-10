<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
	<div class="page-content">
		<!-- BEGIN PAGE HEADER-->
		<h3 class="page-title">
            <?php echo lang('setting_header') ?>
        </h3>
		<div class="page-bar">
			<ul class="page-breadcrumb">
				<li><i class="fa fa-home"></i> <a
					href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
					<i class="fa fa-angle-right"></i></li>
				<li><a
					href="<?php echo site_url($this->lang->lang() . '/settings/') ?>"><?php echo lang('bc_setting') ?></a>
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
                    <div class="tabbable tabbable-custom tabbable-full-width">
			 <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#">
                                    <?php echo lang('setting_tab_general') ?> </a>
                            </li>
                            <li>
                                <a href="<?php echo site_url($this->lang->lang() . '/settings/media') ?>">
                                    <?php echo lang('setting_tab_media') ?> </a>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
                            <div id="tab_1_1" class="tab-pane active">
                                <div class="portlet light bordered form-fit">
					
					<div class="portlet-body form">
						<!-- BEGIN FORM-->
                        <?php echo form_open_multipart($this->uri->uri_string(),'method="POST" class="form-horizontal form-bordered form-label-stripped"',$hiddens)?>
                        <div class="form-body">
                            <div class="form-group <?php echo form_error('fee_shipping') ? 'has-error' : ''; ?>">
								<label class="control-label col-md-3"><?php echo lang('setting_shipping_fee')?> <span class="required" aria-required="true"> * </span></label>
								<div class="col-md-3 input-inline input-medium">
                                    <?php echo form_input('fee_shipping',set_value('fee_shipping',isset($shipping->fee_shipping) ? $shipping->fee_shipping : ''), ' class="form-control"')?>
                                	<?php echo form_error('fee_shipping')?>
                                </div>
								<span class="help-inline" style="padding-top: 20px"> SGD </span>
							</div>
							<div class="form-group <?php echo form_error('meta_keywords') ? 'has-error' : ''; ?>">
								<label class="control-label col-md-3"><?php echo lang('setting_meta_keywords')?> <span class="required" aria-required="true"> * </span></label>
								<div class="col-md-9">
                                    <?php echo form_input('meta_keywords',set_value('meta_keywords',isset($setting['meta_keywords']) ? $setting['meta_keywords'] : ''), ' class="form-control"')?>
                                	<?php echo form_error('meta_keywords')?>
                                </div>
							</div>
							<div class="form-group <?php echo form_error('meta_description') ? 'has-error' : ''; ?>">
								<label class="control-label col-md-3"><?php echo lang('setting_meta_description')?> <span class="required" aria-required="true"> * </span></label>
								<div class="col-md-9">
                                    <?php echo form_input('meta_description',set_value('meta_description',isset($setting['meta_description']) ? $setting['meta_description'] : ''), ' class="form-control"')?>
                                	<?php echo form_error('meta_description')?>
                                </div>
							</div>
							<div class="form-group <?php echo form_error('website_address') ? 'has-error' : ''; ?>">
								<label class="control-label col-md-3"><?php echo lang('setting_website_address')?> <span class="required" aria-required="true"> * </span></label>
								<div class="col-md-9">
                                    <?php echo form_input('website_address',set_value('website_address',isset($setting['website_address']) ? $setting['website_address'] : ''), ' class="form-control"')?>
                                	<?php echo form_error('website_address')?>
                                </div>
							</div>
							<div class="form-group <?php echo form_error('website_email') ? 'has-error' : ''; ?>">
								<label class="control-label col-md-3"><?php echo lang('setting_website_email')?> <span class="required" aria-required="true"> * </span></label>
								<div class="col-md-9">
                                    <?php echo form_input('website_email',set_value('website_email',isset($setting['website_email']) ? $setting['website_email'] : ''), ' class="form-control"')?>
                                	<?php echo form_error('website_email')?>
                                </div>
							</div>
							<div class="form-group <?php echo form_error('website_phone') ? 'has-error' : ''; ?>">
								<label class="control-label col-md-3"><?php echo lang('setting_website_phone')?> <span class="required" aria-required="true"> * </span></label>
								<div class="col-md-9">
                                    <?php echo form_input('website_phone',set_value('website_phone',isset($setting['website_phone']) ? $setting['website_phone'] : ''), ' class="form-control"')?>
                                	<?php echo form_error('website_phone')?>
                                </div>
							</div>
							<div class="form-group <?php echo form_error('radius_nearby_distance') ? 'has-error' : ''; ?>">
								<label class="control-label col-md-3"><?php echo lang('setting_radius_nearby_distance')?> <span class="required" aria-required="true"> * </span></label>
								<div class="col-md-9">
                                    <?php echo form_input('radius_nearby_distance',set_value('radius_nearby_distance',isset($setting['radius_nearby_distance']) ? $setting['radius_nearby_distance'] : ''), ' class="form-control"')?>
                                	<?php echo form_error('radius_nearby_distance')?>
                                </div>
							</div>
							<div class="form-group last <?php echo form_error('listing_distance') ? 'has-error' : ''; ?>">
								<label class="control-label col-md-3"><?php echo lang('setting_listing_distance')?> <span class="required" aria-required="true"> * </span></label>
								<div class="col-md-9">
                                    <?php echo form_input('listing_distance',set_value('listing_distance',isset($setting['listing_distance']) ? $setting['listing_distance'] : ''), ' class="form-control"')?>
                                	<?php echo form_error('listing_distance')?>
                                </div>
							</div>
						</div>
						<div class="form-actions">
							<div class="row">
								<div class="col-md-offset-3 col-md-9">
									<button type="submit" class="btn blue">
										<i class="fa fa-check"></i> <?php echo lang('setting_button_save')?></button>
								</div>
							</div>
						</div>
                        <?php echo form_close()?>
                        <!-- END FORM-->
					</div>
				</div>
                            </div>
                        </div>
				
			</div>
                    </div>
		</div>
		<!-- END PAGE CONTENT-->
	</div>
</div>