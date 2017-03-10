<?php

class Trash extends Admin_Controller {

    var $data = array();
    var $module = 'business';

    function __construct() {
        parent::__construct();

        $this->load->library(array('ion_auth', 'messages'));
        $this->load->helper(array('url', 'language'));

        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load(array('ion_auth', 'business'));
        $this->load->model(array('auth/ion_auth_model', 'business_model', 'categories_model', 'media_model', 'users/permissions_model'));
        $this->load->helper('permission');
        $this->load->helper('listing');
    }

    function index() {

        if (!Permission::check_permission($this->module . '.index') && !$this->ion_auth->is_admin() && !Permission::check_permission($this->module . '.individual')) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            $status = array(2);            //$this->input->get('status') ? $this->input->get('status') : 0;
            $keyword = $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
            $limit = $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
            $offset = $this->input->get('per_page') ? $this->input->get('per_page') : 0;
            $my_listing = $this->input->get('my_listing') ? true : false;
            $this->data['offset'] = $offset;
            
            $this->data['countries'] = $this->business_model->getCountries($this->session->userdata('user_id')); 
            $default_country = '';
            if(sizeof($this->data['countries']) > 1){
            	$default_country = array_keys($this->data['countries'])[1];
            }
            $this->data['default_country'] = $default_country;
            $country = $this->input->get('country') ? $this->input->get('country') : $default_country;
            
            $array_field = array('id', 'name', 'full_name', 'address', 'hour', 'phone', 'website', 'status', 'created_date');
            $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
            $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
            $this->data['order_field'] = $order_field;
            $this->data['sort'] = $sort;

            $this->data['txt_search_value'] = $keyword;
            $this->data['country'] = $country;
            //get data
            $this->data['total'] = $this->business_model->getItems('total', $status, $keyword, false, false, $limit, $offset, $my_listing, $this->session->userdata('user_id'), $country);
            $this->data['records'] = $this->business_model->getItems('list', $status, $keyword, $order_field, $sort, $limit, $offset, $my_listing, $this->session->userdata('user_id'), $country);
            $this->data['count'] = $this->business_model->getItems('count_list', $status, $keyword, $order_field, $sort, $limit, $offset, $my_listing, $this->session->userdata('user_id'), $country);           

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
            $this->data['my_listing'] = $my_listing;

            $this->data['permissions'] = $this->permissions_model->get_permissions_user($this->session->userdata('user_id'));
            $this->data['module'] = $this->module;
            $this->data['is_admin'] = $this->ion_auth->is_admin();

            //list the categories
            if ($this->data['records']) {
                foreach ($this->data['records'] as $k => $business) {
                    $this->data['records'][$k]->categories = $this->business_model->get_business_categories($business->id)->result();
                    $this->data['records'][$k]->individual = $this->business_model->check_owner('business_items', $business->id, $this->session->userdata('user_id'));
                }
            }
        }
//        echo '<pre>'.var_dump($this->data['records']).'</pre>';
//        exit;
        // Deleting anything?
        if ($this->input->post('btn_restore')) {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked)) {
                $result = FALSE;
                $adminListing = new Admin_listing();
                foreach ($checked as $id) {
                    if(Permission::check_permission($this->module . '.delete') || $this->ion_auth->is_admin() || (Permission::check_permission($this->module . '.individual') && $this->business_model->check_owner('business_items', $id, $this->session->userdata('user_id')))){
                        //$result = $this->business_model->deleteBusiness($id);
                        $result = $adminListing->restoreListing($id);
                        if ($result) {
                            // Add log for deleted
                            $this->business_model->addActivityLog("Restored",'Business',$id,"","","");
                            // End
                            $this->messages->add(lang('business_restore_action_success', $id), "success");
                            //log_message('message', 'deleted successful business item id:'.$id);
                        } else {
                            $this->messages->add(lang('business_restore_action_fail', $id), "error");
                            //log_message('debug', 'deleted fail business item id:'.$id);
                        }
                    }
                }

                redirect($this->lang->lang() . '/business/trash/index');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('business_trash_header');
        $this->render_page('trash/index', $this->data);
    }

    public function detail($id) {
        if (!$id) {
            //redirect if invalid business id
            $this->messages->add(lang('business_invalid_id'), "error");
            redirect($this->lang->lang() . '/business/index');
        }
        if(Permission::check_permission($this->module . '.individual')){
            if(!$this->business_model->check_owner('business_items', $id, $this->session->userdata('user_id'))){
                // return show_404();
            }
        }

        //business data
        $this->data['categories_items'] = $this->categories_model->getCategories(array('status' => 1, 'name!=' => 'Nearby'));
        $this->data['record'] = $this->business_model->detail($id);
        // Add log for view
        $this->business_model->addActivityLog("Viewed","Business",$id,"","","");
        // End
        if (empty($this->data['record'])) {
            //redirect if invalid business id
            $this->messages->add(lang('business_invalid_id'), "error");
            redirect($this->lang->lang() . '/business/index');
        }
        $hour = explode("-", $this->data['record']->hour);
        if (sizeof($hour) == 2) {
            $this->data['record']->start_time = str_replace("h", ":", $hour[0]);
            $this->data['record']->end_time = str_replace("h", ":", $hour[1]);
        } else {
            $this->data['record']->start_time = "0:00";
            $this->data['record']->end_time = str_replace("h", ":", $hour[0]);
        }

        //list the business
        $this->data['record']->categories = $this->business_model->get_business_categories($id)->result();
        //load map google
        $this->load->library('googlemaps');

        $location = $this->data['record']->latitude . ', ' . $this->data['record']->longitude;
        //var_dump($location);exit;
        $config['center'] = $location;
        $config['zoom'] = '16';
        $config['disableDoubleClickZoom'] = 'true';

        $this->googlemaps->initialize($config);

        $marker = array();
        $marker['position'] = $location;
        $marker['draggable'] = true;
        $marker['ondragend'] = '
        document.getElementById("lat").innerHTML =  \'<input type="text" class="form-control" name="latitude" value= \' + event.latLng.lat() + \' > \';
        document.getElementById("long").innerHTML = \'<input type="text" class="form-control" name="longitude" value= \' + event.latLng.lng() + \' > \' ';

        $this->googlemaps->add_marker($marker);
        $this->data['map'] = $this->googlemaps->create_map();
        $this->data['individual'] = Permission::check_permission($this->module . '.individual');
        
        $this->_assetForm();
        $this->page_title = lang('business_trash_header');
        $this->render_page('trash/detail', $this->data);
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
            // '../global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.css'
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
            // '../js/custom/custom.js',
            // '../js/custom/components-form-tools.js',
            // '../global/plugins/jquery-multi-select/js/jquery.multi-select.js',
            // '../global/plugins/bootstrap-select/bootstrap-select.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js',
            // '../global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.js',
            // 'src="http://maps.google.com/maps/api/js?sensor=false&libraries=places&callback=initMap" async defer',
            // '../js/map/map.js',
            '../global/plugins/select2/js/select2.min.js',
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            '../pages/scripts/components-date-time-pickers.min.js',
        );
        // $this->assets_js['page_script'] = array(
        //'../js/map/map.js',
        //'src="http://maps.google.com/maps/api/js?sensor=false&libraries=places&callback=initMap" async defer',
        //);
        // $this->js_domready = array(
        //     'Metronic.init();', // init metronic core components
        //     'Layout.init();', // init current layout
        //     'QuickSidebar.init();', // init quick sidebar
        //     'Demo.init();', // init demo features'
        //     'ComponentsFormTools.init();',
        //     'ComponentsPickers.init();',
        //     'TableAdvanced.init();',
        //     'Map.init();',
        //     'ComponentsDateTimePickers.init();',
        // );
    }

}
