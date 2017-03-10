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
            <?php echo lang('pet_talk_header') ?>
            <small><?php echo lang('pet_talk_listing') ?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang().'/pet_talk/')?>"><?php echo lang('bc_pet_talk')?></a>
                </li>
            </ul>
            <?php if(isset($permissions[$module.'.create']) || isset($permissions['pet_talk.categories.create']) || $is_admin):?>
            <div class="page-toolbar">
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown">
                        <?php echo lang('pet_talk_actions')?> <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <?php if(isset($permissions[$module.'.create']) || $is_admin):?>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/pet_talk/create')?>"><?php echo lang('pet_talk_add')?></a>
                        </li>
                        <?php endif?>
                    </ul>
                </div>
            </div>
            <?php endif?>
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
                                            <?php echo form_open(site_url($this->lang->lang().'/pet_talk/index'),'method="GET" name="form_searchResult" id="form_searchResult"')?>
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
                                        <?php echo form_open($this->uri->uri_string(),'method="POST" name="form_listRecords" id="form_listRecords"')?>
                                        <table class="table table-striped table-bordered table-hover" id="sample_1">
                                            <thead>
                                            <tr>
                                                <?php if(isset($permissions[$module.'.delete']) || $is_admin):?>
                                                    <th class="column-check" data-sortable="false" data-id="id"><?php echo form_checkbox(false,'',false,'class="check-all"')?></th>
                                                <?php endif;?>
                                                <th><?php echo lang('lbl_no.')?></th>
                                                <th>
                                                    <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'title', 'sort' => $order_field == "title" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('pet_talk_title') . ' ';
                                                        if($order_field == 'title'){
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
                                                    <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'category_name', 'sort' => $order_field == "category_name" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('pet_talk_category') . ' ';
                                                        if($order_field == 'category_name'){
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
                                                    <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'user_name', 'sort' => $order_field == "user_name" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('pet_talk_author') . ' ';
                                                        if($order_field == 'user_name'){
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
                                                    <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'created_date', 'sort' => $order_field == "created_date" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('pet_talk_created_date') . ' ';
                                                        if($order_field == 'created_date'){
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
                                                <th></th>
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
                                                        <td><?php echo $record->title ? ellipsize($record->title, 100, 1) : ellipsize($record->content, 100, 1)?></td>
                                                        <td><?php if($record->category_name):?>
                                                                <?php echo $record->category_name ? $record->category_name : '<i>N/A</i>' ?>
                                                            <?php else:?>
                                                                No Category
                                                            <?php endif;?>
                                                        </td>
                                                        <td><?php echo $record->user_name ? $record->user_name : 'N/A';?></td>
<!--                                                        <td>  
                                                            <div class="status" onchange='set_status("<?php echo $record->id;?>", "<?php echo $_SERVER['REQUEST_URI'];?>")'>
                                                                <label>
                                                                    <?php
                                                                        // $status = array(
                                                                        //     1 => 'Active',
                                                                        //     0 => 'Deactivated'
                                                                        // );
                                                                    ?>
                                                                    <?php //echo form_dropdown('status', $status, $record->status,'class="form-control input-inline" id="status_'.$record->id.'" ')?>
                                                                    
                                                                </label>
                                                            </div>
                                                        </td> -->
                                                        <td><?php echo get_time_date($record->created_date)?></td>
                                                        <td>
                                                            <a class="btn default btn-xs purple" href="<?php echo site_url('pet_talk/comments/add/'. $record->id);?>">
                                                                <i class="fa fa-edit"></i>
                                                                Add Comment
                                                            </a>
                                                            <?php if(isset($permissions[$module.'.edit']) || $is_admin || $record->individual):?>
                                                                <a href="<?php echo site_url($this->lang->lang() . '/pet_talk/edit/' . $record->id ) ;?>"
                                                                    class="btn default btn-xs green-meadow"> <i class="fa fa-edit"></i> <?php echo lang('btn_edit'); ?>
                                                                </a>
                                                            <?php endif?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach;?>
                                            <?php endif;?>
                                            </tbody>
                                        </table>
                                        <div class="row">
                                            <div class="col-md-7 col-sm-12">
                                                <?php echo lang('showing_result_items_pagination',$pagination_start_item , $pagination_total_rows_ppage,$pagination_total_rows)?> <br/>
                                                <?php if(isset($permissions[$module.'.delete']) || $is_admin):?>
                                                     <a href="#" data-href="" class="delete-dialog" data-toggle="modal" data-target="#confirm"><button  class="btn btn-danger">Delete</button></a> 
                                                    <!-- <?php //echo form_submit('btn_delete',lang('btn_delete'), 'class="btn btn-danger"')?> -->
                                                <?php endif;?>
                                            </div>
                                            <div class="col-md-5 col-sm-12 text-right"><?php echo ($pagination) ? $pagination : ''?></div>
                                        </div>

                                     
                                        <div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-body">
                                                        Are you sure?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn default" data-dismiss="modal">Close</button>
                                                        <input type="submit" class="btn btn-danger" name="btn_delete" id="delete" value="Delete">
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
