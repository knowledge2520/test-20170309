<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Groups extends Admin_Controller {

    var $data = array();
    var $module = 'users.groups';

    function __construct() {
        parent::__construct();
        //load library
        $this->load->library(array('ion_auth'));
        $this->load->library('messages');
        //load model
        $this->load->model('auth/ion_auth_model');
        $this->load->model('users_model');
        $this->load->model('groups_model');
        $this->load->model('permissions_model');
        //load lang
        $this->lang->load('ion_auth');
        $this->lang->load('users');
        $this->lang->load('groups');
        //load helper
        $this->load->helper(array('url', 'language', 'permission'));
    }

    //redirect if needed, otherwise display the user list
    function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            
            $status = $this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;
            
            $array_field = array('id', 'name', 'description');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            //get data
            $this->data['total'] = $this->groups_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->groups_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->groups_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);

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
                        $result = $this->groups_model->delete_group($id);
                        if ($result) {
                            $this->messages->add(lang('group_delete_action_succes', $id), "success");
                            //log_message('message', 'deleted successful group id:' . $id);
                        } else {
                            $this->messages->add(lang('group_delete_action_fail', $id), "error");
                            //log_message('debug', 'deleted fail group id:' . $id);
                        }
                    }

                    redirect($this->lang->lang() . '/users/groups/index');
                }
            }
           
            //set asset
            $this->_assetIndex();
            $this->page_title = lang('groups');
            $this->render_page('groups/index', $this->data);
        }
    }

    // create a new group
    function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $this->page_title = $this->lang->line('create_group_title');

            if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
                redirect('auth', 'refresh');
            }

            //validate form input
            $this->form_validation->set_rules('group_name', $this->lang->line('create_group_validation_name_label'), 'required|alpha_dash');

            if ($this->form_validation->run() == TRUE) {
                $new_group_id = $this->ion_auth->create_group($this->input->post('group_name'), $this->input->post('description'));
                if ($new_group_id) {
                    // check to see if we are creating the group
                    // redirect them back to the admin page
                    $this->messages->add($this->ion_auth->messages(), 'success');
                    redirect("users/groups", 'refresh');
                }
            } else {
                //display the create group form
                //set the flash data error message if there is one
                $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

                $this->data['group_name'] = array(
                    'name' => 'group_name',
                    'id' => 'group_name',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('group_name'),
                );
                $this->data['description'] = array(
                    'name' => 'description',
                    'id' => 'description',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $this->form_validation->set_value('description'),
                );

                //set asset
                $this->_assetEditForm();
                $this->page_title = lang('groups');
                $this->render_page('groups/create', $this->data);
            }
        }
    }

    //edit a group
    function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin() || $id == 1) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            // bail if no group id given
            if (!$id || empty($id)) {
                redirect('auth', 'refresh');
            }

            $this->page_title = $this->lang->line('edit_group_title');

            if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
                redirect('auth', 'refresh');
            }

            $group = $this->ion_auth->group($id)->row();

            //validate form input
            $this->form_validation->set_rules('group_name', $this->lang->line('edit_group_validation_name_label'), 'required|alpha_dash');

            if (isset($_POST) && !empty($_POST)) {
                if ($this->form_validation->run() === TRUE) {
                    $group_update = $this->ion_auth_model->update_group($id, $_POST['group_name'], $_POST['group_description']);

                    if ($group_update) {
                        $this->messages->add($this->lang->line('edit_group_saved'), 'success');
                    } else {
                        $this->messages->add($this->ion_auth_model->errors(), 'error');
                    }
                    redirect("users/groups", 'refresh');
                }
            }

            //set the flash data error message if there is one
            $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
            $this->messages->add($this->data['message'], 'error');

            //pass the user to the view
            $this->data['group'] = $group;

            $this->data['group_name'] = array(
                'name' => 'group_name',
                'id' => 'group_name',
                'type' => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_name', $group->name),
            );
            $this->data['group_description'] = array(
                'name' => 'group_description',
                'id' => 'group_description',
                'type' => 'text',
                'class' => 'form-control',
                'value' => $this->form_validation->set_value('group_description', $group->description),
            );

            //set asset
            $this->_assetEditForm();
            $this->page_title = lang('groups');
            $this->render_page('groups/edit', $this->data);
        }
    }

    public function addToGroup() {
        $this->page_title = $this->lang->line('edit_group_title');

        //set asset
        $this->_assetEditForm();
        $this->render_page('groups/add_to_group', $this->data);
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
    private function _assetEditForm() {
        $this->assets_css['page_style'] = array(
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
            // '../global/plugins/select2/select2.min.js',
            // '../js/users/users.js',
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
        // );
    }

}
