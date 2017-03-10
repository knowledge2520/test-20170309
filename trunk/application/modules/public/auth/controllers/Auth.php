<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Auth extends Front_Controller{

    function __construct(){
        parent::__construct();
        $this->load->helper(array('url','language'));
        $this->load->model(array('ion_auth_model'));
        $this->load->library(array('form_validation','email','messages'));
        $this->lang->load('auth');
    }

    public function index(){
        redirect('home');
    }

    public function reset_password($forgotten_password_code = false){
        if($this->input->post()){
            $id = $this->input->post('userId');
            $this->_reset_password_complete($id);
        }
        else{
            $check          = $this->ion_auth_model->get_by('forgotten_password_code', $forgotten_password_code);
            $data['item']   = $check;
            if($check){
                $this->render_page('reset_password', $data);
            }
            else{
                redirect('home');
            }
        }

    }

    protected function _reset_password_complete($id){

        $this->form_validation->set_rules('newPassword', 'password', 'required');
        $this->form_validation->set_rules('confirmPassword', 'confirm password', 'required|matches[newPassword]');

        if($this->form_validation->run() == FALSE){
            $data['message']    = 'error';
            $data['item']       = $this->ion_auth_model->get_by('id', $id);
            $this->render_page('reset_password', $data);
        }
        else{
            $user           = $this->ion_auth_model->get_by('id', $id);
            $password       = $this->input->post('newPassword');
            $salt           = $user->salt;
            $password_encode = SHA1($password);

            $data   = array(
                'password'                  => SHA1($salt.$password_encode),
                'forgotten_password_code'   => '',
                'forgotten_password_time'   => '',
            );

            $this->ion_auth_model->updateData($id, $data);

            $this->render_page('reset_password_complete');
        }

    }
}