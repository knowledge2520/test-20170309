<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Banners extends Admin_Controller
{
    var $data = array();
    var $module = 'banners';
    
    function __construct(){
        parent::__construct();

        $this->load->model(array('auth/ion_auth_model', 'banners_model', 'users/permissions_model'));
        //$this->load->library('messages');
        $this->lang->load('banners');
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
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            $array_field = array('order', 'id', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'order';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            //get data
            $this->data['total'] = $this->banners_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->banners_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->banners_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);

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

        //set status
        if($this->input->post('set_status')){            
            $id = $this->input->post('id');
            $status = $this->input->post('status');

            $this->banners_model->update_status($id, $status);
            die();
            //redirect($this->input->post('url'), 'refresh');
        }
        
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->banners_model->delete($id);
                }
                if ($result) {
                    $this->messages->add(lang('banner_delete_action_success' . $id), "success");
                    //log_message('message', 'deleted successful banner id:' . $id);
                } else {
                    $this->messages->add(lang('banner_delete_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail banner id:' . $id);
                }
                redirect($this->lang->lang() . '/banners/index');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('banner_header');
        $this->render_page('index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post()) {
                $id = $this->_save_banner();
                if ($id) {
                    $this->messages->add(lang('banner_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/banners/');
                    }
                    else{
                        redirect($this->lang->lang() . '/banners/edit/' . $id);
                    }
                } else {
                    $this->messages->add(lang('banner_create_action_fail'), "error");
                }
            }

            $this->_assetForm();
            $this->page_title = lang('banner_header');
            $this->render_page('create');
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
                $this->messages->add(lang('banner_invalid_id'), "error");
                redirect($this->lang->lang() . '/banners/index');
            }
            if ($this->input->post()) {
                if ($this->_save_banner('update', $id)) {
                    $this->messages->add(lang('banner_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/banners/edit/' . $id);
                } else {
                    $this->messages->add(lang('banner_edit_action_fail', $id), "error");
                }
            }
            //member data
            $this->data['record'] = $this->banners_model->detail($id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('banner_invalid_id'), "error");
                redirect($this->lang->lang() . '/banners/index');
            }

            $this->_assetForm();
            $this->page_title = lang('banner_header');
            $this->render_page('edit', $this->data);
        }
    }

    private function _save_banner($type = 'insert', $id=0)
    {
        $this->load->library('image_lib');
        
        $return  = false;
        // make sure we only pass in the fields we want
        $data = array ();

        //banner data
        $data['status'] 	= $this->input->post('status');
        $data['hyperlink'] 	= $this->input->post('hyperlink');
        $data['order']      = $this->input->post('order');
        
        $path               = DEFAULT_PATH_ADMIN . $this->config->item('banners_path');
        $upload_field_name  = 'path';

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($upload_field_name),
            'resize_size'           => array(
                    'resize_width'  => BANNER_WIDTH,
                    'resize_height' => BANNER_HEIGHT,
                    'resize_thumb'  => FALSE,
                    'resize_ratio'  => TRUE
                )
        );

        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        $this->form_validation->set_rules('order', lang('banner_order'), 'integer') ;
        if ($type == 'insert') {
            // upload photo & saving image   
            $config['required'] =  true; 
            $library_media = new Admin_media($config);  
            $data = $library_media->saveMediaAdmin($data);

            if($this->form_validation->run() == false){
                $this->render_page('create', $data);
            }
            else{
                // insert banner data
                $data['created_date'] = NOW();
                $id = $this->banners_model->insert($data);
                if($id)
                {
                    $return = $id;
                }
                // $this->load->helper('upload');
                // $image_data = do_upload($this->config->item('banners_path'),'path');
                // if ( !$image_data ){
                //     $this->render_page('create', $data);
                //     $this->messages->add(lang('banner_upload_failure') . $this->upload->display_errors(), "error");
                // }
                // else{
                //     // saving image
                //     //$data['path'] = $this->config->item ( 'admin_upload_path' ) . $this->config->item('banners_path') . $image_data['file_name'];
                //     $data['name'] = $image_data['raw_name'];

                //     //create photo thumb
                //     $this->load->helper('image');               
                //     resizeImage($image_data['full_path'], BANNER_WIDTH, BANNER_HEIGHT, TRUE, TRUE);
                //     $file_name = explode('.', $image_data['file_name']);
                //     $data['path'] = $this->config->item('admin_upload_path') . $this->config->item('banners_path') . $file_name[0] . '_thumb.' . $file_name[1];
                //     // insert member data
                //     $data['created_date'] = NOW();
                //     $id = $this->banners_model->insert($data);
                //     if($id)
                //     {
                //         $return = $id;
                //     }   
                // }
            }
        }
        elseif ($type == 'update') {
            $item = $this->banners_model->find($id);

            // upload photo
            $config['required'] =  true; 
            $config['type'] = 'update';
            $config['item'] = $item;
            $library_media = new Admin_media($config);                

            $data = $library_media->saveMediaAdmin($data);

            if($this->form_validation->run() == false){
                $this->data['record']               = $this->banners_model->detail($id);
                $this->data['record']->status       = $this->input->post('status');
                $this->data['record']->hyperlink    = $this->input->post('hyperlink');
                $this->data['record']->order        = $this->input->post('order');
                $this->render_page('edit',$this->data);
            }
            else{
                 // $this->load->helper('upload');
                // if($image_data = do_upload($this->config->item('banners_path'),'path')){
                //     //delete old image
                //     //if(unlink($banner->path)){
                //     //    log_message('info','deleted old image of banner id='.$id );
                //     //}
                //     //$data['path'] = $this->config->item ( 'admin_upload_path' ).$image_data['file_name'];
                //     $data['name'] = $image_data['raw_name'];

                //     //create photo thumb
                //     $this->load->helper('image');
                //     resizeImage($image_data['full_path'], BANNER_WIDTH, BANNER_HEIGHT, TRUE, FALSE);
                //     $file_name = explode('.', $image_data['file_name']);
                //     $data['path'] = $this->config->item('admin_upload_path') . $this->config->item('banners_path') . $file_name[0] . '_thumb.' . $file_name[1];
                // }

                // update banner data
                if($this->banners_model->update($id,$data)){
                    $return = $id;
                }
            }    
        }
        return $return;
    }

    public function deletePhoto($id){
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->banners_model->deletePhoto($id)) {
                $this->messages->add('delete profile photo success' . $id, "success");
            } else {
                $this->messages->add('delete profile photo fail' . $id, "error");
            }

            redirect(site_url($this->lang->lang() . '/banners/edit/' . $id));
        }
    }
    

    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetIndex(){
        $this->assets_css['page_style'] = array(
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
            //'Users.init();',
            'Custom.init();'
        );
    }
    /**
     * _assetEditForm
     *
     *file_name
     */
    // private function _assetForm(){
    //     $this->assets_css['page_style'] = array(
    //         '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
    //     );
    //     $this->assets_js['page_plugin'] = array(
    //         '../global/plugins/fuelux/js/spinner.min.js',
    //         '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
    //         '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
    //         '../global/plugins/typeahead/typeahead.bundle.min.js',
    //     );
    // }
    private function _assetForm(){
        $this->assets_css['page_style'] = array(
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../admin/pages/css/profile.css',
            '../admin/pages/css/tasks.css',
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../js/banners/banners.js',
            '../js/banners/components-form-tools.js',
            '../js/custom/custom-confirm.js',
        );

        $this->js_domready = array(
            // 'Metronic.init();', // init metronic core components
            // 'Layout.init();', // init current layout
            // 'QuickSidebar.init();', // init quick sidebar
            // 'Demo.init();', // init demo features'
            // 'ComponentsFormTools.init();',
            'CustomConfirm.init();',
        );
    }

    function do_upload($field_name , $path = '') {
        // Use "upload" library to select image, and image will store in root directory "uploads" folder.
        $config = array(
            'upload_path' => $this->config->item ( 'admin_upload_path' ).$path,
            'upload_url' => base_url() . $this->config->item ( 'admin_upload_path' ).$path,
            'allowed_types' => "gif|jpg|png|jpeg"
        );
        $this->load->library('upload', $config);
        // create folder
        if (! is_dir ( $config ['upload_path'] )) {
            mkdir ( $config ['upload_path'], 0777, TRUE );
        }

        if ($this->upload->do_upload($field_name)) {
            //If image upload in folder, set also this value in "$image_data".
            $image_data = $this->upload->data();
            return $image_data;
        }
        else
        {
            $this->session->set_flashdata ( 'error', lang ( 'banner_upload_failure' ) . $this->upload->display_errors () );
            return false;
        }
    }
}