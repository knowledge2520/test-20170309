<?php

class Reviews extends Admin_Controller {

    var $data = array();
    var $module = 'members';

    function __construct() {
        parent::__construct();

        $this->load->library(array('messages'));
        $this->load->model(array('reviews_model', 'media_model', 'users/users_model', 'business/business_model'));
        $this->load->helper(array('url', 'language', 'permission'));
        $this->lang->load('reviews');
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

            $this->data['txt_search_value'] = $keyword;
            //get data
            $this->data['total'] = $this->reviews_model->getItems('total', $status, $keyword, false, false, $limit, $offset, $member_id);
            $this->data['total_deactivate'] = $this->reviews_model->getItems('total', 0, $keyword, false, false, $limit, $offset, $member_id);
            $this->data['records'] = $this->reviews_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id);
            $this->data['count'] = $this->reviews_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id);
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
            //get user and member
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $review) {
                    $this->data['records'][$k]->business = $this->business_model->detail($review->business_id);
                }
            }
            $this->data['member_id'] = $member_id;
            // Deleting anything?
            if ($this->input->post('btn_delete')) {
                $checked = $this->input->post('checked');
                if (is_array($checked) && count($checked)) {
                    $result = FALSE;
                    foreach ($checked as $id) {
                        $result = $this->reviews_model->delete($id);
                    }
                    if ($result) {
                        $this->messages->add('deleted successful review item id:' . $id, "success");
                        log_message('message', 'deleted successful review item id:' . $id);
                    } else {
                        $this->messages->add(lang('deleted fail review item id:') . $this->ion_auth_model->error, "error");
                        log_message('debug', 'deleted fail review item id:' . $id);
                    }
                    redirect($this->lang->lang() . '/members/reviews/index/' . $member_id);
                }
            }
            //set asset
            $this->_assetIndex();
            $this->page_title = lang('review_header');
            $this->render_page('reviews/index', $this->data);
        }
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if ($this->input->post()) {
                $id = $this->_save_review();
                if ($id) {
                    $this->messages->add(lang('review_create_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/members/reviews/edit/' . $id);
                } else {
                    $this->messages->add(lang('review_create_action_fail') . $id, "error");
                }
            }
            //business data
            $this->data['business'] = $this->business_model->find_all_by('status', '1');

            $this->_assetForm();
            $this->page_title = lang('review_header');
            $this->render_page('reviews/create', $this->data);
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
                redirect($this->lang->lang() . '/members/review/index');
            }
            if ($this->input->post()) {
                if ($this->_save_review('update', $id)) {
                    $this->messages->add(lang('review_edit_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/reviews/edit/' . $id);
                } else {
                    $this->messages->add(lang('review_edit_action_fail') . $id, "error");
                }
            }
            //review data
            $this->data['record'] = $this->reviews_model->detail($id);
            $this->data['member_id'] = $this->data['record']->user_id;
            //get user and business data
            if ($this->data['record']) {
                $this->data['record']->user = $this->users_model->detail($this->data['record']->user_id)->username;
                $this->data['record']->business = $this->business_model->detail($this->data['record']->business_id)->name;
            }
            $this->_assetForm();
            $this->page_title = lang('review_header');
            $this->render_page('reviews/edit', $this->data);
        }
    }

    private function _save_review($type = 'insert', $id = 0) {
        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        //category data
        $data['name'] = $this->input->post('name');
        $data['description'] = $this->input->post('description');
        $data['content'] = $this->input->post('content');
        $data['status'] = $this->input->post('status');
        $data['rate'] = $this->input->post('rate');

        $this->form_validation->set_rules('name', lang('review_name'), 'required');
        $this->form_validation->set_rules('description', lang('review_description'), 'required');
        $this->form_validation->set_rules('content', lang('review_content'), 'required');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');
        if ($type == 'insert') {
            if ($this->form_validation->run() == false) {
                //business data
                $this->data['business'] = $this->business_model->find_all_by('status', '1');
                $this->data['record'] = (object) array(
                            'name' => $this->input->post('name'),
                            'description' => $this->input->post('description'),
                            'content' => $this->input->post('content'),
                );
                $this->page_title = lang('review_header');
                $this->render_page('reviews/create', $this->data);
            } else {
                // insert review data
                $data['business_id'] = $this->input->post('business');
                $data['user_id'] = $this->session->userdata('user_id');
                $id = $this->reviews_model->insert($data);
                if ($id) {
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == false) {
                //business data
                $this->data['business'] = $this->business_model->find_all_by('status', '1');
                $this->data['record'] = (object) array(
                            'name' => $this->input->post('name'),
                            'description' => $this->input->post('description'),
                            'content' => $this->input->post('content'),
                            'user' => $this->input->post('user'),
                            'business' => $this->input->post('business'),
                );
                $this->page_title = lang('review_header');
                $this->render_page('reviews/create', $this->data);
            } else {
                // update category data
                if ($this->reviews_model->update($id, $data)) {
                    $return = $id;
                }
            }
        }
        return $return;
    }

    public function mediaList($member_id = false, $review_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$member_id) {
                //redirect if invalid review id
                $this->messages->add(lang('member_invalid_id'), "error");
                redirect($this->lang->lang() . '/members/index');
            }
            if (!$review_id) {
                //redirect if invalid review id
                $this->messages->add(lang('review_invalid_id'), "error");
                redirect($this->lang->lang() . '/members/reviews/index/' . $member_id);
            }

            //$status 		= $this->input->get('status') ? $this->input->get('status') : 0;
            $status = false;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;

            //get data
            $this->data['total'] = $this->media_model->getItems('total', $status, $keyword, false, false, $limit, $offset, $member_id, 'review_id', $review_id);
            $this->data['records'] = $this->media_model->getItems('list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id, 'review_id', $review_id);
            $this->data['count'] = $this->media_model->getItems('count_list', $status, $keyword, 'id', 'DESC', $limit, $offset, $member_id, 'review_id', $review_id);

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
                        //log_message('message', 'deleted successful media id:'.$id);
                    } else {
                        $this->messages->add(lang('deleted fail media id:') . $this->ion_auth_model->error, "error");
                        //log_message('debug', 'deleted fail media id:'.$id);
                    }
                    redirect($this->lang->lang() . '/business/reviews/mediaList/' . $review_id);
                }
            }
            //set asset
            $this->data['member_id'] = $member_id;
            $this->data['review_id'] = $review_id;
            $this->_assetIndexMedia();
            $this->page_title = lang('review_header') . ' | ' . lang('review_media_header');
            $this->render_page('reviews/media_list', $this->data);
        }
    }

    public function mediaEdit($review_id = false, $media_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$review_id) {
                //redirect if invalid review id
                $this->messages->add(lang('review_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/reviews/index');
            }
            if (!$media_id) {
                //redirect if invalid media id
                $this->messages->add(lang('review_media_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/reviews/mediaList');
            }

            if ($this->input->post()) {
                if ($this->_save_media('update', $review_id, $media_id)) {
                    $this->messages->add(lang('review_media_edit_action_success') . $media_id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/mediaEdit/' . $review_id . '/' . $media_id);
                } else {
                    $this->messages->add(lang('review_media_edit_action_fail'), "error");
                }
            }
            //member data
            $this->data['record'] = $this->media_model->detail($media_id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('review_media_invalid_id'), "error");
                redirect($this->lang->lang() . '/banners/index');
            }
            $this->data['review_id'] = $review_id;
            $this->_assetForm();
            $this->page_title = lang('review_header') . ' | ' . lang('review_media_header');
            $this->render_page('reviews/media_edit', $this->data);
        }
    }

    public function mediaCreate($review_id = false) {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if (!$review_id) {
                //redirect if invalid business id
                $this->messages->add(lang('review_invalid_id'), "error");
                redirect($this->lang->lang() . '/business/reviews/index');
            }

            if ($this->input->post()) {
                $id = $this->_save_media('insert', $review_id);
                if ($id) {
                    $this->messages->add(lang('review_media_create_action_success') . $id, "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/business/reviews/mediaEdit/' . $review_id . '/' . $id);
                } else {
                    $this->messages->add(lang('review_media_create_action_fail'), "error");
                }
            }
            $this->data['review_id'] = $review_id;
            $this->_assetForm();
            $this->page_title = lang('review_header') . ' | ' . lang('review_media_header');
            $this->render_page('reviews/media_create', $this->data);
        }
    }

    private function _save_media($type = 'insert', $review_id = false, $media_id = 0) {

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

            // upload photo
            $media_image = $_FILES['path']['name'];
            if (isset($media_image) && $media_image != '') {
                // upload photo & saving image
                $config['table_media']      = TRUE;
                $config['required']         = TRUE;

                $library_media = new Admin_media($config);  
                $data = $library_media->saveMediaAdmin($data);


                if ($this->form_validation->run()) {
                    // insert media data
                    $data ['review_id'] = $review_id;
                    $data ['user_id'] = $this->session->userdata('user_id');
                    $id = $this->media_model->insert($data);
                    if ($id) {
                        $return = $id;
                    }
                }

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
            }

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

    private function _assetIndexMedia() {
        $this->assets_css['page_style'] = array(
            // '../global/plugins/select2/select2.css',
            // '../global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css',
            // '../global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css',
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css',
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
        );
        $this->assets_js['page_plugin'] = array(
            // '../global/plugins/select2/select2.min.js',
            // '../global/plugins/datatables/media/js/jquery.dataTables.min.js',
            // '../global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js',
            // '../global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js',
            // '../global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js',
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js',
            // '../js/media/media-table-advanced.js',
            // '../js/media/media.js',
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
        //     'TableAdvancedMedia.init();',
        //     'Media.init();'
        // );
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
            // '../admin/pages/css/profile.css',
            // '../admin/pages/css/tasks.css',
            // '../global/plugins/jquery-multi-select/css/multi-select.css',
            // '../global/plugins/bootstrap-select/bootstrap-select.min.css',
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css',
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css',
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
            // '../admin/pages/scripts/components-pickers.js',
            // '../js/users/users.js',
            // '../js/users/components-form-tools.js',
            // '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            // '../global/plugins/bootstrap-select/bootstrap-select.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js',
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
        //     'TableAdvanced.init();',
        // );
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
