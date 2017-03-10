<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Quantity extends Admin_Controller {

    var $data = array();
    var $module = 'products';

    function __construct() {
        parent::__construct();

        $this->lang->load(array('products'));
        $this->load->model(array('quantity_model', 'products_model', 'color_model', 'size_model', 'users/permissions_model'));
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
                $status = false;
                $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

                $product = $this->data['record'];

                //get data
                $this->data['total'] = $this->quantity_model->getItems('total', $status, $keyword, false, false, $limit, $offset, $product->id);
                $this->data['records'] = $this->quantity_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, $product->id);

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
            }
        }
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->quantity_model->delete($id);

                    if ($result) {
                        $this->messages->add(lang('product_quantity_delete_action_success', $id), "success");
                        //log_message('message', 'deleted successful size id:'.$id);
                    } else {
                        $this->messages->add(lang('product_quantity_delete_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail size id:'.$id);
                    }
                }

                redirect($this->lang->lang() . '/products/quantity/index/' . $product_id);
            }
        }
        //set asset
        $this->_assetIndex();
        $this->render_page('quantity/index', $this->data);
    }

    public function create($product_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post()) {
                $id = $this->_save_quantity();
                if ($id) {
                    $this->messages->add(lang('product_quantity_create_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/products/quantity/edit/' . $product_id . '/' . $id);
                } else {
                    $this->messages->add(lang('product_quantity_create_action_fail'), "error");
                }
            }
            $this->data['color'] = $this->quantity_model->get_color($product_id);
            $this->data['size'] = $this->quantity_model->get_size($product_id);
            $this->data['product_id'] = $product_id;
            $this->_assetForm();
            $this->render_page('quantity/create', $this->data);
        }
    }

    public function edit($product_id, $id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$product_id) {
                //redirect if invalid member id
                $this->messages->add(lang('product_invalid_id'), "error");
                redirect($this->lang->lang() . '/products/index');
            }

            if ($this->input->post()) {
                if ($this->_save_quantity('update', $id)) {
                    $this->messages->add(lang('product_quantity_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/products/quantity/edit/' . $product_id . '/' . $id);
                } else {
                    $this->messages->add(lang('product_quantity_edit_action_fail', $id), "error");
                }
            }
            //product data
            $this->data['record'] = $this->quantity_model->detail($id);
            $this->data['color'] = $this->quantity_model->get_color($product_id);
            $this->data['size'] = $this->quantity_model->get_size($product_id);
            $this->data['product_id'] = $product_id;

            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('product_invalid_id'), "error");
                redirect($this->lang->lang() . '/products/index');
            }

            $this->_assetForm();
            $this->render_page('quantity/edit', $this->data);
        }
    }

    private function _save_quantity($type = 'insert', $id = 0) {
        $return = false;

        // make sure we only pass in the fields we want
        $data = array();

        //product data
        $data['product_id'] = $this->input->post('product_id');
        $data['color_id'] = $this->input->post('color_id');
        $data['size_id'] = $this->input->post('size_id');
        $data['quantity'] = $this->input->post('quantity');


        $this->form_validation->set_rules('color_id', lang('product_color'), 'required');
        $this->form_validation->set_rules('size_id', lang('product_size'), 'required');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');

        if ($type == 'insert') {
            $this->form_validation->set_rules('quantity', lang('product_quantity'), 'required|integer');
            if ($this->form_validation->run() == FALSE) {
                $this->data['record'] = (object) array(
                            'color_id' => $this->input->post('color_id'),
                            'size_id' => $this->input->post('size_id'),
                            'quantity' => $this->input->post('quantity'),
                );

                $this->data['color'] = $this->quantity_model->get_color($this->input->post('product_id'));
                $this->data['size'] = $this->quantity_model->get_size($this->input->post('product_id'));
                $this->data['product_id'] = $this->input->post('product_id');

                $this->render_page('quantity/create', $this->data);
            } else {
                $check_exist = $this->quantity_model->check_exist($data['color_id'], $data['size_id'], $data['product_id']);
                if ($check_exist) {
                    $this->data['record'] = (object) array(
                                'color_id' => $this->input->post('color_id'),
                                'size_id' => $this->input->post('size_id'),
                                'quantity' => $this->input->post('quantity'),
                    );

                    $this->data['color'] = $this->quantity_model->get_color($this->input->post('product_id'));
                    $this->data['size'] = $this->quantity_model->get_size($this->input->post('product_id'));
                    $this->data['product_id'] = $this->input->post('product_id');

                    $this->messages->add(lang('product_quantity_exist'), "error");
                } else {
                    $data['sell_quantity'] = 0;
                    // insert category data
                    $id = $this->quantity_model->insert($data);
                    if ($id) {
                        $return = $id;
                    }
                }
            }
        } elseif ($type == 'update') {
            $quantity = $this->quantity_model->detail($id);
            $validation_quantity = $quantity->quantity - 1;
            $validation_sell_quantity = $data['quantity'] + 1;
            $data['sell_quantity'] = $this->input->post('sell_quantity');

            $this->form_validation->set_rules('quantity', lang('product_quantity'), 'required|integer|greater_than[' . $validation_quantity . ']');
            $this->form_validation->set_rules('sell_quantity', lang('product_sell_quantity'), 'required|integer|less_than[' . $validation_sell_quantity . ']');
            if ($this->form_validation->run() == FALSE) {
                $this->data['record'] = (object) array(
                            'color_id' => $this->input->post('color_id'),
                            'size_id' => $this->input->post('size_id'),
                            'quantity' => $this->input->post('quantity'),
                            'sell_quantity' => $this->input->post('sell_quantity'),
                );

                $this->data['color'] = $this->quantity_model->get_color($this->input->post('product_id'));
                $this->data['size'] = $this->quantity_model->get_size($this->input->post('product_id'));
                $this->data['product_id'] = $this->input->post('product_id');
            } else {
                if ($quantity->color_id == $data['color_id'] && $quantity->size_id == $data['size_id']) {
                    // update category data
                    if ($this->quantity_model->update($id, $data)) {
                        $return = $id;
                    }
                } else {
                    $check_exist = $this->quantity_model->check_exist($data['color_id'], $data['size_id'], $data['product_id']);
                    if ($check_exist) {
                        $this->data['record'] = (object) array(
                                    'color_id' => $this->input->post('color_id'),
                                    'size_id' => $this->input->post('size_id'),
                                    'quantity' => $this->input->post('quantity'),
                                    'sell_quantity' => $this->input->post('sell_quantity'),
                        );

                        $this->data['color'] = $this->quantity_model->get_color($this->input->post('product_id'));
                        $this->data['size'] = $this->quantity_model->get_size($this->input->post('product_id'));
                        $this->data['product_id'] = $this->input->post('product_id');

                        $this->messages->add(lang('product_quantity_exist'), "error");
                    } else {
                        // update category data
                        if ($this->quantity_model->update($id, $data)) {
                            $return = $id;
                        }
                    }
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
