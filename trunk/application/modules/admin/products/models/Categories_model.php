<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Categories_model extends MY_Model
{
    /**
     * Hooks
     *
     * @var object
     **/
    protected $_hooks;

    protected $table_name	    = "pet_shop_category";
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
    public function getItems($option = 'total' , $status = false , $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){

        if($keyword !='')
        {
            $this->like('id',$keyword);
            $this->or_like('name',$keyword);
            $this->or_like('description',$keyword);
        }
        if($status === 1 || $status === 0){
            $this->where('status', $status);
        }
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
    /**
     * get_product_category
     *
     * @return array
     * @author Ben Edmunds
     **/
    public function get_product_category($category_id=FALSE)
    {
        $this->trigger('get_product_category', $category_id);

        return $this->db->select('*')
            ->where('id', $category_id)
            ->get('pet_shop_category');
    }
    /**
     * add_to_catefories
     *
     * @return bool
     * @author Ben Edmunds
     **/
    public function add_to_category($category_ids, $business_id=false)
    {
        $this->trigger('add_to_category');

        if(!is_array($category_ids))
        {
            $category_ids = array($category_ids);
        }
        $return = 0;

        // Then insert each into the database
        foreach ($category_ids as $category_id)
        {
            $this->db->insert($this->tables['business_items_category'], array( $this->join['business_category'] => (int)$category_id, $this->join['business_items'] => (int)$business_id));
            $return += 1;
        }

        return $return;
    }
    /**
     * remove_from_category
     *
     * @return bool
     **/
    public function remove_from_category($category_ids=false, $business_id=false)
    {
        $this->trigger('remove_from_category');

        // business id is required
        if(empty($business_id))
        {
            return FALSE;
        }

        // if category id(s) are passed remove business from the category(s)
        if( ! empty($category_ids))
        {
            if(!is_array($category_ids))
            {
                $category_ids = array($category_ids);
            }

            foreach($category_ids as $category_id)
            {
                $this->db->delete($this->tables['business_items_category'], array($this->join['business_category'] => (int)$category_id, $this->join['business_items'] => (int)$business_id));
                if (isset($this->_cache_business_in_category[$business_id]) && isset($this->_cache_business_in_category[$business_id][$category_id]))
                {
                    unset($this->_cache_business_in_category[$business_id][$category_id]);
                }
            }

            $return = TRUE;
        }
        // otherwise remove user from all groups
        else
        {
            if ($return = $this->db->delete($this->tables['business_items_category'], array($this->join['business_items'] => (int)$business_id))) {
                $this->_cache_business_in_category[$business_id] = array();
            }
        }
        return $return;
    }


    public function deleteCategory($id){
        $data = array(
            'status'    => 0,
        );
        if($this->update($id, $data)){
            return TRUE;
        }

        return FALSE;
    }
}
