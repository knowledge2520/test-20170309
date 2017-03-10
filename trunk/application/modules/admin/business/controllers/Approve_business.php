<?php

class Approve_business extends Admin_Controller {

    var $data = array();
    var $module = 'business.approve_business';

    function __construct() {
        parent::__construct();

        $this->load->library(array('ion_auth', 'messages'));
        $this->load->helper(array('url', 'language'));

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load(array('ion_auth', 'business'));
        $this->load->model(array('auth/ion_auth_model', 'business_model', 'categories_model', 'media_model', 'users/permissions_model'));
        $this->load->helper('permission');
    }

    function index() {

        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = array(0);            //$this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;
            
            $array_field = array('id', 'name', 'full_name', 'address', 'hour', 'phone', 'website', 'status', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->business_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->business_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->business_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);

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

            //list the categories
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $business) {
                    $this->data['records'][$k]->categories = $this->business_model->get_business_categories($business->id)->result();
                }
            }
        }
        if ($this->input->post()) {
            
        }
        // Deleting anything?
        if ($this->input->post('btn_reject')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->business_model->rejectBusiness($id);
                    if ($result) {
                        // Add log for deleted
                        $this->business_model->addActivityLog("Deleted",'Business',$id,"","","");
                        // End
                        $this->messages->add(lang('business_reject_action_success', $id), "success");
                        //log_message('message', 'deleted successful business item id:'.$id);
                    } else {
                        $this->messages->add(lang('business_reject_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail business item id:'.$id);
                    }
                }

                redirect($this->lang->lang() . '/business/approve_business/index');
            }
        }
        // Active anything?
        if ($this->input->post('btn_active')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $business = $this->business_model->detail($id);
                    if ($business) {
                        $check_active_busines = $this->business_model->check_active_business(trim($business->name), trim($business->address));
                        if ($check_active_busines) {
                            $this->business_model->rejectBusiness($id);
                            $this->messages->add(lang('business_approve_action_fail', $id) . '. ' . lang('business_duplicate'), "error");
                        } else {
                            $result = $this->business_model->activeBusiness($id);
                            if ($result) {
                                $this->messages->add(lang('business_approve_action_success', $id), "success");
                                //log_message('message', 'deleted successful business item id:'.$id);
                            } else {
                                $this->messages->add(lang('business_approve_action_fail', $id), "error");
                                //log_message('debug', 'deleted fail business item id:'.$id);
                            }
                        }
                    }
                }

                redirect($this->lang->lang() . '/business/approve_business/index');
            }
        }
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->business_model->deleteBusiness($id);
                    if ($result) {
                        $this->messages->add(lang('business_delete_action_success', $id), "success");
                        //log_message('message', 'deleted successful business item id:'.$id);
                    } else {
                        $this->messages->add(lang('business_delete_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail business item id:'.$id);
                    }
                }

                redirect($this->lang->lang() . '/business/approve_business/index');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('business_approve_header');
        $this->render_page('approve/index', $this->data);
    }

    public function detail($id) {
        if (!$id) {
            //redirect if invalid business id
            $this->messages->add(lang('business_invalid_id'), "error");
            redirect($this->lang->lang() . '/business/approve_business/index');
        }
        $this->load->view('detail');
    }

    public function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid business id
                $this->messages->add(lang('business_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/approve/index');
            }

            if ($this->input->post()) {
                if ($this->_save_business('update', $id)) {
                    $this->messages->add(lang('business_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/approve_business/edit/' . $id);
                } else {
                    $this->messages->add(lang('business_edit_action_fail', $id), "error");
                }
            }
            //business data
            $this->data['categories_items'] = $this->categories_model->find_all_by('status', 1);
            $this->data['record'] = $this->business_model->detail($id);
            // Add log for view
            $this->business_model->addActivityLog("Viewed","Business",$id,"","","");
            // End
            if (empty($this->data['record'])) {
                //redirect if invalid business id
                $this->messages->add(lang('business_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/approve_business/index');
            }
            $hour = explode("-", $this->data['record']->hour);
            if (sizeof($hour) == 2) {
                $this->data['record']->start_time = str_replace("h", ":", $hour[0]);
                $this->data['record']->end_time = str_replace("h", ":", $hour[1]);
            } else {
                $this->data['record']->start_time = "0:00";
                $this->data['record']->end_time = str_replace("h", ":", $hour[0]);
            }

            //list the business
            $this->data['record']->categories = $this->business_model->get_business_categories($id)->result();
            //load map google
            $this->load->library('googlemaps');

            $location = $this->data['record']->latitude . ', ' . $this->data['record']->longitude;
            //var_dump($location);exit;
            $config['center'] = $location;
            $config['zoom'] = '16';
            $config['disableDoubleClickZoom'] = 'true';

            $this->googlemaps->initialize($config);

            $marker = array();
            $marker['position'] = $location;
            $marker['draggable'] = true;
            $marker['ondragend'] = '
            document.getElementById("lat").innerHTML =  \'<input type="text" class="form-control" name="latitude" value= \' + event.latLng.lat() + \' > \';
            document.getElementById("long").innerHTML = \'<input type="text" class="form-control" name="longitude" value= \' + event.latLng.lng() + \' > \' ';

            $this->googlemaps->add_marker($marker);
            $this->data['map'] = $this->googlemaps->create_map();

            $this->_assetForm();
            $this->page_title = lang('business_approve_header');
            $this->render_page('approve/edit', $this->data);
        }
    }

    private function _save_business($type = 'insert', $id = 0) {

        $this->load->library('image_lib');

        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        //business data
        $data['name'] = trim($this->input->post('name'));
        $data['address'] = trim($this->input->post('address_location'));
        $data['hour'] = trim($this->input->post('hour'));
        $data['phone'] = trim($this->input->post('phone'));
        $data['website'] = trim($this->input->post('website'));
        $data['latitude'] = trim($this->input->post('latitude'));
        $data['longitude'] = trim($this->input->post('longitude'));
        $data['status'] = $this->input->post('status');

        $categories = $this->input->post('categories');

        $path = DEFAULT_PATH_ADMIN . $this->config->item('listings_path');
        $upload_field_name  = 'photo';
        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($upload_field_name),
        );

        $this->form_validation->set_rules('name', lang('business_name'), 'required');
        $this->form_validation->set_rules('address_location', lang('business_location'), 'required|callback_check_location');
        $this->form_validation->set_rules('categories[]', lang('business_categories'), 'required');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        $this->form_validation->set_rules('latitude', lang('business_latitude'), 'numeric');
        $this->form_validation->set_rules('longitude', lang('business_longitude'), 'numeric');
        if ($type == 'insert') {
            if ($this->form_validation->run($this) == false) {
                $this->data['record'] = (object) array(
                            'name' => $this->input->post('name'),
                            'address' => $this->input->post('address'),
                            'hour' => $this->input->post('hour'),
                            'phone' => $this->input->post('phone'),
                            'website' => $this->input->post('website'),
                            'latitude' => $this->input->post('latitude'),
                            'longitude' => $this->input->post('longitude'),
                            'status' => $this->input->post('status'),
                            'address_location' => $this->input->post('address_location'),
                );
                $this->data['categories_items'] = $this->categories_model->find_all_by('status', 1);
                $this->page_title = lang('business_approve_header');
                $this->render_page('approve/create', $this->data);
            } else {
                //set location
                $location = get_location_from_address($data['address']);
                if (!$data['latitude'] || !$data['latitude']) {
                    $data['latitude'] = !empty($location) ? $location['lat'] : LISTING_DEFAULT_LATITUDE;
                    $data['longitude'] = !empty($location) ? $location['long'] : LISTING_DEFAULT_LONGITUDE;
                }
                
                // upload photo & saving image
                $library_media = new Admin_media($config);  
                $data = $library_media->saveMediaAdmin($data);

                // $business_image = $_FILES['photo']['name'];
                // if (isset($business_image) && $business_image != '') {
                    // saving image
                    // $this->load->helper('upload');
                    // if ($image_data = do_upload($this->config->item('listings_path'), 'photo')) {
                    //     //$data['photo'] = $this->config->item ( 'admin_upload_path' ).$this->config->item ( 'listings_path' ).$image_data['file_name'];
                    //     //create photo thumb
                    //     $this->load->helper('image');
                    //     resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, FALSE);
                    //     $file_name = explode('.', $image_data['file_name']);
                    //     $data['photo'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $file_name[0] . '_thumb.' . $file_name[1];
                    // }
                // }
                // insert business data
                $id = $this->business_model->insert($data);
                // Add log for inserted
                $this->business_model->addActivityLog("Created","Business",$id,"","","");
                // End
                if ($id) {
                    if (isset($categories) && !empty($categories)) {
                        $this->business_model->add_to_category($categories, $id);
                    }
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run($this) == false) {
                //business data
                $this->data['categories_items'] = $this->categories_model->find_all_by('status', 1);
                $this->data['record'] = $this->business_model->detail($id);
                $this->data['record'] = (object) array(
                            'name' => $this->input->post('name'),
                            'address' => $this->input->post('address'),
                            'hour' => $this->input->post('hour'),
                            'phone' => $this->input->post('phone'),
                            'website' => $this->input->post('website'),
                            'latitude' => $this->input->post('latitude'),
                            'longitude' => $this->input->post('longitude'),
                            'status' => $this->input->post('status'),
                            'categories' => $this->business_model->get_business_categories($id)->result(),
                            'address_location' => $this->input->post('address_location'),
                );
                $this->page_title = lang('business_approve_header');
                $this->render_page('approve/edit', $this->data);
            } else {
                //set location if type new address
                if($data['address'] != $business->address || !$data['latitude'] || !$data['latitude']){
                    $location = get_location_from_address($data['address']);
                    $data['latitude'] = !empty($location) ? $location['lat'] : LISTING_DEFAULT_LATITUDE;
                    $data['longitude'] = !empty($location) ? $location['long'] : LISTING_DEFAULT_LONGITUDE;
                }
                
                //load business data
                $business = $this->business_model->find($id);
                $_POST['id'] = $id;
                // upload photo
                $config['type'] = 'update';
                $config['item'] = $item;
                $library_media = new Admin_media($config);                

                $data = $library_media->saveMediaAdmin($data);
                // upload photo
                $business_image = $_FILES['photo']['name'];
                // if (isset($business_image) && $business_image != '') {
                    // saving image
                    // $this->load->helper('upload');
                    // if ($image_data = do_upload($this->config->item('listings_path'), 'photo')) {
                    //     //delete old image
                    //     //if(unlink($_SERVER['DOCUMENT_ROOT'].'/'.$business->path)){
                    //     //    log_message('info','deleted old image of banner id='.$id );
                    //     //}
                    //     //$data['photo'] = $this->config->item ( 'admin_upload_path' ).$this->config->item ( 'listings_path' ).$image_data['file_name'];
                    //     //create photo thumb
                    //     $this->load->helper('image');
                    //     resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                    //     $file_name = explode('.', $image_data['file_name']);
                    //     $data['photo'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $file_name[0] . '_thumb.' . $file_name[1];
                    // }
                // }

                // update categories data
                if ($this->business_model->update($id, $data)) {
                    if (isset($categories) && !empty($categories)) {
                        $this->business_model->remove_from_category('', $id);
                        foreach ($categories as $c) {
                            $this->business_model->add_to_category($c, $id);
                        }
                    }

                    if($business->address != $data['address']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business',$id,'address',$business->address,$data['address']);
                        // End
                    }
                    if($business->name != $data['name']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business',$id,'name',$business->name,$data['name']);
                        // End
                    }
                    if($business->hour != $data['hour']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business',$id,'hour',$business->hour,$data['hour']);
                        // End
                    }
                    if($business->phone != $data['phone']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business',$id,'phone',$business->phone,$data['phone']);
                        // End
                    }
                    if($business->website != $data['website']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business',$id,'website',$business->website,$data['website']);
                        // End
                    }
                    if($business->address_location != $data['address_location']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business',$id,'address_location',$business->address_location,$data['address_location']);
                        // End
                    }
                    if($business->latitude != $data['latitude']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business',$id,'latitude',$business->latitude,$data['latitude']);
                        // End
                    }
                    if($business->longitude != $data['longitude']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business',$id,'longitude',$business->longitude,$data['longitude']);
                        // End
                    }
                    if($business->status != $data['status']){
                        // Add log for updated
                        $oldStatus = 'Active';
                        if($business->status == 0){
                            $oldStatus = 'Deactive';
                        }
                        $newStatus = 'Active';
                        if($data['status'] == 0){
                            $newStatus = 'Deactive';
                        }
                        $this->business_model->addActivityLog("Updated",'Business',$id,'status',$oldStatus,$newStatus);
                        // End
                    }

                    $return = $id;
                }
            }
        }
        return $return;
    }
    
    public function check_location($str) {
        $location = get_location_from_address($str);
        if (empty($location)) {
            $this->form_validation->set_message('check_location', "You have entered an invalid address. Please check or re-enter the listing's address to proceed. Thank you.");
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    public function mediaList($business_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$business_id) {
                //redirect if invalid business id
                $this->messages->add(lang('business_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/approve_business/index');
            }

            //$status 		= $this->input->get('status') ? $this->input->get('status') : 0;
            $status = false;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            //get data
            $this->data['total'] = $this->media_model->getItems('total', $status, $keyword, false, false, $limit, $offset, 'business_id', $business_id);
            $this->data['records'] = $this->media_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, 'business_id', $business_id);
            $this->data['count'] = $this->media_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, 'business_id', $business_id);

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
                        $this->messages->add('deleted successful media id:' . $id, "success");
                        //log_message('message', 'deleted successful media id:'.$id);
                    } else {
                        $this->messages->add(lang('business_media_delete_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail media id:'.$id);
                    }
                    redirect($this->lang->lang() . '/business/approve_business/mediaList/' . $business_id);
                }
            }
            //set asset
            $this->data['business_id'] = $business_id;
            $this->_assetIndex();
            $this->page_title = lang('business_approve_header') . ' | ' . lang('business_media_header');
            $this->render_page('approve/media_list', $this->data);
        }
    }

    public function mediaEdit($business_id = false, $media_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$business_id) {
                //redirect if invalid business id
                $this->messages->add(lang('business_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/approve_business/index');
            }
            if (!$media_id) {
                //redirect if invalid business id
                $this->messages->add(lang('business_media_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/approve_business/mediaList');
            }

            if ($this->input->post()) {
                if ($this->_save_media('update', $business_id, $media_id)) {
                    $this->messages->add(lang('business_media_edit_action_success', $media_id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/approve_business/mediaEdit/' . $business_id . '/' . $media_id);
                } else {
                    $this->messages->add(lang('business_media_edit_action_fail', $media_id), "error");
                }
            }
            //member data
            $this->data['record'] = $this->media_model->detail($media_id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('business_media_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/approve_business/mediaList');
            }
            $this->data['business_id'] = $business_id;
            $this->_assetForm();
            $this->page_title = lang('business_approve_header') . ' | ' . lang('business_media_header');
            $this->render_page('approve/media_edit', $this->data);
        }
    }

    public function mediaCreate($business_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$business_id) {
                //redirect if invalid business id
                $this->messages->add(lang('business_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/approve_business/index');
            }

            if ($this->input->post()) {
                $id = $this->_save_media('insert', $business_id);
                if ($id) {
                    $this->messages->add(lang('business_media_create_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/approve_business/mediaEdit/' . $business_id . '/' . $id);
                } else {
                    $this->messages->add(lang('business_media_create_action_fail'), "error");
                }
            }
            $this->data['business_id'] = $business_id;
            $this->_assetForm();
            $this->page_title = lang('business_approve_header') . ' | ' . lang('business_media_header');
            $this->render_page('approve/media_create', $this->data);
        }
    }

    private function _save_media($type = 'insert', $business_id = false, $media_id = 0) {
        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        $this->load->library('image_lib');

        //media data
        $data['status'] = $this->input->post('status');
        
        $path = DEFAULT_PATH_ADMIN . $this->config->item('listings_path');
        $upload_field_name  = 'path';
        $field_image = 'source';
        $field_image_thumb = 'photo_thumb';

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($field_image, $field_image_thumb),
            'table_media'           => TRUE,
            'required'              => TRUE
        );

        $this->form_validation->set_rules('status', 'Status', 'required');

        if ($type == 'insert') {
            // upload photo & saving image
            $library_media = new Admin_media($config);  
            $data = $library_media->saveMediaAdmin($data);

            if ($this->form_validation->run()) {
                //insert media data
                $data ['business_id'] = $business_id;
                $data ['user_id'] = $this->session->userdata('user_id');
                $id = $this->media_model->insert($data);
                if ($id) {
                    $return = $id;
                }
            }
            // upload photo & saving image
            // $this->load->helper('upload');
            // if ($image_data = do_upload($this->config->item('listings_path'), 'path')) {
            //     $data ['source'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $image_data ['file_name'];

            //     // create photo thumb
            //     $this->load->helper('image');

            //     resizeImage($image_data ['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);

            //     $file_name = explode('.', $image_data ['file_name']);
            //     $data ['photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $file_name [0] . '_thumb.' . $file_name [1];

            //     // insert media data
            //     $data ['business_id'] = $business_id;
            //     $data ['user_id'] = $this->session->userdata('user_id');
            //     $id = $this->media_model->insert($data);
            //     if ($id) {
            //         $return = $id;
            //     }
            // } else {
            //     $this->messages->add($this->upload->display_errors(), "error");
            // }
        } elseif ($type == 'update') {

            //load banner data
            $item = $this->media_model->find($media_id);

            // upload photo
            $config['type'] = 'update';
            $config['item'] = $item;
            $library_media = new Admin_media($config);                

            $data = $library_media->saveMediaAdmin($data);

            // upload photo
            // $media_image = $_FILES['path']['name'];
            // if (isset($media_image) && $media_image != '') {
                // saving image
                // $this->load->helper('upload');
                // if ($image_data = do_upload($this->config->item('listings_path'), 'path')) {
                //     //delete old image
                //     //if(unlink($banner->path)){
                //     //    log_message('info','deleted old image of banner id='.$media_id );
                //     //}
                //     $data['source'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $image_data['file_name'];

                //     //create photo thumb
                //     $this->load->helper('image');
                //     resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                //     $file_name = explode('.', $image_data['file_name']);
                //     $data['photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $file_name[0] . '_thumb.' . $file_name[1];
                // }
            // }

            // update media data
            if ($this->media_model->update($media_id, $data)) {
                $return = $media_id;
            }
        }
        return $return;
    }

    function update_location($id = false) {
        if (!id) {
            $this->messages->add('Invalid ID', 'error');
            redirect('business/approve_business');
        }
        $business = $this->business_model->detail($id);
        if ($business) {
            $this->load->helper('site');
            $location = get_location_from_address($business->address);
            if (!empty($location)) {
                $data = array(
                    'latitude' => $location['lat'],
                    'longitude' => $location['long']
                );

                $this->business_model->update($id, $data);
                $this->messages->add('Update Location Success ID: ' . $business->id, 'success');
            } else {
                $this->messages->add('Update Location Fail ID: ' . $business->id, 'error');
            }
        }
        redirect('business/approve_business');
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
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css'
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
