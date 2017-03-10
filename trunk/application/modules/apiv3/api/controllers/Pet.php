<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/modules/api/api/libraries/REST_Controller.php';

/**
 *
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @created_date: May 5, 2015
 */
class Pet extends REST_Controller
{
	function __construct()
	{
		// Construct our parent class
		parent::__construct();

		//load model
		$this->load->model('pet_model');
		$this->load->model('media_model');
		//load lang
		$this->lang->load('api');
		//load helper
		$this->load->helper(array('form', 'url'));
	}

	function add_post()
	{
		$this->_requireAuthToken();

		$data['name']      		= $this->post('name') ? $this->post('name') : null;
		$data['dob']      		= $this->post('dob') ? strtotime($this->post('dob')) : false;
		$data['type']      		= $this->post('type') ? $this->post('type') : false;
		$data['breed']                  = $this->post('breed') ? $this->post('breed') : null;
		$data['sex']      		= $this->post('sex') ? $this->post('sex') : 0;
		$data['color']                  = $this->post('color') ? $this->post('color') : null;
		$data['purchase_date']          = $this->post('purchase_date') ? strtotime($this->post('purchase_date')) : false;
		$data['origin']			= $this->post('origin') ? $this->post('origin') : null;
		$data['profile_photo']          = $this->post('profile_photo') ? $this->post('profile_photo') : null;
		$data['user_id']		= $this->_member->id;
		$data['created_date']           = now();
		$data['modified_date']           = now();
		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('type', 'Pet type', 'integer');
		$this->form_validation->set_rules('sex', 'Pet sex', 'integer');

		if ($this->form_validation->run() == FALSE) {
			$error_list = $this->form_validation->error_array();

			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('name'))
			{
				$error['msg'] = form_error('name');
			}
			if(form_error('type'))
			{
				$error['msg'] = form_error('type');
			}
			if(form_error('sex'))
			{
				$error['msg'] = form_error('sex');
			}
			$this->response($error,200);
		}
		//upload photo and overwrite profile photo
		//$photo = $this->_doUpload($this->config->item('pet_path'));

		$petProfileField = (isset($_FILES['file']) && !empty($_FILES['file'])) ? 'file' : '';
		$petProfileField = (isset($_FILES['profile_photo']) && !empty($_FILES['profile_photo'])) ? 'profile_photo' : $petProfileField;

		$photo = $this->media_model->S3Upload( false, $petProfileField, '');
		if($photo)
		{
			/*//resize
            $this->load->helper('image');
            resizeImage($photo['full_path'],IMAGE_RESIZE_WIDTH,IMAGE_RESIZE_HEIGHT);

            $data['profile_photo'] 			= $this->config->item('api_upload_path').$this->config->item('pet_path').$photo['file_name'];
            $file_name_array 				= explode('.', $photo['file_name']);
            $data['profile_photo_thumb'] 	= $this->config->item('api_upload_path').$this->config->item('pet_path').$file_name_array[0].'_thumb.'.$file_name_array[1];*/
			$data['profile_photo'] 			= $photo['uri'];
			$data['profile_photo_thumb'] 	= $photo['uri_thumb'];
		}
		//add pet
		$pet_id = $this->pet_model->add($data);
		if($pet_id)
		{
			$pet_info = $this->pet_model->get_pet($pet_id);
			$response = $pet_info;

			$this->response($response,200);
		}
		$error['msg'] = lang('Item not found');
		$error['code']	= self::ERROR_CODE_404;
		$this->response($error,200);
	}
	public function saveAdditionalData_post()
	{
		$this->_requireAuthToken();

		$data['pet_id']	= $this->post('pet_id') ? $this->post('pet_id') : false;
		$pet_update_key	= $this->post('key') ? $this->post('key') : false;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');
		$this->form_validation->set_rules('key', 'Pet Update Key', 'required|in_list[vaccinations,medical_examinations,physical_exam,medications,surgeries,allergies,weight,estrus,contact,veterinarian,badge_profile]');
		if ($this->form_validation->run() == FALSE) {
			$error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('pet_id'))
			{
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			if(form_error('key'))
			{
				$error['msg'] = strip_tags(form_error('key'));
			}
			$this->response($error,200);
		}

		switch ($pet_update_key) {
			case 'vaccinations':
				$this->_savePetVaccinations();
				break;
			case 'medical_examinations':
				$this->_savePetMedicalExaminations();
				break;
			case 'physical_exam':
				$this->_savePetPhysicalExam();
				break;
			case 'medications':
				$this->_savePetMedications();
				break;
			case 'surgeries':
				$this->_savePetSurgeries();
				break;
			case 'allergies':
				$this->_savePetAllergies();
				break;
			case 'weight':
				$this->_savePetWeight();
				break;
			case 'estrus':
				$this->_savePetEstrus();
				break;
			case 'contact':
				$this->_savePetContact();
				break;
			case 'veterinarian':
				$this->_savePetVeterinarian();
				break;
			case 'badge_profile':
				$this->_saveBadgeProfile();
				break;
		}
	}
	public function update_post()
	{
		$this->_requireAuthToken();

		$data['name']      		= $this->post('name') ? $this->post('name') : null;
		$data['dob']      		= $this->post('dob') ? strtotime($this->post('dob')) : null;
		$data['type']      		= $this->post('type') ? $this->post('type') : false;
		$data['breed']                  = $this->post('breed') ? $this->post('breed') : null;
		$data['sex']      		= $this->post('sex') ? $this->post('sex') : 0;
		$data['color']                  = $this->post('color') ? $this->post('color') : null;
		$data['purchase_date']          = $this->post('purchase_date') ? strtotime($this->post('purchase_date')) : null;
		$data['origin']			= $this->post('origin') ? $this->post('origin') : null;
		$data['microchip']		= $this->post('microchip') ? $this->post('microchip') : null;
		$data['modified_date']		= now();
		//$data['profile_photo']	= $this->post('profile_photo') ? $this->post('profile_photo') : null;
		$pet_id				= $this->post('id') ? $this->post('id') : false;
		$data['user_id']		= $this->_member->id;

		$qr_code                        = $this->post('qr_code') ? $this->post('qr_code') : false;
		$nfc_tag                        = $this->post('nfc_tag') ? $this->post('nfc_tag') : false;

		$location['latitude']           = $this->post('latitude') ? $this->post('latitude') : false;
		$location['longitude']          = $this->post('longitude') ? $this->post('longitude') : false;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('id', 'Pet ID', 'required');
		$this->form_validation->set_rules('name', 'Name', 'required');
		//$this->form_validation->set_rules('type', 'Pet type', 'required|integer');
		//$this->form_validation->set_rules('sex', 'Pet sex', 'integer');

		if ($this->form_validation->run() == FALSE) {
			$error_list = $this->form_validation->error_array();

			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('id'))
			{
				$error['msg'] = form_error('id');
			}
			if(form_error('name'))
			{
				$error['msg'] = form_error('name');
			}
			$this->response($error,200);
		}
//     	//upload photo and overwrite profile photo
//     	$photo = $this->_doUpload($this->config->item('pet_path'));
//     	if($photo)
//     	{
//     		//resize
//     		$this->load->helper('image');
//     		resizeImage($photo['full_path'],IMAGE_RESIZE_WIDTH,IMAGE_RESIZE_HEIGHT);

//     		$data['profile_photo'] 			= $this->config->item('api_upload_path').$this->config->item('member_path').$photo['file_name'];
//     		$file_name_array 				= explode('.', $photo['file_name']);
//     		$data['profile_photo_thumb'] 	= $this->config->item('api_upload_path').$this->config->item('member_path').$file_name_array[0].'_thumb'.$file_name_array[1];
//     	}
		//update pet
		if($qr_code){
			//$code = My_qrcode::get_tag($qr_code);
			$code = $qr_code;
			$item = $this->pet_model->get_qrcode_by('code',$code);
			if(!$item){
				$error['msg'] = lang('QR code not found');
				$error['code'] = self::ERROR_CODE_404;
				$this->response($error,200);
			}

			if(!empty($item->pet_id) && $item->pet_id!=$pet_id){
				$error['msg'] = lang('QR Code in use');
				$error['code'] = self::ERROR_CODE_ITEM_IN_USE;
				$this->response($error,200);
			}

			$this->pet_model->update_qrcode($code, $pet_id);

			if($location['latitude'] && $location['longitude']){
				$this->pet_model->update_pet_location($location, $pet_id, 'qr_code');
			}
		}

		if($nfc_tag){
			//$code = My_nfc::get_tag($nfc_tag);
			$code = $nfc_tag;
			$item = $this->pet_model->get_nfc_by('code',$code);
			if(!$item){
				$error['msg'] = lang('NFC tag not found');
				$error['code'] = self::ERROR_CODE_404;
				$this->response($error,200);
			}

			if(!empty($item->pet_id) && $item->pet_id!=$pet_id){
				$error['msg'] = lang('NFC tag in use');
				$error['code'] = self::ERROR_CODE_ITEM_IN_USE;
				$this->response($error,200);
			}

			$this->pet_model->update_nfc($code, $pet_id);

			if($location['latitude'] && $location['longitude']){
				$this->pet_model->update_pet_location($location, $pet_id, 'qr_code');
			}
		}

		$this->pet_model->update($data,$pet_id);


		$pet_info = $this->pet_model->get_pet($pet_id);
		$response = $pet_info;

		$this->response($response,200);

	}
	public function updatePetPhoto_post()
	{
		$this->_requireAuthToken();
		$pet_id 	= $this->post('pet_id') ? $this->post('pet_id') : 0;

		$token = $this->_member->token;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('pet_id', 'Pet', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] 	= $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			$error['msg'] = 'Pet id is required';
			$this->response($error,200);
		}

		log_message('info','===============BEFORE UPLOAD PROFILE PHOTO===================');
		log_message('info', "Token:$token");
		log_message('info','===============BEFORE UPLOAD PROFILE PHOTO================');

		$pet = $this->pet_model->get_pet($pet_id);

		if( $pet ) {
			//upload photo and overwrite profile photo
			//$photo = $this->_doUpload($this->config->item('pet_path'));
			$petProfileField = (isset($_FILES['file']) && !empty($_FILES['file'])) ? 'file' : '';
			$petProfileField = (isset($_FILES['profile_photo']) && !empty($_FILES['profile_photo'])) ? 'profile_photo' : $petProfileField;

			$photo = $this->media_model->S3Upload( false, $petProfileField, '');

			if($photo && !empty($photo))
			{
				//resize
				/*$this->load->helper('image');
                resizeImage($photo['full_path'],IMAGE_RESIZE_WIDTH,IMAGE_RESIZE_HEIGHT);

                $data['profile_photo'] = $this->config->item('api_upload_path').$this->config->item('pet_path').$photo['file_name'];
                $file_name_array 				= explode('.', $photo['file_name']);
                $data['profile_photo_thumb'] 	= $this->config->item('api_upload_path').$this->config->item('pet_path').$file_name_array[0].'_thumb.'.$file_name_array[1];*/
				$data['profile_photo'] 			= $photo['uri'];
				$data['profile_photo_thumb'] 	= $photo['uri_thumb'];

				$removePhoto = array();
				if($photo['uri']) {
					$removePhoto['profile_photo'] 		= $pet['information']->profile_photo;
					$removePhoto['profile_photo_thumb'] = $pet['information']->profile_photo_thumb;
					$this->media_model->removeByKeyValue($removePhoto);
				}
				//update data
				$this->pet_model->update($data,$pet_id);

				// Get the updated data
				$petInfo = $this->pet_model->get_pet($pet_id);

				$response['msg'] = 'Update successful';
				$response['items'] = $petInfo;
				log_message('info','===============AFTER UPLOAD PROFILE PHOTO===================');
				log_message('info', "Token:$token");
				log_message('info','===============AFTER UPLOAD PROFILE PHOTO================');
				$this->response($response,200);
			}
			else {
				$error['code'] = self::ERROR_CODE_FILE_ERROR;
				$error['msg']  = lang('File error or not allow');
				$this->response($error,200);
			}
		}
	}
	public function deletePetPhoto_post()
	{
		$this->_requireAuthToken();
		$pet_id 	= $this->post('pet_id') ? $this->post('pet_id') : 0;

		$token = $this->_member->token;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('pet_id', 'Pet', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] 	= $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			$error['msg'] = 'Pet id is required';
			$this->response($error,200);
		}

		$pet = $this->pet_model->get_pet($pet_id);

		if( $pet ) {
			//update data
			$data['profile_photo'] = null;
			$data['profile_photo_thumb'] = null;
			$this->pet_model->update($data, $pet_id);

			$removePhoto = array();
			$removePhoto['profile_photo'] 		= $pet['information']->profile_photo;
			$removePhoto['profile_photo_thumb'] = $pet['information']->profile_photo_thumb;
			$this->media_model->removeByKeyValue($removePhoto);

			// Get Updated data
			$petInfo = $this->pet_model->get_pet($pet_id);
			$response['msg'] = 'Delete successful';
			$response['items'] = $petInfo;
			$this->response($response,200);

			$response['msg'] = 'Update successful';
			$response['items'] = $petInfo;
			$this->response($response,200);
		}
	}
	public function myPets_post()
	{
		$this->_requireAuthToken();

		$data = array();

		$start 			= $this->post('start') ? $this->post('start') : 0;
		$limit 			= $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
		$pet_type 		= $this->post('type') ? $this->post('type') : false;
		$keyword 		= $this->post('keyword') ? $this->post('keyword') : false;
		$user_id		= $this->_member->id;

		$items 			= $this->pet_model->get_list_pets('all',$start, $limit , $keyword , $pet_type , $user_id);
		$data['totalItem'] 	= $this->pet_model->get_list_pets('count',$start, $limit , $keyword , $pet_type , $user_id);
		$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
		$data['limit']		= intval($limit);
		$data['items'] 		= array();

		if(!empty($items) )
		{
			$pet_types = $this->pet_model->get_pet_types();
			if(!empty($pet_types))
			{
				foreach($pet_types as $key => $pt)
				{
					foreach($items as $ikey => $item)
					{
						if($item->type == $pt->id)
						{
							$pet_detail =  $this->pet_model->get_pet($item->id,true,true,true,true,true,true,true,true,true,true,true);

							$pet_types[$key]->list_pets[] = $pet_detail;
							unset($items[$ikey]);
						}
					}
					if(!isset($pet_types[$key]->list_pets)){
						unset($pet_types[$key]);
					}
					else
					{
						$pet_types[$key] = format_output_data($pt);
					}
				}
			}
			$result = format_output_data($pet_types);
			if(!empty($result))
			{
				foreach($result as $key => $item)
				{
					$data['items'][] = $item;
				}
			}
		}

		$this->response($data,200);
	}

	function addQRCode_post(){
		$this->_requireAuthToken();

		$pet_id 		= $this->post('pet_id')? $this->post('pet_id') : 0;
		$qr_code 		= $this->post('qr_code')? $this->post('qr_code') : NULL;
		$brand 			= $this->post('brand')? $this->post('brand') : NULL;
		$type 			= $this->post('type')? $this->post('type') : 1;
		$description 	= $this->post('description')? $this->post('description') : NULL;
 		$latitude 		= $this->post('latitude') ? $this->post('latitude') : false;
		$longitude 		= $this->post('longitude') ? $this->post('longitude') : false;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');
		$this->form_validation->set_rules('qr_code', 'QR Code', 'required');
		// $this->form_validation->set_rules('brand', 'Manufacturer/Brand', 'required');	
		// $this->form_validation->set_rules('type', 'Type', 'required|integer');	
		// $this->form_validation->set_rules('description', 'Description', 'required');	
		$this->form_validation->set_rules('latitude', 'Latitude', 'numeric');
		$this->form_validation->set_rules('longitude', 'longitude', 'numeric');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('pet_id'))
			{
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			if(form_error('qr_code'))
			{
				$error['msg'] = strip_tags(form_error('qr_code'));
			}
			if(form_error('brand'))
			{
				$error['msg'] = strip_tags(form_error('brand'));
			}
			if(form_error('type'))
			{
				$error['msg'] = strip_tags(form_error('type'));
			}
			if(form_error('description'))
			{
				$error['msg'] = strip_tags(form_error('description'));
			}
			$this->response($error,200);
		}

		$code =$qr_code;
		$item = $this->pet_model->get_qrcode_by('code',$code);
		if(!$item){
			$error['msg'] = lang('QR code not found');
			$error['code'] = self::ERROR_CODE_404;
			$this->response($error,200);
		}

		if(!empty($item->pet_id) && $item->pet_id!=$pet_id){
			$error['msg'] = lang('QR Code in use');
			$error['code'] = self::ERROR_CODE_ITEM_IN_USE;
			$this->response($error,200);
		}

		if(!empty($item->pet_id) && $item->pet_id==$pet_id){
			$error['msg'] = lang('QR Code already link to this pet');
			$error['code'] = self::ERROR_CODE_ITEM_IN_USE;
			$this->response($error,200);
		}

		// upload image
		$photo = '';
		$petProfileField = (isset($_FILES['file']) && !empty($_FILES['file'])) ? 'file' : '';
		if($petProfileField){
			$dataPhoto = $this->media_model->S3Upload( false, $petProfileField, '');

			if($dataPhoto && !empty($dataPhoto))
			{
				$photo 			= $dataPhoto['uri'];
				$removePhoto 	= array();				
				if($dataPhoto['uri'] && isset($item->photo)) {
					$removePhoto['photo'] = $item->photo;
					$this->media_model->removeByKeyValue($removePhoto);
				}
			}
			else {
				$error['code'] = self::ERROR_CODE_FILE_ERROR;
				$error['msg']  = lang('File error or not allow');
				$this->response($error,200);
			}
		}		

		$dataInsert = array(
			'pet_id' 		=> $pet_id,
			//'badge_id' 		=> $brand,
			'type_id' 		=> $type,
			'description' 	=> $description,
			'latitude' 		=> $latitude,
			'longitude' 	=> $longitude
		);

		if($photo){
			$dataInsert = array_merge($dataInsert, array('photo' => $photo));
		}

		$this->pet_model->update_qrcode($code, $dataInsert);
		$data = $this->pet_model->get_pet($pet_id, true,true,true,true,true,true,true,true,true,true,true);
		$response = array(
			'msg'=> lang('Add QR code success'),
			'tag' => $code
		);
		foreach ($data as $key=>$value){
			$response = array_merge($response, array($key => $data[$key]));
		}

		$this->response($response,200);
	}

	function addNFCTag_post(){
		$this->_requireAuthToken();
		$pet_id = $this->post('pet_id')? $this->post('pet_id') : 0;
		$nfc_tag = $this->post('nfc_tag')? $this->post('nfc_tag') : 0;
		$latitude = $this->post('latitude') ? $this->post('latitude') : false;
		$longitude = $this->post('longitude') ? $this->post('longitude') : false;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');
		$this->form_validation->set_rules('nfc_tag', 'NFC Tag', 'required');
		$this->form_validation->set_rules('latitude', 'Latitude', 'numeric');
		$this->form_validation->set_rules('longitude', 'longitude', 'numeric');
		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('pet_id'))
			{
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			if(form_error('nfc_tag'))
			{
				$error['msg'] = strip_tags(form_error('nfc_tag'));
			}
			if(form_error('latitude'))
			{
				$error['msg'] = strip_tags(form_error('latitude'));
			}
			if(form_error('longitude'))
			{
				$error['msg'] = strip_tags(form_error('longitude'));
			}
			$this->response($error,200);
		}
		//$code = My_nfc::get_tag($nfc_tag);
		$code = $nfc_tag;
		$item = $this->pet_model->get_nfc_by('code',$code);
		if(!$item){
			$error['msg'] = lang('NFC tag not found');
			$error['code'] = self::ERROR_CODE_404;
			$this->response($error,200);
		}

		if(!empty($item->pet_id) && $item->pet_id!=$pet_id){
			$error['msg'] = lang('NFC tag in use');
			$error['code'] = self::ERROR_CODE_ITEM_IN_USE;
			$this->response($error,200);
		}

		$this->pet_model->update_nfc($code, $pet_id, $latitude, $longitude);
		$data = $this->pet_model->get_pet($pet_id, true,true,true,true,true,true,true,true,true,true,true);

		$response = array(
			'msg'=> lang('Add NFC tag success'),
			'tag' => $code
		);
		foreach ($data as $key=>$value){
			$response = array_merge($response, array($key => $data[$key]));
		}

		$this->response($response,200);
	}

	function getQRCode_post(){
		$this->_requireAuthToken();
		$qr_code = $this->post('qr_code')? $this->post('qr_code') : 0;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('qr_code', 'QR Code', 'required');
		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('qr_code'))
			{
				$error['msg'] = strip_tags(form_error('qr_code'));
			}
			$this->response($error,200);
		}
		//$code = My_qrcode::get_tag($qr_code);
		$code = $qr_code;
		$item = $this->pet_model->get_qrcode_by('code', $code);
		if(!$item || ($item && $item->pet_id == NULL)){
			$error['msg'] = lang('Item not found');
			$error['code']	= self::ERROR_CODE_404;
			$this->response($error,200);
		}

		$data['items'] = $item;

		$this->response($data,200);
	}

	function getNFCTag_post(){
		$this->_requireAuthToken();
		$nfc_tag = $this->post('nfc_tag')? $this->post('nfc_tag') : 0;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('nfc_tag', 'NFC tag', 'required');
		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('nfc_tag'))
			{
				$error['msg'] = strip_tags(form_error('nfc_tag'));
			}
			$this->response($error,200);
		}
		//$code = My_nfc::get_tag($nfc_tag);
		$code = $nfc_tag;
		$item = $this->pet_model->get_nfc_by('code', $code);
		if(!$item || ($item && $item->pet_id == NULL)){
			$error['msg'] = lang('Item not found');
			$error['code']	= self::ERROR_CODE_404;
			$this->response($error,200);
		}

		$data['items'] = $item;

		$this->response($data,200);
	}

	function removeQRCode_post(){
		$this->_requireAuthToken();
		$pet_id = $this->post('pet_id')? $this->post('pet_id') : 0;
		$id = $this->post('id')? $this->post('id') : 0;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');
		// $this->form_validation->set_rules('id', 'badge ID', 'required|integer');
		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('pet_id'))
			{
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			if(form_error('id'))
			{
				$error['msg'] = strip_tags(form_error('id'));
			}
			$this->response($error,200);
		}

		if($id){
			$item = $this->pet_model->get_qrcode_owner($id, $pet_id);
		}else {
			$item = $this->pet_model->get_qrcode_by('pet_id',$pet_id);
		}
		
		if(!$item){
			$error['msg'] = lang('QR code not found');
			$error['code'] = self::ERROR_CODE_404;
			$this->response($error,200);
		}

		if(isset($item->photo) && !empty($item->photo)) {
           $removePhoto['photo'] = $item->photo;
           $this->media_model->removeByKeyValue($removePhoto);
       	}

		$this->pet_model->unlink_qrcode($pet_id, $id);

		$this->response(array('msg'=> lang('Remove QR code success')),200);
	}

	function removeNFCTag_post(){
		$this->_requireAuthToken();
		$pet_id = $this->post('pet_id')? $this->post('pet_id') : 0;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');
		$this->form_validation->set_rules('nfc_tag', 'NFC Tag', 'required');
		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('pet_id'))
			{
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}

		$item = $this->pet_model->get_nfc_by('pet_id',$pet_id);
		if(!$item){
			$error['msg'] = lang('Item not found');
			$error['code'] = self::ERROR_CODE_404;
			$this->response($error,200);
		}

		$this->pet_model->unlink_nfc($code, $pet_id);

		$this->response(array('msg'=> 'Remove NFC tag success', 'tag' => $code),200);
	}

	function getLastLocations_post(){
		$this->_requireAuthToken();
		$pet_id = $this->post('pet_id')? $this->post('pet_id') : 0;


		$data = array();

		$start 			= $this->post('start') ? $this->post('start') : 0;
		$limit 			= $this->post('limit') ? $this->post('limit') : 3;

		$items 			= $this->pet_model->get_scan_location('all',$start, $limit , $pet_id);
		$data['totalItem'] 	= $this->pet_model->get_scan_location('count',$start, $limit , $pet_id);
		$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
		$data['limit']		= intval($limit);
		$data['items'] 		= array();

		if (empty($items)) {
			$error['msg'] = lang('Last location not found');
			$error['code'] = self::ERROR_CODE_404;
			$this->response($error,200);
		}

		$user_location = get_user_location($this->_member->id);
		$timezone = 'Asia/Singapore';

		if($user_location && sizeof($user_location)> 1){
			$lat = $user_location['latitude'];
			$lng = $user_location['longitude'];
			$timezone = get_timezone_from_location($lat, $lng, now());
		}

		foreach ($items as $key=>$item){
			$items[$key] = format_output_data($item, false, $timezone);
		}

		$data['items'] = $items;

		$this->response($data,200);
	}

	function delete_post()
	{
		$this->_requireAuthToken();
		$pet_id = $this->post('pet_id')? $this->post('pet_id') : 0;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');
		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('pet_id'))
			{
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error, 200);
		}

		$pet = $this->pet_model->get_pet($pet_id);

		if( $pet ) {
			$removePhoto = array();
			$removePhoto['profile_photo'] 		= $pet['information']->profile_photo;
			$removePhoto['profile_photo_thumb'] = $pet['information']->profile_photo_thumb;
			$this->media_model->removeByKeyValue($removePhoto);

			//unlnink badge
			$items = $this->pet_model->get_qrcode_by('pet_id', $pet_id);
			if(isset($items) && !empty($items)){
				foreach ($items as $key => $item) {
					if(isset($item->photo) && !empty($item->photo)) {
			           $removePhoto['photo'] = $item->photo;
			           $this->media_model->removeByKeyValue($removePhoto);
			       	}
				}
			}
			$this->pet_model->unlink_qrcode($pet_id);

			//delete pet
			$this->pet_model->delete($pet_id);
			//log
			log_message("info","===========================");
			log_message("info", "DELETE PET ID $pet_id");
			log_message("info","===========================");

			$this->response(array('msg'=> 'Delete success'), 200);
		} else {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			$error['msg'] = 'Pet not found';
			$this->response($error, 200);
		}
	}
	public function deleteAdditionalData_post()
	{
		$this->_requireAuthToken();

		$id				= $this->post('id') ? $this->post('id') : false; // id of property
		$pet_update_key	= $this->post('key') ? $this->post('key') : false;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('id', 'ID', 'required|integer');
		$this->form_validation->set_rules('key', 'Pet Update Key', 'required|in_list[vaccinations,medical_examinations,physical_exam,medications,surgeries,allergies,weight,estrus,contact,veterinarian]');
		if ($this->form_validation->run() == FALSE) {
			$error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('id'))
			{
				$error['msg'] = strip_tags(form_error('id'));
			}
			if(form_error('key'))
			{
				$error['msg'] = strip_tags(form_error('key'));
			}
			$this->response($error,200);
		}

		switch ($pet_update_key) {
			case 'vaccinations':
				$table_name = "pet_vaccinations";
				break;
			case 'medical_examinations':
				$table_name = "pet_medical_examinations";
				break;
			case 'physical_exam':
				$table_name = "pet_physical_exams";
				break;
			case 'medications':
				$table_name = "pet_medications";
				break;
			case 'surgeries':
				$table_name = "pet_surgeries";
				break;
			case 'allergies':
				$table_name = "pet_allergies";
				break;
			case 'weight':
				$table_name = "pet_weight";
				break;
			case 'estrus':
				$table_name = "pet_estrus";
				break;
			case 'contact':
				$table_name = "pet_contact";
				break;
			case 'veterinarian':
				$table_name = "pet_veterinarian";
				break;
		}
		if($this->pet_model->get_pet_additional_data($table_name,$id))
		{
			if($this->pet_model->delete_pet_additional_data($table_name,$id) )
			{
				$response['msg']  = lang('delete successful');
				$this->response($response,200);
			}
			else
			{
				$error['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
				$error['msg']  = lang('delete problem');
				$this->response($error,200);
			}
		}
		else
		{
			$error['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
			$error['msg']  = lang('Item not found');
			$this->response($error,200);
		}

	}
	private function  _doUpload($folder_path)
	{
		$this->load->library('image_lib');
		if(!empty($_FILES))
		{
			$this->load->helper('string');
			foreach ($_FILES as $key => $file) {
				if( (!empty($file) && $file['error'] == 0) && ($key == 'file' || $key == 'profile_photo'))
				{
					$file_name 			= basename($file['name']);
					$ext 				= substr($file_name, strrpos($file_name, '.') + 1);
					$custom_filename 	= strtolower(random_string('alnum',20)."_".$key.".".$ext);

					$config['upload_path']          = $this->config->item('api_upload_path').$folder_path;
					$config['allowed_types']        = 'jpg|png';
					$config['file_name']			= $custom_filename;

					$this->load->library('upload', $config);
					if ( ! $this->upload->do_upload($key))
					{
						$upload_errors = array('error' => $this->upload->display_errors());

						$error['code'] = self::ERROR_CODE_UPLOAD_IMAGE_FAIL;
						$error['msg'] = lang('Error: A problem occurred during file upload!');
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
	/**
	 *
	 * @param string $type
	 * @param number $id
	 *file_name

	private function _savePetInformation(){

	$data['name']      		= $this->post('name') ? $this->post('name') : null;
	$data['dob']      		= $this->post('dob') ? strtotime($this->post('dob')) : false;
	$data['type']      		= $this->post('type') ? $this->post('type') : false;
	$data['breed']      	= $this->post('breed') ? $this->post('breed') : null;
	$data['sex']      		= $this->post('sex') ? $this->post('sex') : 0;
	$data['color']      	= $this->post('color') ? $this->post('color') : null;
	$data['purchase_date']	= $this->post('purchase_date') ? strtotime($this->post('purchase_date')) : false;
	$data['origin']			= $this->post('origin') ? strtotime($this->post('origin')) : false;
	$data['profile_photo']	= $this->post('profile_photo') ? $this->post('profile_photo') : null;
	$data['user_id']		= $this->_member->id;
	$id						= $this->post('id') ? $this->post('id') : 0;

	$this->form_validation->set_rules('name', 'Name', 'required');
	$this->form_validation->set_rules('type', 'Pet type', 'integer');
	$this->form_validation->set_rules('sex', 'Pet sex', 'integer');

	if ($this->form_validation->run() == FALSE) {
	$error_list = $this->form_validation->error_array();

	$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
	if(form_error('pet_update_key'))
	{
	$error['msg'] = form_error('pet_update_key');
	}
	if(form_error('name'))
	{
	$error['msg'] = form_error('name');
	}
	if(form_error('type'))
	{
	$error['msg'] = form_error('type');
	}
	if(form_error('sex'))
	{
	$error['msg'] = form_error('sex');
	}
	$this->response($error,200);
	}
	//upload photo and overwrite profile photo
	$photo = $this->_doUpload($this->config->item('pet_path'));
	if($photo)
	{
	//resize
	$this->load->helper('image');
	resizeImage($photo['full_path'],IMAGE_RESIZE_WIDTH,IMAGE_RESIZE_HEIGHT);

	$data['profile_photo'] 			= $this->config->item('api_upload_path').$this->config->item('member_path').$photo['file_name'];
	$file_name_array 				= explode('.', $photo['file_name']);
	$data['profile_photo_thumb'] 	= $this->config->item('api_upload_path').$this->config->item('member_path').$file_name_array[0].'_thumb'.$file_name_array[1];
	}

	if($id)
	{
	//update pets table
	$this->pet_model->update($data,$id);
	$pet_info = $this->pet_model->get_pet(id);
	$response = $pet_info;

	$this->response($response,200);
	}
	else
	{
	//add pet table
	$pet_id = $this->pet_model->add($data);
	if($pet_id)
	{
	$pet_info = $this->pet_model->get_pet($pet_id);
	$response = $pet_info;

	$this->response($response,200);
	}
	else
	{
	$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
	$error['msg'] 	= lang('Item not found');
	$this->response($error,404);
	}
	}
	}
	 */
	private function _savePetVaccinations(){

		$data['name']      		= $this->post('name') ? $this->post('name') : null;
		$data['use_date']  		= $this->post('use_date') ? strtotime($this->post('use_date')) : false;
		$data['clinic']    		= $this->post('clinic') ? $this->post('clinic') : null;
		$data['notes']    		= $this->post('notes') ? $this->post('notes') : null;
		$id      				= $this->post('id') ? $this->post('id') : false;
		$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;

		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('use_date', 'Use Date', 'required');
		$this->form_validation->set_rules('clinic', 'Clinic', 'required');
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code']	= self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('name')){
				$error['msg'] = strip_tags(form_error('name'));
			}
			if(form_error('use_date')){
				$error['msg'] = strip_tags(lang('pet_date_required'));
			}
			if(form_error('clinic')){
				$error['msg'] = strip_tags(form_error('clinic'));
			}
			if(form_error('pet_id')){
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		if($id)
		{
			//update pet_vaccinations table
			$this->pet_model->update_pet_vaccinations($data,$id);

			$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($data['pet_id']);
			$this->response($response,200);
		}
		else
		{
			//add pet_vaccinations table
			$id = $this->pet_model->add_pet_vaccinations($data);
			if($id)
			{
				$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
				$response = $pet_info;
				$this->pet_model->update_pet_modified_date($data['pet_id']);
				$this->response($response,200);
			}
			else
			{
				$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
				$error['msg'] 	= lang('Item not found');
				$this->response($error,200);
			}
		}
	}
	private function _savePetMedicalExaminations(){

		$data['type']      		= $this->post('type') ? $this->post('type') : null;
		$data['reason']      	= $this->post('reason') ? $this->post('reason') : null;
		$data['clinic']    		= $this->post('clinic') ? $this->post('clinic') : null;
		$data['doctor_name']    = $this->post('doctor_name') ? $this->post('doctor_name') : null;
		$data['date']  			= $this->post('date') ? strtotime($this->post('date')) : now();
		$data['notes']    		= $this->post('notes') ? $this->post('notes') : null;
		$data['result']    		= $this->post('result') ? $this->post('result') : null;
		$data['result_date']  	= $this->post('result_date') ? strtotime($this->post('result_date')) : now();
		$data['follow_up']    	= $this->post('follow_up') ? $this->post('follow_up') : null;//need review
		$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;

		$id      				= $this->post('id') ? $this->post('id') : false;

		$this->form_validation->set_rules('type', 'Type', 'required');
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('type')){
				$error['msg'] = strip_tags(form_error('type'));
			}
			if(form_error('pet_id')){
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		if($id)
		{
			//update pet_vaccinations table
			$this->pet_model->update_pet_medical_examinations($data,$id);
			$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($data['pet_id']);
			$this->response($response,200);
		}
		else
		{
			//add pet_vaccinations table
			$id = $this->pet_model->add_pet_medical_examinations($data);
			if($id)
			{
				$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
				$response = $pet_info;
				$this->pet_model->update_pet_modified_date($data['pet_id']);
				$this->response($response,200);
			}
			else
			{
				$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
				$error['msg'] 	= lang('Item not found');
				$this->response($error,200);
			}
		}
	}
	private function _savePetPhysicalExam(){

		$data['perform_by']     = $this->post('perform_by') ? $this->post('perform_by') : null;
		$data['clinic']    		= $this->post('clinic') ? $this->post('clinic') : null;
		$data['weight']      	= $this->post('weight') ? $this->post('weight') : null;
		$data['weight_unit']    = $this->post('weight_unit') ? $this->post('weight_unit') : 'kgs';
		$data['temperature']    = $this->post('temperature') ? $this->post('temperature') : null;
		$data['pulse']    		= $this->post('pulse') ? $this->post('pulse') : null;
		$data['notes']    		= $this->post('notes') ? $this->post('notes') : null;
		$data['follow_up'] 		= $this->post('follow_up') ? $this->post('follow_up') : null;
		$data['date'] 			= $this->post('date') ? strtotime($this->post('date')) : null;
		$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;

		$id      				= $this->post('id') ? $this->post('id') : false;

		$this->form_validation->set_rules('perform_by', 'Perform By', 'required');
		$this->form_validation->set_rules('date', 'Date', 'required');
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('perform_by')){
				$error['msg'] = strip_tags(form_error('perform_by'));
			}
			if(form_error('pet_id')){
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		if($id)
		{
			//update pet_vaccinations table
			$this->pet_model->update_pet_physical_exams($data,$id);
			$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($data['pet_id']);
			$this->response($response,200);
		}
		else
		{
			//add pet_vaccinations table
			$id = $this->pet_model->add_pet_physical_exams($data);
			if($id)
			{
				$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
				$response = $pet_info;
				$this->pet_model->update_pet_modified_date($data['pet_id']);
				$this->response($response,200);
			}
			else
			{
				$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
				$error['msg'] 	= lang('Item not found');
				$this->response($error,200);
			}
		}
	}
	private function _savePetMedications(){

		$data['name']      		= $this->post('name') ? $this->post('name') : null;
		$data['purpose']                = $this->post('purpose') ? $this->post('purpose') : null;
		$data['dosage']    		= $this->post('dosage') ? $this->post('dosage') : null;
		$data['notes']    		= $this->post('notes') ? $this->post('notes') : null;
		$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
		$id      			= $this->post('id') ? $this->post('id') : false;

		//reminder
		$data['reminder_active']        = $this->post('reminder_active') ? $this->post('reminder_active') : false;


		$times['reminder_times_per_day']= $this->post('reminder_times_per_day') ? $this->post('reminder_times_per_day') : false;
		$times['reminder_times']        = $this->post('reminder_times') ? $this->post('reminder_times') : false;

		$data ['reminder_times']        = json_encode($times);

		$data['reminder_start_date']    = $this->post('reminder_start_date') ? strtotime($this->post('reminder_start_date')) : false;

		//option 0: countinous, >0: numbers of days
		$data['reminder_duration']      = $this->post('reminder_duration') ? $this->post('reminder_duration') : 0;

		//option 0: every day, 2: specific days of week
		$data['reminder_days']          = $this->post('reminder_days') ? $this->post('reminder_days') : 0;

		$this->form_validation->set_rules('name', 'Name', 'required');
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('name')){
				$error['msg'] = strip_tags(form_error('name'));
			}
			if(form_error('pet_id')){
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		if($id)
		{
			//update_pet_medications table
			$this->pet_model->update_pet_medications($data,$id);
			$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($data['pet_id']);
			$this->response($response,200);
		}
		else
		{
			//add_pet_medications table
			$id = $this->pet_model->add_pet_medications($data);
			if($id)
			{
				$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
				$response = $pet_info;
				$this->pet_model->update_pet_modified_date($data['pet_id']);
				$this->response($response,200);
			}
			else
			{
				$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
				$error['msg'] 	= lang('Item not found');
				$this->response($error,200);
			}
		}
	}
	private function _savePetSurgeries(){

		$data['type']      		= $this->post('type') ? $this->post('type') : null;
		$data['reason']      	= $this->post('reason') ? $this->post('reason') : null;
		$data['clinic']    		= $this->post('clinic') ? $this->post('clinic') : null;
		$data['doctor_name']    = $this->post('doctor_name') ? $this->post('doctor_name') : null;
		$data['surgeny_date']  	= $this->post('surgeny_date') ? strtotime($this->post('surgeny_date')) : now();
		$data['notes']    		= $this->post('notes') ? $this->post('notes') : null;
		$data['result']  		= $this->post('result') ? $this->post('result') : $this->post('result');
		$data['follow_up']    	= $this->post('follow_up') ? $this->post('follow_up') : null;//need review
		$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
		$id      				= $this->post('id') ? $this->post('id') : false;

		$this->form_validation->set_rules('type', 'Type', 'required');
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('type')){
				$error['msg'] = strip_tags(form_error('type'));
			}
			if(form_error('pet_id')){
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		if($id)
		{
			//update_pet_medications table
			$this->pet_model->update_pet_surgeries($data,$id);
			$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($data['pet_id']);
			$this->response($response,200);
		}
		else
		{
			//add_pet_medications table
			$id = $this->pet_model->add_pet_surgeries($data);
			if($id)
			{
				$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
				$response = $pet_info;
				$this->pet_model->update_pet_modified_date($data['pet_id']);
				$this->response($response,200);
			}
			else
			{
				$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
				$error['msg'] 	= lang('Item not found');
				$this->response($error,200);
			}
		}
	}
	private function _savePetAllergies(){

		$data['name']      		= $this->post('name') ? $this->post('name') : null;
		$data['reaction']      	= $this->post('reaction') ? $this->post('reaction') : null;
		$data['remedy']    		= $this->post('remedy') ? $this->post('remedy') : null;
		$data['last_update']    = $this->post('last_update') ? strtotime($this->post('last_update')) : null;
		$data['notes']    		= $this->post('notes') ? ($this->post('notes')) : null;
//     	$data['doctor_name']    = $this->post('doctor_name') ? $this->post('doctor_name') : null;
//     	$data['surgeny_date']  	= $this->post('surgeny_date') ? strtotime($this->post('surgeny_date')) : now();
//     	$data['notes']    		= $this->post('notes') ? $this->post('notes') : null;
//     	$data['result']  		= $this->post('result') ? $this->post('result') : $this->post('result');
//     	$data['follow_up']    	= $this->post('follow_up') ? $this->post('follow_up') : null;//need review
		$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
		$id      				= $this->post('id') ? $this->post('id') : false;

		//$this->form_validation->set_rules('type', 'Type', 'required');
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('name')){
				$error['msg'] = strip_tags(form_error('name'));
			}
			if(form_error('pet_id')){
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		if($id)
		{
			//update_pet_allergies table
			$this->pet_model->update_pet_allergies($data,$id);
			$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($data['pet_id']);
			$this->response($response,200);
		}
		else
		{
			//add_pet_allergies table
			$id = $this->pet_model->add_pet_allergies($data);
			if($id)
			{
				$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
				$response = $pet_info;
				$this->pet_model->update_pet_modified_date($data['pet_id']);
				$this->response($response,200);
			}
			else
			{
				$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
				$error['msg'] 	= lang('Item not found');
				$this->response($error,200);
			}
		}
	}
	private function _savePetWeight(){

		$data['date']  			= $this->post('date') ? strtotime($this->post('date')) : false;
		$data['weight']  		= $this->post('weight') ? $this->post('weight') : false;
		$data['weight_unit']	= $this->post('weight_unit') ? $this->post('weight_unit') : false;
		$data['type']    		= $this->post('type') ? $this->post('type') : null;
		$data['notes']    		= $this->post('notes') ? $this->post('notes') : null;
		$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
		$id      				= $this->post('id') ? $this->post('id') : false;// estrus id

		//$this->form_validation->set_rules('date', 'Date', 'required');
		$this->form_validation->set_rules('weight', 'Weight', 'required');
		//$this->form_validation->set_rules('type', 'Type', 'required');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('date')){
				$error['msg'] = strip_tags(lang('pet_date_required'));
			}
			if(form_error('weight')){
				$error['msg'] = strip_tags(form_error('weight'));
			}
			if(form_error('type')){
				$error['msg'] = strip_tags(form_error('type'));
			}
			$this->response($error,200);
		}
		if($id)
		{
			//update pet_vaccinations table
			$this->pet_model->update_pet_weight($data,$id);
			$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($data['pet_id']);
			$this->response($response,200);
		}
		else
		{
			//add add_pet_weight table
			$id = $this->pet_model->add_pet_weight($data);
			if($id)
			{
				$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
				$response = $pet_info;
				$this->pet_model->update_pet_modified_date($data['pet_id']);
				$this->response($response,200);
			}
			else
			{
				$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
				$error['msg'] 	= lang('Item not found');
				$this->response($error,200);
			}
		}
	}
	private function _savePetEstrus(){

		$data['start_date']  	= $this->post('start_date') ? strtotime($this->post('start_date')) : false;
		$data['end_date']  		= $this->post('end_date') ? strtotime($this->post('end_date')) : false;
		$data['notes']    		= $this->post('notes') ? $this->post('notes') : null;
		$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
		$id      				= $this->post('id') ? $this->post('id') : false;// estrus id

		$this->form_validation->set_rules('start_date', 'Start Date', 'required');
		$this->form_validation->set_rules('end_date', 'End Date', 'required');
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('start_date')){
				//$error['msg'] = strip_tags(form_error('start_date'));
				$error['msg'] = strip_tags(lang('pet_date_required'));
			}
			if(form_error('end_date')){
				//$error['msg'] = strip_tags(form_error('end_date'));
				$error['msg'] = strip_tags(lang('pet_date_required'));
			}
			if(form_error('pet_id')){
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		if($id)
		{
			//update pet_vaccinations table
			$this->pet_model->update_pet_estrus($data,$id);
			$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($data['pet_id']);
			$this->response($response,200);
		}
		else
		{
			//add add_pet_estrus table
			$id = $this->pet_model->add_pet_estrus($data);
			if($id)
			{
				$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
				$response = $pet_info;
				$this->pet_model->update_pet_modified_date($data['pet_id']);
				$this->response($response,200);
			}
			else
			{
				$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
				$error['msg'] 	= lang('Item not found');
				$this->response($error,200);
			}
		}
	}
	private function _savePetContact(){

		if($this->post('is_default') && ($this->post('name') || $this->post('phone'))){
			$data['name']  		= $this->post('name') ?  $this->post('name') : null;
			$data['phone']  		= $this->post('phone') ? $this->post('phone') : null;
			$data['alternate_phone_1']  = $this->post('alternate_phone_1') ? $this->post('alternate_phone_1') : null;
			$data['alternate_phone_2']  = $this->post('alternate_phone_2') ? $this->post('alternate_phone_2') : null;
			$data['notes']    		= $this->post('notes') ? $this->post('notes') : null;
			$data['email']    		= $this->post('email') ? $this->post('email') : null;
			$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
			$data['is_default']  	= $this->post('is_default') ? $this->post('is_default') : 0;
			$id      			= $this->post('id') ? $this->post('id') : false;// estrus id
		}
		elseif (!$this->post('is_default')) {
			$data['name']  		= $this->post('name') ?  $this->post('name') : null;
			$data['phone']  		= $this->post('phone') ? $this->post('phone') : null;
			$data['alternate_phone_1']  = $this->post('alternate_phone_1') ? $this->post('alternate_phone_1') : null;
			$data['alternate_phone_2']  = $this->post('alternate_phone_2') ? $this->post('alternate_phone_2') : null;
			$data['notes']    		= $this->post('notes') ? $this->post('notes') : null;
			$data['email']    		= $this->post('email') ? $this->post('email') : null;
			$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
			$id      			= $this->post('id') ? $this->post('id') : false;// estrus id
		}
		else{
			$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
			$data['is_default']  	= $this->post('is_default') ? $this->post('is_default') : 0;
			$id      			= $this->post('id') ? $this->post('id') : false;// estrus id
		}


		if(!$id){
			$this->form_validation->set_rules('name', 'Name', 'required');
			$this->form_validation->set_rules('phone', 'Phone', 'required');
		}

		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('name')){
				$error['msg'] = strip_tags(form_error('name'));
			}
			if(form_error('phone')){
				$error['msg'] = strip_tags(form_error('phone'));
			}
			if(form_error('pet_id')){
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		if($id)
		{
			//update pet_vaccinations table
			$this->pet_model->update_pet_contact($data,$id);
			$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($data['pet_id']);
			$this->response($response,200);
		}
		else
		{
			//add add_pet_estrus table
			$id = $this->pet_model->add_pet_contact($data);
			if($id)
			{
				$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
				$response = $pet_info;
				$this->pet_model->update_pet_modified_date($data['pet_id']);
				$this->response($response,200);
			}
			else
			{
				$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
				$error['msg'] 	= lang('Item not found');
				$this->response($error,200);
			}
		}
	}

	private function _savePetVeterinarian(){
		if($this->post('is_default') && ($this->post('clinic') || $this->post('doctor') || $this->post('phone') || $this->post('address'))){
			$data['clinic']  		= $this->post('clinic') ?  $this->post('clinic') : null;
			$data['doctor']  		= $this->post('doctor') ? $this->post('doctor') : null;
			$data['phone']    		= $this->post('phone') ? $this->post('phone') : null;
			$data['address']    	= $this->post('address') ? $this->post('address') : null;
			$data['is_default']    	= $this->post('is_default') ? $this->post('is_default') : 0;
			$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
			$id      			= $this->post('id') ? $this->post('id') : false;// estrus id
		}
		elseif (!$this->post('is_default')) {
			$data['clinic']  		= $this->post('clinic') ?  $this->post('clinic') : null;
			$data['doctor']  		= $this->post('doctor') ? $this->post('doctor') : null;
			$data['phone']    		= $this->post('phone') ? $this->post('phone') : null;
			$data['address']    	= $this->post('address') ? $this->post('address') : null;
			$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
			$id      			= $this->post('id') ? $this->post('id') : false;// estrus id
		}
		else{
			$data['is_default']    	= $this->post('is_default') ? $this->post('is_default') : 0;
			$data['pet_id']  		= $this->post('pet_id') ? $this->post('pet_id') : false;
			$id      			= $this->post('id') ? $this->post('id') : false;// estrus id
		}

		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('pet_id')){
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		if($id)
		{
			//update pet_vaccinations table
			$this->pet_model->update_pet_veterinarian($data,$id);
			$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($data['pet_id']);
			$this->response($response,200);
		}
		else
		{
			//add add_pet_estrus table
			$id = $this->pet_model->add_pet_veterinarian($data);
			if($id)
			{
				$pet_info = $this->pet_model->get_pet($data['pet_id'],true,true,true,true,true,true,true,true,true,true,true);
				$response = $pet_info;
				$this->pet_model->update_pet_modified_date($data['pet_id']);
				$this->response($response,200);
			}
			else
			{
				$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
				$error['msg'] 	= lang('Item not found');
				$this->response($error,200);
			}
		}
	}

	private function _saveBadgeProfile(){

		$data['contact_name']                   = $this->post('contact_name')  ?  $this->post('contact_name') : 0;
		$data['contact_primary_number']  	= $this->post('contact_primary_number') ? $this->post('contact_primary_number') : 0;
		$data['contact_alternate_number_1']  	= $this->post('contact_alternate_number_1') ? $this->post('contact_alternate_number_1') : 0;
		$data['contact_alternate_number_2']  	= $this->post('contact_alternate_number_2') ? $this->post('contact_alternate_number_2') : 0;
		$data['contact_email']  		= $this->post('contact_email') ? $this->post('contact_email') : 0;
		$data['veterinarian']                   = $this->post('veterinarian') ? $this->post('veterinarian') : 0;
		$data['medications']                    = $this->post('medications') ? $this->post('medications') : 0;
		$data['allergies']                      = $this->post('allergies') ? $this->post('allergies') : 0;
		$data['vaccinations']                   = $this->post('vaccinations') ? $this->post('vaccinations') : 0;
		$data['notes_check']                    = $this->post('notes_check') ? $this->post('notes_check') : 0;
		$data['notes']                          = $this->post('notes') ? $this->post('notes') : "";

		$reward_check                           = $this->post('reward_check') ? $this->post('reward_check') : 0;
		$reward_value                           = $this->post('reward_value') ? $this->post('reward_value') : "";
		$reward_currency                        = $this->post('reward_currency') ? $this->post('reward_currency') : "";
		$data['reward']                         = json_encode(array('reward_check' => $reward_check, 'reward_value' => $reward_value, 'reward_currency' => $reward_currency));

		$pet_id                                 = $this->post('pet_id') ? $this->post('pet_id') : false;

		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('pet_id')){
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		if($this->pet_model->get_pet($pet_id)){
			//update pet_settings table
			$this->pet_model->update_pet_badge_profile($data,$pet_id);
			$pet_info = $this->pet_model->get_pet($pet_id,true,true,true,true,true,true,true,true,true,true,true);
			$response = $pet_info;
			$this->pet_model->update_pet_modified_date($pet_id);
			$this->response($response,200);
		}
		$error['code']	= self::ERROR_OBJECT_NOT_FOUND;
		$error['msg'] 	= lang('Pet not found');
		$this->response($error,200);
	}

	public function getQRCodes_post(){
		$this->_requireAuthToken();

		$data 			= array();
		$pet_id 		= $this->post('pet_id')? $this->post('pet_id') : 0;
		$keyword 		= $this->post('keyword') ? $this->post('keyword') : false;
		$user_id		= $this->_member->id;
		$start 			= 0;														//$this->post('start') ? $this->post('start') : 0;		
		$limit 			= 10000;													//$this->post('limit') ? $this->post('limit') : 10000;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('pet_id'))
			{
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			$this->response($error,200);
		}
		//  get_list_badges($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $keyword = false , $pet_id = 0 , $user_id = 0)
		$items 				= $this->pet_model->get_list_qrcodes('all',$start, $limit , $keyword , $pet_id );
		$data['totalItem'] 	= $this->pet_model->get_list_qrcodes('count',$start, $limit , $keyword , $pet_id );
		$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
		$data['limit']		= intval($limit);
		$data['items'] 		= array();

		if(!empty($items)){
			$qrcode_types = $this->pet_model->get_qrcode_types();
			if(!empty($qrcode_types))
			{
				foreach($qrcode_types as $key => $qrt)
				{
					foreach($items as $ikey => $item)
					{
						if($item->type == $qrt->id)
						{
							$qrcode_detail =  $this->pet_model->get_qrcode_detail($item->id);

							$qrcode_types[$key]->list_qrcodes[] = $qrcode_detail;
							unset($items[$ikey]);
						}
					}
					if(!isset($qrcode_types[$key]->list_qrcodes)){
						unset($qrcode_types[$key]);
					}
					else
					{
						$qrcode_types[$key] = ($qrt);
					}
				}
			}
			$result = format_output_data($qrcode_types);
			if(!empty($result))
			{
				foreach($result as $key => $item)
				{
					$data['items'][] = $item;
				}
			}
		}

		$this->response($data,200);
	}

	public function getQRCodeDetail_post(){
		$this->_requireAuthToken();
		$id 			= $this->post('id')? $this->post('id') : 0;
		$code 			= $this->post('qr_code')? $this->post('qr_code') : NULL;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate

		$this->form_validation->set_rules('id', 'ID', 'integer');
		if(!$id && !$code){
			$this->form_validation->set_rules('qr_code', 'QR Code', 'required');
		}	

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('id'))
			{
				$error['msg'] = strip_tags(form_error('id'));
			}
			if(form_error('qr_code'))
			{
				$error['msg'] = strip_tags(form_error('qr_code'));
			}
			$this->response($error,200);
		}

		$item = $this->pet_model->get_qrcode_detail($id, $code);

        if ($item) {
            $response['item'] = $item;
        } else {
        	$response['msg'] = lang('QR code not found');
            $response['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;            
        }
        $this->response($response, 200);
	}
	
	public function getQRCodeTypes_post(){
		$this->_requireAuthToken();

		$start 			= $this->post('start') ? $this->post('start') : 0;		
		$limit 			= $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
		$keyword 		= $this->post('keyword') ? $this->post('keyword') : false;

		//  get_list_badges($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $keyword = false , $pet_id = 0 , $user_id = 0)
		$items 				= $this->pet_model->get_list_qrcode_types('all',$start, $limit , $keyword);
		$data['totalItem'] 	= $this->pet_model->get_list_qrcode_types('count',$start, $limit , $keyword);
		$data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
		$data['limit']		= intval($limit);
		$data['items'] 		= array();

		if(!empty($items) )
		{
			$data['items'] = $items;
			if(!empty($result))
			{
				foreach($result as $key => $item)
				{
					$data['items'][] = $item;
				}
			}
		}

		$this->response($data,200);
	}

	public function updateQRCode_post(){

        $this->_requireAuthToken();

		$pet_id 		= $this->post('pet_id')? $this->post('pet_id') : 0;
		$id 			= $this->post('id')? $this->post('id') : NULL;
		//$brand 			= $this->post('brand')? $this->post('brand') : NULL;
		$type 			= $this->post('type')? $this->post('type') : 1;
		$description 	= $this->post('description')? $this->post('description') : NULL;

		$user_id = $this->_member->id;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate

		$this->form_validation->set_rules('id', 'ID', 'required|integer');		
		$this->form_validation->set_rules('pet_id', 'Pet ID', 'required|integer');		
		$this->form_validation->set_rules('type', 'Type', 'required|integer');	
		$this->form_validation->set_rules('description', 'Description', 'required');	

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('id'))
			{
				$error['msg'] = strip_tags(form_error('id'));
			}
			if(form_error('pet_id'))
			{
				$error['msg'] = strip_tags(form_error('pet_id'));
			}
			if(form_error('type'))
			{
				$error['msg'] = strip_tags(form_error('type'));
			}
			if(form_error('description'))
			{
				$error['msg'] = strip_tags(form_error('description'));
			}
			$this->response($error,200);
		}

		
		$item = $this->pet_model->get_qrcode_by('id',$id);
		$check_pet_owner = $this->pet_model->check_pet_owner($pet_id, $user_id);
		$code =$item->code;

		if( !$item || (!empty($item->pet_id) && $item->pet_id!=$pet_id) || !$check_pet_owner){
			$error['msg'] = lang('QR code not found');
			$error['code'] = self::ERROR_CODE_404;
			$this->response($error,200);
		}

		// upload image
		$photo = '';
		$petProfileField = (isset($_FILES['file']) && !empty($_FILES['file'])) ? 'file' : '';
		if($petProfileField){
			$dataPhoto = $this->media_model->S3Upload( false, $petProfileField, '');

			if($dataPhoto && !empty($dataPhoto))
			{
				$photo 			= $dataPhoto['uri'];
				$removePhoto 	= array();				
				if($dataPhoto['uri'] && isset($item->photo)) {
					$removePhoto['photo'] = $item->photo;
					$this->media_model->removeByKeyValue($removePhoto);
				}
			}
			else {
				$error['code'] = self::ERROR_CODE_FILE_ERROR;
				$error['msg']  = lang('File error or not allow');
				$this->response($error,200);
			}
		}

		$dataUpdate = array(
			'type_id' 		=> $type,
			'description' 	=> $description,
		);
		if($photo){
			$dataUpdate = array_merge($dataUpdate, array('photo' => $photo));
		}

		$this->pet_model->update_qrcode($code, $dataUpdate);
		// $data = $this->pet_model->get_pet($pet_id, true,true,true,true,true,true,true,true,true,true,true);

		$item = $this->pet_model->get_qrcode_detail($id, $code);
		$response = array(
			'msg'=> lang('Update QR code success'),
			'item' => $item
		);
		// foreach ($data as $key=>$value){
		// 	$response = array_merge($response, array($key => $data[$key]));
		// }

		$this->response($response,200);
	}

	public function updateQRCodePhoto_post()
	{
		$this->_requireAuthToken();
		$id 		= $this->post('id') ? $this->post('id') : 0;
		$pet_id 	= $this->post('pet_id') ? $this->post('pet_id') : 0;

		$user_id = $this->_member->id;
		$token = $this->_member->token;
		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate
		$this->form_validation->set_rules('id', 'badge id', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			$error['code'] 	= $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			$error['msg'] = 'Badge id is required';
			$this->response($error,200);
		}

		log_message('info','===============BEFORE UPLOAD BADGE PHOTO===================');
		log_message('info', "Token: $token");
		log_message('info','===============BEFORE UPLOAD BADGE PHOTO================');

		$item = $this->pet_model->get_qrcode_by('id',$id);

		if( $item ) {
			//upload photo and overwrite profile photo
			//$photo = $this->_doUpload($this->config->item('pet_path'));
			$petProfileField = (isset($_FILES['file']) && !empty($_FILES['file'])) ? 'file' : '';

			$photo = $this->media_model->S3Upload( false, $petProfileField, '');

			if($photo && !empty($photo))
			{
				$data['photo'] 			= $photo['uri'];
				$removePhoto = array();				
				if($photo['uri'] && isset($item->photo)) {
					$removePhoto['photo'] 		= $item->photo;
					$this->media_model->removeByKeyValue($removePhoto);
				}
				//update data
				$this->pet_model->update_qrcode($item->code, $data);

				// Get the updated data
				$petInfo = $this->pet_model->get_pet($pet_id);

				$response['msg'] = 'Update successful';
				$response['items'] = $petInfo;
				log_message('info','===============AFTER UPLOAD PROFILE PHOTO===================');
				log_message('info', "Token:$token");
				log_message('info','===============AFTER UPLOAD PROFILE PHOTO================');
				$this->response($response,200);
			}
			else {
				$error['code'] = self::ERROR_CODE_FILE_ERROR;
				$error['msg']  = lang('File error or not allow');
				$this->response($error,200);
			}
		}
	}

	public function checkQRCodeStatus_post(){
		$this->_requireAuthToken();
		$id 			= $this->post('id')? $this->post('id') : 0;
		$code 			= $this->post('qr_code')? $this->post('qr_code') : NULL;

		$this->load->library('form_validation');
		/*Set the form validation rules*/
		$_POST = $this->post();//set this for validate

		$this->form_validation->set_rules('id', 'ID', 'integer');
		if(!$id && !$code){
			$this->form_validation->set_rules('qr_code', 'QR Code', 'required');
		}	

		if ($this->form_validation->run() == FALSE) {
			$error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
			if(form_error('id'))
			{
				$error['msg'] = strip_tags(form_error('id'));
			}
			if(form_error('qr_code'))
			{
				$error['msg'] = strip_tags(form_error('qr_code'));
			}
			$this->response($error,200);
		}
		
		$item = $this->pet_model->get_qrcode_detail($id, $code);

		if(!$item){
			$error['msg'] = lang('QR code not found');
			$error['code'] = self::ERROR_CODE_404;
			$this->response($error,200);
		}

		if(!empty($item->pet_id)){
			$error['msg'] = lang('QR Code in use');
			$error['code'] = self::ERROR_CODE_ITEM_IN_USE;
			$this->response($error,200);
		}

		$response['msg'] = lang('QR Code not use');
        // $error['code'] = self::ERROR_CODE_ITEM_NOT_USE;        
        $this->response($response, 200);
	}
}