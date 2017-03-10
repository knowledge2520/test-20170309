<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_media
{

	private $upload_field_name;
	private $type;
	private $path;
	private $field_image;
	private $table;
	private $item;
	private $required;
	private $data;
	private $resize_size;
	private $s3;

	function __construct($config = array())
    {	
    	$this->s3 						= new S3_Upload();
        if ( ! empty($config))
        {
            $this->upload_field_name 	= $config['upload_field_name'];
            $this->path 				= $config['path'];            
            $this->field_image 			= $config['field_image'];
            $this->type 				= isset($config['type']) ? $config['type'] : 'insert';
            $this->table_media 			= isset($config['table_media']) ? $config['table_media'] : '';
            $this->item 				= isset($config['item']) ? $config['item'] : array();
            $this->required 			= isset($config['required']) ? $config['required'] : '';
            $this->resize_size 			= isset($config['resize_size']) ? $config['resize_size'] : array();
            
        }
        else{
        	return false;
        }
    }

    function checkRequired(){
    	$CI =& get_instance();
		$CI->load->library(array('messages', 'form_validation'));
		$CI->load->helper(array('form', 'url'));
    	$image = $_FILES[$this->upload_field_name]['name'];

    	if($this->required && $this->type != 'update'){
    		if (empty($image))
	        {
	            $CI->form_validation->set_rules($this->upload_field_name, 'Photo', 'required');
	        }
    	}
    }

	function saveMediaAdmin($data){
		$CI =& get_instance();
		$CI->load->library(array('messages', 'form_validation'));
		$CI->load->helper(array('form', 'url', 'upload'));

		$this->data = $data;
		$image = $_FILES[$this->upload_field_name]['name'];

		if($this->required && $this->type != 'update'){	

	        if (empty($image))
	        {
	            $CI->form_validation->set_rules($this->upload_field_name, 'Photo', 'required');
	        }

	        if ($CI->form_validation->run()) {
	            // saving image              
	            $image_data = $this->s3->saveMedia($this->path, $this->upload_field_name, $this->resize_size);

	            if($image_data['code']){
	            	if($this->type == 'update'){
	            		$this->removeMediaAdmin($this->item);
	            	}

	            	$this->saveItemMediaAdmin($this->data, $image_data);   
	            }else{
	               	$CI->messages->add($image_data['message'], "error");
	                return false;
	            }
	        }
		}else{
			if(!empty($image)){			
			 	// saving image   
				 $image_data = $this->s3->saveMedia($this->path, $this->upload_field_name, $this->resize_size);
	            if($image_data['code']){
	            	if($this->type == 'update'){
	            		$this->removeMediaAdmin($this->item);
	            	}

	            	$this->saveItemMediaAdmin($this->data, $image_data);   
	            }else{
	                $CI->messages->add($image_data['message'], "error");
	                return false;
	            }              
			}
           
	    }

        return $this->data;
	}


	function removeMediaAdmin($item){
		if($item){
			if(sizeof($this->field_image) > 1){
				$field_image 			= $this->field_image[0];
				$field_image_thumb		= $this->field_image[1];

				$this->s3->removeByKeyValue($item->$field_image);
	            $this->s3->removeByKeyValue($item->$field_image_thumb);
			}else{
				$field_image 			= $this->field_image[0];

				$this->s3->removeByKeyValue($item->$field_image);
	            $this->s3->removeByKeyValue(str_replace("_thumb", "_file", $item->$field_image));
			}
		}		
	}

	function saveItemMediaAdmin($data, $image_data){
		if(sizeof($this->field_image) > 1){
			$field_image 						= $this->field_image[0];
			$field_image_thumb					= $this->field_image[1];

			$this->data [$field_image]        	= $image_data['uri'];
        	$this->data [$field_image_thumb]  	= $image_data['uri_thumb'];
		}else{
			$field_image 						= $this->field_image[0];

			$this->data [$field_image] 			= $image_data['uri_thumb'];
		}

        if($this->table_media){
        	$this->data ['width_source']      	= $image_data['width'];
            $this->data ['height_source']     	= $image_data['height'];
            $this->data ['width_thumb']       	= $image_data['width_thumb'];
            $this->data ['height_thumb']      	= $image_data['height_thumb'];
            $this->data ['type']              	= 'PHOTO';
        }
	}
}