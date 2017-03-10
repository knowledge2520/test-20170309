<?php

class Pets extends Admin_Controller {

    var $data = array();
    var $module = 'pets';

    function __construct() {
        parent::__construct();

        $this->lang->load('pets');
        $this->load->model(array('pets_model', 'users/users_model', 'pet_types_model', 'users/permissions_model'));
        $this->load->library('messages');
        $this->load->helper('permission');
    }

    public function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = 1; //$this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;
            
            $array_field = array('id', 'name', 'dob', 'pet_type_name', 'user_name', 'breed', 'sex', 'color', 'purchase_date', 'origin', 'microchip', 'badge_id_code');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->pets_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->pets_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->pets_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);
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
                foreach ($checked as $id) {
                    $result = $this->pets_model->deletePet($id);
                    if ($result) {
                        $this->messages->add(lang('pet_delete_action_success', $id), "success");
                        //log_message('message', 'deleted successful pet id:'.$id);
                    } else {
                        $this->messages->add(lang('pet_delete_action_fail', $id), "error");
                        //log_message('debug', 'deleted fail pet id:'.$id);
                    }
                }

                redirect($this->lang->lang() . '/pets/index');
            }
        }
        if ($this->data['records']) {
            foreach ($this->data['records'] as $k => $pet) {
                $pet_type = $this->pet_types_model->detail($pet->type);
                $user = $this->users_model->detail($pet->user_id);
                $this->data['records'][$k]->type_pet = isset($pet_type) && !empty($pet_type) ? $pet_type->name : '';
                $this->data['records'][$k]->user_name = isset($user) && !empty($user) ? $user->first_name . ' ' . $user->last_name : '';
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('pet_header');
        $this->render_page('index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {

            if ($this->input->post()) {
                $id = $this->_save_pet();
                if ($id) {
                    $this->messages->add(lang('pet_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/pets');
                    }
                    else{
                        redirect($this->lang->lang() . '/pets/edit/' . $id);
                    }                    
                } else {
                    $this->messages->add(lang('pet_create_action_fail'), "error");
                }
            }
            //load users
            $this->data['users'] = $this->users_model->find_all();
            $this->data['pet_types'] = $this->pet_types_model->find_all();
            $this->_assetForm();
            $this->page_title = lang('pet_header');
            $this->render_page('create', $this->data);
        }
    }

    public function edit($id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid pet id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            }
            
            if ($this->input->post()) {
                if ($this->_save_pet('update', $id)) {
                    $this->messages->add(lang('pet_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/pets/edit/' . $id);
                } else {
                    $this->messages->add(lang('pet_edit_action_fail', $id), "error");
                }
            }
            //pet data
            $this->data['record'] = $this->pets_model->detail($id);

            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            }
//        var_dump($this->data);exit;
            $this->data['users'] = $this->users_model->find_all();
            $this->data['pet_types'] = $this->pet_types_model->find_all();
            $this->data['pet_id'] = $id;

            $this->_assetForm();
            $this->page_title = lang('pet_header');
            $this->render_page('edit', $this->data);
        }
    }

    public function deleteProfilePhoto($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->pets_model->deletePhoto($id)) {
                $this->messages->add('delete profile photo success' . $id, "success");
            } else {
                $this->messages->add('delete profile photo fail' . $id, "error");
            }
            $this->data['record'] = $this->pets_model->detail($id);
            $this->data['users'] = $this->users_model->find_all();
            $this->data['pet_types'] = $this->pet_types_model->find_all();
            $this->data['pet_id'] = $id;

            redirect(site_url($this->lang->lang() . '/pets/edit/' . $id));
        }
    }

    private function _save_pet($type = 'insert', $id = 0) {

        $this->load->library('image_lib');
        
        $return = false;
        // make sure we only pass in the fields we want
        $data = array();
        //pet data
        $date_of_birth = $this->input->post('dob');
        $sql_dob_date = implode(explode('/', $date_of_birth));

        $purchase_date = $this->input->post('purchase_date');
        $sql_purchase_date = implode(explode('/', $purchase_date));

        $this->data['record'] = array(
                    'name' => $this->input->post('name'),
                    'type' => $this->input->post('type'),
                    'breed' => $this->input->post('breed'),
                    'sex' => $this->input->post('sex'),
                    'color' => $this->input->post('color'),
                    'origin' => $this->input->post('origin'),
                    'microchip' => $this->input->post('microchip'),
                    'dob' => mysql_to_unix($sql_dob_date),
                    'purchase_date' => mysql_to_unix($sql_purchase_date),
        );
        $this->form_validation->set_rules('name', lang('pet_name'), 'required');
        $this->form_validation->set_rules('type', lang('pet_type'), 'required');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        
        //config upload media
        $path = DEFAULT_PATH_ADMIN . $this->config->item('pet_path');
        $upload_field_name  = 'profile_photo';
        $field_image = 'profile_photo';
        $field_image_thumb = 'profile_photo_thumb';    

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($field_image, $field_image_thumb),
        );

        if ($type == 'insert') {
            if ($this->form_validation->run() == FALSE) {
                //load users
                $this->data['users'] = $this->users_model->find_all();
                $this->data['pet_types'] = $this->pet_types_model->find_all();
                $this->page_title = lang('pet_header');
                $this->render_page('create', $this->data);
            } else {
                $this->data['record'] = array_merge(
                        $this->data['record'], 
                        array(
                            'user_id' => $this->input->post('user')
                        )
                );
                $data = array();
                // upload photo & saving image
                $library_media = new Admin_media($config);  
                $data = $library_media->saveMediaAdmin($data);
                $this->data['record'] = array_merge($this->data['record'], $data);

                // $pet_image = $_FILES['profile_photo']['name'];
                // if (isset($pet_image) && $pet_image != '') {
                //     // saving image
                //     $this->load->helper('upload');
                //     if ($image_data = do_upload($this->config->item('pet_path'), 'profile_photo')) {
                //         //$this->data['record']->profile_photo = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $image_data['file_name'];

                //         //create photo thumb
                //         $this->load->helper('image');
                //         resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                //         $file_name = explode('.', $image_data['file_name']);
                //         //$this->data['record']->profile_photo_thumb = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $file_name[0] . '_thumb.' . $file_name[1];
                        
                //         $this->data['record'] = array_merge(
                //                 $this->data['record'], 
                //                 array(
                //                     'profile_photo' => $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $image_data['file_name'], 
                //                     'profile_photo_thumb' => $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $file_name[0] . '_thumb.' . $file_name[1]
                //                 )
                //         );
                //     }
                // }
                
                // insert member data
                $id = $this->pets_model->insert($this->data['record']);
                if ($id) {
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == FALSE) {
                //pet data
                $this->data['record'] = $this->pets_model->detail($id);


                if (empty($this->data['record'])) {
                    //redirect if invalid pet id
                    $this->messages->add(lang('pet_invalid_id'), "error");
                    redirect($this->lang->lang() . '/pets/index');
                }

                $this->data['users'] = $this->users_model->find_all();
                $this->data['pet_types'] = $this->pet_types_model->find_all();
                $this->page_title = lang('pet_header');
                $this->render_page('edit', $this->data);
            } else {
                //load pet data
                $pet = $this->pets_model->detail($id);

                //update badge id
                $badge_id = $this->input->post('badge_id');
                if($badge_id){
                	$result = $this->pets_model->updateBadgeId($id, $badge_id);
                	if(!$result['result']){
                		$this->messages->add($result['message'], "error");
                		return false;
                	}                	 
                }
                
                // upload photo
                $data = array();
                $config['type'] = 'update';
                $config['item'] = $item;
                $library_media = new Admin_media($config);  
                $data = $library_media->saveMediaAdmin($data);
                $this->data['record'] = array_merge($this->data['record'], $data);

                // $pet_image = $_FILES['profile_photo']['name'];
                // if (isset($pet_image) && $pet_image != '') {
                //     // saving image
                //     $this->load->helper('upload');
                //     if ($image_data = do_upload($this->config->item('pet_path'), 'profile_photo')) {
                //         //delete old image
                //         //if (unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $pet->profile_photo)) {
                //         //    log_message('info', 'deleted old image of pet id=' . $id);
                //         //}
                //         //$this->data['record']->profile_photo = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $image_data['file_name'];                        

                //         //create photo thumb
                //         $this->load->helper('image');
                //         resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                //         $file_name = explode('.', $image_data['file_name']);
                //         //$this->data['record']->profile_photo_thumb = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $image_data['file_name'];
                        
                //         $this->data['record'] = array_merge(
                //                 $this->data['record'], 
                //                 array(
                //                     'profile_photo' => $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $image_data['file_name'], 
                //                     'profile_photo_thumb' => $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $file_name[0] . '_thumb.' . $file_name[1]
                //                 )
                //         );
                //     }
                // }

                // update pet data
                if ($this->pets_model->update($id, $this->data['record'])) {
                    $return = $id;
                }
            }
        }
        return $return;
    }
    
    public function unlink($pet_id){
    	if(!$this->pets_model->detail($pet_id)){
    		redirect('pets');
    	}
    	
    	$this->pets_model->unlinkBadgeId($pet_id);
    	$this->messages->add('unlink badge ID successfull', "success");
    	redirect(site_url($this->lang->lang() . '/pets/edit/' . $pet_id));
    }

    public function allergies($pet_id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$this->pets_model->detail($pet_id)) {
                //redirect if invalid pet id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            } else {
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

                //get data
                $this->data['total'] = $this->pets_model->getTableData('pet_allergies', $pet_id, 'total', false, false, $limit, $offset);
                $this->data['records'] = $this->pets_model->getTableData('pet_allergies', $pet_id, 'list', 'id', 'DESC', $limit, $offset);
                $this->data['count'] = $this->pets_model->getTableData('pet_allergies', $pet_id, 'count_list', 'id', 'DESC', $limit, $offset);
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
                            $result = $this->pets_model->deleteInfo('pet_allergies', $id);
                        }
                        if ($result) {
                            $this->messages->add('deleted successful id:' . $id, "success");
                            log_message('message', 'deleted successful id:' . $id);
                        } else {
                            $this->messages->add('deleted fail id:' . $id, "error");
                            log_message('debug', 'deleted fail id:' . $id);
                        }
                        redirect($this->lang->lang() . '/pets/allergies/' . $pet_id);
                    }
                }
                $this->data['pet_id'] = $pet_id;
                //set asset
                $this->_assetIndex();
                $this->page_title = lang('pet_header') . ' | ' . lang('pet_allergies_header');
                $this->render_page('information/allergies', $this->data);
            }
        }
    }

    public function contact($pet_id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$this->pets_model->detail($pet_id)) {
                //redirect if invalid pet id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            } else {
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

                //get data
                $this->data['total'] = $this->pets_model->getTableData('pet_contact', $pet_id, 'total', false, false, $limit, $offset);
                $this->data['records'] = $this->pets_model->getTableData('pet_contact', $pet_id, 'list', 'id', 'DESC', $limit, $offset);
                $this->data['count'] = $this->pets_model->getTableData('pet_contact', $pet_id, 'count_list', 'id', 'DESC', $limit, $offset);
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
                            $result = $this->pets_model->deleteInfo('pet_contact', $id);
                        }
                        if ($result) {
                            $this->messages->add('deleted successful id:' . $id, "success");
                            log_message('message', 'deleted successful id:' . $id);
                        } else {
                            $this->messages->add('deleted fail id:' . $id, "error");
                            log_message('debug', 'deleted fail id:' . $id);
                        }
                        redirect($this->lang->lang() . '/pets/contact/' . $pet_id);
                    }
                }
                $this->data['pet_id'] = $pet_id;
                //set asset
                $this->_assetIndex();
                $this->page_title = lang('pet_header') . ' | ' . lang('pet_contact_header');
                $this->render_page('information/contact', $this->data);
            }
        }
    }

    public function estrus($pet_id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$this->pets_model->detail($pet_id)) {
                //redirect if invalid pet id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            } else {
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

                //get data
                $this->data['total'] = $this->pets_model->getTableData('pet_estrus', $pet_id, 'total', false, false, $limit, $offset);
                $this->data['records'] = $this->pets_model->getTableData('pet_estrus', $pet_id, 'list', 'id', 'DESC', $limit, $offset);
                $this->data['count'] = $this->pets_model->getTableData('pet_estrus', $pet_id, 'count_list', 'id', 'DESC', $limit, $offset);
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
                            $result = $this->pets_model->deleteInfo('pet_estrus', $id);
                        }
                        if ($result) {
                            $this->messages->add('deleted successful id:' . $id, "success");
                            log_message('message', 'deleted successful id:' . $id);
                        } else {
                            $this->messages->add('deleted fail id:' . $id, "error");
                            log_message('debug', 'deleted fail id:' . $id);
                        }
                        redirect($this->lang->lang() . '/pets/estrus/' . $pet_id);
                    }
                }
                $this->data['pet_id'] = $pet_id;
                //set asset
                $this->_assetIndex();
                $this->page_title = lang('pet_header') . ' | ' . lang('pet_entrus_header');
                $this->render_page('information/estrus', $this->data);
            }
        }
    }

    public function medical_examinations($pet_id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$this->pets_model->detail($pet_id)) {
                //redirect if invalid pet id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            } else {
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

                //get data
                $this->data['total'] = $this->pets_model->getTableData('pet_medical_examinations', $pet_id, 'total', false, false, $limit, $offset);
                $this->data['records'] = $this->pets_model->getTableData('pet_medical_examinations', $pet_id, 'list', 'id', 'DESC', $limit, $offset);
                $this->data['count'] = $this->pets_model->getTableData('pet_medical_examinations', $pet_id, 'count_list', 'id', 'DESC', $limit, $offset);
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
                            $result = $this->pets_model->deleteInfo('pet_medical_examinations', $id);
                        }
                        if ($result) {
                            $this->messages->add('deleted successful id:' . $id, "success");
                            log_message('message', 'deleted successful id:' . $id);
                        } else {
                            $this->messages->add('deleted fail id:' . $id, "error");
                            log_message('debug', 'deleted fail id:' . $id);
                        }
                        redirect($this->lang->lang() . '/pets/medical_examinations/' . $pet_id);
                    }
                }
                $this->data['pet_id'] = $pet_id;
                //set asset
                $this->_assetIndex();
                $this->page_title = lang('pet_header') . ' | ' . lang('pet_medical_examinations_header');
                $this->render_page('information/medical_examinations', $this->data);
            }
        }
    }

    public function medications($pet_id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$this->pets_model->detail($pet_id)) {
                //redirect if invalid pet id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            } else {
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

                //get data
                $this->data['total'] = $this->pets_model->getTableData('pet_medications', $pet_id, 'total', false, false, $limit, $offset);
                $this->data['records'] = $this->pets_model->getTableData('pet_medications', $pet_id, 'list', 'id', 'DESC', $limit, $offset);
                $this->data['count'] = $this->pets_model->getTableData('pet_medications', $pet_id, 'count_list', 'id', 'DESC', $limit, $offset);
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
                            $result = $this->pets_model->deleteInfo('pet_medications', $id);
                        }
                        if ($result) {
                            $this->messages->add('deleted successful id:' . $id, "success");
                            log_message('message', 'deleted successful id:' . $id);
                        } else {
                            $this->messages->add('deleted fail id:' . $id, "error");
                            log_message('debug', 'deleted fail id:' . $id);
                        }
                        redirect($this->lang->lang() . '/pets/medications/' . $pet_id);
                    }
                }
                $this->data['pet_id'] = $pet_id;
                //set asset
                $this->_assetIndex();
                $this->page_title = lang('pet_header') . ' | ' . lang('pet_medications_header');
                $this->render_page('information/medications', $this->data);
            }
        }
    }

    public function physical_exams($pet_id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$this->pets_model->detail($pet_id)) {
                //redirect if invalid pet id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            } else {
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

                //get data
                $this->data['total'] = $this->pets_model->getTableData('pet_physical_exams', $pet_id, 'total', false, false, $limit, $offset);
                $this->data['records'] = $this->pets_model->getTableData('pet_physical_exams', $pet_id, 'list', 'id', 'DESC', $limit, $offset);
                $this->data['count'] = $this->pets_model->getTableData('pet_physical_exams', $pet_id, 'count_list', 'id', 'DESC', $limit, $offset);
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
                            $result = $this->pets_model->deleteInfo('pet_physical_exams', $id);
                        }
                        if ($result) {
                            $this->messages->add('deleted successful id:' . $id, "success");
                            log_message('message', 'deleted successful id:' . $id);
                        } else {
                            $this->messages->add('deleted fail id:' . $id, "error");
                            log_message('debug', 'deleted fail id:' . $id);
                        }
                        redirect($this->lang->lang() . '/pets/physical_exams/' . $pet_id);
                    }
                }
                $this->data['pet_id'] = $pet_id;
                //set asset
                $this->_assetIndex();
                $this->page_title = lang('pet_header') . ' | ' . lang('pet_physical_exams_header');
                $this->render_page('information/physical_exams', $this->data);
            }
        }
    }

    public function surgeries($pet_id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$this->pets_model->detail($pet_id)) {
                //redirect if invalid pet id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            } else {
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

                //get data
                $this->data['total'] = $this->pets_model->getTableData('pet_surgeries', $pet_id, 'total', false, false, $limit, $offset);
                $this->data['records'] = $this->pets_model->getTableData('pet_surgeries', $pet_id, 'list', 'id', 'DESC', $limit, $offset);
                $this->data['count'] = $this->pets_model->getTableData('pet_surgeries', $pet_id, 'count_list', 'id', 'DESC', $limit, $offset);
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
                            $result = $this->pets_model->deleteInfo('pet_surgeries', $id);
                        }
                        if ($result) {
                            $this->messages->add('deleted successful id:' . $id, "success");
                            log_message('message', 'deleted successful id:' . $id);
                        } else {
                            $this->messages->add('deleted fail id:' . $id, "error");
                            log_message('debug', 'deleted fail id:' . $id);
                        }
                        redirect($this->lang->lang() . '/pets/surgeries/' . $pet_id);
                    }
                }
                $this->data['pet_id'] = $pet_id;
                //set asset
                $this->_assetIndex();
                $this->page_title = lang('pet_header') . ' | ' . lang('pet_surgeries_header');
                $this->render_page('information/surgeries', $this->data);
            }
        }
    }

    public function vaccinations($pet_id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$this->pets_model->detail($pet_id)) {
                //redirect if invalid pet id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            } else {
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

                //get data
                $this->data['total'] = $this->pets_model->getTableData('pet_vaccinations', $pet_id, 'total', false, false, $limit, $offset);
                $this->data['records'] = $this->pets_model->getTableData('pet_vaccinations', $pet_id, 'list', 'id', 'DESC', $limit, $offset);
                $this->data['count'] = $this->pets_model->getTableData('pet_vaccinations', $pet_id, 'count_list', 'id', 'DESC', $limit, $offset);
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
                            $result = $this->pets_model->deleteInfo('pet_vaccinations', $id);
                        }
                        if ($result) {
                            $this->messages->add('deleted successful id:' . $id, "success");
                            log_message('message', 'deleted successful id:' . $id);
                        } else {
                            $this->messages->add('deleted fail id:' . $id, "error");
                            log_message('debug', 'deleted fail id:' . $id);
                        }
                        redirect($this->lang->lang() . '/pets/vaccinations/' . $pet_id);
                    }
                }
                $this->data['pet_id'] = $pet_id;
                //set asset
                $this->_assetIndex();
                $this->page_title = lang('pet_header') . ' | ' . lang('pet_vaccinations_header');
                $this->render_page('information/vaccinations', $this->data);
            }
        }
    }

    public function weight($pet_id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$this->pets_model->detail($pet_id)) {
                //redirect if invalid pet id
                $this->messages->add(lang('pet_invalid_id'), "error");
                redirect($this->lang->lang() . '/pets/index');
            } else {
                $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
                $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

                //get data
                $this->data['total'] = $this->pets_model->getTableData('pet_weight', $pet_id, 'total', false, false, $limit, $offset);
                $this->data['records'] = $this->pets_model->getTableData('pet_weight', $pet_id, 'list', 'id', 'DESC', $limit, $offset);
                $this->data['count'] = $this->pets_model->getTableData('pet_weight', $pet_id, 'count_list', 'id', 'DESC', $limit, $offset);
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
                            $result = $this->pets_model->deleteInfo('pet_weight', $id);
                        }
                        if ($result) {
                            $this->messages->add('deleted successful id:' . $id, "success");
                            log_message('message', 'deleted successful id:' . $id);
                        } else {
                            $this->messages->add('deleted fail id:' . $id, "error");
                            log_message('debug', 'deleted fail id:' . $id);
                        }
                        redirect($this->lang->lang() . '/pets/weight/' . $pet_id);
                    }
                }
                $this->data['pet_id'] = $pet_id;
                //set asset
                $this->_assetIndex();
                $this->page_title = lang('pet_header') . ' | ' . lang('pet_weight_header');
                $this->render_page('information/weight', $this->data);
            }
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
            // '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
            // '../admin/pages/css/profile.css',
            // '../admin/pages/css/tasks.css',
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
        );
        $this->assets_js['page_plugin'] = array(
            // '../global/plugins/fuelux/js/spinner.min.js',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            // '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            // '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            // '../global/plugins/typeahead/typeahead.bundle.min.js',
            // '../global/plugins/select2/select2.min.js',
            // '../admin/pages/scripts/components-pickers.js',
            // '../js/users/users.js',
            // '../js/users/components-form-tools.js',
            // '../js/data/data-confirm.js',
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
            // 'DataConfirm.init();',
            'ComponentsDateTimePickers.init();',
        );
    }

    function do_upload($field_name, $path = '') {
        // Use "upload" library to select image, and image will store in root directory "uploads" folder.
        $config = array(
            'upload_path' => $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $path,
            'upload_url' => base_url() . $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $path,
            'allowed_types' => "gif|jpg|png|jpeg"
        );
        $this->load->library('upload', $config);
        // create folder
        if (!is_dir($config ['upload_path'])) {
            mkdir($config ['upload_path'], 0777, TRUE);
        }

        if ($this->upload->do_upload($field_name)) {
            //If image upload in folder, set also this value in "$image_data".
            $image_data = $this->upload->data();
            return $image_data;
        } else {
            $this->session->set_flashdata('error', lang('pet_upload_failure') . $this->upload->display_errors());
            return false;
        }
    }

}
