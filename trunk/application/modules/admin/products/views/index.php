<?php
$pagination                     = isset($paging) ? $paging->create_links() : false;
$pagination_total_rows          = isset($total) ? $total : 0;
$pagination_total_rows_ppage    = (isset($paging) && $paging->cur_page >= 1 && isset($count) && $paging->per_page == $count) ? $paging->per_page * $paging->cur_page : $pagination_total_rows;
$pagination_start_item          =  $total == 0 ? 0 : (isset($paging) && $paging->cur_page > 1 ? ($paging->per_page * ($paging->cur_page - 1)) : 1);
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('product_header')?>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang().'/products/')?>"><?php echo lang('bc_product')?></a>
                </li>
            </ul>
            <?php if(isset($permissions[$module.'.create']) || isset($permissions['products.categories.create']) || $is_admin):?>
            <div class="page-toolbar">
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                        <?php echo lang('product_action')?> <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <?php if(isset($permissions[$module.'.create']) || $is_admin):?>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/products/create')?>"><?php echo lang('product_create')?></a>
                        </li>
                        <?php endif;?>
                        <?php if(isset($permissions['products.categories.create']) || $is_admin):?>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/products/categories/index')?>"><?php echo lang('product_category')?></a>
                        </li>
                        <?php endif;?>
                    </ul>
                </div>
            </div>
            <?php endif;?>
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
                    <?php endforeach;
                } ?>


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

            </div>
            <div class="col-md-12">
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
                                            <i class="fa fa-globe"></i> <?php echo lang('product_result_records')?>
                                        </div>
                                        <div class="tools">
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="row">
                                            <?php echo form_open(site_url($this->lang->lang().'/products/index'),'method="GET" name="form_searchResult" id="form_searchResult"')?>
                                            <div class="col-md-4 col-sm-12">
                                                <div class="dataTables_length" id="dataTables_length">
                                                    <label>
                                                        <?php
                                                        $data_tableLength = SiteHelper::recordPerPage();
                                                        ?>
                                                        <?php echo form_dropdown('dataTables_length',$data_tableLength , isset($item_tableLength) ? $item_tableLength : ADMIN_ITEMS_PERPAGE,'class="form-control input-xsmall input-inline" id="dataTables_length" ')?>
                                                        <?php echo lang('product_records')?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-12">
                                                <div id="sample_1_filter" class="dataTables_filter">
                                                    <label><?php echo lang('product_my_search')?>:
                                                        <?php echo form_input('txt_search',isset($txt_search_value)? $txt_search_value : '' , 'type="search" class="form-control input-small input-inline" placeholder=""')?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-12">
                                                <div class="text-right"> <?php echo ($pagination) ? $pagination : ''?></div>
                                            </div>
                                            <?php echo form_close()?>
                                        </div>

                                        <?php echo form_open(site_url($this->lang->lang().'/products/index'),'method="POST" name="form_listRecords" id="form_listRecords"')?>
                                        <table class="table table-striped table-bordered table-hover" id="sample_1">
                                            <thead>
                                            <tr>
                                                <?php if(isset($permissions[$module.'.delete']) || $is_admin):?>
                                                    <th class="column-check" data-sortable="false" data-id="id"><?php echo form_checkbox(false,'',false,'class="check-all"')?></th>
                                                <?php endif;?>
                                                <th><?php echo lang('product_id')?></th>
                                                <th><?php echo lang('product_name')?></th>
                                                <th><?php echo lang('product_category')?></th>
                                                <th><?php echo lang('product_price')?></th>
                                                <th><?php echo lang('product_quantity')?></th>
                                                <th><?php echo lang('product_status')?></th>
                                                <th><?php echo lang('product_created_date')?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php if(!empty($records)):?>
                                                <?php foreach($records as $record):?>
                                                    <tr>
                                                        <?php if(isset($permissions[$module.'.delete']) || $is_admin):?>
                                                            <td class="column-check"><?php echo form_checkbox('checked[]',$record->id,false,'class="checkbox-delete"')?></td>
                                                        <?php endif;?>
                                                        <td><?php echo $record->id?></td>
                                                        <td><?php echo anchor(site_url($this->lang->lang().'/products/edit/'.$record->id), (!empty($record->name) ? $record->name : '<i>none</i>'))?></td>
                                                        <td>
                                                            <?php if(isset($record->category) && !empty($record->category)):?>
                                                                <a href="<?php base_url();?>products/categories/edit/<?php echo $record->category->id;?>"><?php echo $record->category->name;?></a>
                                                            <?php endif?>
                                                        </td>
                                                        <td><?php echo $record->price?></td>
                                                        <td><?php echo $record->stock?></td>
                                                        <td><?php echo ($record->status==1) ? 'Active' : 'Deactive'?></td>
                                                        <td><?php echo get_time_date($record->created_date)?></td>
                                                    </tr>
                                                <?php endforeach;?>
                                            <?php endif;?>
                                            </tbody>
                                        </table>
                                        <div class="row">
                                            <div class="col-md-7 col-sm-12">
                                                <?php echo lang('showing_result_items_pagination',$pagination_start_item , $pagination_total_rows_ppage,$pagination_total_rows)?> <br/>
                                                <?php if(isset($permissions[$module.'.delete']) || $is_admin):?>
                                                    <?php echo form_submit('btn_delete',lang('btn_delete'), 'class="btn btn-danger"')?>
                                                <?php endif;?>
                                            </div>
                                            <div class="col-md-5 col-sm-12 text-right"><?php echo ($pagination) ? $pagination : ''?></div>
                                        </div>

                                        <div class="modal fade" id="confirm" tabindex="-1" role="basic" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-body">
                                                        Are you sure?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn default" data-dismiss="modal">Close</button>
                                                        <button type="button" class="btn blue" id="delete">Delete</button>
                                                    </div>
                                                </div>
                                                <!-- /.modal-content -->
                                            </div>
                                            <!-- /.modal-dialog -->
                                        </div>
                                        <?php echo form_close()?>
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
</div>