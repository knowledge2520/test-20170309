<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pet_types_model extends MY_Model{

    protected $table_name	    = "pet_types";
    protected $key			    = "id";
    protected $soft_deletes	    = false;
    protected $date_format	    = "datetime";

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
    protected $validation_rules         = array();
    protected $insert_validation_rules  = array();
    protected $skip_validation          = false;
    protected $empty_validation_rules   = array();

    /**
     *
     * @param string $option
     * @param number $status
     * @param string $keyword
     * @param string $order_field
     * @param string $sort
     * @param string $limit
     * @param string $offset
     * @return Ambigous <number, mixed, boolean, string>
     *file_name
     */
    public function getItems($option = 'total' , $status = array() , $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){
        $this->db->select('*, 
            (SELECT count(*) 
                    FROM `pets`  p
                    WHERE p.`type` = t.`id` AND p.`status` = 1
            ) as total');
        $this->db->from($this->table_name . ' t');
        if($keyword !='')
        {
            $query = "t.id = '".$keyword."' OR t.name LIKE '%".$keyword."%' OR t.description LIKE '%".$keyword."%'";
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
        else{
            $this->db->order_by($order_field,$sort);
            $this->db->limit($limit,$offset);
            $results = $this->db->get();

            if($option == 'count_list'){
                $return  = $results->num_rows();
            }
            else{
                $return  = $results->num_rows() > 0 ? $results->result() : false;
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

        return  $this->find_by($this->table_name .'.id',$id);
    }

    public function delete($id)
    {
        return $this->db->delete($this->table_name, array('id' => $id));
    }

}