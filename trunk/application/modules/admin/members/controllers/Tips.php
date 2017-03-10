<?php

class Tips extends Admin_Controller {

    var $data = array();
    var $module = 'members';

    function __construct() {
        parent::__construct();

        $this->load->library(array('messages'));
        $this->load->model(array('tips_model', 'business/business_model', 'members_model', 'media_model', 'users/permissions_model'));
        $this->load->helper(array('url', 'language', 'permission'));
        $this->lang->load('tips');
    }

    function index($member_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = $this->input->get('status') ? $this->input->get('status') : false;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            //get data
            $this->data['total'] = $this->tips_model->getItems('total', $status, $keyword, false, false, $limit, $offset, $member_id);
            $this->data['total_deactivate'] = $this->tips_model->getItems('total', 0, $keyword, false, false, $limit, $offset, $member_id);
            $this->data['records'] = $this->tips_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id);
            $this->data['count'] = $this->tips_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id);

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

            //get user and business
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $tip) {
                    $this->data['records'][$k]->business = $this->business_model->detail($tip->business_id);
                }
            }
            $this->data['member_id'] = $member_id;
            // Deleting anything?
            if ($this->input->post('btn_delete')) {
                $checked = $this->input->post('checked');
                if (is_array($checked) && count($checked)) {
                    $result = FALSE;
                    $success = array();
                    foreach ($checked as $id) {
                        $success = array_merge($success, $id);
                        //$result = $this->tips_model->delete($id);
                    }
                    if (!empty($success)) {
                        $this->messages->add('deleted successful business item id:' . $success, "success");
                        //log_message('message', 'deleted successful business item id:'.$id);
                    } else {
                        $this->messages->add('deleted fail business item id:', "error");
                        //log_message('debug', 'deleted fail business item id:'.$id);
                    }
                    redirect($this->lang->lang() . '/business/tips/index');
                }
            }
            //set asset
            $this->_assetIndex();
            $this->render_page('tips/index', $this->data);
        }
    }

    public function deactivate() {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            //get data
            $this->data['total'] = $this->tips_model->getItems('total', $status, $keyword, false, false, $limit, $offset);
            $this->data['records'] = $this->tips_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset);
            $this->data['count'] = $this->tips_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset);

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

            //get user and business
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $tip) {
                    $this->data['records'][$k]->user = $this->users_model->detail($tip->user_id);
                    $this->data['records'][$k]->business = $this->business_model->detail($tip->business_id);
                }
            }

            // Deleting anything?
            if ($this->input->post('btn_delete')) {
                $checked = $this->input->post('checked');
                if (is_array($checked) && count($checked)) {
                    $result = FALSE;
                    $success = array();
                    $error = array();
                    foreach ($checked as $id) {
                        $success = array_merge($success, $id);
//                    $result = $this->tips_model->delete($id);
//                    if($result){
//                        $success = array_merge($success,$id);
//                    }
//                    else{
//                        $error = array_merge($error, $id);
//                    }
                    }
                    if (!empty($success)) {
                        $this->messages->add('deleted successful business item id: ' . $success, "success");
                        //log_message('message', 'deleted successful business item id:'.$id);
                    }
                    if (!empty($error)) {
                        $this->messages->add(lang('deleted fail business item id: ') . $error, "error");
                        //log_message('debug', 'deleted fail business item id:'.$id);
                    }
                    redirect($this->lang->lang() . '/business/tips/index');
                }
            }
            //set asset
            $this->_assetIndex();
            $this->render_page('tips/deactivate', $this->data);
        }
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post()) {
                $id = $this->_save_tip();
                if ($id) {
                    $this->messages->add(lang('tip_create_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/tips/edit/' . $id);
                } else {
                    $this->messages->add(lang('tip_create_action_fail') . $id, "error");
                }
            }
            //business data
            $this->data['business'] = $this->business_model->find_all_by('status', '1');

            $this->_assetForm();
            $this->render_page('tips/create', $this->data);
        }
    }

    public function edit($id) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$id) {
                //redirect if invalid business id
                $this->messages->add(lang('tip_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/tips/index');
            }
            if ($this->input->post()) {
                if ($this->_save_tip('update', $id)) {
                    $this->messages->add(lang('tip_edit_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/tips/edit/' . $id);
                } else {
                    $this->messages->add(lang('tip_edit_action_fail') . $id, "error");
                }
            }
            //business data
            $this->data['record'] = $this->tips_model->detail($id);
            $this->data['record']->user = $this->users_model->detail($this->data['record']->user_id)->username;
            $this->data['record']->business = $this->business_model->detail($this->data['record']->business_id)->name;

            $this->_assetForm();
            $this->render_page('tips/edit', $this->data);
        }
    }

    private function _save_tip($type = 'insert', $id = 0) {
        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        //tip data
        $data['name'] = $this->input->post('name');
        $data['description'] = $this->input->post('description');
        $data['status'] = $this->input->post('status');

        $this->form_validation->set_rules('name', lang('tip_name'), 'required');
        $this->form_validation->set_rules('description', lang('tip_description'), 'required');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if ($type == 'insert') {
            if ($this->form_validation->run() == false) {
                $this->data['business'] = $this->business_model->find_all_by('status', '1');
                $this->data['record'] = (object) array(
                            'name' => $this->input->post('name'),
                            'description' => $this->input->post('description'),
                );
                $this->render_page('tips/create', $this->data);
            } else {
                $data['business_id'] = $this->input->post('business');
                $data['user_id'] = $this->session->userdata('user_id');
                // insert business data
                $id = $this->tips_model->insert($data);
                if ($id) {
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == false) {
                $this->data['business'] = $this->business_model->find_all_by('status', '1');
                $this->data['record'] = (object) array(
                            'name' => $this->input->post('name'),
                            'description' => $this->input->post('description'),
                            'user' => $this->input->post('user'),
                            'business' => $this->input->post('business'),
                );
                $this->render_page('tips/edit', $this->data);
            } else {
                $_POST['id'] = $id;

                // update categories data
                if ($this->tips_model->update($id, $data)) {
                    $return = $id;
                }
            }
        }
        return $return;
    }

    public function mediaList($tip_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$tip_id) {
                //redirect if invalid tip id
                $this->messages->add(lang('tip_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/tips/index');
            }

            //$status 		= $this->input->get('status') ? $this->input->get('status') : 0;
            $status = false;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            //get data
            $this->data['total'] = $this->media_model->getItems('total', $status, $keyword, false, false, $limit, $offset, 'tip_id', $tip_id);
            $this->data['records'] = $this->media_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, 'tip_id', $tip_id);
            $this->data['count'] = $this->media_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, 'tip_id', $tip_id);

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
                        $result = $this->media_model->delete($id);
                    }
                    if ($result) {
                        $this->messages->add('deleted successful media id:' . $id, "success");
                        log_message('message', 'deleted successful media id:' . $id);
                    } else {
                        $this->messages->add(lang('deleted fail media id:') . $this->ion_auth_model->error, "error");
                        log_message('debug', 'deleted fail media id:' . $id);
                    }
                    redirect($this->lang->lang() . '/business/tips/mediaList/' . $tip_id);
                }
            }
            //set asset
            $this->data['tip_id'] = $tip_id;
            $this->_assetIndexMedia();
            $this->render_page('tips/media_list', $this->data);
        }
    }

    public function mediaEdit($tip_id = false, $media_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$tip_id) {
                //redirect if invalid tip id
                $this->messages->add(lang('tip_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/tips/index');
            }
            if (!$media_id) {
                //redirect if invalid media id
                $this->messages->add(lang('tip_media_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/tips/mediaList');
            }

            if ($this->input->post()) {
                if ($this->_save_media('update', $tip_id, $media_id)) {
                    $this->messages->add(lang('tip_media_edit_action_success') . $media_id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/tips/mediaEdit/' . $tip_id . '/' . $media_id);
                } else {
                    $this->messages->add(lang('tip_media_edit_action_fail'), "error");
                }
            }
            //member data
            $this->data['record'] = $this->media_model->detail($media_id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('tip_media_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/tips/mediaList/' . $tip_id);
            }
            $this->data['tip_id'] = $tip_id;
            $this->_assetForm();
            $this->render_page('tips/media_edit', $this->data);
        }
    }

    public function mediaCreate($tip_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$tip_id) {
                //redirect if invalid business id
                $this->messages->add(lang('tip_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/tips/index');
            }

            if ($this->input->post()) {
                $id = $this->_save_media('insert', $tip_id);
                if ($id) {
                    $this->messages->add(lang('tip_media_create_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/tips/mediaEdit/' . $tip_id . '/' . $id);
                } else {
                    $this->messages->add(lang('tip_media_create_action_fail'), "error");
                }
            }
            $this->data['tip_id'] = $tip_id;
            $this->_assetForm();
            $this->render_page('tips/media_create', $this->data);
        }
    }

    private function _save_media($type = 'insert', $tip_id = false, $media_id = 0) {

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

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($field_image, $field_image_thumb),
        );

        if ($type == 'insert') {

            // upload photo & saving image
            $config['table_media']      = TRUE;
            $config['required']         = TRUE;

            $library_media = new Admin_media($config);  
            $data = $library_media->saveMediaAdmin($data);


            if ($this->form_validation->run()) {
                // insert media data
                $data ['tip_id'] = $tip_id;
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
            $_POST['id'] = $media_id;

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
            //     // saving image
            //     $this->load->helper('upload');
            //     if ($image_data = do_upload($this->config->item('listings_path'), 'path')) {
            //         //delete old image
            //         //if(unlink($banner->path)){
            //         //    log_message('info','deleted old image of banner id='.$media_id );
            //         //}
            //         $data['source'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $image_data['file_name'];

            //         //create photo thumb
            //         $this->load->helper('image');
            //         resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
            //         $file_name = explode('.', $image_data['file_name']);
            //         $data['photo_thumb'] = $this->config->item('admin_upload_path') . $this->config->item('listings_path') . $file_name[0] . '_thumb.' . $file_name[1];
            //     }
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

    private function _assetIndexMedia() {
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
            '../js/media/media-table-advanced.js',
            '../js/media/media.js',
        );

        $this->js_domready = array(
            'Metronic.init();', // init metronic core components
            'Layout.init();', // init current layout
            'QuickSidebar.init();', // init quick sidebar
            'Demo.init();', // init demo features'
            'TableAdvancedMedia.init();',
            'Media.init();'
        );
    }

    /**
     * _assetEditForm
     *
     * file_name
     */
    private function _assetForm() {
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

    function do_upload($field_name, $path = '') {
        // Use "upload" library to select image, and image will store in root directory "uploads" folder.
        $config = array(
            'upload_path' => $this->config->item('admin_upload_path') . $path,
            'upload_url' => base_url() . $this->config->item('admin_upload_path') . $path,
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
