<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Content extends Front_Controller {

    var $data = array();
    public function __construct() {
        parent::__construct();
        $this->load->helper(array('url','language'));
    }
    public function index()
    {
        redirect('home');
    }

    public function policy(){
        $this->template->title('Privacy Policy');      
        $this->render_page('policy_tpl');

    }
    public function actived_successful(){
    	$this->template->title('Email Verification Complete');
    	$this->render_page('actived_successful_tpl');
    
    }
    public function actived_fail(){
    	$this->template->title('Account Activation Unsuccessful');
    	$this->render_page('actived_fail_tpl');
    
    }
    
    public function already_actived(){
    	$this->template->title('[Pet Widget] Account Already Active');
    	$this->render_page('already_actived_tpl');
    
    }
   
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */