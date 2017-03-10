<?php
class Likes extends Admin_Controller
{
    var $data = array();

    function __construct(){
        parent::__construct();

        $this->load->library(array('messages'));
        $this->load->helper(array('url','language'));
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load(array('like'));
        $this->load->model(array('likes_model', 'members_model', 'business/tips_model', 'products/products_model', 'pet_talk/pet_talk_model'));
    }

    function index()
    {
        $status 		= $this->input->get('status') ? $this->input->get('status') : 0;
        $keyword	 	= $this->input->get('txt_search') ? $this->input->get('txt_search') : '';
        $limit			= $this->input->get('dataTables_length') ? $this->input->get('dataTables_length') : $this->limit;
        $offset 		= $this->input->get('per_page') ? $this->input->get('per_page') : 0;

        $this->data['txt_search_value']        = $keyword;
        //get data
        $this->data['total'] 	= $this->likes_model->getItems('total',$status,$keyword,false,false,$limit,$offset);
        $this->data['records'] 	= $this->likes_model->getItems('list',$status,$keyword,'id','DESC',$limit,$offset);
        $this->data['count'] 	= $this->likes_model->getItems('count_list',$status,$keyword,'id','DESC',$limit,$offset);

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
            foreach ($this->data['records'] as $k => $like)
            {
                $this->data['records'][$k]->user        = $this->members_model->detail($like->user_id);
                $this->data['records'][$k]->tip         = $this->tips_model->detail($like->tip_id);
                $this->data['records'][$k]->topic       = $this->pet_talk_model->detail($like->topic_id);
                $this->data['records'][$k]->product     = $this->products_model->detail($like->product_id);
            }
        }
        // Deleting anything?
        if ($this->input->post('btn_delete'))
        {
            $checked = $this->input->post('checked');
            if (is_array($checked) && count($checked))
            {
                $result = FALSE;
                foreach ($checked as $id)
                {
                    $result = $this->likes_model->delete($id);
                }
                if ($result)
                {
                    $this->messages->add('deleted successful like id:'.$id, "success");
                    log_message('message', 'deleted successful like id:'.$id);
                }
                else
                {
                    $this->messages->add(lang('deleted fail like id:'), "error");
                    log_message('debug', 'deleted fail like id:'.$id);
                }
                redirect($this->lang->lang().'/members/likes/index');
            }
        }
        //set asset
        $this->_assetIndex();
        $this->render_page('likes/index', $this->data);
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
            //'../global/plugins/select2/select2.css',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.css',
            '../global/plugins/bootstrap-datepicker/css/datepicker3.css',
            '../admin/pages/css/profile.css',
            '../admin/pages/css/tasks.css',
        );
        $this->assets_js['page_plugin'] = array(
            '../global/plugins/fuelux/js/spinner.min.js',
            '../global/plugins/bootstrap-fileinput/bootstrap-fileinput.js',
            '../global/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js',
            '../global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js',
            '../global/plugins/typeahead/typeahead.bundle.min.js',
            '../global/plugins/select2/select2.min.js',
            '../admin/pages/scripts/components-pickers.js',
        );

        $this->js_domready = array(
            'Metronic.init();', // init metronic core components
            'Layout.init();', // init current layout
            'QuickSidebar.init();', // init quick sidebar
            'Demo.init();', // init demo features'
            //'FormSamples.init();',
            'ComponentsFormTools.init();',
            'ComponentsPickers.init();',
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