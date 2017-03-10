<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Badge_type_model extends MY_Model{

    protected $table_name	= "pet_badge_type";
    protected $key			= "id";
    protected $soft_deletes	= FALSE;
    protected $date_format	= "datetime";

    protected $log_user 	= FALSE;

    protected $set_created	= false;
    protected $set_modified = false;
    protected $created_field = "created_date";
    protected $modified_field = "modified_date";

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
    public function getItems($option = 'total' , $status = 0 , $cate_id, $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){

        if($status){
            $this->where('active',$status);
        }
        if($keyword !='')
        {
            $this->like('id',$keyword);
            $this->or_like('code',$keyword);
        }
        if(!empty($cate_id)){
            $this->where('category_id',$cate_id);
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

        return  $this->find_by('id',$id);
    }

    public function delete($id)
    {
        // remove photo
        $item = $this->detail($id);
        if(!$item){
            return fasle;
        }
        $this->delete_media($item->photo);

        return $this->db->delete($this->table_name, array('id' => $id));
    }
    
    public function check_relationship_types($id){
        $result = $this->db->query("
            SELECT *
                    FROM `pet_qrcode`  p
                    WHERE p.`type_id` = '$id'
        ");
        if($result->num_rows()>0){
            return true;
        }
        return false;
    }

    public function delete_media($photo){
        if($photo){
            $this->load->helper('upload');
            S3_Upload::removeByKeyValue($photo);
            return TRUE;
        }
        return FALSE;
    }
    
}