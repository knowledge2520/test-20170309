<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends Admin_Controller {

    var $data = array();
    var $module = 'pet_talk.categories';

    function __construct() {
        parent::__construct();

        $this->lang->load(array('pet_talk', 'category'));
        $this->load->model(array('categories_model', 'users/permissions_model'));
        $this->load->library(array('ion_auth', 'messages'));
        $this->load->helper('permission');

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
    }

    function index() {
        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = array(0,1);    //$this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $this->data['offset'] = $offset;
            
            $array_field = array('id', 'name', 'description', 'sort');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
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
        }
        //set status
        if($this->input->post('set_status')){            
            $id = $this->input->post('id');
            $status = $this->input->post('status');
            
            $this->categories_model->update_status($id, $status);
            die();
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
                    $this->messages->add(lang('category_delete_action_success', $id), "success");
                    //log_message('message', 'deleted successful cattegory id:' . $id);
                } else {
                    $this->messages->add(lang('category_delete_action_fail', $id), "error");
                    //log_message('debug', 'deleted fail cattegory id:' . $id);
                }
                redirect($this->lang->lang() . '/pet_talk/categories');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('category_header');
        $this->render_page('categories/index', $this->data);
    }

    public function create() {
        if (!Permission::check_permission($this->module . '.create') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            //load categories
            if ($this->input->post()) {
                $id = $this->_save_category();
                if ($id) {
                    $this->messages->add(lang('category_create_action_success', $id), "success");
                    //redirect to edit page
                    if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
                        redirect($this->lang->lang() . '/pet_talk/categories');
                    } else {
                        redirect($this->lang->lang() . '/pet_talk/categories/edit/' . $id);
                    }
                } else {
                    $this->messages->add(lang('category_create_action_fail'), "error");
                }
            }

            $this->_assetForm();
            $this->page_title = lang('category_header');
            $this->render_page('categories/create');
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
                redirect($this->lang->lang() . '/pet_talk/categories/index');
            }

            if ($this->input->post()) {
                if ($this->_save_category('update', $id)) {
                    $this->messages->add(lang('category_edit_action_success', $id), "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/pet_talk/categories/edit/' . $id);
                } else {
                    $this->messages->add(lang('category_edit_action_fail', $id), "error");
                }
            }
            //member data
            $this->data['record'] = $this->categories_model->detail($id);
            if (empty($this->data['record'])) {
                //redirect if invalid member id
                $this->messages->add(lang('categories_invalid_id'), "error");
                redirect($this->lang->lang() . '/pet_talk/categories/index');
            }

            $this->_assetForm();
            $this->page_title = lang('category_header');
            $this->render_page('categories/edit', $this->data);
        }
    }

    private function _save_category($type = 'insert', $id = 0) {
        $this->load->library('image_lib');

        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        //category data
        $data['name'] = $this->input->post('name');
        $data['description'] = $this->input->post('description');
        $data['status'] = $this->input->post('status');
        $data['sort'] = $this->input->post('sort');
        $data['is_popular'] = $this->input->post('popular') != NULL ? 1 : 0;

        $this->data['record'] = (object) array(
                    'name' => $this->input->post('name'),
                    'description' => $this->input->post('description'),
                    'sort' => $this->input->post('sort'),
                    'status' => $this->input->post('status'),
                    'is_popular' => $this->input->post('popular') != NULL ? 1 : 0,
        );

        $this->form_validation->set_rules('name', lang('category_name'), 'required');
        $this->form_validation->set_rules('description', lang('category_description'), 'required');
        $this->form_validation->set_rules('sort', lang('category_sort'), 'integer');
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');

        //config upload media
        $path = DEFAULT_PATH_ADMIN . $this->config->item('pet_path');
        $upload_field_name  = 'photo';
        $field_image = 'photo';
        $field_image_thumb = 'photo_thumb';    

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($field_image, $field_image_thumb),
        );

        if ($type == 'insert') {
            if ($this->form_validation->run() == FALSE) {
                $this->page_title = lang('category_header');
                $this->render_page('categories/create', $this->data);
            } else {
                // upload photo & saving image
                $library_media = new Admin_media($config);  
                $data = $library_media->saveMediaAdmin($data);

                // $pet_talk_image = $_FILES['photo']['name'];
                // if (isset($pet_talk_image) && $pet_talk_image != '') {
                //     // saving image
                //     $this->load->helper('upload');
                //     if ($image_data = do_upload($this->config->item('pet_path'), 'photo')) {
                //         $this->data['record']->photo = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $image_data['file_name'];

                //         //create photo thumb
                //         $this->load->helper('image');
                //         resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                //         $file_name = explode('.', $image_data['file_name']);
                //         $this->data['record']->photo_thumb = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $file_name[0] . '_thumb.' . $file_name[1];
                //     }
                // }
                
                // insert data
                $id = $this->categories_model->insert($data);
                if ($id) {
                    $return = $id;
                }
            }
        } elseif ($type == 'update') {
            if ($this->form_validation->run() == FALSE) {
                $this->page_title = lang('category_header');
                $this->render_page('categories/edit', $this->data);
            } else {
                //load category data
                $item = $this->categories_model->find($id);

                // upload photo
                $config['type'] = 'update';
                $config['item'] = $item;
                $library_media = new Admin_media($config);                

                $data = $library_media->saveMediaAdmin($data);

                // $pet_talk_image = $_FILES['photo']['name'];
                // if (isset($pet_talk_image) && $pet_talk_image != '') {
                //     // saving image
                //     $this->load->helper('upload');
                //     if ($image_data = do_upload($this->config->item('pet_path'), 'photo')) {
                //         $this->data['record']->photo = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $image_data['file_name'];

                //         //create photo thumb
                //         $this->load->helper('image');
                //         resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, TRUE, TRUE);
                //         $file_name = explode('.', $image_data['file_name']);
                //         $this->data['record']->photo_thumb = $this->config->item('admin_upload_path') . $this->config->item('pet_path') . $file_name[0] . '_thumb.' . $file_name[1];
                //     }
                // }

                // update category data
                if ($this->categories_model->update($id, $data)) {
                    $return = $id;
                }
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
    private function _assetForm() {
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
            // '../js/users/components-form-tools.js',
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
        //     'ComponentsDateTimePickers.init();',
        // );
    }

}
