<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reviews_model extends MY_Model
{
    /**
     * Hooks
     *
     * @var object
     **/
    protected $_hooks;

    protected $table_name	    = "user_reviews";
    protected $key			    = "id";
    protected $soft_deletes	    = FALSE;
    protected $date_format	    = "int";

    protected $log_user 	    = FALSE;

    protected $set_created	    = true;
    protected $set_modified     = false;
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
    public function getItems($option = 'total' , $status = false , $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false, $business_id = false, $my_review = false, $user_id = 0){
        
        $this->db->select('r.*, u.first_name, u.last_name, u.email, b.name as business_name, c.name as category_name, CONCAT_WS(" ", u.first_name, u.last_name) as full_name', false);
        $this->db->from('user_reviews r');
        $this->db->join('users u', 'u.id = r.user_id', 'left');
        $this->db->join('business_items b', 'b.id = r.business_id', 'left');
        $this->db->join('business_items_category bc', 'bc.business_id = b.id', 'left');
        $this->db->join('business_category c', 'c.id = bc.business_category_id', 'left');
        
        $query = [];

        if($my_review){
            $query[] = 'r.user_id = "' . $user_id .'"';
        }
        else{
            if ($status) {
                $where = [];
                foreach ($status as $s) {
                    $where[]= ' r.status = "' . $s . '"';
                }

                $query[] = ' ( ' . implode(' OR ', $where) . ' ) ';
            }
        }        
        
        if ($keyword != '') {
            $where = [];
            $where[] = ' r.id = "' . $keyword . '"';
            $where[] = ' r.content LIKE "%' . $keyword . '%"';
            $where[] = ' r.rate LIKE "%' . $keyword . '%"';
            $where[] = ' c.name LIKE "%' . $keyword . '%"';
            $where[] = ' b.name LIKE "%' . $keyword . '%"';
            $where[] = ' u.email LIKE "%' . $keyword . '%"';
            $where[] = ' u.first_name LIKE "%' . $keyword . '%"';
            $where[] = ' u.last_name LIKE "%' . $keyword . '%"';
            $where[] = ' CONCAT_WS(" ", u.first_name, u.last_name) LIKE "%' . $keyword . '%"';
            
            $query[] = ' ( ' . implode(' OR ', $where) . ' ) ';
        }
        if ($business_id) {
            $query[] = 'r.business_id = "' . $business_id . '"';
        }
        
        if($query){
            $this->db->where(implode(' AND ', $query));
        }

        $this->db->group_by('r.id');
        
        if($option == 'total'){
            $results = $this->db->get();
            $return  = $results->num_rows();
        }
        else{
            $this->db->order_by($order_field,$sort);
            $this->db->limit($limit,$offset);
            $results = $this->db->get();

            if($option == 'count_list'){
                $return  = $results->num_rows();
            }
            else{
                $return  = $results->num_rows() > 0 ? $results->result() : array();
            }            
        }

        return $return;
    }

    public function detail($id)
    {
        if(!$id)
        {
            return false;
        }
        $this->db->select('r.*, CONCAT_WS(" ", u.first_name, u.last_name) as user_name, b.name as business_name', false);
        $this->db->from($this->table_name .' r');
        $this->db->join('business_items b', 'b.id = r.business_id', 'left');
        $this->db->join('users u', 'u.id = r.user_id', 'left');
        $this->db->where('r.id', $id);
        $result = $this->db->get();

        return  $result->num_rows() > 0 ? $result->row() : false;
    }

    public function deleteReview($id){
        //delete like review
        $this->load->model('members/likes_model');
        $this->likes_model->deleteUserLikeBy('review_id', $id);

        //delete comment review
        $this->load->model('comments_model');
        $this->comments_model->deleteCommentBy('review_id', $id);

        //delete media review
        $this->load->model('media_model');
        $this->media_model->deleteMediaBy('review_id', $id);

        //delete review
        $this->db->delete("user_reviews", array("id" => $id));
    }


    public function update_status($id, $status){
        $data = array(
            'status' => $status,
        );
        $this->update($id, $data);
        return TRUE;
    }

    /**
     * [excuteItem description]
     * @param  [type] $field  [description]
     * @param  [type] $value  [description]
     * @param  string $action delete, restore, remove from trash
     * @return [type]         [description]
     */
    public function excuteItem($field, $value, $action = 'delete', $force_delete = false){
        if(!$field || !$value){
            return false;
        }

        $this->db->where($field, $value);
        $results = $this->db->get($this->table_name);

        if($results->num_rows() > 0){
            $results = $results->result();

            $this->load->model('comments_model');
            $this->load->model('newsfeed/newsfeed_model');
            $this->load->model('members/likes_model');
            $this->load->model('business_modelne/media_model');
            
            foreach ($results as $key => $item) {
                $this->comments_model->excuteItem('review_id', $item->id, $action);
                $this->likes_model->excuteItem('review_id', $item->id, $action);
                $this->newsfeed_model->excuteItem('review_id', $item->id, $action);
                $this->media_model->excuteItem('review_id', $item->id, $action);
            }         

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
