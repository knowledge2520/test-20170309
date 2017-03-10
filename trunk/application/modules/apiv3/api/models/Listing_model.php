<?php
class Listing_model extends CI_Model {

	protected $listingItem;
	protected $listingCat;

	function __construct(){
		// Call the Model constructor
		parent::__construct();
	}
	public function addListing($data)
	{
		$this->db->insert('business_items',$data);
		return $this->db->insert_id();
	}
	public function editListing($data,$id)
	{
		$this->db->where('id',$id);
		return $this->db->update('business_items',$data);
	}
	public function addBookMarkListing($data)
	{
		$this->db->insert('user_bookmarks',$data);
		return $this->db->insert_id();
	}
	public function deleteBookMarkListing($id)
	{
		return $this->db->delete('user_bookmarks',array('id'=>$id));
	}
	/**
	 *
	 * @param string $option
	 * @param number $start
	 * @param string $limit
	 * @return unknown
	 *file_name
	 */
	function get_listing_categories($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $keyword = false , $sort_field = 'order',$sort_value = 'ASC')
	{
		$this->db->select('*');
		$this->db->from('business_category');
		$this->db->where('status',1);

		if($keyword){
			$this->db->like('name',$keyword);
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
	public function get_category_detail($id)
	{
		return $this->db->get_where('business_category', array('id' => $id))->first_row();
	}
	/**
	 *
	 * @param string $option
	 * @param number $start
	 * @param string $limit
	 * @param unknown $cat_id
	 * @param unknown $user_location
	 * @return unknown
	 *file_name
	 */
	function get_listings_by_category($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $cat_id , $user_location = array(), $keyword = false, $sort_type = FALSE, $sort_value = 'ASC')
	{

		$where = " WHERE 1 = 1 ";
		//set where category
		if($cat_id)
		{
			$where .= " AND bc.business_category_id = $cat_id ";
		}
		//set keyword
		if($keyword)
		{
			/*$where .= " AND b.name LIKE '%$keyword%' ";
            $where .= " OR b.address LIKE '%$keyword%' ";*/
			$keyword = "%" . $keyword . "%";
			$where .= " AND b.name LIKE ? ";
			$where .= " OR b.address LIKE ? ";
		}

		$where .= " AND b.status = 1 ";

		if(!empty($user_location) && $user_location['latitude'] && $user_location['longitude'])
		{
			$lat = $user_location['latitude'];
			$lng = $user_location['longitude'];
			$search_distance = $user_location['search_distance'];

			//sort
			switch ($sort_type){
				case 'newest':
					$sort_type = 'b.id';
					break;
				case 'average_rating':
					$sort_type = 'rating';
					break;
				default:
					$sort_type = 'distance';
					break;
			}

			//get listing with user location
			$query = "SELECT b.*,
    				c.name as category_name, c.id as category_id ,
    				COUNT(r.id) as total_review,
				    SUM(r.rate) as total_point,
				    ROUND (SUM(r.rate) / COUNT(r.id),1) as rating,
    				( 6371 * acos( cos( radians($lat) ) * cos( radians( b.latitude ) ) * cos( radians( b.longitude ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( b.latitude ) ) ) ) AS distance				
    				FROM 
    					( business_items as b 
    						LEFT JOIN business_items_category as bc ON b.id = bc.business_id
    						LEFT JOIN business_category as c ON c.id = bc.business_category_id
    						LEFT JOIN user_reviews AS r ON r.business_id = b.id)
    				$where
    				GROUP BY bc.business_id HAVING distance < $search_distance ORDER BY $sort_type $sort_value, b.name ASC
    			";
			if ($option == 'count') {
				return $this->db->query ( $query, array($keyword, $keyword) )->num_rows ();
			} else {
				$start = intval ( $start ); // start
				$limit = intval ( $limit ); // limit

				$query .= " LIMIT $start , $limit";

				$result = $this->db->query ( $query, array($keyword, $keyword) )->result ();
				return $result;
			}
		}
		else
		{
			if($option == 'count')
			{
				return  0;
			}
			else
			{
				return array();
			}
//     		//sort
//     		switch ($sort_type){
//     			case 'average_rating':
//     				$sort_type = 'rating';
//     				break;
//     			default:
//     				$sort_type = 'b.id';
//     				break;
//     		}

//     		//get listing 
//     		$query = "SELECT b.*, 
//     				c.name as category_name, c.id as category_id,
//     				COUNT(r.id) as total_review,
//     				SUM(r.rate) as total_point,
//     				ROUND (SUM(r.rate) / COUNT(r.id),1) as rating 
//     				FROM 
//     					(business_items as b 
//     						LEFT JOIN business_items_category as bc ON b.id = bc.business_id
//     						LEFT JOIN business_category as c ON c.id = bc.business_category_id
//     						LEFT JOIN user_reviews AS r ON r.business_id = b.id)
//     				$where
//     				GROUP BY bc.business_id ORDER BY $sort_type $sort_value
//     			";
		}
	}
	function get_listing_detail($listing_id , $get_types = false , $get_media = false , $get_average_rating = false , $get_reviews = false , $get_tips = false , $user_id = FALSE)
	{
		$row =  $this->db->get_where('business_items', array('id' => $listing_id, 'status' => 1))->first_row();
		if(isset($row) && !empty($row)){
			$row->photo_path = $row->photo;
		}
		if($get_types)
		{
			//get types
			$types = $this->get_types_listing($listing_id);
			if($types){
				$row->type = $types;
			}
			else
			{
				$row->type = array();
			}
		}
		if($get_media)
		{
			//get photo of listing
			$total_media = $this->get_media_by_listing('count', 0  , false , $listing_id);
			$list_media = $this->get_media_by_listing('all', 0  , false , $listing_id, 'id' , 'desc');
			if($list_media)
			{
				foreach($list_media as $mk => $media)
				{
					$list_media[$mk] = format_output_data($media);
				}
			}

			$row->total_media = $total_media;
			$row->media = $list_media;
		}
		if($get_average_rating)
		{
			//get average rating
			$average_rating = $this->get_listing_average_rating($listing_id);
			$row->rating = $average_rating;
		}
		if($get_reviews)
		{
			$this->load->model('review_model');

			$total_reviews 	= $this->review_model->get_reviews_by_listing('count', 0 , false , $listing_id);
			$reviews 		= $this->review_model->get_reviews_by_listing('list', 0 , API_NUM_RECORD_PER_PAGE , $listing_id, 'created_date' , 'DESC');

			$row->total_review 	= $total_reviews;
			$row->reviews		= $reviews;
		}
		if($get_tips)
		{
			$this->load->model('tip_model');

			$total_tips 	= $this->tip_model->get_tips_by_listing('count', 0 , false , $listing_id);
			$tips 			= $this->tip_model->get_tips_by_listing('list', 0 , API_NUM_RECORD_PER_PAGE , $listing_id, 'id' , 'DESC' , $user_id);

			$row->total_tip 	= $total_tips;
			$row->tips		= $tips;
		}
		if($user_id){
			//check bookmark status
			$bookmark_status = $this->member_model->check_user_bookmark_listing($user_id,$listing_id);
			if($bookmark_status){
				$row->bookmark_status = strval(1);
			}
			else{
				$row->bookmark_status = strval(0);
			}
		}

		$row = format_output_data($row);

		return $row;
	}
	/**
	 *
	 * @param unknown $listing_id
	 * @param number $total_review
	 * @param number $total_point
	 * @return number
	 *file_name
	 */
	function get_listing_average_rating($listing_id, $total_review = 0 , $total_point = 0) {

		if(!$listing_id)
		{
			return 0;
		}
		if(!$total_review || !$total_point) {
			$result = $this->db->query("select count(*) as total_review, SUM(rate) as total_point  from user_reviews where status = 1 and business_id = $listing_id ");
			if(!$total_review)
			{
				$total_review = ($result->num_rows() > 0) ? $result->first_row()->total_review : 0;
			}
			if(!$total_point)
			{
				$total_point = ($result->num_rows() > 0) ? $result->first_row()->total_point : 0;
			}
		}

		if($total_review && $total_point > 0)
		{
			$average_rating = round($total_point/$total_review, 0, PHP_ROUND_HALF_UP);
		}
		else
		{
			$average_rating = 0;
		}


		return $average_rating;
	}
	function get_media_by_listing($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE, $listing_id = false , $order_field = 'id', $order_val = 'ASC' , $user_id = FALSE, $user_info = true)
	{
		if(!$listing_id)
		{
			return false;
		}
		$this->db->select('m.id, m.source , m.photo_thumb , m.type , m.business_id,m.user_id');
		$this->db->from('user_media as m');
		$this->db->where("m.status = 1");
		if($listing_id){
			$this->db->where(" m.business_id = $listing_id ");
		}
		if($user_id){
			$this->db->where("m.user_id = $user_id");
		}
//     	$query = "SELECT m.id, m.source , m.photo_thumb , m.type , m.business_id  
//     				FROM user_media as m
//     			WHERE m.business_id = ? AND m.status = ?";
//     	if($user_id){
//     		$query .= " AND m.user_id = $user_id"; 
//     	}
//     	$params = array();
//     	$params[] = $listing_id; //$listing_id
//     	$params[] = 1; //1:active, need to change later

		if($option =='count')
		{
			return  $this->db->get()->num_rows();
		}
		else
		{
			if(!empty($order_field) )
			{
//     			$query .= " ORDER BY `m`.$order_field $order_val";
				$this->db->order_by(`m`.$order_field,$order_val);
			}
			if($limit)
			{
				$this->db->limit($limit,$start);
//     			$query .= " LIMIT ? , ?";
//     			$params[] = intval($start);//start
//     			$params[] = intval($limit);//limit
			}

			$result = $this->db->get()->result();

			//get user info
			if(!empty($result) && $user_info){
				$this->load->model('member_model');
				foreach($result as $key => $rs){
					$user_info = $this->member_model->getMemberByMemberID($rs->user_id,true,false,true,true);
					if($user_info){
						$result[$key]->user_info = format_output_data($user_info);
					}
				}
			}
			return $result;
		}
	}
	public function get_types_listing($listing_id)
	{
		//get type
		$result = $this->db->select('business_category_id')->from('business_items_category')->where('business_id',$listing_id)->get()->result();
		if(!empty($result))
		{
			$data = array();
			foreach($result as $bc_id)
			{
				$categoty_info = $this->get_category_detail($bc_id->business_category_id);
				if(!empty($categoty_info))
				{
					$data[] = $categoty_info->name;
				}
			}
			return $data;
		}
		return false;
	}
	public function check_user_added_bookmark($user_id, $listing_id)
	{
		if(!$user_id || !$listing_id)
		{
			return false;
		}
		return $this->db->get_where('user_bookmarks',array('user_id'=>$user_id, 'business_id'=>$listing_id) )->first_row();
	}
	/**
	 *
	 * @param string $option
	 * @param number $start
	 * @param string $limit
	 * @param unknown $cat_id
	 * @param unknown $user_location
	 * @return unknown
	 *file_name
	 */
	function search_listings($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $cat_id = false , $user_location = array(), $keyword = false, $sort_type = 'distance', $sort_value = 'ASC')
	{


		if(!empty($user_location) && $user_location['latitude'] && $user_location['longitude'])
		{
			$this->db->select('b.*');
			$lat = $user_location['latitude'];
			$lng = $user_location['longitude'];
			$search_distance = $user_location['search_distance'];

			$this->db->select("( 6371 * acos( cos( radians($lat) ) * cos( radians( b.latitude ) ) * cos( radians( b.longitude ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( b.latitude ) ) ) ) AS distance");
			$this->db->having("distance < $search_distance");
			$this->db->order_by($sort_type,$sort_value);

			$this->db->select('c.name as category_name, c.id as category_id');

			$this->db->from('business_items as b');
			$this->db->join('business_items_category as bc','b.id = bc.business_id');
			$this->db->join('business_category as c','c.id = bc.business_category_id');

			$this->db->where('b.status',1);

			//set where category
			if($cat_id)
			{
				$this->db->where('bc.business_category_id',$cat_id);
			}
			//set keyword
			if($keyword)
			{
				//search by listing name
				//$keyword = $this->db->escape_str($keyword);
				$this->db->like('b.name', $keyword);
			}
			$this->db->group_by('bc.business_id');

			//return value
			if($option == 'count')
			{
				$result = $this->db->get()->num_rows();
			}
			else
			{
				$this->db->limit(intval($limit),intval($start) );
				$result = $this->db->get()->result();
			}
		}
		else
		{
			//$this->db->order_by('id','DESC');
			if($option == 'count')
			{
				$result = 0;
			}
			else
			{
				$result = array();
			}
			return $result;
		}
		return $result;
	}
	public function remove_all_categories($business_id){
		$this->db->where('business_id',$business_id);
		return $this->db->delete('business_items_category');
	}
	function calculate_google_distance($lat1, $lat2, $long1, $long2)
	{
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=pl-PL";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		$response_a = json_decode($response, true);
		if($response_a['rows'][0]['elements'][0]['status'] == 'OK'){
			$dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
			$time = $response_a['rows'][0]['elements'][0]['duration']['text'];

			return round($dist/1000,3);
		}
		return "";
	}
	/*::  Passed to function:                                                    :*/
	/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
	/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
	/*::    unit = the unit you desire for results                               :*/
	/*::           where: 'M' is statute miles (default)                         :*/
	/*::                  'K' is kilometers                                      :*/
	/*::                  'N' is nautical miles									 :*/
	function calculate_distance($lat1, $lat2, $lon1, $lon2, $unit = "K") {

		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		if ($unit == "K") {
			return ($miles * 1.609344);
		} else if ($unit == "N") {
			return ($miles * 0.8684);
		} else {
			return $miles;
		}
	}

	public function check_exist_comment($review_id, $comment_id){
		$this->db->where('review_id', $review_id);
		$this->db->where('id', $comment_id);
		$result = $this->db->get('user_comments');
		return $result->num_rows() > 0 ? TRUE : FALSE;
	}

	public function get_comment($comment_id){
		$this->db->where('id', $comment_id);
		$result = $this->db->get('user_comments');
		return $result->num_rows() > 0 ? $result->row() : FALSE;
	}

	public function delete_comment_review($comment_id){
		//check valid comment id
		if(!$this->get_comment($comment_id)){
			return FALSE;
		}

		//delete comment
		$this->db->where('id', $comment_id);
		$this->db->delete('user_comments');

		return TRUE;
	}

	public function get_tip_by($field, $value){
		return $this->db->get_where('user_tips', array($field => $value))->row();
	}

	public function delete_tip($tip_id){
		if(!$tip_id){
			return FALSE;
		}

		if(!$this->get_tip_by('id', $tip_id)){
			return FALSE;
		}

		$this->load->model('member_model');
		//delete media
		$medias = $this->member_model->get_medias_by('tip_id', $tip_id);
		if($medias){
			foreach ($medias as $media){
				$this->member_model->delete_media($media->id);
			}
		}

		//delete like
		$likes = $this->member_model->get_likes_by('tip_id', $tip_id);
		if($likes){
			foreach ($likes as $like){
				$this->member_model->delete_like($like->id);
			}
		}

		//delete note
		$this->db->where('id', $tip_id);
		$this->db->delete('user_tips');

		return TRUE;
	}

	public function get_review_by($field, $value){
		return $this->db->get_where('user_reviews', array($field => $value))->row();
	}

	public function delete_review($review_id){
		if(!$review_id){
			return FALSE;
		}

		if(!$this->get_review_by('id', $review_id)){
			return FALSE;
		}

		$this->load->model('member_model');
		//delete media
		$medias = $this->member_model->get_medias_by('review_id', $review_id);
		if($medias){
			foreach ($medias as $media){
				$this->member_model->delete_media($media->id);
			}
		}

		//delete like
		$likes = $this->member_model->get_likes_by('review_id', $review_id);
		if($likes){
			foreach ($likes as $like){
				$this->member_model->delete_like($like->id);
			}
		}

		//delete comment
		$comments = $this->get_review_comments_by('review_id', $review_id);
		if($comments){
			foreach ($comments as $comment){
				$this->delete_comment_review($comment->id);
			}
		}

		//delete review
		$this->db->where('id', $review_id);
		$this->db->delete('user_reviews');

		return TRUE;
	}

	public function get_review_comments_by($field, $value){
		return $this->db->get_where('user_comments',array($field => $value))->result();
	}

	public function listingPhoto( $newFeedId = false, $newsFeedItemId = false ) {
		$sqlPhotoListing = "SELECT bi.id AS businessId, bi.name, bi.address, bi.country, bi.latitude, bi.longitude, bi.photo, u.id, u.first_name, u.profile_photo, u.profile_photo_thumb, u.last_name, media.id AS mediaId, media.source, media.photo_thumb
        FROM listing_album_photo album
        INNER JOIN business_items bi ON bi.id = album.business_id
        INNER JOIN user_media media ON media.business_id = bi.id AND media.status = 1 AND media.newfeed_id = $newFeedId
        INNER JOIN users u ON media.user_id = u.id
        WHERE media.newfeed_id = ? AND album.id = ?";

		$photoListingQuery = $this->db->query($sqlPhotoListing, array($newFeedId, $newsFeedItemId));

		$this->listingItem = $photoListingQuery->num_rows() > 0 ? $photoListingQuery->row() : array();
		return $this;
	}

	public function listingPhotoTransformer( $item = false ) {

		if( $this->listingItem ) {
			$item = $this->listingItem;
		}

		if( $item ) {
			format_output_data($item);
			$catName = $this->getListingCategories($item->businessId)->getListingCategoryName();
			return array(
				ID        	=> $item->businessId,
				LATITUDE  	=> $item->latitude,
				LONGITUDE 	=> $item->longitude,
				COUNTRY   	=> $item->country,
				ADDRESS   	=> $item->address,
				NAME      	=> $item->name,
				WEBSITE   	=> !empty($item->website) ? $item->website : "",
				CATEGORY  	=> $catName,
				PHOTO     	=> $item->photo,
				TOTAL_REVIEW => $this->checkin_model->getTotalReviews($item->businessId),
				TOTAL_RATING => $this->checkin_model->getTotalRating($item->businessId),
				VISIT     => getTotalVisit((int)$item->businessId),
			);
		} else {
			return array();
		}
	}

	public function deleteListingPhoto( $photoListingId ) {
		$this->db->delete("listing_album_photo", array("id" => $photoListingId));
	}

	public function getListingCategories($listingId) {
		$sql = "SELECT c.id, `name`, description, photo, `order`, status
		FROM business_category c
		INNER JOIN business_items_category bc ON c.id = bc.business_category_id
		WHERE business_id = ?
		GROUP BY bc.business_category_id";
		$query = $this->db->query($sql, array($listingId));
		$this->listingCat = $query->num_rows() > 0 ? $query->result() : array();
		return $this;
	}

	public function getListingCategoryName() {
		if($this->listingCat) {
			$arrItems = array();
			foreach($this->listingCat as $item) {
				array_push($arrItems, $item->name);
			}
			return implode(", ", $arrItems);
		}
		return "";
	}

	/**
	 *
	 * @param string $option
	 * @param number $start
	 * @param string $limit
	 * @param unknown $user_location
	 * @return unknown
	 *file_name
	 */
	function search_listings_category($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $user_location = array(), $keyword = false)
	{
		$where = " WHERE 1 = 1 ";
		//set keyword
		if($keyword)
		{
			$where .=" AND (
            	b.address LIKE '%" . $this->db->escape_like_str(trim($keyword)) . "%' OR
            	c.name LIKE '%" . $this->db->escape_like_str(trim($keyword)) . "%' OR
            	b.name LIKE '%" . $this->db->escape_like_str(trim($keyword)) . "%'
            )";
		}
		$where .= " AND b.status = 1 ";
		$lat = $user_location['latitude'];
		$lng = $user_location['longitude'];
		$search_distance = $user_location['search_distance'];
		//get listing with user location
		if($lat && $lng){
			$query = "SELECT b.*,
				c.name as category_name, c.id as category_id ,
				COUNT(r.id) as total_review,
			    SUM(r.rate) as total_point,
			    ROUND (SUM(r.rate) / COUNT(r.id),1) as rating,
				( 6371 * acos( cos( radians($lat) ) * cos( radians( b.latitude ) ) * cos( radians( b.longitude ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( b.latitude ) ) ) ) AS distance				
				FROM 
					( business_items as b 
						LEFT JOIN business_items_category as bc ON b.id = bc.business_id
						LEFT JOIN business_category as c ON c.id = bc.business_category_id
						LEFT JOIN user_reviews AS r ON r.business_id = b.id)
				$where
				GROUP BY bc.business_id HAVING distance < $search_distance ORDER BY b.name,b.address,c.name, distance ASC
			";
		}else{ // Not have user's location
			$query = "SELECT b.*,
				c.name as category_name, c.id as category_id ,
				COUNT(r.id) as total_review,
			    SUM(r.rate) as total_point,
			    ROUND (SUM(r.rate) / COUNT(r.id),1) as rating			
				FROM 
					( business_items as b 
						LEFT JOIN business_items_category as bc ON b.id = bc.business_id
						LEFT JOIN business_category as c ON c.id = bc.business_category_id
						LEFT JOIN user_reviews AS r ON r.business_id = b.id)
				$where
				GROUP BY bc.business_id ORDER BY b.name ASC
			";
		}
		if ($option == 'count') {
			return $this->db->query ($query)->num_rows ();
		} else {
			$start = intval ( $start ); // start
			$limit = intval ( $limit ); // limit

			$query .= " LIMIT $start , $limit";

			$result = $this->db->query ($query)->result ();
			return $result;
		}
	}

	public function get_country_by($field, $value){
		$this->db->where($field, $value);
		$result = $this->db->get('countries');
		if($result->num_rows() > 0){
			$result = $result->row();
			return $result;
		}	
		return false;
	}
}