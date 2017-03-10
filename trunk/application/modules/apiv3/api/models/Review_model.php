<?php
class Review_model extends CI_Model {

	protected $dataResults = array();
	protected $review;

	function __construct(){
        // Call the Model constructor
        parent::__construct();
		$this->load->model('listing_model');
    }
    
    function get_reviews_by_listing($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE, $listing_id , $sort = 'id' , $sort_value = 'DESC' , $keyword = false)
    {
    	if(!$listing_id)
    	{
    		return false;
    	}
    	$this->db->select('r.id, r.content, r.rate, r.created_date,
    						u.id as user_id , u.first_name , u.last_name , u.profile_photo , u.profile_photo_thumb
    			');
    	$this->db->from('user_reviews as r');
    	$this->db->join('users as u','u.id = r.user_id');
        $this->db->join('user_newsfeed_activities as nf','nf.review_id = r.id');
//     	$this->db->join('business_items as b','b.id = r.business_id');
    	$this->db->where('r.status',1);
    	$this->db->where('r.business_id',$listing_id);
    	
    	if($keyword){
    		$this->db->like('r.content',$keyword);
    	}
    	
    	if($option =='count')
    	{
    		$result = $this->db->get()->num_rows();
    	}
    	else
    	{
    		$this->db->order_by('r.'.$sort,$sort_value);
    		$this->db->limit($limit,$start);
			$result = $this->db->get()->result();
			
    		if(!empty($result))
    		{
    			foreach($result as $key => $row)
    			{
    				$media = $this->get_media_from_review('all',0,false,$row->id,'id','ASC');
    				if(!empty($media))
    				{
    					foreach($media as $mkey=>$row_media)
    					{
    						$media[$mkey] = format_output_data($row_media);
    					}
    				}
    				$row->media = $media;
    				//get friends
    				$row->total_friend = $this->member_model->get_user_friends('count', $row->user_id);
    				$row->total_review = $this->get_reviews_by_user('count',0,0,$row->user_id);
    				//total photo of user comment
    				$row->total_photo 	= $this->member_model->get_photos_by_user('count' , 0 , false ,$row->user_id,1);
    				
    				$result[$key] = format_output_data($row);
    			}
    		}    		
    	}    
    	return $result;
    }
    function get_reviews_by_user($option = 'count', $start = 0, $limit = false, $user_id , $order_field = 'id', $order_val = 'ASC',$keyword = false)
    {
    	if(!$user_id)
    	{
    		return false;
    	}
    	$query = "SELECT r.* 
    				FROM user_reviews as r
    				LEFT JOIN business_items b ON b.id = r.business_id 
    			WHERE b.status = 1 AND r.status = ? AND r.user_id = ?";
    	$params = array();
    	$params[] = 1;//status
    	$params[] = $user_id; //user id
    	
    	if($keyword){
    		$query .= "AND b.name LIKE '%$keyword%' ";
    	}
    	
    	
    	if($option =='count')
    	{
    		return  $this->db->query($query,$params)->num_rows();
    	}
    	else
    	{    
    		if(!empty($order_field) )
    		{
    			$query .= " ORDER BY `r`.$order_field $order_val";
    		}		
    		if($limit)
    		{
    			$query .= " LIMIT ? , ?";
    			$params[] = intval($start);//start
    			$params[] = intval($limit);//limit
    		}
    	
    		$result = $this->db->query($query,$params)->result();
    		return $result;
    	}
    }
    public function get_media_from_review($option = 'count', $start = 0, $limit = false, $review_id , $order_field = 'id', $order_val = 'ASC')
    {
    	if(!$review_id)
    	{
    		return false;
    	}
    	$query = "SELECT m.id, m.review_id, m.source , m.photo_thumb , m.type 
    				FROM user_media as m
    				LEFT JOIN user_reviews r ON r.id = m.review_id
    			WHERE m.review_id = ?";
    	$params = array();
    	$params[] = $review_id; //review id
    	if($option =='count')
    	{
    		return  $this->db->query($query,$params)->num_rows();
    	}
    	else
    	{
    		if(!empty($order_field) )
    		{
    			$query .= " ORDER BY `m`.$order_field $order_val";
    		}
    		if($limit)
    		{
    			$query .= " LIMIT ? , ?";
    			$params[] = intval($start);//start
    			$params[] = intval($limit);//limit
    		}
    		 
    		$result = $this->db->query($query,$params)->result();
    		return $result;
    	}
    }
	public function get_review_detail($id , $get_media = false , $get_comments = false ,$get_total_like = false, $get_user_total_friend = false, $get_user_total_photo = false, $get_user_total_review = false)
	{
		if(!$id)
		{
			return false;
		}
		$this->db->select('r.id,r.user_id,r.business_id,r.content,rate,r.created_date,r.status,
    						u.first_name,u.last_name,u.profile_photo,u.profile_photo_thumb'
		);
		$this->db->from('user_reviews as r');
		$this->db->join('users as u','u.id = r.user_id');
		$this->db->where('r.id',$id);
		$result = $this->db->get();
		if($result->num_rows() > 0)
		{
			$review = $result->row();
			if($get_media)
			{
				//get media
				$media = $this->get_media_from_review('all',0,false,$review->id,'id','ASC');
				if(!empty($media))
				{
					foreach($media as $key => $row_media)
					{
						$media[$key] = format_output_data($row_media);
					}
				}
				$review->media = $media;
			}
			if($get_comments)
			{
				//get comments
				$total_comment 	= $this->get_comments_by_review('count',0,API_NUM_RECORD_PER_PAGE,'id','DESC',$review->id,false);
				$comments		= $this->get_comments_by_review('all',0,API_NUM_RECORD_PER_PAGE,'id','DESC',$review->id,false);
				$review->total_comment 	= $total_comment;
				if($comments)
				{
					foreach($comments as $key => $comment)
					{
						$comments[$key] = format_output_data($comment);
					}
				}
				$review->comments 		= $comments;
			}
			if($get_total_like)
			{
				//total like of review
				$review->total_like = $this->get_likes_by_review('count', 0 , false , 'id' , 'ASC' , $review->id , 0);
			}

			if($get_user_total_friend)
			{
				$review->total_friend = $this->member_model->get_user_friends('count',$review->user_id,1);
			}

			if($get_user_total_photo)
			{
				$review->total_photo = $this->member_model->get_photos_by_user('count',0,false,$review->user_id,1, 'id', 'ASC', 'business');
			}

			if($get_user_total_review)
			{
				$review->total_review = $this->get_reviews_by_user('count', 0 , false , $review->user_id);
			}

			return $review;
		}
		return false;
	}
    public function get_comments_by_review($option = 'count', $start = 0 , $limit = false , $order_field = 'id' , $order_val = 'ASC' , $review_id , $status = '')
    {
    	if(!$review_id)
    	{
    		return false;
    	}
    	$this->db->select('c.*,u.first_name , u.last_name');
    	$this->db->from('user_comments as c');
    	$this->db->join('users u', 'u.id = c.user_id');
    	$this->db->where('c.review_id',$review_id);
    	
    	if($status && $status != '')
    	{
    		$this->db->where('c.status',$status);
    	}    	
    	if($option == 'count')
    	{
    		$result = $this->db->get()->num_rows();
    	}
    	else
    	{
    		if($limit)
    		{
    			$this->db->limit($limit,$start);
    		}
    		//order
    		$this->db->order_by("c.$order_field",$order_val);
    		
    		$result = $this->db->get()->result();
    	}
    	return $result;    	
    }
    public function get_likes_by_review($option = 'count', $start = 0 , $limit = false , $order_field = 'id' , $order_val = 'ASC' , $review_id , $type = 0)
    {
    	if(!$review_id)
    	{
    		return false;
    	}
    	$this->db->select('l.*');
    	$this->db->from('user_likes as l');
    	$this->db->where('l.review_id',$review_id);
    	 
    	if($type)
    	{
    		$this->db->where('l.type',$type);
    	}
    	else {
    		$this->db->where('l.type',0);
    	}
    	if($option == 'count')
    	{
    		$result = $this->db->get()->num_rows();
    	}
    	else
    	{
    		if($limit)
    		{
    			$this->db->limit($limit,$start);
    		}
    		//order
    		$this->db->order_by("t.$order_field",$order_val);
    	
    		$result = $this->db->get()->result();
    	}
    	return $result;
    }    
    public function get_reviews($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $sort = 'id' , $sort_value = 'DESC')
    {
    	$this->db->select('r.id,r.business_id,r.user_id,r.content,r.rate,r.created_date,
    						u.first_name, u.last_name,u.profile_photo,u.profile_photo_thumb');
    	$this->db->from('user_reviews as r');
    	$this->db->join('users as u','u.id = r.user_id');
    	$this->db->where('status',1);
    	
    	
    	$this->db->order_by('r.'.$sort,$sort_value);
    	$this->db->limit($limit,$start);
    	
    	$result = $this->db->get()->result();
    	return $result;
    }
    public function update($data,$id){
    	$this->db->where('id',$id);
    	return $this->db->update('user_reviews',$data);
    }
    //('all',0,4,'id','DESC', $user_location);
    public function get_reviews_by_distance($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $sort = 'id' , $sort_value = 'DESC', $user_location = array())
    {
    	if(!empty($user_location) && $user_location['latitude'] && $user_location['longitude'])
    	{
    		$this->db->select('r.id,r.business_id,r.user_id,r.content,r.rate,r.created_date,
    						u.first_name, u.last_name,u.profile_photo,u.profile_photo_thumb');
    		$lat = $user_location['latitude'];
    		$lng = $user_location['longitude'];
    		$search_distance = $user_location['search_distance'];
    		
    		$this->db->select("( 6371 * acos( cos( radians($lat) ) * cos( radians( b.latitude ) ) * cos( radians( b.longitude ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( b.latitude ) ) ) ) AS distance");
    		$this->db->from('user_reviews as r');
    		$this->db->join('users as u','u.id = r.user_id', 'left');
    		$this->db->join('business_items as b','b.id = r.business_id', 'left');
    		
    		$this->db->having("distance < $search_distance");    		
    		$this->db->where('r.status',1);    		
    		$this->db->order_by('r.'.$sort,$sort_value);
    		$this->db->limit($limit,$start);
    		
    		$result = $this->db->get()->result();
    	}
    	else{
    		$result = array();
    	}

    	return $result;
    }

	public function item( $newsFeedItemId = false, $newsFeedId = false ) {
		$sqlReview = "SELECT bi.id AS businessId, bi.name, bi.address, bi.country, bi.latitude, bi.longitude, bi.photo, review.id AS reviewId, review.content, review.rate
        FROM user_reviews review
        LEFT JOIN business_items bi ON bi.id = review.business_id
        WHERE review.id = ?
        GROUP BY review.id";

		$reviewQuery = $this->db->query($sqlReview, array($newsFeedItemId));

		$this->review = $reviewQuery->num_rows() > 0 ? $reviewQuery->row() : array();
		return $this;
	}

	public function itemTransformer( $item = false ) {

		if( $this->review) {
			$item = $this->review;
		}

		if($item) {

			format_output_data($item);

			$shortContent = giveShortContent($item->content);

			$catName = $this->listing_model->getListingCategories($item->businessId)->getListingCategoryName();

			return array(
				LISTING_INFO => array(
					ID => $item->businessId,
					LATITUDE => $item->latitude,
					LONGITUDE => $item->longitude,
					COUNTRY => $item->country,
					ADDRESS => $item->address,
					NAME => $item->name,
					WEBSITE => !empty($item->website) ? $item->website : "",
					CATEGORY => $catName,
					PHOTO => $item->photo,
					TOTAL_REVIEW => $this->checkin_model->getTotalReviews($item->businessId),
					TOTAL_RATING => $this->checkin_model->getTotalRating($item->businessId),
					VISIT => getTotalVisit((int)$item->businessId),
				),
				REVIEW_INFO => array(
					ID		=> $item->reviewId,
					CONTENT	=> $item->content,
					SHORT_CONTENT => $shortContent,
					RATE	=> $item->rate,
				),
			);
		} else {
			return array(LISTING_INFO => array(), REVIEW_INFO => array());
		}
	}

	/**
	 * @param $reviewId
	 * @param $userId
	 * @description: Delete a user review
	 * @tags: petnewsfeed lib
     */
	public function delete( $reviewId, $userId ) {
		$this->db->delete("user_reviews", array("id" => $reviewId, "user_id" => $userId));
	}

    public function searchPreviews($member, $keyword, $option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $user_location = array())
    {
        $lat = $user_location['latitude'];
        $lng = $user_location['longitude'];
        $search_distance = $user_location['search_distance'];
        $result = array();
        if($lat && $lng)
        {
            $this->db->select('r.id,r.business_id,r.user_id,r.content,r.rate,r.created_date,
                            u.first_name, u.last_name,u.profile_photo,u.profile_photo_thumb');
            $this->db->select("( 6371 * acos( cos( radians($lat) ) * cos( radians( b.latitude ) ) * cos( radians( b.longitude ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( b.latitude ) ) ) ) AS distance");
            $this->db->from('user_reviews as r');
            $this->db->join('users as u','u.id = r.user_id', 'left');
            $this->db->join('business_items as b','b.id = r.business_id', 'left');
            $this->db->join('user_newsfeed_activities as nf','nf.review_id = r.id','inner');
            $this->db->having("distance < $search_distance");           
            $this->db->where('r.status',1);   
            //$this->db->where('r.user_id',$member->id);        
            $this->db->order_by('r.id DESC');
            if($keyword){
                $this->db->like('r.content',$keyword);
            }
            if($option =='count')
            {
                $result = $this->db->get()->num_rows();
            }
            else
            {
                $this->db->limit($limit,$start);
                $result = $this->db->get()->result_array();
            }
        }else{
            $this->db->select('r.id,r.business_id,r.user_id,r.content,r.rate,r.created_date,
                            u.first_name, u.last_name,u.profile_photo,u.profile_photo_thumb');
            $this->db->from('user_reviews as r');
            $this->db->join('users as u','u.id = r.user_id');
            $this->db->join('user_newsfeed_activities as nf','nf.review_id = r.id');
            $this->db->where('r.status',1); 
            $this->db->order_by('r.id DESC');
            if($keyword){
                $this->db->like('r.content',$keyword);
            }
            if($option =='count')
            {
                $result = $this->db->get()->num_rows();
            }
            else
            {
                $this->db->limit($limit,$start);
                $result = $this->db->get()->result_array();
            }
        }
        return $result;
    }
}