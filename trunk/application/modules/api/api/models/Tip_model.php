<?php
class Tip_model extends CI_Model {
	
	function __construct(){
        // Call the Model constructor
        parent::__construct();
    }
    
    function get_tips_by_listing($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE, $listing_id , $sort_field = 'id', $sort_val = 'ASC', $user_id = FALSE)
    {
    	if(!$listing_id)
    	{
    		return false;
    	}
    	$query = "SELECT 
    					t.id, t.description, t.created_date,
    					u.id as user_id , u.first_name , u.last_name , u.profile_photo , u.profile_photo_thumb 
    			FROM user_tips as t 
    			LEFT JOIN users as u ON u.id = t.user_id
    			WHERE 
    				t.status = ? AND t.business_id = ? ";
    	$params = array();
    	$params[] = 1;//status
    	$params[] = $listing_id; //bussiness id
    	if($option =='count')
    	{
    		return  $this->db->query($query,$params)->num_rows();
    	}
    	else
    	{
    		$query .= "ORDER BY t.$sort_field $sort_val";
    		$query .= " LIMIT ? , ?";
    		$params[] = intval($start);//start
    		$params[] = intval($limit);//limit
    		
    		$result = $this->db->query($query,$params)->result();
    		if(!empty($result))
    		{
    			foreach($result as $key => $row)
    			{
    				$media = $this->get_media_from_tips('all',0,false,$row->id,'id','ASC');
    				if(!empty($media))
    				{
    					foreach($media as $mkey =>$row_media)
    					{
    						$media[$mkey] = format_output_data($row_media);
    					}
    				}
    				$row->media = $media;
    				
    				//get total like
    				$row->total_like = $this->get_like_tip('count',0,0,$row->id);    	

    				//get user like tip
    				if($user_id){
    					$user_like = $this->get_tip_user_like($user_id,$row->id);
    					if($user_like){
    						$row->like_type = strval($user_like->type);
    					}
    					else{
    						$row->like_type = '';
    					}
    				}
    				
    				$result[$key] = format_output_data($row);
    			}    			
    		}
    		return $result;
    	}     	    
    }
    function get_tips_by_user($option = 'count', $start = 0, $limit = false, $user_id , $order_field = 'id', $order_val = 'ASC')
    {
    	if(!$user_id)
    	{
    		return false;
    	}
    	$query = "SELECT r.* 
    				FROM user_reviews as r
    				LEFT JOIN business_items b ON b.id = r.business_id 
    			WHERE r.status = ? AND r.user_id = ?";
    	$params = array();
    	$params[] = 1;//status
    	$params[] = $user_id; //user id
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
    public function get_media_from_tips($option = 'count', $start = 0, $limit = false, $review_id , $order_field = 'id', $order_val = 'ASC')
    {
    	if(!$review_id)
    	{
    		return false;
    	}
    	$query = "SELECT m.id, m.tip_id, m.source , m.photo_thumb , m.type 
    				FROM user_media as m
    				LEFT JOIN user_tips t ON t.id = m.tip_id
    			WHERE m.tip_id = ?";
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
    public function get_like_tip($option = 'count', $start = 0, $limit = false, $tip_id , $order_field = 'id', $order_val = 'ASC')
    {
    	if(!$tip_id)
    	{
    		return false;
    	}
    	$query = "SELECT l.*
    				FROM user_likes as l
    			WHERE l.tip_id = ?";
    	$params = array();
    	$params[] = $tip_id; //tip id
    	if($option == 'count')
    	{
    		$result = $this->db->query($query,$params)->num_rows();
    		return $result;
    	}
    	else
    	{
    		if(!empty($order_field) )
    		{
    			$query .= " ORDER BY `l`.$order_field $order_val";
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
    public function get_tip_detail($tip_id , $get_likes = false)
    {
//     	$row =  $this->db->get_where('user_tips', array('id' => $tip_id))->first_row();
    	$this->db->select('t.*,u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb');
    	$this->db->from('user_tips as t');
    	$this->db->join('users as u','u.id = t.user_id');
    	$this->db->where('t.id',$tip_id);
    	
        $result = $this->db->get();
        
        if($result->num_rows() < 1) {
            return false;
        }
        
        $row = $result->row();
    	
    	if($get_likes)
    	{
    		//get likes
    		$total_like = $this->get_like_tip('count',0,false,$tip_id);
    		$row->total_like = $total_like;
    	}    	 
    	$row = format_output_data($row);
    	
    	return $row;
    }
    public function get_tip_user_like($user_id,$tip_id){
    	$row = $this->db->select('*')->from('user_likes')->where('user_id',$user_id)->where('tip_id',$tip_id)->get()->row();
    	return $row;
    }
}