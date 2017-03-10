<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pet_talk_info extends Admin_Controller {

    var $data = array();
    var $module = 'pet_talk';

    function __construct() {
        parent::__construct();

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

        $this->load->library(array('messages'));
        $this->lang->load(array('pet_talk_info'));
        $this->load->model(array('pet_talk_info_model', 'categories_model', 'comments_model', 'users/users_model', 'users/permissions_model', 'members/members_model', 'pet_talk_model', 'newsfeed/newsfeed_model', 'business/media_model'));
        $this->load->helper(array('url', 'language', 'permission', 'security'));
        $this->config->load('pet_report');
    }

    function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;
            
            $array_field = array('id', 'name', 'type', 'user_name', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['categories'] = $this->pet_talk_info_model->getCategories();
            $default_category = '';
            if(sizeof($this->data['categories']) > 1){
                $default_category = array_keys($this->data['categories'])[1];
            }
            $this->data['default_category'] = $default_category;
            $category = $this->input->get('category') ? $this->input->get('category') : $default_category;

            //get data
            $this->data['total'] = $this->pet_talk_info_model->getItems('total', $keyword, false, false, $limit, $offset, $category);

            $this->data['records'] = $this->pet_talk_info_model->getItems('list', $keyword, $order_field, $sort, $limit, $offset, $category);
            $this->data['count'] = $this->pet_talk_info_model->getItems('count_list', $keyword, $order_field, $sort, $limit, $offset, $category);
            $this->data['txt_search_value'] = $keyword;
            $this->data['category'] = $category;

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
                $this->load->library('admin_newsfeed');
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->admin_newsfeed->deletePettalkInfoAdmin($id);
                    if ($result) {
                        $this->messages->add(lang('pet_talk_info_delete_action_success', $id), "success");
                        //log_message('message', 'deleted successful pet talk item id:'.$id);
                    } else {
                        $this->messages->add(lang('pet_talk_info_delete_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail pet talk item id:'.$id);
                    }
                }

                
                redirect($this->lang->lang() . '/pet_talk/pet_talk_info/index');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('pet_talk_info_header');
        $this->render_page('pet_talk_info/index', $this->data);
    }

    public function create($type = 'adoption'){
        if ($this->input->post()) {
            $id = $this->_save_pet_talk_info();
            if ($id) {
                $this->messages->add(lang('pet_talk_info_create_action_success', $id), "success");
                //redirect to edit page
                $uri = $type == 'lost_report' ? 'Lost+Report' : ($type == 'found_report' ? 'Found+Report' : 'Adoptions');
                redirect($this->lang->lang() . '/pet_talk/pet_talk_info?category='.$uri);
            } else {
                $this->messages->add(lang('pet_talk_info_create_action_fail'), "error");
            }
        }
        $this->_assetForm();
        $this->page_title = lang('pet_talk_info_header');
        switch ($type) {
            case 'lost_report':
                $this->render_page('pet_talk_info/create_lost_report_tpl', $this->data);
                break;
            case 'found_report': 
                $this->render_page('pet_talk_info/create_found_report_tpl', $this->data);
                break;
            default:
                $this->render_page('pet_talk_info/create_adoption_tpl', $this->data);
                break;
        }
    } 

    public function edit($id){
        if ($this->input->post()) {
            $update = $this->_save_pet_talk_info('update', $id);
            if ($update) {
                $this->messages->add(lang('pet_talk_info_edit_action_success', $id), "success");
                //redirect to edit page
                redirect($this->lang->lang() . '/pet_talk/pet_talk_info/');
            } else {
                $this->messages->add(lang('pet_talk_info_edit_action_fail', $id), "error");
            }
        }
        $this->data['record'] = $this->pet_talk_info_model->detail($id);
        $this->data['cover_image'] = $this->pet_talk_info_model->getCoverImage($id);
        // var_dump($this->data['record']->user_id);exit;
        $this->data['user'] = $this->members_model->find($this->data['record']->user_id);

        if (empty($this->data['record'])) {
            //redirect if invalid pet_talk id
            $this->messages->add(lang('pet_talk_invalid_id'), "error");
            redirect($this->lang->lang() . '/pet_talk/pet_talk_info/index');
        }

        $this->_assetForm();
        $this->page_title = lang('pet_talk_info_header');
        switch ($this->data['record']->infoType) {
            case ADD_PETTALK_LOST_REPORT:
                $this->render_page('pet_talk_info/edit_lost_report_tpl', $this->data);
                break;
            case ADD_PETTALK_FOUND_REPORT: 
                $this->render_page('pet_talk_info/edit_found_report_tpl', $this->data);
                break;
            default:
                $this->render_page('pet_talk_info/edit_adoption_tpl', $this->data);
                break;
        }
    } 

    private function _save_pet_talk_info($type = 'insert', $id = 0) {

        $this->load->library('image_lib');
        $this->load->model(array('business/media_model', 'newsfeed/newsfeed_model'));

        $return = false;

        $data ['name']              = $this->input->post('name') ? $this->input->post('name') : "";
        $data ['age']               = $this->input->post('age') ? $this->input->post('age') : "";
        $data ['type']              = $this->input->post('type') ? $this->input->post('type') : "";
        $data ['breed']             = $this->input->post('breed') ? $this->input->post('breed') : "";
        $data ['sex']               = $this->input->post('sex') ? $this->input->post('sex') : "";
        $data ['color']             = $this->input->post('color') ? $this->input->post('color') : "";
        $data ['contact']           = $this->input->post('contact') ? $this->input->post('contact') : "";
        $data ['additionalInfo']    = $this->input->post('information') ? $this->input->post('information') : "";
        $data ['user_id']           = $this->input->post('user_id') ? $this->input->post('user_id') : false;
        $data ['infoType']          = $this->input->post('infoType') ? $this->input->post('infoType') : ADD_PETTALK_LOST_REPORT;
        $data ['location']          = $this->input->post('location') ? $this->input->post('location') : "";

        $data ['when']              = $this->input->post('when') ? $this->input->post('when') : "";
        $data ['where']             = $this->input->post('where') ? $this->input->post('where') : "";
        $data ['rewardCurrency']    = $this->input->post('rewardCurrency') ? $this->input->post('rewardCurrency') : "";
        $data ['currency']          = $this->input->post('currency') ? $this->input->post('currency') : "";
        $data ['microchip']         = $this->input->post('microchip') ? $this->input->post('microchip') : "";

        // $this->data['record'] = (object) array(
        //             'name'          => $data ['name'],
        //             'age'           => $data ['age'],
        //             'type'          => $data ['type'],
        //             'breed'         => $data ['breed'],
        //             'sex'           => $data ['sex'],
        //             'color'         => $data ['color'],
        //             'contact'       => $data ['contact'],
        //             'location'      => $data ['location'],
        //             'additionalInfo'=> $data ['additionalInfo'],
        //             'user_id'       => $data ['user_id'],
        // );
        $this->data['record'] = (object) $data;
        // make sure we only pass in the fields we want
        
        $this->form_validation->set_rules('type', lang('pet_talk_info_type'), 'required|xss_clean');
        $this->form_validation->set_rules('age', lang('pet_talk_info_age'), 'is_natural|xss_clean');
        $this->form_validation->set_rules('breed', lang('pet_talk_info_breed'), 'required|xss_clean');
        $this->form_validation->set_rules('sex', lang('pet_talk_info_sex'), 'required|xss_clean');         

        if($data ['infoType'] == ADD_PETTALK_LOST_REPORT){
            $this->form_validation->set_rules('name', lang('pet_talk_info_name'), 'required|xss_clean');
            $this->form_validation->set_rules('color', lang('pet_talk_info_color'), 'required|xss_clean');
            $this->form_validation->set_rules('where', lang('pet_talk_info_location'), 'required|xss_clean|callback_location_check');
            $this->form_validation->set_rules('when', lang('pet_talk_info_when'), 'required|xss_clean');
            $this->form_validation->set_rules('currency', lang('pet_talk_info_currency'), 'numeric|xss_clean');
            $this->form_validation->set_rules('user_id', lang('pet_talk_info_owner'), 'required|xss_clean'); 
        }elseif($data ['infoType'] == ADD_PETTALK_FOUND_REPORT){
            $this->form_validation->set_rules('user_id', lang('pet_talk_info_finder'), 'required|xss_clean'); 
        }else{
            $this->form_validation->set_rules('user_id', lang('pet_talk_info_owner'), 'required|xss_clean'); 
            $this->form_validation->set_rules('name', lang('pet_talk_info_name'), 'required|xss_clean');
            $this->form_validation->set_rules('location', lang('pet_talk_info_location'), 'required|xss_clean|callback_location_check');
        }
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');

        //config upload media
        $path = DEFAULT_PATH_ADMIN . $this->config->item('pet_path');
        $upload_field_name  = 'photo';
        $field_image = 'source';
        $field_image_thumb = 'photo_thumb'; 

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($field_image, $field_image_thumb),
            'required'              => true,
            'table_media'           => true
        );

        if($type == 'update'){
            // load old image
            $media = $this->media_model->getMediaCoverBy('pettalk_info_id', $id);

            // upload photo & saving image
            $config['type'] = 'update';
            if($media){
                $config['item'] = $media;                
            }
        }

        // var_dump($this->form_validation->run());exit;
        $library_media          = new Admin_media($config);  
        $library_media->checkRequired();

        // get latitude and longitude from location
        $location = !empty($data['location']) ? get_location_from_address($data['location']) : get_location_from_address($data['where']);
        $data['lat'] = !empty($location) ? $location['lat'] : 0;
        $data['lng'] = !empty($location) ? $location['long'] : 0;

        if ($type == 'insert') {
            if ($this->form_validation->run($this) == FALSE) {
                //load categories
                
                switch ($data ['infoType']) {
                    case ADD_PETTALK_LOST_REPORT:
                        $this->render_page('pet_talk_info/create_lost_report_tpl', $this->data);
                        break;
                    case ADD_PETTALK_FOUND_REPORT: 
                        $this->render_page('pet_talk_info/create_found_report_tpl', $this->data);
                        break;
                    default:
                        $this->render_page('pet_talk_info/create_adoption_tpl', $this->data);
                        break;
                }
                $this->render_page('pet_talk_info/create_adoption_tpl', $this->data);
            } else {

                $data['updated_date'] = now();

                if($data['infoType'] == ADD_PETTALK_LOST_REPORT){
                    $data['catId'] = '22';
                }elseif($data['infoType'] == ADD_PETTALK_FOUND_REPORT){
                    $data['catId'] = '23';
                }else{
                    $data['catId'] = '24';
                }

                // insert pet_talk_info data
                $id = $this->pet_talk_info_model->insert($data);
                $return = $id;

                // add newsfeed
                $dataNewsfeed = array(
                    "pettalk_info_id"   => $id,
                    "newsFeedType"      => $data['infoType'],
                    "user_id"           => $data ['user_id'],
                    "created_date"      => now(),
                    "updated_date"      => now()
                );
                $newsFeedId = $this->newsfeed_model->addNew($dataNewsfeed);

                // upload photo & saving image
                $dataMedia = array(
                    'media_type'        => 'cover',
                    'pettalk_info_id'   => $id,
                    'newfeed_id'        => $newsFeedId ? $newsFeedId : null,
                    'user_id'           => $data ['user_id'],
                    'type'              => 'PHOTO',
                    'status'            => '1',
                );                
                $dataMedia              = $library_media->saveMediaAdmin($dataMedia);

                $newsFeedId = $this->media_model->insert($dataMedia);
            }
        } elseif ($type == 'update') {
            
            if ($this->form_validation->run($this) == FALSE) {
                //load categories
                switch ($data ['infoType']) {
                    case ADD_PETTALK_LOST_REPORT:
                        $this->render_page('pet_talk_info/edit_lost_report_tpl', $this->data);
                        break;
                    case ADD_PETTALK_FOUND_REPORT: 
                        $this->render_page('pet_talk_info/edit_found_report_tpl', $this->data);
                        break;
                    default:
                        $this->render_page('pet_talk_info/edit_adoption_tpl', $this->data);
                        break;
                }
            } else {
                //load pet_talk_info data
                $item = $this->pet_talk_info_model->find($id);
                $_POST['id'] = $id;

                // update data
                if ($this->pet_talk_info_model->update($id, $data)) {
                    $return = $id;
                }

                // update photo if $_FILE exist
                $image = $_FILES[$upload_field_name ]['name'];
                if (!empty($image)){
                    // get newsfeed id of report
                    $newsFeed = $this->newsfeed_model->getNewsFeedItemBy('pettalk_info_id', $id);

                    $dataMedia = array(
                        'media_type'        => 'cover',
                        'pettalk_info_id'   => $id,
                        'newfeed_id'        => $newsFeed ? $newsFeed->id : null,
                        'user_id'           => $data ['user_id'],
                        'type'              => 'PHOTO',
                        'status'            => '1',
                    );                
                    $dataMedia              = $library_media->saveMediaAdmin($dataMedia);

                    if($media){
                        $this->media_model->update($media->id, $dataMedia);
                    }else{
                        $this->media_model->insert($dataMedia);
                    }
                    
                }

            }
        }
        return $return;

        // update categories data
        // if ($this->pet_talk_info_model->update($id, $data)) {
        //     $return = $id;
        // }

        // return $return;
    }

    function mediaList($pettalk_info_id = false) {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            // check newsfeed before processing
            
            $item = $this->checkPettalkInfo($pettalk_info_id);

            $status = array(0,1);//$this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;
            
            $array_field = array('id');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : reset($array_field);
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'ASC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            //get data
            $this->data['total'] = $this->pet_talk_info_model->getPhotoItems('total', $status, $keyword, false, false, $limit, $offset, $pettalk_info_id);
            $this->data['records'] = $this->pet_talk_info_model->getPhotoItems('list', $status, $keyword, $order_field, $sort, $limit, $offset, $pettalk_info_id);
            $this->data['count'] = $this->pet_talk_info_model->getPhotoItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset, $pettalk_info_id);
            $this->data['txt_search_value'] = $keyword;

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

            $this->data['pettalk_info_id'] = $pettalk_info_id;
            $this->data['is_admin'] = $this->ion_auth->is_admin();
        }
        $this->data['number_photos'] = $this->data['count'];

        // Deleting anything?
        if ($this->input->post('btn_delete')) {            
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {

                    $result = $this->pet_talk_info_model->deletePhoto($id, $pettalk_info_id);
                    if ($result) {
                        $this->messages->add(lang('pet_talk_info_media_delete_action_success', $id), "success");
                        //log_message('message', 'deleted successful pet talk item id:'.$id);
                    } else {
                        $this->messages->add(lang('pet_talk_info_media_delete_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail pet talk item id:'.$id);
                    }
                }

                
                redirect($this->lang->lang() . '/pet_talk/pet_talk_info/mediaList/'. $pettalk_info_id);
            }
        }
        //set asset
        $this->_assetMediaIndex();
        $this->render_page('pet_talk_info_photo/index', $this->data);    
    }

    public function mediaCreate($pettalk_info_id = false) {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            // check newsfeed before processing
            $item = $this->checkPettalkInfo($pettalk_info_id);
            $count = $this->pet_talk_info_model->getPhotoItems('count_list', array(0,1), NULL, 'id', 'ASC', $this->limit, 0, $pettalk_info_id);;

            if( ! ( $count < 3 ) ){
                $this->messages->add(lang('pet_talk_info_media_create_action_get_max'), "error");
                redirect($this->lang->lang() . '/pet_talk/pet_talk_info/mediaList/' . $pettalk_info_id);
            }

            if ($this->input->post()) {
                $id = $this->_save_photo('insert', $pettalk_info_id);
                if ($id) {
                    $this->messages->add(lang('pet_talk_info_media__create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/pet_talk/pet_talk_info/');
                    } else {
                        redirect($this->lang->lang() . '/pet_talk/pet_talk_info/mediaEdit/' . $pettalk_info_id . '/' . $id);
                    }
                } else {
                    $this->messages->add(lang('pet_talk_info_media_create_action_fail'), "error");
                }
            }

            $this->_assetForm();
            $this->data['pettalk_info_id'] = $pettalk_info_id;
            $this->page_title = lang('pet_talk_header') . ' | ' . lang('pet_talk_photo_header');
            $this->render_page('pet_talk_info_photo/create', $this->data);
        }
    }

    public function mediaEdit($pettalk_info_id = false, $id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid pet_talk id
                $this->messages->add(lang('pet_talk_invalid_id'), "error");
                redirect($this->lang->lang() . '/pet_talk/pet_talk_info/mediaList/' . $pettalk_info_id);
            }

            // check newsfeed before processing
            $item = $this->checkPettalkInfo($pettalk_info_id);
            if ($this->input->post()) {
                if ($this->_save_photo('update', $pettalk_info_id, $id)) {
                    $this->messages->add(lang('pet_talk_info_media__edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/pet_talk/pet_talk_info/mediaEdit/' . $pettalk_info_id . '/' . $id);
                } else {
                    $this->messages->add(lang('pet_talk_info_media_edit_action_fail', $id), "error");
                }
            }else{
                //pet_talk data                
                $this->data['record'] = $this->pet_talk_info_model->getPhotoItem($id, $pettalk_info_id);

                if (!$this->data['record']) {
                    //redirect if invalid pet_talk id
                    $this->messages->add(lang('pet_talk_info_media_invalid_id'), "error");
                    redirect($this->lang->lang() . '/pet_talk/pet_talk_info/mediaList/' . $pettalk_info_id);
                }
            }

            // var_dump($this->data['photo']);exit;
            $this->data['pettalk_info_id'] = $pettalk_info_id;
            $this->_assetForm();
            $this->page_title = lang('pet_talk_header') . ' | ' . lang('pet_talk_photo_header');
            $this->render_page('pet_talk_info_photo/edit', $this->data); 
        }
    }

    private function _save_photo($type = 'insert', $pettalk_info_id = false, $id = 0) {

        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        $this->load->library('image_lib');

        //media data
        $data['status'] = $this->input->post('status');

        //config upload media
        $path = DEFAULT_PATH_ADMIN . $this->config->item('pet_path');
        $upload_field_name  = 'path';
        $field_image = 'source';
        $field_image_thumb = 'photo_thumb';    

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($field_image, $field_image_thumb),
        );

        // get newsfeed item
        $newsFeedItem = $this->newsfeed_model->getNewsFeedItemBy('pettalk_info_id', $id);

        $this->form_validation->set_rules('status', 'Status', 'required');

        if ($type == 'insert') {

            // upload photo & saving image
            $config['table_media']      = TRUE;
            $config['required']         = TRUE;

            $library_media = new Admin_media($config);  
            $data = $library_media->saveMediaAdmin($data);            

            if ($this->form_validation->run()) {
                // insert media data
                $data ['newfeed_id'] = $newsFeedItem->id;
                $data ['pettalk_info_id'] = $pettalk_info_id;              
                $data ['media_type'] = 'attachment';              
                $data ['user_id'] = $this->session->userdata('user_id');
                $id = $this->media_model->insert($data);
                if ($id) {
                    $return = $id;
                }
            }

        } elseif ($type == 'update') {

            //load data
            $this->load->model('business/media_model');
            $item = $this->media_model->find($id);
            $_POST['id'] = $id;

            // upload photo
            $config['type'] = 'update';
            $config['item'] = $item;
            $config['table_media'] = TRUE;

            $library_media = new Admin_media($config);
            $data = $library_media->saveMediaAdmin($data);

            // update media data
            $data ['user_id'] = $this->session->userdata('user_id');
            if ($this->media_model->update($id, $data)) {
                $return = $id;
            }
        }
        return $return;
    }

    private function checkPettalkInfo($id){
        $item = $this->pet_talk_info_model->detail($id);      
        if (!$item) {
            //redirect if invalid pet_talk id
            $this->messages->add(lang('pet_talk_info_invalid_id'), "error");
            redirect($this->lang->lang() . '/pet_talk/pet_talk_info/index');
        }

        return $item;
    }

    public function fetch_data_type(){
        $type = $this->input->post('type');
        
        $response = [
            'type' => $type,
            'data' => $this->config->item($type),
        ];
        echo json_encode($response);
        exit;
    }

    public function location_check($str) {
        $location = get_location_from_address($str);

        if (empty($location)) {
            $this->form_validation->set_message('location_check', "You have entered an invalid location. Please check or re-enter the location to proceed. Thank you.");
            return FALSE;
        } else {
            return TRUE;
        }
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
            // //'../css/bootstrap-editable.css',
            // //'../css/bootstrap-combined.min.css',
            // //'../global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css',
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
            // //'../global/plugins/jquery.mockjax.js',
            // //'../global/plugins/moment.min.js',
            // //'../admin/pages/scripts/form-editable.js',            
            // '../js/custom/custom-table-advanced.js',
            // '../js/custom/custom.js',            
            // //'../js/bootstrap-editable.js',
            // //'../js/main.js',
            '../global/plugins/select2/js/select2.min.js',
            '../global/scripts/datatable.js',
            '../global/plugins/datatables/datatables.min.js',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
            '../js/custom/custom-table-advanced.js',
            '../js/custom/custom.js',
        );
//assets/global/plugins/jquery.mockjax.js
        $this->js_domready = array(
            // 'Metronic.init();', // init metronic core components
            // 'Layout.init();', // init current layout
            // 'QuickSidebar.init();', // init quick sidebar
            // 'Demo.init();', // init demo features'
            'TableAdvancedCustom.init();',
            'Custom.init();',
            //'FormEditable.init();',
        );
    }

    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetMediaIndex() {
        $this->assets_css['page_style'] = array(
            // '../global/plugins/select2/select2.css',
            // '../global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css',
            // '../global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css',
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css',
            //'../css/bootstrap-editable.css',
            //'../css/bootstrap-combined.min.css',
            //'../global/plugins/bootstrap-editable/bootstrap-editable/css/bootstrap-editable.css',
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
            // //'../global/plugins/jquery.mockjax.js',
            // //'../global/plugins/moment.min.js',
            // //'../admin/pages/scripts/form-editable.js',            
            // '../js/custom/custom-table-advanced.js',
            // '../js/custom/custom.js',            
            // //'../js/bootstrap-editable.js',
            // //'../js/main.js',
            '../global/plugins/select2/js/select2.min.js',
            '../global/scripts/datatable.js',
            '../global/plugins/datatables/datatables.min.js',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
            '../js/custom/custom-table-advanced.js',
            '../js/custom/custom.js',
        );
//assets/global/plugins/jquery.mockjax.js
        $this->js_domready = array(
            // 'Metronic.init();', // init metronic core components
            // 'Layout.init();', // init current layout
            // 'QuickSidebar.init();', // init quick sidebar
            // 'Demo.init();', // init demo features'
            // 'TableAdvancedCustom.init();',
            'Custom.init();',
            //'FormEditable.init();',
        );
    }

    /**
     * _assetEditForm
     *
     * file_name
     */
    private function _assetForm() {
        $this->assets_css['page_style'] = array(
            // '../plugin/select2/css/select2.min.css',
            // // '../global/plugins/select2/select2.css',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            // '../admin/pages/css/profile.css',
            // '../admin/pages/css/tasks.css',
            // '../global/plugins/jquery-multi-select/css/multi-select.css',
            // '../global/plugins/bootstrap-select/bootstrap-select.min.css',
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css',
            // '../global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',
            '../global/plugins/select2/css/select2.min.css',
            '../global/plugins/select2/css/select2-bootstrap.min.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
            '../global/plugins/jquery-multi-select/css/multi-select.css',
            '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
            '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css',
            '../global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',
        );
        $this->assets_js['page_plugin'] = array(
            // '../global/plugins/fuelux/js/spinner.min.js',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            // '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            // '../global/plugins/typeahead/typeahead.bundle.min.js',
            // // '../global/plugins/select2/select2.min.js',
            // '../global/plugins/ckeditor/ckeditor.js',
            // '../admin/pages/scripts/components-pickers.js',
            // '../js/custom/custom.js',
            // '../js/custom/custom-pet-report.js',
            // '../js/custom/components-form-tools.js',
            // '../js/custom/select2.js',
            // '../plugin/select2/js/select2.full.min.js',
            // '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            // '../global/plugins/bootstrap-select/bootstrap-select.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js',
            // '../global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js',
            // //'../js/data/data-confirm.js',
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
            '../js/custom/custom-pet-report.js',
            '../js/custom/select2.js',
        );

        // $this->js_domready = array(
        //     'Metronic.init();', // init metronic core components
        //     'Layout.init();', // init current layout
        //     'QuickSidebar.init();', // init quick sidebar
        //     'Demo.init();', // init demo features'
        //     'ComponentsFormTools.init();',
        //     'ComponentsPickers.init();',
        //     'Custom.init();',
        //     'DataConfirm.init();',
        //     'ComponentsDateTimePickers.init();',
        // );
    }

}
