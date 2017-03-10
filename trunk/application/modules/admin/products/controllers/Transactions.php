<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Transactions extends Admin_Controller {

    var $data = array();
    var $module = 'products.transactions';

    function __construct() {
        parent::__construct();

        $this->lang->load(array('ion_auth', 'categories', 'products', 'transactions'));
        $this->load->model(array('auth/ion_auth_model', 'categories_model', 'products_model', 'transactions_model', 'color_model', 'size_model', 'members/members_model'));
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
            $this->data['total'] = $this->transactions_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->transactions_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset);
            $this->data['count'] = $this->transactions_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset);

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
                    $this->data['records'][$k]->order = $this->transactions_model->get_transaction_order($transaction->order_id)->row();
                    $this->data['records'][$k]->product = $this->transactions_model->get_transaction_product($transaction->product_id)->row();
                    $options = unserialize($this->data['records'][$k]->options);
                    $this->data['records'][$k]->color = $this->color_model->detail($options[0]['color_id']);
                    $this->data['records'][$k]->size = $this->size_model->detail($options[0]['size_id']);
                    $this->data['records'][$k]->user = $this->members_model->detail($this->data['records'][$k]->order->user_id);
                }
            }
        }
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->transactions_model->delete($id);
                }
                if ($result) {
                    $this->messages->add('deleted successful transaction id:' . $id, "success");
                    log_message('message', 'deleted successful transaction id:' . $id);
                } else {
                    $this->messages->add(lang('deleted fail transaction id:') . $id, "error");
                    log_message('debug', 'deleted fail transaction id:' . $id);
                }
                redirect($this->lang->lang() . '/products/transactions/');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->render_page('transactions/index', $this->data);
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
