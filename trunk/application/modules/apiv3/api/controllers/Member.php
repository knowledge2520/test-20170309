<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/modules/api/api/libraries/REST_Controller.php';

/**
 * 
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @created_date: May 5, 2015
 */
class Member extends REST_Controller
{	
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
        
        //load model
        $this->load->model('member_model');
        $this->load->model('review_model');
		$this->load->model('usercontact_model');

        //load lang
        $this->lang->load('api');
        
        $this->load->library('parse');   
        $this->load->helper(array('form', 'url','util'));
        $this->parse->setDatabase($this->db);
        $this->load->helper('member');
        $this->load->library('image_lib');
		$this->load->library('friendnearby');
		$this->load->library('petreview');
		$this->load->library('petcheckin');
		$this->load->library('petmember');
		$this->load->library('petuserfriend');
    }
    
    function register_post()
    {       	
    	
    	$data['email']      	= $this->post('email') ? $this->post('email') : false;
    	$data['username']	= $data['email'];
    	$data['password']   	= $this->post('password') ? $this->post('password') : false;
    	$data['first_name']	= $this->post('first_name') ? $this->post('first_name') : false;
    	$data['last_name']  	= $this->post('last_name') ? $this->post('last_name') : false;
    	$data['dob']        	= $this->post('dob') ? strtotime($this->post('dob')) : false;
        $data['gender']		= $this->post('gender') !== FALSE &&  $this->post('gender') != NULL ? $this->post('gender') : -1;
    	$data['facebook_id']	= $this->post('facebook_id') ? $this->post('facebook_id') : NULL;
    	$data['profile_photo']	= $this->post('profile_photo') ? $this->post('profile_photo') : false;
        $device_token_firebase = $this->post('deviceTokenFireBase') ? $this->post('deviceTokenFireBase') : false;
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate

    	//$this->form_validation->set_rules('email', 'Email', 'trim|required|is_unique[users.email]',
// 		$this->form_validation->set_rules('email', 'Email', 'trim|required|is_unique[users.email]',
// 			array('is_unique' => lang('email_is_unique'))
// 		);
    	$this->form_validation->set_rules('email', 'Email', 'trim|required|callback_email_check',
        	array('is_unique' => lang('email_is_unique'))
        );
    	
    	if(!$data['facebook_id'])
    	{
    		//register normal    		
    		$this->form_validation->set_rules('password', 'Password', 'trim|required');    		
    		$this->form_validation->set_rules('first_name', 'First name', 'required');
    		$this->form_validation->set_rules('last_name', 'Last name', 'required');
    	}
    	else
    	{
    		$this->form_validation->set_rules('facebook_id', 'Facebook ID', 'is_unique[users.facebook_id]');
    	}
    	
    	if ($this->form_validation->run() === FALSE) {
    		$error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		
    		if(form_error('email'))
    		{
    			$error['msg'] = strip_tags(form_error('email'));
    		}
    		if(form_error('password'))
    		{
    			$error['msg'] = strip_tags(form_error('password'));
    		}
    		if(form_error('first_name'))
    		{
    			$error['msg'] = strip_tags(form_error('first_name'));
    		}
    		if(form_error('last_name'))
    		{
    			$error['msg'] = strip_tags(form_error('last_name'));
    		}
    		$this->response($error,200);
    	}
    	
    	//check member
    	$status = $this->member_model->checkMember($data['email'], false);
    	
    	if (!empty($status)) {
    		$error['code'] = self::ERROR_CODE_MEMBER_USERNAME_EMAIL_EXIST;
    		$error['msg']  = lang('email_is_unique');
    		$this->response($error, 200);
    	}
    	
    	//default data
    	//$data['active']             = 0;
    	$data['active']             = 1;
    	$data['activation_code']    = $this->member_model->_generateCode(12);;
    	$data['salt']               = $this->member_model->_generateCode();
    	$data['password']           = SHA1($data['salt'].$data['password']);
    	$data['created_on']         = now();
    	
    	//upload photo and overwrite profile photo
    	/*$photo = $this->_doUpload($this->config->item('member_path'));
    	if($photo)
    	{    		
    		//resize
    		$this->load->helper('image');
    		resizeImage($photo['full_path'],AVATAR_WIDTH,AVATAR_HEIGHT); 
    		   		
    		$data['profile_photo'] = $this->config->item('api_upload_path').$this->config->item('member_path').$photo['file_name'];
    		$file_name_array 				= explode('.', $photo['file_name']);
    		$data['profile_photo_thumb'] 	= $this->config->item('api_upload_path').$this->config->item('member_path').$file_name_array[0].'_thumb.'.$file_name_array[1];
    	}*/


		$photoProfile 		= $this->media_model->S3Upload( false, 'file');
		$photoBackground 	= $this->media_model->S3Upload( false, 'background');

		if($photoProfile) {
			$data['profile_photo'] 			= $photoProfile['uri'];
			$data['profile_photo_thumb'] 	= $photoProfile['uri_thumb'];
		}

		if($photoBackground) {
			$data['profile_background'] 		= $photoBackground['uri'];
			$data['profile_background_thumb'] 	= $photoBackground['uri_thumb'];
		}

    	//add user
        $data['display_name'] = $data['first_name'] . ' ' . $data['last_name'];
    	$userID = $this->member_model->insert($data);
    	
    	if ($userID) {
    		
    		$data['device_type']  = $this->post('DeviceType') ? $this->post('DeviceType') : false;
    		$data['device_token'] = $this->post('DeviceToken') ? $this->post('DeviceToken') : false;
    		
    		$logged_id = $this->member_model->add_member_logged($userID,$data['device_token'],$data['device_type']);
    		
    		//Update device token
    		if ($data['device_type']) {    			
    			$this->member_model->updateDeviceToken($data['device_token'], $data['device_type'], $userID,$device_token_firebase);
    		}
    		//Upload device token to parse
    		if($data['device_token'] && $data['device_type']){
    			$device_data = array('device_type'=> $data['device_type'], 'device_token'=> $data['device_token']);
    			$this->_checkParseUser($device_data, $data['email']); 			    
    		}//END user check parser
    		
    		//send active mail
    		//$send_mail_active = send_active_email($userID);
    		
    		
    		//reponse data
    		$reponse = array();
    		$member = $this->member_model->getMemberByMemberID($userID);
    		
    		//check user options
    		$user_options = $this->member_model->get_user_options($member->id);
    		if(empty($user_options)){
    			//add default user options    			
    			$this->member_model->add_user_options(array('user_id'=>$member->id));
    			$user_options = $this->member_model->get_user_options($member->id);
    		}

    		//overwrite member token
    		$logged_info = $this->member_model->get_member_logged($logged_id);
    		$member->token = $logged_info->token;
    		
    		$reponse['msg']						= 'Register successfully.';
    		$reponse['MemberInfo'] 				=  format_output_data($member);
    		$reponse['MemberInfo']->settings 	=  $user_options;

            // Send MailChimp subscribed
            $firstName          = ($data['first_name'] != false) ? $data['first_name'] : "";
            $lastName          = ($data['last_name'] != false) ? $data['last_name'] : "";
            UtilHelper::syncMailchimp($data['username'],$firstName,$lastName);
            // End

    		//send reponse data
    		$this->response($reponse,200);
    	}else {
            $error['code'] = $this::ERROR_CODE_MEMBER_ADD_NEW_WRONG;
            $error['msg']  = lang('Can not add new member');
            $this->response($error,200);
        }
    }
    public function login_post() {
    	$error = array();
    	
    	$email        = $this->post('email') ? $this->post('email') : false;
    	$password     = $this->post('password') ? $this->post('password') : false;
    	$device_token = $this->post('deviceToken') ? $this->post('deviceToken') : false;
    	$device_type  = $this->post('deviceType') ? $this->post('deviceType') : false;
    	$facebook_id  = $this->post('facebook_id') ? $this->post('facebook_id') : false;
    	
        $latitude = $this->post('latitude') && $this->post('latitude') != 0 ? $this->post('latitude') : false;
        $longitude = $this->post('longitude') && $this->post('longitude') != 0 ? $this->post('longitude') : false;
        $device_token_firebase = $this->post('deviceTokenFireBase') ? $this->post('deviceTokenFireBase') : false;
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
//     	$this->form_validation->set_rules('deviceToken', 'Device Token', 'required');
//     	$this->form_validation->set_rules('deviceType', 'Device Type', 'required');
//     	if ($this->form_validation->run() == FALSE) {
//     		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
//     		if(form_error('deviceToken'))
//     		{
//     			$error['msg'] = strip_tags(form_error('deviceToken'));
//     		}
//     		$this->response($error,200);
//     	}    
    	if(!$facebook_id) {	
    		// login normal    	
	    	if (!$email || !$password) {
	    		$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
	    		$error['msg']  = lang('Email and Password are require');
	    		$this->response($error, 200);
	    	}
	    	$salt = $this->member_model->getSaltByEmail($email);
	    	if (empty($salt)) {
	    		$error['code'] = $this::ERROR_CODE_MEMBER_PASSWORD_WRONG;
	    		$error['msg']  = lang('The Email supplied is invalid');
	    		$this->response($error, 200);
	    	}
	    	$password = SHA1($salt . $password);
	    	$check_member = $this->member_model->checkMember($email, false);
    	}
    	else 
    	{
    		//check facebook id
    		$check_member = $this->member_model->checkMember(false,$facebook_id);
    		if(!$check_member)
    		{
    			//login facebook
    			if($email)
    			{
    				//check email already exist
    				$check_member = $this->member_model->checkMember($email, false);
    				if($check_member)
    				{
    					if(empty($check_member->facebook_id)){
    						//update facebook id
    						$data['facebook_id'] = $facebook_id;
    						$this->member_model->update($data,$check_member->id);
    						$check_member = $this->member_model->checkMember($email, false);
    					}
    					else{
    						$error['code'] = $this::ERROR_CODE_MEMBER_USERNAME_EMAIL_EXIST;
    						$error['msg']  = lang('email_is_unique');
    						$this->response($error, 200);
    					}
    				}
    				else
    				{
    					//create new user with email vs facebook
    					$data_create = array(
    							'email' 		=> $email,
    							'facebook_id' 	=> $facebook_id,
    							'active'		=> 1,
    							'salt'			=> $this->member_model->_generateCode(),
    							'first_name'	=> $this->post('first_name') ? $this->post('first_name') : null,
    							'last_name'		=> $this->post('last_name') ? $this->post('last_name') : null,
    							'gender'		=> $this->post('gender') ? $this->post('gender') : 0,
    							'profile_photo' => $this->post('profile_photo') ? $this->post('profile_photo') : null,
    							'profile_photo_thumb' => $this->post('profile_photo') ? $this->post('profile_photo') : null,
    							'created_on'	=> now()
    					);
    					$member_id = $this->member_model->insert($data_create);
    					//overwrite $check_member
    					$check_member = $this->member_model->getMemberByMemberID($member_id);
    				}
    			}
    			else
    			{
    				$check_member = $this->member_model->checkMember(false, $facebook_id);
    				if(!$check_member)
    				{
    					$error['code'] = $this::ERROR_CODE_MEMBER_FACEBOOK_REQUIRE_EMAIL;
    					$error['msg']  = lang('Facebook ID not exist but require email');
    					$this->response($error, 200);
    				}
    			}
    		}
    	} 
    	
    	if($device_type){
    		//update device info
    		$deviceInfo = $this->member_model->updateDeviceToken($device_token, $device_type, $check_member->id,$device_token_firebase);
    		log_message('info','<!> Logged : '.$deviceInfo->user_id.' on '.$deviceInfo->device_type.'['.$deviceInfo->id.'] with token '.$deviceInfo->device_auth_token);
    	}  	
    	if($check_member)
    	{
    		// Check status
    		// * 1: Approve / 2: Pending / 3: Suspended / 4: Delete / 5: Mobile unverified / 6: Mobile verified
    		if($check_member->active == 0) {
    			$error['msg']  = lang('Your account need be verified by email');
    			$error['code']  = self::ERROR_CODE_USER_AUTH_REQUIRED;
    			$this->response($error, 200);
    		}
    		if($check_member->active == 3) {
    			$error['msg']  = lang('Your account is suspended');
    			$this->response($error, 200);
    		}
    		
    		if($check_member->active == 4) {
    			$error['msg']  = lang('Your account not found');
    			$this->response($error, 200);
    		}
    	}
    	
    	$member = $this->member_model->login($email, $password, $facebook_id, $check_member);
    	if (empty($member)) {
    		$error['code'] = $this::ERROR_CODE_MEMBER_PASSWORD_WRONG;
    		$error['msg']  = lang('The password supplied is invalid');
    		$this->response($error, 200);
    	}
    	else{    		
    		$logged_id = $this->member_model->add_member_logged($member->id,$device_token,$device_type);
    		
    		//overwrite member token
    		$logged_info = $this->member_model->get_member_logged($logged_id);
    		$member->token = $logged_info->token;
    	}
        //check user options	
        $user_options = $this->member_model->get_user_options($member->id);

		// Add User Location
		$this->friendnearby->setUserLocation( $member, $latitude, $longitude );

       	$user_options = $this->member_model->get_user_options($member->id);
        $user_options = format_output_data($user_options);
    	$data_reponse['MemberInfo'] = format_output_data($member);
    	$data_reponse['MemberInfo']->settings = $user_options;
    	
    	//get user score;
    	$data_reponse['MemberInfo']->score = $this->_get_user_score($member->id);
    	
        //get pet medication of user
        $data_reponse['MemberInfo']->medications = $this->_get_user_pets_medications($member->id);

    	// Get total friend requests of logged in user
		$data_reponse['MemberInfo']->totalFriendRequest = $this->member_model->getTotalFriendRequest($member->id);

		// Get total bookmarks of logged in user
		$data_reponse['MemberInfo']->totalBookmarks =  $this->member_model->getTotalBookmarks($member->id);

		// GET user status
		$userOpt = $this->member_model->getUserOptions($member->id);

		$data_reponse['userStatus'] = $userOpt["userStatus"];

		// GET user last know location
		$data_reponse['latitude'] = $userOpt["latitude"];
		$data_reponse['longitude'] = $userOpt["longitude"];

    	//check user paser
    	if($device_token && $device_type){
    		$device_data = array('device_token'=>$device_token,'device_type'=>$device_type);
    		$this->_checkParseUser($device_data, $member->email);
    	}
    	
    	$this->response($data_reponse,200);
    }
   function listingItems_post() {

        $this->_requireAuthToken();
        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;
        $sort_by = $this->post('sort_by') ? $this->post('sort_by') : 'first_name';
        $sort_value = $this->post('sort_value') ? $this->post('sort_value') : 'ASC';

        $data['items'] = $this->member_model->get_users('all', $start, $limit, $keyword, $sort_by, $sort_value);
        $data['totalItem'] = $this->member_model->get_users('count', $start, $limit, $keyword, $sort_by, $sort_value);
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);

        if (!empty($data['items'])) {
            foreach ($data['items'] as $key => $user) {
                $user_detail = $this->member_model->getMemberByMemberID($user->id , true , true , true , true, $this->_member->id);
                if(!empty($user_detail))
                {
                    $user_detail = format_output_data($user_detail);
                    
                    //setup output data
                    unset($user_detail->token);
                    unset($user_detail->activation_code);
                    unset($user_detail->last_login);
                    unset($user_detail->friends);

                    //get user photo


                    //get user score;
                    $user_detail->score = $this->_get_user_score($user->id);  
                        
                    //check user friend
                    $user_detail->friend_status = $this->member_model->check_user_friend_status($user->id,  $this->_member->id);    
                }       

                $data['items'][$key] = $user_detail;
            }
        }
        $this->response($data, 200);
    }
    function updateProfile_post()
    {
    	$this->_requireAuthToken();
    	
//     	$data['email']      	= $this->post('email') ? $this->post('email') : false;    	    	
    	$data['first_name']	 	= $this->post('first_name') ? $this->post('first_name') : false;
    	$data['last_name']  	= $this->post('last_name') ? $this->post('last_name') : false;
    	$data['dob']        	= $this->post('dob') ? strtotime($this->post('dob')) : false;
        $data['gender']		= $this->post('gender') !== FALSE &&  $this->post('gender') != NULL ? $this->post('gender') : -1;
    	//$data['profile_photo']	= $this->post('profile_photo') ? $this->post('profile_photo') : false;
    	 
    	$this->load->helper(array('form', 'url'));
    	$this->load->library('form_validation');
    	 
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
//     	if(empty($this->_member->email) )
//     	{  	
//     		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]',
//     				array('is_unique' => lang('email_is_unique'))
//     		);
//     	}
//     	else
//     	{
//     		//not allow for update email
//     		unset($data['email']);
//     	}
    	$this->form_validation->set_rules('token', 'Member token', 'required');    	 
    	if ($this->form_validation->run() == FALSE) {
    		$error_list = $this->form_validation->error_array();
    		
    		$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
//     		if(form_error('email'))
//     		{
//     			$error['msg'] = strip_tags(form_error('email'));
//     		}
    		if(form_error('token'))
    		{
    			$error['msg'] = strip_tags(form_error('token'));
    		}
    		$this->response($error,200);
    	}
        $data['display_name'] = $data['first_name'] . ' ' . $data['last_name'];
    	$this->petmember->updateMemberProfile( $data, $this->_member );

    	$response['msg'] = "Update successful";
    	$this->response($response,200);
    }
    public function updateMemberPhoto_post()
    {
    	$this->_requireAuthToken();

		$user = $this->petmember->updateMemberProfile( false, $this->_member );

		$user = format_output_data($user);

		$this->response($user, 200);
    }
    function logout_post()
    {
    	$this->_requireAuthToken();
    	
    	$device_token = $this->post('deviceToken') ? $this->post('deviceToken') : false;
    	$device_type  = $this->post('deviceType') ? $this->post('deviceType') : false;
    	$device_token_firebase = $this->post('deviceTokenFireBase') ? $this->post('deviceTokenFireBase') : false;
    	$member_token = $this->post('token');
    	
    	$this->member_model->logout($this->_member->id, $device_token, $device_type,$member_token,$device_token_firebase);
    	$data['msg'] = "Logout successful";
    	$this->response($data,200);
    }
    public function forgotPassword_post() {
    	$error = array();
    	    
    	$email	= $this->post('email') ? $this->post('email') : false;
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		$error['msg'] 	= strip_tags(form_error('email'));
    		$this->response($error,200);
    	}
    	$member = $this->member_model->checkMember($email);
    	if(!$member)	{
    		$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
    		$error['msg'] 	= lang('System will prompt to use that email is not recognized as text below the input box');
    		$this->response($error,200);
    	}
    	$status = $this->member_model->forgotPassword($member->id, $member->email); 
    	if($status)
    	{
    		$response['msg'] = 'Please check in your mail box';
    	} 
    	else
    	{
    		$response['msg'] = 'Reset your password fail';
    		$response['code'] = self::ERROR_CODE_USER_INVALID;
    	}  	    	
    	$this->response($response,200);
    }
    public function changePassword_post() {
    	$this->_requireAuthToken();
    		
    	$new_password	= $this->post('new_password') ? $this->post('new_password') : false;
    	$old_password	= $this->post('old_password') ? $this->post('old_password') : false;
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('new_password', 'New Password', 'trim|required');
    	$this->form_validation->set_rules('old_password', 'Current Password', 'trim|required');
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		$error['msg'] 	= strip_tags(form_error('new_password'));
    		$this->response($error,200);
    	}
    	$salt = $this->member_model->getSaltByEmail($this->_member->email);    	
    	$old_password = SHA1($salt.$old_password);
    	$new_password = SHA1($salt.$new_password);
    	if($old_password !== $this->_member->password){
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		$error['msg'] 	= lang('password_not_match_with_current_password');
    		$this->response($error,200);
    	}
    	if($old_password === $new_password){
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		$error['msg'] 	= lang('new_password_equal_current_password');
    		$this->response($error,200);
    	}
    	if($this->member_model->update(array('password'=>$new_password), $this->_member->id)){    		
    		$response['msg'] = 'Congratulations! Your password has been changed.';
    		
    		//send mail
    		$this->load->helper('member');
    		$send_mail_change = send_new_password_email($this->_member->id,$new_password);
    		
    		//response
    		$this->response($response,200);
    		
    	}
    	$error['code'] 	= self::ERROR_OBJECT_NOT_FOUND;
    	$error['msg'] 	= lang('change_password_failure');
    	$this->response($error,200);
    }
    
    private function _checkParseUser($device_data , $email)
    {
    	$memberParseCheck = $this->member_model->checkParseMember ($email);
    	
    	if (! $memberParseCheck) {    	
    		// upload member to parse
    		$userObj = $this->parse->ParseUser ();
    		$insertParseUser = $userObj->signup ( $email, uniqid(), $email );
    	}
    	else{
    		$insertParseUser = $memberParseCheck;
    	}
    	// upload installation object    	
    	
    	$device_token 	= $device_data['device_token'];
    	$device_type 	= $device_data['device_type'];
    	
    	// find if existinstallation
    	$installObj = $this->member_model->findInstallObject($insertParseUser->objectId,$device_token);
    	if(empty($installObj)){
    		$installObj = $this->parse->ParseObject ( '_Installation' );
    		
    		$appIdentifier = strtolower ( $device_type) == 'ios' ? $this->config_items->appIdentifier_ios : $this->config_items->appIdentifier_android;
    		$appName = strtolower ( $device_type ) == 'ios' ? $this->config->item ( 'appName_ios' ) : $this->config->item ( 'appName_android' );
    	
    		$installData = array ();
    		$installData ['timeZone'] = 'Asia/Singapore';
    		$installData['deviceType'] = strtolower($device_type);
    		$installData['deviceToken'] = $device_token ;
    		$pushType = ( strtolower($device_type) == 'android') ? 'gcm' : null;
    	
    		if($pushType){
    			$installData ['pushType'] = $pushType;
    		}
    	
    		$installData ['appName'] 		= $appName;
    		$installData ['MemberObjectId'] = $insertParseUser->objectId;
    		$installData ['appIdentifier'] 	= $appIdentifier;
    	
    		$installObj->data = $installData;
    		if ($installObj->save ()) {
    				// log member save parse
    		}
    	}
    }
    
    public function addUserCheckin_post()
    {
    	$this->_requireAuthToken();
    	
    	$data['user_id'] 		= $this->_member->id;
    	$data['business_id'] 	= $this->post('business_id') ? $this->post('business_id') : false;
    	$data['comment'] 		= $this->post('comment') ? $this->post('comment') : null;
    	$data['created_date'] 	= now();
		$userTag                = $this->post('tags') ? $this->post('tags') : "";
    	
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('business_id', 'Listing', 'required|callback__listing_check');
    	 
    	if ($this->form_validation->run() == FALSE) {    		
    		$error['code'] 	= $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		$error['msg']	= strip_tags(form_error('business_id'));
    		$this->response($error,200);
    	}

		$response[ITEM] = $this->petcheckin->saveNew( $data, $this->_member, $userTag );

    	$this->response($response, 200);
    	
    }
    public function addUserReview_post()
    {
    	$this->_requireAuthToken();

    	$data['user_id'] 		= $this->_member->id;
    	$data['business_id'] 	= $this->post('business_id') ? $this->post('business_id') : false;
    	$data['content'] 		= $this->post('content') ? $this->post('content') : null;
    	$data['rate'] 			= $this->post('rate') ? $this->post('rate') : false;
    	$data['created_date'] 	= now();
    	$data['status'] 		= 1;//active
		$userTag                = $this->post('tags') ? $this->post('tags') : "";
    	 
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('business_id', 'Listing', 'required|callback__listing_check');
    	$this->form_validation->set_rules('rate', 'Rate', 'numeric');
    
    	if ($this->form_validation->run() == FALSE) {
    		if(form_error('business_id'))
    		{
    			$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    			$error['msg']	= strip_tags(form_error('business_id'));
    			$this->response($error,200);
    		}
    		if(form_error('rate'))
    		{
    			$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    			$error['msg']	= strip_tags(form_error('rate'));
    			$this->response($error,200);
    		}    		
    	}

		$response[ITEM] = $this->petreview->saveNew( $data, $this->_member, $userTag );

    	$this->response($response,200);
    }
    public function addUserTip_post()
    {
    	$this->_requireAuthToken();
    	 
    	$this->load->helper('image');
    	$this->load->library('image_lib');
    
    	$data['user_id'] 		= $this->_member->id;
    	$data['business_id'] 	= $this->post('business_id') ? $this->post('business_id') : false;
    	$data['description'] 	= $this->post('description') ? $this->post('description') : null;
    	$data['created_date'] 	= now();
    	$data['status'] 		= 1;//active for testing (need change to 0) 
    
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('business_id', 'Listing', 'required|callback__listing_check');
    	$this->form_validation->set_rules('rate', 'Rate', 'numeric');
    
    	if ($this->form_validation->run() == FALSE) {
    		if(form_error('business_id'))
    		{
    			$error['code'] 	= $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    			$error['msg']	= strip_tags(form_error('business_id'));
    			$this->response($error,200);
    		}
    		if(form_error('description'))
    		{
    			$error['code'] 	= $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    			$error['msg']	= strip_tags(form_error('description'));
    			$this->response($error,200);
    		}
    	}
    	     	
    	//upload photo
    	$media_files = $this->_doMultiUpload($this->config->item('listings_path'));
    	 
    	//add user tip
    	$tip_id = $this->member_model->add_user_tip_listing($data);
    	 
    	//save media to db
    	if($media_files && $tip_id)
    	{
	    	$data_insert = array();	  
	    	$album_id = $tip_id.random_string('numeric',4);
	    	$newConfig = 'config';
	    	$i = 0;
	    	$j = 1;
	    	foreach($media_files as $file)
	    	{
		    	$file_array 	= array();
		    	$media_insert 	= array();
		    	 
		    	$file_array = $file['upload_data'];
		    	 
		    	$media_insert['tip_id']		= $tip_id;
		    	$media_insert['user_id']	= $this->_member->id;
		    	$media_insert['business_id']    = $data['business_id'];
		    	$media_insert['source'] 	= $this->config->item('api_upload_path').$this->config->item('listings_path').$file_array['file_name'];
		    	$media_insert['created_date']	= now();;
                        if(empty($file_array['image_type']) )
		    	{
		    		//video
		    		$media_insert['type'] = 'VIDEO';
		    		$media_insert['photo_thumb'] = null;
		    	}
		    	else
		    	{
		    		$file_name_array = array();
		    		$file_name_array 				= explode('.', $file_array['file_name']);
		    		$photo_thumb_name				= $this->config->item('api_upload_path').$this->config->item('listings_path').$file_name_array[0].'_thumb.'.$file_name_array[1];
		    				    
			    	//resize
			    	resizeImage($file_array['full_path'],IMAGE_RESIZE_WIDTH,IMAGE_RESIZE_HEIGHT);
		    		
			    	$media_insert['photo_thumb'] 	= $photo_thumb_name;
			    	$media_insert['album_id']		= $album_id;
			    	$media_insert['type'] 			= 'PHOTO';
		    	}
		    	array_push($data_insert, $media_insert);
		    }
    		//insert
    		insert_user_media($data_insert);
    	}
    	$this->load->model('tip_model');
    	$tips = $this->tip_model->get_tips_by_listing('all', 0,API_NUM_RECORD_PER_PAGE, $data['business_id'],'id','DESC');
    	
    	$response = array(
    			'msg'	=> lang('Add user tip successful'),
    			'items'	=> $tips
    	);
    	$this->response($response,200);    
    }
    function _listing_check($listing_id)
    {
    	$this->load->model('listing_model');
    	$item = $this->listing_model->get_listing_detail($listing_id);
    	if(!$item)
    	{
    		$this->form_validation->set_message('_listing_check', 'The {field} item does not exits');
    		return false;
    	}
    	return true;
    }
    
    private function _doUpload($folder_path)
    {
    	if(!empty($_FILES))
    	{    
    		$this->load->helper('string');
	    	foreach ($_FILES as $key => $file) {
	    		if( (!empty($file) && $file['error'] == 0) && ($key == 'file' || $key = 'background'))
	    		{
	    			$file_name 			= basename($file['name']);
	    			$ext 				= substr($file_name, strrpos($file_name, '.') + 1);
	    			$custom_filename 	= strtolower(random_string('alnum',20)."_".$key.".".$ext);

	    			$config['upload_path']          = $this->config->item('api_upload_path').$folder_path;
	    			$config['allowed_types']        = 'jpg|png|jpeg';
	    			$config['file_name']			= $custom_filename;
					$config ['encrypt_name']    	= TRUE;
// 	    			$config['max_size']             = 5000;
// 	    			$config['max_width']            = 1024;
// 	    			$config['max_height']           = 768;

	    			$this->load->library('upload', $config);
					$this->upload->initialize($config);

	    			if( ! $this->upload->do_upload($key))
	    			{
	    				$upload_errors = array('error' => $this->upload->display_errors());

	    				$error['code'] = self::ERROR_CODE_UPLOAD_IMAGE_FAIL;
	    				$error['msg'] = json_encode($upload_errors); //lang('Error: A problem occurred during file upload!');
		    			$this->response($error,200);
		    			return false;
	    			}
	    			else
	    			{
	    				$data = array('upload_data' => $this->upload->data());
	    				return $data['upload_data'];
	    			}
	    		}
	    		else
	    		{
	    			$error['code'] = self::ERROR_CODE_FILE_ERROR;
	    			$error['msg']  = lang('File error or not allow');
	    			$this->response($error,200);
	    			return false;
	    		}
	        }
    	}
    	return false;    	
    }
    private function _doMultiUpload($folder_path)
    {
	    $files = $_FILES;
	    $this->load->library('upload');
	    $this->load->helper('string');
	    
	    // upload an image options
	    $config = array ();
	    $config ['upload_path'] = $this->config->item('api_upload_path').$folder_path;
	    $config ['allowed_types'] = allow_file_upload('review');
	    $config ['encrypt_name'] = TRUE;
	
	    if(!empty($_FILES))
	    {
	    	$data = array();
		    $cpt = count ( $_FILES ['file'] ['name'] );
		    for($i = 0; $i < $cpt; $i ++) {
		
		        $_FILES ['file'] ['name'] = $files ['file'] ['name'] [$i];
		        $_FILES ['file'] ['type'] = $files ['file'] ['type'] [$i];
		        $_FILES ['file'] ['tmp_name'] = $files ['file'] ['tmp_name'] [$i];
		        $_FILES ['file'] ['error'] = $files ['file'] ['error'] [$i];
		        $_FILES ['file'] ['size'] = $files ['file'] ['size'] [$i];
		        
		        
		        $file_name 			= basename($_FILES ['file'] ['name']);
		        $ext 				= substr($file_name, strrpos($file_name, '.') + 1);
		        $custom_filename 	= strtolower(random_string('alnum',20)."_file.".$ext);
		        	
		        $config['file_name']			= $custom_filename;
		
		        $this->upload->initialize ( $config );
		        if ( ! $this->upload->do_upload('file'))
		        {
		        	$upload_errors = array('error' => $this->upload->display_errors());
		        
		        	$error['code'] = self::ERROR_CODE_UPLOAD_IMAGE_FAIL;
		        	$error['msg'] = lang('Error: A problem occurred during file upload!');
		        	$this->response($upload_errors,200);
		        }
		        else
		        {
		        	$data[$i] = array('upload_data' => $this->upload->data());		        	
		        }
		    }
		    return $data;
	    }
    }
    public function userDetail_post()
    {
    	$this->_requireAuthToken();
    	
    	$user_id = $this->_member->id;
    	
    	$user = $this->member_model->getMemberByMemberID($user_id , true , true , true , true, $this->_member->id);
    	if(!empty($user))
    	{
    		$user = format_output_data($user);
    		//$response['item'] = $user;
    		//check user options
    		$user_options = $this->member_model->get_user_options($user_id);
    		$user_options = format_output_data($user_options);
    		$user->setting = $user_options;
    		
    		//get user score;
    		$user->score = $this->_get_user_score($user_id);  

                //get pet medication of user
            $user->medications = $this->_get_user_pets_medications($user_id);

			$user->totalFriendRequest = $this->member_model->getTotalFriendRequest($user_id);

			$user->totalBookmarks =  $this->member_model->getTotalBookmarks($user_id);

			$userOpt = $this->member_model->getUserOptions($user_id);
			// GET user status
			$user->userStatus = $userOpt["userStatus"];

			// GET user last know location
			$user->latitude = $userOpt[LATITUDE];
			$user->longitude = $userOpt[LONGITUDE];

			$this->response($user, 200);
    	}    	
    	
    	
    	$error['msg'] = lang('Your account not found');
    	$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
    	$this->response($error,200);
    }
    public function userReviews_post()
    {
    	$this->_requireAuthToken();
    	
    	$this->load->model('review_model');
    	
    	$start = $this->post('start') ? $this->post('start') : 0;
    	$limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
    	$keyword = $this->post('keyword') ? $this->post('keyword') : false;
    	$user_id = $this->_member->id;
    	 
    	$data['items'] 		= $this->review_model->get_reviews_by_user('all', $start, $limit, $user_id , 'id', 'DESC',$keyword);
    	$data['totalItem'] 	= $this->review_model->get_reviews_by_user('count',$start, $limit, $user_id,'id', 'DESC',$keyword);
    	$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
    	$data['limit']		= intval($limit);
    	
    	if($data['items'])
    	{
    		$this->load->model('listing_model');
    		foreach($data['items'] as $key => $review)
    		{
    			$business_info = $this->listing_model->get_listing_detail($review->business_id , true , true , true);
    			$data['items'][$key]->business_info = $business_info;
    			
    			//check like
    			$like_status = $this->member_model->check_user_like_review($user_id,$review->id);
    			if($like_status){
    				$review->like_type = strval($like_status->type);
    			}
    			else{
    				$review->like_type = '';
    			}
    			
    			//format data output
    			$data['items'][$key] = format_output_data($review);
    		}
    		
    	}
    	
    	$this->response($data,200);
    }
    /*public function userPhotos_post()
    {
    	$this->_requireAuthToken();
    	
    	$start 		= $this->post('start') ? $this->post('start') : 0;
    	$limit 		= $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
//     	$keyword 	= $this->post('keyword') ? $this->post('keyword') : false;
    	$business_id    = $this->post('listing_id') ? $this->post('listing_id') : 0;
    	$user_id 	= $this->post('user_id')? $this->post('user_id') : $this->_member->id;
    	
    	$data['items'] 		= $this->member_model->get_user_photos('all', $start, $limit,$business_id,$user_id , 'id', 'DESC');
    	$data['totalItem'] 	= $this->member_model->get_user_photos('count',$start, $limit,$business_id,$user_id);
    	$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
    	$data['limit']		= intval($limit);
    	
    	$this->load->library('form_validation');

    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('user_id', 'User ID', 'required');
    	 
    	if ($this->form_validation->run() == FALSE) {
    		if(form_error('user_id'))
    		{
    			$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    			$error['msg'] = strip_tags(form_error('user_id'));
    			$this->response($error,200);
    		}
    	}
    	
    	if($data['items'])
    	{
    		$this->load->model('listing_model');
    		foreach($data['items'] as $key => $listing)
    		{    
    			$data['items'][$key] = format_output_data($listing);
    			$data['items'][$key]->media = array();
    			$media = $this->listing_model->get_media_by_listing('all',0,API_NUM_RECORD_PER_PAGE,$listing->business_id,'id','DESC',$user_id);
    			if($media)
    			{
    				foreach($media as $mkey => $m)
    				{
    					//get total like media
    					$total_like = $this->member_model->get_media_likes($m->id);
    					$m->total_like = count($total_like);
    					
    					//get user like media
    					$like_type = $this->member_model->check_user_like_media($user_id,$m->id);
    					if($like_type){
    						$m->like_type = strval($like_type->type);
    					}
    					else
    					{
    						$m->like_type = '';
    					}
    					
    					$media[$mkey] = format_output_data($m);
    				}
    				$data['items'][$key]->media = $media;			
    			}
    			//get user info
    			$user_info = $this->member_model->getMemberByMemberID($user_id,true,true,true,true,$this->_member->id);
    			$data['items'][$key]->user_info = format_output_data($user_info);
    		}
    	
    	}    	
    	$this->response($data,200);
    }*/

	public function userPhotos_post() {
		$this->_requireAuthToken();

		$start 			= $this->post('start') ? $this->post('start') : 0;
		$limit 			= $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
		$businessId    	= $this->post('listing_id') ? $this->post('listing_id') : 0;
		$userId 		= $this->post('user_id') ? $this->post('user_id') : $this->_member->id;

		$response		= $this->petmember->getUserPhotos( $start, $limit, $userId );

		$this->response($response, 200);
	}

    public function userTips_post()
    {
    	$this->_requireAuthToken();

    	$start 		= $this->post('start') ? $this->post('start') : 0;
    	$limit 		= $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
    	//     	$keyword 	= $this->post('keyword') ? $this->post('keyword') : false;
    	$business_id= $this->post('listing_id') ? $this->post('listing_id') : 0;
    	$user_id 	= $this->_member->id;
    	 
    	$data['items'] 		= $this->member_model->get_user_tips('all', $start, $limit,$business_id,$user_id , false ,'id', 'DESC');
    	$data['totalItem'] 	= $this->member_model->get_user_tips('count',$start, $limit,$business_id,$user_id);
    	$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
    	$data['limit']		= intval($limit);
    	
    	if($data['items'])
    	{
    		$this->load->model('tip_model');
    		$this->load->model('listing_model');
    		foreach($data['items'] as $key => $tip)
    		{    			
    			$data['items'][$key]->media = array();
    			$media = $this->tip_model->get_media_from_tips('all',0,API_NUM_RECORD_PER_PAGE,$tip->id);
    			if($media)
    			{
    				foreach($media as $mkey => $m)
    				{
    					$media[$mkey] = format_output_data($m);
    				}
    				$data['items'][$key]->media = $media;
    			}
    			
    			//business info
    			$listing_detail = $this->listing_model->get_listing_detail($tip->business_id);
    			$tip->business_info = $listing_detail;
    			
    			$data['items'][$key] = format_output_data($tip);
    		}    		
    	}
    	$this->response($data,200);
    }
    public function userBookMarks_post()
    {
    	$this->_requireAuthToken();
    	
    	$start 		= $this->post('start') ? $this->post('start') : 0;
    	$limit 		= $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
    	//     	$keyword 	= $this->post('keyword') ? $this->post('keyword') : false;
    	$business_id= $this->post('listing_id') ? $this->post('listing_id') : 0;
    	$user_id 	= $this->post('user_id') ? $this->post('user_id') : $this->_member->id;
    	
    	$data['items'] 		= $this->member_model->get_user_bookmarks('all', $start, $limit,$user_id , 'id', 'DESC');
    	$data['totalItem'] 	= $this->member_model->get_user_bookmarks('count',$start, $limit,$user_id);
    	$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
    	$data['limit']		= intval($limit);
    	 
    	if($data['items'])
    	{
    		$this->load->model('listing_model');
    		foreach($data['items'] as $key => $bm)
    		{
    			$data['items'][$key]->business_info = array();
    			$listing_detail = $this->listing_model->get_listing_detail($bm->business_id,true,false,true,true,false,$user_id);
    			$data['items'][$key]->business_info = $listing_detail;        			
    		}
    	}
    	$this->response($data,200);
    }
    public function userMessages_post()
    {
    	$this->_requireAuthToken();
    	
    	$data 		= array();
    	$start 		= $this->post('start') ? $this->post('start') : 0;
    	$limit 		= $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
    	//     	$keyword 	= $this->post('keyword') ? $this->post('keyword') : false;
    	$user_id 	= $this->_member->id;
    	 
    	$items 			= $this->member_model->get_user_messages('all', $start, $limit,$user_id , false ,'id', 'DESC');
    	$data['totalItem'] 	= $this->member_model->get_user_messages('count',$start, $limit,$user_id);
    	$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
    	$data['limit']		= intval($limit);
    	
    	if(!empty($items) ){
    		$data['items'] = $items;
    	}
    	else
    	{
    		$data['items'] = array();
    	}
    	
    	$this->response($data,200);
    }
    public function userCheckins_post()
    {
    	$this->_requireAuthToken();

    	$this->load->model('listing_model');
    	
    	$data 		= array();
    	$start 		= $this->post('start') ? $this->post('start') : 0;
    	$limit 		= $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
    	//     	$keyword 	= $this->post('keyword') ? $this->post('keyword') : false;
    	$user_id 	= $this->_member->id;
    
    	$items 		= $this->member_model->get_user_checkins('all', $start, $limit,$user_id , false ,'id', 'DESC');
    	$data['totalItem'] 	= $this->member_model->get_user_checkins('count',$start, $limit,$user_id);
    	$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
    	$data['limit']		= intval($limit);
    	
    	if($items)
    	{
    		foreach($items as $key => $item)
    		{
    			$business_item = $this->listing_model->get_listing_detail($item->business_id,true,false,true,true,false);
    			$item->business_info = $business_item;
    			$data['items'][$key] = format_output_data($item);
    		}
    	}
    	 
    	$this->response($data,200);
    }
    public function userMessageDetail_post()
    {
    	$this->_requireAuthToken();
    	$data 		= array();
    	$start 		= $this->post('start') ? $this->post('start') : 0;
    	$limit 		= $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
    	$message_id = $this->post('id') ? $this->post('id') : 0;
    	$user_id 	= $this->_member->id;
        
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('id', 'Message ID', 'required');
    	
    	if ($this->form_validation->run() == FALSE) {
    		if(form_error('id'))
    		{
    			$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    			$error['msg']	= strip_tags(form_error('id'));    			
    		}
    		$this->response($error,200);
    	}
    	
    	$msg = $this->member_model->get_message_by_id($message_id);
        $recievedId = $user_id != $msg->senderID ? $msg->senderID : $msg->recievedID;
    	if(!empty($msg) )
    	{
    		$result                 = $this->member_model->get_messages_chanel('all',$start,$limit,$user_id,$recievedId,'id','DESC', true);
    		$data['totalItem']      = $this->member_model->get_messages_chanel('count',$start,$limit,$user_id,$recievedId,'id','DESC', true);    		
    		$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
    		$data['limit']		= intval($limit);
    		if(!empty($result) )
    		{
    			foreach($result as $key => $row)
    			{
                                $row->media = array();
                                $medias = $this->member_model->get_media_message('all', 0, API_NUM_RECORD_PER_PAGE, 'id', 'DESC', $row->id, 1);
                                if ($medias) {
                                    foreach ($medias as $mkey => $media) {
                                        $row->media[$mkey] = format_output_data($media);
                                    }
                                }
                                
                                $data['items'][$key] = format_output_data($row);
    			}
    			$this->response($data,200);
    		}
    	}
    	$error['code'] 	= self::ERROR_CODE_ITEM_NOT_EXIST;
    	$error['msg']	= lang('Item not found');
    	$this->response($error,200);
    	
    }
    public function userSendMessage_post()
    {
    	$this->_requireAuthToken();
    	$this->load->helper('date');
    	
    	$data['recievedID']         = $this->post('recieved_id')  ? $this->post('recieved_id') : false;
    	$data['content']            = $this->post('content')  ? $this->post('content') : false;
    	$user_id                    = $this->_member->id;
    	$data['senderID']           = $user_id;
    	$data['created_date']       = now();
    	
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('recieved_id', 'Accepter ID', 'required');
    	$this->form_validation->set_rules('content', 'Content', 'required');
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		if(form_error('recieved_id'))
    		{    			
    			$error['msg']	= strip_tags(form_error('recieved_id'));
    		}
    		if(form_error('content'))
    		{
    			$error['msg']	= strip_tags(form_error('content'));
    		}
    		$this->response($error,200);
    	}
    	$check_channel = $this->member_model->get_messages_chanel('all',0,API_NUM_RECORD_PER_PAGE,$user_id,$data['recievedID'],'id','DESC', true);
    	
        if(!empty($check_channel))
    	{
    		foreach($check_channel as $msg_row)
    		{
    			if($msg_row->channel == 0)
    			{
    				$data['channel'] = $msg_row->id;
    				$message_id = $msg_row->id;
    			}
                        else{
                            $data['channel'] = $msg_row->channel;
                        }
    		}    		
    	}   
    	else 
    	{
    		$data['channel'] = 0;
    	}	    	
    	$insert_id = $this->member_model->add_user_message($data); 
    	
        if ($insert_id) {
            //upload photo and overwrite profile photo
            $media_files = $this->_doMultiUpload($this->config->item('member_path'));
            //save media to db
            if ($media_files && $insert_id) {
                $data_insert = array();


                foreach ($media_files as $file) {
                    $file_array = array();
                    $media_insert = array();

                    $file_array = $file['upload_data'];

                    $media_insert['message_id'] = $insert_id;
                    $media_insert['source'] = $this->config->item('api_upload_path') . $this->config->item('member_path') . $file_array['file_name'];
                    $media_insert['created_date'] = now();
                    $media_insert['status'] = 1;
                    $media_insert['user_id'] = $this->_member->id;
                    if (empty($file_array['image_type'])) {
                        //video
                        $media_insert['type'] = 'VIDEO';
                        $media_insert['photo_thumb'] = null;
                    } else {
                        $media_insert['type'] = 'PHOTO';

                        //resize
                        $this->load->helper('image');
                        resizeImage($file_array['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                        $file_name_array = explode('.', $file_array['file_name']);
                        $media_insert['photo_thumb'] = $this->config->item('api_upload_path') . $this->config->item('member_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];
                    }
                    array_push($data_insert, $media_insert);
                }
                //insert
                insert_user_media($data_insert);
            }

            $response['msg'] = lang('Add new successful');
            $response['item'] = array();
        }
        else {
            $error['msg'] = lang('Item not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error, 200);
        }
        
        
        
    	$result = $this->member_model->get_messages_chanel('all',0,API_NUM_RECORD_PER_PAGE,$user_id,$data['recievedID'],'id','DESC', true);
        if(!empty($result) )
        {
            foreach($result as $key => $row)
            {
                    $row->media = array();
                    $medias = $this->member_model->get_media_message('all', 0, API_NUM_RECORD_PER_PAGE, 'id', 'DESC', $row->id, 1);
                    if ($medias) {
                        foreach ($medias as $mkey => $media) {
                            $row->media[$mkey] = format_output_data($media);
                        }
                    }
                    $result[$key] = format_output_data($row);
            }	    	
        } 

        $response['msg'] = 'Send successful';
        $response['items'] = $result;
	    
    	$this->response($response,200);
    }
    public function userImportContact_post()
    {
    	$this->_requireAuthToken();
    	
    	$contact = $this->post('items') ? $this->post('items') : false;
    	$user_id = $this->_member->id;

    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('items[]', 'Contact item', '"trim|xss_clean');
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		if(form_error('items[]'))
    		{
    			$error['msg']	= strip_tags(form_error('items[]'));
    		}
    		$this->response($error,200);
    	}
    	
    	$response['msg'] = '';
    	$response['status'] = false;
    	$response['items'] = array();
    	$user = $this->member_model->getMemberByMemberID($user_id);
    	$data_import = array();
    	$data = array();
    	
        if(!empty($contact))
    	{
    		
    		$i=0;
    		//update registed contact list
    		$this->member_model->update_contact_friends_registed($user_id);
    		
    		foreach ($contact as $friend)
    		{
    			if(is_array($friend))
    			{
    				$friend_data['user_id'] 		= $user_id;
    				$friend_data['email'] 			= isset($friend['email']) && !empty($friend['email']) ? $friend['email'] : '';
    				$friend_data['first_name'] 		= isset($friend['first_name']) && !empty($friend['first_name']) ? $friend['first_name'] : '';
    				$friend_data['last_name'] 		= isset($friend['last_name']) && !empty($friend['last_name']) ? $friend['last_name'] : '';
    				$friend_data['phone'] 			= isset($friend['phone']) && !empty($friend['phone']) ? $friend['phone'] : '';
    				$friend_data['registed']		= 0;
    				$friend_data['status']			= 0;
    				$friend_data['social_type'] 	= isset($friend['social_type']) && !empty($friend['social_type']) ? $friend['social_type'] : 0; //0 user contact ; 1 facebook , 2 instaram
					
    				if($friend_data['social_type'] == 1)
    				{
    					if(isset($friend['facebook_id']) )
    					{
    						$facebookIDExist = $this->member_model->checkMember(false,$friend['facebook_id']);
    						if($facebookIDExist)
    						{
    							$friend_data['email'] = $facebookIDExist->email;
    						}
    					}
    					else
    					{
    						$friend_data['email'] = '';
    					}   					
    				}
					    				
    				if(!empty($friend_data['email']) && $friend_data['email']!='')
    				{
    					$user_friend 					= $this->member_model->getMemberByEmail($friend_data['email']);
    					if($user_friend){
    						$i++;
    						$friend_data['registed']  	= $user_friend->id;
    					}
    					
    					$checkContactExist = $this->member_model->checkContactExist ( $user_id, $friend_data ['email'] );
						if (trim ( strtolower ( $user->email ) ) == trim ( strtolower ( $friend_data ['email'] ) )) {
							log_message ( 'info', '===============IMPORT CONTACT===================' );
							log_message ( 'info', "$user->email is owner user: #$user_id" );
							log_message ( 'info', '===============END IMPORT CONTACT================' );
						} elseif ($checkContactExist) {
							array_push ( $data, $friend_data );
							log_message ( 'info', '===============IMPORT CONTACT===================' );
							log_message ( 'info', "$checkContactExist->email already exist in your contact list of user: #$user_id" );
							log_message ( 'info', '===============END IMPORT CONTACT================' );
						} else {
							array_push ( $data, $friend_data );
							array_push ( $data_import, $friend_data );
						}
    				}    				
    			}
    		}
    		if($i > 0)
    		{
    			//do import
    			if(!empty($data_import)){
    				$this->member_model->user_import_friends ( $data_import );
    				log_message ( 'info', '===============IMPORT CONTACT===================' );
    				log_message ( 'info', "$i contact was be imported for user id #$user_id" );
    				log_message ( 'info', '===============END IMPORT CONTACT================' );
    			}
				
				// update registed contact list
				$this->member_model->update_contact_friends_registed ( $user_id );
				
				$response ['msg'] = lang ( 'Import friends success' );
				$response ['status'] = true;
				$temp = 0;
				foreach ( $data as $item ) {
					$contact_detail = $this->member_model->getMemberByMemberID ( $item ['registed'], true, true, true, true );
					if(isset($contact_detail) && !empty($contact_detail)){
						$contact_detail->social_type = $item ['social_type'];
						$contact_detail->friend_status = $this->member_model->get_friend_status($user_id, $item ['registed']);
						$response ['items'] [$temp] = format_output_data ( $contact_detail );
						$temp++;
					}
				}
                        
    		}
    		else 
    		{
    			$response['msg'] = lang('No friend was imported');
    			$response['status'] = false;
    		}
    	}
    	else 
    	{
    		
    		$response['msg'] = lang('No friend was imported');
    		$response['status'] = false;
    	}

//     	$items = $this->member_model->get_user_contact('all','first_name','ASC',$user_id,'',1);
//     	if($data_import)
//     	{
//     		foreach($data_import as $ckey => $item)
//     		{
//     			$contact_detail = $this->member_model->getMemberByMemberID($item->registed,true,true,true,true);
//     			$contact_detail->social_type 	= $item->social_type;
//     			$contact_detail->friend_status 	= $item->status;
//     			$response['items'][$ckey] = format_output_data($contact_detail);
//     		}
//     	}

    	$this->response($response,200);    	    	    	
    }
    public function userFriends_post()
    {
    	$this->_requireAuthToken();
    	$status = $this->post('status') ? $this->post('status') : 1;
		$userId = $this->post('user_id') ? $this->post('user_id') : $this->_member->id;
    	
		$start = $this->post('start') ? $this->post('start') : 0;
		$limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
		
    	$friends = $this->member_model->get_user_friends('all', $userId, $status, 'first_name','ASC', $start, $limit, $this->_member->id);
    	$contact = array();
    	if($friends)
    	{
    		foreach($friends as $key => $row)
    		{
    			$contact_detail = $this->member_model->getMemberByMemberID($row->registed,true,true,true,true,$this->_member->id);
    			$contact_detail->social_type = $row->social_type;
    			$contact_detail->friend_status 	= $row->status;
    			$contact_detail->score 	= $this->_get_user_score($contact_detail->id);
    			$contact[$key] = format_output_data($contact_detail);
    		}
    	}
    	
    	$this->response(array('items'=> $contact),200);
    }
    public function userContact_post()
    {
    	$this->_requireAuthToken();
    	$social_type = $this->post('social_type') ? $this->post('social_type') : '';
    	 
    	$contact = $this->member_model->get_user_contact('all','first_name','ASC',$this->_member->id,$social_type,1);
    	if($contact)
    	{
    		foreach($contact as $key => $row)
    		{
    			$contact_detail = $this->member_model->getMemberByMemberID($row->registed,true,true,true,true);
    			$contact_detail->social_type = $row->social_type;
    			$contact_detail->friend_status 	= $row->status;
    			$contact_detail->score 	= $this->_get_user_score($this->_member->id);
    			$contact_detail->score 	= $this->_get_user_score($contact_detail->id);
    			$contact[$key] = format_output_data($contact_detail);
    		}
    	}
    	 
    	$this->response(array('items'=>$contact),200);
    }
    public function userSendRequestFriends_post()
    {
    	$this->_requireAuthToken();
    	$friends = $this->post('friends') ? $this->post('friends') : false;
    	$user_id = $this->_member->id;
    	
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('friends[]', 'Contact', 'required');
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		if(form_error('friends[]'))
    		{
    			$error['msg']	= strip_tags(form_error('friends[]'));
    		}
    		$this->response($error,200);
    	}
    	
    	if(!empty($friends))
    	{
    		foreach($friends as $friend)
    		{
    			if(isset($friend['email']) && !empty($friend['email']))
    			{
					$contact = $this->member_model->getMemberByEmail($friend['email']);
					$userContact = $this->usercontact_model->findOne("((user_id = $user_id AND registed = $contact->id) OR (user_id = $contact->id AND registed = $user_id)) AND status = ".CONTACT_BLOCK);
					if( $userContact ) {
						$this->response(array('msg'=>'Cannot sent friend request when status is blocking'),200);
						return;
					}
    				//check is not friend
    				$checkFriend = $this->member_model->checkUserIsNotFriend($user_id,$friend['email']);
    				if(!$checkFriend)
    				$checkFriend = $this->member_model->getUserFriend($user_id,$friend['email']);
    				if($checkFriend && $checkFriend->status == 1)
    				{
    					$friend_email = $friend['email'];
    					//is friend
    					log_message('info','===============SEND FRIEND REQUEST===================');
    					log_message('info', "User email $friend_email & your id $user_id are friend already.");
    					log_message('info','===============END SEND FRIEND REQUEST================');    					
    				}
    				else
    				{    					
    					//send request 
    					$this->member_model->update_contact(array('status'=>'2'),$checkFriend->id);

    					//update recived status
    					$recievedUserCheck =   $this->member_model->checkContactExist($checkFriend->registed,$this->_member->email);
    					if(!$recievedUserCheck) 
    					{    						
    						$friend_data['user_id'] 		= $checkFriend->registed;
		    				$friend_data['email'] 			= $this->_member->email;
		    				$friend_data['registed']		= $this->_member->id;
		    				$friend_data['status']			= 3;
		    				$friend_data['social_type'] 	= $checkFriend->social_type; //0 user contact ; 1 facebook , 2 instaram
		    				
    						$import_friend_contact = array($friend_data); 
    						//do import
    						$this->member_model->user_import_friends($import_friend_contact);    						
    					}
    					else
    					{
    						$this->member_model->update_contact(array('status'=>'3'),$recievedUserCheck->id);
    					}	
    					
    					//send push to user who received request
    					$this->load->model('notification_model');
    					$this->load->helper('notification');
    					
    					$actor_user_id          = $checkFriend->registed;
    					$name_user_action       = $this->_member->first_name . ' ' . $this->_member->last_name;
    					$message 		= $name_user_action . ' ' .lang('send friend request');
    					$data_push 		= array();
    					$action_type            = get_action_type(self::PUSH_TYPE_SEND_FRIEND_REQUEST);
    					$source_id 		= 0;
    					$data_push 		= array(
    							'action_type'               => self::PUSH_TYPE_SEND_FRIEND_REQUEST,
    							'sender_id'                 => $this->_member->id,
    							'sender_name'               => $name_user_action,
                                'type'                      => 'send_friend_request',
                                'bages_unread_notification' => count_unread_notification($actor_user_id) + 1,
    					);    		
                                        //$this->notification_model->send_push_notification($actor_user_id,$message,$data_push,$action_type->id,$source_id);
    					$this->notification_model->send_push_notification($actor_user_id,$message,$data_push,$action_type->id,$source_id);
    					//end push
    				}
    			}    			 
    		}
    		$this->response(array('msg'=>'Send request successful'),200);
    	}
    	else 
    	{
    		$this->response(array('msg'=>lang('Item not found'),'code' => self::ERROR_CODE_404),200);
    	}
    	
    }
    public function userAcceptRequestFriend_post()
    {
    	$this->_requireAuthToken();
    	$friend_id 	= $this->post('friend_user_id') ? $this->post('friend_user_id') : false;
    	$user_id 	= $this->_member->id;
    	
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('friend_user_id', 'Friend id', 'required');
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		if(form_error('friend_user_id'))
    		{
    			$error['msg']	= strip_tags(form_error('friend_user_id'));
    		}
    		$this->response($error,200);
    	}
    	$friend = $this->member_model->getMemberByMemberID($friend_id);
    	if($friend)
    	{
    		//check user in contact list
    		$contact = $this->member_model->checkContactExist($user_id,$friend->email);    		
    		if($contact)
    		{
    			//accept friend
    			$this->member_model->update_contact(array('status'=>'1'),$contact->id);
    			
    			//update recived status
    			$recievedUserCheck =   $this->member_model->checkContactExist($friend->id,$this->_member->email);
    			$this->member_model->update_contact(array('status'=>'1'),$recievedUserCheck->id);
    			
    			//send push to user who sent friend request
    			$this->load->model('notification_model');
    			$this->load->helper('notification');    				
    			$actor_user_id          = $friend->id;
    			$name_user_action       = $this->_member->first_name . ' ' . $this->_member->last_name;
    			$message 		= $name_user_action .' '.lang('accept friend request');
    			$data_push 		= array();
    			$action_type            = get_action_type(self::PUSH_TYPE_ACCEPT_FRIEND_REQUEST);
    			$source_id 		= 0;
    			$data_push 		= array(
    					'action_type'               => self::PUSH_TYPE_ACCEPT_FRIEND_REQUEST,
    					'sender_id'                 => $this->_member->id,
    					'sender_name'               => $name_user_action,
                                        'type'                      => 'accept_friend_request',
                                        'bages_unread_notification' => count_unread_notification($actor_user_id) + 1,
    			);
    			$this->notification_model->send_push_notification($actor_user_id,$message,$data_push,$action_type->id,$source_id);
    			//end push
    			
    			$this->response(array('msg'=>'add friend successful'),200);
    		}
    		else 
    		{
    			$this->response(array('msg'=>'Contact not found','code'=>self::ERROR_CODE_404),200);
    		}
    		
    	}
    	else 
    	{
    		$this->response(array('msg'=>'Friend user id not found','code'=>self::ERROR_CODE_404),200);
    	}
    	
    	
    }
    public function userRejectFriend_post()
    {
    	$this->_requireAuthToken();
    	$friend_id 	= $this->post('friend_user_id') ? $this->post('friend_user_id') : false;
    	$user_id 	= $this->_member->id;
    	 
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('friend_user_id', 'Friend id', 'required');
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		if(form_error('friend_user_id'))
    		{
    			$error['msg']	= strip_tags(form_error('friend_user_id'));
    		}
    		$this->response($error,200);
    	}
    	$friend = $this->member_model->getMemberByMemberID($friend_id);
    	if($friend)
    	{
    		//check user in contact list
    		$contact = $this->member_model->checkContactExist($user_id,$friend->email);
    		if($contact)
    		{
    			//reject friend
    			$this->member_model->update_contact(array('status'=>'0'),$contact->id);
    			 
    			//update recived status
    			$recievedUserCheck =   $this->member_model->checkContactExist($friend->id,$this->_member->email);
    			$this->member_model->update_contact(array('status'=>'0'),$recievedUserCheck->id);
    			 
    			//send push to user_id
    			$this->response(array('msg'=>'reject friend successful'),200);
    		}
    		else
    		{
    			$this->response(array('msg'=>'Contact not found','code'=>self::ERROR_CODE_404),200);
    		}
    
    	}
    	else
    	{
    		$this->response(array('msg'=>'Friend user id not found','code'=>self::ERROR_CODE_404),200);
    	} 
    }
    public function userDeleteContact_post()
    {
    	$this->_requireAuthToken();
    	$friend_id 	= $this->post('friend_user_id') ? $this->post('friend_user_id') : false;
    	$user_id 	= $this->_member->id;
    	
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('friend_user_id', 'Friend id', 'required');
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		if(form_error('friend_user_id'))
    		{
    			$error['msg']	= strip_tags(form_error('friend_user_id'));
    		}
    		$this->response($error,200);
    	}
    	$friend = $this->member_model->getMemberByMemberID($friend_id);
    	if($friend)
    	{
    		//check user in contact list
    		$contact = $this->member_model->checkContactExist($user_id,$friend->email);
    		if($contact)
    		{
    			//delete friend
    			$this->member_model->delete_contact($user_id,$friend->id);
    	
    			$this->response(array('msg'=>'delete contact successful'),200);
    		}
    		else
    		{
    			$this->response(array('msg'=>'Contact not found','code'=>self::ERROR_CODE_404),200);
    		}
    	
    	}
    	else
    	{
    		$this->response(array('msg'=>'Friend user id not found','code'=>self::ERROR_CODE_404),200);
    	}
    }
    public function userLikeReview_post()
    {
    	$this->_requireAuthToken();
    	$this->load->model('review_model');
    	 
    	$data['review_id'] 		= $this->post('review_id') ? $this->post('review_id') : false;
    	$data['user_id']     	= $this->_member->id;
    	$data['type']     		= 0;
    	$data['created_date']	= now();
    	 
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('type', 'Type', 'required');
    	$this->form_validation->set_rules('review_id', 'Review ID', 'required');
    	 
    	if ($this->form_validation->run() == FALSE) {
    		if(form_error('review_id'))
    		{
    			$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    			$error['msg'] = strip_tags(form_error('review_id'));
    			$this->response($error,200);
    		}
    	}
    
    	//check like
    	$check_like = $this->member_model->check_user_like_review($data['user_id'] , $data['review_id']);
    	if($check_like)
    	{
    		$row_id = $check_like->id;
    		$this->member_model->delete_like($row_id);
    		$item = $this->review_model->get_review_detail($data['review_id'],true,true,true);
    		
    		$response['msg'] = lang('Remove like successful');    		    		
    		$response['item'] = format_output_data($item);
    		$response['like_type'] = strval(1);
    		$this->response($response,200);
    	}
    	else
    	{
    		//ADD like
    		$like_id = $this->member_model->add_like($data);
    		if($like_id)
    		{
    			$item = $this->review_model->get_review_detail($data['review_id'],true,true,true);
    			
    			$response['msg'] = lang('Add new successful');
    			$response['item'] = format_output_data($item);
    			$response['like_type'] = strval(0);
    			//get total like review
    			$total_like = $this->review_model->get_likes_by_review('count',0 ,false ,'id' ,'ASC' , $item->id ,0);
    			$response['total_like'] = $total_like;
    			
    			//send push to user who post review
    			$this->load->model('notification_model');
    			$this->load->helper('notification');
    			$actor_user_id                  = $item->user_id;
    			$name_user_action               = $this->_member->first_name . ' ' . $this->_member->last_name;
    			$message 			= $name_user_action .' '.lang('like your review');
    			$action_type                    = get_action_type(self::PUSH_TYPE_LIKE_REVIEW);
    			$source_id 			= $data['review_id'] ;
    			$data_push 			= array(
    					'action_type'               => self::PUSH_TYPE_LIKE_REVIEW,
    					'sender_id'                 => $this->_member->id,
    					'sender_name'               => $name_user_action,
                                        'type'                      => 'review',
                                        'review_id'                 => $source_id,
                                        'bages_unread_notification' => count_unread_notification($actor_user_id) + 1,
    			);
    			$this->notification_model->send_push_notification($actor_user_id,$message,$data_push,$action_type->id,$source_id);
    			//end send push notification
    			
    			//do activity
//     			$data_activity = array(
//     					'activity_type' => self::PUSH_TYPE_LIKE_REVIEW,
//     					'source_id'		=> $like_id ,
//     					'created_date'	=> now(),
//     					'is_read'		=> 0,
//     					'user_id'		=> $this->_member->id,
//     			);
    			//$this->member_model->insert_user_activity($data_activity);
    			
    			$this->response($response,200);
    		}
    		else
    		{
    			$error['msg'] = lang('Item not found');
    			$error['code']	= self::ERROR_CODE_404;
    			$this->response($error,200);
    		}
    	}
    }  
    public function userCommentReview_post()
    {
    	$this->_requireAuthToken();
    	$this->load->model('review_model');
    	
    	$data['review_id'] 		= $this->post('review_id') ? $this->post('review_id') : false;
    	$data['content'] 		= $this->post('content') ? $this->post('content') : false;
    	$data['user_id']     	= $this->_member->id;
    	$data['created_date']	= now();
    	$data['status']			= 1;
    	
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('content', 'Content', 'required');
    	$this->form_validation->set_rules('review_id', 'Review ID', 'required|integer');
    	
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		if(form_error('review_id'))
    		{    			
    			$error['msg'] = strip_tags(form_error('review_id'));
    			$this->response($error,200);
    		}
    		if(form_error('content'))
    		{
    			$error['msg'] = strip_tags(form_error('content'));
    			$this->response($error,200);
    		}
    	}
    	
    	$comment_id = $this->member_model->add_comment_review($data);
    	if($comment_id)
    	{
    		$total_comments 	= $this->review_model->get_comments_by_review('count', 0 , API_NUM_RECORD_PER_PAGE , 'id' , 'DESC' , $data['review_id'] , 1);
    		$list_comments 		= $this->review_model->get_comments_by_review('all', 0 , API_NUM_RECORD_PER_PAGE , 'id' , 'DESC' , $data['review_id'] , 1);    		
    		
    		if($list_comments)
    		{
    			foreach($list_comments as $key => $comment)
    			{
    				$list_comments[$key] = format_output_data($comment);
    			}
    		}
    		
    		$response['msg'] = lang('Add new successful');
    		$response['total_comment'] = $total_comments;
    		$response['limit'] = API_NUM_RECORD_PER_PAGE;
    		$response['items'] = $list_comments;
    	}
    	else 
    	{
    		$response['error'] = lang('Item not found');
    		$response['code']	= self::ERROR_CODE_404;
    	}
    	$this->response($response,200);    	
    } 
    public function userAddLikePhoto_post()
    {
    	$this->_requireAuthToken();
    
    	$data['media_id'] 		= $this->post('media_id') ? $this->post('media_id') : false;
    	$data['user_id']     	= $this->_member->id;
    	$data['type']     		= 0;
    	$data['created_date']	= now();
    
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('media_id', 'Media ID', 'required');
    
    	if ($this->form_validation->run() == FALSE) {
    		if(form_error('media_id'))
    		{
    			$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    			$error['msg'] = strip_tags(form_error('media_id'));
    			$this->response($error,200);
    		}
    	}
    
    	//check media
    	$media = $this->member_model->get_media_by_id($data['media_id'], $data['user_id']);
    	if($media && $media->status == 1)
    	{
    		//check like
    		$check_like = $this->member_model->check_user_like_media($data['user_id'] , $data['media_id']);
    		if($check_like)
    		{
    			$row_id = $check_like->id;
    			$this->member_model->delete_like($row_id);
    
    			//get total like media
    			$total_like = $this->member_model->get_media_likes($data['media_id']);
    			 
    			$response['msg'] = lang('Remove like successful');
    			$response['total_like'] = count($total_like);
    			$response['like_type'] = strval(1);
    			$this->response($response,200);
    		}
    		else
    		{
    			//ADD like
    			$like_id = $this->member_model->add_like($data);
    			if($like_id)
    			{
    				//send push to user who post photo
    				$this->load->model('notification_model');
    				$this->load->helper('notification');    				
    				$actor_user_id          = $media->user_id;
    				$name_user_action       = $this->_member->first_name . ' ' . $this->_member->last_name;
    				$message 		= $name_user_action .' '.lang('like your photo');    				   		
    				$action_type            = get_action_type(self::PUSH_TYPE_LIKE_PHOTO);
    				$source_id 		= $media->business_id;
    				$data_push 		= array(
    						'action_type'               => self::PUSH_TYPE_LIKE_PHOTO,
    						'sender_id'                 => $this->_member->id,
    						'sender_name'               => $name_user_action,
                                                'type'                      => 'photo',
                                                'business_id'               => $source_id,
                                                'bages_unread_notification' => count_unread_notification($actor_user_id) + 1,
    				);
    				$this->notification_model->send_push_notification($actor_user_id,$message,$data_push,$action_type->id,$source_id);
    				//end send push notification
    				
    				//get total like media
    				$total_like = $this->member_model->get_media_likes($data['media_id']);
    
    				$response['msg'] = lang('Add like successful');
    				$response['total_like'] = count($total_like);
    				$response['like_type'] = strval(0);
    				
    				//do activity
//     				$data_activity = array(
//     						'activity_type' => self::PUSH_TYPE_LIKE_PHOTO,
//     						'source_id'		=> $like_id ,
//     						'created_date'	=> now(),
//     						'is_read'		=> 0,
//     						'user_id'		=> $this->_member->id,
//     				);
//     				$this->member_model->insert_user_activity($data_activity);
    				
    				$this->response($response,200);
    			}
    			else
    			{
    				$error['msg'] = lang('Item not found');
    				$error['code']	= self::ERROR_CODE_404;
    				$this->response($error,200);
    			}
    		}
    	}
    	else
    	{
    		$error['msg'] = lang('Item not found');
    		$error['code']	= self::ERROR_CODE_404;
    		$this->response($error,200);
    	}
    }
    public function deleteMedia_post()
    {
    	$this->_requireAuthToken();
    	$media_id       = $this->post('media_id') ? $this->post('media_id') : false;
        $member_id      = $this->_member->id;
    	 
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('media_id', 'Media ID', 'required');
    	 
    	if ($this->form_validation->run() == FALSE) {
    		if(form_error('media_id'))
    		{
    			$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    			$error['msg'] = strip_tags(form_error('media_id'));
    			$this->response($error,200);
    		}
    	}
    	 
    	//check media
        $media = $this->member_model->get_media_by_id($media_id, $member_id);
        $arrRemoveFile = array();
        $listingArray = array();
        if($media && $media->status == 1)
        {
            $arrRemoveFile[] = $media->source;
            $arrRemoveFile[] = $media->photo_thumb;
            $listingArray    = array($media->business_id);
            //delete
            $this->member_model->delete_media($media_id);
    
            $this->media_model->removeByKeyValue($arrRemoveFile);
            $this->media_model->updateCoverListing($listingArray);

            $response['msg'] = lang('delete media successful');
            $this->response($response,200);
        }
    	else
    	{
    		$error['msg'] = lang('Item not found');
    		$error['code']	= self::ERROR_CODE_404;
    		$this->response($error,200);
    	}
    }
    public function deleteMultiMedia_post()
    {
    	$this->_requireAuthToken();
    
    	$medias_id 		= $this->post('medias_id') ? $this->post('medias_id') : false;
// format: [1,2,3,4,5]
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate    	
    	$this->form_validation->set_rules('medias_id[]', 'Medias ID', 'required');
    
    	if ($this->form_validation->run() == FALSE) {
    		if(form_error('medias_id'))
    		{
    			$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    			$error['msg'] = strip_tags(form_error('medias_id'));
    			$this->response($error,200);
    		}
    	}

		if(is_array($medias_id)) {
			$arrMedia = $medias_id;
		} else {
			$arrMedia = json_decode($medias_id);
		}

    	if(count($arrMedia)) {
			$arrRemoveFile = array();
			$hasCoverDelete = false;
            $listingArray = [];
    		foreach ($arrMedia as $id) {
    			//check media
                $media = $this->member_model->get_media_by_id($id, $this->_member->id);
                if($media){
                    if($media->business_id && !$media->review_id && !$media->tip_id && !in_array($media->business_id, $listingArray)){
                        $listingArray = array_merge($listingArray, array($media->business_id));
                    }
                }	
    			if( $media && $media->media_type != 'cover' )
    			{
					// Get the file path info
					$arrRemoveFile[] = $media->source;
					$arrRemoveFile[] = $media->photo_thumb;

    				//delete
    				$this->member_model->delete_media($id);
    			} elseif( $media && $media->media_type == 'cover' ) {
					$hasCoverDelete = true;
					break;
				} else {
    				$error['msg'] = lang('Item not found') . "#$id";
    				$error['code']	= self::ERROR_CODE_404;
    				$this->response($error,200);
    			}
    		}

			if($hasCoverDelete) {
				$error['msg'] = lang('delete cover photo');
				$error['code']	= self::ERROR_CODE_404;
				$this->response($error, 200);
			} else {
				// Remove file from S3
				$this->media_model->removeByKeyValue($arrRemoveFile);
                $this->media_model->updateCoverListing($listingArray);
				//$response['msg'] = lang('delete media successful');
				$response		= $this->petmember->getUserPhotos( 0, API_NUM_RECORD_PER_PAGE, $this->_member->id );
				$this->response($response, 200);
			}
    	}
    	else
    	{
    		$error['msg'] = lang('Item not found');
    		$error['code']	= self::ERROR_CODE_404;
    		$this->response($error, 200);
    	}
    }
    public function activeMember_get()
    {
    	$activation_code = $this->get('activation_code')?$this->get('activation_code'):false;
    	if(!empty($activation_code) )
    	{
    		$user = $this->db->get_where('users',array('activation_code'=>$activation_code))->first_row();
    		if($user && $user->active != 1)
    		{
    			//active user
    			$this->db->where('id',$user->id);
    			$this->db->update('users',array('active'=>1));
    			
    			//send mail to user
    			$this->load->library('email');
    			$this->load->helper('site');
    			$this->email->initialize(MailHelp::config());
    			
    			$admin_mail 	= $this->config->item('email_from_email');
    			$admin_name 	= $this->config->item('mail_app_title');
    			$this->email->from($admin_mail, $admin_name);
    			$this->email->to($user->email);
    			
    			$message	= "Your account was actived successful";
    			
    			$this->email->subject("[PetApp] Active account");
    			$this->email->message($message);
    			$this->email->send();
    			
    			//redirect(siteURL().'Email_Verification_Complete');
                        
                        //redirect dev server
    			redirect(siteURL().'version2/pet-server/trunk/email_verification_complete');
    			
    			$response['msg'] = 'successful';
    			$this->response($response,200);
    		}
                elseif($user && $user->active == 1){
                        //redirect(siteURL().'Account_Already_Active');
                    
                        //redirect dev server
                        redirect(siteURL().'version2/pet-server/trunk/account_already_active');
    			
    			$error['msg'] = lang('Item not found');
    			$error['code']	= self::ERROR_CODE_404;
    			$this->response($error,200);
                }
    		else 
    		{
                        //redirect(siteURL().'en/content/actived_fail');
                        
                        //redirect dev server
    			redirect(siteURL().'version2/pet-server/trunk/activation_unsuccessful');
    			
    			$error['msg'] = lang('Item not found');
    			$error['code']	= self::ERROR_CODE_404;
    			$this->response($error,200);
    		}
    	}
    	else 
    	{
    		
    		redirect(siteURL().'en/content/actived_fail');
    		
    		$error['msg'] = lang('Item not found');
    		$error['code']	= self::ERROR_CODE_404;
    		$this->response($error,200);
    	}
    }
    public function userReport_post()
    {
    	$this->_requireAuthToken();
    	
    	$data['message'] 		= $this->post('message') ? $this->post('message') : false;
    	$data['user_id']     	= $this->_member->id;
    	$data['created_date']	= now();
    	
    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('message', 'Message', 'required');
    	
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		if(form_error('message'))
    		{    			
    			$error['msg'] = strip_tags(form_error('message'));
    			$this->response($error,200);
    		}
    	}
    	$this->db->insert('user_reports',$data);
    	$this->response(array('msg'=> 'Thank you for your report'),200);
    }
    public function userNotification_post()
    {
    	$this->_requireAuthToken();
    	
    	$this->load->model('notification_model');
    	$this->load->model('pet_model');
        $this->load->helper('listing');

    	$start 		= $this->post('start') ? $this->post('start') : 0;
    	$limit 		= $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
    	$user_id	= $this->_member->id;
    	
    	$items 			= $this->notification_model->get_notification_by_user('all',$user_id,$start,$limit,'id','DESC');
    	$total_items 	= $this->notification_model->get_notification_by_user('count',$user_id);
    	
    	if($items)
    	{
    		foreach($items as $key=> $item)
    		{    			
    			$item->data 	= json_decode($item->data);
    			$item_data 		= $item->data;
    			if(isset($item_data->data->sender_id) )
    			{
    				$sender_info 					= $this->member_model->getMemberByMemberID($item_data->data->sender_id);
    				$sender_info 					= format_output_data($sender_info);
    				$item_data->data->profile_photo = $sender_info->profile_photo_thumb;
    			}   
                if(isset($item_data->data->pet_id) )
                {
                    $pet                            = $this->pet_model->get_pet($item_data->data->pet_id);
                    if($pet){
                        $pet_info                   = $pet['information'];
                        $item_data->data->profile_photo = $pet_info->profile_photo_thumb;
                        // $item_data->data->profile_photo = fileExists($pet_info->profile_photo_thumb) ? $pet_info->profile_photo_thumb : "";
                    }                    
                }    			    			
    			$items[$key] 	= format_output_data($item);
    		}	
    	}
    	else
    	{
    		$items = array();
    	}
    	$response['items'] 		= $items;
    	$response['totalItem'] 	= $total_items;
    	$response['totalPage']	= ceil(intval($total_items) / $limit);
    	$response['limit']		= intval($limit);
    	
    	$this->response($response,200);    	
    }
    public function resetBagesPush_post() {

        $this->_requireAuthToken();
        $this->load->helper('notification');
        $this->load->model('notification_model');
        
        $user_id = $this->post('user_id') ? $this->post('user_id') : $this->_member->id;
        $data = array(
            'is_read' => 1,
        );
        $this->notification_model->update_where('user_id', $user_id, $data);
        $response['items'] = array(
            'user_id' => $user_id,
            'bages_unread_notification' => count_unread_notification($user_id),
        );               
        $this->response($response, 200);
    }
    public function userMyFeed_post()
    {
    	$this->_requireAuthToken();
    	$this->load->model('listing_model');
    	$this->load->model('tip_model');
    	
    	$start = $this->post('start') ? $this->post('start') : 0;
    	$limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
    	
    	$user_id = $this->_member->id;
    	$activity_types = array(self::PUSH_TYPE_ADD_REVIEW,self::PUSH_TYPE_ADD_TIP,self::PUSH_TYPE_BOOKMARK);
    	$items 			= $this->member_model->get_user_feeds('all',$user_id,$start,$limit,'created_date','DESC',$activity_types);
    	$total_items 	= $this->member_model->get_user_feeds('count',$user_id,false,false,'created_date','DESC',$activity_types);
    	
        $result_items 	= array();
    	if($items)
    	{
    		$i = 0;    		
    		foreach($items as $key => $item)
    		{
    			switch ($item->activity_type) {
    					case self::PUSH_TYPE_ADD_REVIEW:
    						$review_detail = $this->review_model->get_review_detail($item->source_id,true,true,true);    						
    						if($review_detail)
    						{
    							$business_detail = $this->listing_model->get_listing_detail($review_detail->business_id,true,true,true,true,true);
    							$review_detail->business_info = $business_detail;
    						}
                                                $item->created_date = local_to_gmt($item->created_date);
    						$item->activity_data = format_output_data($review_detail);
    						$result_items[$i] = format_output_data($item);
    					break;
    						//     				case self::PUSH_TYPE_LIKE_PHOTO:
    						//     					$media_detail = $this->member_model->get_media_by_id($item->source_id);
    						//     					$item->activity_data = format_output_data($media_detail);
    						//     				break;
    				
    						//     				case self::PUSH_TYPE_LIKE_TIP:
    						//     					$this->load->model('tip_model');
    						//     					$tip_detail = $this->tip_model->get_tip_detail($item->source_id,true);
    						//     					$item->activity_data = format_output_data($tip_detail);
    						//     				break;
    					case self::PUSH_TYPE_ADD_TIP:    						
    						$tip_detail = $this->tip_model->get_tip_detail($item->source_id,true);    						
    						if($tip_detail)
    						{
    							$business_detail = $this->listing_model->get_listing_detail($tip_detail->business_id,true,true,true,true,true);
    							$tip_detail->business_info = $business_detail;
    						}
                                                $item->created_date = local_to_gmt($item->created_date);
    						$item->activity_data = format_output_data($tip_detail);
    						$result_items[$i] = format_output_data($item);
    					break;
    					case self::PUSH_TYPE_BOOKMARK:                                                
    						$listing_detail = $this->listing_model->get_listing_detail($item->source_id,true,true,true,true,true);
                                                $item->created_date = local_to_gmt($item->created_date);	
                                                $item->activity_data['business_info'] = $listing_detail;    						
    						$result_items[$i] = format_output_data($item);
    					break;    					    			
    				}
    			$i++;
    		}
    	}
    	
    	$response['items'] 		= $result_items;
    	$response['totalItem'] 	= $total_items;
    	$response['totalPage']	= ceil(intval($total_items) / $limit);
    	$response['limit']		= intval($limit);
    	$this->response($response,200);
    }

    public function editReview_post(){
        $this->_requireAuthToken();
        
        $this->load->helper('image');
        $this->load->library('image_lib');
        
        $response = array();
        $review_id 	= $this->post('review_id') ? $this->post('review_id') : FALSE;
        $rate 		= $this->post('rate') ? $this->post('rate') : FALSE;
        $content 	= $this->post('content') ? $this->post('content') : FALSE;
        $user_id 	= $this->_member->id;
        
        $this->load->library('form_validation');
        /*Set the form validation rules*/
        $_POST = $this->post();//set this for validate
        $this->form_validation->set_rules('review_id', 'Review id', 'required|callback_checkUserEditReview');
        $this->form_validation->set_rules('content', 'Content', 'required');
        if ($this->form_validation->run() === FALSE) {
        	
        	$response['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
        	if(form_error('review_id')){
        		$response['msg'] = strip_tags(form_error('review_id'));
        		$this->response($response,200);
        	}
        	elseif(form_error('content')){
        		$response['msg'] = strip_tags(form_error('content'));
        		$this->response($response,200);
        	}
        }
        $data_update = array(
        		'content' => $content
        );
        if($rate){
        	$data_update['rate'] = $rate;
        }
        //update review
        $this->review_model->update($data_update,$review_id);
        
        //upload photo and overwrite profile photo
        $media_files = $this->_doMultiUpload($this->config->item('listings_path'));
         
        //save media to db
        if($media_files && $review_id)
        {
        	$data_insert = array();
        
        
        	foreach($media_files as $file)
        	{
        		$file_array 	= array();
        		$media_insert 	= array();
        		 
        		$file_array = $file['upload_data'];
        		 
        		$media_insert['review_id']	= $review_id;
        		$media_insert['source'] 	= $this->config->item('api_upload_path').$this->config->item('listings_path').$file_array['file_name'];
        		$media_insert['status']		= 1;
        		$media_insert['user_id']	= $this->_member->id;
        		$media_insert['created_date']	= now();;
                        if(empty($file_array['image_type']) )
        		{
        			//video
        			$media_insert['type'] = 'VIDEO';
        			$media_insert['photo_thumb'] = null;
        		}
        		else
        		{
        			$media_insert['type'] = 'PHOTO';
        
        			//resize
        			resizeImage($file_array['full_path'],IMAGE_RESIZE_WIDTH,IMAGE_RESIZE_HEIGHT);
        			$file_name_array 				= explode('.', $file_array['file_name']);
        			$media_insert['photo_thumb'] 	= $this->config->item('api_upload_path').$this->config->item('listings_path').$file_name_array[0].'_thumb.'.$file_name_array[1];
        		}
        		array_push($data_insert, $media_insert);
        	}
        	//insert
        	insert_user_media($data_insert);
        }

        $item = $this->review_model->get_review_detail($review_id,true);
        $response['item'] = format_output_data($item);
        $this->response($response,200);
    }
    
    public function checkUserEditReview($review_id){
    	$member = $this->_member->id;
    	$review_detail = $this->review_model->get_review_detail($review_id);    	
    	if($review_detail){
    		$author = $review_detail->user_id;
    		if($member !== $author){    			
    			$this->form_validation->set_message('checkUserEditReview', 'Your account do not have permission to edit this review');
    			return FALSE;
    		}
    		else
    		{    			
    			return TRUE;
    		}
    	}
    	else
    	{    		
    		$this->form_validation->set_message('checkUserEditReview', '%s does not exist');
    		return FALSE;
    	}
    }
    function updateUserSetting_post()
    {
    	$this->_requireAuthToken();
    	$update_data = array();
    	
    	$data ['notifications_likes_and_comments'] = $this->post ( 'notifications_likes_and_comments' ) ? $this->post ( 'notifications_likes_and_comments' ) : FALSE;
        $data ['notifications_messages'] = $this->post ( 'notifications_messages' ) ? $this->post ( 'notifications_messages' ) : FALSE;
        $data ['notifications_message_preview'] = $this->post ( 'notifications_message_preview' ) ? $this->post ( 'notifications_message_preview' ) : FALSE;
        $data ['notifications_vibrate'] = $this->post ( 'notifications_vibrate' ) ? $this->post ( 'notifications_vibrate' ) : FALSE;
        $data ['notifications_light'] = $this->post ('notifications_light' ) ? $this->post ( 'notifications_light' ) : FALSE;
        $data ['notifications_announcements'] = $this->post ( 'notifications_announcements' ) ? $this->post ( 'notifications_announcements' ) : FALSE;
        $data ['shareoptions_facebook'] = $this->post ( 'shareoptions_facebook' ) ? $this->post ( 'shareoptions_facebook' ) : FALSE;
        $data ['shareoptions_twitter'] = $this->post ( 'shareoptions_twitter' ) ? $this->post ( 'shareoptions_twitter' ) : FALSE;
        $data ['shareoptions_instagram'] = $this->post ( 'shareoptions_instagram' ) ? $this->post ( 'shareoptions_instagram' ) : FALSE;
        $data ['shareoptions_achievement'] = $this->post ( 'shareoptions_achievement' ) ? $this->post ( 'shareoptions_achievement' ) : FALSE;
        $data ['location_lock'] = $this->post ( 'location_lock' ) ? $this->post ( 'location_lock' ) : FALSE;        
        $data ['distance_units'] = $this->post ( 'distance_units' ) ? $this->post ( 'distance_units' ) : FALSE;
        $data ['privacy_findfriends'] = $this->post ( 'privacy_findfriends' ) ? $this->post ( 'privacy_findfriends' ) : FALSE;
        $data ['privacy_visibility_name_profile'] = $this->post ( 'privacy_visibility_name_profile' ) ? $this->post ( 'privacy_visibility_name_profile' ) : FALSE;
        $data ['privacy_visibility_generaldemograpdemographics'] = $this->post ( 'privacy_visibility_generaldemograpdemographics' ) ? $this->post ( 'privacy_visibility_generaldemograpdemographics' ) : FALSE;
    	
        $location_city = $this->post ( 'location_city' ) ? $this->post ( 'location_city' ) : FALSE;
        
        if($location_city){
            $str = explode(',', $location_city);
            
            if(!(sizeof($str) == 2 && is_numeric(trim($str[0])) && is_numeric(trim($str[1])))){
               $location_address = get_location_from_address($location_city);
               
               if(empty($location_address)){
                   $response['msg'] = "Location not found";
                   $response['code'] = self::ERROR_CODE_LOCATION_NOT_FOUND;
                   $this->response($response,200);
               }
               else{
                   $location_city = $location_address['lat'] . ',' . $location_address['long'];
               }
            }
        }
        
        $data ['location_city'] = $location_city;
        
//     	$this->load->library('form_validation');
//     	/*Set the form validation rules*/
//     	$_POST = $this->post();//set this for validate
//     	$this->form_validation->set_rules('allow_facebook_sharing', 'facebook sharing', 'required');
//     	$this->form_validation->set_rules('allow_twitter_sharing', 'twitter sharing', 'required');
//     	$this->form_validation->set_rules('allow_notification', 'notification', 'required');
//     	if ($this->form_validation->run() === FALSE) {
//     		$response['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
//     		if(form_error('allow_facebook_sharing')){
//     			$response['msg'] = strip_tags(form_error('allow_facebook_sharing'));
//     			$this->response($response,200);
//     		}
//     		elseif(form_error('allow_twitter_sharing')){
//     			$response['msg'] = strip_tags(form_error('allow_twitter_sharing'));
//     			$this->response($response,200);
//     		}
//     		elseif(form_error('allow_notification')){
//     			$response['msg'] = strip_tags(form_error('allow_notification'));
//     			$this->response($response,200);
//     		}
//     	}
		if(!empty($data)){
			$i = 0;
			foreach($data as $key => $dt){
				if($dt === FALSE){
					unset($data[$key]);
				}
				else
				{
					$update_data[$key] = $dt;
				}
			}
		}
            	  	 
    	//update member
    	if($this->member_model->update_user_options($update_data,$this->_member->id)){
    		$response['msg'] = "Update successful";
    	}
    	else
    	{
    		$response['msg'] = "Update failure";
    		$response['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    	}
    	//check user options
    	$user_options = $this->member_model->get_user_options($this->_member->id);
    	$user_options = format_output_data($user_options);
    	
    	$response['item'] = $user_options;
    	$this->response($response,200);
    }
    public function getUserScore_post(){
    	$this->_requireAuthToken();
    	
    	$user_id = $this->_member->id;
    	$score = $this->_get_user_score($user_id);
    	$this->response(array('item'=>$score),200);
    }
    private function _get_user_score($user_id = false){
    	$score = 0;
    	
    	$checkin_score = 0;
    	$review_score = 0;
    	$tip_score = 0;
    	$listing_score = 0;
    	$topic_score = 0;
    	$media_score = 0;
    	
    	if(!$user_id){
    		return 0;
    	}
    	else
    	{
    		$activity_types = array(
    				self::PUSH_TYPE_ADD_CHECKIN,
    				self::PUSH_TYPE_ADD_REVIEW,
    				self::PUSH_TYPE_ADD_TIP,
    				self::PUSH_TYPE_ADD_LISTING,
    				self::PUSH_TYPE_ADD_PET_TOPIC,
    				self::PUSH_TYPE_ADD_MEDIA
    		);
    		$feeds = $this->member_model->get_user_feeds('all',$user_id,0,5000,'id','DESC',$activity_types);
    		if($feeds){
    			foreach($feeds as $record){
    				if($record->activity_type === self::PUSH_TYPE_ADD_CHECKIN){
    					$checkin_score += 1;
    					continue;
    				}
    				if($record->activity_type === self::PUSH_TYPE_ADD_REVIEW){
    					$review_score += 1;
    					continue;
    				}
    				if($record->activity_type === self::PUSH_TYPE_ADD_TIP){
    					$tip_score += 1;
    					continue;
    				}
    				if($record->activity_type === self::PUSH_TYPE_ADD_LISTING){
    					$listing_score += 1;
    					continue;
    				}
    				if($record->activity_type === self::PUSH_TYPE_ADD_PET_TOPIC){
    					$topic_score += 1;
    					continue;
    				}
    				if($record->activity_type === self::PUSH_TYPE_ADD_MEDIA){
    					$media_score += 1;
    					continue;
    				}
    			}
    			$score = ($checkin_score + $review_score + $tip_score + $listing_score + $topic_score + $media_score);
    		}
    	}
    	return $score;
    }
    
    public function _get_user_pets_medications($user_id = false){
        if(!$user_id){
    		return 0;
    	}

        $this->load->model('pet_model');
        $items = $this->pet_model->get_pets($user_id);
        
        $data = array();
        if(!empty($items) )
        {    	
//                $pet_types = $this->pet_model->get_pet_types();
//                if(!empty($pet_types))
//                {
//                        foreach($pet_types as $key => $pt)
//                        {
//                                foreach($items as $ikey => $item)
//                                {
//                                        if($item->type == $pt->id)
//                                        {    						
//                                                $pet_detail 				=  $this->pet_model->get_pet($item->id,true,false,false,false,true,false,false,false,false,false);
//
//                                                $pet_types[$key]->list_pets[] = $pet_detail;
//                                                unset($items[$ikey]);
//                                        }    					
//                                }    				
//                                if(!isset($pet_types[$key]->list_pets)){
//                                        unset($pet_types[$key]);
//                                }
//                                else
//                                {
//                                        $pet_types[$key] = format_output_data($pt);
//                                }
//                        }    
//
//                }
//                $result = format_output_data($pet_types);
//                if(!empty($result))
//                {
//                        foreach($result as $key => $item)
//                        {
//                                $data['items'][] = $item;
//                        }
//                }

                foreach ($items as $key => $item){
                    $medications =  $this->pet_model->get_pet_medications($item->id);
                    if($medications)
    			{
    				foreach($medications as $mkey => $mitem)
    				{
                                        $mitem->reminder_times_per_day = json_decode($mitem->reminder_times) && json_decode($mitem->reminder_times)->reminder_times_per_day ? json_decode($mitem->reminder_times)->reminder_times_per_day : 0;
                                        $mitem->reminder_times =  json_decode($mitem->reminder_times) && json_decode($mitem->reminder_times)->reminder_times ? json_decode($mitem->reminder_times)->reminder_times : array();
    					$mitem->pet_name = $item->name;
                                        
                                        array_push($data, format_output_data($mitem));
    				}
    			}
                }
        }
        
        return $data;
    }
    
    public function getUserInfo_post(){     
        $this->_requireAuthToken();
        
        $user_id = $this->post('user_id') ? $this->post('user_id') : 0;
        $start      = $this->post('start') ? $this->post('start') : 0;
        $limit      = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
//      $keyword    = $this->post('keyword') ? $this->post('keyword') : false;
        $business_id    = $this->post('listing_id') ? $this->post('listing_id') : 0;
        
        $data['items']      = $this->member_model->get_user_photos_v4('all', $start, $limit, $user_id, false);
        $data['totalItem']  = $this->member_model->get_user_photos_v4('count',$start, $limit, $user_id, false);
        $data['totalPage']  = ceil(intval($data['totalItem']) / $limit);
        $data['limit']      = intval($limit);
        
        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('user_id', 'User ID', 'required|integer');
        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('user_id')) {
                $error['msg'] = strip_tags(form_error('user_id'));
            }
            $this->response($error, 200);
        }
        
        $user = $this->member_model->getMemberByMemberID($user_id , true , true , true , true, $this->_member->id);
    	if(!empty($user))
    	{
    		$user = format_output_data($user);
    		//$response['item'] = $user;

    		//setup output data
            unset($user->token);
            unset($user->activation_code);
            unset($user->last_login);
            unset($user->friends);

			// get user total bookmark
			$user->totalBookmarks =  $this->member_model->getTotalBookmarks($user_id);

			// GET user status
			$userOpt = $this->member_model->getUserOptions($user_id);

			$user->userStatus = $userOpt["userStatus"];

			// GET user last know location
			$user->latitude = $userOpt["latitude"];
			$user->longitude = $userOpt["longitude"];

            $ownerOpt = $this->member_model->getUserOptions($this->_member->id);
            $user->distance = distance($userOpt["latitude"],$userOpt["longitude"], $ownerOpt["latitude"], $ownerOpt["longitude"]);

			// GET user last know location
			/*$data_reponse['latitude'] = $userOpt["latitude"];
			$data_reponse['longitude'] = $userOpt["longitude"];*/

            //get user photo
            $user->photo = array();
            if($data['items'])
            {
                $this->load->model('listing_model');
                foreach($data['items'] as $key => $listing)
                {    
                    $data['items'][$key] = format_output_data($listing);
                    $data['items'][$key]->media = array();
                    $media = $this->listing_model->get_media_by_listing('all',0,API_NUM_RECORD_PER_PAGE,$listing->business_id,'id','DESC',$user_id, true);
                    if($media)
                    {
                        foreach($media as $mkey => $m)
                        {
                            //get total like media
                            $total_like = $this->member_model->get_media_likes($m->id);
                            $m->total_like = count($total_like);
                            
                            //get user like media
                            $like_type = $this->member_model->check_user_like_media($user_id,$m->id);
                            if($like_type){
                                $m->like_type = strval($like_type->type);
                            }
                            else
                            {
                                $m->like_type = '';
                            }
                            
                            $media[$mkey] = format_output_data($m);
                        }
                        $data['items'][$key]->media = $media;       
                        $user->photo = array_merge($user->photo, $media);
                    }
                    
                }
                //$user->photo = $data['items'];
            
            }       
            else{
                $user->photo = array();
            }
    		//get user score;
    		$user->score = $this->_get_user_score($user_id); 
             
            //get user location
            $user->location = array(
                'latitude' => '',
                'longitude' => '',
            );
            $user_option = $this->member_model->get_user_options($user_id);
            if($user_option && !empty($user_option->location_city)){
                $location = explode(',', $user_option->location_city);
                $user->location['latitude'] = $location[0];
                $user->location['longitude'] = $location[1];
            }
 
                
            //check user friend
            $user->friend_status = $this->member_model->check_user_friend_status($user_id,  $this->_member->id);       		
    		
            $this->response($user, 200);
    	}    	
    	
    	
    	$error['msg'] = lang('User not found');
    	$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
    	$this->response($error,200);
    }

    public function userDeleteMessage_post(){
        
        $this->_requireAuthToken();
        
        $message_id 	= $this->post('id') ? $this->post('id') : false;
        $channel        = $this->post('channel') !== false  ? $this->post('channel') : false;
    	$user_id 	= $this->_member->id;

    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('id', 'Message id', 'required');
    	if ($this->form_validation->run() == FALSE) {
    		$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
    		if(form_error('id'))
    		{
    			$error['msg']	= strip_tags(form_error('id'));
    		}
    		$this->response($error,200);
    	}
        
    	$msg = $this->member_model->get_message_by_id($message_id);
        
    	if($msg)
    	{
                if($channel === '0'){
                    $result = $this->member_model->deleteUserMessage($message_id, $user_id, true);
                }
                else{
                    $result = $this->member_model->deleteUserMessage($message_id, $user_id);
                }
                
                if($result){
                    $this->response(array('msg'=>'Delete message successful'),200);
                }
                else{
                    $this->response(array('msg'=>'Delete message failure','code'=>self::ERROR_CODE_404),200);
                }
    	}
    	else
    	{
    		$this->response(array('msg'=>'Message id not found','code'=>self::ERROR_CODE_404),200);
    	}
    }
    
    public function resendEmailActivation_post(){
        $email = $this->post('email') ? $this->post('email') : false;

    	$this->load->library('form_validation');
    	/*Set the form validation rules*/
    	$_POST = $this->post();//set this for validate
    	$this->form_validation->set_rules('email', 'Email', 'trim|required');
        
        if($this->member_model->checkNonActiveMember($email)){
            //send active mail
            $member = $this->member_model->getMemberByEmail($email);
            $send_mail_active = send_active_email($member->id);
            if($send_mail_active){
                $this->response(array('msg'=>'Success. Please check your email to active the account!'),200);
            }
            else{
                $this->response(array('msg'=>'Error. Please try again!','code'=>self::ERROR_CODE_404),200);
            }
        }
        
        $this->response(array('msg'=>'Account already actived!','code'=>self::ERROR_CODE_404),200);
        
    }


	public function unfriend_post() {

		$this->_requireAuthToken();

		$userId 	= $this->_member->id;

		$friendId	= $this->post('friendId') ? $this->post('friendId') : false;

		if(!$userId || !$friendId) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			$error['msg'] = "Please input friend ID";
			$this->response($error, 200);
		}

		$this->member_model->unfriend($userId, $friendId);

		$this->response(array(), 200);
	}

    public function email_check($email){
        $status = $this->member_model->checkMember($email, false);
        
        if ($status)
        {
        	$this->form_validation->set_message('email_check', 'The {field} already registed before, please try again');
        	return FALSE;
        }
        else
        {
        	return TRUE;
        }
        
        //return $status ? FALSE : TRUE;
    }

	public function cancelRequest_post() {

		$this->_requireAuthToken();

		$userId 	= $this->_member->id;

		$friendId	= $this->post('senderId') ? $this->post('senderId') : false;

		if(!$userId || !$friendId) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			$error['msg'] = "Please input sender ID";
			$this->response($error, 200);
		}

		$this->member_model->cancelFriendRequest( $userId, $friendId );

		$this->response(array(), 200);
	}

	public function privateSetting_post() {

		$this->_requireAuthToken();

		$userId 		= $this->_member->id;

		$privateSetting	= (int) $this->post('private_user') ? $this->post('private_user') : false;

		$this->db->update("users", array("private_user" => $privateSetting), array("id" => $userId));

		$this->response(array(), 200);
	}
	
	public function userGetContact_post()
	{
		$this->_requireAuthToken();
		 
		$contact = $this->post('items') ? $this->post('items') : false;
		$user_id = $this->_member->id;
	
		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('items[]', 'Contact item', '"trim|xss_clean');
		if ($this->form_validation->run() == FALSE) {
			$error['code'] 	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('items[]'))
			{
				$error['msg']	= strip_tags(form_error('items[]'));
			}
			$this->response($error,200);
		}
		 
		$response['msg'] = '';
		$response['status'] = false;
		$response['items'] = array();
		$user = $this->member_model->getMemberByMemberID($user_id);
	
		if(!empty($contact))
		{
			$data_import = array();
			$i=0;
			//update registed contact list
			$this->member_model->update_contact_friends_registed($user_id);
	
			foreach ($contact as $friend)
			{
				if(is_array($friend))
				{
					$friend_data['user_id'] 		= $user_id;
					$friend_data['email'] 			= isset($friend['email']) && !empty($friend['email']) ? $friend['email'] : '';
					$friend_data['first_name'] 		= isset($friend['first_name']) && !empty($friend['first_name']) ? $friend['first_name'] : '';
					$friend_data['last_name'] 		= isset($friend['last_name']) && !empty($friend['last_name']) ? $friend['last_name'] : '';
					$friend_data['phone'] 			= isset($friend['phone']) && !empty($friend['phone']) ? $friend['phone'] : '';
					$friend_data['registed']		= 0;
					$friend_data['status']			= 0;
					$friend_data['social_type'] 	= isset($friend['social_type']) && !empty($friend['social_type']) ? $friend['social_type'] : 0; //0 user contact ; 1 facebook , 2 instaram
	
					if($friend_data['social_type'] == 1)
					{
						if(isset($friend['facebook_id']) )
						{
							$facebookIDExist = $this->member_model->checkMember(false,$friend['facebook_id']);
							if($facebookIDExist)
							{
								$friend_data['email'] = $facebookIDExist->email;
							}
						}
						else
						{
							$friend_data['email'] = '';
						}
					}
					 
					if(!empty($friend_data['email']) && $friend_data['email']!='')
					{
						$checkContactExist = $this->member_model->checkContactExist($user_id,$friend_data['email']);
						if(trim(strtolower($user->email)) ==  trim(strtolower($friend_data['email']))){
							log_message('info','===============IMPORT CONTACT===================');
							log_message('info', "$user->email is owner user: #$user_id");
							log_message('info','===============END IMPORT CONTACT================');
						}
						elseif($checkContactExist)
						{
							log_message('info','===============IMPORT CONTACT===================');
							log_message('info', "$checkContactExist->email already exist in your contact list of user: #$user_id");
							log_message('info','===============END IMPORT CONTACT================');
	
						}
						else
						{
							array_push($data_import, $friend_data);
							$i++;
						}
					}
				}
			}
			if(!empty($data_import))
			{
				//do import
				$this->member_model->user_import_friends($data_import);
				log_message('info','===============IMPORT CONTACT===================');
				log_message('info', "$i contact was be imported for user id #$user_id");
				log_message('info','===============END IMPORT CONTACT================');
				 
				//update registed contact list
				$this->member_model->update_contact_friends_registed($user_id);
				 
				$response['msg'] = lang('Import friends success');
				$response['status'] = true;
			}
			else
			{
				$response['msg'] = lang('No friend was imported');
				$response['status'] = true;
			}
		}
		else
		{
	
			$response['msg'] = lang('No friend was imported');
			$response['status'] = false;
		}
		$items = $this->member_model->get_user_contact('all','first_name','ASC',$user_id,'',1);
		if($items)
		{
			foreach($items as $ckey => $item)
			{
				$contact_detail = $this->member_model->getMemberByMemberID($item->registed,true,true,true,true);
				$contact_detail->social_type 	= $item->social_type;
				$contact_detail->friend_status 	= $item->status;
				$response['items'][$ckey] = format_output_data($contact_detail);
			}
		}
		$this->response($response,200);
	}

	/**
	 * @description: User block contact API
     */
	public function blockUser_post() {
		$this->_requireAuthToken();
		$userId = $this->post('userId') ? $this->post('userId') : false;
		$this->petuserfriend->blockUser( $userId, $this->_member);
		$this->response(array(), 200);
	}

	/**
	 * @description: User unblock contact API
	 */
	public function unblockUser_post() {
		$this->_requireAuthToken();
		$userId = $this->post('userId') ? $this->post('userId') : false;
		$this->petuserfriend->unblockUser( $userId, $this->_member);
		$this->response(array(), 200);
	}

	/**
	 * @description: Get user contact list API
	 */
	public function userBlockList_post() {
		$this->_requireAuthToken();
		$start = $this->post('start') ? $this->post('start') : 0;
		$limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
		$response = $this->petuserfriend->getUserContactItems( $this->_member, CONTACT_BLOCK, $start, $limit );
		$this->response($response, 200);
	}
}