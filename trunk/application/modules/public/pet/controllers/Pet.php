<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pet extends Pet_Controller {
    
     var $data = array();
    public function __construct() {
        parent::__construct();
        $this->load->helper(array('site','url','language', 'image'));
        $this->load->model(array('pet_model'));
    }
    public function index()
    {
        $this->load->view("index");
    }
    
    public function tag($tag){
        if(strlen($tag) < 3){
            $this->load->view("error_404");
        }
        else{
            $pet = $this->pet_model->get_pet_from_qrcode($tag);
            if($pet){
                $data['pet'] = format_output_data($pet, true);
                $data['badgeId'] = My_qrcode::get_badgeId($pet->code);
                $data['contact'] = $this->pet_model->get_contact($pet->id);
                $data['veterinarian'] = $this->pet_model->get_veterinarian($pet->id);
                $data['medications'] = $this->pet_model->get_medications($pet->id);
                $data['medications'] = $this->pet_model->get_medications($pet->id);
                $data['allergies'] = $this->pet_model->get_allergies($pet->id);
                $data['vaccinations'] = $this->pet_model->get_vaccinations($pet->id);
                $data['pet_settings'] = $this->pet_model->get_settings($pet->id);
                //$this->load->view('pet', $data);
                
                $this->assets_css['custom_style'] = array(
					'customs.css'
                );
                $this->assets_js['page_plugin'] = array(
                	'geolocation.js'
                );
                $this->assets_js['page_script'] = array(
					'<script async defer src="https://maps.googleapis.com/maps/api/js?key=aAIzaSyCoAh4ZzBXxjeRAkolDpM_5MkaG7QwfuNc&callback=getLocation"></script>'
                );
                
                $this->template->title('Pet Widget - Search badge ID');
                $this->page_meta_description = 'A community of pet owners, and the most integrated pet centric tool that fits in your pocket. Accessible whenever, wherever.';
                $this->page_meta_keywords = 'App, Pet, Singapore, Android, iOS, Applications, Free';
                $this->page_meta_author = 'Pet Widget';
                 
                $this->render_page('pet', $data);
            }
            else{
                $this->load->view("error_404");
            }
        }
    }
    
    function saveLocation(){
        $response = new stdClass();
        
        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
        $url = $this->input->post('url');
        
        $explode_url = explode('/', $url);
        $code = end($explode_url);
        
        $pet = $this->pet_model->get_pet_from_code($code);
        
        if($pet && $pet->pet_id){
            $requestTime = strtotime(date('Y-m-d H:i:s'));  // current time
            $lastRequestTime = $pet->lastRequestTime + (1 * 60); // last request time + 1 minutes
            if( $requestTime >= $lastRequestTime ) {
                $data = $this->pet_model->update_pet_location($pet, $latitude, $longitude);
                $data = $this->pet_model->send_push_scan($code);
                $response->distance = $data;
            }
        }
        
        $response->status = true;
        $response->url = $url;
        $response->code = $code;
        $response->latitude = $latitude;
        $response->longitude = $longitude;
        echo json_encode($response);
        exit;
    }

    function searchBadgeId(){
    	$data = array();
    	if($this->input->post('badgeId')){
    		$badgeId = trim($this->input->post('badgeId'));
    		if(strlen($badgeId) > 5){
    			$pet = $this->pet_model->get_pet_from_code($badgeId);
    			if(!empty($pet->pet_id)){
    				redirect('tag/'.$badgeId);
    			}
    		}
    		$data['error'] = true;
    	}
    	
    	$this->assets_css['custom_style'] = array(
    			'customs.css'
    	);
    	
    	$this->template->title('Pet Widget - Search badge ID');
    	$this->page_meta_description = 'A community of pet owners, and the most integrated pet centric tool that fits in your pocket. Accessible whenever, wherever.';
    	$this->page_meta_keywords = 'App, Pet, Singapore, Android, iOS, Applications, Free';
    	$this->page_meta_author = 'Pet Widget';
    	
    	$this->render_page('search_badge', $data);
        //$this->load->view("search_badge",$data);
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */