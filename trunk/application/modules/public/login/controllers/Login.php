<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends Front_Controller {

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
        $this->load->database();
        $this->load->helper(array('url','language','string'));
        $this->load->model(array('login_model', 'auth/ion_auth_model'));
        $this->load->library(array('ion_auth','form_validation'));
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

    }
    public function index()
    {
        //validate form input
        $this->form_validation->set_rules('identity', 'Identity', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if($this->form_validation->run() == true){
            //check to see if the user is logging in
            //check for "remember me"
            $remember = (bool) $this->input->post('remember');

            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember))
            {
                //if the login is successful
                //redirect them back to the home page
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect('home', 'refresh');
            }
            else
            {
                //if the login was un-successful
                //redirect them back to the login page
                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect('login', 'refresh'); //use redirects instead of loading views for compatibility with MY_Controller libraries
            }
        }
        else{
            //the user is not logging in so display the login page
            //set the flash data error message if there is one
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            $this->data['identity'] = array('name' => 'identity',
                'id' => 'identity',
                'type' => 'text',
                'value' => $this->form_validation->set_value('identity'),
            );
            $this->data['password'] = array('name' => 'password',
                'id' => 'password',
                'type' => 'password',
            );

            $this->_assetIndex();
            $this->render_page('index', $this->data);
        }
    }
    public function register(){
        $tables = $this->config->item('tables','ion_auth');

        //validate form input
        $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required');
        $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required');
        $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique['.$tables['users'].'.email]');
        $this->form_validation->set_rules('address', $this->lang->line('create_user_validation_address_label'), 'required');
        $this->form_validation->set_rules('password', $this->lang->line('create_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('create_user_validation_password_confirm_label'), 'required');

        if ($this->form_validation->run() == true)
        {
            $username = strtolower($this->input->post('first_name')) . ' ' . strtolower($this->input->post('last_name'));
            $email    = strtolower($this->input->post('email'));
            $password = $this->input->post('password');

            $additional_data = array(
                'first_name'    => $this->input->post('first_name'),
                'last_name'     => $this->input->post('last_name'),
                'address'       => $this->input->post('address'),
                'gender'        => $this->input->post('gender'),
                'dob'           => $this->input->post('dob'),
            );
            $group = array(2);
        }
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $group))
        {
            //check to see if we are creating the user
            //redirect them back to the admin page
            $this->session->set_flashdata('success', $this->ion_auth->messages());
            $this->data['success'] = $this->session->flashdata('success');
            $this->_assetIndex();
            $this->render_page('index', $this->data);
        }
        else
        {
            //display the create user form
            //set the flash ddata error message if there is one
            $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

            $this->data['first_name'] = array(
                'name'  => 'first_name',
                'id'    => 'first_name',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('first_name'),
            );
            $this->data['last_name'] = array(
                'name'  => 'last_name',
                'id'    => 'last_name',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('last_name'),
            );
            $this->data['email'] = array(
                'name'  => 'email',
                'id'    => 'email',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('email'),
            );
            $this->data['address'] = array(
                'name'  => 'address',
                'id'    => 'address',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('address'),
            );
            $this->data['password'] = array(
                'name'  => 'password',
                'id'    => 'password',
                'type'  => 'password',
                'value' => $this->form_validation->set_value('password'),
            );
            $this->data['password_confirm'] = array(
                'name'  => 'password_confirm',
                'id'    => 'password_confirm',
                'type'  => 'password',
                'value' => $this->form_validation->set_value('password_confirm'),
            );
            $this->_assetIndex();
            $this->render_page('register', $this->data);
        }
    }
    public function forgot_password(){
        $this->load->library('email');
        $this->load->helper('email');

        if($this->input->post()){
            $email  = $this->input->post('email');
           if($this->ion_auth_model->email_check($email)){
               $config = Array(
                   'protocol'   => $this->config->item('protocol'),
                   'smtp_host'  => $this->config->item('smtp_host'),
                   'smtp_port'  => $this->config->item('smtp_port'),
                   'smtp_user'  => $this->config->item('smtp_user'),
                   'smtp_pass'  => $this->config->item('smtp_pass'),
                   'mailtype'   => $this->config->item('mailtype'),
                   'charset'    => $this->config->item('charset'),
                   'wordwrap'   => $this->config->item('wordwrap')
               );
               $this->email->initialize($config);
               $this->email->set_newline($this->config->item('newline'));

               $new_password = random_string('alnum', 4);
               $this->email->from($this->config->item('smtp_user'), $this->config->item('mail_app_title'));
               $this->email->to($email);
               $this->email->subject('Forgot Password');
               $this->email->message('Your new password is: ' . $new_password);

               $user = $this->login_model->find_by('email', $email);

               if($this->email->send(FALSE)){
                   $this->ion_auth_model->update($user->id, array('password' => $new_password));
                   $this->data['success'] = 'success';
               }
               else{
                   $this->data['message'] =  show_error($this->email->print_debugger(array('headers')));
               }

           }
            else{

                $this->data['message'] = 'invalid email';
            }
        }
        $this->_assetIndex();
        $this->render_page('forgot_password', $this->data);
    }

    //log the user out
    public function logout(){
        //log the user out
        $logout = $this->ion_auth->logout();

        //redirect them to the login page
        $this->session->set_flashdata('message', $this->ion_auth->messages());
        redirect(site_url(), 'refresh');
    }

    private function _assetIndex(){
        $this->assets_css['page_style'] = array(

        );
        $this->assets_js['page_plugin'] = array(

        );

        $this->js_domready = array(

        );
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */