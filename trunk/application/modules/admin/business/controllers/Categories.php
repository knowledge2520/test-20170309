<?php

class Categories extends Admin_Controller {

    var $data = array();
    var $module = 'business.categories';

    function __construct() {
        parent::__construct();

        $this->load->helper(array('url', 'language'));
        $this->lang->load(array('business', 'categories'));
        $this->load->model(array('business_model', 'categories_model', 'users/permissions_model'));
        $this->load->library('messages', 'admin_media');
        $this->load->helper('permission');
    }

    function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = array(0,1);                    //$this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;

            $array_field = array('id', 'name', 'description', 'status', 'total', 'position');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'position';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'ASC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->categories_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->categories_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset);
            $this->data['count'] = $this->categories_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset);

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

            if(isset($this->data['records']) && !empty($this->data['records'])){
                foreach ($this->data['records'] as $key => $item) {
                   $this->data['records'][$key]->countries = $this->categories_model->getCountryCategory($item->id);
                }
            }
        }
        // Deleting anything?
        if ($this->input->post('btn_delete')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                foreach ($checked as $id) {
                    $result = $this->categories_model->deleteCategory($id);
                }
                if ($result) {
                    // Add log for deleted
                    $this->business_model->addActivityLog("Deleted",'Business Categories',$id,"","","");
                    // End
                    $this->messages->add(lang('category_delete_action_success', $id), "success");
                    //log_message('message', 'deleted successful cattegory id:' . $id);
                } else {
                    $this->messages->add(lang('category_delete_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail cattegory id:' . $id);
                }
                redirect($this->lang->lang() . '/business/categories');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('categories');
        $this->render_page('categories/index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            //load categories
            if ($this->input->post()) {
                $id = $this->_save_categories();
                if ($id) {
                    $this->messages->add(lang('category_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/business/categories/');
                    }
                    else{
                        redirect($this->lang->lang() . '/business/categories/edit/' . $id);
                    }
                    
                } else {
                    $this->messages->add(lang('category_create_action_fail'), "error");
                }
            }

            $this->_assetForm();
            $this->page_title = lang('categories');
            $this->render_page('categories/create', $this->data);
        }
    }

    public function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid member id
                $this->messages->add(lang('categories_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/categories/index');
            }

            if ($this->input->post()) {
                if ($this->_save_categories('update', $id)) {
                    $this->messages->add(lang('category_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/categories/edit/' . $id);
                } else {
                    $this->messages->add(lang('category_edit_action_fail', $id), "error");
                }
            }
            //member data
            $this->data['record'] = $this->categories_model->detail($id);
            // Add log for Viewed
            $this->business_model->addActivityLog("Viewed",'Business Categories',$id,"","","");
            // End
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('categories_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/categories/index');
            }

            $this->_assetForm();
            $this->page_title = lang('categories');
            $this->render_page('categories/edit', $this->data);
        }
    }

    private function _save_categories($type = 'insert', $id = 0) {
        $this->load->library('image_lib');

        $return = false;

        //category data
        $data ['name']              = $this->input->post('name');
        $data ['description']       = $this->input->post('description');
        $data ['order']             = $this->input->post('order');
        $data ['status']            = $this->input->post('status');

        //config upload media
        $path = DEFAULT_PATH_ADMIN . $this->config->item('listings_path');
        $upload_field_name  = 'photo';

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($upload_field_name),
        );

        $this->form_validation->set_rules('name', lang('category_name'), 'required');
        $this->form_validation->set_rules('order', lang('category_order'), 'integer');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if ($type == 'insert') {
            if ($this->form_validation->run() == FALSE) {
                 $this->data['record'] = (object) array(
                    'name' => $this->input->post('name'),
                    'description' => $this->input->post('description'),
                    'order' => $this->input->post('order'),
                    'status' => $this->input->post('status'),
                );
                $this->page_title = lang('categories');
                $this->render_page('categories/create', $this->data);
            } else {
                // upload photo & saving image
                $library_media = new Admin_media($config);  
                $data = $library_media->saveMediaAdmin($data);

                // insert category data
                $id = $this->categories_model->insert($data);
                if ($id) {
                    // Add log for deleted
                    $this->business_model->addActivityLog("Created",'Business Categories',$id,"","","");
                    // End
                    $return = $id;
                }

                // if ($image_data = do_upload($this->config->item('listings_path'), 'photo')) {
                //     // $this->data['record']->photo = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $image_data['file_name'];
                //     // create photo thumb
                //     $this->load->helper('image');
                //     resizeImage($image_data ['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                //     $file_name = explode('.', $image_data ['file_name']);
                //     $this->data ['record']->photo = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $file_name [0] . '_thumb.' . $file_name [1];

                //     // insert category data
                //     $id = $this->categories_model->insert($this->data ['record']);
                //     if ($id) {
                //         $return = $id;
                //     }
                // } else {
                //     $this->messages->add($this->upload->display_errors(), "error");
                // }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == FALSE) {
                 $this->data['record'] = (object) array(
                    'name' => $this->input->post('name'),
                    'description' => $this->input->post('description'),
                    'order' => $this->input->post('order'),
                    'status' => $this->input->post('status'),
                );
                $this->page_title = lang('categories');
                $this->render_page('categories/edit', $this->data);
            } else {
                //load category data
                $item = $this->categories_model->find($id);
                $_POST['id'] = $id;
                // upload photo
                $config['type'] = 'update';
                $config['item'] = $item;
                $library_media = new Admin_media($config);  
                $data = $library_media->saveMediaAdmin($data);

                // update category data
                if ($this->categories_model->update($id, $data)) {

                    if($item->description != $data['description']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business Categories',$id,'description',$item->description,$data['description']);
                        // End
                    }
                    if($item->name != $data['name']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business Categories',$id,'name',$item->name,$data['name']);
                        // End
                    }
                    if($item->order != $data['order']){
                        // Add log for updated
                        $this->business_model->addActivityLog("Updated",'Business Categories',$id,'order',$item->order,$data['order']);
                        // End
                    }
                    if($item->status != $data['status']){
                        // Add log for updated
                        $oldStatus = 'Active';
                        if($item->status == 0){
                            $oldStatus = 'Deactive';
                        }
                        $newStatus = 'Active';
                        if($data['status'] == 0){
                            $newStatus = 'Deactive';
                        }
                        $this->business_model->addActivityLog("Updated",'Business Categories',$id,'status',$oldStatus,$newStatus);
                        // End
                    }
                    $return = $id;
                }
                //$category_image = $_FILES['photo']['name'];
                // if ($image_data = do_upload($this->config->item('listings_path'), 'photo')) {
                //     //delete old image
                //     //if(unlink($_SERVER['DOCUMENT_ROOT'].'/'.$category->photo)){
                //     //   log_message('info','deleted old image of category id='.$id );
                //     //}
                //     //$this->data['record']->photo = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $image_data['file_name'];
                //     //create photo thumb
                //     $this->load->helper('image');
                //     resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                //     $file_name = explode('.', $image_data['file_name']);
                //     $this->data['record']->photo = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $file_name[0] . '_thumb.' . $file_name[1];

                //     // update category data
                //     if ($this->categories_model->update($id, $this->data['record'])) {
                //         $return = $id;
                //     }
                // } else {
                //     $this->messages->add($this->upload->display_errors(), "error");
                // }
            }
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
            // '../global/plugins/select2/select2.css',
            // // '../admin/pages/css/profile.css',
            // // '../admin/pages/css/tasks.css',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            // '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
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
            // '../js/users/components-form-tools.js',
            //'../pages/scripts/components-date-time-pickers.min.js',
        );

        // $this->js_domready = array(
        //     'Metronic.init();', // init metronic core components
        //     'Layout.init();', // init current layout
        //     'QuickSidebar.init();', // init quick sidebar
        //     'Demo.init();', // init demo features'
        //     'ComponentsFormTools.init();',
        //     'ComponentsDateTimePickers.init();',
        // );
    }

}
