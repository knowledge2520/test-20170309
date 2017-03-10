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
    <?php echo lang('business_header') ?>
    <small><?php echo lang('business_list_header')?></small>
</h3>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <a href="<?php echo site_url($this->lang->lang() . '/business/') ?>"><?php echo lang('bc_business') ?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?php echo lang('bc_business_listings') ?>
        </li>
    </ul>
    <?php if(isset($permissions[$module.'.create']) || isset($permissions[$module.'.individual']) || isset($permissions['business.categories.create']) || $is_admin):?>
    <div class="page-toolbar">
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown">
                <?php echo lang('business_actions') ?> <i class="fa fa-angle-down"></i>
            </button>
            <ul class="dropdown-menu pull-right" role="menu">
                <?php if(isset($permissions[$module.'.create']) || $is_admin || isset($permissions[$module.'.individual'])):?>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/business/create') ?>"><?php echo lang('business_create') ?></a>
                </li>
                <?php endif;?>
                <?php if($is_admin && 0):?>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/business/import') ?>"><?php echo lang('business_import') ?></a>
                </li>
                <?php endif;?>
                <?php if(isset($permissions['business.categories.create']) || $is_admin):?>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/business/categories/index') ?>"><?php echo lang('business_categories') ?></a>
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

    </div>
    <div class="col-md-12">
        <div class="portlet-body">
            <!--BEGIN TABS-->
            <div class="tabbable tabbable-custom tabbable-full-width">
                   <ul class="nav nav-tabs">
                   <?php if(isset($countries) && !empty($countries)):?>
                   <?php foreach($countries as $key=>$item):?>
                   <?php if($key):?>
                   		<?php 
                   			$url = get_url($key, 'business?country=');
                   			$check_active = check_active_tab($key, $default_country, 'country');
                   		?>
                   		<li <?php if($check_active) echo 'class="active"';?> >
                            <a href="<?php echo $url; ?>">
                                <?php echo ucwords(strtolower($item));?> 
                            </a>
                        </li>
                   <?php endif;?>
                   <?php endforeach;?>
                   <?php endif;?>
                    </ul>
                    <div class="tab-content">
                    	<div class="row">
	                    <div class="col-md-12">
	                        <!-- CONTENT TAB HERE -->
	                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
	                        <div class="portlet box blue-hoki">
	                            <div class="portlet-title">
	                                <div class="caption">
	                                    <i class="fa fa-globe"></i> <?php echo lang('lbl_result_records') ?>
	                                </div>
	                                <div class="tools">
	                                </div>
	                            </div>
	                            <div class="portlet-body">
	                                <div class="row">
	                                    <?php echo form_open(site_url($this->uri->uri_string()), 'method="GET" name="form_searchResult" id="form_searchResult"') ?>
	                                    <div class="col-md-4 col-sm-4 col-xs-6">
	                                        <div class="dataTables_length" id="dataTables_length">
	                                            <label>
	                                                <?php
	                                                $data_tableLength = SiteHelper::recordPerPage();
	                                                ?>
	                                                <?php echo form_dropdown('dataTables_length', $data_tableLength, isset($item_tableLength) ? $item_tableLength : ADMIN_ITEMS_PERPAGE, 'class="form-control input-xsmall input-inline" id="dataTables_length" ') ?>
	                                                <?php echo lang('lbl__records') ?>
	                                            </label>
	                                        </div>
	                                    </div>
	                                    <div class="col-md-4 col-sm-4 col-xs-6">
	                                        <div id="sample_1_filter" class="dataTables_filter">
	                                            <div class="rơw">
	                                                <label><?php echo lang('lbl_my_search') ?>:
	                                                    <?php echo form_input('txt_search', isset($txt_search_value) ? $txt_search_value : '', 'type="search" class="form-control input-small input-inline" placeholder=""') ?>
	                                                    <?php echo form_hidden('country', $country); ?>
	                                                </label>
	                                                &nbsp;
	                                                <?php if(isset($permissions[$module.'.individual'])):?>
	                                                <label>
	                                                    <input type="checkbox" name="my_listing" value="on" id="my_listing" <?php if($my_listing) echo 'checked';?>>My listing<br>
	                                                </label>
	                                                <?php endif;?>
	                                            </div>
	                                        </div>
	                                    </div>
	                                    
	                                    <!--  
	                                    <div class="col-md-2 col-sm-12">
	                                        <div class="dataTables_length" id="dataTables_length">
	                                            <label>
	                                                <?php //echo lang('business_country'); ?>
	                                                <?php //echo form_dropdown('country', $countries, $country, 'class="form-control input-xsmall input-inline" onchange="this.form.submit()"') ?>
	                                            </label>
	                                        </div>
	                                    </div>
	                                    -->
	                                    
	                                    <div class="col-md-12 col-sm-12 col-xs-12">
	                                        <div class="text-right"> <?php echo ($pagination) ? $pagination : '' ?></div>
	                                    </div>
	                                    <?php echo form_close() ?>
	                                </div>
	                                    <?php echo form_open(site_url($this->lang->lang() . '/business/index'), 'method="POST" name="form_listRecords" id="form_listRecords"') ?>
	                                    <table class="table table-striped table-bordered table-hover" id="sample_1">
	                                        <thead>
	                                        <tr>
	                                            <th class="column-check" data-sortable="false"
	                                                data-id="id"><?php echo form_checkbox(false, '', false, 'class="check-all"') ?></th>
                                                <?php if(isset($permissions[$module.'.edit']) || $is_admin || $record->individual):?>
                                                    <th></th>
                                                <?php endif;?>
                                                <th><?php echo lang('lbl_no.')?></th>
	                                            <th>   
	                                            	<a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'id', 'sort' => $order_field == "id" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('business_id') . ' ';
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
	                                            	<?php echo lang('business_photo') ?>	                                            	
	                                            </th>
	                                            <th width="10%">	 
	                                            	<a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'name', 'sort' => $order_field == "name" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('business_name') . ' ';
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
	                                            <th width="10%">
	                                            	<?php echo lang('business_category') ?>	                                          	
	                                            </th>
	                                            <th>
	                                            	<a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'full_name', 'sort' => $order_field == "full_name" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('business_author') . ' ';
                                                        if($order_field == 'full_name'){
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
	                                            	<a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'address', 'sort' => $order_field == "address" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('business_address') . ' ';
                                                        if($order_field == 'address'){
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
	                                            <th width="10%">
	                                            	<a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'hour', 'sort' => $order_field == "hour" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('business_hour') . ' ';
                                                        if($order_field == 'hour'){
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
	                                            	<a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'phone', 'sort' => $order_field == "phone" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('business_phone') . ' ';
                                                        if($order_field == 'phone'){
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
	                                            <th width="5%">
	                                            	<a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'website', 'sort' => $order_field == "website" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('business_website') . ' ';
                                                        if($order_field == 'website'){
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
	                                            	<?php echo lang('business_status') ?>                                            	
	                                            </th>
	                                            <th>
	                                            	<a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'created_date', 'sort' => $order_field == "created_date" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo lang('business_created_date') . ' ';
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
	                                        </tr>
	                                        </thead>
	                                        <tbody>
                                            <?php if(!empty($records)): $no = $offset + 1;?>
	                                        <?php foreach ($records as $record):?>
	                                            <tr <?php if(!($record->latitude && $record->longitude)) echo 'class="strong-row-item"'; ?>>
	                                                <td class="column-check">
	                                                    <?php echo form_checkbox('checked[]', $record->id, false, 'class="checkbox-delete"');?>
	                                                </td>
                                                    <?php if(isset($permissions[$module.'.edit']) || $is_admin || $record->individual):?>
                                                        <td>                                                            
                                                            <a href="<?php echo site_url($this->lang->lang() . '/business/edit/' . $record->id) ;?>"
                                                                class="btn default btn-xs green-meadow"> <i class="fa fa-edit"></i> <?php echo lang('btn_edit'); ?>
                                                            </a>
                                                        </td>
                                                    <?php endif?>	     
                                                    <td><?php echo $no++;?></td>        
	                                                <td><?php echo $record->id;?></td>                                   
	                                                <td><img alt="" src="<?php echo CMSHelper::output_media($record->photo) ?>" width="75px"></td>
	                                                <td><?php  echo !empty($record->name) ? $record->name : '<i>none</i>'; ?></td>
	                                                <td>
	                                                    <?php
	                                                        if(isset($record->categories) && !empty($record->categories)){
                                                                $arr = [];
                                                                if($is_admin){
                                                                    foreach ($record->categories as $data){
                                                                        $arr[] = '<a href="'.site_url().$this->lang->lang().'/business/categories/edit/'.$data->id.'">'.$data->name.'</a>';
                                                                    }                                                                    
                                                                }else{
                                                                    foreach ($record->categories as $data){                                                                    
                                                                        $arr[] = $data->name;
                                                                    }    
                                                                }
                                                                echo implode(', ', $arr);  
	                                                        }
	                                                    ?>
	
	                                                </td>
	                                                <td><?php echo !empty($record->first_name) && !empty($record->last_name) ? $record->first_name . ' ' . $record->last_name : 'N/A';?> </td>
	                                                <td><?php echo ellipsize($record->address, 30, 1) ?></td>
	                                                <td><?php echo ellipsize($record->hour, 30, 1) ?></td>
	                                                <td><?php echo ellipsize($record->phone, 20, 1) ?></td>
	                                                <td>
		                                                <?php if(!filter_var(get_website_listing($record->website), FILTER_VALIDATE_URL) === false):?>
		                                                	<a href="<?php echo get_website_listing($record->website); ?>" target="_blank"><?php echo  ellipsize(get_website_listing($record->website), 20, 1); ?></a>
		                                                <?php else:?>
		                                                	<?php echo get_website_listing($record->website); ?>
		                                                <?php endif;?>
	                                                	
	                                                </td>
	                                                <td>
	                                                    <?php                                                      
	                                                    switch ($record->status){
	                                                        case 0: echo lang('business_status_deactivate');break;
	                                                        case 1: echo lang('business_status_active');break;
	                                                        case 2: echo lang('business_status_delete');break;
	                                                        case 3: echo lang('business_status_reject');break;
	                                                    }
	                                                    ;?>
	                                                </td>
	                                                <td><?php echo get_time_date($record->created_date) ?></td>
	                                            </tr>
	                                        <?php endforeach; ?>
                                        <?php else:?>
                                            <tr class="odd"><td valign="top" colspan="20" class="dataTables_empty">No data available in table</td></tr>
                                        <?php endif;?>
                                        </tbody>
                                    </table>
	                                <div class="row">
	                                    <div class="col-md-7 col-sm-12">
	                                        <?php echo lang('showing_result_items_pagination', $pagination_start_item, $pagination_total_rows_ppage, $pagination_total_rows) ?>
	                                        <br/>
	                                        <?php if(isset($permissions[$module.'.delete']) || $is_admin || isset($permissions[$module.'.individual'])): ?>
	                                        <a href="#" data-href="" class="delete-dialog" data-toggle="modal" data-target="#confirm"><button  class="btn btn-danger">Move to trash</button></a>
	                                        <?php endif;?>
	                                            
	                                              <!-- echo form_submit('btn_delete', lang('btn_delete'), 'class="btn btn-danger"'); --> 
	                                                
	                
	                                    </div>
	                                    <div
	                                        class="col-md-5 col-sm-12 text-right"><?php echo ($pagination) ? $pagination : '' ?></div>
	                                </div>
	
	                                <div class="modal fade" id="confirm" tabindex="-1" role="basic" aria-hidden="true">
	                                    <div class="modal-dialog">
	                                        <div class="modal-content">
	                                            <div class="modal-body">
	                                                Are you sure?
	                                            </div>
	                                            <div class="modal-footer">
	                                                <button type="button" class="btn default" data-dismiss="modal">Close
	                                                </button>
	                                                <input type="submit" class="btn btn-danger" name="btn_delete" id="delete" value="Delete">
	                                            </div>
	                                        </div>
	                                        <!-- /.modal-content -->
	                                    </div>
	                                    <!-- /.modal-dialog -->
	                                </div>
	                                <?php echo form_close() ?>
	                            </div>
	                        </div>
	                        <!-- END EXAMPLE TABLE PORTLET-->
	                    </div>
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