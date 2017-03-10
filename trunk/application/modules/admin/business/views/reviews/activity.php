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
            <small><?php echo lang('business_edit_header') ?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a href="<?php echo site_url($this->lang->lang().'/business/')?>"><?php echo lang('bc_business')?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><?php echo lang('bc_business_edit')?></li>
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
                        <li>
                            <a href="#">
                                <?php echo lang('review_tab_general')?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/business/reviews/mediaList/'.$review_id)?>">
                                <?php echo lang('review_tab_media')?> </a>
                        </li>
                        <li>
                            <a href="<?php echo site_url($this->lang->lang().'/business/reviews/commentList/'.$review_id)?>">
                                <?php echo lang('review_tab_comment')?> </a>
                        </li>
                        <?php if($is_admin):?>
                        <li class="active">
                            <a href="<?php echo site_url($this->lang->lang().'/business/activity/review/'.$review_id)?>">
                                Activity Log </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <div class="tab-content">
                        <div class="portlet light bordered form-fit">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="icon-user font-blue-hoki"></i>
                                    <span
                                        class="caption-subject font-blue-hoki bold uppercase">Activity Log</span>
                                </div>
                            </div>
                            <div class="row">
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
                                                                <i class="fa fa-globe"></i> Records
                                                            </div>
                                                            <div class="tools">
                                                            </div>
                                                        </div>
                                                        <div class="portlet-body">
                                                            <div class="row">
                                                                <?php echo form_open(site_url($this->lang->lang().'/business/activity/review/'. $review_id),'method="GET" name="form_searchResult_Filter" id="form_searchResult_Filter"')?> 
                                                                <div class="col-md-3">
                                                                    <input type="text" name="keyword1" value="<?php echo isset($keyword['actor_name'])?$keyword['actor_name']:""?>" placeholder="Search by Actor name or ID" class="form-control" placeholder="">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select class="form-control" name="action">
                                                                        <option value="-1">All Actions</option>
                                                                        <option value="Created" <?php echo isset($keyword['action']) && $keyword['action']=="Created" ?" selected":""?>>Created</option>
                                                                        <option value="Updated" <?php echo isset($keyword['action']) && $keyword['action']=="Updated" ?" selected":""?>>Updated</option>
                                                                        <option value="Deleted" <?php echo isset($keyword['action']) && $keyword['action']=="Deleted" ?" selected":""?>>Deleted</option>
                                                                        <option value="Viewed" <?php echo isset($keyword['action']) && $keyword['action']=="Viewed" ?" selected":""?>>Viewed</option>
                                                                    </select>   
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="input-group" data-date="13/07/2013" data-date-format="mm/dd/yyyy">
                                                                          <input type="text" class="form-control default-date-picker" name="startDate" id="startDate" value="<?php echo isset($keyword['startDate']) && $keyword['startDate'] !="1970-01-01"  ? $keyword['startDate']:""?>" placeholder="From">
                                                                          <span class="input-group-addon"><li class="fa fa-calendar"></li></span>
                                                                          <input type="text" class="form-control default-date-picker" name="endDate" id="endDate" value="<?php echo isset($keyword['endDate']) && $keyword['endDate'] !="1970-01-01"  ? $keyword['endDate']:""?>" placeholder="To">
                                                                    </div>
                                                                    <div class="clearfix" style="color:#a94442;margin-left:-15px;margin-top:7px;display: none" id="errordate">
                                                                        <div class="col-md-4 clearfix"></div>
                                                                        <div class="col-md-10 clearfix" id="messError" style="">To date must be later than From date</div>
                                                                    </div>  
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <input type="submit" value="Search" onclick="save(event)" class="btn green-meadow" >
                                                                </div>
                                                                <?php echo form_close()?>
                                                            </div>
                                                             <div class="row">
                                                                <?php echo form_open(site_url($this->lang->lang().'/business/activity/review/'.$review_id),'method="GET" name="form_searchResult" id="form_searchResult"')?>
                                                                <div class="col-md-4 col-sm-12">
                                                                    <div class="dataTables_length" id="dataTables_length">
                                                                        <label>
                                                                            <?php
                                                                            $data_tableLength = SiteHelper::recordPerPage();
                                                                            ?>
                                                                            <?php echo form_dropdown('dataTables_length',$data_tableLength , isset($item_tableLength) ? $item_tableLength : ADMIN_ITEMS_PERPAGE,'class="form-control input-xsmall input-inline" id="dataTables_length" ')?>
                                                                            Records
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 col-sm-12">
                                                                    <div class="text-right"> <?php echo ($pagination) ? $pagination : ''?></div>
                                                                </div>
                                                                <?php echo form_close()?>
                                                            </div>   
                                                             <?php echo form_open(site_url($this->lang->lang().'/business/activity/review/'.$review_id),'method="POST" name="form_listRecords" id="form_listRecords"')?>
                                                            <table class="table table-striped table-bordered table-hover" id="">
                                                                <thead>
                                                                <tr>
                                                                    <th>Event Time</th>
                                                                    <th>Action</th>
                                                                    <th>Actor Name</th>  
                                                                    <th>Actor ID</th>
                                                                    <th>Entity Name</th>
                                                                    <th>Entity ID</th>                                              
                                                                    <th>Field Name</th>
                                                                    <th>Old Value</th>
                                                                    <th>New Value</th> 
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php if(!empty($records)):?>
                                                                    <?php foreach($records as $record):?>
                                                                        <tr>                                            
                                                                            <td><?php echo $record->event_time?></td>
                                                                            <td><?php echo $record->action?></td>
                                                                            <td><?php echo $record->actor_name?></td>
                                                                            <td><?php echo $record->actor_id?></td>
                                                                            <td><?php echo $record->entity_type?></td>
                                                                            <td><?php echo $record->entity_id?></td>
                                                                            <td><?php echo $record->field_name?></td>
                                                                            <td><?php echo $record->old_value?></td>
                                                                            <td><?php echo $record->new_value?></td>
                                                                        </tr>
                                                                    <?php endforeach;?>
                                                                <?php endif;?>
                                                                </tbody>
                                                            </table>
                                                            <div class="row">
                                                                <div class="col-md-7 col-sm-12">
                                                                    <?php echo lang('showing_result_items_pagination',$pagination_start_item , $pagination_total_rows_ppage,$pagination_total_rows)?> <br/>
                                                                </div>
                                                                <div class="col-md-5 col-sm-12 text-right"><?php echo ($pagination) ? $pagination : ''?></div>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>
<script>
function save(event){
    event.preventDefault();
    if($("#endDate").val() !="" && $("#endDate").val() !=""){
        var startDate = new Date($("#startDate").val());
        var endDate = new Date($("#endDate").val());
        if(endDate < startDate){
            $("#errordate").show();
            $(".fakeTable").show();
            $(".realTable").hide();
            return;
        }else{
            $("#form_searchResult_Filter").submit();
        }
    }
    $("#form_searchResult_Filter").submit();
}
</script>