<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Command extends CI_Controller{

    public function __construct() {
        parent::__construct();

        $this->load->model(array('command_model', 'media_model'));
        $this->load->helper(array('file', 'date', 'url', 'listing', 'upload', 'image'));
        $this->load->config('s3');

        if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != 'petwidget' || $_SERVER['PHP_AUTH_PW'] != '789654123') {
             header('WWW-Authenticate: Basic realm="MyProject"');
             header('HTTP/1.0 401 Unauthorized');
             die('Access Denied');
        }
    }

	public function transfer(){
		$this->_doUpdate('user_media', array('source', 'photo_thumb'));
		$this->_doUpdate('business_items', array('photo'));
		$this->_doUpdate('business_category', array('photo'));
		$this->_doUpdate('banners', array('path'));
		$this->_doUpdate('pets', array('profile_photo', 'profile_photo_thumb'));
		$this->_doUpdate('users', array('profile_photo', 'profile_photo_thumb', 'profile_background', 'profile_background_thumb'));
		$this->_doUpdate('pet_talk_category', array('photo', 'photo_thumb'));

		echo '<pre> -- Finish!!!</pre>';
        exit;
	}

	protected function getImagePath($image, $photo_fb = false){
		if(!$image){
			return false;
		}
		if($photo_fb){
			if(substr($image, 0, 26) == 'http://graph.facebook.com/'){
				return $image;
			}
		}		
		if(substr($image, 0, 26) != 'http://graph.facebook.com/'){
			return str_replace('../', '', $image);
		}
		return false;
	}

	protected function checkFileUpload($image){
		$this->media_model->checkFileUpload($image);
	}

	public function removeMediaTmp(){
		$this->media_model->removeMediaTmp();
		echo "<pre>Successful!!!</pre>";		
	}

	public function checkInfo(){
		echo var_dump(array(
			'upload'				=> $this->config->item('upload'),
			'environment' 			=> ENVIRONMENT,
			's3-bucket' 			=> $this->config->item('s3-bucket'),
			's3-path' 				=> $this->config->item('s3-path'),
			's3-uri' 				=> $this->config->item('s3-uri')
		)); 
		exit;
	}

	protected function _doUpdate($table, $field_image){
		$data = $this->command_model->getMedia($table, reset($field_image));

		echo "<pre> -- ====================================================================. </pre>";
		echo "<pre> -- ============================ ".$table." ============================. </pre>";		
		echo "<pre> -- ====================================================================. </pre>";
		if($data){
			foreach ($data as $key => $item) {
				echo "<pre> -- id: ".$item->id.". ";
				$text = array();
				$file_path = null;

				foreach ($field_image as $field) {
					$file_path = $this->getImagePath($item->$field);
					$newFileName = md5(uniqid()) . '_' . now();
					$newImage = null;
					$check = false;

					if($file_path && get_file_info($file_path)){
						$check = $this->media_model->checkFileUpload($item->$field);
						if($check){
							$newImage = $check->photo_new;
							echo $field.": already upload. ";
						}else{
							$imageExtension = pathinfo($file_path, PATHINFO_EXTENSION);
							$newImage = $this->media_model->putToS3($file_path, $newFileName.'.'.$imageExtension);

							if(!is_array($newImage)){
								$data = array(
									'photo_old' 	=> $item->$field,
									'photo_new'		=> $newImage,
									'photo_type'	=> $field,
									'photo_id'		=> $item->id,
									'table'			=> $table,
									'created_date' 	=> now(),
								);
								$this->media_model->insertMediaTmpData($data);
								echo $field.": successful - ".$newImage.". ";					
							}
						}
					}else{
						echo $field.": failure. ";
					}
					$text[] = "`".$field."` = '".$newImage."'";
				}

				echo "</pre>";
				echo "<pre> UPDATE `".$table."` SET ".implode(",", $text)." WHERE `id` = '".$item->id."';</pre>";
			}
		}
		//str_replace('../', '', $object->profile_background_thumb);		
		echo "<pre> -- ====================================================================. </pre>";
	}


	public function upload(){
		$file = 'http://app.petwidget.com/themes/public/images/logo/pet-widget-complete-logo-mini.png';
		var_dump(fileExists($file));exit;
		$path = '';
		$image = '';
		$newImage = $this->media_model->putToS3($path, $image);
		echo $newImage;
	}

	public function checkMediaPost(){
		$data['data'] = '';
		if($this->input->post()){
			$file = $this->input->post('file');
			$data['data'] = checkMediaExist($file);
		}
		$this->load->view('check_tpl', $data);
	}

	public function removeMediaPost(){
		$data['data'] = '';
		if($this->input->post()){
			$file = $this->input->post('file');
			S3_Upload::removeMedia($file);
			$data['data'] = checkMediaExist($file) ? false : true;
		}
		$this->load->view('remove_tpl', $data);
	}


	public function applyImageTmp(){
		$imageTmp = $this->media_model->getMediaTmpData();

		if($imageTmp){
			foreach ($imageTmp as $key => $item) {
				$table = $item->table;
				$field_key = $item->photo_type;
				$field_value= $item->photo_old;
				$data = array(
					$field_key => $item->photo_new
				);
				$this->media_model->applyMediaFromTmp($table, $field_key, $field_value, $data);
			}
			echo "<pre>Successful!!!</pre>";
		}
	}

	public function checkMedia($table = 'user_media'){

		// $myfile = fopen("check_media_".now().".txt", "w");
		switch ($table) {
			case 'business_items':
				$this->_doCheck('business_items', array('photo'));
				break;
			case 'business_category':
				$this->_doCheck('business_category', array('photo'));
				break;
			case 'banners':
				$this->_doCheck('banners', array('path'));
				break;
			case 'pets':
				$this->_doCheck('pets', array('profile_photo', 'profile_photo_thumb'));
				break;
			case 'users':
				$this->_doCheck('users', array('profile_photo', 'profile_photo_thumb', 'profile_background', 'profile_background_thumb'));
				break;
			case 'pet_talk_category':
				$this->_doCheck('pet_talk_category', array('photo', 'photo_thumb'));
				break;
			default:
				$this->_doCheck('user_media', array('source', 'photo_thumb'));
				break;
		}

		// $this->_doCheck('user_media', array('source', 'photo_thumb'));
		// $this->_doCheck('business_items', array('photo'));
		// $this->_doCheck('business_category', array('photo'));
		// $this->_doCheck('banners', array('path'));
		// $this->_doCheck('pets', array('profile_photo', 'profile_photo_thumb'));
		// $this->_doCheck('users', array('profile_photo', 'profile_photo_thumb', 'profile_background', 'profile_background_thumb'));
		// $this->_doCheck('pet_talk_category', array('photo', 'photo_thumb'));

		echo "Finish!!!\r\n";
		// fwrite($myfile, "Successful!!!\r\n");
		// fclose($myfile);
        exit;
	}

	public function _doCheck($table, $field_image){
		$data = $this->command_model->getMediaAll($table, reset($field_image));
		echo "<pre> -- ====================================================================. </pre>";
		echo "<pre> -- ============================ ".$table." ============================. </pre>";		
		echo "<pre> -- ====================================================================. </pre>";

		if($data){
			foreach ($data as $key => $item) {
				
				$i = 0;
				$text = array();

				if($table == 'user_media'){
					$f =  reset($field_image);
					if(!checkMediaExist($item->$f)){
						foreach ($field_image as $field) {
							$text[] = $item->$field . "\r\n";
							//S3_Upload::removeByKeyValue($item->$field);
						}
						//$this->media_model->removeMediaFromTable($table, 'id', $item->id);
						$i++;
					}
				}else{
					foreach ($field_image as $field) {
						if(!checkMediaExist($item->$field)){
							$text[] = $item->$field . "";
							//S3_Upload::removeByKeyValue($item->$field);
							//$this->media_model->removeMediaFromTable($table, 'id', $item->id, $field);
							$i++;
						}
					}
				}

				if($i > 0){
					echo "<pre>-- id: ".$item->id.". \r\n".implode(",", $text).". Done</pre>";
				}

			}
		}
		
		echo "<pre> -- ====================================================================. </pre>";
	}
}
