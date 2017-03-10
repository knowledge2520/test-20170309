<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <h3 class="page-title">
            <?php echo lang('setting_header') ?>
        </h3>

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i> <a
                        href="<?php echo site_url($this->lang->lang() . '/home') ?>"><?php echo lang('bc_home') ?></a>
                    <i class="fa fa-angle-right"></i></li>
                <li><a
                        href="<?php echo site_url($this->lang->lang() . '/settings/') ?>"><?php echo lang('bc_setting') ?></a>
                </li>
            </ul>
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
                            <strong>Error!</strong><?php echo $error; ?>
                        </div>
                    <?php endforeach;
                } ?>

                <?php
                //success message
                $success_msg = $this->messages->get('success');
                if (!empty($success_msg)):
                    ?>
    <?php foreach ($success_msg as $message): ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button"></button>
                            <strong>Success!</strong> <?php echo $message ?>
                        </div>
                    <?php endforeach; ?>
<?php endif; ?>

            </div>
            <div class="col-md-12">
                <div class="portlet-body">
                    <!--BEGIN TABS-->
                    <div class="tabbable tabbable-custom tabbable-full-width">
                        <div class="row">
                            <div class="col-md-12">
                                <!-- CONTENT TAB HERE -->
                                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                                <div class="tabbable tabbable-custom tabbable-full-width">
                                    <ul class="nav nav-tabs">
                                        <li>
                                            <a href="<?php echo site_url($this->lang->lang() . '/settings') ?>">
                                            <?php echo lang('setting_tab_general') ?> </a>
                                        </li>
                                        <li class="active">
                                            <a href="#">
                                            <?php echo lang('setting_tab_media') ?> </a>
                                        </li>
                                    </ul>

                                    <div class="tab-content">
                                        <div id="tab_1_2" class="tab-pane active">
                                            <div class="portlet box blue-hoki">
                                                <div class="portlet-title">
                                                    <div class="caption">
                                                        <i class="fa fa-globe"></i> <?php echo 'Result Records' ?>
                                                    </div>
                                                    <div class="tools">
                                                    </div>
                                                </div>

                                                <div class="portlet-body">

                                                        <?php if (!empty($records)): $i = 1; ?>

                                                        <table class="table table-striped table-bordered table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Image</th>
                                                                    <th>Name</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                    <?php foreach ($records as $k => $record): ?>
                                                                    <tr>                                                                       
                                                                        <td><img alt="" src="<?php echo CMSHelper::output_media($record) ?>" width="75px"></td>
                                                                        <td><?php echo str_replace('_', ' ', $k) ?></td>
                                                                        <td><a href="<?php echo site_url('settings/media/edit/' . $i++); ?>" class="btn default btn-xs green-meadow"><i class="fa fa-edit"></i> Edit</a></td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                        <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

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