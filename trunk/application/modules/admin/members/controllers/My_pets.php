<?php

class My_pets extends Admin_Controller {

    var $data = array();
    var $module = 'members';

    function __construct() {
        parent::__construct();

        $this->lang->load('pets');
        $this->load->model(array('pets_model', 'users/users_model', 'pets/pet_types_model', 'users/permissions_model'));
        $this->load->library('messages');
        $this->load->helper('permission');
    }

    public function index($member_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = $this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->pets_model->getItems('total', $status, $keyword, false, false, $limit, $offset, $member_id);
            $this->data['records'] = $this->pets_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id);
            $this->data['count'] = $this->pets_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id);
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
        }

        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->pets_model->delete($id);
                }
                if ($result) {
                    $this->messages->add('deleted successful pet id:' . $id, "success");
                    log_message('message', 'deleted successful pet id:' . $id);
                } else {
                    $this->messages->add(lang('deleted fail pet id:') . $id, "error");
                    log_message('debug', 'deleted fail pet id:' . $id);
                }
                redirect($this->lang->lang() . '/members/my_pets/index/' . $member_id);
            }
        }
        if ($this->data['records']) {
            foreach ($this->data['records'] as $k => $pet) {
                $pet_type = $this->pet_types_model->detail($pet->type);
                $this->data['records'][$k]->type_pet = $pet_type->name;
            }
        }
        $this->data['member_id'] = $member_id;
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('pet_header');
        $this->render_page('pets/index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {

            if ($this->input->post()) {
                $id = $this->_save_pet();
                if ($id) {
                    $this->messages->add(lang('pet_create_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/pets/edit/' . $id);
                } else {
                    $this->messages->add(lang('pet_create_action_fail') . $this->pets_model->error, "error");
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

    public function edit($member_id = false, $id = false) {
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
                    $this->messages->add(lang('pet_edit_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/pets/edit/' . $id);
                } else {
                    $this->messages->add(lang('member_edit_action_fail') . $id, "error");
                }
            }
            //pet data
            $this->data['record'] = $this->pets_model->detail($id);
            $this->data['vaccinations'] = $this->pets_model->getTableData('pet_vaccinations', $id);
            $this->data['medical_examinations'] = $this->pets_model->getTableData('pet_medical_examinations', $id);
            $this->data['physical_exams'] = $this->pets_model->getTableData('pet_physical_exams', $id);
            $this->data['medications'] = $this->pets_model->getTableData('pet_medications', $id);
            $this->data['surgeries'] = $this->pets_model->getTableData('pet_surgeries', $id);
            $this->data['allergies'] = $this->pets_model->getTableData('pet_allergies', $id);
            $this->data['weight'] = $this->pets_model->getTableData('pet_weight', $id);
            $this->data['estrus'] = $this->pets_model->getTableData('pet_estrus', $id);
            $this->data['contact'] = $this->pets_model->getTableData('pet_contact', $id);

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

    private function _save_pet($type = 'insert', $id = 0) {

        $this->load->library('image_lib');

        $return = false;
        // make sure we only pass in the fields we want
        $data = array();
        //pet data
        $data['name'] = $this->input->post('name');
        $data['type'] = $this->input->post('type');
        $data['breed'] = $this->input->post('breed');
        $data['sex'] = $this->input->post('sex');
        $data['color'] = $this->input->post('color');
        $data['origin'] = $this->input->post('origin');
        $data['microchip'] = $this->input->post('microchip');

        $date_of_birth = $this->input->post('dob');
        $sql_date = implode(explode('/', $date_of_birth));
        $data['dob'] = mysql_to_unix($sql_date);

        $purchase_date = $this->input->post('purchase_date');
        $sql_date = implode(explode('/', $purchase_date));
        $data['purchase_date'] = mysql_to_unix($sql_date);

        $this->form_validation->set_rules('name', lang('pet_name'), 'required');
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
                $data['type'] = $this->input->post('type');
                $data['user_id'] = $this->input->post('user');

                // upload photo & saving image
                $library_media = new Admin_media($config);  
                $data = $library_media->saveMediaAdmin($data);

                // $pet_image = $_FILES['profile_photo']['name'];
                // if (isset($pet_image) && $pet_image != '') {
                    // saving image
                    // $this->load->helper('upload');
                    // if ($image_data = do_upload($this->config->item('pet_path'), 'profile_photo')) {
                    //     $data['profile_photo'] = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $image_data['file_name'];

                    //     //create photo thumb
                    //     $this->load->helper('image');
                    //     resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                    //     $file_name = explode('.', $image_data['file_name']);
                    //     $data['profile_photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $file_name[0] . '_thumb.' . $file_name[1];
                    // }
                // }
                
                // insert member data
                $id = $this->pets_model->insert($data);
                if ($id) {
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == FALSE) {
                //pet data
                $this->data['record'] = $this->pets_model->detail($id);
                $this->data['vaccinations'] = $this->pets_model->getTableData('pet_vaccinations', $id);
                $this->data['medical_examinations'] = $this->pets_model->getTableData('pet_medical_examinations', $id);
                $this->data['physical_exams'] = $this->pets_model->getTableData('pet_physical_exams', $id);
                $this->data['medications'] = $this->pets_model->getTableData('pet_medications', $id);
                $this->data['surgeries'] = $this->pets_model->getTableData('pet_surgeries', $id);
                $this->data['allergies'] = $this->pets_model->getTableData('pet_allergies', $id);
                $this->data['weight'] = $this->pets_model->getTableData('pet_weight', $id);
                $this->data['estrus'] = $this->pets_model->getTableData('pet_estrus', $id);
                $this->data['contact'] = $this->pets_model->getTableData('pet_contact', $id);

                if (empty($this->data['record'])) {
                    //redirect if invalid member id
                    $this->messages->add(lang('pet_invalid_id'), "error");
                    redirect($this->lang->lang() . '/pets/index');
                }

                $this->data['users'] = $this->users_model->find_all();
                $this->data['pet_types'] = $this->pet_types_model->find_all();
                $this->page_title = lang('pet_header');
                $this->render_page('edit', $this->data);
            } else {
                //load pet data
                $item = $this->pets_model->find($id);

                // upload photo
                $config['type'] = 'update';
                $config['item'] = $item;
                $library_media = new Admin_media($config);                

                $data = $library_media->saveMediaAdmin($data);

                // $pet_image = $_FILES['profile_photo']['name'];
                // if (isset($pet_image) && $pet_image != '') {
                    // saving image
                    // $this->load->helper('upload');
                    // if ($image_data = do_upload($this->config->item('pet_path'), 'profile_photo')) {
                    //     //delete old image
                    //     //if (unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $pet->profile_photo)) {
                    //     //    log_message('info', 'deleted old image of pet id=' . $id);
                    //     //}
                    //     $data['profile_photo'] = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $image_data['file_name'];

                    //     //create photo thumb
                    //     $this->load->helper('image');
                    //     resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                    //     $file_name = explode('.', $image_data['file_name']);
                    //     $data['profile_photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $file_name[0] . '_thumb.' . $file_name[1];
                    // }
                // }

                // update pet data
                if ($this->pets_model->update($id, $data)) {
                    $return = $id;
                }
            }
        }
        return $return;
    }

    public function allergies($pet_id) {
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
            $this->render_page('information/allergies', $this->data);
        }
    }

    public function contact($pet_id) {
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
            $this->render_page('information/contact', $this->data);
        }
    }

    public function estrus($pet_id) {
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
            $this->render_page('information/estrus', $this->data);
        }
    }

    public function medical_examinations($pet_id) {
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
            $this->render_page('information/medical_examinations', $this->data);
        }
    }

    public function medications($pet_id) {
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
            $this->render_page('information/medications', $this->data);
        }
    }

    public function physical_exams($pet_id) {
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
            $this->render_page('information/physical_exams', $this->data);
        }
    }

    public function surgeries($pet_id) {
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
            $this->render_page('information/surgeries', $this->data);
        }
    }

    public function vaccinations($pet_id) {
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
            $this->render_page('information/vaccinations', $this->data);
        }
    }

    public function weight($pet_id) {
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
            $this->render_page('information/weight', $this->data);
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
        // );
    }

}
