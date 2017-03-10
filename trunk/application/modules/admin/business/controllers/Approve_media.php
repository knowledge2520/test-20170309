<?php

class Approve_media extends Admin_Controller {

    var $data = array();
    var $module = 'business.approve_media';

    function __construct() {
        parent::__construct();

        $this->load->library(array('ion_auth', 'messages'));
        $this->load->helper(array('url', 'language', 'site'));

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load(array('ion_auth', 'business'));
        $this->load->model(array('auth/ion_auth_model', 'business_model', 'categories_model', 'media_model', 'users/permissions_model'));
        $this->load->helper('permission');
    }

    public function index() {

        //$status 		= $this->input->get('status') ? $this->input->get('status') : 0;
        $status = array(0, 3);
        $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
        $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
        $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
        
        $this->data['txt_search_value'] = $keyword;
        //get data
        $this->data['total'] = $this->media_model->getItems('total', $status, $keyword, false, false, $limit, $offset, false, false, false, false, 'business');
        $this->data['records'] = $this->media_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, false, false, false, false, 'business');
        $this->data['count'] = $this->media_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, false, false, false, false, 'business');

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

        $this->data['count_approve'] = array(
            'business' => $this->media_model->count_approve_media('business'),
            'tip' => $this->media_model->count_approve_media('tip'),
            'review' => $this->media_model->count_approve_media('review'),
        );
        
        //list the categories
        if ($this->data['records']) {
            foreach ($this->data['records'] as $k => $media) {
                $this->data['records'][$k]->categories = $this->business_model->get_business_categories($media->business_id)->result();
            }
        }
            
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->media_model->delete_media($id);
                    $result = $this->media_model->delete($id);
                }
                if ($result) {
                    $this->messages->add(lang('business_media_delete_action_success', $id), "success");
                    //log_message('message', 'deleted successful media id:'.$id);
                } else {
                    $this->messages->add(lang('business_media_delete_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail media id:'.$id);
                }
                redirect($this->lang->lang() . '/business/approve_media/');
            }
        }
        
         // Active anything?
        if ($this->input->post('btn_active')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->media_model->active_media($id);
                }
                if ($result) {
                    $this->messages->add(lang('business_media_approve_action_success', $id), "success");
                    //log_message('message', 'deleted successful media id:'.$id);
                } else {
                    $this->messages->add(lang('business_media_approve_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail media id:'.$id);
                }
                redirect($this->lang->lang() . '/business/approve_media/');
            }
        }
        
        // Reject anything?
        if ($this->input->post('btn_reject')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->media_model->reject_media($id);
                }
                if ($result) {
                    $this->messages->add(lang('business_media_reject_action_success', $id), "success");
                    //log_message('message', 'deleted successful media id:'.$id);
                } else {
                    $this->messages->add(lang('business_media_reject_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail media id:'.$id);
                }
                redirect($this->lang->lang() . '/business/approve_media/');
            }
        }
        
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('business_media_header');
        $this->render_page('media/business', $this->data);
    }

    public function tip(){
        //$status 		= $this->input->get('status') ? $this->input->get('status') : 0;
        $status = array(0, 3);
        $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
        $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
        $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
        
        $this->data['txt_search_value'] = $keyword;
        //get data
        $this->data['total'] = $this->media_model->getItems('total', $status, $keyword, false, false, $limit, $offset, false, false, false, false, 'tip');
        $this->data['records'] = $this->media_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, false, false, false, false, 'tip');
        $this->data['count'] = $this->media_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, false, false, false, false, 'tip');

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

        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->media_model->delete($id);
                }
                if ($result) {
                    $this->messages->add(lang('business_media_delete_action_success', $id), "success");
                    //log_message('message', 'deleted successful media id:'.$id);
                } else {
                    $this->messages->add(lang('business_media_delete_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail media id:'.$id);
                }
                redirect($this->lang->lang() . '/business/approve_media/tip');
            }
        }
        $this->data['count_approve'] = array(
            'business' => $this->media_model->count_approve_media('business'),
            'tip' => $this->media_model->count_approve_media('tip'),
            'review' => $this->media_model->count_approve_media('review'),
        );
         // Active anything?
        if ($this->input->post('btn_active')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->media_model->active_media($id);
                }
                if ($result) {
                    $this->messages->add(lang('business_media_active_action_success', $id), "success");
                    //log_message('message', 'deleted successful media id:'.$id);
                } else {
                    $this->messages->add(lang('business_media_active_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail media id:'.$id);
                }
                redirect($this->lang->lang() . '/business/approve_media/tip');
            }
        }
        
        // Reject anything?
        if ($this->input->post('btn_reject')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->media_model->reject_media($id);
                }
                if ($result) {
                    $this->messages->add(lang('business_media_reject_action_success', $id), "success");
                    //log_message('message', 'deleted successful media id:'.$id);
                } else {
                    $this->messages->add(lang('business_media_reject_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail media id:'.$id);
                }
                redirect($this->lang->lang() . '/business/approve_media/');
            }
        }
        
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('business_media_header');
        $this->render_page('media/tip', $this->data);
    }
    
    public function review(){
        //$status 		= $this->input->get('status') ? $this->input->get('status') : 0;
        $status = array(0, 3);
        $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
        $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
        $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
        
        $this->data['txt_search_value'] = $keyword;
        //get data
        $this->data['total'] = $this->media_model->getItems('total', $status, $keyword, false, false, $limit, $offset, false, false, false, false, 'review');
        $this->data['records'] = $this->media_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, false, false, false, false, 'review');
        $this->data['count'] = $this->media_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, false, false, false, false, 'review');

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
        
        $this->data['count_approve'] = array(
            'business' => $this->media_model->count_approve_media('business'),
            'tip' => $this->media_model->count_approve_media('tip'),
            'review' => $this->media_model->count_approve_media('review'),
        );
        
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->media_model->delete($id);
                }
                if ($result) {
                    $this->messages->add(lang('business_media_delete_action_success', $id), "success");
                    //log_message('message', 'deleted successful media id:'.$id);
                } else {
                    $this->messages->add(lang('business_media_delete_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail media id:'.$id);
                }
                redirect($this->lang->lang() . '/business/approve_media/review');
            }
        }
        
         // Active anything?
        if ($this->input->post('btn_active')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->media_model->active_media($id);
                }
                if ($result) {
                    $this->messages->add(lang('business_media_active_action_success', $id), "success");
                    //log_message('message', 'deleted successful media id:'.$id);
                } else {
                    $this->messages->add(lang('business_media_active_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail media id:'.$id);
                }
                redirect($this->lang->lang() . '/business/approve_media/review');
            }
        }
        
        // Reject anything?
        if ($this->input->post('btn_reject')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->media_model->reject_media($id);
                }
                if ($result) {
                    $this->messages->add(lang('business_media_reject_action_success', $id), "success");
                    //log_message('message', 'deleted successful media id:'.$id);
                } else {
                    $this->messages->add(lang('business_media_reject_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail media id:'.$id);
                }
                redirect($this->lang->lang() . '/business/approve_media/');
            }
        }
        
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('business_media_header');
        $this->render_page('media/review', $this->data);
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

}
