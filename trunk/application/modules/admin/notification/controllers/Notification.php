<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends Admin_Controller {

    var $data = array();
    var $module = 'notification';

    function __construct() {
        parent::__construct();

        $this->lang->load(array('notification'));
        $this->load->model(array('notification_model', 'users/permissions_model','users/users_model'));
        $this->load->library(array('ion_auth', 'messages'));
        $this->load->helper('permission', 'site');

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
    }

    function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post('options')) {
                $action = $this->input->post('options');
                if ($action == 'all') {
                    redirect('notification/all');
                    //$this->load->view('index');
                } else if($action == 'individual') {
                    redirect('notification/individual');
                }else if($action == 'country') {
                    redirect('notification/country');
                }else{
                    redirect('notification/pettype');
                }
            } else {

                $status = false;                    //$this->input->get('status') ? $this->input->get('status') : 0;
                $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
                $this->data['offset'] = $offset;
                
                $array_field = array('id', 'title', 'type');
                $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
                $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
                $this->data['order_field'] = $order_field;
                $this->data['sort'] = $sort;
                
                $this->data['txt_search_value'] = $keyword;
                //get data
                $this->data['total'] = $this->notification_model->getItems('total', $status, $keyword, $order_field, $sort, $limit, $offset);
                $this->data['records'] = $this->notification_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
                $this->data['count'] = $this->notification_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);

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

                // Deleting anything?
                if ($this->input->post('btn_delete')) {
                    $checked = $this->input->post('checked');
                    if (is_array($checked) && count($checked)) {
                        $result = FALSE;
                        foreach ($checked as $id) {
                            $result = $this->notification_model->delete_notification($id);
                            if ($result) {
                                $this->messages->add(lang('notification_delete_action_success', $id), "success");
                                //log_message('message', 'deleted successful cattegory id:' . $id);
                            } else {
                                $this->messages->add(lang('notification_delete_action_fail', $id), "error");
                                //log_message('debug', 'deleted fail cattegory id:' . $id);
                            }
                        }

                        redirect($this->lang->lang() . '/notification');
                    }
                }

                //set asset
                $this->_assetIndex();
                $this->page_title = lang('notification_header');
                $this->render_page('index', $this->data);
                //$this->load->view('index');
            }
        }
    }

    public function detail($id){
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid business id
                $this->messages->add(lang('notification_invalid_id'), "error");
                redirect($this->lang->lang() . '/notification/index');
            }
            
            //notification data
            $this->data['record'] = $this->notification_model->detail($id);
            $this->_assetForm();
            $this->page_title = lang('notification_header');
            $this->render_page('detail', $this->data);
        }
    }
    
    public function all() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post('submit') && $this->input->post('submit') == 'all') {
                $this->_send_push('all', $this->input->post());
            }

            $this->_assetIndex();
            $this->page_title = lang('notification_header');
            $this->render_page('all');
        }
    }

    public function country() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post('submit') && $this->input->post('submit') == 'country') {
                //echo 123;exit;
                $this->_send_push('country', $this->input->post());
            }

            $this->_assetForm();
            $this->data['countries'] = $this->users_model->loadCountry();
            $this->page_title = lang('notification_header');
            $this->render_page('country',$this->data);
        }
    }

    public function pettype() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post('submit') && $this->input->post('submit') == 'pettype') {
                $this->_send_push('pettype', $this->input->post());
            }

            $this->_assetForm();
            $this->data['pettypes'] = $this->notification_model->getPetType();
            $this->page_title = lang('notification_header');
            $this->render_page('pettype',$this->data);
        }
    }

    public function individual() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post('submit') && $this->input->post('submit') == 'individual') {
                $this->_send_push('individual', $this->input->post());
            }

            $status = $this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : 10;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = 0;//$this->notification_model->getItemsUser('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = [];//$this->notification_model->getItemsUser('list', $status, $keyword, 'id', 'ASC', $limit, $offset);
            $this->data['count'] = 0;//$this->notification_model->getItemsUser('count_list', $status, $keyword, 'id', 'ASC', $limit, $offset);

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

            //$this->data['records'] = $this->notification_model->get_users();
            $this->_assetForm();
            $this->page_title = lang('notification_header');
            $this->render_page('individual', $this->data);
        }
    }

    public function getUsers() {

        $query = $this->input->get('q') ?  $this->input->get('q') : "";
        $page = $this->input->get('page') ?  $this->input->get('page') : 1;

        $limit = 30;
        $offset = ($page - 1) * $limit;

        $response['items'] = $this->notification_model->getItemsUser('list', 0, $query, 'id', 'ASC', $limit, $offset);
        if(!$response['items']){
            $response['incomplete_results'] = false;
            $response['items'] = [];
        }

        $response['total_count'] = $this->notification_model->getItemsUser('total', 0, $query, false, false, $limit, $offset);
         // var_dump(($response));exit;
        echo json_encode($response);
        
    }

    private function _send_push($type = 'all', $data) {
        $this->load->model('members/members_model');
        $user               = format_output_data($this->members_model->detail($this->session->userdata('user_id')));
        $action_type        = $this->notification_model->get_action_type('SYSTEM_PUSH');
        $name_user_action   = $user->display_name;
        $message            = $this->input->post('message') ? $this->input->post('message') : '';
        $title              = $this->input->post('title') ? $this->input->post('title') : '';
        $countries          = $this->input->post('countries') ? $this->input->post('countries') : false;
        $pettypes           = $this->input->post('pettypes') ? $this->input->post('pettypes') : false;;

        $data_push = array(
            'action_type' => 'SYSTEM_PUSH',
            'sender_id' => $user->id,
            'sender_name' => $name_user_action,
            'profile_photo' => $user->profile_photo_thumb,
            'created_date' => date('d-m-Y H:i:s',now()),
            'type' => 'system',
            'bages_unread_notification' => 0
        );

        if ($type == 'all') {
            $this->form_validation->set_rules('title', lang('notification_title'), 'required');
            $this->form_validation->set_rules('message', lang('notification_message'), 'required');
            if ($this->form_validation->run($this) == false) {
                $this->messages->add(lang('notification_send_fail'), "error");
            } else {  
                $this->notification_model->send_push_all($title, $message, $data_push, $action_type->id, $name_user_action,'all',false);                
                $this->messages->add(lang('notification_send_success'), "success");
                redirect('notification', 'refresh');
            }
        } 
        elseif ($type == 'individual') {
            $this->form_validation->set_rules('title', lang('notification_title'), 'required');
            $this->form_validation->set_rules('message', lang('notification_message'), 'required');
            if ($this->form_validation->run($this) == false) {
                $this->messages->add(lang('notification_send_fail'), "error");
            } else {
                $checked = $this->input->post('checked');
                if (is_array($checked) && count($checked)) {
                    $result = FALSE;
                    foreach ($checked as $id) {
                        $data_push['bages_unread_notification'] = $this->notification_model->count_unread_notification($id) + 1;
                        $result = $this->notification_model->send_push($id, $title, $message, $data_push, $action_type->id, $name_user_action);
                        //$this->messages->add(lang('notification_send_success'), "success");
                        if ($result) {
                            $this->messages->add(lang('notification_send_success'), "success");
                        } else {
                            $this->messages->add(lang('notification_send_fail'), "error");
                        }
                    }
                    redirect('notification', 'refresh');
                }
            }
        } 
        else if ($type == 'country') {
            //$this->form_validation->set_rules('countries', "Countries", 'required');
            $this->form_validation->set_rules('title', lang('notification_title'), 'required');
            $this->form_validation->set_rules('message', lang('notification_message'), 'required');
            if ($this->form_validation->run($this) == false) {
                //echo json_encode($this->input->post());exit;
                $this->messages->add(lang('notification_send_fail'), "error");
            } else {
                $this->notification_model->send_push_all($title, $message, $data_push, $action_type->id, $name_user_action,'country', $countries);                
                $this->messages->add(lang('notification_send_success'), "success");
                redirect('notification', 'refresh');
            }
        }
        else if ($type == 'pettype') {
            //$this->form_validation->set_rules('pettypes', "Pet Type", 'required');
            $this->form_validation->set_rules('title', lang('notification_title'), 'required');
            $this->form_validation->set_rules('message', lang('notification_message'), 'required');
            if ($this->form_validation->run($this) == false) {
                $this->messages->add(lang('notification_send_fail'), "error");
            } else {
                $this->notification_model->send_push_all($title, $message, $data_push, $action_type->id, $name_user_action,'pettype',$pettypes);                
                $this->messages->add(lang('notification_send_success'), "success");
                redirect('notification', 'refresh');
            }
        }
        else {
            redirect('notification', 'refresh');
        }
    }

    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetIndex() {
        $this->assets_css['page_style'] = array(
            // //'../global/plugins/select2/select2.css',
            // '../global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css',
            // '../global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css',
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css',
            // '../plugin/select2/css/select2.min.css',
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
            '../global/plugins/datatables/datatables.min.css',
        );
        $this->assets_js['page_plugin'] = array(
            // //'../global/plugins/select2/select2.min.js',
            // '../global/plugins/datatables/media/js/jquery.dataTables.min.js',
            // '../global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js',
            // '../global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js',
            // '../global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js',
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js',
            // '../js/custom/custom-table-advanced.js',
            // '../js/custom/custom.js',
            // '../js/custom/select2.js',
            // '../plugin/select2/js/select2.full.min.js',
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
            // '../global/plugins/jquery-multi-select/css/multi-select.css',
            // '../global/plugins/select2/select2.css',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            // '../admin/pages/css/profile.css',
            // '../admin/pages/css/tasks.css',
            '../global/plugins/select2/css/select2.min.css',
            '../global/plugins/select2/css/select2-bootstrap.min.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
            '../global/plugins/jquery-multi-select/css/multi-select.css',
            '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
            '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css',
            '../global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',
            '../global/plugins/jquery-multi-select/css/multi-select.css',
        );
        $this->assets_js['page_plugin'] = array(
            // '../global/plugins/fuelux/js/spinner.min.js',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            // '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            // '../global/plugins/typeahead/typeahead.bundle.min.js',
            // '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            // '../global/plugins/select2/select2.min.js',
            // '../js/users/users.js',
            // '../js/users/components-form-tools.js'
            '../global/plugins/select2/js/select2.min.js',
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js',
            '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js',
            '../global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js',
            '../pages/scripts/components-date-time-pickers.min.js',
            '../js/custom/custom.js',
            '../js/custom/select2.js',
            '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
        );

        // $this->js_domready = array(
        //     'Metronic.init();', // init metronic core components
        //     'Layout.init();', // init current layout
        //     'QuickSidebar.init();', // init quick sidebar
        //     'Demo.init();', // init demo features'
        //     'ComponentsFormTools.init();',
        // );
    }

}
