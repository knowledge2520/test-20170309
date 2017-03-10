<?php

class Reviews extends Admin_Controller {

    var $data = array();
    var $module = 'business.reviews';

    function __construct() {
        parent::__construct();

        $this->load->library(array('messages'));
        $this->load->model(array('reviews_model', 'business_model', 'media_model', 'comments_model', 'users/users_model', 'users/permissions_model'));
        $this->load->helper(array('url', 'language', 'permission'));
        $this->lang->load('reviews');
        $this->lang->load('business');
    }

    function index($business_id = false) {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin() && !Permission::check_permission('business.individual')) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $my_review = false;
            if($business_id && Permission::check_permission('business.individual')){
                if(!$this->business_model->check_owner('business_items', $business_id, $this->session->userdata('user_id'))){
                    // return show_404();
                }
                $my_review = true;
            }
            
            $status = $business_id ? array(1,0) : array(1);//$this->input->get('status') ? $this->input->get('status') : false;
            $status_deactivate = array(0);
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;

            $array_field = array('id', 'full_name', 'business_name', 'category_name','content', 'rate', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->reviews_model->getItems('total', $status, $keyword, false, false, $limit, $offset, $business_id, $my_review, $this->session->userdata('user_id'));
            $this->data['total_deactivate'] = $this->reviews_model->getItems('total', $status_deactivate, '', false, false, $limit, $offset, $business_id, $my_review, $this->session->userdata('user_id'));
            $this->data['records'] = $this->reviews_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset, $business_id, $my_review, $this->session->userdata('user_id'));
            $this->data['count'] = $this->reviews_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset, $business_id, $my_review, $this->session->userdata('user_id'));
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

            //get categories
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $review) {
                    if ($business_id == false) {
                        $this->data['records'][$k]->categories = $this->business_model->get_business_categories($review->business_id)->result();
                    }
                }
            }
            $this->data['business_id'] = $business_id;
        }
        
        //set status
        if($this->input->post('set_status')){            
            $id = $this->input->post('id');
            $status = $this->input->post('status');

            $response = array();
            $this->reviews_model->update_status($id, $status);

            $response['redirect'] = true;
            die ( json_encode ( $response ) );
        }

        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $this->load->library('admin_newsfeed');
                foreach ($checked as $id) {
                    $result = $this->admin_newsfeed->deleteReviewAdmin($id);
                    if ($result) {
                        // Add log for deleted
                        $this->business_model->addActivityLog("Deleted","Business Review",$id,"","","");
                        // End
                        $this->messages->add(lang('review_delete_action_success', $id), "success");
                        //log_message('message', 'deleted successful business item id:'.$id);
                    } else {
                        $this->messages->add(lang('review_delete_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail business item id:'.$id);
                    }
                }

                redirect($this->lang->lang() . '/business/reviews/index/' . $business_id);
            }
        }

        //set asset
        $this->_assetIndex();
        $this->page_title = lang('review_header');
        $this->render_page('reviews/index', $this->data);
    }

    public function deactivate() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = array(0);
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;
            
            $array_field = array('id', 'full_name', 'business_name', 'category_name','content', 'rate', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->reviews_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['total_deactivate'] = $this->reviews_model->getItems('total', $status, '', false, false, $limit, $offset);
            $this->data['records'] = $this->reviews_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->reviews_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);
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
            
            //get categories          
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $review) {                    
                    $this->data['records'][$k]->categories = $this->business_model->get_business_categories($review->business_id)->result();
                }
            }
        }

        //set status
        if($this->input->post('set_status')){            
            $id = $this->input->post('id');
            $status = $this->input->post('status');

            $response = array();
            $this->reviews_model->update_status($id, $status);

            $response['redirect'] = true;
            die ( json_encode ( $response ) );
        }
        
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->reviews_model->deleteReview($id);
                }
                if ($result) {
                    // Add log for deleted
                    $this->business_model->addActivityLog("Deleted","Business Review",$id,"","","");
                    // End
                    $this->messages->add(lang('review_delete_action_success', $id), "success");
                    //log_message('message', 'deleted successful business item id:'.$id);
                } else {
                    $this->messages->add(lang('review_delete_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail business item id:'.$id);
                }
                redirect($this->lang->lang() . '/reviews/deactivate');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('review_header');
        $this->render_page('reviews/deactivate', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin() && !Permission::check_permission('business.individual')) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post()) {
                $id = $this->_save_review();
                if ($id) {
                    $this->messages->add(lang('review_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/business/reviews/');
                    } else {
                        redirect($this->lang->lang() . '/business/reviews/edit/' . $id);
                    }
                } else {
                    $this->messages->add(lang('review_create_action_fail'), "error");
                }
            }
            //business data
            $this->data['business'] = $this->business_model->find_all_by('status', '1');

            $this->_assetForm();
            $this->page_title = lang('review_header');
            $this->render_page('reviews/create', $this->data);
        }
    }

    public function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid business id
                $this->messages->add(lang('business_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/index');
            }
            if ($this->input->post()) {
                if ($this->_save_review('update', $id)) {
                    $this->messages->add(lang('review_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/reviews/edit/' . $id);
                } else {
                    $this->messages->add(lang('review_edit_action_fail', $id), "error");
                }
            }
            //review data
            $this->data['record'] = $this->reviews_model->detail($id);
            $this->data['is_admin'] = $this->ion_auth->is_admin();
            // Add log for deleted
            $this->business_model->addActivityLog("Viewed","Business Review",$id,"","","");
            // End
            $this->_assetForm();
            $this->page_title = lang('review_header');
            $this->render_page('reviews/edit', $this->data);
        }
    }

    private function _save_review($type = 'insert', $id = 0) {
        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        //category data
        $data['name'] = $this->input->post('name');
        $data['description'] = $this->input->post('description');
        $data['content'] = $this->input->post('content');
        $data['status'] = $this->input->post('status');
        $data['rate'] = $this->input->post('rate');

        // $this->form_validation->set_rules('name', lang('review_name'), 'required');
        // $this->form_validation->set_rules('description', lang('review_description'), 'required');
        $this->form_validation->set_rules('content', lang('review_content'), 'required');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if ($type == 'insert') {
            if ($this->form_validation->run() == false) {
                //business data
                $this->data['business'] = $this->business_model->find_all_by('status', '1');
                $this->data['record'] = (object) array(
                            'name' => $this->input->post('name'),
                            'description' => $this->input->post('description'),
                            'content' => $this->input->post('content'),
                );
                $this->page_title = lang('review_header');
                $this->render_page('reviews/create', $this->data);
            } else {
                // insert review data
                $data['business_id'] = $this->input->post('business');
                $data['user_id'] = $this->session->userdata('user_id');
                $id = $this->reviews_model->insert($data);
                // Add log for deleted
                $this->business_model->addActivityLog("Created","Business Review",$id,"","","");
                // End
                if ($id) {
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == false) {
                //business data
                $this->data['business'] = $this->business_model->find_all_by('status', '1');
                $this->data['record'] = (object) array(
                            'name' => $this->input->post('name'),
                            'description' => $this->input->post('description'),
                            'content' => $this->input->post('content'),
                            'user' => $this->input->post('user'),
                            'business' => $this->input->post('business'),
                );
                $this->page_title = lang('review_header');
                $this->render_page('reviews/create', $this->data);
            } else {
                // update category data
                $review = $this->reviews_model->detail($id);
                $business = $this->business_model->detail($review->business_id);
                if ($this->reviews_model->update($id, $data)) {
                    
                    if($review->name != $data['name']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business Review',$id,'name',$business->name,$data['name']);
                        // End
                    }
                    if($review->description != $data['description']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business Review',$id,'description',$business->description,$data['description']);
                        // End
                    }
                    if($review->content != $data['content']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business Review',$id,'content',$business->content,$data['content']);
                        // End
                    }
                    if($review->business != $data['business']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business Review',$id,'business',$business->business,$data['business']);
                        // End
                    }
                    if($review->user != $data['user']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business Review',$id,'user',$business->user,$data['user']);
                        // End
                    }
                    if($review->rate != $data['rate']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business Review',$id,'rate',$business->user,$data['rate']);
                        // End
                    }
                    if($review->status != $data['status']){
                        // Add log for updated
                        $oldStatus = 'Active';
                        if($business->status == 0){
                            $oldStatus = 'Deactive';
                        }
                        $newStatus = 'Active';
                        if($data['status'] == 0){
                            $newStatus = 'Deactive';
                        }
                        $this->business_model->addActivityLog("Updated",'Business Review',$id,'status',$oldStatus,$newStatus);
                        // End
                    }
                    $return = $id;
                }
            }
        }
        return $return;
    }

    public function mediaList($review_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$review_id) {
                //redirect if invalid review id
                $this->messages->add(lang('review_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/reviews/index');
            }

            //$status 		= $this->input->get('status') ? $this->input->get('status') : 0;
            $status = false;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;

            $array_field = array('id', 'name', 'full_name', 'address', 'hour', 'phone', 'website', 'status', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'created_date';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->media_model->getItems('total', $status, $keyword, false, false, $limit, $offset, 'review_id', $review_id, false, false, 'review');
            $this->data['records'] = $this->media_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset, 'review_id', $review_id, false, false, 'review');
            $this->data['count'] = $this->media_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset, 'review_id', $review_id, false, false, 'review');

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
            $this->data['is_admin'] = $this->ion_auth->is_admin();
            
        }
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->media_model->delete($id);
                }
                if ($result) {
                    // Add log for deleted
                    $this->business_model->addActivityLog("Deleted","Business Media",$id,"","","");
                    // End
                    $this->messages->add('deleted successful media id:' . $id, "success");
                    log_message('message', 'deleted successful media id:' . $id);
                } else {
                    $this->messages->add(lang('deleted fail media id:') . $this->ion_auth_model->error, "error");
                    log_message('debug', 'deleted fail media id:' . $id);
                }
                redirect($this->lang->lang() . '/business/reviews/mediaList/' . $review_id);
            }
        }
        //set asset
        $this->data['review_id'] = $review_id;
        $this->_assetIndex();
        $this->page_title = lang('review_header') . ' | ' . lang('review_media_list_header');
        $this->render_page('reviews/media_list', $this->data);
    }

    public function mediaEdit($review_id = false, $media_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$review_id) {
                //redirect if invalid review id
                $this->messages->add(lang('review_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/reviews/index');
            }
            if (!$media_id) {
                //redirect if invalid media id
                $this->messages->add(lang('review_media_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/reviews/mediaList');
            }

            if ($this->input->post()) {
                if ($this->_save_media('update', $review_id, $media_id)) {
                    $this->messages->add(lang('review_media_edit_action_success', $media_id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/mediaEdit/' . $review_id . '/' . $media_id);
                } else {
                    $this->messages->add(lang('review_media_edit_action_fail', $media_id), "error");
                }
            }
            //member data
            $this->data['record'] = $this->media_model->detail($media_id);
            // Add log for View
            $this->business_model->addActivityLog("Viewed","Business Media",$id,"","","");
            // End
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('review_media_invalid_id'), "error");
                redirect($this->lang->lang() . '/banners/index');
            }
            $this->data['review_id'] = $review_id;
            $this->_assetForm();
            $this->page_title = lang('review_header') . ' | ' . lang('review_media_list_header');
            $this->render_page('reviews/media_edit', $this->data);
        }
    }

    public function mediaCreate($review_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$review_id) {
                //redirect if invalid business id
                $this->messages->add(lang('review_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/reviews/index');
            }

            if ($this->input->post()) {
                $id = $this->_save_media('insert', $review_id);
                if ($id) {
                    $this->messages->add(lang('review_media_create_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/reviews/mediaEdit/' . $review_id . '/' . $id);
                } else {
                    $this->messages->add(lang('review_media_create_action_fail'), "error");
                }
            }
            $this->data['review_id'] = $review_id;
            $this->_assetForm();
            $this->page_title = lang('review_header') . ' | ' . lang('review_media_list_header');
            $this->render_page('reviews/media_create', $this->data);
        }
    }

    private function _save_media($type = 'insert', $review_id = false, $media_id = 0) {

        $this->load->library('image_lib');

        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        //media data
        $data['status'] = $this->input->post('status');

        //config upload media
        $path = DEFAULT_PATH_ADMIN . $this->config->item('listings_path');
        $upload_field_name  = 'path';
        $field_image = 'source';
        $field_image_thumb = 'photo_thumb';

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($field_image, $field_image_thumb),
        );

        $this->form_validation->set_rules('status', 'Status', 'required');

        if ($type == 'insert') {

            // upload photo & saving image
            $config['table_media']      = TRUE;
            $config['required']         = TRUE;
            $library_media = new Admin_media($config);  
            $data = $library_media->saveMediaAdmin($data);  

            if ($this->form_validation->run()) {
                // insert media data
                $data ['review_id'] = $review_id;
                $data ['user_id'] = $this->session->userdata('user_id');

                $id = $this->media_model->insert($data);
                if ($id) {
                    // Add log for Created
                    $this->business_model->addActivityLog("Created","Business Media",$id,"","","");
                    // End
                    $return = $id;
                }
            }

            // $this->load->helper('upload');
            // if ($image_data = do_upload($this->config->item('listings_path'), 'path')) {
            //     $data ['source'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $image_data ['file_name'];

            //     // create photo thumb
            //     $this->load->helper('image');
            //     resizeImage($image_data ['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
            //     $file_name = explode('.', $image_data ['file_name']);
            //     $data ['photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $file_name [0] . '_thumb.' . $file_name [1];

            //     // insert media data
            //     $data ['review_id'] = $review_id;
            //     $data ['user_id'] = $this->session->userdata('user_id');
            //     $id = $this->media_model->insert($data);
            //     if ($id) {
            //         $return = $id;
            //     }
            // } else {
            //     $this->messages->add($this->upload->display_errors(), "error");
            // }
        } elseif ($type == 'update') {

            //load item data
            $item = $this->media_model->find($media_id);

            // upload photo
            $config['type'] = 'update';
            $config['item'] = $item;
            $config['table_media'] = TRUE;

            $library_media = new Admin_media($config);
            $data = $library_media->saveMediaAdmin($data);

            // update media data
            $data ['user_id'] = $this->session->userdata('user_id');
            if ($this->media_model->update($media_id, $data)) {
                // Add log for Created
                $this->business_model->addActivityLog("Updated","Business Media",$id,"","","");
                // End
                $return = $media_id;
            }

            // $media_image = $_FILES['path']['name'];
            // if (isset($media_image) && $media_image != '') {
                // saving image
                // $this->load->helper('upload');
                // if ($image_data = do_upload($this->config->item('listings_path'), 'path')) {
                //     //delete old image
                //     //if(unlink($item->path)){
                //     //    log_message('info','deleted old image of item id='.$media_id );
                //     //}
                //     $data['source'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $image_data['file_name'];

                //     //create photo thumb
                //     $this->load->helper('image');
                //     resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                //     $file_name = explode('.', $image_data['file_name']);
                //     $data['photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $file_name[0] . '_thumb.' . $file_name[1];

                //     // update item data
                //     if ($this->media_model->update($media_id, $data)) {
                //         $return = $media_id;
                //     }
                // } else {
                //     $this->messages->add($this->upload->display_errors(), "error");
                // }
            // } else {
            //     // update item data
            //     if ($this->media_model->update($media_id, $data)) {
            //         $return = $media_id;
            //     }
            // }
        }
        return $return;
    }

    public function commentList($review_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$review_id) {
                //redirect if invalid review id
                $this->messages->add(lang('review_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/reviews/index');
            }

            //$status 		= $this->input->get('status') ? $this->input->get('status') : 0;
            $status = false;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            //get data
            $this->data['total'] = $this->comments_model->getItems('total', $status, $keyword, false, false, $limit, $offset, 'review_id', $review_id);
            $this->data['records'] = $this->comments_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, 'review_id', $review_id);
            $this->data['count'] = $this->comments_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, 'review_id', $review_id);

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
            $this->data['is_admin'] = $this->ion_auth->is_admin();
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
                    $this->messages->add(lang('review_comment_edit_action_success', $id), "success");
                    log_message('info', 'deleted successful comment id:' . $id);
                } else {
                    $this->messages->add(lang('review_comment_edit_action_fail', $id), "error");
                    log_message('info', 'deleted fail comment id:' . $id);
                }
                redirect($this->lang->lang() . '/business/reviews/commentList/' . $review_id);
            }
        }
        //set asset
        $this->data['review_id'] = $review_id;
        $this->_assetIndex();
        $this->page_title = lang('review_header') . ' | ' . lang('review_comment_list_header');
        $this->render_page('reviews/comment_list', $this->data);
    }

    public function commentEdit($review_id = false, $comment_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$review_id) {
                //redirect if invalid review id
                $this->messages->add(lang('review_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/reviews/index');
            }
            $comment = $this->comments_model->detail($comment_id);
            if (!$comment_id || !$comment) {
                //redirect if invalid comment id
                $this->messages->add(lang('review_comment_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/reviews/commentList');
            } 
            if ($this->input->post()) {
                if ($this->_save_comment('update', $review_id, $comment_id)) {
                    $this->messages->add(lang('review_comment_edit_action_success', $comment_id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/reviews/commentEdit/' . $review_id . '/' . $comment_id);
                } else {
                    $this->messages->add(lang('review_comment_edit_action_fail', $comment_id), "error");
                }
            }
            //member data
            $this->data['record'] = $this->comments_model->detail($comment_id);
            // Add log for Created
            $this->business_model->addActivityLog("Viewed","Business Comment",$id,"","","");
            // End
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('review_comment_invalid_id'), "error");
                redirect($this->lang->lang() . '/reviews/commentList');
            }
            $this->data['review_id'] = $review_id;
            $this->_assetForm();
            $this->page_title = lang('review_header') . ' | ' . lang('review_comment_list_header');
            $this->render_page('reviews/comment_edit', $this->data);
        }
    }

    private function _save_comment($type = 'update', $review_id = false, $comment_id = 0) {

        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        //comment data
        $data['status'] = $this->input->post('status');
        $data['content'] = $this->input->post('content');

        $this->form_validation->set_rules('status', lang('review_status'), 'required');
        $this->form_validation->set_rules('content', lang('business_content'), 'required');
        if ($this->form_validation->run() == TRUE) {            
            $this->comments_model->update($comment_id, $data);
            // Add log for Updated
            $this->business_model->addActivityLog("Updated","Business Comment",$id,"","","");
            // End
            $return = TRUE;
        }

        return $return;
    }

    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetIndex() {
        $this->assets_css['page_style'] = array(
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
            '../global/plugins/datatables/datatables.min.css',
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/select2/js/select2.min.js',
            '../global/scripts/datatable.js',
            '../global/plugins/datatables/datatables.min.js',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
            '../js/custom/custom-table-advanced.js',
            '../js/custom/custom.js',
        );

        $this->js_domready = array(
            // 'App.init();', // init metronic core components
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
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css'
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

        $this->js_domready = array(
            // 'Metronic.init();', // init metronic core components
            // 'Layout.init();', // init current layout
            // 'QuickSidebar.init();', // init quick sidebar
            // 'Demo.init();', // init demo features'
            // 'ComponentsFormTools.init();',
            // 'ComponentsPickers.init();',
            // 'TableAdvanced.init();',
            'ComponentsDateTimePickers.init();',
        );
    }

}
