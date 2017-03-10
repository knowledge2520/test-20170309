<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends Admin_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     *
     */
    var $data = array();

    public function __construct() {
        parent::__construct();

        $this->load->model(array('members/members_model' , 'pets/pets_model', 'badge/badge_model'));
        $this->lang->load('home');
        $this->load->helper('permission');

        if (!Permission::has_permission() && !$this->ion_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            $this->load->library(array('ion_auth'));
            $this->ion_auth->logout();
            $this->session->set_flashdata('error_message', 'sadasds'. lang('not_permission'));
            redirect('auth/login');
        }
    }

    public function index()
    {
        $this->data['member_dashboard']         = $this->members_model->getDashboard();
        $this->data['pet_dashboard']            = $this->pets_model->getDashboard();
        $this->data['member_overall']           = $this->members_model->getOverall();
        $this->data['badge_dashboard']          = $this->badge_model->getDashboard();
        $this->data['badge_overall']            = $this->badge_model->getOverall();
        $this->data['pet_overall']              = $this->pets_model->getOverall();
        // var_dump($this->data['pet_overall']   );exit;
        $this->_assetIndex();
        $this->page_title = lang('dashboard_header');
        $this->render_page('index', $this->data);
    }

    /**
     * @funciton assetIndex
     * @todo inlcude css , js for function index
     */
    private function _assetIndex(){
        $this->assets_css['page_style'] = array(
            // '../global/plugins/select2/select2.css',
            // '../global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css',
            // '../global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css',
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css',
            // '../admin/pages/css/tasks.css',
        );
        $this->assets_js['page_plugin'] = array(
            // '../global/plugins/select2/select2.min.js',
            // '../global/plugins/datatables/media/js/jquery.dataTables.min.js',
            // '../global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js',
            // '../global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js',
            // '../global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js',
            // '../global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js',
            // '../admin/pages/scripts/index.js',
            // '../admin/pages/scripts/tasks.js',
        );

        $this->js_domready = array(
            // 'Metronic.init();', // init metronic core components
            // 'Layout.init();', // init current layout
            // 'QuickSidebar.init();', // init quick sidebar
            // 'Demo.init();', // init demo features'
            // 'Index.init();'
        );
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */