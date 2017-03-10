<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends Admin_Controller {

    var $data = array();
    var $module = 'users';

    function __construct() {
        parent::__construct();
        $this->load->library(array('ion_auth'));
        $this->load->helper(array('url', 'language', 'form'));
        
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

        $this->lang->load(array('ion_auth', 'users'));
        $this->load->model(array('auth/ion_auth_model', 'users_model', 'groups_model', 'permissions_model'));
        $this->load->library('messages');
        $this->load->helper('permission');
    }

    //redirect if needed, otherwise display the user list
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
            
            $array_field = array('id', 'first_name', 'email', 'phone', 'company', 'created_on', 'active', 'last_login', 'country');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->users_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->users_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->users_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);

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
            //list the users
            if(isset($this->data['records']) && !empty($this->data['records'])){
                foreach ($this->data['records'] as $k => $user) {
                    $this->data['records'][$k]->groups = $this->ion_auth_model->get_users_groups($user->id)->result();
                }
            }
        }

        //set status
        if($this->input->post('set_status')){            
            $id = $this->input->post('id');
            $status = $this->input->post('status');
            
            $this->users_model->update_status($id, $status);
            die();
        }

        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->users_model->deleteUser($id);
                }
                if ($result) {
                    $this->messages->add(lang('user_delete_action_success',$id), "success");
                    log_message('message', lang('user_delete_action_success',$id));
                } else {
                    $this->messages->add(lang('user_delete_action_fail', $id), "error");
                    log_message('debug', lang('user_delete_action_fail', $id));
                }
                redirect($this->lang->lang() . '/users/index');
            }
        }

        //set asset
        $this->_assetIndex();
        $this->page_title = lang('users');
        $this->render_page('index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $this->data['title'] = "Create User";

            if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
                redirect('users', 'refresh');
            }
            //load groups
            $this->data['groups_items'] = $this->groups_model->find_all();

            $tables = $this->config->item('tables', 'ion_auth');

            //validate form input
            $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required');
            $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required');
            $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'max_length[15]'); //regex_match[/^[0-9().-]+$/]
            $this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'max_length[100]');
            $this->form_validation->set_rules('groups', $this->lang->line('create_user_validation_groups_label'), 'required');
            $this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');
            //set validate style
            $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
            
            if ($this->form_validation->run() == true) {
                $email = strtolower($this->input->post('email'));
                $username = $email;
                $password = SHA1($this->input->post('password'));
                $groups = $this->input->post('groups');
                $countries = $this->input->post('countries') ? $this->input->post('countries') : false;
                $additional_data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                );

                if(!is_array($groups))
                {
                    $groups = array($groups);
                }

            }
            if ( $this->form_validation->run() == true && $this->ion_auth_model->register($username, $password, $email, $additional_data, $groups,$countries)) {
                //check to see if we are creating the user
                //redirect them back to the admin page
                $this->messages->add($this->ion_auth->messages(), "success");
                redirect($this->lang->lang() . '/users/index');
            } else {
                //display the create user form
                //set the flash data error message if there is one
                //$this->data['error'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('error')));
                //$this->messages->add($this->data['error'], "error");

                $this->data['first_name'] = array(
                    'name' => 'first_name',
                    'id' => 'first_name',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('first_name'),
                );
                $this->data['last_name'] = array(
                    'name' => 'last_name',
                    'id' => 'last_name',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('last_name'),
                );
                $this->data['email'] = array(
                    'name' => 'email',
                    'id' => 'email',
                    'type' => 'email',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('email')
                );
                $this->data['company'] = array(
                    'name' => 'company',
                    'id' => 'company',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('company'),
                );
                $this->data['phone'] = array(
                    'name' => 'phone',
                    'id' => 'phone',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('phone'),
                );
                $this->data['password'] = array(
                    'name' => 'password',
                    'id' => 'password',
                    'type' => 'password',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('password'),
                );
                $this->data['password_confirm'] = array(
                    'name' => 'password_confirm',
                    'id' => 'password_confirm',
                    'type' => 'password',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('password_confirm'),
                );
            }

            $this->_assetEditForm();
            $this->data['countries'] = $this->users_model->loadCountry();
            $this->page_title = lang('users');
            $this->render_page('create', $this->data);
        }
    }

    function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $this->page_title = "Edit User";

            if (!$this->ion_auth->logged_in() || (!$this->ion_auth->is_admin() && !($this->ion_auth->user()->row()->id == $id))) {
                redirect('auth', 'refresh');
            }

            $user = $this->ion_auth_model->user($id)->row();
            $groups = $this->ion_auth_model->groups()->result_array();
            $currentGroups = $this->ion_auth_model->get_users_groups($id)->result();

            $email = $this->input->post('email');
            $unique_email = $user && $user->email != $email ? '|is_unique[users.email]' : '';
            //validate form input
            $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required');
            $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required');
            $this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'max_length[15]'); //regex_match[/^[0-9().-]+$/]
            $this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'max_length[100]');
            $this->form_validation->set_rules('groups', $this->lang->line('create_user_validation_groups_label'), 'required');
            $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required' . $unique_email);

            if (isset($_POST) && !empty($_POST)) {
                // do we have a valid request?
//                 if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id')) {
//                     show_error($this->lang->line('error_csrf'));
//                 }

                //update the password if it was posted
                if ($this->input->post('password')) {
                    $this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']');
                }

                if ($this->form_validation->run() === TRUE) {
                    $data = array(
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'company' => $this->input->post('company'),
                        'phone' => $this->input->post('phone'),
                        'email' => $this->input->post('email')
                    );

                    //update the password if it was posted
                    if ($this->input->post('password')) {
                        $data['password'] = $password = SHA1($this->input->post('password'));
                    }



                    // Only allow updating groups if user is admin
                    if ($this->ion_auth->is_admin()) {
                        //Update the groups user belongs to
                        $groupData = $this->input->post('groups');
                        $countries = $this->input->post('countries') ? $this->input->post('countries') : false;
                        if (isset($groupData) && !empty($groupData)) {
                            if(!is_array($groupData))
                            {
                                $groupData = array($groupData);
                            }

                            $this->ion_auth_model->remove_from_group('', $id);

                            foreach ($groupData as $grp) {
                                $this->ion_auth_model->add_to_group($grp, $id);
                            }
                        }
                        if($countries){
                            $this->ion_auth_model->addCountry($id,$countries);
                        }
                    }

                    //check to see if we are updating the user
                    if ($this->ion_auth_model->update($user->id, $data)) {
                        //redirect them back to the admin page
                        $this->messages->add($this->ion_auth->messages(), "success");
                        redirect($this->lang->lang() . '/users/edit/' . $id, 'refresh');
                    } else {
                        //redirect them back to the admin page if admin, or to the base url if non admin
                        $this->messages->add($this->ion_auth->errors(), "error");
                        redirect($this->lang->lang() . '/users/edit/' . $id, 'refresh');
                    }
                }
            }

            //display the edit user form
            $this->data['csrf'] = $this->_get_csrf_nonce();

            //set the flash data error message if there is one
            $this->data['error'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('error')));
            $this->messages->add($this->data['error'], 'error');

            //pass the user to the view
            $this->data['user'] = $user;
            $this->data['groups'] = $groups;
            $this->data['currentGroups'] = $currentGroups;

            $this->data['first_name'] = array(
                'name' => 'first_name',
                'id' => 'first_name',
                'type' => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('first_name', $user->first_name),
            );
            $this->data['last_name'] = array(
                'name' => 'last_name',
                'id' => 'last_name',
                'type' => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('last_name', $user->last_name),
            );
            $this->data['email'] = array(
                'name' => 'email',
                'id' => 'email',
                'type' => 'email',
                'class' => 'form-control',
            	'readonly' => 'readonly',
                'value' => $this->form_validation->set_value('email', $user->email),
            );
            $this->data['company'] = array(
                'name' => 'company',
                'id' => 'company',
                'type' => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('company', $user->company),
            );
            $this->data['phone'] = array(
                'name' => 'phone',
                'id' => 'phone',
                'type' => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('phone', $user->phone),
            );
            $this->data['password'] = array(
                'name' => 'password',
                'id' => 'password',
                'type' => 'password',
                'class' => 'form-control',
            );

            $this->_assetEditForm();
            $this->data['countries'] = $this->users_model->loadCountry();
            $this->data['userCountries'] = $this->users_model->loadCountryByUser($id);
            $this->page_title = lang('users');
            $this->render_page('edit', $this->data);
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
    private function _assetEditForm() {
        $this->assets_css['page_style'] = array(
            // '../global/plugins/jquery-multi-select/css/multi-select.css',
            // '../global/plugins/select2/select2.css',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
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
            // '../global/plugins/typeahead/typeahead.bundle.min.js',
            // '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            // '../global/plugins/select2/select2.min.js',
            // //'../admin/pages/scripts/form-samples.js',
            '../js/users/users.js',
            // '../js/users/components-form-tools.js',
            '../js/users/multiselect.js',
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
        //     //'FormSamples.init();',
        //     'ComponentsFormTools.init();',
        // );
    }

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
            redirect("users", 'refresh');
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

            $this->render_page('deactivate_user', $this->data);
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
            redirect('users', 'refresh');
        }
    }

}
