<?php

class Activity extends Admin_Controller {

    var $data = array();
    var $module = 'business.activity';

    function __construct() {
        parent::__construct();
        $this->lang->load(array('business'));
        $this->load->library(array('messages'));
        $this->load->model(array('activity_model',));
        $this->load->helper(array('url', 'language', 'permission'));
        $this->lang->load('reviews');
    }

    function index($business_id= false) {
        if(!$this->ion_auth->is_admin())
        {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        }
        else
        {
            $keyword1 = array();
            $data['keyword1'] = $this->input->get('keyword1') ? $this->input->get('keyword1') : '';
            $data['startDate'] = $this->input->get('startDate') ? $this->input->get('startDate') : '';
            $data['endDate'] = $this->input->get('endDate') ? $this->input->get('endDate') : '';
            $data['action'] = $this->input->get('action') ? $this->input->get('action') : '';
            if(isset($data['keyword1']) && !empty($data['keyword1'])){
                $keyword1 = ["actor_name"=>$data['keyword1']];
            }
            if(isset($data['startDate']) && isset($data['endDate']) && !empty($data['startDate']) && !empty($data['endDate'])){
                $keyword1['startDate'] = $data['startDate'];
                $keyword1['endDate'] = $data['endDate'];
            }
            if(isset($data['endDate']) && empty($data['startDate']) && !empty($data['endDate'])){
                $keyword1['startDate'] = "1970-01-01";
                $keyword1['endDate'] = $data['endDate'];
            }
            if(isset($data['startDate']) && empty($data['endDate']) && !empty($data['startDate'])){
                $keyword1['startDate'] = $data['startDate'];
                $keyword1['endDate'] = date("Y-m-d");
            }
            if(isset($data['action'])){
                $keyword1['action'] = $data['action'];
            }

            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            $array_field = array('id', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;
            $status = 1;
            //get data
            $this->data['total'] = $this->activity_model->getItems('total', $status, $keyword1, false, false, $limit, $offset,$business_id);
            $this->data['records'] = $this->activity_model->getItems('list', $status, $keyword1, $order_field, $sort, $limit, $offset,$business_id);
            $this->data['count'] = $this->activity_model->getItems('count_list', $status, $keyword1, $order_field, $sort, $limit, $offset,$business_id);
            
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
            $this->data['keyword'] = $keyword1;
            //$this->data['permissions'] = $this->permissions_model->get_permissions_user($this->session->userdata('user_id'));
            $this->data['module'] = $this->module;
            $this->data['is_admin'] = $this->ion_auth->is_admin();
        }
        //set asset
        $this->_assetIndex();
        if($business_id){
            $this->data['business_id'] = $business_id;
            $this->render_page('activity', $this->data);
        }
        else $this->render_page('activity/index', $this->data);
    }

    function review($review_id= false) {
        if(!$this->ion_auth->is_admin())
        {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        }
        else
        {
            $status = 1;
            $keyword1 = array();
            $data['keyword1'] = $this->input->get('keyword1') ? $this->input->get('keyword1') : '';
            $data['startDate'] = $this->input->get('startDate') ? $this->input->get('startDate') : '';
            $data['endDate'] = $this->input->get('endDate') ? $this->input->get('endDate') : '';
            $data['action'] = $this->input->get('action') ? $this->input->get('action') : '';
            if(isset($data['keyword1']) && !empty($data['keyword1'])){
                $keyword1 = ["actor_name"=>$data['keyword1']];
            }
            if(isset($data['startDate']) && isset($data['endDate']) && !empty($data['startDate']) && !empty($data['endDate'])){
                $keyword1['startDate'] = $data['startDate'];
                $keyword1['endDate'] = $data['endDate'];
            }
            if(isset($data['endDate']) && empty($data['startDate']) && !empty($data['endDate'])){
                $keyword1['startDate'] = "1970-01-01";
                $keyword1['endDate'] = $data['endDate'];
            }
            if(isset($data['startDate']) && empty($data['endDate']) && !empty($data['startDate'])){
                $keyword1['startDate'] = $data['startDate'];
                $keyword1['endDate'] = date("Y-m-d");
            }
            if(isset($data['action'])){
                $keyword1['action'] = $data['action'];
            }

            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            $array_field = array('id', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            //get data
            $this->data['total'] = $this->activity_model->getItems('total', $status, $keyword1, false, false, $limit, $offset,$review_id);
            $this->data['records'] = $this->activity_model->getItems('list', $status, $keyword1, $order_field, $sort, $limit, $offset,$review_id);
            $this->data['count'] = $this->activity_model->getItems('count_list', $status, $keyword1, $order_field, $sort, $limit, $offset,$review_id);

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
            $this->data['keyword'] = $keyword1;
            //$this->data['permissions'] = $this->permissions_model->get_permissions_user($this->session->userdata('user_id'));
            $this->data['module'] = $this->module;
            $this->data['is_admin'] = $this->ion_auth->is_admin();
        }
        //set asset
        $this->_assetIndex();
        if($review_id){
            $this->data['review_id'] = $review_id;
            $this->render_page('reviews/activity', $this->data);
        }
        else $this->render_page('activity/index', $this->data);
    }

    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetIndex(){
        $this->assets_css['page_style'] = array(
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            '../pages/scripts/components-date-time-pickers.min.js',
            //'../js/users/users.js',
            '../js/custom/custom.js',
        );

        $this->js_domready = array(
            // 'Metronic.init();', // init metronic core components
            // 'Layout.init();', // init current layout
            // 'QuickSidebar.init();', // init quick sidebar
            // 'Demo.init();', // init demo features'
            // 'TableAdvancedCustom.init();',
            //'Users.init();',
            // 'ComponentsDateTimePickers.init();',
            'Custom.init();'
        );
    }
}