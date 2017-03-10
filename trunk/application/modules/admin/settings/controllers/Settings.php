<?php
class Settings extends Admin_Controller{
     var $data = array();

    function __construct(){
        parent::__construct();

        $this->lang->load('settings');
        $this->load->model(array('shipping_model', 'settings_model'));
        $this->load->library(array('messages'));
        $this->load->helper(array('url','language'));
    }

    public function index(){
        if(!$this->ion_auth->is_admin())
        {
            //redirect them to the home page because they must be an administrator to view this
            return show_error('You must be an administrator to view this page.');
        }
        else{
            if($this->input->post()){
                if($this->_save_setting())
                {
                    $this->messages->add(lang('setting_save_action_success'), "success");
                    //redirect to edit page
                    redirect($this->lang->lang().'/settings');
                }
                else {
                    $this->messages->add(lang('setting_save_action_fail'), "error");
                }
            }

            $this->data['shipping'] = $this->shipping_model->find_by('countryCode', 'SG');
			$this->data['setting'] = $this->settings_model->get_settings();
			
            $this->_assetForm();
            $this->render_page('index',$this->data);
        }
    }

    private function _save_setting(){

        $return = FALSE;
        $this->form_validation->set_rules('fee_shipping', lang('setting_shipping_fee'), 'required') ;
        $this->form_validation->set_rules('meta_keywords', lang('setting_meta_keywords'), 'required') ;
        $this->form_validation->set_rules('meta_description', lang('setting_meta_description'), 'required') ;
        $this->form_validation->set_rules('website_address', lang('setting_website_address'), 'required') ;
        $this->form_validation->set_rules('website_email', lang('setting_website_email'), 'required') ;
        $this->form_validation->set_rules('website_phone', lang('setting_website_phone'), 'required') ;
        $this->form_validation->set_rules('radius_nearby_distance', lang('setting_radius_nearby_distance'), 'required|numeric|greater_than[-1]') ;
        $this->form_validation->set_rules('listing_distance', lang('setting_listing_distance'), 'required|numeric|greater_than[-1]') ;
        $this->form_validation->set_error_delimiters('<span class="help-block">', '</span>');

        if($this->form_validation->run() == TRUE){
        	$data['shipping']['fee_shipping']       = $this->input->post('fee_shipping');
        	//        var_dump($data);exit;
        	$this->shipping_model->update_where('countryCode', 'SG', $data['shipping']);
        	
        	$this->data['setting'] = array(
        			'meta_keywords' => $this->input->post('meta_keywords'),
        			'meta_description' => $this->input->post('meta_description'),
        			'website_address' => $this->input->post('website_address'),
        			'website_email' => $this->input->post('website_email'),
        			'website_phone' => $this->input->post('website_phone'),
        			'radius_nearby_distance' => $this->input->post('radius_nearby_distance'),
        			'listing_distance' => $this->input->post('listing_distance'),
        	);
        	$this->settings_model->save_settings($this->data['setting']);
        	$return = TRUE;
        
        }
        
        return $return;
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