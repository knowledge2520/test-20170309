<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Size extends Admin_Controller {

    var $data = array();
    var $module = 'products';

    function __construct() {
        parent::__construct();

        $this->lang->load(array('categories', 'products'));
        $this->load->model(array('categories_model', 'products_model', 'color_model', 'size_model', 'users/permissions_model'));
        $this->load->library(array('messages'));
        $this->load->helper(array('url', 'language', 'permission'));
    }

    function index($product_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $this->data['record'] = $this->products_model->detail($product_id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('product_invalid_id'), "error");
                redirect($this->lang->lang() . '/products/index');
            } else {
                $product = $this->data['record'];
                $this->data['size'] = $this->size_model->get_size_product($product->id)->result();
            }
            // Deleting anything?
            if ($this->input->post('btn_delete')) {
                $checked = $this->input->post('checked');
                if (is_array($checked) && count($checked)) {

                    foreach ($checked as $id) {
                        $result = FALSE;

                        //check quantity have this color
                        $check_quantity_exist = $this->size_model->check_quantity_exist($id);
                        if (!$check_quantity_exist) {
                            $result = $this->size_model->delete($id);
                        }
                        if ($result) {
                            $this->messages->add(lang('product_size_delete_action_success', $id), "success");
                            //log_message('message', 'deleted successful product id:' . $id);
                        } else {
                            $this->messages->add(lang('product_size_delete_action_fail', $id), "error");
                            //log_message('debug', 'deleted fail product id:' . $id);
                        }
                    }
                    redirect($this->lang->lang() . '/products/size/index/' . $product_id);
                }
            }
            //set asset
            $this->_assetIndex();
            $this->render_page('size/index', $this->data);
        }
    }

    public function create($product_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post()) {
                $id = $this->_save_size();
                if ($id) {
                    $this->messages->add(lang('product_create_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/products/size/edit/' . $product_id . '/' . $id);
                } else {
                    $this->messages->add(lang('product_create_action_fail'), "error");
                }
            }
            $this->data['product_id'] = $product_id;
            $this->_assetForm();
            $this->render_page('size/create', $this->data);
        }
    }

    public function edit($product_id, $id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid member id
                $this->messages->add(lang('product_invalid_id'), "error");
                redirect($this->lang->lang() . '/products/index');
            }
            if ($this->input->post()) {
                if ($this->_save_size('update', $id)) {
                    $this->messages->add(lang('product_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/products/size/edit/' . $product_id . '/' . $id);
                } else {
                    $this->messages->add(lang('product_edit_action_fail', $id), "error");
                }
            }
            //product data
            $this->data['record'] = $this->size_model->detail($id);
            $this->data['product_id'] = $product_id;
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('product_invalid_id'), "error");
                redirect($this->lang->lang() . '/products/index');
            }

            $this->_assetForm();
            $this->render_page('size/edit', $this->data);
        }
    }

    private function _save_size($type = 'insert', $id = 0) {
        $return = false;

        // make sure we only pass in the fields we want
        $data = array();

        //product data
        $data['product_id'] = $this->input->post('product_id');
        $data['size'] = $this->input->post('size');
        $this->form_validation->set_rules('size', lang('product_size'), 'required');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if ($type == 'insert') {
            if ($this->form_validation->run() == FALSE) {
                $this->data['product_id'] = $this->input->post('product_id');
                $this->render_page('size/create', $this->data);
            } else {
                // insert category data
                $id = $this->size_model->insert($data);
                if ($id) {
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == FALSE) {
                $this->data['record'] = $this->size_model->detail($id);
                $this->data['product_id'] = $this->input->post('product_id');
                $this->render_page('size/edit', $this->data);
            } else {
                // update category data
                if ($this->size_model->update($id, $data)) {
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
            '../global/plugins/select2/select2.css',
            '../global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css',
            '../global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css',
            '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css'
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/select2/select2.min.js',
            '../global/plugins/datatables/media/js/jquery.dataTables.min.js',
            '../global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js',
            '../global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js',
            '../global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js',
            '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js',
            '../js/custom/custom-table-advanced.js',
            '../js/custom/custom.js',
        );

        $this->js_domready = array(
            'Metronic.init();', // init metronic core components
            'Layout.init();', // init current layout
            'QuickSidebar.init();', // init quick sidebar
            'Demo.init();', // init demo features'
            'TableAdvancedCustom.init();',
            'Custom.init();',
        );
    }

    /**
     * _assetEditForm
     *
     * file_name
     */
    private function _assetForm() {
        $this->assets_css['page_style'] = array(
            '../global/plugins/select2/select2.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../admin/pages/css/profile.css',
            '../admin/pages/css/tasks.css',
            '../global/plugins/jquery-multi-select/css/multi-select.css',
            '../global/plugins/bootstrap-select/bootstrap-select.min.css',
            '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
            '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css'
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/typeahead/typeahead.bundle.min.js',
            '../global/plugins/select2/select2.min.js',
            '../admin/pages/scripts/components-pickers.js',
            '../js/users/users.js',
            '../js/users/components-form-tools.js',
            '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            '../global/plugins/bootstrap-select/bootstrap-select.min.js',
            '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js',
            '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js',
        );

        $this->js_domready = array(
            'Metronic.init();', // init metronic core components
            'Layout.init();', // init current layout
            'QuickSidebar.init();', // init quick sidebar
            'Demo.init();', // init demo features'
            'ComponentsFormTools.init();',
            'ComponentsPickers.init();',
        );
    }

}
