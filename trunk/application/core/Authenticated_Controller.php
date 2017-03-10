<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Authenticated Controller
 *
 * Provides a base class for all controllers that must check user login
 * status.
 *
 * @package    Bonfire\Core\Controllers
 * @category   Controllers
 * @author     Bonfire Dev Team
 * @link       http://guides.cibonfire.com/helpers/file_helpers.html
 *
 */
class Authenticated_Controller extends Base_Controller
{

	//--------------------------------------------------------------------

	/**
	 * Class constructor setup login restriction and load various libraries
	 *
	 */
	public function __construct()
	{		
		parent::__construct();
		// Load the Auth library before the parent constructor to ensure
		$this->load->database();
		$this->load->library(array('ion_auth','form_validation'));
		$this->load->helper(array('url','language'));
		
		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
		
		$this->lang->load('auth');
		
		if (!$this->ion_auth->logged_in())
		{					
			//redirect them to the login page
			redirect('auth/login', 'refresh');
		}
	}//end construct()

	//--------------------------------------------------------------------

}

/* End of file Authenticated_Controller.php */
/* Location: ./application/core/Authenticated_Controller.php */