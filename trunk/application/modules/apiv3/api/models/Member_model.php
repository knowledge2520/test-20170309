<?php
class Member_model extends CI_Model {
	function __construct(){
        // Call the Model constructor
        parent::__construct();
		$this->load->library('parse');
    }
    /**
     * 
     * @param unknown $data
     * @return unknown
     *file_name
     */
    function insert($data)
    {
    	//generate token
    	$token = $this->_generateToken();
    	$data['token'] = $token;

    	$this->db->insert('users', $data);
    	$memberID = $this->db->insert_id();
    	return $memberID;
    }
    function update($data,$member_id)
    {   
    	foreach($data as $key => $value)
    	{
    		if($value === false)
    		{
    			unset($data[$key]);
    		}
    	}
    	return $this->db->update('users', $data , "id = $member_id");
    }
    /**
     * 
     * @param unknown $email
     * @param unknown $password
     * @param unknown $member
     * @return boolean|unknown
     *file_name
     */
    function login($email, $password, $facebook_id, $member)
    {
    	if (!empty($member)) {
    		if(!$facebook_id)
    		{
    			if ($password != $member->password) {
    				return false;
    			}
    		}    		
    		//do login
    		$data_update = array();
    		$token = $this->_generateToken();
    		$data_update['token'] = $token;
    		$member->token = $token;
    		$data_update['last_login'] = now();
    		$this->db->where('id', $member->id);
    		$this->db->update('users', $data_update);
    		unset($member->password);
    		return $member;
    	} else {
    		return false;
    	}
    }    
    function logout($member_id, $device_token = false, $device_type = false, $member_token = false,$token_fire_base=false)
    {
    	if (!empty($device_token) && !empty($device_type)) {
    		$this->db->where('user_id', $member_id);
    		$this->db->where('device_type', strtolower($device_type));
    		$this->db->where('device_token', $device_token);
    		$this->db->delete('user_device');
    	}
        if (!empty($token_fire_base) && !empty($device_type)) {
            $this->db->where('user_id', $member_id);
            $this->db->where('device_token_firebase', $token_fire_base);
            $this->db->delete('user_device');
        }
    	$this->db->where('user_id', $member_id);
    	$this->db->where('token', $member_token);
    	$this->db->delete('user_logged');
        
        //remove memberobjectId on _installation
        if($device_token){
            $parse_queries = $this->parse->ParseQuery('_Installation');
            $parse_queries->where("deviceToken", $device_token);
            $result = $parse_queries->find()->results;
            if(!empty($result)){
                $parse_object = $this->parse->ParseObject('_Installation');
                $parse_object->delete($result[0]->objectId);
            }
        }
    	$this->db->where('id', $member_id);
    	$this->db->update('users', array('token' => ''));
    }
    public function forgotPassword($userId, $email) {
    	$password = $this->_genereatePassword(4);
    	$salt = $this->getSaltByEmail($email);
    	$password_encode = SHA1($salt.$password);

    	//$this->db->update('users', array('password' => SHA1($salt.$password_encode)), array('id' => $userId));
        $this->db->update('users', array('forgotten_password_code' => $salt.$password, 'forgotten_password_time' => time()), array('id' => $userId));

    	//send forgot mail
    	$send_mail_forgot = send_forgot_email($userId,$salt.$password);
    	if($send_mail_forgot)
    	{
    		return true;
    	}
    	else
    	{
    		return false;
    	}
    }
    function loadMemberFromToken($token)
    {
    	$params = array();
    	$query = $this->db->select('l.*,u.*')
    			->from('user_logged as l')
    			->join('users as u', 'u.id = l.user_id')
    			->where('l.token',$token);    
    	$result = $query->get();    
    	if ($result->num_rows() > 0) {
    		$row = $result->row();
    		return $row;
    	}
    	return false;
    }
    /**
     * 
     * @param unknown $id
     * @return unknown|boolean
     *file_name
     */
    public function getMemberByMemberID($id , $get_reviews = false , $get_checkins = false , $get_photos = false , $get_friends = false, $owner_id = false)
    {
    	$params = array();
    	$query = " SELECT m.id, m.token, m.first_name, m.last_name, m.gender, m.activation_code,"
    			. " m.email, m.phone, m.active, m.last_login, m.profile_photo, m.profile_photo_thumb, m.profile_background, m.profile_background_thumb, m.dob, m.created_on,m.facebook_id, m.private_user";
    	$query .= " FROM users AS m WHERE 1 ";
    	$query .= " AND m.id = ? ";
    	$params[] = $id;
    	$result = $this->db->query($query, $params);
    
    	if ($result->num_rows() > 0) {
    		$row = $result->first_row();
    		$user_id = $row->id;
    		
    		if($get_reviews)
    		{
    			//load member reviews
    			$this->load->model('review_model');
    			$total_reviews = $this->review_model->get_reviews_by_user('count', 0 , false , $user_id);
//     			$list_reviews = $this->review_model->get_reviews_by_user('all', 0 , API_NUM_RECORD_PER_PAGE , $user_id , 'id', 'DESC');
    			
    			$row->total_reviews = $total_reviews;
//     			if(!empty($list_reviews))
//     			{
//     				foreach($list_reviews as $key => $review)
//     				{
//     					$list_reviews[$key] = format_output_data($review);
//     				}
//     			}
//     			$row->list_reviews	= $list_reviews;
    		}
    		//get total checkin
    		if($get_checkins)
    		{
    			$total_checkins = $this->get_checkins_by_user('count',0,false,$user_id);
    			$row->total_checkins = $total_checkins;
    		}
    		//get total photos
    		if($get_photos)
    		{
    			$total_photos = $this->get_user_photos_v4('count', 0, API_NUM_RECORD_PER_PAGE, $user_id, false);
    			$row->total_photos = $total_photos;
    		}
    		//get friends
    		if($get_friends)
    		{    			
    			$total_friends 		= $this->get_user_friends('count',$user_id,1);
    			$friends 			= $this->get_user_friends('all',$user_id,1,'first_name','ASC',0,API_NUM_RECORD_PER_PAGE,$owner_id);    			
    			if($friends)
    			{
    				foreach($friends as $fkey => $friend)
    				{
    					$friends[$fkey] = format_output_data($friend);
    				}
    			}
    			$row->total_friends	= $total_friends;
    			$row->friends 		= $friends;
    		}
    		
    		return $row;
    	}
    	return false;
    }
    /**
     * 
     * @param unknown $email
     * @return boolean
     *file_name
     */
    public function getSaltByEmail($email)
    {
    	$params = array();
    	$query = " SELECT salt FROM users WHERE email = ? ORDER BY id DESC ";
    	$params[] = $email;
    	$result = $this->db->query($query, $params);
    
    	if ($result->num_rows() > 0) {
    		$row = $result->first_row();
    		return $row->salt;
    	}
    	return false;
    }
    /**
     * 
     * @param unknown $token
     * @param unknown $type
     * @param unknown $member_id
     *file_name
     */
	function updateDeviceToken($token, $type, $member_id,$device_token_firebase = false)
    {    	
        // delete old device account
//         $sql_delete = "DELETE FROM `user_device` WHERE `user_id` = ? AND device_token = ?";
//         $params = array();
//         $params[] = $member_id;
//         $params[]= $token;
//         $this->db->query($sql_delete, $params);
		
    	//clear all item has the same device token
    	if($device_token_firebase){

            // Delete device token of parse
            $this->db->where(array('user_id'=>$member_id));
            $this->db->where("(device_token <>'')");
            $this->db->delete('user_device');

            // Delte device token cua firebase
            $this->db->where(array('user_id'=>$member_id,'device_token_firebase'=>$device_token_firebase));
            $this->db->delete('user_device');

        }
        if($token){
            $this->db->where(array('user_id'=>$member_id,'device_token'=>$token));
            $this->db->delete('user_device');
        }
        
        $now = time();
        //$gmt = local_to_gmt($now);
        $gmt = now();
        $data_insert = array();
        $data_insert['device_type'] = strtolower($type);
        if($device_token_firebase){ // Got update version, then got firebase Token
            $data_insert['device_token_firebase'] = $device_token_firebase;
        }
        $data_insert['device_token'] = $token;
        $data_insert['user_id'] = $member_id;
        $data_insert['created_date'] = date('Y-m-d H:i', $gmt);
        $data_insert['device_auth_token'] = $this->_generateToken();
        $this->db->insert('user_device', $data_insert);
        
        $row = $this->db->get_where('user_device',array('id'=>$this->db->insert_id()))->first_row();
        return $row;
    	
    	
		/*
        $params = array();
        $query = "SELECT d.* FROM `user_device` AS d WHERE 1 ";
        $query .= " AND d.user_id = ? ";
        $params[] = $member_id;
        $result = $this->db->query($query, $params);
        if ($result->num_rows() > 0) {
            $data_update = array();
            $data_update['device_type'] = strtolower($type);
            $data_update['device_token'] = $token;
            $this->db->where('user_id', $member_id);
            $this->db->update('user_device', $data_update);
        } else {
            $now = time();
            //$gmt = local_to_gmt($now);
            $data_insert = array();
            $data_insert['device_type'] = strtolower($type);
            $data_insert['device_token'] = $token;
            $data_insert['user_id'] = $member_id;
            $data_insert['created_date'] = date('Y-m-d H:i', $gmt);
            $this->db->insert('user_device', $data_insert);
        }*/
    }
    public function checkParseMember($email) {    	
    	$queryUserObj = $this->parse->ParseQuery('users');
    	$queryUserObj->where('email', $email);
    	$rs = $queryUserObj->find();
    	if (empty($rs->results)) {
    		return false;
    	}
    	foreach($rs->results as $v){
    		return $v;
    	}
    }
    public function findInstallObject($member_object_id,$device_token = false)
    {
    	$queryUserObj = $this->parse->ParseQuery('_Installation');
    	$queryUserObj->where('MemberObjectId', $member_object_id);
    	if($device_token){
    		$queryUserObj->where('deviceToken', $device_token);
    	}
    	$rs = $queryUserObj->find();
    	if (empty($rs->results)) {
    		return false;
    	}
    	foreach($rs->results as $v){
    		return $v;
    	}
    }
    
    public function checkMember($email = false, $fbId = false) {
    	$return = false;
    	if($email) {
                $q = '(email = "'. $email .'" AND active != -1)';
                $this->db->where($q);
                $query = $this->db->get('users');
    		//$query = $this->db->get_where('users', array('email' => $email));
    		if($query->num_rows() > 0)
    		{
    			$return = $query->row(0);
    		}
    	}
    	if($fbId) {
    		
    		$query = " SELECT * from users where facebook_id=?";
    		$resultdata = $this->db->query($query, array('facebook_id' => $fbId));
    		if($resultdata->num_rows() > 0)
    		{
    			$resultdata = $resultdata->row(0);
    			$return = $resultdata;    			
    		}
    		else
    		{
//     			if($return)
//     			{
// //     				update facebook id for exits user
//     				$data_update = array('facebook_id'=>$fbId);
//     				$this->db->where('email',$email);
//     				$this->db->update('users',$data_update);
    				
//     				//get member info
// 					$return = $this->getMemberByMemberID($return->id);
//     			}
    		}
    	}
    	return $return;
    }
    
    /**
     * @todo create the code number to verify user's phone number
     *
     */
    public function _generateCode($length = 4)
    {
    	$pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    	$key = '';
    	$count = strlen($pool);
    	while ($length--) {
    		$key .= $pool[mt_rand(0, $count - 1)];
    	}
    	return $key;
    }
    
    function genereateForgotCode($length = 6)
    {
    	$pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    
    	do {
    		$key = '';
    		$count = strlen($pool);
    		while ($length--) {
    			$key .= $pool[mt_rand(0, $count - 1)];
    		}
    
    		$params = array();
    		$query = "SELECT m.* FROM crm_members AS m WHERE 1 ";
    		$query .= " AND m.forgot_code = ? ";
    		$params[] = $key;
    		$result = $this->db->query($query, $params);
    		if ($result->num_rows() > 0) {
    			$row = $result->row();
    			$continue = empty($row->token) ? false : true;
    		} else {
    			$continue = false;
    		}
    	} while ($continue);
    
    	return $key;
    }
    
    private function _generateToken($length = 32)
    {
    	$pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    
    	do {
    		$key = '';
    		$count = strlen($pool);
    		while ($length--) {
    			$key .= $pool[mt_rand(0, $count - 1)];
    		}
    
    		$params = array();
    		$query = "SELECT m.* FROM users AS m WHERE 1 ";
    		$query .= " AND m.token = ? ";
    		$params[] = $key;
    		$result = $this->db->query($query, $params);
    		if ($result->num_rows() > 0) {
    			$row = $result->row();
    			$continue = empty($row->token) ? false : true;
    		} else {
    			$continue = false;
    		}
    	} while ($continue);
    
    	return $key;
    }
    private function _genereatePassword($length=6){
//     	$pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    		
//     	$key = '';
//     	$count = strlen($pool);
//     	while ($length--){
//     		$key .= $pool[mt_rand(0, $count - 1)];
//     	}
    	
    	$this->load->helper('string');
    	$key = random_string('alnum', 8);
    
    	return $key;
    }
    
    function add_user_checkin_listing($data){
    	$this->db->insert('user_checkins',$data);
    	return $this->db->insert_id();
    }
    function add_user_review_listing($data){
    	$this->db->insert('user_reviews',$data);
    	return $this->db->insert_id();
    }
    function add_user_tip_listing($data){
    	$this->db->insert('user_tips',$data);
    	return $this->db->insert_id();
    }
	function get_checkins_by_user($option = 'count', $start = 0, $limit = false, $user_id , $order_field = 'id', $order_val = 'ASC')
    {
    	if(!$user_id)
    	{
    		return false;
    	}
    	$query = "SELECT c.*, 
    					b.name, b.address, b.hour, b.phone, b.website, b.photo 
    				FROM user_checkins as c
    				LEFT JOIN business_items b ON b.id = c.business_id 
    			WHERE c.status = ? AND c.user_id = ?";
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
    			$query .= " ORDER BY `c`.$order_field $order_val";
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
    function get_photos_by_user($option = 'count', $start = 0, $limit = false, $user_id , $status = '' , $order_field = 'id', $order_val = 'ASC', $type=false)
    {
    	if(!$user_id)
    	{
    		return false;
    	}
    	$this->db->select('*');
    	$this->db->from('user_media m');
        $this->db->join('business_items b', 'm.business_id = b.id', 'left');
    	$this->db->where('m.user_id',$user_id);
    	$this->db->where('b.status',1);
    	if($type){
                switch ($type){
                    case 'business': 
                        $this->db->where('m.business_id!=',null);
                        break;
                }
        }
    	if($status && $status!='')
    	{
    		$this->db->where('m.status',$status);
    	}
    	
    	if($option == 'count')
    	{
    		$result = $this->db->get()->num_rows();
    	}
    	else 
    	{
    		if($order_field)
    		{
    			$this->db->order_by($order_field,$order_val);
    		}
    		if($limit)
    		{
    			$this->db->limit($limit,$start);
    		}
    		$result = $this->db->get()->result();
    	}
    	return $result;
    }
    /*function get_friends_by_user($option = 'count', $start = 0, $limit = false, $user_id , $order_field = 'id', $order_val = 'ASC')
    {
    	if(!$user_id)
    	{
    		return false;
    	}
    	$query = "SELECT f.*,
    					u.first_name, u.last_name, u.email
    				FROM user_friends as f
    				LEFT JOIN users u ON u.id = f.friend_id
    			WHERE f.user_id = ? AND u.active = 1";
    	$params = array();
    	$params[] = $user_id; //user id
    	if($option =='count')
    	{
    		return  $this->db->query($query,$params)->num_rows();
    	}
    	else
    	{
    		if(!empty($order_field) )
    		{
    			$query .= " ORDER BY `f`.$order_field $order_val";
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
    }*/
    public function delete_like($id)
    {
    	$this->db->delete('user_likes',array('id'=>$id));
    }
    public function check_user_like_tip($user_id, $tip_id)
    {
    	return $this->db->get_where('user_likes',array('user_id'=>$user_id,'tip_id'=>$tip_id))->first_row();
    }
    public function check_user_like_review($user_id, $review_id)
    {
    	return $this->db->get_where('user_likes',array('user_id'=>$user_id,'review_id'=>$review_id))->first_row();
    }
    public function check_user_like_media($user_id, $media_id)
    {
    	return $this->db->get_where('user_likes',array('user_id'=>$user_id,'media_id'=>$media_id))->first_row();
    }
    public function check_user_like_topic($user_id, $topic_id)
    {
    	return $this->db->get_where('user_likes',array('user_id'=>$user_id,'topic_id'=>$topic_id))->first_row();
    }
    public function check_user_like_product($user_id, $product_id)
    {
    	return $this->db->get_where('user_likes',array('user_id'=>$user_id,'product_id'=>$product_id))->first_row();
    }
    public function check_user_bookmark_listing($user_id, $listing_id)
    {
    	return $this->db->get_where('user_bookmarks',array('user_id'=>$user_id,'business_id'=>$listing_id))->first_row();
    }
    public function add_like($data)
    {
    	$this->db->insert('user_likes',$data);
    	return $this->db->insert_id();
    }
    public function get_user_friends($option = 'count' , $user_id = false , $status = '' , $order_field = 'id', $order_val = 'ASC', $start = 0, $limit = API_NUM_RECORD_PER_PAGE, $ownerId = false)
    {
    	if(!$user_id)
    	{
    		return false;
    	} 
 
    	$this->db->select('u.id, u.first_name,u.last_name,u.email,u.profile_photo,u.profile_photo_thumb,c.social_type, c.registed, c.status');
    	$this->db->from('user_contact as c');
    	$this->db->join('users as u', 'u.email = c.email');
    	$this->db->where('c.user_id',$user_id);
    	$this->db->where('u.active',1);
    	
    	$this->db->where('c.status',1);
    	if($status != '')
    	{
    		$status = intval($status);
    		$this->db->where('c.status',$status);
    	}
    	if($option == 'count')
    	{
    		return $this->db->get()->num_rows();
    	}
    	else {    		
    		$this->db->order_by("u.$order_field",$order_val);
    		//$this->db->limit($limit,$start);//$this->_member->id
    		$results = $this->db->get();

    		if($results->num_rows() > 0){
    			$results = $results->result();
    			if($ownerId){
    				foreach($results as $k=>$data)
    				{
    					$results[$k]->status = $this->get_friend_status($ownerId, $data->registed);
    				}
    			}
    			return $results;
    		}
    		return array();	
    	}
    }
    public function get_friend_status($user_id, $friend_id){
    	$this->db->select('status');
    	$this->db->where('user_id', $user_id);
    	$this->db->where('registed', $friend_id);
    	$result = $this->db->get('user_contact');
    	if($result->num_rows() > 0){
    		$result= $result->row();
    		return $result->status;
    	}
    	return 0;
    }
    public function get_user_photos($option = 'count' ,$start = 0, $limit = false, $business_id = false, $user_id = false , $order_field = 'id', $order_val = 'ASC')
    {
    	$business_sql 	= "SELECT DISTINCT m.business_id,i.* FROM user_media m";
    	$business_sql 	.= " JOIN business_items as i ON i.id = m.business_id ";
    	$business_sql 	.= " WHERE m.status = 1 AND i.status = 1";
    	if($business_id)
    	{
    		$business_sql .= " AND m.business_id = $business_id";
    	}
    	if($user_id)
    	{
    		$business_sql .= " AND m.user_id = $user_id ";
    	}    	
//     	echo $business_sql;exit;
		if($option == 'count')
		{
			return $this->db->query($business_sql)->num_rows();
		}
		else {
			if($limit)
			{
				$business_sql .= " LIMIT $start,$limit";
			}
			return $this->db->query($business_sql)->result();
		}
    }

	/**
	 * @param string $option
	 * @param int $start
	 * @param bool|false $limit
	 * @param bool|false $userId
	 * @param bool|false $businessId
	 * @return mixed
     */
	public function get_user_photos_v4( $option = 'count', $start = 0, $limit = false, $userId = false, $businessId = false ) {

		/*$business_sql 	= "SELECT m.id, m.source, m.photo_thumb, m.business_id,  m.user_id FROM user_media m";
		$business_sql 	.= " JOIN business_items as i ON i.id = m.business_id ";
		$business_sql 	.= " WHERE m.status = 1 AND i.status = 1";*/

		$business_sql = "SELECT m.id, m.source, m.photo_thumb, m.business_id, m.user_id, m.width_thumb, m.height_thumb, m.width_source, m.height_source FROM user_media m
		WHERE m.user_id = ? AND m.status = 1
        AND m.message_id IS NULL GROUP BY m.id ORDER BY m.created_date DESC";

		if($option == 'count')
		{
			return $this->db->query($business_sql, array($userId))->num_rows();
		} else {
			$business_sql .= " LIMIT $start, $limit";
			return $this->db->query($business_sql, array($userId))->result();
		}
	}

    public function get_user_tips($option = 'count' ,$start = 0, $limit = false, $business_id = false, $user_id = false , $keyword = false , $order_field = 'id', $order_val = 'ASC')
    {
    	if(!$user_id)
    	{
    		return false;
    	}
    	$this->db->select('t.id,t.business_id,t.description,t.created_date,t.status');
    	$this->db->from('user_tips as t');
//     	$this->db->join('business_items as i','i.id=t.business_id');
    	$this->db->where('t.status',1);
    	if($business_id)
    	{
    		$this->db->where('t.business_id',$business_id);
    	}
    	$this->db->where('t.user_id',$user_id);
    	//     	echo $business_sql;exit;
    	if($option == 'count')
    	{
    		return $this->db->get()->num_rows();
    	}
    	else {
    		$this->db->order_by("t.$order_field",$order_val);
    		if($limit)
    		{
    			$this->db->limit($limit,$start);
    		}    		
    		$result =  $this->db->get()->result();
    		return $result;
    	}
    }
    public function get_user_bookmarks($option = 'count' ,$start = 0, $limit = false, $user_id = false , $keyword = false , $order_field = 'id', $order_val = 'ASC')
    {
    	if(!$user_id)
    	{
    		return false;
    	} 
    	$business_sql 	= "SELECT * FROM user_bookmarks as b LEFT JOIN business_items bi ON bi.id = b.business_id";
    	$business_sql 	.= " WHERE b.user_id = $user_id AND bi.status = 1";
    	//     	echo $business_sql;exit;
    	if($option == 'count')
    	{
    		return $this->db->query($business_sql)->num_rows();
    	}
    	else {
    		if($limit)
    		{
    			$business_sql .= " LIMIT $start,$limit";
    		}
    		return $this->db->query($business_sql)->result();
    	}
    }
    public function get_user_checkins($option = 'count' ,$start = 0, $limit = false, $user_id = false , $keyword = false , $order_field = 'id', $order_val = 'ASC')
    {
    	if(!$user_id)
    	{
    		return false;
    	}
    	$business_sql 	= "SELECT c.* FROM user_checkins as c LEFT JOIN business_items b ON b.id = c.business_id";
    	$business_sql 	.= " WHERE c.user_id = $user_id AND b.status = 1";
    	//     	echo $business_sql;exit;
    	if($option == 'count')
    	{
    		return $this->db->query($business_sql)->num_rows();
    	}
    	else {    		
    		if($order_field)
    		{
    			$business_sql .= " ORDER BY c.$order_field $order_val ";
    		}
    		if($limit)
    		{
    			$business_sql .= " LIMIT $start,$limit";
    		}
    		return $this->db->query($business_sql)->result();
    	}
    }
    public function get_user_messages($option = 'count' ,$start = 0, $limit = false, $user_id = false , $keyword = false , $order_field = 'id', $order_val = 'ASC')
    {
    	if(!$user_id)
    	{
    		return false;
    	}
    	$business_sql 	= "SELECT `senderID` , `recievedID` , `id`
    						FROM user_messages 
    						WHERE (senderID = $user_id OR recievedID = $user_id) AND channel = 0 AND is_delete <> $user_id
    						ORDER BY $order_field $order_val
    						";
    	//     	echo $business_sql;exit;
    	if($option == 'count')
    	{
    		return $this->db->query($business_sql)->num_rows();
    	}
    	else {
    		if($limit)
    		{
    			$business_sql .= " LIMIT $start,$limit";
    		}
    		$result = $this->db->query($business_sql)->result();
    		if(!empty($result))
    		{
                        $result = $this->remake($result);
    			foreach($result as $key => $row)
    			{
                                $recievedId = $user_id != $row->recievedID ? $row->recievedID : $row->senderID;
    				$messages_in_lists = $this->get_messages_chanel('all',0,$limit,$user_id,$recievedId,'id','ASC');
    				if($messages_in_lists)
    				{
    					foreach($messages_in_lists as $mkey => $msg)
    					{
    						$messages_in_lists[$mkey] = format_output_data($msg);
    					}
    					$result[$key]->messages = $messages_in_lists;
    				}    				
    			}
    			return $result;
    		}
    	}
    	return false;
    }
    public function remake($items = array()){
        $data = array();
        if(count($items) > 1){
            for($i=0 ; $i <  count($items); $i++){
                $m = 0; $j=count($items)-1; //recievedID
                while($j>$i){
                    if(($items[$i]->senderID == $items[$j]->senderID || $items[$i]->senderID == $items[$j]->recievedID) && ( $items[$i]->recievedID == $items[$j]->senderID|| $items[$i]->recievedID == $items[$j]->recievedID)){
                        $m++;
                    }
                    $j--;
                }
                if($m == 0){
                    array_push($data, $items[$i]);
                }
            }
        }
        else{
            $data = $items;
        }
        return $data;
    }
    public function get_messages_chanel($option = 'count' ,$start = 0, $limit = false, $user_id = false , $recieve_id = false, $order_field = 'id', $order_val = 'ASC' ,$revert = false)
    {
    	if(!$user_id && !$recieve_id)
    	{
    		return false;
    	}
    	
    	$business_sql 	= "SELECT DISTINCT m.id, m.senderID, m.recievedID as accepterID , m.content , m.is_read , m.created_date , m.channel ,
                            u.first_name as sender_first_name , u.last_name as sender_last_name, u.profile_photo as sender_profile_photo , u.profile_photo_thumb as sender_profile_photo_thumb , 
                            u2.first_name as accepter_first_name , u2.last_name as accepter_last_name, u2.profile_photo as accepter_profile_photo , u2.profile_photo_thumb as accepter_profile_photo_thumb 
                            FROM user_messages as m
                            LEFT JOIN users as u ON u.id = m.senderID  
                            LEFT JOIN users as u2 ON u2.id = m.recievedID
                            WHERE (m.senderID = $user_id OR m.recievedID = $user_id) AND (m.senderID = $recieve_id OR m.recievedID = $recieve_id) AND is_delete <> $user_id
                            ORDER BY m.$order_field $order_val ";

    	if($option == 'count')
    	{
    		return $this->db->query($business_sql)->num_rows();
    	}
    	else {
    		if($limit)
    		{
    			$business_sql .= " LIMIT $start,$limit";
    		}
                $results = $this->db->query($business_sql);
                
                if(!$revert){
                    return $results->num_rows() ? $results->result() : false;
                }
                
                $data = array();
                if($results->num_rows() > 0){
                    $n = $results->num_rows();
                    $results = $results->result();                       
                    $i = $limit ? ($limit-1) : ($n-1);
                    while($i >= 0){
                        if(isset($results[$i]) && !empty($results[$i])){
                            array_push($data, $results[$i]);
                        }
                        $i--;
                    }
                }    
                return $data;
    		//return $this->db->query($business_sql)->result();
    	}
    }
    public function get_message_by_id($id)
    {
    	return $this->db->get_where('user_messages',array('id'=>$id))->first_row();
    }
    public function add_user_message($data)
    {
    	$this->db->insert('user_messages',$data);
    	return $this->db->insert_id();
    }
    public function user_import_friends($data)
    {
    	return $this->db->insert_batch('user_contact',$data);
    }
    
    public function update_contact_friends_registed($user_id)
    {
    	$sql = "SELECT * FROM user_contact WHERE user_id = $user_id";
    	$result = $this->db->query($sql)->result();
    	if(!empty($result))
    	{
    		$update_registed_data = array();
    		foreach ($result as $row)
    		{
    			if(!empty($row->email) )
    			{    				
    				//check member exist
    				$checkMember = $this->checkMember($row->email);
    				if($checkMember)
    				{    					
    					$update_data = array(
    						'id' 		=> $row->id,
    						'registed' 	=> $checkMember->id,
    					);
    					array_push($update_registed_data, $update_data);
    				}
    				
    			}
    		}
    		if(!empty($update_registed_data))
    		{
    			$this->db->update_batch('user_contact',$update_registed_data,'id');
    		}
    	}
    	return false;
    }
    public function checkContactExist($user_id , $email_contact)
    {
    	return $this->db->get_where('user_contact',array('user_id'=>$user_id,'email'=>$email_contact) )->first_row();
    }
    public function get_user_contact($option = 'count' , $order_field = 'id', $order_val = 'DESC' ,$user_id = false , $social_type = '' , $is_registed = false  )
    {
    	if(!$user_id)
    	{
    		return false;
    	}
    	$this->db->select('c.first_name,c.last_name,c.email,c.registed,c.social_type,c.status,
    						u.profile_photo,u.profile_photo_thumb,u.id');
    	$this->db->from('user_contact as c');
    	$this->db->join('users as u', 'u.email = c.email AND u.active = 1');
    	$this->db->where('c.user_id',$user_id);
    	if($social_type!='')
    	{
    		$this->db->where('c.social_type',$social_type);
    	}
    	if($is_registed)
    	{
    		$this->db->where('c.registed !=',0);
    	}
    	if($option == 'count')
    	{
    		return $this->db->get()->num_rows();
    	}
    	else {
    		$this->db->order_by("u.$order_field",$order_val);
    		return $this->db->get()->result();
    	}
    }
    public function checkUserIsFriend($user_id,$friend_email,$status = 1)
    {
    	$where['user_id'] = $user_id;
    	$where['email'] = $friend_email;
    	$where['status'] = $status;
    	return $this->db->get_where('user_contact',$where)->first_row();
    }
    public function checkUserIsNotFriend($user_id,$friend_email)
    {
    	$where['user_id'] = $user_id;
    	$where['email'] = $friend_email;
    	$where['status !='] = 1;
    	return $this->db->get_where('user_contact',$where)->first_row();
    }
    public function getUserFriend($user_id,$friend_email){
        $this->db->where('user_id', $user_id);
        $this->db->where('email', $friend_email);
        $result = $this->db->get('user_contact');
        if(!($result->num_rows() > 0)){
            $this->addNewUserFriendContact($user_id,$friend_email);
        }
    	
        $where['user_id'] = $user_id;
    	$where['email'] = $friend_email;
    	return $this->db->get_where('user_contact',$where)->first_row();
    }
    public function addNewUserFriendContact($user_id, $friend_email = '', $contactId = ''){
        $user_friend = !empty($friend_email) ? $this->getMemberByEmail($friend_email) : $this->getMemberByMemberID($contactId);
        if($user_friend){
            $data = array(
                'user_id' => $user_id,
                'email' => $user_friend->email,
                'first_name' => $user_friend->first_name,
                'last_name' => $user_friend->last_name,
                'phone' => $user_friend->phone,
                'registed' => $user_friend->id,
                'status' => 0,
                'social_type' => $user_friend->facebook_id ? 1 : 0,
            );
            
            $this->db->insert('user_contact', $data);
        }
        return false;
    }
    public function update_contact($data,$id)
    {
    	$this->db->where('id',$id);
    	return $this->db->update('user_contact',$data);
    }
    public function delete_contact($user_id,$friend_user_id)
    {
    	$this->db->where('user_id',$user_id);
    	$this->db->where('registed',$friend_user_id);
    	$this->db->delete('user_contact');
    }
    public function findInstallObjectByToken($token)
    {
    	//include_once APPPATH . 'libraries/Parse.php';
    
    	$this->load->library('parse');
    	$this->parse->setDatabase($this->db);
    	$queryUserObj = $this->parse->ParseQuery('_Installation');
    	$queryUserObj->where('deviceToken', $token);
    	$rs = $queryUserObj->find();
    	if (empty($rs->results)) {
    		return false;
    	}
    	foreach($rs->results as $v){
    		return $v;
    	}
    }
    public function add_comment_review($data)
    {
    	$this->db->insert('user_comments',$data);
    	return $this->db->insert_id();
    }
    public function get_media_by_id($media_id, $member_id)
    {
        return $this->db->get_where('user_media',array('id'=>$media_id, 'user_id' => $member_id))->first_row();
    }
     public function get_medias_by($field, $value)
    {
    	return $this->db->get_where('user_media',array($field=>$value))->result();
    }
    public function get_media_likes($media_id)
    {
    	return $this->db->get_where('user_likes',array('media_id'=>$media_id))->result();
    }
    public function get_likes_by($field, $value)
    {
    	return $this->db->get_where('user_likes',array($field => $value))->result();
    }
    public function delete_media($media_id)
    {
        //delete media like
        $likes = $this->get_media_likes($media_id);
        if($likes){
            foreach ($likes as $like){
                $this->delete_like($like->id);
            }
        }
        
        //delete media
    	$this->db->where('id',$media_id);
    	$this->db->delete('user_media');
    }
    public function get_user_feeds($option = 'count', $user_id = false , $start = 0, $limit= API_NUM_RECORD_PER_PAGE, $sort_field = 'id', $sort_value = 'DESC',$activity_types = array())
    {
    	if(!$user_id)
    	{
    		return false;
    	}
    	$this->db->select('a.id,a.activity_type,a.source_id,a.created_date');
    	$this->db->from('user_activities as a');
    	$this->db->where('a.user_id',$user_id);
    	
    	if(!empty($activity_types)){
    		$this->db->where_in('a.activity_type', $activity_types);
    	}
    	
    	if($option == 'count')
    	{
    		$result = $this->db->get()->num_rows();
    	}
    	else 
    	{
    		$this->db->limit($limit,$start);
    		$this->db->order_by('a.'.$sort_field,$sort_value);     		   		    
    		$result = $this->db->get()->result();
    	}
    	return $result;
    	
    }
    public function insert_user_activity($data)
    {    	
    	return $this->db->insert('user_activities',$data);
    }
    public function add_user_options($data){
    	$this->db->insert('user_options',$data);
    	return $this->db->insert_id();
    }
    public function get_user_options($user_id){
    	$row = $this->db->order_by('id','DESC')->get_where('user_options',array('user_id'=>$user_id) )->first_row();
    	return $row;
    }
    public function update_user_options($data,$user_id){
    	$this->db->where('user_id',$user_id);
    	return $this->db->update('user_options',$data);
    }
    public function add_member_logged($user_id, $device_token = false , $device_type = false){
    	$data = array(
    			'user_id' => $user_id,
    			'device_token' => $device_token,
    			'device_type' => $device_type,
    			'token'=> $this->_generateToken()
    	);
    	$this->db->insert('user_logged',$data);
    	return $this->db->insert_id();
    }
    public function get_member_logged($id){
    	$row = $this->db->get_where('user_logged',array('id'=>$id))->first_row();
    	return $row;
    }
    
    public function get_media_message($option = 'count', $start = 0 , $limit = false , $order_field = 'id' , $order_val = 'ASC' , $message_id , $status = ''){
    	if(!$message_id)
    	{
    		return false;
    	}
    	$this->db->select('m.*');
    	$this->db->from('user_media as m');
    	$this->db->where('m.message_id',$message_id);
    
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
    	}
    	return $result;
    }
    
    public function deleteUserMessage($message_id, $user_id, $all = false){
        $message = $this->get_message_by_id($message_id);
        if(!$message){
            return false;
        }
        $recievedId = $user_id != $message->recievedID ? $message->recievedID : $message->senderID;
        $messages = $this->get_messages_chanel('all', false, false, $user_id, $recievedId, 'id', 'DESC');
        if($all){
            if($messages){
                foreach ($messages as $m){
                    $this->deleteMessage($m->id, $user_id);
                }
            }
            
            return true;
        }
        else{
            return $this->deleteMessage($message->id, $user_id);
        }
    }
    
    public function deleteMessage($message_id, $user_id){
        $message = $this->get_message_by_id($message_id);
        if(!$message){
            return false;
        }
        if($message->channel == 0){
            $recievedId = $user_id != $message->recievedID ? $message->recievedID : $message->senderID;
            $messages = $this->get_messages_chanel('all', false, false, $user_id, $recievedId, 'id', 'ASC');
            if(count($messages) > 1){
                $this->db->where('id', $messages[1]->id);
                $this->db->update('user_messages', array('channel' => 0));
            }
        }
        
        if($message->is_delete != 0 && $message->is_delete != $user_id){
            $this->db->where('id', $message_id);
            $this->db->delete('user_messages');
        }
        else{
            $this->db->where('id', $message_id);
            $this->db->update('user_messages', array('is_delete' => $user_id));
        }
        return true;
    }
    
    public function getMemberByEmail($email){
        $this->db->where('email', $email);
        $this->db->where('active', 1);
        $result = $this->db->get('users');
        if($result->num_rows() > 0){
            return $result->row();
        }
        return FALSE;
    }
                    
    public function checkNonActiveMember($email){
        $this->db->where('email', $email);
        $this->db->where('active', 0);
        $this->db->where('activation_code!=', NULL);
        $result = $this->db->get('users');
        if($result->num_rows() > 0){
            return TRUE;
        }
        return FALSE;
    }

    public function check_user_friend_status($friend_user, $owner_user){
        $this->db->where('user_id', $owner_user);
        $this->db->where('registed', $friend_user);
        $result = $this->db->get('user_contact');
        if($result->num_rows() > 0){
            $result = $result->row();
            return $result->status;
        }
        return 0;
    }

    //('all', $start, $limit, $keyword, $sort_by, $sort_value);
    public function get_users($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $keyword = false , $sort_field = 'first_name',$sort_value = 'ASC')
    {
        $this->db->select('*, CONCAT(first_name, \'   \', last_name) full_name', false);
        $this->db->from('users');
        $this->db->where('active',1);
        
        if($keyword){
            $query = 'CONCAT(first_name, " ", last_name) LIKE "%'.$keyword.'%"';
            $this->db->where($query);
        }
        
        if($option =='count')
        {
            $result = $this->db->get()->num_rows();         
        }
        else
        {
            $this->db->order_by($sort_field,$sort_value);
            $this->db->limit($limit,$start);        
            $result = $this->db->get()->result();
        }
        
        return $result;         
    }

	public function unfriend($userId, $friendId) {

		//$this->db->delete('user_friends', array('user_id' => $userId, 'friend_id' => $friendId));
		$deleteQuery = "DELETE FROM user_friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";

		$this->db->query($deleteQuery, array($userId, $friendId, $friendId, $userId));

		$updateQuery = "UPDATE user_contact SET status = 0 WHERE (user_id = ? AND registed = ?) OR (user_id = ? AND registed = ?)";

		$this->db->query($updateQuery, array($userId, $friendId, $friendId, $userId));
	}

	public function getTotalFriendRequest($userId) {

		/*
		 * 0 default; 1 active, 2 sender pending , 3 approve pending; 4 reject , 5 block
		 */
		$query = $this->db->get_where("user_contact", array("user_id" => $userId, "status" => 3));

		return $query->num_rows();
	}

	public function cancelFriendRequest( $userId, $senderId ) {

		$updateQuery = "UPDATE user_contact SET status = 0 WHERE (user_id = ? AND registed = ?) OR (user_id = ? AND registed = ?)";

		$this->db->query($updateQuery, array($userId, $senderId, $senderId, $userId));
	}

	public function getTotalBookmarks( $userId ) {

		$query = $this->db->get_where("user_bookmarks", array("user_id" => $userId));

		return $query->num_rows();
	}

	public function getUserOptions($userId) {
		$this->db->select("ns.title, ns.content, op.user_nearby_status, op.location_city");
		$this->db->from("user_options op");
		$this->db->join("user_nearby_status ns", "ns.id = op.user_nearby_status", "LEFT");
		$this->db->where("op.user_id", $userId);
		//$query = $this->db->get_where("user_options op", array("user_id" => $userId));

		$query = $this->db->get();
		if( $query->num_rows() > 0) {
			$row = $query->row();
			return array(
				"userStatus" => array(
					ID		=> $row->user_nearby_status,
					TITLE	=> $row->title,
					CONTENT	=> $row->content,
				),
				LATITUDE	=> !empty($row->location_city) ? substr($row->location_city, 0, strpos($row->location_city, ",")) : 0,
				LONGITUDE	=> !empty($row->location_city) ? substr($row->location_city, strpos($row->location_city, ",") + 1, strlen($row->location_city)) : 0,
			);
		} else {
			return array();
		}

	}

	public function getUserContact( $userId, $contactId ) {
		$query = $this->db->get_where("user_contact", array("user_id" => $userId, "registed" => $contactId));
		return $query->num_rows() > 0 ? $query->row() : false;
	}
}