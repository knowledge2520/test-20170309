<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contact extends Front_Controller {

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
        $this->load->helper(array('url','language', 'captcha'));
        $this->load->model('contact_model');
        $this->load->library('form_validation');
    }
    public function index()
    {
        $this->load->library('email');
        $random_number = substr(number_format(time() * rand(),0,'',''),0,6);
      // setting up captcha config
        $vals = array(
            'word' => $random_number,
            'img_path' => './themes/front/captcha/',
            'img_url' => base_url('../themes/front/captcha/'),
            'font_path' => './themes/front/font/OpenSans-Bold.ttf',
            'img_width' => '150',
            'font_size' => '20',
            'img_height' => '50',
            'expiration' => 3600,
            'pool' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',

            // White background and border, black text and red grid
            'colors' => array(
                'background' => array(255, 255, 255),
                'border' => array(255, 255, 255),
                'text' => array(0, 0, 0),
                'grid' => array(255, 40, 40)
            )
        );
        
        if($this->input->post()){

            $this->data['email']            = $this->input->post('email');
            $this->data['name']             = $this->input->post('name');
            $this->data['phone']            = $this->input->post('phone');
            $this->data['contact_message']  = $this->input->post('contact_message');
           
            
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('email_confirm', 'Email', 'required|matches[email]');
            $this->form_validation->set_rules('name', 'Name', 'required');
            $this->form_validation->set_rules('phone', 'Phone', 'required');
            $this->form_validation->set_rules('contact_message', 'Message', 'required');
            $this->form_validation->set_rules('captcha', 'Captcha', 'required');    
            
            if($this->form_validation->run() == TRUE){
                
                if($this->input->post('captcha') != $this->session->userdata['captchaWord']){
                     $this->data['message'] = "Wrong captcha";
                     //$this->form_validation->set_message('captcha', 'Wrong captcha code');
                }
                else{
                    $send_email = $this->contact_model->send_contact_email($this->data);
                    
                    if($send_email){
                        $this->data['success'] = 'success';
                        $this->data = array(
                            'email' => '',
                            'name' => '',
                            'phone' => '',
                            'contact_message' => '',
                        );
                    }
                    else{
                        $this->data['message'] = 'sent fail';
                    }
                }
                
            }
        }

        $this->data['captcha'] = create_captcha($vals);
        //var_dump($this->data['captcha']['image']);exit;
        //if($this->session->userdata['captchaImage']){
        //    if(file_exists(BASEPATH."../themes/front/captcha/".$this->session->userdata['captchaImage']))
        //    unlink(BASEPATH."../themes/front/captcha/".$this->session->userdata['captchaImage']);
        //}
        
        $this->session->set_userdata('captchaWord',$this->data['captcha']['word']);
        $this->session->set_userdata('captchaImage',$this->data['captcha']['image']);
        
        $this->template->title('Contact Us') ;
        $this->_assetIndex();
        $this->render_page('index', $this->data);

    }

    public function validate_captcha(){
//        var_dump($this->input->post('captcha'));exit;
       
        if($this->input->post('captcha') != $this->session->userdata['captcha']['word'])
        {
            $this->form_validation->set_message('validate_captcha', 'Wrong captcha code');
            return false;
        }else{
            return true;
        }

    }
    private function _assetIndex(){
        $this->assets_js['page_plugin'] = array(
            'https://maps.googleapis.com/maps/api/js',
            'custom/map.js',
        );

        $this->js_domready = array(
            'Map.init();'
        );
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */