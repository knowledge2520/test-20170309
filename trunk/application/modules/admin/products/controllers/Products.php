<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends Admin_Controller {

    var $data = array();
    var $module = 'products';

    function __construct() {
        parent::__construct();

        $this->lang->load(array('categories', 'products'));
        $this->load->model(array('categories_model', 'products_model', 'color_model', 'size_model', 'users/permissions_model'));
        $this->load->library(array('messages'));
        $this->load->helper(array('url', 'language', 'permission'));
    }

    function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = 1;        //$this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->products_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->products_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset);
            $this->data['count'] = $this->products_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset);

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
            
            //list the category
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $product) {
                    $this->data['records'][$k]->category = $this->products_model->get_product_category($product->category_id)->row();
                }
            }
        }
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->products_model->deleteProduct($id);
                }
                if ($result) {
                    $this->messages->add(lang('product_delete_action_success', $id), "success");
                    //log_message('message', 'deleted successful product id:'.$id);
                } else {
                    $this->messages->add(lang('product_delete_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail product id:'.$id);
                }
                redirect($this->lang->lang() . '/products/');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->render_page('index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            //load categories
            $this->data['categories'] = $this->categories_model->find_all();

            //check file upload
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) &&
                    empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
                $displayMaxSize = ini_get('post_max_size');

                switch (substr($displayMaxSize, -1)) {
                    case 'G':
                        $displayMaxSize = $displayMaxSize * 1024;
                    case 'M':
                        $displayMaxSize = $displayMaxSize * 1024;
                    case 'K':
                        $displayMaxSize = $displayMaxSize * 1024;
                }

                $error = 'Posted data is too large. ' .
                        $_SERVER['CONTENT_LENGTH'] .
                        ' bytes exceeds the maximum size of ' .
                        $displayMaxSize . ' bytes.';
                $this->messages->add($error, "error");
            }

            if ($this->input->post()) {
                $id = $this->_save_product();
                if ($id) {
                    $this->messages->add(lang('product_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/products');
                    }
                    else{
                        redirect($this->lang->lang() . '/products/edit/' . $id);
                    }                    
                } else {
                    $this->messages->add(lang('product_create_action_fail') . $id, "error");
                }
            }
            $this->_assetForm();
            $this->render_page('create', $this->data);
        }
    }

    public function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid member id
                $this->messages->add(lang('product_invalid_id'), "error");
                redirect($this->lang->lang() . '/products/index');
            }

            //check file upload
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) &&
                    empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
                $displayMaxSize = ini_get('post_max_size');

                switch (substr($displayMaxSize, -1)) {
                    case 'G':
                        $displayMaxSize = $displayMaxSize * 1024;
                    case 'M':
                        $displayMaxSize = $displayMaxSize * 1024;
                    case 'K':
                        $displayMaxSize = $displayMaxSize * 1024;
                }

                $error = 'Posted data is too large. ' .
                        $_SERVER['CONTENT_LENGTH'] .
                        ' bytes exceeds the maximum size of ' .
                        $displayMaxSize . ' bytes.';
                $this->messages->add($error, "error");
            }

            if ($this->input->post()) {
                if ($this->_save_product('update', $id)) {
                    $this->messages->add(lang('product_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/products/edit/' . $id);
                } else {
                    $this->messages->add(lang('product_edit_action_fail', $id), "error");
                }
            }
            //product data
            $this->data['record'] = $this->products_model->detail($id);
            $this->data['categories'] = $this->categories_model->find_all();
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('product_invalid_id'), "error");
                redirect($this->lang->lang() . '/products/index');
            } else {
                $product = $this->data['record'];
                $this->data['color'] = $this->color_model->get_color_product($product->id)->result();
                $this->data['size'] = $this->size_model->get_size_product($product->id)->result();
            }
            $this->data['tab'] = 'general';
            $this->_assetForm();
            $this->render_page('edit', $this->data);
        }
    }

    private function _save_product($type = 'insert', $id = 0) {

        $this->load->library('image_lib');

        $return = false;
        // make sure we only pass in the fields we want
        $data = array();
        //product data
        $data['name'] = $this->input->post('name');
        $data['description'] = $this->input->post('description');
        $data['category_id'] = $this->input->post('category');
        $data['price'] = $this->input->post('price');
        $data['cost'] = $this->input->post('cost');
        $data['price_on_sale'] = $this->input->post('price_on_sale');
        $data['stock'] = $this->input->post('stock');
        $data['sort'] = $this->input->post('sort');
        $data['status'] = $this->input->post('status');
        if ($this->input->post('free_shipping') != NULL) {
            $data['free_shipping'] = 1;
        } else {
            $data['free_shipping'] = 0;
        }

        $this->form_validation->set_rules('name', lang('product_name'), 'required');
        $this->form_validation->set_rules('stock', lang('product_stock'), 'required|integer');
        $this->form_validation->set_rules('sort', lang('product_sort'), 'required|integer');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if ($type == 'insert') {
            if ($this->form_validation->run() == FALSE) {
                //load categories
                $this->data['categories'] = $this->categories_model->find_all();
                $this->render_page('create', $this->data);
            } else {
                // upload photo
                $this->load->helper('upload');
                $image_data = do_upload($this->config->item('product_path'), 'photo');
                if ($image_data) {
                    // saving image
                    $data['photo'] = $this->config->item('admin_upload_path') . $this->config->item('product_path') . $image_data['file_name'];

                    //create photo thumb
                    $this->load->helper('image');
                    resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                    $file_name = explode('.', $image_data['file_name']);
                    $data['photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('product_path') . $file_name[0] . '_thumb.' . $file_name[1];

                    // insert category data
                    $id = $this->products_model->insert($data);
                    if ($id) {
                        $return = $id;
                    }
                } else {
                    $this->messages->add(lang('upload_failure') . ' ' . $this->upload->display_errors(), 'error');
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == FALSE) {
                //product data
                $this->data['record'] = $this->products_model->detail($id);
                $this->data['categories'] = $this->categories_model->find_all();
                $this->render_page('edit', $this->data);
            } else {
                // upload photo
                $product_image = $_FILES['photo']['name'];
                if (isset($product_image) && $product_image != '') {

                    $this->load->helper('upload');
                    $image_data = do_upload($this->config->item('product_path'), 'photo');
                    if ($image_data) {
                        // saving image
                        $data['photo'] = $this->config->item('admin_upload_path') . $this->config->item('product_path') . $image_data['file_name'];

                        //create photo thumb
                        $this->load->helper('image');
                        resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                        $file_name = explode('.', $image_data['file_name']);
                        $data['photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('product_path') . $file_name[0] . '_thumb.' . $file_name[1];

                        // update category data
                        if ($this->products_model->update($id, $data)) {
                            $return = $id;
                        }
                    } else {
                        $this->messages->add(lang('upload_failure') . ' ' . $this->upload->display_errors(), 'error');
                    }
                } else {
                    // update category data
                    if ($this->products_model->update($id, $data)) {
                        $return = $id;
                    }
                }
            }
        }
        return $return;
    }

    function do_upload($field_name, $path = '') {
        // Use "upload" library to select image, and image will store in root directory "uploads" folder.
        $config = array(
            'upload_path' => $this->config->item('admin_upload_path') . $this->config->item('product_path') . $path,
            'upload_url' => base_url() . $this->config->item('admin_upload_path') . $this->config->item('product_path') . $path,
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
            $this->session->set_flashdata('error', lang('product_upload_failure') . $this->upload->display_errors());
            return false;
        }
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
