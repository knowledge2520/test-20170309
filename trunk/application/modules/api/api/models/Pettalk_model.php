<?php
/**
 * 
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @created_date: May 12, 2015
 * @file: file_name
 * @todo:
 */
class Pettalk_model extends CI_Model {
	
	function __construct(){
        // Call the Model constructor
        parent::__construct();
    }	
    
    public function add($data)
    {    	
    	$this->db->insert('pet_talk_category',$data);
    	return $this->db->insert_id();
    }
    public function add_topic($data)
    {
    	$this->db->insert('pet_talk_topics',$data);
    	return $this->db->insert_id();
    }
    public function add_comment($data)
    {
    	$this->db->insert('user_pettalk_comments',$data);
    	return $this->db->insert_id();
    }      
    /**
     * 
     * @param string $option
     * @param number $start
     * @param string $limit
     * @param string $keyword
     * @param number $status
     * @param string $sort_field
     * @param string $sort_val
     * @return unknown
     *file_name
     */
    public function get_list_pettalk($option = 'count', $start = 0, $limit = false , $keyword = false , $status = 1 , $sort_field = 'sort', $sort_val = 'ASC')
    {
    	$where = " WHERE status = '$status' ";
    	//set keyword
    	if($keyword)
    	{
    		$where .= " AND name LIKE '%$keyword%' ";
    	}
    	
    	 
    	$query = "SELECT * 
    				FROM pet_talk_category
    				$where ";
    		 
    	if($option == 'count')
    	{
    		return  $this->db->query($query)->num_rows();
    	}
    	else
    	{
    		$start= intval($start);//start
    		$limit= intval($limit);//limit
    		if($limit)
    		{
    			$query .= " ORDER BY $sort_field $sort_val LIMIT $start , $limit";
    		}
    		else
    		{
    			$query .= " ORDER BY $sort_field $sort_val";
    		}
    		 
    		$result = $this->db->query($query)->result();
    		return $result;
    	}
    }
    public function get_topics_pettalk($pt_id = false , $option = 'count', $start = 0 , $limit = false , $keyword = false ,$status = 1 , $sort_field = 'id', $sort_val = 'ASC')
    {
    	$this->db->select('t.*,c.name as category_name, c.description as category_desc ,c.photo as category_photo, c.photo_thumb as category_photo_thumb');
    	$this->db->from('pet_talk_topics as t');
    	$this->db->join('pet_talk_category as c','c.id = t.category_id');
    	if($status)
    	{
    		$this->db->where('t.status',$status);
    	}
    	if($pt_id)
    	{
    		$this->db->where('t.category_id',$pt_id);
    	}
    	if($keyword)
    	{
    		$this->db->like('t.title',$keyword);
    	}
    	
    	// get option
    	if($option == 'count')
    	{
    		$result =  $this->db->get()->num_rows();
    	}
    	else
    	{
    		if($limit)
	    	{	    		
	    		$this->db->limit($limit,$start);
	    	}
	    	$this->db->order_by('t.'.$sort_field,$sort_val);
	    	
	    	$result = $this->db->get()->result();
    	}
    	return $result;
    }
    public function get_comments_topics($topic_id , $option = 'count', $start = 0 , $limit = false , $status = 1 , $sort_field = 'id', $sort_val = 'ASC')
    {
    	if(!$status)
    	{
    		$status = 1;
    	}
    	$where = " WHERE c.status = '$status' AND c.topic_id = $topic_id";
    	 
    	$query = "SELECT c.*, 
    					u.profile_photo as profile_photo, u.profile_photo_thumb as profile_photo_thumb , u.first_name, u.last_name 
    				FROM user_pettalk_comments as c
    				LEFT JOIN users as u ON u.id = c.user_id
    				LEFT JOIN pet_talk_topics as t ON t.id = c.topic_id 
    				$where ";
    		 
    	if($option == 'count')
    	{
    		return  $this->db->query($query)->num_rows();
    	}
    	else
    	{
    		$start= intval($start);//start
    		$limit= intval($limit);//limit   
    		if($limit)
    		{
    			$query .= " ORDER BY c.$sort_field $sort_val LIMIT $start , $limit";
    		}
    		else {
    			$query .= " ORDER BY c.$sort_field $sort_val , c.id DESC";
    		}
    		 
    		$result = $this->db->query($query)->result();
    		return $result;
    	}
    }
    public function get_like_topics($topic_id , $type = 0, $option = 'count', $start = 0 , $limit = false , $status = 1 , $sort_field = 'id', $sort_val = 'ASC')
    {
    	if(!$type)
    	{
    		$type = 0;
    	} 
    	
    	$where = " WHERE l.type = '$type' AND l.topic_id = $topic_id";
    
    	$query = "SELECT l.* FROM user_likes as l $where ";
    	 
    	if($option == 'count')
    	{
    		return  $this->db->query($query)->num_rows();
   	 	}
    	else
    	{
    		$start= intval($start);//start
    		$limit= intval($limit);//limit
    		if($limit)
    		{
    			$query .= " ORDER BY t.$sort_field $sort_val LIMIT $start , $limit";
    		}
    		else
    		{
    			$query .= " ORDER BY t.$sort_field $sort_val";
    		}
    		
    			 
    		$result = $this->db->query($query)->result();
    		return $result;
    	}
    }
    public function get_pettalk($id)
    {
    	$this->db->select('*');
    	$this->db->from('pet_talk_category as p');
    	$this->db->where('p.id',$id);
    	$pet = $this->db->get()->first_row();
    	if(!empty($pet))
    	{    		
    		//$pet['total_topics']				= $this->get_topics_pettalk($id,$option = 'count' , false , false , false ,1);    
    		return $pet;
    	}
    	return false;
    }
    public function get_topic($id , $comments = false , $like = false , $dislike = false , $media = false)
    {
    	$this->db->select('t.*,c.name as category_name, c.description as category_desc');
    	$this->db->from('pet_talk_topics as t');
    	$this->db->where('t.id',$id);
    	$this->db->join('pet_talk_category as c','c.id = t.category_id');
    	$topic = $this->db->get()->first_row();
    	if(!empty($topic))
    	{    	
    		$topic = format_output_data($topic);
    		foreach($topic as $key => $value)
    		{
    			$result[$key] = $value;
    		}
                $result['short_content'] = character_limiter(strip_tags(add_break_link($result['content']), '<br>'),200);

                if($comments)
    		{
    			$result['total_comments']		= $this->get_comments_topics($id,$option = 'count' , false , false , false ,1);
    			$result['comments']				= format_output_data($this->get_comments_topics($id,$option = 'all') , 0 , API_NUM_RECORD_PER_PAGE , false ,1);
    		}	
    		if($like)
    		{
    			$result['total_like'] 			= $this->get_like_topics($id, 0 , $option = 'count' , false , false , false);
    		}
    		if($dislike)
    		{
    			$result['total_dislike'] 		= $this->get_like_topics($id, 1 , $option = 'count' , false , false , false);
    		}
    		if($media)
    		{
    			$result['media'] = array();
    			$medias 				= $this->get_media_topic('all', 0 , API_NUM_RECORD_PER_PAGE, 'id' , 'DESC' , $id , 1);
    			if(!empty($medias)){
    				foreach($medias as $mkey => $media){
    					$result['media'][$mkey] = format_output_data($media);
    				}
    			}
    		}
    		return $result;
    	}
    	return false;
    }
    public function get_hot_topic($start = 0, $limit = false)
    {
//     	$query = "SELECT * FROM pet_talk_topics as t
// 					LEFT JOIN (SELECT count(id) as total_comments, topic_id FROM user_pettalk_comments WHERE status = 1 GROUP BY topic_id ORDER BY total_comments DESC) as c ON t.id = c.topic_id
// 					WHERE t.status = 1
// 					ORDER BY c.total_comments DESC
// 					LIMIT 0,4";
    	$this->db->select('t.id, t.title, t.content, t.status , t.created_date, t.created_by, t.category_id,
    						tc.name as category_name, tc.photo , tc.photo_thumb,
    						c.total_comments');
    	$this->db->from('pet_talk_topics as t');
    	$this->db->join('(SELECT count(id) as total_comments, topic_id FROM user_pettalk_comments WHERE status = 1 GROUP BY topic_id ORDER BY total_comments DESC) as c','t.id = c.topic_id','left');
    	$this->db->join('pet_talk_category as tc', 'tc.id = category_id');
    	$this->db->where('t.status',1);
    	$this->db->order_by('c.total_comments','DESC');
    	$this->db->limit($limit,$start);
    	$result = $this->db->get()->result();
    	return $result;
    }
    public function get_user_like_topic($user_id,$topic_id){
    	$row = $this->db->get_where('user_likes',array('user_id'=>$user_id,'topic_id'=>$topic_id));    	
    	if($row->num_rows() > 0){
    		$result = $row->first_row();
    		return $result;
    	}
    	else
    	{
    		return null;
    	}
    }
    public function get_media_topic($option = 'count', $start = 0 , $limit = false , $order_field = 'id' , $order_val = 'ASC' , $topic_id , $status = ''){
    	if(!$topic_id)
    	{
    		return false;
    	}
    	$this->db->select('m.*');
    	$this->db->from('user_media as m');
    	$this->db->where('m.topic_id',$topic_id);
    	 
    	if($status && $status != '')
    	{
    		$this->db->where('m.status',$status);
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
    		$this->db->order_by("m.$order_field",$order_val);
    	
    		$result = $this->db->get()->result();
                
                //get user info
    		if(!empty($result)){
    			$this->load->model('member_model');
    			foreach($result as $key => $rs){
    				$user_info = $this->member_model->getMemberByMemberID($rs->user_id,true,false,true,true);
    				if($user_info){
    					$result[$key]->user_info = format_output_data($user_info);
    				}
    			}
    		}
    	}
    	return $result;
    }
    public function get_media_comment($option = 'count', $start = 0 , $limit = false , $order_field = 'id' , $order_val = 'ASC' , $comment_id , $status = ''){
    	if(!$comment_id)
    	{
    		return false;
    	}
    	$this->db->select('m.*');
    	$this->db->from('user_media as m');
    	$this->db->where('m.topic_comment_id',$comment_id);
    
    	if($status && $status != '')
    	{
    		$this->db->where('m.status',$status);
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
    		$this->db->order_by("m.$order_field",$order_val);
    		 
    		$result = $this->db->get()->result();
                
                //get user info
    		if(!empty($result)){
    			$this->load->model('member_model');
    			foreach($result as $key => $rs){
    				$user_info = $this->member_model->getMemberByMemberID($rs->user_id,true,false,true,true);
    				if($user_info){
    					$result[$key]->user_info = format_output_data($user_info);
    				}
    			}
    		}
    	}
    	return $result;
    }
    
    public function check_exist_comment($topic_id, $comment_id){
        $this->db->where('topic_id', $topic_id);
        $this->db->where('id', $comment_id);  
        $result = $this->db->get('user_pettalk_comments');
        return $result->num_rows() > 0 ? TRUE : FALSE;
    }
    
    public function get_comment($comment_id){
        $this->db->where('id', $comment_id);  
        $result = $this->db->get('user_pettalk_comments');
        return $result->num_rows() > 0 ? $result->row() : FALSE;
    }
    
    public function update_comment($data, $id){
        //check valid comment id
        if(!$this->get_comment($id)){
            return FALSE;
        }
        
        $this->db->where('id', $id);
        $this->db->update('user_pettalk_comments', $data);
    }
    
    public function update_topic($data, $id){
        //check valid topic id
        if(!$this->get_topic($id)){
            return false;
        }
        
        $this->db->where('id', $id);
        $this->db->update('pet_talk_topics', $data);
    }
    
    public function delete_comment($id){
//        //check valid comment id
//        if(!$this->get_comment($comment_id)){
//            return FALSE;
//        }
//        
//        //delete media in comment
//        $this->load->model('member_model');
//        $medias = $this->member_model->get_medias_by('topic_comment_id', $comment_id);
//        if($medias){
//            foreach ($medias as $media){
//                $this->member_model->delete_media($media->id);
//            }
//        }
        
        //delete comment
        $this->db->where('id', $id);
        $this->db->update('user_pettalk_comments', array('status' => 2));
        
        return TRUE;
    }
    
    public function delete_topic($id){
        
        //delete comment
        $this->db->where('id', $id);
        $this->db->update('pet_talk_topics', array('status' => 2));
        
    }
}