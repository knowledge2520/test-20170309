<?php
/**
 * 
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @created_date: May 12, 2015
 * @file: file_name
 * @todo:
 */
class Petshop_model extends CI_Model {
	
	public function addCategory($data)
	{
		$this->db->insert('pet_shop_category',$data);
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
	public function get_list_category($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $keyword = false , $status = 1 , $sort_field = 'sort', $sort_val = 'ASC', $role = 'parent', $category_id = false)
	{
		$where = "WHERE 1 AND status = $status";		
                if($role == 'parent'){
                    $where.= " AND parent = 0";
                }
                else{
                    if($category_id){
                         $where.= " AND parent = " . $category_id;
                    }
                }
		//set keyword
		if($keyword)
		{
			$where .= " AND name LIKE '%$keyword%' ";
		}		
		
		$query = "SELECT * FROM pet_shop_category $where ";		 
		if($option == 'count')
		{
			return  $this->db->query($query)->num_rows();
		}
		else
		{
			$start= intval($start);//start
			$limit= intval($limit);//limit
		
			$query .= " ORDER BY $sort_field $sort_val LIMIT $start , $limit";
			 
			$result = $this->db->query($query)->result();
			return $result;
		}
	}
	public function get_products_by_category($cat_id = false , $user_location = array(), $option = 'count' ,$start = 0, $limit = API_NUM_RECORD_PER_PAGE , $keyword = false , $status = 1 , $sort_field = 'id', $sort_val = 'ASC')
	{
		$where = "WHERE 1 AND p.status = $status";
		//set category
		if($cat_id)
		{
			$where .= " AND p.category_id = $cat_id ";
		}
		//set keyword
		if($keyword)
		{
			$where .= " AND p.name LIKE '%$keyword%' ";
		}
		
                if(!empty($user_location) && $user_location['latitude'] && $user_location['longitude'])
                {
                    $lat = $user_location['latitude'];
                    $lng = $user_location['longitude'];
                    $country = get_address_from_location($lat, $lng);

                    $query = "SELECT p.*, c.name as category_name
					FROM pet_shop_product as p 
					LEFT JOIN pet_shop_category as c ON c.id = p.category_id
					$where AND country = '" . $country . "'";
                }
                else{
                    $query = "SELECT p.*, c.name as category_name
					FROM pet_shop_product as p 
					LEFT JOIN pet_shop_category as c ON c.id = p.category_id
					$where ";
                }
		
                
		if($option == 'count')
		{
			return  $this->db->query($query)->num_rows();
		}
		else
		{
			$start= intval($start);//start
			$limit= intval($limit);//limit
		
			$query .= " ORDER BY p.$sort_field $sort_val, p.id DESC LIMIT $start , $limit";
		
			$result = $this->db->query($query)->result();
			return $result;
		}
	}
	public function get_comments_product($product_id ,$option = 'count' ,$start = 0, $limit = API_NUM_RECORD_PER_PAGE , $keyword = false , $status = 1 , $sort_field = 'id', $sort_val = 'ASC')
	{
		$this->db->select("c.id, c.user_id , c.comment , c.rating , c.created_date,
							CONCAT(u.first_name, ' ' ,u.last_name) AS name,u.profile_photo, u.profile_photo_thumb",FALSE);
		$this->db->from('user_petproduct_comments as c');
		$this->db->join('pet_shop_product as p','c.product_id = p.id');
		$this->db->join('users as u','u.id = c.user_id');
		
		$this->db->where('c.status',$status);
		if($product_id)
		{
			$this->db->where('c.product_id',$product_id);
		}
		//set keyword
		if($keyword)
		{
			$this->db->like('c.comment',$keyword);
		}
		
		if($option == 'count')
		{
			$result = $this->db->get()->num_rows();
			return  $result;
		}
		else
		{
			$this->db->limit($limit,$start);
			$this->db->order_by('c.'.$sort_field,$sort_val);
			$result = $this->db->get()->result();
			return $result;
		}
	}
	public function get_like_product($product_id , $type = 0 ,$option = 'count' ,$start = 0, $limit = API_NUM_RECORD_PER_PAGE , $status = 1 , $sort_field = 'id', $sort_val = 'ASC')
	{
		$where = "WHERE 1 ";
		//set category
		if($product_id)
		{
			$where .= " AND l.product_id = $product_id ";
		}
		else
		{
			return false;
		}
		//set type like or dislike
		if(!$type)
		{
			$type = 0;			
		}
		$where .= " AND l.type = $type ";
		
	
		$query = "SELECT l.* FROM user_likes as l $where ";
		
		if($option == 'count')
		{
			return  $this->db->query($query)->num_rows();
		}
		else
		{
			$start= intval($start);//start
			$limit= intval($limit);//limit
	
			$query .= " ORDER BY c.$sort_field $sort_val LIMIT $start , $limit";
	
			$result = $this->db->query($query)->result();
			return $result;
		}
	}
	public function get_reviews_product($product_id)
	{
		$where = "WHERE 1 ";
		//set category
		if($product_id)
		{
			$where .= " AND r.product_id = $product_id ";
		}
		else
		{
			return false;
		}
		
		$where .= " AND r.status = 1 ";
		
		$query = "SELECT COUNT(*) as total_reviews , SUM(r.rating) as sum_rating FROM user_petproduct_reviews as r $where ";
	
		$row = $this->db->query($query)->first_row();
		return $row;
	}
	
	public function get_average_rating_product($total_review = 0 , $sum_rating = 0)
	{
		if($total_review == 0 || $sum_rating == 0 )
		{
			return 0;
		}
		else
		{
			return round($total_review / $sum_rating, 0 , PHP_ROUND_HALF_UP);
		}
	}
	
	public function get_product_size($product_id)
	{
		if(!$product_id)
		{
			return false;			
		}
		
                return $this->db->select('s.id, s.size')
                                ->from('pet_shop_product_size s')
                                ->join('pet_shop_product_quantity q', 'q.size_id = s.id', 'left')
                                ->where('s.product_id', $product_id)
                                ->where('q.quantity > q.sell_quantity')
                                ->get()
                                ->result(); 
	}
	public function get_product_color($product_id)
	{
		if(!$product_id)
		{
			return false;
		}
                
                return $this->db->select('c.id, c.color')
                                ->from('pet_shop_product_color c')
                                ->join('pet_shop_product_quantity q', 'q.color_id = c.id', 'left')
                                ->where('c.product_id', $product_id)
                                ->where('q.quantity > q.sell_quantity')
                                ->get()
                                ->result();   
	}	
        public function get_product_with_option($product_id, $color_id, $size_id){
                if(!$product_id || !$color_id || !$size_id)
		{
			return false;
		}
                return $this->db->get_where('pet_shop_product_quantity',array('product_id'=>$product_id, 'color_id'=>$color_id, 'size_id'=>$size_id))->row();
        }
	public function get_product_detail($product_id, $get_comments = false, $get_likes = false, $get_reviews = false ,$get_rating = true , $get_color = false ,$get_size = false , $user_id = FALSE)
	{
		if(!$product_id)
		{
			return false;			
		}
		
		$product = $this->db->get_where('pet_shop_product',array('id'=>$product_id))->first_row();
		if(!empty($product))
		{
			if($get_comments)
			{
				//comments
				$total_comments 	= $this->get_comments_product($product_id,'count',0,API_NUM_RECORD_PER_PAGE,false,1,'id','ASC');
				$comments 			= $this->petshop_model->get_comments_product($product_id,'all',0,API_NUM_RECORD_PER_PAGE,false,1,'id','DESC');
				if(!empty($comments))
				{
					foreach($comments as $ckey => $comment )
					{
						$comments[$ckey] = format_output_data($comment);
					}
					$product->comments = format_output_data($comments);
				}
				else
				{
					$product->comments = array();
				}
				$product->total_comments = $total_comments;
			}
			if($get_likes)
			{
				//likes
				$total_like 				= $this->get_like_product($product_id,0,'count',0,API_NUM_RECORD_PER_PAGE,1,'id','ASC');
				$product->total_like 		= $total_like;
			}
			if($get_reviews)
			{
				//reviews
				$total_reviews				= $this->get_reviews_product($product_id);
				if(!empty($total_reviews))
				{
					$product->total_reviews = $total_reviews->total_reviews;
					$sum_rating = $total_reviews->sum_rating;
				}
				else
				{
					$product->total_reviews = 0;
					$sum_rating = 0;
				}
			}
			if($get_rating)
			{
				if(!isset($product->total_reviews))
				{
					//reviews
					$total_reviews				= $this->get_reviews_product($product_id);
					if(!empty($total_reviews))
					{
						$product->total_reviews = $total_reviews->total_reviews;
						$sum_rating = $total_reviews->sum_rating;
					}
					else
					{
						$product->total_reviews = 0;
						$sum_rating = 0;
					}
				}
				
				//ratings
				$rating					= $this->get_average_rating_product($product->total_reviews , $sum_rating);
				$product->rating 		= $rating;
			}
			if($get_color)
			{
				//color
				$color					= $this->get_product_color($product_id);
				$product->colors 		= $color;
			}
			if($get_size)
			{
				//size
				$size					= $this->get_product_size($product_id);
				$product->sizes 		= $size;
			}
			if($user_id){
				$user_like = $this->get_product_like_user($user_id,$product_id);
				if($user_like){
					$product->like_type = strval($user_like->type);
				}
				else{
					$product->like_type = '';
				}
			}
						
			return $product;
		}
		return false;		
	}
	public function add_order($data)
	{
		$this->db->insert('user_orders',$data);
		return $this->db->insert_id();
	}
	public function get_products($cat_id = false , $option = 'count', $start = 0 , $limit = false , $keyword = false ,$status = 1 , $sort_field = 'id', $sort_val = 'ASC')
	{
	    	$this->db->select('p.id, p.name , p.description , p.price , p.price_on_sale, p.sale_msg,p.photo,p.photo_thumb,p.category_id,
	    			c.name as category_name, c.photo as category_photo, c.photo_thumb as category_photo_thumb');
	    	$this->db->from('pet_shop_product as p');
	    	$this->db->join('pet_shop_category as c','c.id = p.category_id');
	    	if($status)
	    	{
	    		$this->db->where('p.status',$status);
	    	}
	    	if($cat_id)
	    	{
	    		$this->db->where('p.category_id',$cat_id);
	    	}
	    	if($keyword)
	    	{
	    		$this->db->like('p.name',$keyword);
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
		    	$this->db->order_by('p.'.$sort_field,$sort_val);
		    	
		    	$result = $this->db->get()->result();
	    	}
	    	return $result;
	 }
	 public function get_popular_product($start = 0, $limit = false)
	 {
	 	$this->db->select('p.*,c.total_point,t.total_rating, (c.total_point/t.total_rating) as average_rating');
	 	$this->db->from('pet_shop_product as p');
	 	$this->db->join('(SELECT SUM(rating) as total_point, product_id FROM user_petproduct_comments WHERE status = 1 GROUP BY product_id ORDER BY total_point DESC) as c','p.id = c.product_id');
	 	$this->db->join('(SELECT COUNT(rating) as total_rating, product_id FROM user_petproduct_comments WHERE status = 1 GROUP BY product_id ORDER BY total_rating DESC) as t','p.id = t.product_id');
	 	$this->db->where('p.status',1);
	 	$this->db->order_by('average_rating','DESC');
	 	$this->db->limit($limit,$start);
	 	$result = $this->db->get()->result();
	 	return $result;
	 }
	 public function add_comment_product($data){
	 	$this->db->insert('user_petproduct_comments',$data);
	 	return $this->db->insert_id();
	 }
	 public function get_product_like_user($user_id,$product_id){
	 	$row = $this->db->select('*')->from('user_likes')->where('user_id',$user_id)->where('product_id',$product_id)->get()->row();
	 	return $row;
	 }
	 
         public function get_category_detail($category_id)
	{
		if(!$category_id)
		{
			return false;			
		}
		
		$category = $this->db->get_where('pet_shop_category',array('id'=>$category_id))->first_row();
		if(!empty($category))
		{
			return $category;
		}
		return false;		
	}
        
        public function check_exist_comment($member_id, $comment_id){
            $this->db->where('user_id', $member_id);
            $this->db->where('id', $comment_id);  
            $result = $this->db->get('user_petproduct_comments');
            return $result->num_rows() > 0 ? TRUE : FALSE;
        }
        
        public function delete_comment($comment_id){
            //check valid comment id
            if(!$this->get_comment($comment_id)){
                return FALSE;
            }

            //delete comment
            $this->db->where('id', $comment_id);
            $this->db->delete('user_petproduct_comments');

            return TRUE;
        }
        
        public function get_comment($comment_id){
            return $this->db->where('id', $comment_id)->get('user_petproduct_comments')->row();
        }
        
        public function get_coupon($code){
            $this->db->select('*');
            $this->db->where('code', $code);
            $result = $this->db->get('pet_shop_coupon');
            return $result->num_rows() > 0 ? $result->row() : false;
        }
}