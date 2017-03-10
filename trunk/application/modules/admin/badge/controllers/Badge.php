<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Badge extends Admin_Controller
{
    var $data = array();
    var $module = 'badge';
    
    function __construct(){
        parent::__construct();

        $this->load->model(array('auth/ion_auth_model', 'badge_model','badge_category_model', 'users/permissions_model'));
        $this->lang->load('badge');
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
            $this->data['total'] = $this->badge_model->getItems('total', $status,$cate_id, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->badge_model->getItems('list', $status,$cate_id, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->badge_model->getItems('count_list', $status,$cate_id, $keyword, $order_field, $sort, $limit, $offset);

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
                    if ($this->badge_model->check_relationship_codes($id)) {
                        $result = FALSE;
                    } else {
                        $result = $this->badge_model->delete($id);
                        if ($result) {
                            $this->messages->add(lang('badge_delete_action_success', $id), "success");
                            log_message('message', 'deleted successful badge id:' . $id);
                        }
                    }
                   
                }
                if ($result) {
                    $this->messages->add(lang('badge_delete_action_success' . $id), "success");
                    //log_message('message', 'deleted successful badge id:' . $id);
                } else {
                    $this->messages->add(lang('badge_delete_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail badge id:' . $id);
                }
                redirect($this->lang->lang() . '/badge/index');
            }
        }
        if ($this->data['records']) {
            foreach ($this->data['records'] as $k => $cate) {
                $badge_cate = $this->badge_category_model->detail($cate->category_id);
                $this->data['records'][$k]->badge_cate = isset($badge_cate) && !empty($badge_cate) ? $badge_cate->name : '';
            }
        }
        //set asset
        $this->_assetIndex();
        $this->data['badge_category'] = $this->badge_category_model->getCate();
        $this->data['cate_id'] = "";
        if(!empty($cate_id)){
            $this->data['cate_id'] = $cate_id;
        }
        $this->data['keyword'] = "";
        if(!empty($keyword)){
            $this->data['keyword'] = $keyword;
        }
        $this->render_page('index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post()) {
                $id = $this->_save_badge();
                if ($id) {
                    $this->messages->add(lang('badge_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/badge/');
                    }
                    else{
                        redirect($this->lang->lang() . '/badge/edit/' . $id);
                    }
                } else {
                    $this->messages->add(lang('badge_create_action_fail'), "error");
                }
            }

            $this->_assetForm();
            $this->data['badge_category'] = $this->badge_category_model->getCate();
            $this->render_page('create',$this->data);
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
                $this->messages->add(lang('badge_invalid_id'), "error");
                redirect($this->lang->lang() . '/badge/index');
            }
            if ($this->input->post()) {
                if ($this->_save_badge('update', $id)) {
                    $this->messages->add(lang('badge_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/badge/edit/' . $id);
                } else {
                    $this->messages->add(lang('badge_edit_action_fail', $id), "error");
                }
            }
            //member data
            $this->data['record'] = $this->badge_model->detail($id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('badge_invalid_id'), "error");
                redirect($this->lang->lang() . '/badge/index');
            }
            if( $this->data['record']->status == 1){
                //redirect if invalid member id
                $this->messages->add(lang('badge_invalid_edit'), "error");
                redirect($this->lang->lang() . '/badge/index');
            }
            $this->_assetForm();
            $this->data['badge_category'] = $this->badge_category_model->getCate();
            $this->render_page('edit', $this->data);
        }
    }

    private function _save_badge($type = 'insert', $id=0)
    {
        $return  = false;
        // make sure we only pass in the fields we want
        $data = array ();
        //badge data
        if ($type == 'insert') {
            $validate = $this->validate();
            if($validate){
                // insert badge data
                $data['status']   = 0; // Not active
                $data['quantity']   = $this->input->post('quantity');
                $data['category_id']      = $this->input->post('category_id');
                $data['created_date'] = NOW();
                $id = $this->badge_model->insert($data);
                if($id)
                {
                    $return = $id;
                }
            }
        }
        elseif ($type == 'update') {
            $item = $this->badge_model->find($id);
            $validate = $this->validate();
            if($validate){
                // update badge data
                $data['quantity']   = $this->input->post('quantity');
                $data['category_id']        = $this->input->post('category_id');
                $data['modified_date']      = NOW();
                if($this->badge_model->update($id,$data)){
                    $return = $id;
                }
            }
            else{
                $this->data['record']               = $this->badge_model->detail($id);
                $this->data['record']->url          = $this->input->post('url');
                $this->data['record']->code         = $this->input->post('code');
                $this->data['record']->category_id  = $this->input->post('category_id');
                $this->render_page('edit',$this->data);
            } 
        }
        return $return;
    }

    private function validate() {
        $this->load->library('form_validation');
        $data = array ();
        $id         = $this->input->post('id');
        $this->form_validation->set_rules('category_id',lang('badge_cate'), 'required');
        $this->form_validation->set_rules('quantity',lang('badge_quantity'), 'required|numeric|greater_than[0]');
        $this->form_validation->set_error_delimiters('<span style="color:red">', '</span>');
        if (!$this->form_validation->run()) {
            $data['errors'] = array (  
                'category_id' => form_error('category_id'),             
                'quantity' => form_error('quantity'),
            );                 
            $this->render_page('create', $data);
            return false;
        }
        return true;
    }

    public function deletePhoto($id){
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->badge_model->deletePhoto($id)) {
                $this->messages->add('delete profile photo success' . $id, "success");
            } else {
                $this->messages->add('delete profile photo fail' . $id, "error");
            }

            redirect(site_url($this->lang->lang() . '/badge/edit/' . $id));
        }
    }
    

    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetIndex(){
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
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js',
            // '../js/badge/custom-table-advanced.js',
            // '../js/custom/custom.js',
            '../global/plugins/select2/js/select2.min.js',
            '../global/scripts/datatable.js',
            '../global/plugins/datatables/datatables.min.js',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js',
            '../js/badge/badge-table-advanced.js',
            '../js/badge/badge.js',
        );

        $this->js_domready = array(
            // 'Metronic.init();', // init metronic core components
            // 'Layout.init();', // init current layout
            // 'QuickSidebar.init();', // init quick sidebar
            // 'Demo.init();', // init demo features'
            'TableAdvancedBadge.init();',
            //'Users.init();',
            'Badge.init();'
        );
    }
    /**
     * _assetEditForm
     *
     *file_name
     */
    private function _assetForm(){
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
            // 'CustomConfirm.init();',
            'ComponentsDateTimePickers.init();',
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
            $this->session->set_flashdata ( 'error', lang ( 'badge_upload_failure' ) . $this->upload->display_errors () );
            return false;
        }
    }

    public function getCodes(){
        $id = $this->input->get('id') ? $this->input->get('id') : 0;
        $item = $this->badge_model->find($id);
        if($item){
            // Check generated
            $array_field = array('id', 'code_id', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $data = $this->badge_model->checkGeneratedWitSort($item->id,$order_field, $sort);
            if($data ==0){
                // Start generate
                $this->messages->add(lang('badge_invalid_get_codes'), "error");
                redirect($this->lang->lang() . '/badge/index');
            }
            
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;
            $this->data['records'] = $data;
            $this->data['id'] = $id;
            $this->_assetIndex();
            $this->render_page('codes', $this->data);
            return;
        }
        redirect(site_url($this->lang->lang() . '/badge/index'));
    }

    public function run(){
        $id = $this->input->get('id') ? $this->input->get('id') : 0;
        $item = $this->badge_model->find($id);
        if($item){
            // Check generated
            if($item->quantity > QUANTITY_BADGE_LIMIT_NUMBER){
                $this->messages->add('Limit quantity to run is less than ' . QUANTITY_BADGE_LIMIT_NUMBER . ". Please run this badge at command line", "error");
            }
            else{
                $data = $this->badge_model->checkGenerated($item->id);
                if($data ==0){
                    // Start generate
                    $data = $this->badge_model->createCodes($item);
                    redirect(site_url($this->lang->lang() . '/badge/index'));
                }
                else{
                    $this->messages->add(lang('badge_invalid_run'), "error");
                }
            }
        }
        redirect(site_url($this->lang->lang() . '/badge/index'));
    }

    function download(){
        $data = $_GET['url'];
        $file = urldecode($data);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"'); 
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}