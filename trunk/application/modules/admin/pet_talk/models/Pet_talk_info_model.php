<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pet_talk_info_model extends MY_Model {

    /**
     * Hooks
     *
     * @var object
     * */
    protected $_hooks;
    protected $table_name = "pet_talk_info";
    protected $key = "id";
    protected $soft_deletes = FALSE;
    protected $date_format = "int";
    protected $log_user = FALSE;
    protected $set_created = true;
    protected $set_modified = true;
    protected $created_field = "created_date";
    protected $modified_field = "updated_date";

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
    public function getItems($option = 'total', $keyword = '', $order_field = 't.id', $sort = 'ASC', $limit = ADMIN_ITEMS_PERPAGE, $offset = false, $category = '') {

        $this->db->select("t.*, c.name as category_name,  concat(u.first_name, ' ', u.last_name) AS user_name", false);
        $this->db->from('pet_talk_info t');
        $this->db->join('pet_talk_category c', 'c.id = t.catId', 'left');
        $this->db->join('users u', 'u.id = t.user_id', 'left');
        $this->db->where('c.is_special = 1');
        $this->db->group_by('t.id');
        
        $query = [];        
        
        if ($keyword != '') {
            $where = [];
            $where[] = ' t.id = "' . $keyword . '" ';
            $where[] = ' t.name LIKE "%' . $keyword . '%" ';
            $where[] = ' t.type LIKE "%' . $keyword . '%" ';
            $where[] = ' concat(u.first_name, " ", u.last_name) LIKE "%' . $keyword . '%" ';
            
            $query[] = ' ( ' . implode(' OR ', $where) . ' ) ';
        }    
        
        if( !empty($category) ) {
            $query[] = ' c.name = "' . $category . '" ' ;
        }

        if($query){
            $this->db->where(implode(' AND ', $query));
        }

        if ($option == 'total') {
            $results = $this->db->get();
            $return  = $results->num_rows();  
        }else{
            $this->order_by($order_field, $sort);
            $this->limit($limit, $offset);
            $results = $this->db->get();
            if($option == 'count_list'){
                $return = $results->num_rows();
            }
            else{
                $return = $results->num_rows() > 0 ? $results->result() : array();
            }
        }
        return $return;
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
    public function get_pettalk_categories() {
        $this->table_name = 'pet_talk_category';
        $this->db->where('is_special', 1);
        $results = $this->db->get ($this->table_name);
        return $results->num_rows() > 0 ? $results->result() : array();
    }

    function getCategories() {
        $sql = "select name from pet_talk_category where is_special = 1";
        $query = $this->db->query($sql);
        if($query->num_rows() > 0) {
            $results = $query->result();
            $arrData = array('' => '');
            foreach($results as $item) {
                $arrData[$item->name] = ucwords(strtolower($item->name));
            }
            return $arrData;
        } else {
            return array();
        }
    }

    public function detail($id) {

        if (!$id) {
            return false;
        }
        $this->db->select('t.*, u.id as user_id, u.email, u.first_name, u.last_name');
        $this->db->from('pet_talk_info t');
        $this->db->join('users u', 'u.id = t.user_id', 'left');
        $this->db->group_by('t.id');
        $this->db->where('t.id', $id);
        $result = $this->db->get();
        return $result->num_rows() > 0 ? $result->row() : array();
    }

    function getCoverImage($id){
        $this->db->where('pettalk_info_id', $id);
        $this->db->where('media_type', 'cover');
        $result = $this->db->get('user_media');

        return $result->num_rows() > 0 ? $result->row() : false;
    }

    public function deletePettalkInfo($id = null) {
        if(!$id){
            return false;
        }
        //delete pettalk info comment
        $this->load->model('comments_model');
        $this->comments_model->deleteCommentBy('pettalk_info_id', $id);

        //delete pettalk info like
        $this->load->model('members/likes_model');
        $this->likes_model->deleteUserLikeBy('pettalk_info_id', $id);

        //delete pettalk info media
        $this->load->model('business/media_model');
        $this->media_model->deleteMediaBy('pettalk_info_id', $id);

        //delete pettalk info
        $this->db->delete("pet_talk_info", array("id" => $id));
    }
    
    public function update_status($id, $status){
        $data = array(
            'status' => $status,
        );
        $this->update($id, $data);
        return TRUE;
    }

    public function getPhotoItems($option = 'total', $status = array(), $keyword = '', $order_field = 'id', $sort = 'ASC', $limit = ADMIN_ITEMS_PERPAGE, $offset = false, $pettalk_info_id = false){
        if(!$pettalk_info_id){
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
        $query[] = "pettalk_info_id = '" . $pettalk_info_id . "'";
        $query[] = "media_type != 'cover'";

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

    public function getPhotoItem($id = false, $pettalk_info_id = false){
        if(!$id || !$pettalk_info_id){
          return fasle;
        }

        $this->db->where('id', $id);
        $this->db->where('pettalk_info_id', $pettalk_info_id);
        $this->db->where('media_type!=', 'cover');
        $result = $this->db->get('user_media');

        if($result->num_rows() > 0){
           return $result->row();
        }
        return FALSE;

    }

    public function deletePhoto($id = false, $pettalk_info_id = false){
        if(!$id || !$pettalk_info_id){
          return fasle;
        }

        $this->db->where('id', $id);
        $this->db->where('pettalk_info_id', $pettalk_info_id);
        $result = $this->db->get('user_media');

        if($result->num_rows() > 0){
            $item = $result->row();

            // delete photo
            $this->load->helper('upload');
            S3_Upload::removeByKeyValue($item->source);
            S3_Upload::removeByKeyValue($item->photo_thumb);
            
            // remove record
            $this->db->where('id', $id);
            $this->db->where('pettalk_info_id', $pettalk_info_id);
            $this->db->delete('user_media');
            return TRUE;
        }
        return FALSE;

    }
}
