<?php
class Faqs extends Admin_Controller
{
    var $data = array();

    function __construct(){
        parent::__construct();

        $this->load->library(array('ion_auth', 'messages'));
        $this->load->helper(array('url','language', 'image'));

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load(array('ion_auth', 'faqs'));
        $this->load->model(array('auth/ion_auth_model', 'faqs_model', 'settings/settings_model'));
    }

    function index()
    {
        $status 		                        = '';            //$this->input->get('status') ? $this->input->get('status') : 0;
        $keyword	 	                        = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
        $limit			                        = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
        $offset 		                        = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

        $this->data['txt_search_value']        = $keyword;
        //get data
        $this->data['total'] 	                = $this->faqs_model->getItems('total',$status,$keyword,false,false,$limit,$offset);
        $this->data['records'] 	                = $this->faqs_model->getItems('list',$status,$keyword,'id','DESC',$limit,$offset);

        //pagination
        $this->load->library('pagination');
        $this->pager['base_url'] 			    = current_url() .'?'.http_build_query($_GET);
        $this->pager['total_rows'] 			    = $this->data['total'];
        $this->pager['per_page'] 			    = $limit;
        $this->pager['page_query_string']	    = TRUE;
        $this->pager['query_string_segment']    = 'per_page';
        $this->pager['first_url']               = current_url().'?'.http_build_query($_GET) . '&' . $this->pager['query_string_segment'] . '=';
        //install pagination
        $this->pagination->initialize($this->pager);

        $this->data['paging']                   = $this->pagination;
        $this->data['item_tableLength']         = $limit;

        // Deleting anything?
        if ($this->input->post('btn_delete'))
        {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked))
            {
                $result = FALSE;
                foreach ($checked as $id)
                {
                    $result = $this->faqs_model->delete($id);
                    if ($result)
                    {
                        $this->messages->add('deleted successful faq item id:'.$id, "success");
                        //log_message('message', 'deleted successful faqs item id:'.$id);
                    }
                    else
                    {
                        $this->messages->add('deleted fail faq item id:'.$id, "error");
                        //log_message('debug', 'deleted fail faq item id:'.$id);
                    }
                }

                redirect($this->lang->lang().'/faqs/index');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->render_page('index', $this->data);

    }

    public function create(){

        if($this->input->post())
        {
            $id = $this->_save_faq();
            if($id)
            {
                $this->messages->add(lang('faq_create_action_success').$id, "success");
                //redirect to edit page
                redirect($this->lang->lang().'/faqs/edit/'.$id);
            }
            else {
                $this->messages->add(lang('faq_create_action_fail'), "error");
            }
        }

        $this->_assetForm();
        $this->render_page('create' ,$this->data);
    }

    public function edit($id){
        if(!$id)
        {
            //redirect if invalid faq id
            $this->messages->add(lang('faq_invalid_id'), "error");
            redirect($this->lang->lang().'/faqs/index');
        }
        if($this->input->post())
        {
            if($this->_save_faq('update',$id))
            {
                $this->messages->add(lang('faq_edit_action_success').$id, "success");
                //redirect to edit page
                redirect($this->lang->lang().'/faqs/edit/'.$id);
            }
            else {
                $this->messages->add(lang('faq_edit_action_fail').$id, "error");
            }
        }
        //faq data
        $this->data['record'] 		            = $this->faqs_model->detail($id);

        $this->_assetForm();
        $this->render_page('edit',$this->data);
    }

    private function _save_faq($type = 'insert', $id=0){
        $return  = false;
        // make sure we only pass in the fields we want
        $data = array ();

        //faq data
        $data['question'] 	= $this->input->post('question');
        $data['answer'] 	= $this->input->post('answer');
        $data['status'] 	= $this->input->post('status');

        $this->form_validation->set_rules('question', lang('faq_question'), 'required') ;
        $this->form_validation->set_rules('answer', lang('faq_answer'), 'required') ;
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');

        if ($type == 'insert') {
            if($this->form_validation->run() == false){
                $this->data['record']   = array(
                    'question'      => $this->input->post('question'),
                    'answer'        => $this->input->post('answer'),
                );
                $this->render_page('create' ,$this->data);
            }
            else{
                // insert faq data
                $id = $this->faqs_model->insert($data);
                if($id)
                {
                    $return = $id;
                }
            }
        }
        elseif ($type == 'update') {
            $data['order'] 	= $this->input->post('order');
            $this->form_validation->set_rules('order', lang('faq_order'), 'required|integer') ;
            if($this->form_validation->run() == false){
                $this->data['record']   = array(
                    'question'      => $this->input->post('question'),
                    'answer'        => $this->input->post('answer'),
                    'status'        => $this->input->post('status'),
                    'order'         => $this->input->post('order'),
                );
                $this->render_page('edit',$this->data);
            }
            else{
                // update categories data
                if($this->faqs_model->update($id,$data)){
                    $return = $id;
                }
            }
        }
        return $return;
    }

    public function setting(){
        
        if($this->input->post()){
            $data['faq_title'] 	        = $this->input->post('title');
            $data['faq_description'] 	= $this->input->post('description');

            $this->form_validation->set_rules('title', lang('faq_title'), 'required') ;
            $this->form_validation->set_rules('description', lang('faq_description'), 'required') ;
            if($this->form_validation->run() == false){
                $setting                        = $this->settings_model->getSetting();
                $this->data['record'] = (object) array(
                    'title'         => $setting['faq_title'],
                    'description'   => $setting['faq_description'],
                    'photo'         => $setting['faq_image'],
                );

                $this->render_page('setting',$this->data);
            }
            else{
                // upload photo
                $save_data[0] = array(
                    'key'       => 'faq_title',
                    'value'     => $data['faq_title']
                );
                $save_data[1] = array(
                    'key'       => 'faq_description',
                    'value'     => $data['faq_description']
                );
                $faq_image = $_FILES['photo']['name'];
                if(isset($faq_image) && $faq_image!='' ){
                    // saving image
                    $this->load->helper('upload');
                    if($image_data = do_upload($this->config->item('faq_path'), 'photo')){
                        //delete old image
                        //if(unlink($_SERVER['DOCUMENT_ROOT'].'/'.$business->path)){
                        //    log_message('info','deleted old image of banner id='.$id );
                        //}
                        //$data['photo'] = $this->config->item ( 'upload_path' ).$this->config->item ( 'listings_path' ).$image_data['file_name'];

                        //create photo thumb
                        $this->load->helper('image');
                        resizeImage($image_data['full_path'],1170, 484, TRUE, TRUE);
                        $file_name = explode('.', $image_data['file_name']);
                        $data['faq_image'] = $this->config->item('upload_path') . $this->config->item('faq_path') . $file_name[0] . '_thumb.' . $file_name[1];
                        array_push(
                            $save_data,
                            array(
                                'key'       => 'faq_image',
                                'value'     => $data['faq_image']
                            )
                        );
                    }
                }

                //save faq setting
                $this->settings_model->updateSetting($save_data);
                redirect('faqs/setting');
            }
        }
        else{
            $setting                        = $this->settings_model->getSetting();
            $this->data['record'] = (object) array(
                'title'         => $setting['faq_title'],
                'description'   => $setting['faq_description'],
                'photo'         => $setting['faq_image'],
            );
            //var_dump($this->data);exit;
            $this->_assetForm();
            $this->render_page('setting',$this->data);
        }
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
            $this->session->set_flashdata ( 'error', lang ( 'faq_upload_failure' ) . $this->upload->display_errors () );
            return false;
        }
    }
    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetIndex(){
        $this->assets_css['page_style'] = array(
            '../global/plugins/select2/select2.css',
            '../global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css',
            '../global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css',
            '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css'
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/select2/select2.min.js',
            '../global/plugins/datatables/media/js/jquery.dataTables.min.js',
            '../global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js',
            '../global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js',
            '../global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js',
            '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js',
            '../js/custom/custom-table-advanced.js',
            '../js/custom/custom.js',
        );

        $this->js_domready = array(
            'Metronic.init();', // init metronic core components
            'Layout.init();', // init current layout
            'QuickSidebar.init();', // init quick sidebar
            'Demo.init();', // init demo features'
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
            '../global/plugins/select2/select2.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../admin/pages/css/profile.css',
            '../admin/pages/css/tasks.css',
            '../global/plugins/jquery-multi-select/css/multi-select.css',
            '../global/plugins/bootstrap-select/bootstrap-select.min.css',
            '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
            '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css'
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/typeahead/typeahead.bundle.min.js',
            '../global/plugins/select2/select2.min.js',
            '../admin/pages/scripts/components-pickers.js',
            '../js/users/users.js',
            '../js/users/components-form-tools.js',
            '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            '../global/plugins/bootstrap-select/bootstrap-select.min.js',
            '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js',
            '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js',

        );

        $this->js_domready = array(
            'Metronic.init();', // init metronic core components
            'Layout.init();', // init current layout
            'QuickSidebar.init();', // init quick sidebar
            'Demo.init();', // init demo features'
            'ComponentsFormTools.init();',
            'ComponentsPickers.init();',
            'TableAdvanced.init();',
        );
    }
}