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
                            <i class="fa fa-users"></i><?php echo lang('section_title_dashboard') ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="task-content">
                            <ul class="task-list">
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_today') ?></span>
                                        <span style="float: right"><?php echo $member_dashboard['today']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_yesterday') ?></span>
                                        <span style="float: right"><?php echo $member_dashboard['yesterday']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_this_month') ?></span>
                                        <span style="float: right"><?php echo $member_dashboard['this_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_last_month') ?></span>
                                        <span style="float: right"><?php echo $member_dashboard['last_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_month_before_last') ?></span>
                                        <span style="float: right"><?php echo $member_dashboard['last_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_this_year') ?></span>
                                        <span style="float: right"><?php echo $member_dashboard['this_year']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_change_from_last_year') ?></span>
                                        <span style="float: right"><?php echo $member_dashboard['change_from_last_year'] > 0 ? '+' . $member_dashboard['change_from_last_year'] . '%' : ($member_dashboard['change_from_last_year'] == 0 ? 0 . '%' : $member_dashboard['change_from_last_year'] . '%') ?></span>
                                    </div>
                                </li>
                                <li class="last-line">
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_total') ?></span>
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
                            <i class="fa fa-paw"></i><?php echo lang('section_title_pet_dashboard') ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="task-content">
                            <ul class="task-list">
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_today') ?></span>
                                        <span style="float: right"><?php echo $pet_dashboard['today']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_yesterday') ?></span>
                                        <span style="float: right"><?php echo $pet_dashboard['yesterday']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_this_month') ?></span>
                                        <span style="float: right"><?php echo $pet_dashboard['this_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_last_month') ?></span>
                                        <span style="float: right"><?php echo $pet_dashboard['last_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_month_before_last') ?></span>
                                        <span style="float: right"><?php echo $pet_dashboard['month_before_last']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_this_year') ?></span>
                                        <span style="float: right"><?php echo $pet_dashboard['this_year']?></span>
                                    </div>
                                </li>
                                <li class="last-line">
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_total') ?></span>
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
                            <i class="fa fa-signal"></i><?php echo lang('section_title_member_overall') ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="task-content">
                            <ul class="task-list">
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_active_users') ?></span>
                                        <span style="float: right"><?php echo $member_overall['active_user']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_male') ?></span>
                                        <span style="float: right"><?php echo $member_overall['male']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_female') ?></span>
                                        <span style="float: right"><?php echo $member_overall['female']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_ios') ?></span>
                                        <span style="float: right"><?php echo $member_overall['ios']?></span>
                                    </div>
                                </li>
                                <li <?php if(empty($member_overall['top_countries'])) echo 'class="task-title"'?>>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_android') ?></span>
                                        <span style="float: right"><?php echo $member_overall['android']?></span>
                                    </div>
                                </li>
                                <?php if(isset($member_overall['top_countries']) && !empty($member_overall['top_countries'])):?>
                                <li class="last-line">
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_top_countries') ?></span>
                                        <ul class="task-list">
                                            <?php foreach ($member_overall['top_countries'] as $key => $item) :?>
                                            <li class="last-line">
                                                <span class="task-title-sp">- <?php echo $item->country_name ? $item->country_name : 'N/A' ?></span>
                                                <span style="float: right"><?php echo $member_overall['total'] == 0 ? '100%' : round( $item->total*100/$member_overall['total'], 2) . '%'  ?></span>
                                            </li>
                                            <?php endforeach?>                                            
                                        </ul>
                                    </div>
                                </li>
                                <?php endif?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-4">
                <div class="portlet box green-haze tasks-widget">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-signal"></i><?php echo lang('section_title_badgeid_linked') ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="task-content">
                            <ul class="task-list">
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_today') ?></span>
                                        <span style="float: right"><?php echo $badge_dashboard['today']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_yesterday') ?></span>
                                        <span style="float: right"><?php echo $badge_dashboard['yesterday']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_this_month') ?></span>
                                        <span style="float: right"><?php echo $badge_dashboard['this_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_last_month') ?></span>
                                        <span style="float: right"><?php echo $badge_dashboard['last_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_month_before_last') ?></span>
                                        <span style="float: right"><?php echo $badge_dashboard['last_month']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_this_year') ?></span>
                                        <span style="float: right"><?php echo $badge_dashboard['this_year']?></span>
                                    </div>
                                </li>
                                <li>
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_change_from_last_year') ?></span>
                                        <span style="float: right"><?php echo $badge_dashboard['change_from_last_year'] > 0 ? '+' . $badge_dashboard['change_from_last_year'] . '%' : ($badge_dashboard['change_from_last_year'] == 0 ? 0 . '%' : $badge_dashboard['change_from_last_year'] . '%') ?></span>
                                    </div>
                                </li>
                                <li class="last-line">
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_total') ?></span>
                                        <span style="float: right"><?php echo $badge_dashboard['total']?></span>
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
                            <i class="fa fa-signal"></i><?php echo lang('section_title_badgeid_overview') ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="task-content">
                            <ul class="task-list">
                                <li class="last-line">
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_top_countries_overview') ?></span>
                                        <ul class="task-list">
                                            <?php if(isset($badge_overall['top_countries']) && !empty($badge_overall['top_countries'])): ?>
                                                <ul class="task-list">
                                                    <?php foreach ($badge_overall['top_countries'] as $key => $item) :?>
                                                    <li class="last-line">
                                                        <span class="task-title-sp">- <?php echo $item->country_name ? $item->country_name : 'N/A' ?></span>
                                                        <span style="float: right"><?php echo $badge_overall['total'] == 0 ? '100%' : round( $item->total*100/$badge_overall['total'], 2) . '%'  ?></span>
                                                    </li>
                                                    <?php endforeach?>                                            
                                                </ul>
                                            <?php endif?>
                                        </ul>
                                    </div>
                                </li>
                                <li class="last-line">
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_top_brands_overview') ?></span>
                                        <ul class="task-list">
                                            <?php if(isset($badge_overall['top_brands']) && !empty($badge_overall['top_brands'])):?>
                                                <ul class="task-list">
                                                    <?php foreach ($badge_overall['top_brands'] as $key => $item) :?>
                                                    <li class="last-line">
                                                        <span class="task-title-sp">- <?php echo $item->name ? $item->name : 'N/A' ?></span>
                                                        <span style="float: right"><?php echo $badge_overall['total'] == 0 ? '100%' : round( $item->total*100/$badge_overall['total'], 2) . '%'  ?></span>
                                                    </li>
                                                    <?php endforeach?>                                            
                                                </ul>
                                            <?php endif?>
                                        </ul>
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
                            <i class="fa fa-signal"></i><?php echo lang('section_title_pet_overview') ?>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="task-content">
                            <ul class="task-list">
                                <li class="last-line">
                                    <div class="task-title">
                                        <span class="task-title-sp"><?php echo lang('section_top_pet_types') ?></span>
                                        <ul class="task-list">
                                            <?php if(isset($pet_overall['top_pet_types']) && !empty($pet_overall['top_pet_types'])):?>
                                                <ul class="task-list">
                                                    <?php foreach ($pet_overall['top_pet_types'] as $key => $item) :?>
                                                    <li>
                                                        <span class="task-title-sp">- <?php echo $item->name ? $item->name : 'N/A' ?></span>
                                                        <span style="float: right"><?php echo $pet_overall['total'] == 0 ? '100%' : round( $item->total*100/$pet_overall['total'], 2) . '%'  ?></span>

                                                            <ul>                                                   
                                                                <li class="last-line">
                                                                    <span class="task-title-sp">Male</span>
                                                                    <span style="float: right"><?php echo $pet_overall['total'] == 0 ? '100%' : round( $item->male*100/$item->total, 2) . '%'  ?></span>
                                                                </li>  
                                                                <li>
                                                                    <span class="task-title-sp">Female</span>
                                                                    <span style="float: right"><?php echo $pet_overall['total'] == 0 ? '100%' : round( $item->female*100/$item->total, 2) . '%'  ?></span>
                                                                </li>                                                                                          
                                                            </ul>
                                                    </li>
                                                    <?php endforeach?>                                            
                                                </ul>
                                            <?php endif?>
                                        </ul>
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