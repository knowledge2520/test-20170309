<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pet_talk_model extends MY_Model {

    /**
     * Hooks
     *
     * @var object
     * */
    protected $_hooks;
    protected $table_name = "pet_talk_topics";
    protected $key = "id";
    protected $soft_deletes = FALSE;
    protected $date_format = "int";
    protected $log_user = FALSE;
    protected $set_created = true;
    protected $set_modified = false;
    protected $created_field = "created_date";
    protected $modified_field = "modified_date";

    /*
      Customize the operations of the model without recreating the insert, update,
      etc methods by adding the method names to act as callbacks here.
     */
    protected $before_insert = array();
    protected $after_insert = array();
    protected $before_update = array();
    protected $after_update = array();
    protected $before_find = array();
    protected $after_find = array();
    protected $before_delete = array();
    protected $after_delete = array();

    /*
      For performance reasons, you may require your model to NOT return the
      id of the last inserted row as it is a bit of a slow method. This is
      primarily helpful when running big loops over data.
     */
    protected $return_insert_id = TRUE;
    // The default type of element data is returned as.
    protected $return_type = "object";
    // Items that are always removed from data arrays prior to
    // any inserts or updates.
    protected $protected_attributes = array();

    /*
      You may need to move certain rules (like required) into the
      $insert_validation_rules array and out of the standard validation array.
      That way it is only required during inserts, not updates which may only
      be updating a portion of the data.
     */
    protected $validation_rules = array();
    protected $insert_validation_rules = array();
    protected $skip_validation = FALSE;

    /**
     * caching of categories
     *
     * @var array
     * */
    protected $_cache_categories = array();

    /**
     * caching of business and their categories
     *
     * @var array
     * */
    public $_cache_business_in_category = array();

    /**
     * Where
     *
     * @var array
     * */
    public $_where = array();

    /**
     * Limit
     *
     * @var string
     * */
    public $_limit = NULL;

    /**
     * Offset
     *
     * @var string
     * */
    public $_offset = NULL;

    /**
     * Order By
     *
     * @var string
     * */
    public $_order_by = NULL;

    /**
     * Order
     *
     * @var string
     * */
    public $_order = NULL;

    /**
     *
     * @param string $option
     * @param number $member_status
     * @param unknown $member_type
     * @param string $keyword
     * @param string $order_field
     * @param string $sort
     * @param string $limit
     * @param string $offset
     * @return number
     * file_name
     */
    public function getItems($option = 'total', $status = array(), $keyword = '', $order_field = 't.id', $sort = 'ASC', $limit = ADMIN_ITEMS_PERPAGE, $offset = false) {
        //ID    Title   Category    Author  Status  Date Created
        $sql = "SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, discuss.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType,
            ua.id as id, discuss.title as title, discuss.content as content, c.name as category_name, CONCAT_WS(' ', u.first_name, u.last_name) as user_name, discuss.status as status, 'topic' as type

            FROM user_newsfeed_activities ua
            INNER JOIN pet_talk_topics discuss ON discuss.id = ua.topic_id AND ua.newsFeedType = 'ADD_PET_TOPIC'
            INNER JOIN pet_talk_category c ON discuss.category_id = c.id
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PET_TOPIC' AND discuss.status != 2)
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, post.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType,
            ua.id as id, post.title as title, post.content as content, 'Newsfeed Post' as category_name, CONCAT_WS(' ', u.first_name, u.last_name) as user_name, 1 as status, 'post_updated' as type

            FROM user_newsfeed_activities ua
            LEFT JOIN user_post_updated post ON post.id = ua.post_update_id AND ua.newsFeedType = 'ADD_POST_UPDATED'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_POST_UPDATED')
            GROUP BY newsFeedId
            ";


        if ($option == 'total') {
            $results = $this->db->query($sql);
            $return = $results->num_rows();
        } else{


            // $this->db->order_by($order_field, $sort);
            // $this->db->limit($limit, $offset);
            
            $sql .= " ORDER BY $order_field $sort LIMIT $offset, $limit ";
            $results = $this->db->query($sql);

            if($option == 'count_list') {
                $return = $results->num_rows();
            }
            else{
                $return = $results->num_rows() > 0 ? $results->result() : array();
            }
        }

        return $return;

        // if($option == 'all') {

        //     $sql .= " LIMIT $start, $limit";

        //     $query = $this->db->query($sql);

        //     $this->newsfeedData = $query->num_rows() > 0 ? $query->result() : array();
        //     return $this;
        //     //return $this->processingNewsFeed( $query, $userLogedId );

        // } else {
        //     $query = $this->db->query($sql);

        //     return $query->num_rows();
        // }



        // $this->db->select('t.*, u.first_name, u.last_name, u.email, c.name as category_name, CONCAT_WS(" ", u.first_name, u.last_name) as user_name', false);
        // $this->db->from('pet_talk_topics t');
        // $this->db->join('pet_talk_category c', 'c.id = t.category_id', 'left');
        // $this->db->join('users u', 'u.id = t.created_by', 'left');
        // $this->db->group_by('t.id');
        
        // $query = [];

        // if (is_array($status)) {  
        //     $where = [];          
        //     foreach ($status as $s) { 
        //         $where[] = ' t.status = "' . $s . '" ';
        //     }
        //     $quey[] = ' ( ' . implode(' OR ', $where) . ' ) ';  
        // }
        
        // if ($keyword != '') {
        //     $where = [];
        //     $where[] = ' t.id = "' . $keyword . '"';
        //     $where[] = ' t.title LIKE "%' . $keyword . '%"';
        //     $where[] = ' c.name LIKE "%' . $keyword . '%"';
        //     $where[] = ' u.first_name LIKE "%' . $keyword . '%"';
        //     $where[] = ' u.last_name LIKE "%' . $keyword . '%"';       
        //     $where[] = ' CONCAT_WS(" ", u.first_name, u.last_name) LIKE "%' . $keyword . '%"';    
        //     $query[] = ' ( ' . implode(' OR ', $where) . ' ) ';   
        // }

        // if($query){
        //     $this->db->where(implode(' AND ', $query));
        // }
        
        // if ($option == 'total') {
        //     $results = $this->db->get();
        //     $return = $results->num_rows();
        // } else{
        //     $this->db->order_by($order_field, $sort);
        //     $this->db->limit($limit, $offset);
        //     $results = $this->db->get();

        //     if($option == 'count_list') {
        //         $return = $results->num_rows();
        //     }
        //     else{
        //         $return = $results->num_rows() > 0 ? $results->result() : array();
        //     }
        // }
        // return $return;
    }

    private function searchCategories($keyword = '') {
        $this->table_name = 'pet_talk_category';
        $this->where('status !=', 0);
        $this->like('name', $keyword);
        return $this->find_all();
    }

    /**
     * get_users_groups
     *
     * @return array
     * @author Ben Edmunds
     * */
    public function get_pettalk_categories($category_id = FALSE) {
        $this->table_name = 'pet_talk_category';
        return $this->find_all_by('id', $category_id);
//        return $this->db->select('*')
//            ->where('pet_talk_category.id',  $category_id)
//            ->get('pet_talk_category');
    }

    public function detail($id) {

        if (!$id) {
            return false;
        }
        $this->select('t.*, u.id as user_id, u.email, u.first_name, u.last_name, CONCAT_WS(" ", u.first_name, u.last_name) as user_name', false);
        $this->from('pet_talk_topics t');
        $this->join('users u', 'u.id = t.created_by', 'left');
        $this->group_by('t.id');
        return $this->find_by('t.id', $id);
    }
    
    public function deletePetTalk($id = null) {
        if(!$id){
            return false;
        }
        //delete pettalk info comment
        $this->load->model('comments_model');
        $this->comments_model->deleteCommentBy('topic_id', $id);

        //delete pettalk info like
        $this->load->model('members/likes_model');
        $this->likes_model->deleteUserLikeBy('topic_id', $id);

        //delete pettalk info media
        $this->load->model('business/media_model');
        $this->media_model->deleteMediaBy('topic_id', $id);

        //delete pettalk info
        $this->db->delete("pet_talk_topics", array("id" => $id));
    }

    public function update_status($id, $status){
        $data = array(
            'status' => $status,
        );
        $this->update($id, $data);
        return TRUE;
    }

    function getPhoto($id){
        $this->db->where('newfeed_id', $id);
        $results = $this->db->get('user_media');

        if($results->num_rows() > 0){
          $results = $results->result();
          if(reset($results)->post_update_id){
            return $results;
          }
          return reset($results);
        }
        return false;
    }

    function getItem($id){
        if (!$id) {
            return false;
        }
        $sql = "SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, discuss.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType,
            ua.id as id, discuss.title as title, discuss.content as content, c.name as category_name, c.id as category_id, CONCAT_WS(' ', u.first_name, u.last_name) as user_name, discuss.status as status, 'topic' as type

            FROM user_newsfeed_activities ua
            INNER JOIN pet_talk_topics discuss ON discuss.id = ua.topic_id AND ua.newsFeedType = 'ADD_PET_TOPIC'
            INNER JOIN pet_talk_category c ON discuss.category_id = c.id
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PET_TOPIC' AND discuss.status != 2) AND ua.id = $id
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, post.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType,
            ua.id as id, post.title as title, post.content as content, 'Newsfeed Post' as category_name, null as category_id, CONCAT_WS(' ', u.first_name, u.last_name) as user_name, 1 as status, 'post_updated' as type

            FROM user_newsfeed_activities ua
            LEFT JOIN user_post_updated post ON post.id = ua.post_update_id AND ua.newsFeedType = 'ADD_POST_UPDATED'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_POST_UPDATED') AND ua.id = $id
            GROUP BY newsFeedId
            ";
        $result = $this->db->query($sql);

        return $result->num_rows() > 0 ? $result->row() : false;

    }

    public function getPhotoItems($option = 'total', $status = array(), $keyword = '', $order_field = 'id', $sort = 'ASC', $limit = ADMIN_ITEMS_PERPAGE, $offset = false, $newsfeed_id = false){
        if(!$newsfeed_id){
          return false;
        }

        $this->db->select('*');
        $this->db->from('user_media');

        $query = [];

        if($status){
            $where = [];
            foreach($status as $item){
                $where[] = " status = '" . $item . "'";
            }
            $query[] = implode(' OR ', $where);
        }
        $query[] = "newfeed_id = '" . $newsfeed_id . "'";
        $this->db->where(implode(' AND ', $query));
        //echo $this->db->last_query();die();
        if($option == 'total'){
            $results = $this->db->get();
            if($results->num_rows() > 0) {
                $return  = count($results->result());
            }
            else{
                $return  = 0;
            }            
        }elseif($option == 'count_list'){
            $this->db->order_by($order_field, $sort);
            $this->db->limit($limit, $offset);
            $results = $this->db->get();
            $return = $results->num_rows() > 0 ? count($results->result()) : 0;
        } 
        else {
            $this->order_by($order_field, $sort);
            $this->limit($limit, $offset);
            $results = $this->db->get();
            $return = $results->num_rows() > 0 ? $results->result() : false;
        }
        return $return;

    }

    public function getPhotoItem($id = false, $newsfeed_id = false){
        if(!$id || !$newsfeed_id){
          return fasle;
        }

        $this->db->where('id', $id);
        $this->db->where('newfeed_id', $newsfeed_id);
        $result = $this->db->get('user_media');

        if($result->num_rows() > 0){
           return $result->row();
        }
        return FALSE;

    }

    public function deletePhoto($id = false, $newsfeed_id = false){
        if(!$id || !$newsfeed_id){
          return fasle;
        }

        $this->db->where('id', $id);
        $this->db->where('newfeed_id', $newsfeed_id);
        $result = $this->db->get('user_media');

        if($result->num_rows() > 0){
            $item = $result->row();

            // delete photo
            $this->load->helper('upload');
            S3_Upload::removeByKeyValue($item->source);
            S3_Upload::removeByKeyValue($item->photo_thumb);
            
            // remove record
            $this->db->where('id', $id);
            $this->db->where('newfeed_id', $newsfeed_id);
            $this->db->delete('user_media');
            return TRUE;
        }
        return FALSE;

    }
}
