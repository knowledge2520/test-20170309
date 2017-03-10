<?php
$pagination                     = isset($paging) ? $paging->create_links() : false;
$pagination_total_rows          = isset($total) ? $total : 0;
$pagination_total_rows_ppage    = (isset($paging) && $paging->cur_page >= 1 && isset($count) && $paging->per_page == $count) ? $paging->per_page * $paging->cur_page : $pagination_total_rows;
$pagination_start_item          =  $total == 0 ? 0 : (isset($paging) && $paging->cur_page > 1 ? ($paging->per_page * ($paging->cur_page - 1)) : 1);

$current_url                    = site_url().$this->uri->uri_string().'.html';
$query_url                      = $_SERVER['QUERY_STRING'];
?>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('category_header') ?>
            <small><?php echo lang('category_listing') ?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang().'/pet_talk/categories')?>"><?php echo lang('bc_categories')?></a>
                </li>
            </ul>
            <?php if(isset($permissions[$module.'.create']) || $is_admin):?>
            <div class="page-toolbar">
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown">
                        <?php echo lang('category_action')?> <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pet_talk/categories/create')?>"><?php echo lang('category_add')?></a>
                        </li>
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
                                            <i class="fa fa-globe"></i> <?php echo lang('lbl_result_records')?>
                                        </div>
                                        <div class="tools">
                                        </div>
                                    </div>
                                    <div class="portlet-body">
                                        <div class="row">
                                            <?php echo form_open(site_url($this->lang->lang().'/pet_talk/categories/index'),'method="GET" name="form_searchResult" id="form_searchResult"')?>
                                            <div class="col-md-4 col-sm-4 col-xs-6">
                                                <div class="dataTables_length" id="dataTables_length">
                                                    <label>
                                                        <?php
                                                        $data_tableLength = SiteHelper::recordPerPage();
                                                        ?>
                                                        <?php echo form_dropdown('dataTables_length', $data_tableLength, isset($item_tableLength) ? $item_tableLength : ADMIN_ITEMS_PERPAGE, 'class="form-control input-xsmall input-inline" id="dataTables_length" ') ?>
                                                        <?php echo lang('lbl_records') ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-4 col-xs-6">
                                                <div id="sample_1_filter" class="dataTables_filter">
                                                    <label><?php echo lang('lbl_my_search') ?>:                                                                         
                                                        <?php echo form_input('txt_search', isset($txt_search_value) ? $txt_search_value : '', 'type="search" class="form-control input-small input-inline" placeholder=""') ?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class="text-right"> <?php echo ($pagination) ? $pagination : '' ?></div>
                                            </div>
                                            <?php echo form_close()?>
                                        </div>
                                        <?php echo form_open(site_url($this->lang->lang().'/pet_talk/categories/index'),'method="POST" name="form_listRecords" id="form_listRecords"')?>
                                        <table class="table table-striped table-bordered table-hover" id="sample_1">
                                            <thead>
                                            <tr>
                                                <?php if(isset($permissions[$module.'.delete']) || $is_admin):?>
                                                    <th class="column-check" data-sortable="false" data-id="id"><?php echo form_checkbox(false,'',false,'class="check-all"')?></th>
                                                <?php endif;?>
                                                <th><?php echo lang('lbl_no.')?></th>
                                                <th>
                                                    <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'id', 'sort' => $order_field == "id" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('category_id') . ' ';
                                                        if($order_field == 'id'){
                                                            if($sort == 'DESC'){
                                                                echo '<span class="fa fa-sort-desc span-header-table"></span>';
                                                            }else{
                                                                echo '<span class="fa fa-sort-asc span-header-table"></span>';
                                                            }
                                                        }else{
                                                            echo '<span class="fa fa-sort span-header-table"></span>';
                                                        }
                                                    ?>          
                                                    </a>
                                                </th>
                                                <th>
                                                    <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'name', 'sort' => $order_field == "name" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('category_name') . ' ';
                                                        if($order_field == 'name'){
                                                            if($sort == 'DESC'){
                                                                echo '<span class="fa fa-sort-desc span-header-table"></span>';
                                                            }else{
                                                                echo '<span class="fa fa-sort-asc span-header-table"></span>';
                                                            }
                                                        }else{
                                                            echo '<span class="fa fa-sort span-header-table"></span>';
                                                        }
                                                    ?>          
                                                    </a>
                                                </th>
                                                <th>
                                                    <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'description', 'sort' => $order_field == "description" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('category_description') . ' ';
                                                        if($order_field == 'description'){
                                                            if($sort == 'DESC'){
                                                                echo '<span class="fa fa-sort-desc span-header-table"></span>';
                                                            }else{
                                                                echo '<span class="fa fa-sort-asc span-header-table"></span>';
                                                            }
                                                        }else{
                                                            echo '<span class="fa fa-sort span-header-table"></span>';
                                                        }
                                                    ?>          
                                                    </a>
                                                </th>
                                                <th>
                                                    <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'sort', 'sort' => $order_field == "sort" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('category_sort') . ' ';
                                                        if($order_field == 'sort'){
                                                            if($sort == 'DESC'){
                                                                echo '<span class="fa fa-sort-desc span-header-table"></span>';
                                                            }else{
                                                                echo '<span class="fa fa-sort-asc span-header-table"></span>';
                                                            }
                                                        }else{
                                                            echo '<span class="fa fa-sort span-header-table"></span>';
                                                        }
                                                    ?>          
                                                    </a>
                                                </th>
                                                <th><?php echo lang('category_popular')?></th>
                                                <th><?php echo lang('category_status')?></th>
                                                <?php if(isset($permissions[$module.'.edit']) || $is_admin):?>
                                                    <th></th>
                                                <?php endif?>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php if(!empty($records)): $no = $offset +1;?>
                                                <?php foreach($records as $record):?>
                                                    <tr>
                                                        <?php if(isset($permissions[$module.'.delete']) || $is_admin):?>
                                                            <td class="column-check"><?php echo form_checkbox('checked[]',$record->id,false,'class="checkbox-delete"')?></td>
                                                        <?php endif;?>
                                                        <td><?php echo $no++?></td>
                                                        <td><?php echo $record->id?></td>
                                                        <td><?php echo $record->name ? $record->name : '<i>N/A</i>' ?></td>
                                                        <td><?php echo ellipsize($record->description, 30) ?></td>
                                                        <td><?php echo $record->sort?></td>
                                                        <td><?php echo ($record->is_popular == 0) ? lang('category_not_popular') : lang('category_is_popular')?></td>
                                                        <td>  
                                                            <div class="status" onchange='set_status("<?php echo $record->id;?>", "<?php echo $_SERVER['REQUEST_URI'];?>")'>
                                                                <label>
                                                                    <?php
                                                                        $status = array(
                                                                            1 => 'Active',
                                                                            0 => 'Deactivated'
                                                                        );
                                                                    ?>
                                                                    <?php echo form_dropdown('status', $status, $record->status,'class="form-control input-inline" id="status_'.$record->id.'" ')?>
                                                                    
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <?php if(isset($permissions[$module.'.edit']) || $is_admin):?>
                                                            <td>                                                            
                                                                <a href="<?php echo site_url($this->lang->lang() . '/pet_talk/categories/edit/' . $record->id) ;?>"
                                                                    class="btn default btn-xs green-meadow"> <i class="fa fa-edit"></i> <?php echo lang('btn_edit'); ?>
                                                                </a>
                                                            </td>
                                                        <?php endif?>
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
                                        <div class="modal fade" id="dialog" tabindex="-1" role="basic" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                          <span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <h4 class="modal-title">Successfully!</h4>
                                                    </div>
                                                    <div class="modal-footer" id="modal-footer">
                                                        <button type="button" class="btn default" id="modal-btn-close" data-dismiss="modal">Close</button>
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