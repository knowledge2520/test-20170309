<?php

class Checkins extends Admin_Controller {

    var $data = array();
    var $module = 'members';

    function __construct() {
        parent::__construct();

        $this->load->library(array('messages'));
        $this->load->model(array('checkins_model', 'business/business_model', 'members_model', 'users/permissions_model'));
        $this->load->helper(array('url', 'language', 'permission'));
        $this->lang->load('checkins');
    }

    function index($member_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = $this->input->get('status') ? $this->input->get('status') : false;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            //get data
            $this->data['total'] = $this->checkins_model->getItems('total', $status, $keyword, false, false, $limit, $offset, $member_id);
            $this->data['total_deactivate'] = $this->checkins_model->getItems('total', 0, $keyword, false, false, $limit, $offset, $member_id);
            $this->data['records'] = $this->checkins_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id);
            $this->data['count'] = $this->checkins_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id);

            //pagination
            $this->load->library('pagination');
            $this->pager['base_url'] = current_url() . '?' . http_build_query($_GET);
            $this->pager['total_rows'] = $this->data['total'];
            $this->pager['per_page'] = $limit;
            $this->pager['page_query_string'] = TRUE;
            $this->pager['query_string_segment'] = 'per_page';
            $this->pager['first_url'] = current_url() . '?' . http_build_query($_GET) . '&' . $this->pager['query_string_segment'] . '=';
            //install pagination
            $this->pagination->initialize($this->pager);

            $this->data['paging'] = $this->pagination;
            $this->data['item_tableLength'] = $limit;

            //get user and member
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $checkin) {
                    $this->data['records'][$k]->business = $this->business_model->detail($checkin->business_id);
                }
            }
            $this->data['member_id'] = $member_id;
            // Deleting anything?
            if ($this->input->post('btn_delete')) {
                $checked = $this->input->post('checked');
                if (is_array($checked) && count($checked)) {
                    $result = FALSE;
                    foreach ($checked as $id) {
                        $result = $this->checkins_model->delete($id);

                        if ($result) {
                            $this->messages->add('deleted successful check in item id:' . $id, "success");
                            //log_message('message', 'deleted successful business item id:'.$id);
                        } else {
                            $this->messages->add(lang('deleted fail check in item id:'), "error");
                            //log_message('debug', 'deleted fail business item id:'.$id);
                        }
                    }

                    redirect($this->lang->lang() . '/members/checkins/index/' . $member_id);
                }
            }
            //set asset
            $this->_assetIndex();
            $this->page_title = lang('checkin_header');
            $this->render_page('checkins/index', $this->data);
        }
    }

    public function deactivate() {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            //get data
            $this->data['total'] = $this->checkins_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->checkins_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset);
            $this->data['count'] = $this->checkins_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset);

            //pagination
            $this->load->library('pagination');
            $this->pager['base_url'] = current_url() . '?' . http_build_query($_GET);
            $this->pager['total_rows'] = $this->data['total'];
            $this->pager['per_page'] = $limit;
            $this->pager['page_query_string'] = TRUE;
            $this->pager['query_string_segment'] = 'per_page';
            $this->pager['first_url'] = current_url() . '?' . http_build_query($_GET) . '&' . $this->pager['query_string_segment'] . '=';
            //install pagination
            $this->pagination->initialize($this->pager);

            $this->data['paging'] = $this->pagination;
            $this->data['item_tableLength'] = $limit;

            //get user and business
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $checkin) {
                    $this->data['records'][$k]->user = $this->users_model->detail($checkin->user_id);
                    $this->data['records'][$k]->business = $this->business_model->detail($checkin->business_id);
                }
            }

            // Deleting anything?
            if ($this->input->post('btn_delete')) {
                $checked = $this->input->post('checked');
                if (is_array($checked) && count($checked)) {
                    $result = FALSE;
                    foreach ($checked as $id) {
                        $result = $this->business_model->delete($id);
                    }
                    if ($result) {
                        $this->messages->add('deleted successful business item id:' . $id, "success");
                        log_message('message', 'deleted successful business item id:' . $id);
                    } else {
                        $this->messages->add(lang('deleted fail business item id:') . $this->ion_auth_model->error, "error");
                        log_message('debug', 'deleted fail business item id:' . $id);
                    }
                    redirect($this->lang->lang() . '/business/index');
                }
            }
            //set asset
            $this->_assetIndex();
            $this->page_title = lang('checkin_header');
            $this->render_page('checkins/deactivate', $this->data);
        }
    }

    public function edit($id) {
        if (!$id) {
            //redirect if invalid business id
            $this->messages->add(lang('business_invalid_id'), "error");
            redirect($this->lang->lang() . '/business/index');
        }
        if ($this->input->post()) {
            if ($this->_save_business('update', $id)) {
                $this->messages->add(lang('checkin_edit_action_success') . $id, "success");
                //redirect to edit page
                redirect($this->lang->lang() . '/business/checkins/edit/' . $id);
            } else {
                $this->messages->add(lang('checkin_edit_action_fail') . $id, "error");
            }
        }
        //business data
        $this->data['record'] = $this->checkins_model->detail($id);

        $this->_assetForm();
        $this->page_title = lang('checkin_header');
        $this->render_page('checkins/edit', $this->data);
    }

    private function _save_business($type = 'insert', $id = 0) {
        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        //category data
        $data['status'] = $this->input->post('status');

        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if ($type == 'insert') {
            //
        } elseif ($type == 'update') {
            // update category data
            if ($this->checkins_model->update($id, $data)) {
                $return = $id;
            }
            return $return;
        }
        return $return;
    }

    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetIndex() {
        $this->assets_css['page_style'] = array(
            // '../global/plugins/select2/select2.css',
            // '../global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css',
            // '../global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css',
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css',
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
            '../global/plugins/datatables/datatables.min.css',
        );
        $this->assets_js['page_plugin'] = array(
            // '../global/plugins/select2/select2.min.js',
            // '../global/plugins/datatables/media/js/jquery.dataTables.min.js',
            // '../global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js',
            // '../global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js',
            // '../global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js',
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js',
            // '../js/custom/custom-table-advanced.js',
            // '../js/custom/custom.js',
            '../global/plugins/select2/js/select2.min.js',
            '../global/scripts/datatable.js',
            '../global/plugins/datatables/datatables.min.js',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
            '../js/custom/custom-table-advanced.js',
            '../js/custom/custom.js',
        );

        $this->js_domready = array(
            // 'Metronic.init();', // init metronic core components
            // 'Layout.init();', // init current layout
            // 'QuickSidebar.init();', // init quick sidebar
            // 'Demo.init();', // init demo features'
            'TableAdvancedCustom.init();',
            'Custom.init();'
        );
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
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css',
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

    function do_upload($field_name, $path = '') {
        // Use "upload" library to select image, and image will store in root directory "uploads" folder.
        $config = array(
            'upload_path' => $this->config->item('admin_upload_path') . $path,
            'upload_url' => base_url() . $this->config->item('admin_upload_path') . $path,
            'allowed_types' => "gif|jpg|png|jpeg"
        );
        $this->load->library('upload', $config);
        // create folder
        if (!is_dir($config ['upload_path'])) {
            mkdir($config ['upload_path'], 0777, TRUE);
        }

        if ($this->upload->do_upload($field_name)) {
            //If image upload in folder, set also this value in "$image_data".
            $image_data = $this->upload->data();
            return $image_data;
        } else {
            $this->session->set_flashdata('error', lang('banner_upload_failure') . $this->upload->display_errors());
            return false;
        }
    }

}
