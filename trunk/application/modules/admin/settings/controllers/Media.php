<?php

class Media extends Admin_Controller {

    var $data = array();

    function __construct() {
        parent::__construct();

        $this->lang->load('settings');
        $this->load->model('settings_model');
        $this->load->library(array('messages'));
        $this->load->helper(array('url', 'language'));
    }

    function index() {
        redirect('settings','refresh');
        if (!$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error('You must be an administrator to view this page.');
        } else {
            $array = array('pet_widget_image_listings', 'pet_widget_image_mypets', 'pet_widget_image_pettalk', 'pet_widget_image_shop');
            $this->data['records'] = $this->settings_model->get_setting($array);

            $this->_assetIndex();
            $this->render_page('media/index', $this->data);
        }
    }

    function edit($id) {
        redirect('settings','refresh');
        if (!$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error('You must be an administrator to view this page.');
        } else {
            
            switch ($id) {
                case 1: $name = 'pet_widget_image_listings';
                    break;
                case 2: $name = 'pet_widget_image_mypets';
                    break;
                case 3: $name = 'pet_widget_image_pettalk';
                    break;
                case 4: $name = 'pet_widget_image_shop';
                    break;
            }

            if ($this->input->post()) {
                $result = $this->_save_media($name);
                if ($result) {
                    $this->messages->add('Save success', "success");
                    //redirect to edit page
                    redirect($this->lang->lang() . '/settings/media/edit/' . $id);
                } else {
                    $this->messages->add('Save failure', "error");
                }
            }

            $this->data['record'] = $this->settings_model->get_setting(array($name));
            $this->data['name'] = $name;
            $this->_assetForm();
            $this->render_page('media/edit', $this->data);
        }
    }

    private function _save_media($name) {
        $return = false;
        // make sure we only pass in the fields we want
        $data = array();

        $this->load->library('image_lib');

        //config upload media
        $path = DEFAULT_PATH_ADMIN;
        $upload_field_name  = 'path';
        $field_image = $name;   

        $config = array(
            'upload_field_name'     => $upload_field_name,
            'path'                  => $path,
            'field_image'           => array($field_image),
            'resize_size'           => array(
                    'resize_width'  => 400,
                    'resize_height' => 300,
                    'resize_thumb'  => TRUE,
                    'resize_ratio'  => TRUE
                )
        );
        $this->form_validation->set_rules('status', 'status', 'required');

        //load media data
        $media = $this->settings_model->get_setting(array($name));

        // upload photo & saving image
        $config['required']         = TRUE;

        $library_media = new Admin_media($config);  
        $data = $library_media->saveMediaAdmin($data);

        if ($this->form_validation->run()) {
            // insert media data
            S3_Upload::removeByKeyValue($media[$name]);
            S3_Upload::removeByKeyValue(str_replace("_thumb", "", $media[$name]));
            
            $this->settings_model->save_settings($data);

            $return = TRUE;
        }

        // $media_image = $_FILES['path']['name'];
        // if (isset($media_image) && $media_image != '') {


        //     $this->load->helper('upload');
        //     if ($image_data = do_upload('', 'path')) {
        //         //delete old image
        //         //if(unlink($banner->path)){
        //         //    log_message('info','deleted old image of banner id='.$media_id );
        //         //}
        //         //$this->data['setting'] = $this->config->item('admin_upload_path') . $image_data['file_name'];

        //         //create photo thumb
        //         $this->load->helper('image');
        //         resizeImage($image_data['full_path'], 400, 300, TRUE, TRUE);
        //         $file_name = explode('.', $image_data['file_name']);
        //         $this->data['setting'] = array(
        //             $name => $this->config->item('admin_upload_path') . $file_name[0] . '_thumb.' . $file_name[1],
        //         );

        //         // update banner data
        //         $this->settings_model->save_settings($this->data['setting']);
        //         $return = TRUE;

        //     } else {
        //         $this->messages->add($this->upload->display_errors(), "error");
        //         $return = FALSE;
        //     }
        // } else {
        //     $return = TRUE;
        // }

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
        //     'ComponentsFormTools.init();',
        //     'ComponentsPickers.init();',
        // );
    }

}
