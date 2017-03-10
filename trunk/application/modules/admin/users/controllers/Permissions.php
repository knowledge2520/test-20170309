<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Permissions extends Admin_Controller {

    var $data = array();
    var $module = 'users.permissions';
    
    function __construct() {
        parent::__construct();

        $this->lang->load(array('permissions', 'users'));
        $this->load->library('messages');
        $this->load->model(array('permissions_model', 'groups_model'));
    }

    function edit($id) {
        if (!$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error('You must be an administrator to view this page.');
        } else {
            $group = $this->groups_model->find_by('id', $id);
            if (!$group) {
                $this->messages->add(lang('group_invalid', $id), "error");
                redirect('users/groups');
            } else {
                if ($this->input->post()) {
                    if ($this->_save_permission('update', $id)) {
                        $this->messages->add(lang('permission_edit_action_success', $id), "success");
                    } else {
                        $this->messages->add(lang('permission_edit_action_fail', $id), "error");
                    }
                }

                $this->data['records'] = $this->permissions_model->get_permissions_group($id); //$this->permissions_model->get_permission($id);

                $this->data['permissions'] = $this->permissions_model->get_permissions_user($this->session->userdata('user_id'));
                $this->data['module'] = $this->module;
                $this->data['is_admin'] = $this->ion_auth->is_admin();

                $this->_assetForm();
                $this->page_title = lang('permissions_header');
                $this->render_page('permissions/edit', $this->data);
            }
        }
    }

    function _save_permission($type = 'insert', $id = 0) {
        $return = false;

        $data = $this->input->post();
        $this->permissions_model->update_permission($id, $data);
        return true;
    }

    /**
     * _assetEditForm
     *
     * file_name
     */
    private function _assetForm() {
        $this->assets_css['page_style'] = array(
            // '../global/plugins/select2/select2.css',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            // '../admin/pages/css/profile.css',
            // '../admin/pages/css/tasks.css',
            // '../global/plugins/jquery-multi-select/css/multi-select.css',
            // '../global/plugins/bootstrap-select/bootstrap-select.min.css',
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css'
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
        );
        $this->assets_js['page_plugin'] = array(
            // '../global/plugins/fuelux/js/spinner.min.js',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            // '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            // '../global/plugins/typeahead/typeahead.bundle.min.js',
            // '../global/plugins/select2/select2.min.js',
            // '../admin/pages/scripts/components-pickers.js',
            // '../js/users/users.js',
            // '../js/users/components-form-tools.js',
            // '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            // '../global/plugins/bootstrap-select/bootstrap-select.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js',
            '../global/plugins/select2/js/select2.min.js',
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            '../pages/scripts/components-date-time-pickers.min.js',
        );

        // $this->js_domready = array(
        //     'Metronic.init();', // init metronic core components
        //     'Layout.init();', // init current layout
        //     'QuickSidebar.init();', // init quick sidebar
        //     'Demo.init();', // init demo features'
        //     'ComponentsFormTools.init();',
        //     'ComponentsPickers.init();',
        //     'TableAdvanced.init();',
        // );
    }

}
