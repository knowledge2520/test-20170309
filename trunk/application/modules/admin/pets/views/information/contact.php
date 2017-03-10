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
    <?php echo lang('pet_header') ?>
    <small><?php echo lang('pet_contact_header') ?></small>
</h3>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <i class="fa fa-home"></i>
            <a href="<?php echo site_url($this->lang->lang().'/home')?>"><?php echo lang('bc_home')?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <a href="<?php echo site_url($this->lang->lang().'/pets')?>"><?php echo lang('bc_pet')?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <a href="<?php echo site_url($this->lang->lang().'/pets/edit/'.$pet_id)?>"><?php echo lang('bc_pet_edit')?></a>
            <i class="fa fa-angle-right"></i>
        </li>
        <li>
            <?php echo lang('bc_pet_contact')?>
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
            <div class="alert alert-danger alert-dismissable">
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
                <ul class="nav nav-tabs nav-tabs-lg">
                    <li>
                        <a href="<?php echo site_url($this->lang->lang().'/pets/edit/'.$pet_id)?>">
                            <?php echo lang('pet_information')?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo site_url($this->lang->lang().'/pets/vaccinations/'.$pet_id)?>">
                            <?php echo lang('pet_vaccinations')?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo site_url($this->lang->lang().'/pets/medical_examinations/'.$pet_id)?>">
                            <?php echo lang('pet_medical_examinations')?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo site_url($this->lang->lang().'/pets/physical_exams/'.$pet_id)?>">
                            <?php echo lang('pet_physical_exams')?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo site_url($this->lang->lang().'/pets/medications/'.$pet_id)?>">
                            <?php echo lang('pet_medications')?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo site_url($this->lang->lang().'/pets/surgeries/'.$pet_id)?>">
                            <?php echo lang('pet_surgeries')?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo site_url($this->lang->lang().'/pets/allergies/'.$pet_id)?>">
                            <?php echo lang('pet_allergies')?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo site_url($this->lang->lang().'/pets/vaccination/'.$pet_id)?>" data-toggle="tab">
                            <?php echo lang('pet_weight')?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo site_url($this->lang->lang().'/pets/vaccination/'.$pet_id)?>" data-toggle="tab">
                            <?php echo lang('pet_estrus')?>
                        </a>
                    </li>
                    <li class="active">
                        <a href="<?php echo site_url($this->lang->lang().'/pets/vaccination/'.$pet_id)?>" data-toggle="tab">
                            <?php echo lang('pet_contact')?>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
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
                                <?php echo form_open(site_url($this->uri->uri_string()),'method="GET" name="form_searchResult" id="form_searchResult"')?>
                                <div class="col-md-4 col-sm-4 col-xs-6">
                                    <div class="dataTables_length" id="dataTables_length">
                                        <label>
                                            <?php
                                            $data_tableLength = SiteHelper::recordPerPage();
                                            ?>
                                            <?php echo form_dropdown('dataTables_length',$data_tableLength , isset($item_tableLength) ? $item_tableLength : ADMIN_ITEMS_PERPAGE,'class="form-control input-xsmall input-inline" id="dataTables_length" ')?>
                                            <?php echo lang('pet_records')?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4 col-xs-6">

                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="text-right"> <?php echo ($pagination) ? $pagination : ''?></div>
                                </div>
                                <?php echo form_close()?>
                            </div>
                            <?php echo form_open(site_url($this->uri->uri_string()),'method="POST" name="form_listRecords" id="form_listRecords"')?>
                            <table class="table table-striped table-bordered table-hover" id="sample_1">
                                <thead>
                                <tr>
                                    <th class="column-check" data-sortable="false" data-id="id"><?php echo form_checkbox(false,'',false,'class="check-all"')?></th>
                                    <th><?php echo lang('pet_contact_id');?></th>
                                    <th><?php echo lang('pet_contact_name');?></th>
                                    <th><?php echo lang('pet_contact_phone');?></th>
                                    <th><?php echo lang('pet_contact_notes');?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if(isset($records) && !empty($records)):?>
                                    <?php foreach($records as $record):?>
                                        <tr role="row" class="filter">
                                            <td class="column-check"><?php echo form_checkbox('checked[]',$record->id,false,'class="checkbox-delete"')?></td>
                                            <td><?php echo $record->id;?></td>
                                            <td><?php echo $record->name;?></td>
                                            <td><?php echo $record->phone;?></td>
                                            <td><?php echo $record->notes;?></td>
                                        </tr>
                                    <?php endforeach?>
                                <?php endif?>
                                </tbody>
                            </table>
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
            <!--END TABS-->
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->
</div>
</div>