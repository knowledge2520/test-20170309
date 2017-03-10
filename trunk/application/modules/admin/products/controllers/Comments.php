<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Comments extends Admin_Controller {

    var $data = array();
    var $module = 'products.comments';

    function __construct() {
        parent::__construct();

        $this->lang->load(array('comments', 'products'));
        $this->load->model(array('auth/ion_auth_model', 'comments_model', 'users/permissions_model'));
        $this->load->library(array('ion_auth', 'messages'));
        $this->load->helper(array('url', 'language', 'permission'));

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
    }

    function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = $this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            //get data
            $this->data['total'] = $this->comments_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->comments_model->getItems('list', $status, $keyword, 'c.id', 'DESC', $limit, $offset);
            $this->data['count'] = $this->comments_model->getItems('count_list', $status, $keyword, 'c.id', 'DESC', $limit, $offset);

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
            
            //
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $transaction) {
                    $this->data['records'][$k]->user = $this->comments_model->get_comment_user($transaction->user_id)->row();
                    $this->data['records'][$k]->product = $this->comments_model->get_comment_product($transaction->product_id)->row();
                }
            }
        }
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->comments_model->delete($id);
                }
                if ($result) {
                    $this->messages->add('deleted successful comment id:' . $id, "success");
                    log_message('message', 'deleted successful comment id:' . $id);
                } else {
                    $this->messages->add(lang('deleted fail comment id:') . $id, "error");
                    log_message('debug', 'deleted fail comment id:' . $id);
                }
                redirect($this->lang->lang() . '/products/comments/');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->render_page('comments/index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            //load categories
            if ($this->input->post()) {
                $id = $this->_save_categories();
                if ($id) {
                    $this->messages->add(lang('categories_create_action_success') . $id, "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/business/categories');
                    }
                    else{
                        redirect($this->lang->lang() . '/business/categories/edit/' . $id);
                    }                    
                } else {
                    $this->messages->add(lang('categories_create_action_fail') . $this->categories_model->error, "error");
                }
            }

            $this->_assetForm();
            $this->render_page('categories/create', $this->data);
        }
    }

    public function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid member id
                $this->messages->add(lang('categories_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/categories/index');
            }
            if ($this->input->post()) {
                if ($this->_save_categories('update', $id)) {
                    $this->messages->add(lang('categories_edit_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/categories/edit/' . $id);
                } else {
                    $this->messages->add(lang('categories_edit_action_fail') . $this->categories_model->error, "error");
                }
            }
            //member data
            $this->data['record'] = $this->categories_model->detail($id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('categories_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/categories/index');
            }

            $this->_assetForm();
            $this->render_page('comments/edit', $this->data);
        }
    }

    private function _save_product($type = 'insert', $id = 0) {
        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        //category data
        $data['name'] = $this->input->post('name');
        $data['description'] = $this->input->post('description');

        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if ($type == 'insert') {

            // insert category data
            $id = $this->categories_model->insert($data);
            if ($id) {
                $return = $id;
            }
        } elseif ($type == 'update') {

            // update category data
            if ($this->categories_model->update($id, $data)) {
                $return = $id;
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
