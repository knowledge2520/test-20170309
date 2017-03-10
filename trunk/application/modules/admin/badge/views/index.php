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
        <?php echo lang('badge_management')?>
    </h3>
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li>
                <i class="fa fa-home"></i>
                <a href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                <i class="fa fa-angle-right"></i>
            </li>
            <li>
                <a href="<?php echo site_url($this->lang->lang().'/badge/index')?>">Badge ID Management</a>
                <i class="fa fa-angle-right"></i>
            </li>
            <li>
                Badge ID Creation Log
            </li>
        </ul>
        <?php if(isset($permissions[$module.'.create']) || $is_admin):?>
        <div class="page-toolbar">
            <div class="btn-group pull-right">
                <button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown">
                    <?php echo lang('badge_actions')?> <i class="fa fa-angle-down"></i>
                </button>
                <ul class="dropdown-menu pull-right" role="menu">
                    <li>
                        <a href="<?php echo site_url($this->lang->lang().'/badge/create')?>"><?php echo lang('badge_create')?></a>
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
            <?php endforeach;
            }?>
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
                                        <?php echo form_open(site_url($this->lang->lang().'/badge/index'),'method="GET" name="form_searchResult" id="form_searchResult"')?>
                                        <div class="col-md-3 col-sm-3 col-xs-3">
                                            <div class="dataTables_length" id="dataTables_length">
                                                <label>
                                                    <?php
                                                    $data_tableLength = SiteHelper::recordPerPage();
                                                    ?>
                                                    <?php echo form_dropdown('dataTables_length',$data_tableLength , isset($item_tableLength) ? $item_tableLength : ADMIN_ITEMS_PERPAGE,'class="form-control input-xsmall input-inline" id="dataTables_length" ')?>
                                                    <?php echo lang('badge_records')?>
                                                </label>
                                            </div>
                                        </div>  
                                        <div class="col-md-3 col-sm-3 col-xs-3">
                                                <select class="form-control" name="category_id">
                                                    <option value="">All Brands</option>\
                                                    <?php foreach($badge_category as $item):?>
                                                        <?php if($cate_id == $item->id):?>
                                                            <option value="<?php echo $item->id?>" selected> <?php echo $item->name?></option>
                                                        <?php else:?>
                                                            <option value="<?php echo $item->id?>" > <?php echo $item->name?></option>
                                                        <?php endif;?>    
                                                    <?php endforeach;?>
                                                </select>   
                                        </div>
                                        <div class="col-md-4 col-sm-4 col-xs-6">
                                            <div id="sample_1_filter" class="">
                                                <label><?php // echo lang('lbl_my_search')?>
                                                    <?php echo form_input('txt_search',isset($keyword)? $keyword : '' , 'type="search" class="form-control input-small input-inline" placeholder=""')?>
                                                </label>
                                                <input type="submit" value="Search" onclick="save(event)" class="btn green-meadow" >
                                        </div>
                                        </div>     
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div class="text-right"> <?php echo ($pagination) ? $pagination : ''?></div>
                                        </div>                                                                              
                                        <?php echo form_close()?>
                                    </div>
                                    <?php echo form_open(site_url($this->lang->lang().'/badge/index'),'method="POST" name="form_listRecords" id="form_listRecords"')?>
                                    <table class="table table-striped table-bordered table-hover" id="sample_1">
                                        <thead>
                                        <tr>
                                            <?php if(isset($permissions[$module.'.delete']) || $is_admin):?>
                                                <th class="column-check" data-sortable="false" data-id="id"><?php echo form_checkbox(false,'',false,'class="check-all"')?></th>
                                            <?php endif;?>
                                            <th>
                                                <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'id', 'sort' => $order_field == "id" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                <?php 
                                                    echo 'S/N ';
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
                                            <th><?php echo lang('badge_url')?></th>  
                                            <th><?php echo lang('badge_quantity')?></th>                                              
                                            <th>
                                                <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'created_date', 'sort' => $order_field == "created_date" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                <?php 
                                                    echo lang('badge_created_date') . ' ';
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
                                            <th><?php echo lang('badge_status')?></th>  
                                            <?php if(isset($permissions[$module.'.edit']) || $is_admin ):?>
                                                <th></th>  
                                            <?php endif?> 
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
                                                    <td>
                                                    <?php if(isset($record->url) && !empty($record->url)):?>
                                                    <a href="<?php echo site_url($this->lang->lang() . '/badge/download?url=' .urlencode($record->url)) ;?>"
                                                                class="btn default btn-xs red"> Download
                                                    </a>
                                                    <?php else:?>
                                                        <p></p>
                                                    <?php endif;?>    
                                                    </td>                                                    
                                                    <td><?php echo $record->quantity?></td>
                                                    <td><?php echo get_time_date($record->created_date)?></td>
                                                    <td>  
                                                      
                                                            <label>
                                                                <?php
                                                                    $status = array(
                                                                        1 => 'Live',
                                                                        0 => 'Pending'
                                                                    );

                                                                    echo $status[$record->status];
                                                                ?>
                                                            </label>
                                                       
                                                    </td>
                                                    <?php if(isset($permissions[$module.'.edit']) || $is_admin ):?>
                                                        <td>
                                                            <?php if($record->status == 0):?>
                                                            <a href="<?php echo site_url($this->lang->lang() . '/badge/edit/' . $record->id) ;?>"
                                                                class="btn default btn-xs green-meadow"> <i class="fa fa-edit"></i> <?php echo lang('btn_edit'); ?>
                                                            </a>
                                                            <?php endif;?>   

                                                            <?php if(intval($record->status) == 0):?>
                                                            <a href="<?php echo site_url($this->lang->lang() . '/badge/run?id=' . $record->id) ;?>"
                                                                class="btn default btn-xs red"> <i class="fa fa-toggle-right"></i> Create
                                                            </a>
                                                            <?php endif?>  
                                                            <?php if(intval($record->status) == 1):?>
                                                            <a href="<?php echo site_url($this->lang->lang() . '/badge/getCodes?id=' . $record->id) ;?>"
                                                                class="btn default btn-xs blue"> <i class="fa fa-file-o"></i> View
                                                            </a>
                                                            <?php endif?>  
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
                                                <div class="modal-footer">
                                                    <button type="button" class="btn default" data-dismiss="modal">Close</button>
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