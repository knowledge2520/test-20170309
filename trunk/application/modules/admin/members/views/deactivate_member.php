<div class="page-content-wrapper">
	<div class="page-content">
		<!-- BEGIN PAGE HEADER-->
		<h3 class="page-title">
			<?php echo lang('deactivate_heading');?>
			</h3>
		<div class="page-bar">
			<ul class="page-breadcrumb">
				<li><i class="fa fa-home"></i> <a
					href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
					<i class="fa fa-angle-right"></i></li>
				<li><a href="<?php echo site_url($this->lang->lang().'/members/')?>"><?php echo lang('bc_member')?></a>
					<i class="fa fa-angle-right"></i></li>
				<li><?php echo lang('bc_users_deactive') .' #' . $user->id?></li>
			</ul>
			<div class="page-toolbar">
				<div class="btn-group pull-right">
					<button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown">
					<?php echo lang('member_actions')?> <i class="fa fa-angle-down"></i>
					</button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>
							<a href="<?php echo site_url($this->lang->lang().'/members/index')?>"><?php echo lang('members')?></a>
						</li>
						<li>
							<a href="<?php echo site_url($this->lang->lang().'/users/groups/')?>"><?php echo lang('member_groups')?></a>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<!-- END PAGE HEADER-->
		<!-- BEGIN PAGE CONTENT-->
		<div class="row">
			<div class="col-md-12">				
				<p><?php echo sprintf(lang('deactivate_subheading'), $user->username);?></p>
				
				<?php echo form_open("members/deactivate/".$user->id);?>
				
				  <p>
				  	<?php echo lang('deactivate_confirm_y_label', 'confirm');?>
				    <input type="radio" name="confirm" value="yes" checked="checked" />
				    <?php echo lang('deactivate_confirm_n_label', 'confirm');?>
				    <input type="radio" name="confirm" value="no" />
				  </p>
				
				  <?php echo form_hidden($csrf); ?>
				  <?php echo form_hidden(array('id'=>$user->id)); ?>
				
				  <p><?php echo form_submit('submit', lang('deactivate_submit_btn'),'class="btn btn-primary"');?></p>
				
				<?php echo form_close();?>
			</div>
		</div>
	</div>
</div>