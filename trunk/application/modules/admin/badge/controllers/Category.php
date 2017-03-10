<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Category extends Admin_Controller {

    var $data = array();
    var $module = 'category';

    function __construct() {
        parent::__construct();

        $this->lang->load(array('badge_category'));
        $this->load->model(array('badge_category_model','badge_model','users/permissions_model'));
        $this->load->library('messages');
        $this->load->helper('permission');
    }

    public function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = array(0, 1);
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;
            
            $array_field = array('id', 'name', 'description', 'total');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->badge_category_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->badge_category_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->badge_category_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);
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

            $this->data['permissions'] = $this->permissions_model->get_permissions_user($this->session->userdata('user_id'));
            $this->data['module'] = $this->module;
            $this->data['is_admin'] = $this->ion_auth->is_admin();
        }

        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    if ($this->badge_model->check_relationship($id)) {
                        $this->messages->add(lang('badge_category_delete_action_fail', $id), "error");
                        log_message('debug', 'deleted failcategory id:' . $id);
                    } else {
                        $result = $this->badge_category_model->delete($id);
                        if ($result) {
                            $this->messages->add(lang('badge_category_delete_action_success', $id), "success");
                            log_message('message', 'deleted successful pet type id:' . $id);
                        }
                    }
                }
                redirect($this->lang->lang() . '/badge/category/index');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->render_page('/category/index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post()) {
                $id = $this->_save_pet_type();
                if ($id) {
                    $this->messages->add(lang('badge_category_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/badge/category');
                    } else {
                        redirect($this->lang->lang() . '/badge/category/edit/' . $id);
                    }
                } else {
                    $this->messages->add(lang('badge_category_create_action_fail'), "error");
                }
            }

            $this->_assetForm();
            $this->render_page('/category/create');
        }
    }

    public function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid member id
                $this->messages->add(lang('business_empty_data'), "error");
                redirect($this->lang->lang() . '/badge/category/index');
            }
            if ($this->input->post()) {
                if ($this->_save_pet_type('update', $id)) {
                    $this->messages->add(lang('pet_type_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/badge/category/edit/' . $id);
                } else {
                    $this->messages->add(lang('badge_category_edit_action_fail', $id), "error");
                }
            }
            //member data
            $this->data['record'] = $this->badge_category_model->detail($id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('business_empty_data'), "error");
                redirect($this->lang->lang() . '/badge/category/index');
            }

            $this->_assetForm();
            $this->render_page('/category/edit', $this->data);
        }
    }

    private function _save_pet_type($type = 'insert', $id = 0) {
        $return = false;
        // make sure we only pass in the fields we want
        //$data = array ();
        //category data
        $this->data['record'] = (object) array(
                    'name' => $this->input->post('name'),
                    'description' => $this->input->post('description'),
                    'status' => $this->input->post('status')
        );
        $this->form_validation->set_rules('name', lang('pet_type_name'), 'required');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if ($type == 'insert') {
            if ($this->form_validation->run() == false) {
                $this->render_page('/category/create', $this->data);
            } else {
                // insert category data
                $this->data['record']->created_date = strtotime(date("Y-m-d H:i:s"));
                $this->data['record']->modified_date = strtotime(date("Y-m-d H:i:s"));
                $id = $this->badge_category_model->insert($this->data['record']);
                if ($id) {
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == false) {
                $this->data['record'] = $this->badge_category_model->detail($id);
                $this->render_page('/category/edit', $this->data);
            } else {
                // update category data
                $this->data['record']->modified_date = strtotime(date("Y-m-d H:i:s"));
                if ($this->badge_category_model->update($id, $this->data['record'])) {
                    $return = $id;
                }
            }
        }
        return $return;
    }

    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetIndex() {
        $this->assets_css['page_style'] = array(
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
            '../global/plugins/datatables/datatables.min.css',
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/select2/js/select2.min.js',
            '../global/scripts/datatable.js',
            '../global/plugins/datatables/datatables.min.js',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
            '../js/custom/custom-table-advanced.js',
            '../js/custom/custom.js',
        );

        $this->js_domready = array(
            // 'App.init();', // init metronic core components
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
    private function _assetForm(){
        $this->assets_css['page_style'] = array(
            // '../global/plugins/select2/select2.css',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            // '../admin/pages/css/profile.css',
            // '../admin/pages/css/tasks.css',
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/select2/js/select2.min.js',
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            '../pages/scripts/components-date-time-pickers.min.js',

        );

        $this->js_domready = array(
            // 'Metronic.init();', // init metronic core components
            // 'Layout.init();', // init current layout
            // 'QuickSidebar.init();', // init quick sidebar
            // 'Demo.init();', // init demo features'
            // 'ComponentsFormTools.init();',
            // 'CustomConfirm.init();',
            'ComponentsDateTimePickers.init();',
        );
    }

}
