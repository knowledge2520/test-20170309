<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Categories_model extends MY_Model
{
    protected $table_name	    = "business_category";
    protected $key			    = "id";
    protected $soft_deletes	    = FALSE;
    protected $date_format	    = "int";

    protected $log_user 	    = FALSE;

    protected $set_created	    = false;
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
    protected $before_find 	= array();
    protected $after_find 	= array();
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
		
    	$this->db->select('*, 
            (SELECT count(*) 
                    FROM `business_items`  b
                    LEFT JOIN `business_items_category` bc ON bc.`business_id` = b.`id`
                    WHERE bc.`business_category_id` = c.`id` AND b.`status` = 1
            ) as total, order as position');
    	$this->db->from($this->table_name . ' c');
        if($keyword !='')
        {
        	$query = "c.id = '".$keyword."' OR c.name LIKE '%".$keyword."%' OR c.description LIKE '%".$keyword."%'";
            $this->db->where($query);
          
        }
        //var_dump($status);exit;
        if(is_array($status) && sizeof($status)> 0){
        	$newarray = implode(", ", $status);
        	$query = "status IN (".$newarray.")";
            $this->db->where($query);
        }
        if($option == 'total'){
        	$results = $this->db->get(); 
            $return  = $results->num_rows();
        }
        elseif($option == 'count_list'){
        	$this->db->order_by($order_field,$sort);
            $this->db->limit($limit,$offset);
            $results = $this->db->get();
            $return  = $results->num_rows();
        }
        else{

            $this->db->order_by($order_field,$sort);
            $this->db->limit($limit,$offset);
            $results = $this->db->get();
            $return  = $results->num_rows() > 0 ? $results->result() : false;
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

    public function deleteCategory($id = FALSE){
        $data =  array(
            'status'    => -1,
        );
        if($this->update($id, $data)){
            return TRUE;
        }

        return FALSE;
    }
    
    public function check_categories($data){
        $this->table_name = 'business_category';
        $categories = explode(',', $data);
        foreach($categories as $c){
             if($this->find_by('name', trim($c))){
                 return TRUE;
             }
        }
        return FALSE;
    }

    public function getCategories($where){
        $this->db->where($where);
        $results = $this->db->get('business_category');

        return $results->num_rows() > 0 ? $results->result() : array();
    }

    public function getCountryCategory($id){

        $query = "SELECT COALESCE(c.countryName, 'N/A') as country_name, count(COALESCE(c.countryName, 'N/A')) as total
                    FROM business_items b 
                    LEFT JOIN business_items_category bc ON bc.business_id = b.id
                    LEFT JOIN business_category cat ON cat.id = bc.business_category_id
                    LEFT JOIN countries c ON c.id = b.country_id
                    WHERE cat.id = $id  AND b.status = 1
                    GROUP BY c.id
                    ORDER BY count(c.id) DESC";

        $results = $this->db->query($query);

        return $results->num_rows() > 0 ? $results->result() : array();
    }
}