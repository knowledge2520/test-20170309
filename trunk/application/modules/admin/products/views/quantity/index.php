<?php
$pagination                     = isset($paging) ? $paging->create_links() : false;
$pagination_total_rows          = isset($total) ? $total : 0;
$pagination_total_rows_ppage    = (isset($paging) && $paging->cur_page >= 1 && isset($count) && $paging->per_page == $count) ? $paging->per_page * $paging->cur_page : $pagination_total_rows;
$pagination_start_item          =  $total == 0 ? 0 : (isset($paging) && $paging->cur_page > 1 ? ($paging->per_page * ($paging->cur_page - 1)) : 1);
?>
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('product_edit_header') ?>
            <small><?php echo lang('product_quantity_header') ?></small>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/products/') ?>"><?php echo lang('bc_product') ?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?php echo site_url($this->lang->lang() . '/products/edit/' . $record->id) ?>"><?php echo lang('bc_product_edit') ?></a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <?php echo lang('bc_product_quantity') ?>
                </li>
            </ul>
            <div class="page-toolbar">
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-fit-height grey-salt dropdown-toggle" data-toggle="dropdown"
                            data-hover="dropdown" data-delay="1000" data-close-others="true">
                        <?php echo lang('product_action') ?> <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>
                            <a href="<?php echo site_url($this->lang->lang() . '/products/quantity/create/' . $record->id) ?>"><?php echo lang('product_quantity_create') ?></a>
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

                <div class="portlet">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-shopping-cart"></i><?php echo lang('bc_product_quantity')?>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="tabbable">
                            <ul class="nav nav-tabs">
                                <li>
                                    <a href="<?php echo site_url($this->lang->lang() . '/products/edit/' . $record->id) ?>">
                                        <?php echo lang('product_tab_general');?> </a>
                                </li>
                                <li>
                                    <a href="<?php echo site_url($this->lang->lang() . '/products/color/index/' . $record->id) ?>">
                                        <?php echo lang('product_tab_color');?> </a>
                                </li>
                                <li>
                                    <a href="<?php echo site_url($this->lang->lang() . '/products/size/index/' . $record->id) ?>">
                                        <?php echo lang('product_tab_size');?> </a>
                                </li>
                                <li class="active">
                                    <a href="#tab_4" class="active">
                                        <?php echo lang('product_tab_quantity');?> </a>
                                </li>
                            </ul>
                            <div class="tab-content no-space">
                                <div id="tab_4" class="tab-pane active">
                                    <?php echo form_open(site_url($this->lang->lang() . '/products/quantity/index/'.$record->id), 'method="POST" name="form_listRecords" id="form_listRecords"') ?>
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                        <tr>
                                            <th width="10%" class="column-check" data-sortable="false"
                                                data-id="id"><?php echo form_checkbox(false, '', false, 'class="check-all"') ?></th>
                                            <th><?php echo lang('quantity_color') ?></th>
                                            <th><?php echo lang('quantity_size') ?></th>
                                            <th><?php echo lang('quantity_quantity') ?></th>
                                            <th><?php echo lang('quantity_sell_quantity') ?></th>
                                            <td></td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (!empty($records)): ?>
                                            <?php foreach ($records as $item): ?>
                                                <tr>
                                                    <td class="column-check"><?php echo form_checkbox('checked[]', $item->id, false, 'class="checkbox-delete"') ?></td>
                                                    <td><?php echo $item->color ?></td>
                                                    <td><?php echo $item->size ?></td>
                                                    <td><?php echo $item->quantity ?></td>
                                                    <td><?php echo $item->sell_quantity ?></td>
                                                    <td>
                                                    	<a href="<?php echo site_url($this->lang->lang() . '/products/quantity/edit/' . $record->id . '/' . $item->id) ?>" class="btn default btn-xs purple">
					                                        <i class="fa fa-edit"></i> <?php echo lang('btn_edit');?>
					                                    </a>
                                    				</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <div class="row">
                                        <div class="col-md-7 col-sm-12">
                                            <?php echo form_submit('btn_delete', lang('btn_delete'), 'class="btn btn-danger"') ?>
                                        </div>
                                        <div class="col-md-5 col-sm-12 text-right"><?php echo ($pagination) ? $pagination : ''?></div>
                                    </div>
                                    <div class="modal fade" id="confirm" tabindex="-1" role="basic"
                                         aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-body">
                                                    Are you sure?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn default" data-dismiss="modal">
                                                        Close
                                                    </button>
                                                    <button type="button" class="btn blue" id="delete">Delete
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <?php echo form_close() ?>
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
<!-- END CONTENT -->