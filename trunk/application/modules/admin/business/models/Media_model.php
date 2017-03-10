<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Media_model extends MY_Model
{
    /**
     * Hooks
     *
     * @var object
     **/
    protected $_hooks;

    protected $table_name	    = "user_media";
    protected $key			    = "id";
    protected $soft_deletes	    = FALSE;
    protected $date_format	    = "int";

    protected $log_user 	    = FALSE;

    protected $set_created	    = TRUE;
    protected $set_modified     = FALSE;
    protected $created_field    = "created_date";
    protected $modified_field   = "modified_date";

    /*
        Customize the operations of the model without recreating the insert, update,
        etc methods by adding the method names to act as callbacks here.
     */
    protected $before_insert 	= array();
    protected $after_insert 	= array();
    protected $before_update 	= array();
    protected $after_update 	= array();
    protected $before_find 		= array();
    protected $after_find 		= array();
    protected $before_delete 	= array();
    protected $after_delete 	= array();

    /*
        For performance reasons, you may require your model to NOT return the
        id of the last inserted row as it is a bit of a slow method. This is
        primarily helpful when running big loops over data.
     */
    protected $return_insert_id 	= TRUE;

    // The default type of element data is returned as.
    protected $return_type 			= "object";

    // Items that are always removed from data arrays prior to
    // any inserts or updates.
    protected $protected_attributes = array();

    /*
        You may need to move certain rules (like required) into the
        $insert_validation_rules array and out of the standard validation array.
        That way it is only required during inserts, not updates which may only
        be updating a portion of the data.
     */
    protected $validation_rules 		= array();
    protected $insert_validation_rules 	= array();
    protected $skip_validation 			= FALSE;

    /**
     * caching of categories
     *
     * @var array
     **/
    protected $_cache_categories = array();

    /**
     * caching of business and their categories
     *
     * @var array
     **/
    public $_cache_business_in_category = array();
    /**
     * Where
     *
     * @var array
     **/
    public $_where = array();
    /**
     * Limit
     *
     * @var string
     **/
    public $_limit = NULL;

    /**
     * Offset
     *
     * @var string
     **/
    public $_offset = NULL;
    /**
     * Order By
     *
     * @var string
     **/
    public $_order_by = NULL;
    /**
     * Order
     *
     * @var string
     **/
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
     *file_name
     */
    public function getItems($option = 'total' , $status = array() , $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false, $field = false, $value = false , $my_media = false, $user_id = false, $type = FALSE){
        
        $this->select('m.*, u.first_name, u.last_name, u.email, CONCAT_WS(" ", u.first_name, u.last_name) as full_name', false);
        $this->from($this->table_name . ' m');
        $this->join('users u', 'u.id = m.user_id', 'left');
        
        $where = '(m.pet_id is NULL)';

        if($my_media){
            $this->where('m.user_id', $user_id);
        }
        else{
            if (is_array($status)) {
                $where.= ' AND (m.status = "' . $status[0] . '"';
                foreach ($status as $s) {                
                    $where.= ' OR m.status = "' . $s . '"';
                }
                $where.=')';
                
                $this->where($where);
            }
        }
        
        
        if ($keyword != '') {
            $where = ' (m.id LIKE "%' . $keyword . '%"';
            $where.= ' OR b.name LIKE "%' . $keyword . '%"';
            $where.= ' OR c.name LIKE "%' . $keyword . '%"';
            $where.= ' OR u.first_name LIKE "%' . $keyword . '%"';
            $where.= ' OR u.last_name LIKE "%' . $keyword . '%")';
            
            $this->where($where);
        }
        
        if($type){
            switch ($type){
                case 'business': 
                    $this->select('b.name as business_name, b.id as business_id');
                    $this->join('business_items b', 'b.id = m.business_id', 'left');
                    $this->join('business_items_category bc', 'bc.business_id = b.id', 'left');
                    $this->join('business_category c', 'c.id = bc.business_category_id', 'left');                   
                    $this->where('m.business_id!=', NULL); 
                    $this->where('m.tip_id', NULL); 
                    $this->where('m.review_id', NULL); 
                    break;
                case 'tip': 
                    $this->select('b.name as business_name, b.id as business_id');
                    $this->join('user_tips ut', 'ut.id = m.tip_id', 'left');
                    $this->join('business_items b', 'b.id = ut.business_id', 'left');
                    $this->join('business_items_category bc', 'bc.business_id = b.id', 'left');
                    $this->join('business_category c', 'c.id = bc.business_category_id', 'left');
                     $this->where('m.tip_id!=', NULL); 
                    break;
                case 'review': 
                    $this->select('b.name as business_name, b.id as business_id');
                    $this->join('user_reviews ur', 'ur.id = m.review_id', 'left');
                    $this->join('business_items b', 'b.id = ur.business_id', 'left');
                    $this->join('business_items_category bc', 'bc.business_id = b.id', 'left');
                    $this->join('business_category c', 'c.id = bc.business_category_id', 'left');
                    $this->where('m.review_id!=', NULL); 
                    break;
            }            
        }      
        else{           
            $this->where('m.review_id', NULL);
            $this->where('m.tip_id', NULL);
            $this->where('m.topic_id', NULL);
            $this->where('m.topic_comment_id', NULL);
        }
        //var_dump($where);exit;
        if($field && $value){            
            $this->where('m.'.$field, $value);
        }
        
        //var_dump($where);exit;
        $this->group_by('m.id');
        //var_dump($this->output->enable_profiler(TRUE));
        if($option == 'total'){
            $results = $this->find_all();
            if($results) {
                $return  = count($results);
            }
            else{
                $return  = 0;
            }

        }
        elseif($option == 'count_list'){
            $this->order_by($order_field,$sort);
            $this->limit($limit,$offset);
            $return  = count($this->find_all());
        }
        else{
            $this->order_by($order_field,$sort);
            $this->limit($limit,$offset);
            $return  = $this->find_all();
        }
        return $return;
    }

    public function detail($id)
    {
        if(!$id)
        {
            return false;
        }
        $this->select('*');
        //$this->join('ew_member_address', 'ew_member_address.memberId = ew_members.id');

        return  $this->find_by($this->table_name .'.id',$id);
    }
    
    public function count_approve_media($type = 'business'){
        switch ($type){
            case 'business': $this->db->where('business_id!=', NULL); $this->db->where('tip_id', NULL); $this->db->where('review_id', NULL);break;
            case 'review': $this->db->where('review_id!=', NULL);break;
            case 'tip': $this->db->where('tip_id!=', NULL);break;
        }
        
        $this->db->where('status', 0);
        return $this->db->count_all_results($this->table_name);
    }
    
    public function active_media($id){
        if($this->detail($id)){
            $data = array(
                'status' => 1,
            );
            $this->update($id, $data);
            return TRUE;
        }
        return FALSE;
    }
    
    public function reject_media($id){
        if($this->detail($id)){
            $data = array(
                'status' => 3,
            );
            $this->update($id, $data);
            return TRUE;
        }
        return FALSE;
    }

    public function delete_media($id){
        $media = $this->detail($id);
        if($media){
            $this->load->helper('upload');

            S3_Upload::removeByKeyValue($media->source);
            S3_Upload::removeByKeyValue($media->photo_thumb);


            return TRUE;
        }
        return FALSE;
    }

    public function deleteMediaBy($field, $value){
        if(!$field || !$value){
            return false;
        }
        $this->db->where($field, $value);  
        $results = $this->db->get('user_media');
        if($results->num_rows() > 0){
            // $this->load->helper('upload');
            // $results = $results->result();
            // foreach ($results as $key => $item) {
            //     S3_Upload::removeByKeyValue($item->source);
            //     S3_Upload::removeByKeyValue($item->photo_thumb);
            // }
            $this->db->delete('user_media', array($field => $value));
        }
    }

    public function getMediaCoverBy($field, $value){
        $this->db->where($field, $value);  
        $this->db->where('type', 'cover');  
        $result = $this->db->get('user_media');

        return $result->num_rows() > 0 ? $result->row() : false;
    }

    public function getMediaBy($field, $value){
        $this->db->where($field, $value);  
        $result = $this->db->get('user_media');

        return $result->num_rows() > 0 ? $result->row() : false;
    }

    
    public function excuteItem($field, $value, $action = 'delete', $force_delete = false){
        if(!$field || !$value){
            return false;
        }

        $this->db->where($field, $value);
        $results = $this->db->get($this->table_name);

        if($results->num_rows() > 0){
            switch ($action) {
                case 'restore':
                    $this->db->where($field, $value);
                    $this->db->update($this->table_name, array('status' => 1));
                    break;
                
                case 'remove':
                    $this->db->where($field, $value);
                    if($force_delete){
                        $this->db->delete($this->table_name);
                    }else{
                        $this->db->update($this->table_name, array('status' => 2));
                    }
                    break;

                default:
                    $this->db->where($field, $value);
                    $this->db->update($this->table_name, array('status' => 0));
                    break;
            }    
            
            return true;    
        }

        return false;
    }
}
