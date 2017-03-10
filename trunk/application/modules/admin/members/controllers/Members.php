<?php

class Members extends Admin_Controller {

    var $data = array();
    var $module = 'members';

    function __construct() {
        parent::__construct();

        $this->load->library(array('ion_auth', 'messages'));
        $this->load->helper(array('url', 'language', 'permission'));
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load(array('ion_auth', 'member'));
        $this->load->model(array('auth/ion_auth_model', 'members_model', 'media_model', 'users/permissions_model'));
    }

    function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = array(0,1);
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;

            $array_field = array('id', 'user_name', 'age', 'dob', 'gender', 'email', 'phone', 'location');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : reset($array_field);
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->members_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->members_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->members_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);

            //pagination
            $this->load->library('pagination');
            $this->pager['base_url'] = current_url() . '?' . http_build_query($_GET);
            $this->pager['total_rows'] = $this->data['total'];
            $this->pager['per_page'] = $limit;
            $this->pager['page_query_string'] = TRUE;
            $this->pager['query_string_segment'] = 'per_page';
            $this->pager['num_links'] = 2;
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
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->members_model->deleteUser($id);
                }
                if ($result) {
                    $this->messages->add('deleted successful member id:' . $id, "success");
                    log_message('message', 'deleted successful member id:' . $id);
                } else {
                    $this->messages->add(lang('deleted fail member id:'), "error");
                    log_message('debug', 'deleted fail member id:' . $id);
                }
                redirect($this->lang->lang() . '/members/index');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('members');
        $this->render_page('index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {

            if ($this->input->post()) {
                $id = $this->_save_member();
                if ($id) {
                    $this->messages->add(lang('member_create_action_success') . $id, "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/members');
                    } else {
                        redirect($this->lang->lang() . '/members/edit/' . $id);
                    }
                } else {
                    $this->messages->add(lang('member_create_action_fail'), "error");
                }
            }
            $this->_assetForm();
            $this->page_title = lang('members');
            $this->render_page('create');
        }
    }

    public function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid member id
                $this->messages->add(lang('member_invalid_id'), "error");
                redirect($this->lang->lang() . '/members/index');
            }

            if ($this->input->post()) {
                if ($this->_save_member('update', $id)) {
                    $this->messages->add(lang('member_edit_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/members/edit/' . $id);
                } else {
                    $this->messages->add(lang('member_edit_action_fail'), "error");
                }
            }
            //member data
            $this->data['member_id'] = $id;
            $this->data['record'] = $this->members_model->detail($id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('member_invalid_id'), "error");
                redirect($this->lang->lang() . '/members/index');
            }
            $this->_assetForm();
            $this->page_title = lang('members');
            $this->render_page('edit', $this->data);
        }
    }

    private function _save_member($type = 'insert', $id = 0) {

        $this->load->library('image_lib');

        $return = false;

        $tables = $this->config->item('tables', 'ion_auth');
        //validate form input
        $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required');
        $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required');
        //$this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique['.$tables['users'].'.email]');
        $this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'max_length[15]'); //regex_match[/^[0-9().-]+$/]
        $this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'max_length[100]');

        $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required');
        $this->form_validation->set_rules('dob', $this->lang->line('member_dob'), 'required');

        // make sure we only pass in the fields we want
        $data = array();

        //set validate style
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');

        $categories = $this->input->post('categories');

        //member data
        $date = $this->input->post('dob');
        $sql_date = implode(explode('/', $date));
        $unix_time = mysql_to_unix($sql_date);

        $data['first_name'] = $this->input->post('first_name');
        $data['last_name'] = $this->input->post('last_name');
        $data['company'] = $this->input->post('company');
        $data['email'] = strtolower($this->input->post('email'));
        $data['username'] = strtolower($this->input->post('email'));
        $data['phone'] = $this->input->post('phone');
        $data['address'] = $this->input->post('address');
        $data['gender'] = $this->input->post('gender');
        //$data['salt'] = $this->members_model->_generateCode();
        $data['display_name'] = $this->input->post('display_name');

        $data['dob'] = $unix_time;
        $data['created_on'] = now();

        //config upload media
        $path = DEFAULT_PATH_ADMIN . $this->config->item('member_path');
        $upload_field_name  = 'profile_photo';
        $field_image = 'profile_photo';
        $field_image_thumb = 'profile_photo_thumb';

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($field_image, $field_image_thumb),
            'resize_size'           => array(
                    'resize_width'  => AVATAR_WIDTH,
                    'resize_height' => AVATAR_HEIGHT,
                    'resize_thumb'  => FALSE,
                    'resize_ratio'  => TRUE
                )
        );

        if ($type == 'insert') {
            $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');
            //check validation
            if ($this->form_validation->run() == false) {
                $this->messages->add(lang('member_edit_action_fail'), "error");
                $this->page_title = lang('members');
                $this->render_page('create');
            } else {
                $data['display_name'] = $data['first_name'] . ' ' . $data['last_name'];

                // upload photo & saving image
                $library_media = new Admin_media($config);  
                $data = $library_media->saveMediaAdmin($data);

                // $member_image = $_FILES['profile_photo']['name'];
                // if (isset($member_image) && $member_image != '') {
                    // saving image
                    // $this->load->helper('upload');
                    // if ($image_data = do_upload($this->config->item('member_path'), 'profile_photo')) {
                    //     $data['profile_photo'] = $this->config->item('admin_upload_path') . $this->config->item('member_path') . $image_data['file_name'];

                    //     //create photo thumb
                    //     $this->load->helper('image');
                    //     resizeImage($image_data['full_path'], AVATAR_WIDTH, AVATAR_HEIGHT, TRUE, TRUE);
                    //     $file_name = explode('.', $image_data['file_name']);
                    //     $data['profile_photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('member_path') . $file_name[0] . '_thumb.' . $file_name[1];
                    // }
                // }
                $password = SHA1($this->input->post('password'));
                $data['password'] = $this->ion_auth_model->hash_password($password, $data['salt']);
                $data['active'] = 0;
                // insert member data
                $id = $this->members_model->insert($data);
                if ($id) {
                    $this->members_model->add_to_group($this->input->post('groups'), $id);
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            $member = $this->members_model->detail($id);
            $unique_email = $member && $member->email != $data['email'] ? '|is_unique[users.email]' : '';

            $this->form_validation->set_rules('display_name', $this->lang->line('member_display_name'), 'required');
            $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required' . $unique_email);
            $this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');
            if ($this->form_validation->run() == FALSE) {
                //member data
                $this->data['record'] = $this->members_model->detail($id);
                $this->page_title = lang('members');
                $this->render_page('edit', $this->data);
            } else {
                //load member data
                $item = $this->members_model->find($id);

                // upload photo
                $config['type'] = 'update';
                $config['item'] = $item;
                $library_media = new Admin_media($config);                

                $data = $library_media->saveMediaAdmin($data);

                // $_POST['id'] = $id;
                // upload photo
                // $member_image = $_FILES['profile_photo']['name'];
                // if (isset($member_image) && $member_image != '') {
                    // saving image
                    // $this->load->helper('upload');
                    // if ($image_data = do_upload($this->config->item('member_path'), 'profile_photo')) {
                    //     //delete old image
                    //     if (unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $member->path)) {
                    //         log_message('info', 'deleted old image of member id=' . $id);
                    //     }
                    //     $data['profile_photo'] = $this->config->item('admin_upload_path') . $this->config->item('member_path') . $image_data['file_name'];

                    //     //create photo thumb
                    //     $this->load->helper('image');
                    //     resizeImage($image_data['full_path'], AVATAR_WIDTH, AVATAR_HEIGHT, TRUE, TRUE);
                    //     $file_name = explode('.', $image_data['file_name']);
                    //     $data['profile_photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('member_path') . $file_name[0] . '_thumb.' . $file_name[1];
                    // }
                // }

                if ($this->input->post('password')) {
                    $password = SHA1($this->input->post('password'));
                    $data['password'] = $this->ion_auth_model->hash_password($password, $item->salt);
                }

                if ($this->members_model->update($id, $data)) {
                    if($item->display_name != $data['display_name']){
                        $this->load->model('notification/notification_model');
                        $this->notification_model->updateNotificationSenderName($data['display_name'], $item);
                    }
                    $return = $id;
                }
            }
        }
        return $return;
    }

    // public function deleteProfilePhoto($id) {
    //     $this->data['record'] = $this->members_model->detail($id);
    //     if (empty($this->data['record'])) {
    //         //redirect if invalid member id
    //         $this->messages->add(lang('member_invalid_id'), "error");
    //         redirect($this->lang->lang() . '/members/index');
    //     }
    //     if ($this->members_model->deletePhoto($id)) {
    //         $this->messages->add('delete profile photo success' . $id, "success");
    //     } else {
    //         $this->messages->add('delete profile photo fail' . $id, "error");
    //     }
    //     $this->data['member_id'] = $id;
    //     $this->_assetForm();
    //     redirect(site_url($this->lang->lang() . '/members/edit/' . $id));
    // }

    private function _get_csrf_nonce() {
        $this->load->helper('string');
        $key = random_string('alnum', 8);
        $value = random_string('alnum', 20);
        $this->session->set_flashdata('csrfkey', $key);
        $this->session->set_flashdata('csrfvalue', $value);

        return array($key => $value);
    }

    private function _valid_csrf_nonce() {
        if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
                $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    //activate the user
    function activate($id, $code = false) {
        if ($code !== false) {
            $activation = $this->ion_auth_model->activate($id, $code);
        } else if ($this->ion_auth->is_admin()) {
            $activation = $this->ion_auth_model->activate($id);
        }

        if ($activation) {
            //redirect them to the auth page
            $this->session->set_flashdata('message', $this->ion_auth_model->messages());
            redirect("members", 'refresh');
        } else {
            //redirect them to the forgot password page
            $this->session->set_flashdata('message', $this->ion_auth_model->errors());
            redirect("auth/forgot_password", 'refresh');
        }
    }

    //deactivate the user
    function deactivate($id = NULL) {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error('You must be an administrator to view this page.');
        }

        $id = (int) $id;

        $this->load->library('form_validation');
        $this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required');
        $this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric');

        if ($this->form_validation->run() == FALSE) {
            // insert csrf check
            $this->data['csrf'] = $this->_get_csrf_nonce();
            $this->data['user'] = $this->ion_auth_model->user($id)->row();
            $this->page_title = lang('members');
            $this->render_page('deactivate_member', $this->data);
        } else {
            // do we really want to deactivate?
            if ($this->input->post('confirm') == 'yes') {
                // do we have a valid request?
                if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id')) {
                    show_error($this->lang->line('error_csrf'));
                }

                // do we have the right userlevel?
                if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
                    $this->ion_auth_model->deactivate($id);
                }
            }

            //redirect them back to the auth page
            redirect('members', 'refresh');
        }
    }

    public function mediaList($member_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$member_id) {
                //redirect if invalid business id
                $this->messages->add(lang('member_invalid_id'), "error");
                redirect($this->lang->lang() . '/members/index');
            }

            //$status 		= $this->input->get('status') ? $this->input->get('status') : 0;
            $status = false;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            //get data
            $this->data['total'] = $this->media_model->getItems('total', $status, $keyword, false, false, $limit, $offset, $member_id);
            $this->data['records'] = $this->media_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id);
            $this->data['count'] = $this->media_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id);

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
                    $result = $this->media_model->delete($id);
                }
                if ($result) {
                    $this->messages->add('deleted successful media id:' . $id, "success");
                    log_message('message', 'deleted successful media id:' . $id);
                } else {
                    $this->messages->add(lang('deleted fail media id:') . $this->ion_auth_model->error, "error");
                    log_message('debug', 'deleted fail media id:' . $id);
                }
                redirect($this->lang->lang() . '/members/mediaList/' . $member_id);
            }
        }
        //set asset
        $this->data['member_id'] = $member_id;
        $this->_assetIndex();
        $this->page_title = lang('members') . ' | ' . lang('media_header');
        $this->render_page('media_list', $this->data);
    }

    public function mediaEdit($member_id = false, $media_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        }
        if (!$member_id) {
            //redirect if invalid business id
            $this->messages->add(lang('member_invalid_id'), "error");
            redirect($this->lang->lang() . '/members/index');
        }
        if (!$media_id) {
            //redirect if invalid business id
            $this->messages->add(lang('member_media_invalid_id'), "error");
            redirect($this->lang->lang() . '/business/mediaList');
        }

        if ($this->input->post()) {
            if ($this->_save_media('update', $member_id, $media_id)) {
                $this->messages->add(lang('business_media_edit_action_success') . $media_id, "success");
                //redirect to edit page
                redirect($this->lang->lang() . '/members/mediaEdit/' . $member_id . '/' . $media_id);
            } else {
                $this->messages->add(lang('member_media_edit_action_fail'), "error");
            }
        }
        //member data
        $this->data['record'] = $this->media_model->detail($media_id);
        if (empty($this->data['record'])) {
            //redirect if invalid member id
            $this->messages->add(lang('business_media_invalid_id'), "error");
            redirect($this->lang->lang() . '/banners/index');
        }
        $this->data['member_id'] = $member_id;
        $this->_assetForm();
        $this->page_title = lang('members') . ' | ' . lang('media_header');
        $this->render_page('media_edit', $this->data);
    }

    public function mediaCreate($member_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$member_id) {
                //redirect if invalid member id
                $this->messages->add(lang('member_invalid_id'), "error");
                redirect($this->lang->lang() . '/members/index');
            }

            if ($this->input->post()) {
                $id = $this->_save_media('insert', $member_id);
                if ($id) {
                    $this->messages->add(lang('member_media_create_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/members/mediaEdit/' . $member_id . '/' . $id);
                } else {
                    $this->messages->add(lang('member_media_create_action_fail'), "error");
                }
            }
            $this->data['member_id'] = $member_id;
            $this->_assetForm();
            $this->page_title = lang('members') . ' | ' . lang('media_header');
            $this->render_page('media_create', $this->data);
        }
    }

    private function _save_media($type = 'insert', $member_id = false, $media_id = 0) {
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

        if ($type == 'insert') {

            // upload photo & saving image
            $config['table_media']      = TRUE;
            $config['required']         = TRUE;

            $library_media = new Admin_media($config);  
            $data = $library_media->saveMediaAdmin($data);


            if ($this->form_validation->run()) {
                // insert media data
                $data ['user_id'] = $this->session->userdata('user_id');
                $id = $this->media_model->insert($data);
                if ($id) {
                    $return = $id;
                }
            }

            // $media_image = $_FILES['path']['name'];
            // if (isset($media_image) && $media_image != '') {
                // saving image
                // $this->load->helper('upload');
                // if ($image_data = do_upload($this->config->item('listings_path'), 'path')) {
                //     $data['source'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $image_data['file_name'];

                //     //create photo thumb
                //     $this->load->helper('image');
                //     resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                //     $file_name = explode('.', $image_data['file_name']);
                //     $data['photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $file_name[0] . '_thumb.' . $file_name[1];
                // }
            // }
        } elseif ($type == 'update') {
            //load data
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
                $return = $media_id;
            }


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
            // 'Metronic.init();', // init metronic core components
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
            //'../global/plugins/select2/select2.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
            // '../admin/pages/css/profile.css',
            // '../admin/pages/css/tasks.css',
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            '../pages/scripts/components-date-time-pickers.min.js',
            // '../global/plugins/typeahead/typeahead.bundle.min.js',
            // '../global/plugins/select2/select2.min.js',            
            // '../js/member/member.js',
            // '../js/member/components-form-tools.js',
            // '../js/custom/custom-confirm.js',
        );

        $this->js_domready = array(
            'ComponentsDateTimePickers.init();',
            // 'Metronic.init();', // init metronic core components
            // 'Layout.init();', // init current layout
            // 'QuickSidebar.init();', // init quick sidebar
            // 'Demo.init();', // init demo features'
            //'FormSamples.init();',
            // 'ComponentsFormTools.init();',            
            // 'CustomConfirm.init();',
        );
    }

    function do_upload($field_name, $path = '') {
        // Use "upload" library to select image, and image will store in root directory "uploads" folder.
        $config = array(
            'upload_path' => $this->config->item('admin_upload_path') . $this->config->item('member_path') . $path,
            'upload_url' => base_url() . $this->config->item('admin_upload_path') . $this->config->item('member_path') . $path,
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
            $this->session->set_flashdata('error', lang('banner_upload_failure') . $this->upload->display_errors());
            return false;
        }
    }

}
