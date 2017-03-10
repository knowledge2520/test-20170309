<?php
$id = isset($record->id) && !empty($record) ? $record->id : 0;
$hiddens = array('id' => $id);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('edit_group_heading'); ?>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang() . '/users/') ?>"><?php echo lang('bc_users') ?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_permissions') ?>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_permission_edit') ?></li>
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
                            <i class="icon-user font-blue-hoki"></i>
                            <span class="caption-subject font-blue-hoki bold uppercase"><?php echo lang('edit_group_subheading'); ?></span>							
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->						
                        <?php echo form_open_multipart($this->uri->uri_string(), 'method="POST" class="form-horizontal form-bordered form-label-stripped"') ?>
                        <div class="form-body">								
                            

                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_dashboard') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="home" <?php echo (isset($records['home']) && !empty($records['home'])) ? 'checked' : ''?>> <?php echo lang('permission_list'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_business') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-index" <?php echo (isset($records['business.index']) && !empty($records['business.index'])) ? 'checked' : ''?>> <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-create" <?php echo (isset($records['business.create']) && !empty($records['business.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-edit" <?php echo (isset($records['business.edit']) && !empty($records['business.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-delete" <?php echo (isset($records['business.delete']) && !empty($records['business.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-approve_business-index" <?php echo (isset($records['business.approve_business.index']) && !empty($records['business.approve_business.index'])) ? 'checked' : ''?> > <?php echo lang('permission_approve'); ?> </label>
                                    </div>
                                </div>
                            </div>
                           		
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_business_category') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-categories-index" <?php echo (isset($records['business.categories.index']) && !empty($records['business.categories.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-categories-create" <?php echo (isset($records['business.categories.create']) && !empty($records['business.categories.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-categories-edit" <?php echo (isset($records['business.categories.edit']) && !empty($records['business.categories.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-categories-delete" <?php echo (isset($records['business.categories.delete']) && !empty($records['business.categories.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_business_review') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-reviews-index" <?php echo (isset($records['business.reviews.index']) && !empty($records['business.reviews.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-reviews-create" <?php echo (isset($records['business.reviews.create']) && !empty($records['business.reviews.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-reviews-edit" <?php echo (isset($records['business.reviews.edit']) && !empty($records['business.reviews.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-reviews-delete" <?php echo (isset($records['business.review.delete']) && !empty($records['business.reviews.index'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_business_tip') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-tips-index" <?php echo (isset($records['business.tips.index']) && !empty($records['business.tips.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-tips-create" <?php echo (isset($records['business.tips.create']) && !empty($records['business.tips.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-tips-edit" <?php echo (isset($records['business.tips.edit']) && !empty($records['business.tips.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-tips-delete" <?php echo (isset($records['business.tips.delete']) && !empty($records['business.tips.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_business_checkin') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-checkins-index" <?php echo (isset($records['business.checkins.index']) && !empty($records['business.checkins.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-checkins-create" <?php echo (isset($records['business.checkins.create']) && !empty($records['business.checkins.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-checkins-edit" <?php echo (isset($records['business.checkins.edit']) && !empty($records['business.checkins.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="business-checkins-delete" <?php echo (isset($records['business.checkins.delete']) && !empty($records['business.checkins.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_pet') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pets-index" <?php echo (isset($records['pets.index']) && !empty($records['pets.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pets-create" <?php echo (isset($records['pets.create']) && !empty($records['pets.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pets-edit" <?php echo (isset($records['pets.edit']) && !empty($records['pets.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pets-delete" <?php echo (isset($records['pets.delete']) && !empty($records['pets.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_pet_type') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pets-pet_types-index" <?php echo (isset($records['pets.pet_types.index']) && !empty($records['pets.pet_types.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pets-pet_types-create" <?php echo (isset($records['pets.pet_types.create']) && !empty($records['pets.pet_types.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pets-pet_types-edit" <?php echo (isset($records['pets.pet_types.edit']) && !empty($records['pets.pet_types.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pets-pet_types-delete" <?php echo (isset($records['pets.pet_types.delete']) && !empty($records['pets.pet_types.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_pet_talk') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-index" <?php echo (isset($records['pet_talk.index']) && !empty($records['pet_talk.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-create" <?php echo (isset($records['pet_talk.create']) && !empty($records['pet_talk.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-edit" <?php echo (isset($records['pet_talk.edit']) && !empty($records['pet_talk.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-delete" <?php echo (isset($records['pet_talk.delete']) && !empty($records['pet_talk.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_pet_talk_category') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-categories-index" <?php echo (isset($records['pet_talk.categories.index']) && !empty($records['pet_talk.categories.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-categories-create" <?php echo (isset($records['pet_talk.categories.create']) && !empty($records['pet_talk.categories.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-categories-edit" <?php echo (isset($records['pet_talk.categories.edit']) && !empty($records['pet_talk.categories.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-categories-delete" <?php echo (isset($records['pet_talk.categories.delete']) && !empty($records['pet_talk.categories.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_pet_talk_comment') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-comments-index" <?php echo (isset($records['pet_talk.comments.index']) && !empty($records['pet_talk.comments.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-comments-edit" <?php echo (isset($records['pet_talk.comments.edit']) && !empty($records['pet_talk.comments.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="pet_talk-comments-delete" <?php echo (isset($records['pet_talk.comments.delete']) && !empty($records['pet_talk.comments.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>                                      
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_product') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-index" <?php echo (isset($records['products.index']) && !empty($records['products.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-create" <?php echo (isset($records['products.create']) && !empty($records['products.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-edit" <?php echo (isset($records['products.edit']) && !empty($records['products.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-delete" <?php echo (isset($records['products.delete']) && !empty($records['products.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_product_category') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-categories-index" <?php echo (isset($records['products.categories.index']) && !empty($records['products.categories.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-categories-create" <?php echo (isset($records['products.categories.create']) && !empty($records['products.categories.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-categories-edit" <?php echo (isset($records['products.categories.edit']) && !empty($records['products.categories.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-categories-delete" <?php echo (isset($records['products.categories.delete']) && !empty($records['products.categories.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_product_transaction') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-transactions-index" <?php echo (isset($records['products.transactions.index']) && !empty($records['products.transactions.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-transactions-delete" <?php echo (isset($records['products.transactions.delete']) && !empty($records['products.transactions.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_product_comment') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-comments-index" <?php echo (isset($records['products.comments.index']) && !empty($records['products.comments.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="products-comments-delete" <?php echo (isset($records['products.comments.delete']) && !empty($records['products.comments.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_member') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="members-index" <?php echo (isset($records['members.index']) && !empty($records['members.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="members-create" <?php echo (isset($records['members.create']) && !empty($records['members.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="members-edit" <?php echo (isset($records['members.edit']) && !empty($records['members.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="members-delete" <?php echo (isset($records['members.delete']) && !empty($records['members.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_user') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="users-index" <?php echo (isset($records['users.index']) && !empty($records['users.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="users-create" <?php echo (isset($records['users.create']) && !empty($records['users.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="users-edit" <?php echo (isset($records['users.edit']) && !empty($records['users.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="users-delete" <?php echo (isset($records['users.delete']) && !empty($records['users.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_user_group') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="users-groups-index" <?php echo (isset($records['users.groups.index']) && !empty($records['users.groups.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="users-groups-create" <?php echo (isset($records['users.groups.create']) && !empty($records['users.groups.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="users-groups-edit" <?php echo (isset($records['users.groups.edit']) && !empty($records['users.groups.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="users-groups-delete" <?php echo (isset($records['users.groups.delete']) && !empty($records['users.groups.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo lang('permission_banner') ?></label>
                                <div class="col-md-9">										
                                    <div class="checkbox-list">
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="banners-index" <?php echo (isset($records['banners.index']) && !empty($records['banners.index'])) ? 'checked' : ''?> > <?php echo lang('permission_list'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="banners-create" <?php echo (isset($records['banners.create']) && !empty($records['banners.create'])) ? 'checked' : ''?> > <?php echo lang('permission_add'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="banners-edit" <?php echo (isset($records['banners.edit']) && !empty($records['banners.edit'])) ? 'checked' : ''?> > <?php echo lang('permission_edit'); ?> </label>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="banners-delete" <?php echo (isset($records['banners.delete']) && !empty($records['banners.delete'])) ? 'checked' : ''?> > <?php echo lang('permission_delete'); ?> </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-9">
                                    <button type="submit" class="btn blue"><i class="fa fa-check"></i> <?php echo lang('permission_btn_save') ?></button>
                                    <a href="<?php echo site_url('users/groups');?>"><button class="btn default"><?php echo lang('permission_btn_cancel') ?></button></a>
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