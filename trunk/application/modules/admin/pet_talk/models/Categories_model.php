<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Categories_model extends MY_Model
{
    /**
     * Hooks
     *
     * @var object
     **/
    protected $_hooks;

    protected $table_name	    = "pet_talk_category";
    protected $key			    = "id";
    protected $soft_deletes	    = FALSE;
    protected $date_format	    = "int";

    protected $log_user 	    = FALSE;

    protected $set_created	    = FALSE;
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

    public $tables                      = array();
    public $join                        = array();

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
    public function getItems($option = 'total' , $status = array() , $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){

        $this->db->select('*');
        $this->db->from($this->table_name);

        $query = [];

        if($keyword !='')
        {
            $where = [];
            $where[] = " id = '".$keyword."' ";
            $where[] = " name LIKE '%".$keyword."%' ";
            $where[] = " description LIKE '%".$keyword."%' ";
            $query[] = " ( " . implode(" OR ", $where ) . " ) ";
        }
        if($status){
            $where = [];
            foreach ($status as $key => $item) {
                $where[] = " status = '".$item."' ";
            }            
            $query[] = " ( " . implode(" OR ", $where ) . " ) ";

        }

        $query[] = " is_special = 0 ";

        $this->db->where(implode(" AND ", $query));
        
        if($option == 'total'){
            $results = $this->db->get();
            $return = $results->num_rows(); 
        }
        else{
            $this->order_by($order_field,$sort);
            $this->limit($limit,$offset);
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


    public function detail($id)
    {
        if(!$id)
        {
            return false;
        }
        $this->select('*');
        //$this->join('ew_member_address', 'ew_member_address.memberId = ew_members.id');
        $this->where('status!=', -1);
        //$this->where('id', $id);
        return  $this->find($id);
    }

    public function deleteCategory($id){
        $data = array(
            'status'    => -1,
        );
        $this->update($id, $data);
        return TRUE;

    }

    public function getAll(){
        $this->db->where('is_special', 0);
        $results = $this->db->get('pet_talk_category');

        return $results->num_rows() > 0 ? $results->result() : array();
    }

    public function update_status($id, $status){
        $data = array(
            'status' => $status,
        );
        $this->update($id, $data);
        return TRUE;
    }

    public function getArray(){
        $this->db->where('is_special', 0);
        $results = $this->db->get('pet_talk_category');

        if($results->num_rows() > 0){
            $results = $results->result();
            $arr = array('newsfeed_post' => 'Newsfeed Post');

            foreach ($results as $key => $item) {
                $arr = array_merge($arr, array($item->id => $item->name));
            }

            return $arr;
        }
        return array();
    }
}
