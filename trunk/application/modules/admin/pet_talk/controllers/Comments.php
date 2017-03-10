<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Comments extends Admin_Controller {

    var $data = array();
    var $module = 'pet_talk.comments';

    function __construct() {
        parent::__construct();

        $this->lang->load(array('comments'));
        $this->load->model(array('comments_model', 'pet_talk_model', 'pet_post_updated_model','pet_talk_info_model','users/users_model', 'users/permissions_model'));
        $this->load->library(array('ion_auth', 'messages'));
        $this->load->helper('permission');

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
    }

    function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = array(0,1);//$this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;
            
            $array_field = array('id', 'topic_name', 'user_name', 'content', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->comments_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->comments_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->comments_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);

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
        
        //set status
        if($this->input->post('set_status')){            
            $id = $this->input->post('id');
            $status = $this->input->post('status');
            
            $this->comments_model->update_status($id, $status);
            redirect($this->input->post('url'), 'refresh');
            die();
        }
        
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->comments_model->delete_comment($id);
                    if ($result) {
                        $this->messages->add(lang('comment_delete_action_success', $id), "success");
                        //log_message('message', 'deleted successful comment id:' . $id);
                    } else {
                        $this->messages->add(lang('comment_delete_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail comment id:' . $id);
                    }
                }

                redirect($this->lang->lang() . '/pet_talk/comments');
            }
        }
        
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('comment_header');
        $this->render_page('comments/index', $this->data);
    }
    
    public function create($topic_id, $type = 'topic') {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if($type == 'topic'){
                if (!$topic_id || !$this->pet_talk_model->detail($topic_id)) {
                    //redirect if invalid member id
                    $this->messages->add(lang('pet_talk_invalid_id'), "error");
                    redirect(site_url('/pet_talk'));
                }

                if ($this->input->post()) {
                    $id = $this->_save_comment('insert', false, $type);
                    if ($id) {
                        $this->messages->add(lang('comment_create_action_success', $id), "success");
                        //redirect to edit page
                        redirect($this->lang->lang() . '/pet_talk/comments/edit/' . $id);
                    } else {
                        $this->messages->add(lang('comment_create_action_fail'), "error");
                    }
                }
                $this->data['topic'] = $this->pet_talk_model->detail($topic_id);
                $this->data['topic_name'] = $this->data['topic']->title;
            }else{
                if (!$topic_id || !$this->pet_talk_info_model->detail($topic_id)) {
                    //redirect if invalid member id
                    $this->messages->add(lang('pet_talk_invalid_id'), "error");
                    redirect(site_url('/pet_talk/pet_talk_info'));
                }

                if ($this->input->post()) {
                    $id = $this->_save_comment('insert', false, $type);
                    if ($id) {
                        $this->messages->add(lang('comment_create_action_success', $id), "success");
                        //redirect to edit page
                        redirect($this->lang->lang() . '/pet_talk/comments/edit/' . $id. '/info');
                    } else {
                        $this->messages->add(lang('comment_create_action_fail'), "error");
                    }
                }
                $this->data['topic'] = $this->pet_talk_info_model->detail($topic_id);
                $this->data['topic_name'] = $this->data['topic']->name;
            }
            
            $this->_assetForm();
            $this->page_title = lang('comment_header');
            $this->render_page('comments/create', $this->data);
        }
    }
    
    public function add($newsfeed_id = false) {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
        
            if (!$newsfeed_id) {
                //redirect if invalid member id
                $this->messages->add(lang('pet_talk_invalid_id'), "error");
                redirect(site_url('/pet_talk'));
            }

            $item = $this->pet_talk_model->getItem($newsfeed_id);

            if (!$item || !in_array($item->newsFeedType, array(ADD_POST_UPDATED, ADD_PET_TOPIC))) {
                //redirect if invalid member id
                $this->messages->add(lang('pet_talk_invalid_id'), "error");
                redirect(site_url('/pet_talk'));
            }

            if($item->newsFeedType == ADD_PET_TOPIC){
                $paramType = 'topic';
                $paramId = 'topic_id';
                $this->data['topic'] = $this->pet_talk_model->detail($item->newsFeedItemId);
            } else{
                $paramType = 'post_updated';
                $paramId = 'topic_id';
                $this->data['topic'] = $this->pet_post_updated_model->detail($item->newsFeedItemId);
            }

            if ($this->input->post()) {
                $id = $this->_save_comment('insert', false, $paramType);
                if ($id) {
                    $this->messages->add(lang('comment_create_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/pet_talk/comments/edit/' . $id);
                } else {
                    $this->messages->add(lang('comment_create_action_fail'), "error");
                }
            }

            $this->data['topic_name'] = $this->data['topic']->title;


            $this->_assetForm();
            $this->page_title = lang('comment_header');
            $this->render_page('comments/create', $this->data);
        }
    }

    public function edit($id) {        
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid member id
                $this->messages->add(lang('comment_invalid_id'), "error");
                redirect($this->lang->lang() . '/pet_talk/comments/index');
            }
            //topic data
            $comment = $this->comments_model->detail($id);

            if(!$comment->topic_id && !$comment->pettalk_info_id){
                 //redirect if invalid member id
                $this->messages->add(lang('comment_invalid_id'), "error");
                redirect($this->lang->lang() . '/pet_talk/comments/index');
            }
            if ($this->input->post()) {
                if ($this->_save_comment('update', $id)) {
                    $this->messages->add(lang('comment_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/pet_talk/comments/edit/' . $id);
                } else {
                    $this->messages->add(lang('comment_edit_action_fail', $id), "error");
                }
            }
            
            $this->data['record'] = $comment;

            if($comment->topic_id && !$comment->pettalk_info_id){
                 $this->data['topic'] = $this->pet_talk_model->detail($comment->topic_id);
                $this->data['topic_name'] = $this->data['topic']->title;
            }else{
                 $this->data['topic'] = $this->pet_talk_info_model->detail($comment->pettalk_info_id);
                $this->data['topic_name'] = $this->data['topic']->name;
            }

            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('comment_invalid_id'), "error");
                redirect($this->lang->lang() . '/pet_talk/comments/index');
            }

            $this->_assetForm();
            $this->page_title = lang('comment_header');
            $this->render_page('comments/edit', $this->data);
        }
    }

    private function _save_comment($type = 'insert', $id = false, $topic_type = 'topic') {   
        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        //category data
        $data['status'] = $this->input->post('status');

        $content = $this->input->post('content');
        $id = $this->input->post('topic_id');
        $status = $this->input->post('status');

        $this->data['record'] = array(
            'content' => $content,
            'status' => $status,
        );
        
        $this->form_validation->set_rules('content', lang('comment_content'), 'required');
        //$this->form_validation->set_rules('topic', lang('comment_topic'), 'required');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if($type == 'insert'){
            if ($this->form_validation->run() == FALSE) {
                //load topic
                $this->data['topic'] = $this->pet_talk_model->detail($topic_id);  
                $this->page_title = lang('comment_header');              
                $this->render_page('create', $this->data);
            } else {
                // insert pet_talk data
                $key = $topic_type == 'topic' ? 'topic_id' : 'pettalk_info_id';
                $this->data['record'] = array_merge(
                    $this->data['record'], 
                    array(
                        $key => $topic_id,
                        'user_id' => $this->session->userdata('user_id'),
                    ));    
                $id = $this->comments_model->insert($this->data['record']);
                
                //send push to user owner of topic
                $this->load->model(array('members/members_model','notification/notification_model'));
                
                $topic = $topic_type == 'topic' ? $this->pet_talk_model->detail($topic_id) : $this->pet_talk_info_model->detail($topic_id);
                $actor_user_id = $topic_type == 'topic' ? $topic->created_by : $topic->user_id;
                $category_id = $topic_type == 'topic' ? $topic->category_id : $topic->catId;
                $user = format_output_data($this->members_model->detail($this->session->userdata('user_id')));
                $name_user_action = $user->display_name;
                $message = $content;
                $action_type = $this->notification_model->get_action_type('ADD_COMMENT_TOPIC');
                $source_id = $topic_id;
                $title = $name_user_action . ' commented on your post';
                $data_push = array(
                		'action_type' => 'ADD_COMMENT_TOPIC',
                		'sender_id' => $user->id,
                		'sender_name' => $name_user_action,
                		'profile_photo' => $user->profile_photo_thumb,
                		'created_date' => date('d-m-Y H:i:s',now()),
                		'type' => 'topic',
                		'topic_id' => $source_id,
                		'category_id' => $category_id,
                		'bages_unread_notification' => $this->notification_model->count_unread_notification($id) + 1,
                );
                //$this->notification_model->send_push_notification($actor_user_id, $message, $data_push, $action_type->id, $source_id);
                $this->notification_model->send_push($actor_user_id, $title, $message, $data_push, $action_type->id, $user->username, $source_id, 0);
                //end push
                
                $return = $id;
            }
        }
        else{
            if ($this->form_validation->run() == FALSE) {
                //load topic
                $this->data['topic'] = $this->pet_talk_model->detail($topic_id); 
                $this->page_title = lang('comment_header');               
                $this->render_page('create', $this->data);
            } 
            else{
                // update category data
                if ($this->comments_model->update($id, $this->data['record'])) {
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
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css',
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
            // '../admin/pages/scripts/components-pickers.js',
            // '../js/users/users.js',
            // '../js/users/components-form-tools.js',
            // '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            // '../global/plugins/bootstrap-select/bootstrap-select.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js',
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
        //     'ComponentsPickers.init();',
        //     'ComponentsDateTimePickers.init();',
        // );
    }

}
