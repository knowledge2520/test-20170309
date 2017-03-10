<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pet_talk extends Admin_Controller {

    var $data = array();
    var $module = 'pet_talk';

    function __construct() {
        parent::__construct();

        $this->load->library(array('messages'));
        $this->lang->load(array('pet_talk'));
        $this->load->model(array('pet_talk_model', 'pet_post_updated_model','categories_model', 'comments_model', 'users/users_model', 'users/permissions_model', 'business/media_model', 'newsfeed/newsfeed_model'));
        $this->load->helper(array('url', 'language', 'permission'));
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
            
            $array_field = array('id', 'title', 'category_name', 'user_name', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'newsFeedId';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            //get data
            $this->data['total'] = $this->pet_talk_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->pet_talk_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->pet_talk_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);
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

            // //list the categories
            // if ($this->data['records']) {
            //     foreach ($this->data['records'] as $k => $pet_talk) {
            //         $this->data['records'][$k]->category = $this->categories_model->detail($pet_talk->category_id);
            //     }
            // }
        }
        
        //set status
        if($this->input->post('set_status')){            
            $id = $this->input->post('id');
            $status = $this->input->post('status');
            
            $this->pet_talk_model->update_status($id, $status);
            die();
        }
        // Deleting anything?
        if ($this->input->post('btn_delete')) {            
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $this->load->library('admin_newsfeed');
                $result = FALSE;
                foreach ($checked as $id) {

                    $result = $this->admin_newsfeed->deletePettalkAdmin($id);
                    if ($result) {
                        $this->messages->add(lang('pet_talk_delete_action_success', $id), "success");
                        //log_message('message', 'deleted successful pet talk item id:'.$id);
                    } else {
                        $this->messages->add(lang('pet_talk_delete_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail pet talk item id:'.$id);
                    }
                }

                
                redirect($this->lang->lang() . '/pet_talk/index');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('pet_talk_header');
        $this->render_page('index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            //load categories
            $this->data['categories'] = $this->categories_model->getAll();

            if ($this->input->post()) {
                $id = $this->_save_pet_talk();
                if ($id) {
                    $this->messages->add(lang('pet_talk_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/pet_talk/');
                    } else {
                        redirect($this->lang->lang() . '/pet_talk/edit/' . $id);
                    }
                } else {
                    $this->messages->add(lang('pet_talk_create_action_fail'), "error");
                }
            }

            $this->_assetForm();
            $this->page_title = lang('pet_talk_header');
            $this->render_page('create', $this->data);
        }
    }

    public function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid pet_talk id
                $this->messages->add(lang('pet_talk_invalid_id'), "error");
                redirect($this->lang->lang() . '/pet_talk/index');
            }

            if ($this->input->post()) {
                if ($this->_save_pet_talk('update', $id)) {
                    $this->messages->add(lang('pet_talk_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/pet_talk/edit/' . $id);
                } else {
                    $this->messages->add(lang('pet_talk_edit_action_fail', $id), "error");
                }
            }else{
                //pet_talk data
                $this->data['categories'] = $this->categories_model->getArray();
                unset($this->data['categories']['newsfeed_post']);
                $this->data['record'] = $this->pet_talk_model->getItem($id);

                if (!$this->data['record']) {
                    //redirect if invalid pet_talk id
                    $this->messages->add(lang('pet_talk_invalid_id'), "error");
                    redirect($this->lang->lang() . '/pet_talk/index');
                }

                $this->data['photo'] = $this->pet_talk_model->getPhoto($id);

                if (empty($this->data['record'])) {
                    //redirect if invalid pet_talk id
                    $this->messages->add(lang('pet_talk_invalid_id'), "error");
                    redirect($this->lang->lang() . '/pet_talk/index');
                }
            }

            // var_dump($this->data['photo']);exit;
            $this->_assetForm();
            $this->page_title = lang('pet_talk_header');
            if(!$this->data['record']->category_id){
                $this->render_page('edit_post', $this->data);
            }else{
                $this->render_page('edit', $this->data);
            }
            
        }
    }

    private function _save_pet_talk($type = 'insert', $id = 0) {

        $this->load->library('image_lib');

        $return = false;
        // make sure we only pass in the fields we want
        // var_dump($this->input->post());exit;
        $data ['title'] = $this->input->post('title') ? $this->input->post('title') : '';
        $data ['content'] = $this->input->post('content') ? $this->input->post('content'): '';        
        $data ['category_id'] = $this->input->post('category_id') ? $this->input->post('category_id'): '';        

        $paramType = $this->input->post('category_id') && ($this->input->post('category_id') == 'newsfeed_post' || $this->input->post('category_id') ==  'Newsfeed Post')? ADD_POST_UPDATED : ADD_PET_TOPIC;
        $paramId = $paramType == ADD_POST_UPDATED ? 'newsfeed_id' : 'topic_id';
        $oldParamId = $paramType == ADD_POST_UPDATED ? 'topic_id' : 'newsfeed_id';
        
        $this->data['categories'] = $this->categories_model->getArray();

        //$data ['status'] = $this->input->post('status');
        $this->data['record'] = (object) array(
            'title' => $this->input->post('title'),
            'content' => $this->input->post('content'),
            'category_id' => $this->input->post('category_id'),
            'status' => $this->input->post('status'),
            'user_name' => $this->input->post('user_name')
        );

        if($paramType == ADD_POST_UPDATED){
            $this->form_validation->set_rules('content', lang('pet_talk_title'), 'required');
        }else{
            $this->form_validation->set_rules('content', lang('pet_talk_title'), 'required');
            $this->form_validation->set_rules('title', lang('pet_talk_title'), 'required');
        }
        
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');

        //config upload media
        // $path = DEFAULT_PATH_ADMIN . $this->config->item('pet_path');
        // $upload_field_name  = 'photo';
        // $field_image = 'photo';
        // $field_image_thumb = 'photo_thumb';    

        // $config = array(
        //     'upload_field_name'     => $upload_field_name,
        //     'path'                  => $path,
        //     'field_image'           => array($field_image, $field_image_thumb),
        // );

        //config upload media
        $path = DEFAULT_PATH_ADMIN . $this->config->item('pet_path');
        $upload_field_name  = 'photo';
        $field_image = 'source';
        $field_image_thumb = 'photo_thumb'; 

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($field_image, $field_image_thumb),
            'table_media'           => true
        );

        if($type == 'update'){
            // load old image
            $media = $this->media_model->getMediaBy('newfeed_id', $id);
            if($media){
                $config['type'] = 'update';
                $config['item'] = $media;                
            }
        }
        $library_media          = new Admin_media($config);  

        if ($type == 'insert') {
            if ($this->form_validation->run() == FALSE) {
                //load categories
                $this->data['categories'] = $this->categories_model->getAll();
                $this->page_title = lang('pet_talk_header');
                $this->render_page('create', $this->data);
            } else {
               
                // insert pet_talk data
                if($paramType == ADD_POST_UPDATED){
                    unset($data['category_id']);
                    $data ['title'] = '';
                    $data ['user_id'] = $this->session->userdata('user_id');
                    $data ['updated_date'] = now();
                    $id = $this->pet_post_updated_model->insert($data);
                }else{
                    $data ['created_by'] = $this->session->userdata('user_id');
                    $id = $this->pet_talk_model->insert($data);
                }

                $return = $id;

                // add newsfeed
                $dataNewsfeed = array(
                    $paramId            => $id,
                    "newsFeedType"      => $paramType,
                    "user_id"           => $this->session->userdata('user_id'),
                    "created_date"      => now(),
                    "updated_date"      => now()
                );
                $newsFeedId = $this->newsfeed_model->addNew($dataNewsfeed);

                // upload photo & saving image
                $dataMedia = array(
                    $paramId            => $id,
                    'newfeed_id'        => $newsFeedId ? $newsFeedId : null,
                    'user_id'           => $this->session->userdata('user_id'),
                    'type'              => 'PHOTO',
                    'status'            => '1',
                );                
                $dataMedia              = $library_media->saveMediaAdmin($dataMedia);
                $mediaId = $this->media_model->insert($dataMedia);
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == FALSE) {
                $this->data['categories'] = $this->categories_model->getAll();
                $this->data['photo'] = $this->pet_talk_model->getPhoto($id);
                $this->page_title = lang('pet_talk_header');
                $this->render_page('edit', $this->data);

            } else {
                //load pet_talk data
                $_POST['id'] = $id;

                // get newsfeed id of topic
                $newsFeed = $this->newsfeed_model->getNewsfeedBy('id', $id);

                // check category save topic type or newsfeed post type
                if($newsFeed->newsFeedType == $paramType){                    
                    if($_FILES[$upload_field_name]['name']){
                        // update media
                        $dataMedia = array(
                            $paramId            => $newsFeed->$paramId,
                            'newfeed_id'        => $id ? $id : null,
                            'user_id'           => $this->session->userdata('user_id'),
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
                    // update data
                    if($newsFeed->newsFeedType == ADD_POST_UPDATED){
                        unset($data ['category_id']);
                        $data ['title'] = '';
                        $this->pet_post_updated_model->update($newsFeed->$paramId, $data);
                    }else{
                        $this->pet_talk_model->update($newsFeed->$paramId, $data);
                    }

                    $return = $id;
                }else{
                    // // delete old data
                    // $this->load->library('admin_newsfeed');
                    // $this->admin_newsfeed->deletePettalkAdmin($id);

                    // // insert pet_talk data
                    // if($paramType == ADD_POST_UPDATED){
                    //     unset($data['category_id']);
                    //     $data ['title'] = '';
                    //     $data ['user_id'] = $this->session->userdata('user_id');
                    //     $data ['updated_date'] = now();
                    //     $id = $this->pet_post_updated_model->insert($data);
                    // }else{
                    //     $data ['created_by'] = $this->session->userdata('user_id');
                    //     $id = $this->pet_talk_model->insert($data);
                    // }

                    // $return = $id;

                    // // add newsfeed
                    // $dataNewsfeed = array(
                    //     $paramId            => $id,
                    //     $oldParamId         => null,
                    //     "newsFeedType"      => $paramType,
                    //     "user_id"           => $this->session->userdata('user_id'),
                    //     "created_date"      => now(),
                    //     "updated_date"      => now()
                    // );
                    // $newsFeedId = $this->newsfeed_model->update($id, $dataNewsfeed);

                    // // upload photo & saving image
                    // $dataMedia = array(
                    //     $paramId            => $newsFeed->$paramId,
                    //     'newfeed_id'        => $id ? $id : null,
                    //     'user_id'           => $this->session->userdata('user_id'),
                    //     'type'              => 'PHOTO',
                    //     'status'            => '1',
                    // );                
                    // $dataMedia              = $library_media->saveMediaAdmin($dataMedia);
                    // $mediaId = $this->media_model->insert($dataMedia);
                    $return = $id;
                }
            }
        }
        return $return;
    }

    function mediaList($newsfeed_id = false) {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            // check newsfeed before processing
            $newsFeedItem = $this->checkNewsFeed($newsfeed_id);
            $newsFeedType = $newsFeedItem->topic_id ? 'topic' : 'post_updated';

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
            $this->data['total'] = $this->pet_talk_model->getPhotoItems('total', $status, $keyword, false, false, $limit, $offset, $newsfeed_id);
            $this->data['records'] = $this->pet_talk_model->getPhotoItems('list', $status, $keyword, $order_field, $sort, $limit, $offset, $newsfeed_id);
            $this->data['count'] = $this->pet_talk_model->getPhotoItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset, $newsfeed_id);
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

            $this->data['newsfeed_id'] = $newsfeed_id;
            $this->data['is_admin'] = $this->ion_auth->is_admin();
        }
        $newsfeedPhotos = $this->newsfeed_model->getNewsfeedPhotos($newsfeed_id);
        $this->data['number_photos'] = sizeof($newsfeedPhotos);

        // Deleting anything?
        if ($this->input->post('btn_delete')) {            
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {

                    $result = $this->pet_talk_model->deletePhoto($id, $newsfeed_id);
                    if ($result) {
                        $this->messages->add(lang('pet_talk_media_delete_action_success', $id), "success");
                        //log_message('message', 'deleted successful pet talk item id:'.$id);
                    } else {
                        $this->messages->add(lang('pet_talk_media_delete_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail pet talk item id:'.$id);
                    }
                }

                
                redirect($this->lang->lang() . '/pet_talk/mediaList/'. $newsfeed_id);
            }
        }
        //set asset
        $this->_assetMediaIndex();
        $this->page_title = lang('pet_talk_header') . ' | ' . lang('pet_talk_photo_header');
        if($newsFeedType == 'topic'){
            $this->render_page('topic_photo/index', $this->data);
        }else{
            $this->render_page('post_updated_photo/index', $this->data);
        }        
    }

    public function mediaCreate($newsfeed_id = false) {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            // check newsfeed before processing
            $newsFeedItem = $this->checkNewsFeed($newsfeed_id);
            $newsFeedType = $newsFeedItem->topic_id ? 'topic' : 'post_updated';
            $newsfeedPhotos = $this->newsfeed_model->getNewsfeedPhotos($newsfeed_id);

            if( ! ( ( sizeof($newsfeedPhotos) < 10 && $newsFeedType == 'post_updated' ) || ( sizeof($newsfeedPhotos) < 4 && $newsFeedType == 'topic' ) ) ){
                $this->messages->add(lang('pet_talk_media_create_action_get_max'), "error");
                redirect($this->lang->lang() . '/pet_talk/mediaList/' . $newsfeed_id);
            }

            if ($this->input->post()) {
                $id = $this->_save_photo('insert', $newsfeed_id);
                if ($id) {
                    $this->messages->add(lang('pet_talk_media_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/pet_talk/');
                    } else {
                        redirect($this->lang->lang() . '/pet_talk/mediaEdit/' . $newsfeed_id . '/' . $id);
                    }
                } else {
                    $this->messages->add(lang('pet_talk_media_create_action_fail'), "error");
                }
            }

            $this->_assetForm();
            $this->data['newsfeed_id'] = $newsfeed_id;
            $this->page_title = lang('pet_talk_header') . ' | ' . lang('pet_talk_photo_header');
            if($newsFeedType == 'topic'){
                $this->render_page('topic_photo/create', $this->data);
            }else{
                $this->render_page('post_updated_photo/create', $this->data);
            } 
        }
    }

    public function mediaEdit($newsfeed_id = false, $id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid pet_talk id
                $this->messages->add(lang('pet_talk_invalid_id'), "error");
                redirect($this->lang->lang() . '/pet_talk/mediaList/' . $newsfeed_id);
            }

            // check newsfeed before processing
            $newsFeedItem = $this->checkNewsFeed($newsfeed_id);
            $newsFeedType = $newsFeedItem->topic_id ? 'topic' : 'post_updated';

            if ($this->input->post()) {
                if ($this->_save_photo('update', $newsfeed_id, $id)) {
                    $this->messages->add(lang('pet_talk_media_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/pet_talk/mediaEdit/' . $newsfeed_id . '/' . $id);
                } else {
                    $this->messages->add(lang('pet_talk_media_edit_action_fail', $id), "error");
                }
            }else{
                //pet_talk data                
                $this->data['record'] = $this->pet_talk_model->getPhotoItem($id, $newsfeed_id);

                if (!$this->data['record']) {
                    //redirect if invalid pet_talk id
                    $this->messages->add(lang('pet_talk_media_invalid_id'), "error");
                    redirect($this->lang->lang() . '/pet_talk/mediaList/' . $newsfeed_id);
                }
            }

            // var_dump($this->data['photo']);exit;
            $this->data['newsfeed_id'] = $newsfeed_id;
            $this->_assetForm();
            $this->page_title = lang('pet_talk_header') . ' | ' . lang('pet_talk_photo_header');
            if($newsFeedType == 'topic'){
                $this->render_page('topic_photo/edit', $this->data);
            }else{
                $this->render_page('post_updated_photo/edit', $this->data);
            } 
        }
    }

    private function _save_photo($type = 'insert', $newsfeed_id = false, $id = 0) {

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
        $newsFeedItem = $this->newsfeed_model->getNewsFeedItem($newsfeed_id);
        $newsFeedType = $newsFeedItem->topic_id ? 'topic' : 'post_updated';

        $this->form_validation->set_rules('status', 'Status', 'required');

        if ($type == 'insert') {

            // upload photo & saving image
            $config['table_media']      = TRUE;
            $config['required']         = TRUE;

            $library_media = new Admin_media($config);  
            $data = $library_media->saveMediaAdmin($data);            

            if ($this->form_validation->run()) {
                // insert media data
                $data ['newfeed_id'] = $newsfeed_id;
                if($newsFeedType == 'topic'){
                    $data ['topic_id'] = $newsFeedItem->topic_id;
                }else{
                    $data ['post_update_id'] = $newsFeedItem->post_update_id;
                }                
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

    private function checkNewsFeed($id){
        $newsFeedItem = $this->newsfeed_model->getNewsFeedItem($id);        
        if (!$newsFeedItem) {
            //redirect if invalid pet_talk id
            $this->messages->add(lang('pet_talk_invalid_id'), "error");
            redirect($this->lang->lang() . '/pet_talk/index');
        }

        if(!($newsFeedItem->post_update_id || $newsFeedItem->topic_id)){
            //redirect if newsfeed is not the post updated or pet topic
            $this->messages->add(lang('pet_talk_invalid_id'), "error");
            redirect($this->lang->lang() . '/pet_talk/index');
        }
        return $newsFeedItem;
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
            // '../global/plugins/ckeditor/ckeditor.js',
            // '../admin/pages/scripts/components-pickers.js',
            // '../js/custom/custom.js',
            // '../js/custom/components-form-tools.js',
            // '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            // '../global/plugins/bootstrap-select/bootstrap-select.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js',
            // //'../js/data/data-confirm.js',
            '../global/plugins/select2/js/select2.min.js',
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            '../pages/scripts/components-date-time-pickers.min.js',            
            '../js/custom/custom-pet.js',
        );

        // $this->js_domready = array(
        //     'Metronic.init();', // init metronic core components
        //     'Layout.init();', // init current layout
        //     'QuickSidebar.init();', // init quick sidebar
        //     'Demo.init();', // init demo features'
        //     'ComponentsFormTools.init();',
        //     'ComponentsPickers.init();',
        //     'Custom.init()',
        //     //'DataConfirm.init();',
        //     'ComponentsDateTimePickers.init();',
        // );
    }

}
