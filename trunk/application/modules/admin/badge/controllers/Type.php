<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Type extends Admin_Controller
{
    var $data = array();
    var $module = 'badge';
    
    function __construct(){
        parent::__construct();

        $this->load->model(array('auth/ion_auth_model', 'badge_type_model', 'users/permissions_model'));
        $this->lang->load('badge_type');
        $this->load->helper(array('permission', 'site'));
    }

    function index(){
        if(!Permission::check_permission($this->module.'.index') && !$this->ion_auth->is_admin())
        {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        }
        else
        {
            $status = $this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $cate_id = $this->input->get('category_id') ? $this->input->get('category_id') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            $array_field = array('id', 'created_date','quantity');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            //get data
            $this->data['total'] = $this->badge_type_model->getItems('total', $status,$cate_id, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->badge_type_model->getItems('list', $status,$cate_id, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->badge_type_model->getItems('count_list', $status,$cate_id, $keyword, $order_field, $sort, $limit, $offset);

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

            //$this->data['permissions'] = $this->permissions_model->get_permissions_user($this->session->userdata('user_id'));
            $this->data['module'] = $this->module;
            $this->data['is_admin'] = $this->ion_auth->is_admin();
        }
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    if ($this->badge_type_model->check_relationship_types($id)) {
                        $result = FALSE;
                    } else {
                        $result = $this->badge_type_model->delete($id);
                        if ($result) {
                            $this->messages->add(lang('badge_type_delete_action_success', $id), "success");
                            log_message('message', 'deleted successful badge type id:' . $id);
                        }
                    }
                   
                }
                if ($result) {
                    $this->messages->add(lang('badge_type_delete_action_success' . $id), "success");
                    //log_message('message', 'deleted successful badge id:' . $id);
                } else {
                    $this->messages->add(lang('badge_type_delete_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail badge id:' . $id);
                }
                redirect($this->lang->lang() . '/badge/type/index');
            }
        }

        //set asset
        $this->_assetIndex();
        $this->page_title = lang('badge_type_management');
        $this->render_page('type/index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post()) {
                $id = $this->_save_badge();
                if ($id) {
                    $this->messages->add(lang('badge_type_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/badge/type');
                    }
                    else{
                        redirect($this->lang->lang() . '/badge/type/edit/' . $id);
                    }
                } else {
                    $this->messages->add(lang('badge_type_create_action_fail'), "error");
                }
            }

            $this->_assetForm();
            $this->page_title = lang('badge_type_management');
            $this->render_page('type/create');
        }
    }

    public function edit($id)
    {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid member id
                $this->messages->add(lang('badge_type_invalid_id'), "error");
                redirect($this->lang->lang() . '/badge/index');
            }
            if ($this->input->post()) {
                if ($this->_save_badge('update', $id)) {
                    $this->messages->add(lang('badge_type_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/badge/type/edit/' . $id);
                } else {
                    $this->messages->add(lang('badge_type_edit_action_fail', $id), "error");
                }
            }
            //member data
            $this->data['record'] = $this->badge_type_model->detail($id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('badge_type_invalid_id'), "error");
                redirect($this->lang->lang() . '/badge/type/index');
            }

            $this->_assetForm();
            $this->page_title = lang('badge_type_management');
            $this->render_page('type/edit', $this->data);
        }
    }

    private function _save_badge($type = 'insert', $id=0)
    {
        $this->load->library('image_lib');
        $return  = false;
        //config upload media
        $badge_path = $this->config->item('badges_path') ? $this->config->item('badges_path') : 'badges/';
        $path = DEFAULT_PATH_ADMIN . $badge_path;
        $upload_field_name  = 'photo';

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($upload_field_name),
        );

        // make sure we only pass in the fields we want
        $data = array ();
        //badge data
        if ($type == 'insert') {
            $validate = $this->validate();
            if($validate){
                // insert badge data
                $data['status']             = $this->input->post('status');
                $data['name']               = $this->input->post('name');
                $data['description']        = $this->input->post('description');
                $data['created_date']       = NOW();
                $data['modified_date']      = NOW();

                // upload photo 
                $library_media = new Admin_media($config);  
                $data = $library_media->saveMediaAdmin($data);

                $id = $this->badge_type_model->insert($data);
                if($id)
                {
                    $return = $id;
                }
            }
        }
        elseif ($type == 'update') {
            $item = $this->badge_type_model->find($id);
            $validate = $this->validate();
            if($validate){
                // update badge data
                $data['status']             = $this->input->post('status');
                $data['name']               = $this->input->post('name');
                $data['description']        = $this->input->post('description');
                $data['modified_date']      = NOW();

                // upload photo
                $config['type'] = 'update';
                $config['item'] = $item;
                $library_media = new Admin_media($config); 
                $data = $library_media->saveMediaAdmin($data);

                if($this->badge_type_model->update($id,$data)){
                    $return = $id;
                }
            }
            else{
                $this->data['record']               = $this->badge_type_model->detail($id);
                $this->data['record']->name         = $this->input->post('name');
                $this->data['record']->description  = $this->input->post('description');
                $this->data['record']->status       = $this->input->post('status');
                $this->page_title = lang('badge_type_management');
                $this->render_page('type/edit',$this->data);
            } 
        }
        return $return;
    }  

    private function validate() {
        $this->load->library('form_validation');
        $data                   = array ();
        $id                     = $this->input->post('id');

        $name                   = $this->input->post('name') ? $this->input->post('name') : false;
        $description            = $this->input->post('description') ? $this->input->post('description') : false;
        $status                 = $this->input->post('status') ? $this->input->post('status') : false;

        $this->form_validation->set_rules('name',lang('badge_type_name'), 'required');
        $this->form_validation->set_error_delimiters('<span style="color:red">', '</span>');
        if (!$this->form_validation->run()) {
            $data['errors'] = array (  
                'name' => form_error('name'),    
            );             
            $data['record'] = (object) array(
                'name'          => $name,
                'description'   => $description,
                'status'        => $status
            );    
            $this->render_page('type/create', (object) $data);
            $this->page_title = lang('badge_type_management');
            return false;
        }
        return true;
    }

    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetIndex(){
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
            'TableAdvancedCustom.init();',
            'Custom.init();'
        );
    }
    /**
     * _assetEditForm
     *
     *file_name
     */
    private function _assetForm(){
        $this->assets_css['page_style'] = array(
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/select2/js/select2.min.js',
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            '../pages/scripts/components-date-time-pickers.min.js',
        );
    }

}