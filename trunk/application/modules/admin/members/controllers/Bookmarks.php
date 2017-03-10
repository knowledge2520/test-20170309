<?php
class Bookmarks extends Admin_Controller
{
    var $data = array();
    var $module = 'members';

    function __construct(){
        parent::__construct();

        $this->load->library(array('messages'));
        $this->load->helper(array('url','language', 'permission'));
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load(array('bookmark', 'bookmark'));
        $this->load->model(array('business/business_model', 'bookmarks_model', 'members_model', 'users/permissions_model'));
    }

    function index($member_id = false)
    {
        if (!Permission::check_permission($this->module . '.edit') && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            return show_error(lang('not_permission'));
        } else {
            if(!$member_id){
            //redirect if invalid member id
            $this->messages->add(lang('member_invalid_id'), "error");
            redirect($this->lang->lang().'/members/index');
        }
        $status 		= $this->input->get('status') ? $this->input->get('status') : 0;
        $keyword	 	= $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
        $limit			= $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
        $offset 		= $this->input->get('per_page') ? $this->input->get('per_page') : 0;

        $array_field = array('id', 'name', 'dob', 'pet_type_name', 'user_name', 'breed', 'sex', 'color', 'purchase_date', 'origin', 'microchip', 'badge_id_code');
        $order_field = $this->input->get('order_field') && in_array($this->input->get('order_field'), $array_field) ? $this->input->get('order_field') : 'id';
        $sort = $this->input->get('sort') ? $this->input->get('sort') : 'DESC';
        $this->data['order_field'] = $order_field;
        $this->data['sort'] = $sort;

        $this->data['txt_search_value']        = $keyword;
        //get data
        $this->data['total'] 	= $this->bookmarks_model->getItems('total',$status,$keyword,false,false,$limit,$offset,$member_id);
        $this->data['records'] 	= $this->bookmarks_model->getItems('list',$status,$keyword,$order_field,$offset,$limit,$offset,$member_id);
        $this->data['count'] 	= $this->bookmarks_model->getItems('count_list',$status,$keyword,$order_field,$offset,$limit,$offset,$member_id);

        //pagination
        $this->load->library('pagination');
        $this->pager['base_url'] 			= current_url() .'?'.http_build_query($_GET);
        $this->pager['total_rows'] 			= $this->data['total'];
        $this->pager['per_page'] 			= $limit;
        $this->pager['page_query_string']	= TRUE;
        $this->pager['query_string_segment']= 'per_page';
        $this->pager['first_url']           = current_url().'?'.http_build_query($_GET) . '&' . $this->pager['query_string_segment'] . '=';

        //install pagination
        $this->pagination->initialize($this->pager);
        $this->data['paging'] = $this->pagination;
        $this->data['item_tableLength'] = $limit;

        //list the groups
        if($this->data['records']){
            foreach ($this->data['records'] as $k => $bookmark)
            {
                $this->data['records'][$k]->user        = $this->members_model->detail($bookmark->user_id);
                $this->data['records'][$k]->business    = $this->business_model->detail($bookmark->business_id);
            }
        }
        $this->data['member_id']            = $member_id;
        // Deleting anything?
        if ($this->input->post('btn_delete'))
        {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked))
            {
                $result = FALSE;
                foreach ($checked as $id)
                {
                    $result = $this->bookmarks_model->delete($id);
                }
                if ($result)
                {
                    $this->messages->add('deleted successful bookmark id:'.$id, "success");
                    log_message('message', 'deleted successful bookmark id:'.$id);
                }
                else
                {
                    $this->messages->add(lang('deleted fail bookmark id:'), "error");
                    log_message('debug', 'deleted fail bookmark id:'.$id);
                }
                redirect($this->lang->lang().'/members/bookmarks/index/'.$member_id);
            }
        }
        //set asset
        $this->_assetIndex();
        $this->page_title = lang('bookmark_header');
        $this->render_page('bookmarks/index', $this->data);
        }
    }

    /**
     * assetIndex
     *
     *file_name
     */
    private function _assetIndex(){
        $this->assets_css['page_style'] = array(
            // //'../global/plugins/select2/select2.css',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            // '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
            // '../admin/pages/css/profile.css',
            // '../admin/pages/css/tasks.css',
            '../global/plugins/select2/css/select2.css',
            '../global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css',
            '../global/plugins/datatables/datatables.min.css',
        );
        $this->assets_js['page_plugin'] = array(
            // '../global/plugins/fuelux/js/spinner.min.js',
            // '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            // '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            // '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            // '../global/plugins/typeahead/typeahead.bundle.min.js',
            // '../global/plugins/select2/select2.min.js',
            // '../admin/pages/scripts/components-pickers.js',
            // '../js/bookmark/bookmark.js',
            // '../js/bookmark/components-form-tools.js',
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
            // //'FormSamples.init();',
            // 'ComponentsFormTools.init();',
            // 'ComponentsPickers.init();',
            'TableAdvancedCustom.init();',
            'Custom.init();',
        );
    }

    function do_upload($field_name , $path = '') {
        // Use "upload" library to select image, and image will store in root directory "uploads" folder.
        $config = array(
            'upload_path' => $this->config->item ( 'admin_upload_path' ) . $this->config->item ( 'bookmark_path' ) . $path,
            'upload_url' => base_url() . $this->config->item ( 'admin_upload_path' ).$this->config->item ( 'bookmark_path' ) . $path,
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