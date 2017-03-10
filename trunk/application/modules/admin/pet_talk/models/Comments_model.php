<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comments_model extends MY_Model
{
    /**
     * Hooks
     *
     * @var object
     **/
    protected $_hooks;

    protected $table_name	    = "user_comments";
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

    public $tables                      = array(
        'business_items_category'       => 'business_items_category',
        'business_category'             => 'business_category',
        'business_items'                => 'business_items',
    );
    public $join                        = array(
        'business_category'             => 'business_category_id',
        'business_items'                => 'business_id',
    );

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
    public function getItems($option = 'total' , $status = array() , $keyword = '' , $order_field = 'c.id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){
        
        $this->db->select('c.*, u.first_name, u.last_name, u.email, t.title as topic_title, i.name as topic_info_title, CONCAT_WS(" ", u.first_name, u.last_name) as user_name, coalesce(t.title, i.name) as topic_name, ', false);
        $this->db->from('user_comments c');
        $this->db->join('users u', 'u.id = c.user_id', 'left');
        $this->db->join('pet_talk_topics t', 't.id = c.topic_id', 'left');
        $this->db->join('pet_talk_info i', 'i.id = c.pettalk_info_id', 'left');
        
        $query[] = ' ( c.topic_id IS NOT NULL OR c.pettalk_info_id IS NOT NULL ) ';

        if (is_array($status) && $status) {
            $where = [];
            foreach ($status as $s) {                
                $where[] = ' c.status = "' . $s . '"';
            }

            $query[] = ' ( ' . implode(' OR ', $where) . ' ) ';
        }

        if ($keyword != '') {
            $where = [];
            $where[] = ' c.id = "' . $keyword . '"';
            $where[] = ' t.title LIKE "%' . $keyword . '%"';
            $where[] = ' i.name LIKE "%' . $keyword . '%"';
            $where[] = ' u.first_name LIKE "%' . $keyword . '%"';
            $where[] = ' u.last_name LIKE "%' . $keyword . '%"';
            $where[] = ' CONCAT_WS(" ", u.first_name, u.last_name) LIKE "%' . $keyword . '%"';

            $query[] = ' ( ' . implode(' OR ', $where) . ' ) ';
        }

        $this->db->where(implode(' AND ', $query));   

        $this->db->group_by('c.id');
        if($option == 'total'){
            $results = $this->db->get();
            $return = $results->num_rows();
        }
        else{
            $this->db->order_by($order_field,$sort);
            $this->db->limit($limit,$offset);
            $results = $this->db->get();

            if($option == 'count_list'){
                $return = $results->num_rows();
            }
            else{
                $return = $results->num_rows() > 0 ? $results->result() : false;
            }        
        }
        return $return;
    }
    /**
     * get_users_groups
     *
     * @return array
     * @author Ben Edmunds
     **/
    public function get_pettalk_categories($category_id=FALSE)
    {
        return $this->db->select('*')
            ->where('pet_talk_category.id',  $category_id)
            ->get('pet_talk_category');
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
    
    public function delete_comment($id){
        //check valid comment id
       
       if(!$this->detail($id)){
           return FALSE;
       }
       
//        //delete media in comment
       $this->load->model('members/members_model');
       $medias = $this->members_model->get_medias_user_by('topic_comment_id', $id);
       if($medias){
           foreach ($medias as $media){
	           	@unlink($media->source);
	           	@unlink($media->photo_thumb);
               $this->members_model->delete_media($media->id);
           }
       }
        
        //delete comment
        $this->db->where('id', $id);
        $this->db->delete('user_comments');
        
        return TRUE;
    }
    
    public function update_status($id, $status){
        $data = array(
            'status' => $status,
        );
        $this->update($id, $data);
        return TRUE;
    }

    public function deleteCommentBy($field, $value){
        if(!$field || !$value){
            return false;
        }
        $this->db->where($field, $value);
        $results = $this->db->get('user_comments');                
        if($results->num_rows() > 0){
            $results = $results->result();
            $this->load->model('business/media_model');
            foreach ($results as $key => $item) {
                $this->media_model->deleteMediaBy('topic_comment_id', $item->id);
            }
            $this->db->delete('user_comments', array($field => $value));
        }
    }

    public function deleteCommentNewsfeedBy($field, $value){
        $this->db->where($field, $value);
        $results = $this->db->get('user_comments');
        if($results->num_rows() > 0){
            $results = $results->result();
            $this->load->model('business/media_model');
            foreach ($results as $key => $item) {
                //delete media comment
                $this->media_model->deleteMediaBy('newfeed_comment_id', $item->id);
            }
            $this->db->delete('user_comments', array($field => $value));
        }
    }
}
