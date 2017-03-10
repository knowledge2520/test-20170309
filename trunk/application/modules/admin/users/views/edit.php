<?php
$id = isset($user->id) && !empty($user) ? $user->id : 0;
$hiddens = array('id' => $id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('user_management_header'); ?>
            <small><?php echo lang('user_edit_header'); ?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang() . '/users/') ?>"><?php echo lang('bc_users') ?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_users_edit') . ' #' . $id ?></li>
            </ul>
            <div class="page-toolbar">
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown">
                        <?php echo lang('member_actions') ?> <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>
                            <a href="<?php echo site_url($this->lang->lang() . '/users/index') ?>"><?php echo lang('users') ?></a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang() . '/users/groups/') ?>"><?php echo lang('user_groups') ?></a>
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
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('create_user_subheading'); ?></span>							
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->						
                        <?php echo form_open_multipart($this->uri->uri_string(), 'method="POST" class="form-horizontal form-bordered form-label-stripped"', $hiddens) ?>
                        <?php echo form_hidden($csrf); ?>
                        <div class="form-body">	
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
                            <?php
                            //error message
                            $errors = $this->messages->get("error");
                            if (!empty($errors)) {
                                foreach ($errors as $error):
                                    ?>
                                    <div class="alert alert-danger display-hide" style="display: block;">
                                        <button data-close="alert" class="close"></button>
                                        <?php echo $error; ?>
                                    </div>
                                <?php endforeach;
                            } ?>

                            <div class="form-group <?php echo form_error('first_name') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('user_first_name') ?> <span class="required" aria-required="true">
                                        * </span></label>
                                <div class="col-md-9">										
                                    <?php echo form_input($first_name); ?>
                                    <?php echo form_error('first_name') ?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('last_name') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('user_last_name') ?> <span class="required" aria-required="true">
                                        * </span></label>
                                <div class="col-md-9">
                                    <?php echo form_input($last_name); ?>
                                    <?php echo form_error('last_name') ?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('company') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('user_company') ?></label>
                                <div class="col-md-9">
                                    <?php echo form_input($company); ?>
                                    <?php echo form_error('company') ?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('email') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('user_email') ?> <span class="required" aria-required="true">
                                        * </span></label>
                                <div class="col-md-9">
                                    <?php echo form_input($email); ?>
                                    <?php echo form_error('email') ?>
                                </div>
                            </div>																
                            <div class="form-group <?php echo form_error('phone') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('user_phone') ?></label>
                                <div class="col-md-9">										
                                    <?php echo form_input($phone); ?>
                                    <?php echo form_error('phone') ?>
                                </div>
                            </div>
                            <div class="form-group <?php echo form_error('password') ? 'has-error' : ''; ?>">
                                <label class="control-label col-md-3"><?php echo lang('user_password') ?></label>
                                <div class="col-md-9">										
                                    <?php echo form_input($password); ?>
                                    <?php echo form_error('password') ?>
                                </div>
                            </div>
                            <?php if ($this->ion_auth->is_admin()): ?>
                                <div class="form-group <?php echo form_error('groups') ? 'has-error' : ''; ?>">
                                    <label class="col-md-3 control-label"><?php echo lang('user_groups')?> <span class="required" aria-required="true">
                                        * </span></label>
                                    <div class="col-md-4">
                                        <div class="radio-list">
                                        <?php if(!empty($groups)):?>
                                            <?php foreach($groups as $group):
                                                $gID = $group['id'];
                                                $checked = null;
                                                $item = null;
                                                if (!empty($currentGroups)) {
                                                    foreach ($currentGroups as $grp) {
                                                        if ($gID == $grp->id) {
                                                            $checked = ' checked="checked"';
                                                            break;
                                                        }
                                                    }
                                                }
                                            ?>
                                                <label class="mt-radio mt-radio-outline">
                                                    <input type="radio" name="groups" id="<?php echo $group['id']?>" value="<?php echo $group['id']?>" <?php echo $checked;?>> <?php echo $group['name']?>
                                                    <span></span>
                                                </label>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                        </div>
                                        <?php echo form_error('groups') ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Access Countries </label>
                                    <div class="col-md-9">
                                        <div class="col-xs-4">
                                        <?php if(!empty($countries)):?>
                                            <select id="undo_redo" name="from[]" size="13" multiple="multiple" class="form-control">
                                            <?php 
                                            $selectedOption = [];
                                            foreach($countries as $item):?>
                                                <?php 
                                                    $flag = 0;
                                                    foreach($userCountries as $value){
                                                        if(!empty($value['country_id']==$item->id)){
                                                            $flag=1;
                                                            break;
                                                        }
                                                    }
                                                    if( $flag == 1){
                                                        $itemSelected['id'] =  $item->id;
                                                        $itemSelected['countryName'] =  $item->countryName;
                                                        $selectedOption[] = $itemSelected;
                                                    }else{
                                                        echo '<option value="'.$item->id.'">'.$item->countryName.'</option>';
                                                    }    
                                                ?>
                                                <?php endforeach;?>
                                            </select>   
                                        <?php endif;?>
                                        </div>
                                        <div class="col-xs-2" style="margin-left:20px">
                                            <button type="button" id="undo_redo_undo" class="btn btn-primary btn-block">undo</button>
                                            <button type="button" id="undo_redo_rightAll" class="btn btn-default btn-block"><i class="glyphicon glyphicon-forward"></i></button>
                                            <button type="button" id="undo_redo_rightSelected" class="btn btn-default btn-block"><i class="glyphicon glyphicon-chevron-right"></i></button>
                                            <button type="button" id="undo_redo_leftSelected" class="btn btn-default btn-block"><i class="glyphicon glyphicon-chevron-left"></i></button>
                                            <button type="button" id="undo_redo_leftAll" class="btn btn-default btn-block"><i class="glyphicon glyphicon-backward"></i></button>
                                            <button type="button" id="undo_redo_redo" class="btn btn-warning btn-block">redo</button>
                                        </div>
                
                                        <div class="col-xs-5">
                                            <select name="countries[]" id="undo_redo_to" size="12" class="form-control" multiple="multiple">
                                            <?php foreach($selectedOption as $item):?>
                                                <?php 
                                                    echo '<option value="'.$item['id'].'">'.$item['countryName'].'</option>';                                             
                                                ?>
                                            <?php endforeach;?> 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <button type="submit" class="btn blue"><i class="fa fa-check"></i> Submit</button>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close() ?>
                        <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>