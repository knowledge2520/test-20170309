<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/modules/api/api/libraries/REST_Controller.php';

class Site extends REST_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
        
        // Configure limits on our controller methods. Ensure
        // you have created the 'limits' table and enabled 'limits'
        // within application/config/rest.php
        $this->methods['user_get']['limit'] = 500; //500 requests per hour per user/key
        $this->methods['user_post']['limit'] = 100; //100 requests per hour per user/key
        $this->methods['user_delete']['limit'] = 50; //50 requests per hour per user/key
        
        //load lang
        $this->lang->load('api');
    }
    
    public function index_get()
    {
    	$data = array();
    	//get banners
    	$resultBanners = $this->db->query("SELECT * FROM banners WHERE status = 1 ORDER BY `order` ASC LIMIT 10")->result();
    	if($resultBanners)
    	{
    		foreach($resultBanners as $key => $item)
    		{
    			$resultBanners[$key] = format_output_data($item);
    		}
    		$data['banners']['items'] = $resultBanners;    		
    	}
    	
    	//get pet types
    	$resultPetType = $this->db->query("SELECT * FROM pet_types WHERE status = 1")->result();
    	if($resultPetType)
    	{
    		$data['pet_types']['items'] = $resultPetType;
    	}
    	
        $resultCountries = $this->db->query("SELECT * FROM countries WHERE status = 1")->result();
        if($resultCountries)
    	{
    		$data['countries']['items'] = $resultCountries;
    	}
        
    	$data['radius_nearby_distance'] = $this->config_items->radius_nearby_distance;
    	$data['listing_distance'] = $this->config_items->listing_distance;
    	
    	//response data
    	if(!empty($data))
    	{
    		$this->response($data,200);
    	}
    	else
    	{
    		$error['code'] 	= self::ERROR_OBJECT_NOT_FOUND;
    		$error['msg'] 	= 'Can not found items';
    		$this->response($error, 200);
    	}
    }
    public function petWidget_post(){
    	$this->_requireAuthToken();
    	//get widget items image
    	$response['widget_items'] = array(
    		array('key' => 'KEY_LISTING', 'image' => isset($this->config_items->pet_widget_image_listings) ? output_media_file($this->config_items->pet_widget_image_listings) : ''),
    		array('key' => 'KEY_MYPET', 'image' => isset($this->config_items->pet_widget_image_mypets) ? output_media_file($this->config_items->pet_widget_image_mypets) : ''),
    		array('key' => 'KEY_PETTALK', 'image' => isset($this->config_items->pet_widget_image_pettalk) ? output_media_file($this->config_items->pet_widget_image_pettalk): ''),
    		array('key' => 'KEY_PETSHOP', 'image' => isset($this->config_items->pet_widget_image_shop) ? output_media_file($this->config_items->pet_widget_image_shop) : ''),
    	);
    	//get widget new listing
    	$this->load->model('listing_model');
        
        $user_options = $this->member_model->get_user_options($this->_member->id);
        $search_distance = 60;
        
        if ($user_options) {
            $option_lock = $user_options->location_lock;
            $option_location_latitude = !empty($user_options->location_city) && explode(',', $user_options->location_city)[0] ? trim(explode(',', $user_options->location_city)[0]) : false;
            $option_location_longitude = !empty($user_options->location_city) && explode(',', $user_options->location_city)[1] ? trim(explode(',', $user_options->location_city)[1]) : false;
        }

        $user_location = array(
            'latitude' => $option_location_latitude,
            'longitude' => $option_location_longitude,
            'search_distance' => $search_distance
        );
    	$latest_listings = $this->listing_model->search_listings('all',0,4,false,$user_location,false,'id','DESC');
        
    	if($latest_listings)
    	{
    		foreach($latest_listings as $key => $listing)
    		{
    			$listing_detail = $this->listing_model->get_listing_detail($listing->id,true);
    			//setup output data
    			unset($listing_detail->website);
    			unset($listing_detail->hour);
    			unset($listing_detail->phone);
    			unset($listing_detail->created_date);
    			unset($listing_detail->created_time);
    			unset($listing_detail->status);
    			unset($listing_detail->user_id);
    			unset($listing_detail->latitude);
    			unset($listing_detail->longitude);
    			
                        $listing_detail->photo_width = !empty($listing_detail->photo) ? getimagesize($listing_detail->photo)[0] : 0;
                        $listing_detail->photo_height = !empty($listing_detail->photo) ? getimagesize($listing_detail->photo)[1] : 0;

    			$latest_listings[$key] = $listing_detail;
    		}		
    	}
    	
    	//get hot topics
    	$this->load->model('pettalk_model');
    	$hot_topic = $this->pettalk_model->get_hot_topic(0,4);
    	if($hot_topic){
    		foreach($hot_topic as $key => $topic)
    		{
    			//get comments
    			$total_comment = $this->pettalk_model->get_comments_topics($topic->id,'count');
    			$topic->total_comment = $total_comment;
    			
    			//get total like
    			$total_like = $this->pettalk_model->get_like_topics($topic->id,0,'count');
    			$topic->total_like = $total_like;
    			
    			//get personal like
    			$like_type = $this->pettalk_model->get_user_like_topic($this->_member->id,$topic->id);
    			if($like_type){    				
    				$topic->like_type = strval($like_type->type);
    			}
    			else{
    				$topic->like_type = '';
    			}
    			
    			//get author
    			if(!empty($topic->created_by) )
    			{
    				$this->load->model('member_model');
    				$author = $this->member_model->getMemberByMemberID($topic->created_by);
    				if($author)
    				{
    					$author= format_output_data($author);
    					$author = array(
    							'id'			=> $author->id,
    							'first_name' 	=> $author->first_name,
    							'last_name'		=> $author->last_name,
    							'profile_photo' => $author->profile_photo,
    					);
    					$topic->created_by = $author;
    				}
    			
    			}
    			
    			$hot_topic[$key] = format_output_data($topic);
    		}
    	}
    	
    	//get latest review
    	$this->load->model('review_model');
    	$latest_review = $this->review_model->get_reviews('all',0,4,'id','DESC');
    	if($latest_review){
    		foreach($latest_review as $rkey =>$review){
    			$listing_detail = $this->listing_model->get_listing_detail($review->business_id,true,true,true,true,true,true);
    			$review->business_info = $listing_detail;
    			$latest_review[$rkey] = format_output_data($review);
    		}
    	}
    	//get popular product
    	$this->load->model('petshop_model');
    	$popular_products = $this->petshop_model->get_popular_product(0,4);
    	if($popular_products){
    		foreach($popular_products as $pkey =>$product){
    			$popular_products[$pkey] = format_output_data($product);
    		}
    	}
    	
    	$response['latest_listings'] 	= $latest_listings;
    	$response['hot_topics'] 	= $hot_topic;
    	$response['latest_reviews'] 	= $latest_review;
    	$response['popular_products'] 	= $popular_products;
    	
        $this->load->helper('notification');
        $response['bages_unread_notification']    = count_unread_notification($this->_member->id);
        
    	$this->response($response,200);    	
    }
}