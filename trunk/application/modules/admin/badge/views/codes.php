<?php 
$current_url                    = site_url().$this->uri->uri_string().'.html';
$query_url                      = $_SERVER['QUERY_STRING'];
?>
<div class="page-content-wrapper">
<div class="page-content">
    <!-- BEGIN PAGE HEADER-->
    <h3 class="page-title">
        Badge ID List
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
                <a>Badge Codes</a>
                <i class="fa fa-angle-right"></i>
            </li>
            <li>
                <a>Badge ID Creation Log</a>
                <i class="fa fa-angle-right"></i>
            </li>
            <li>
                Badge ID List
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
                                    <?php echo form_open(site_url($this->lang->lang().'/badge/index'),'method="POST" name="form_listRecords" id="form_listRecords"')?>
                                    <table class="table table-striped table-bordered table-hover" id="sample_2">
                                        <thead>
                                        <tr>
                                            <th>S/N</th>
                                            <th>Manufacturer / Brand</th>
                                            <th>
                                                    <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'code_id', 'sort' => $order_field == "code_id" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo  'Badge ID URL ';
                                                        if($order_field == 'code_id'){
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
                                                    <a class="header-table" href="<?php echo get_url_query($current_url, $query_url, ['order_field' => 'code_id', 'sort' => $order_field == "code_id" ? ($sort == "DESC" ? "ASC" : "DESC") : "DESC"]);?>" >
                                                    <?php 
                                                        echo  'Badge ID ';
                                                        if($order_field == 'code_id'){
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
                                            <th>Date Created</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if(!empty($records)):?>
                                            <?php $i=0;?>
                                            <?php foreach($records as $record):?>
                                                <?php $i++;?>
                                                <tr>
                                                    <td><?php echo $i;?></td>
                                                    <td><?php echo $record['name']?></td>                                                    
                                                    <td><?php echo $record['code']?></td>
                                                    <td><?php echo $record['code_id']?></td>
                                                    <td><?php echo get_time_date($record['created_date'])?></td>
                                                </tr>
                                            <?php endforeach;?>
                                        <?php endif;?>
                                        </tbody>
                                    </table>
                                    
                                    <?php echo form_close()?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-7 col-sm-12">
                                  <a href="<?php echo site_url($this->lang->lang() . '/badge/index') ;?>"
                                    class="btn default btn-sm red"></i> Back
                                  </a>
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