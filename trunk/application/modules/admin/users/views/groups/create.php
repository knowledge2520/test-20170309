<?php 
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id'=>$id);
?>
<div class="page-content-wrapper">
	<div class="page-content">
		<!-- BEGIN PAGE HEADER-->
		<h3 class="page-title">
			<?php echo lang('create_group_heading');?>
			</h3>
		<div class="page-bar">
			<ul class="page-breadcrumb">
				<li><i class="fa fa-home"></i> <a
					href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
					<i class="fa fa-angle-right"></i></li>
				<li><a href="<?php echo site_url($this->lang->lang().'/users/')?>"><?php echo lang('bc_users')?></a>
					<i class="fa fa-angle-right"></i></li>
				<li><a href="<?php echo site_url($this->lang->lang().'/users/groups')?>"><?php echo lang('bc_groups')?></a>
					<i class="fa fa-angle-right"></i></li>
				<li><?php echo lang('bc_groups_add')?></li>
			</ul>
			<div class="page-toolbar">
				<div class="btn-group pull-right">
					<button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown">
					<?php echo lang('member_actions')?> <i class="fa fa-angle-down"></i>
					</button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>
							<a href="<?php echo site_url($this->lang->lang().'/users/index')?>"><?php echo lang('users')?></a>
						</li>
						<li>
							<a href="<?php echo site_url($this->lang->lang().'/users/groups/')?>"><?php echo lang('user_groups')?></a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<!-- END PAGE HEADER-->
		<!-- BEGIN PAGE CONTENT-->
		<div class="row">
			<div class="col-md-12">
				<div class="portlet light bordered form-fit">
					<div class="portlet-title">
						<div class="caption">
							<i class="icon-user font-blue-hoki"></i>
							<span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('create_group_subheading');?></span>							
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
								
								<div class="form-group <?php echo form_error('group_name') ? 'has-error' : ''; ?>">
									<label class="control-label col-md-3"><?php echo lang('groups_name')?> <span class="required" aria-required="true">
										* </span></label>
									<div class="col-md-9">										
										<?php echo form_input($group_name);?>
										<?php echo form_error('group_name')?>
									</div>
								</div>
								<div class="form-group <?php echo form_error('description') ? 'has-error' : ''; ?>">
									<label class="control-label col-md-3"><?php echo lang('groups_desc')?></label>
									<div class="col-md-9">
										<?php echo form_input($description);?>
										<?php echo form_error('description')?>
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