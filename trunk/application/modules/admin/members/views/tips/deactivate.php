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
    <?php echo lang('tip_header')?>
    <small><?php echo lang('tip_list_header') ?></small>
</h3>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <a href="<?php echo site_url($this->lang->lang().'/business/tips')?>"><?php echo lang('bc_tips')?></a>
        </li>
    </ul>
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
    $error_msg = $this->messages->get('error');
    if(!empty($error_msg) ):
        ?>
        <?php foreach($error_msg as $error):?>
        <div class="alert alert-error alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button"></button>
            <strong>Error!</strong> <?php echo $error?>
        </div>
    <?php endforeach;?>
    <?php endif;?>
</div>
<div class="col-md-12">
    <div class="portlet-body">
        <!--BEGIN TABS-->
        <div class="tabbable tabbable-custom tabbable-full-width">
            <ul class="nav nav-tabs">
                <li>
                    <a href="<?php echo site_url($this->lang->lang().'/business/tips/index')?>">
                        <?php echo lang('tip_tab_list')?> </a>
                </li>
                <li class="active">
                    <a href="<?php echo site_url($this->lang->lang().'/business/tips/deactivate')?>">
                        <?php echo lang('tip_tab_deactivate')?>&nbsp;<?php echo (isset($total) && $total != 0) ? '<span class="badge badge-danger">'.$total.'</span>' : ''?>
                    </a>

                </li>
            </ul>
            <div class="tab-content">
                <div id="tab_1_1" class="tab-pane active">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- CONTENT TAB HERE -->
                            <!-- BEGIN EXAMPLE TABLE PORTLET-->
                            <div class="portlet box blue-hoki">
                                <div class="portlet-title">
                                    <div class="caption">
                                        <i class="fa fa-globe"></i> <?php echo lang('tip_result_records')?>
                                    </div>
                                    <div class="tools">
                                    </div>
                                </div>
                                <div class="portlet-body">
                                    <div class="row">
                                        <?php echo form_open(site_url($this->uri->uri_string()),'method="GET" name="form_searchResult" id="form_searchResult"')?>
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
                                    <?php echo form_open(site_url($this->uri->uri_string()),'method="POST" name="form_listRecords" id="form_listRecords"')?>
                                    <?php if(isset($records) && !empty($records)):?>
                                        <table class="table table-striped table-bordered table-hover" id="sample_1">
                                            <thead>
                                            <tr>
                                                <th class="column-check" data-sortable="false" data-id="id"><?php echo form_checkbox(false,'',false,'class="check-all"')?></th>
                                                <th><?php echo lang('tip_id')?></th>
                                                <th><?php echo lang('tip_name')?></th>
                                                <th><?php echo lang('tip_user')?></th>
                                                <th><?php echo lang('tip_business')?></th>
                                                <th><?php echo lang('tip_description')?></th>
                                                <th><?php echo lang('tip_created_date')?></th>
                                                <th><?php echo lang('tip_status')?></th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            <?php foreach($records as $record):?>
                                                <tr>
                                                    <td class="column-check"><?php echo form_checkbox('checked[]',$record->id,false,'class="checkbox-delete"')?></td>
                                                    <td><?php echo $record->id?></td>
                                                    <td><?php echo anchor(site_url($this->lang->lang() . '/business/tips/edit/' . $record->id), !empty($record->name) ? $record->name : '<i>none</i>') ?></td>
                                                    <td><?php echo $record->user->username?></td>
                                                    <td><?php echo $record->business->name?></td>
                                                    <td><?php echo $record->description?></td>
                                                    <td><?php echo get_time_date($record->created_date)?></td>
                                                    <td><?php echo ($record->status == 0) ? lang('tip_status_deactivate') : lang('tip_status_active')?></td>

                                                </tr>
                                            <?php endforeach;?>
                                            </tbody>
                                        </table>
                                    <?php endif;?>
                                    <div class="row">
                                        <div class="col-md-7 col-sm-12">
                                            <?php echo lang('showing_result_items_pagination',$pagination_start_item , $pagination_total_rows_ppage,$pagination_total_rows)?> <br/>
                                            <?php echo form_submit('btn_delete',lang('btn_delete'), 'class="btn btn-danger"')?>
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

            </div>
        </div>
        <!--END TABS-->
    </div>
</div>
</div>
<!-- END PAGE CONTENT-->
</div>
</div>