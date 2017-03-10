<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pet_types extends Admin_Controller {

    var $data = array();
    var $module = 'pets.pet_types';

    function __construct() {
        parent::__construct();

        $this->lang->load(array('pets', 'pet_types'));
        $this->load->model(array('pet_types_model', 'pets_model', 'users/permissions_model'));
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
            $this->data['total'] = $this->pet_types_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->pet_types_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->pet_types_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);
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
                    if ($this->pets_model->check_relationship($id)) {
                        $this->messages->add(lang('pet_type_delete_action_fail', $id), "error");
                        log_message('debug', 'deleted fail pet type id:' . $id);
                    } else {
                        $result = $this->pet_types_model->delete($id);
                        if ($result) {
                            $this->messages->add(lang('pet_type_delete_action_success', $id), "success");
                            log_message('message', 'deleted successful pet type id:' . $id);
                        }
                    }
                }
                redirect($this->lang->lang() . '/pets/pet_types/index');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('pet_type_header');
        $this->render_page('pet_types/index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post()) {
                $id = $this->_save_pet_type();
                if ($id) {
                    $this->messages->add(lang('pet_type_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/pets/pet_types');
                    } else {
                        redirect($this->lang->lang() . '/pets/pet_types/edit/' . $id);
                    }
                } else {
                    $this->messages->add(lang('pet_type_create_action_fail'), "error");
                }
            }

            $this->_assetForm();
            $this->page_title = lang('pet_type_header');
            $this->render_page('pet_types/create');
        }
    }

    public function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid member id
                $this->messages->add(lang('pet_type_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/pet_types/index');
            }
            if ($this->input->post()) {
                if ($this->_save_pet_type('update', $id)) {
                    $this->messages->add(lang('pet_type_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/pets/pet_types/edit/' . $id);
                } else {
                    $this->messages->add(lang('pet_type_edit_action_fail', $id), "error");
                }
            }
            //member data
            $this->data['record'] = $this->pet_types_model->detail($id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('pet_type_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/pet_types/index');
            }

            $this->_assetForm();
            $this->page_title = lang('pet_type_header');
            $this->render_page('pet_types/edit', $this->data);
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
                    'order' => $this->input->post('order'),
                    'status' => $this->input->post('status'),
        );

        $this->form_validation->set_rules('name', lang('pet_type_name'), 'required');
        $this->form_validation->set_rules('description', lang('pet_type_description'), 'required');
        $this->form_validation->set_rules('order', lang('pet_type_order'), 'integer');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if ($type == 'insert') {
            if ($this->form_validation->run() == false) {
                $this->page_title = lang('pet_type_header');
                $this->render_page('pet_types/create', $this->data);
            } else {
                // insert category data
                $id = $this->pet_types_model->insert($this->data['record']);
                if ($id) {
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == false) {
                $this->data['record'] = $this->pet_types_model->detail($id);
                $this->page_title = lang('pet_type_header');
                $this->render_page('pet_types/edit', $this->data);
            } else {
                // update category data
                if ($this->pet_types_model->update($id, $this->data['record'])) {
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
            // '../js/pet_types/pet_types.js',
            // '../js/pet_types/components-form-tools.js',
            // '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            // '../global/plugins/bootstrap-select/bootstrap-select.min.js',
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
        //     'ComponentsDateTimePickers.init();',
        // );
    }

}
