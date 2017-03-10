<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('dashboard_header') ?>
        </h3>
        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="<?php echo site_url($this->lang->lang() . '/dashboard') ?>"><?php echo lang('bc_home') ?></a>
                </li>
            </ul>
        </div>
        <!-- END PAGE HEADER-->
        <!-- BEGIN PAGE CONTENT-->
        <div class="clearfix">
        </div>
        <div class="row ">
            <div class="col-md-4 col-sm-4">
                <div class="portlet box green-haze tasks-widget">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-users"></i>Member Dashboard
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="task-content">
                            <ul class="task-list">
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">Today</span>
                                        <span style="float: right"><?php echo $member_dashboard['today']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">Yesterday</span>
                                        <span style="float: right"><?php echo $member_dashboard['yesterday']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">This month</span>
                                        <span style="float: right"><?php echo $member_dashboard['this_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">Last month</span>
                                        <span style="float: right"><?php echo $member_dashboard['last_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">Month before last</span>
                                        <span style="float: right"><?php echo $member_dashboard['month_before_last']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">This Year</span>
                                        <span style="float: right"><?php echo $member_dashboard['this_year']?></span>
                                    </div>
                                </li>
                                <li class="last-line">
                                    <div class="task-title">
                                        <span class="task-title-sp">Total</span>
                                        <span style="float: right"><?php echo $member_dashboard['total']?></span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-4">
                <div class="portlet box green-haze tasks-widget">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-paw"></i>Pet Dashboard
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="task-content">
                            <ul class="task-list">
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">Today</span>
                                        <span style="float: right"><?php echo $pet_dashboard['today']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">Yesterday</span>
                                        <span style="float: right"><?php echo $pet_dashboard['yesterday']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">This month</span>
                                        <span style="float: right"><?php echo $pet_dashboard['this_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">Last month</span>
                                        <span style="float: right"><?php echo $pet_dashboard['last_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">Month before last</span>
                                        <span style="float: right"><?php echo $pet_dashboard['month_before_last']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">This Year</span>
                                        <span style="float: right"><?php echo $pet_dashboard['this_year']?></span>
                                    </div>
                                </li>
                                <li class="last-line">
                                    <div class="task-title">
                                        <span class="task-title-sp">Total</span>
                                        <span style="float: right"><?php echo $pet_dashboard['total']?></span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-4">
                <div class="portlet box green-haze tasks-widget">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-signal"></i>Member Overall
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="task-content">
                            <ul class="task-list">
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">Male</span>
                                        <span style="float: right"><?php echo $member_overall['male']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">Female</span>
                                        <span style="float: right"><?php echo $member_overall['female']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp">IOS</span>
                                        <span style="float: right"><?php echo $member_overall['ios']?></span>
                                    </div>
                                </li>
                                <li class="last-line">
                                    <div class="task-title">
                                        <span class="task-title-sp">Android</span>
                                        <span style="float: right"><?php echo $member_overall['android']?></span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END PAGE CONTENT-->
    </div>
</div>