<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pets_model extends MY_Model{

    protected $table_name	= "pets";
    protected $key			= "id";
    protected $soft_deletes	= false;
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
    public function getItems($option = 'total' , $status = 0 , $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false, $member_id = false){
        $this->where('user_id',$member_id);
        if($status){
            $this->where('active',$status);
        }
        if($keyword !='')
        {
            //get category
            $new_keywords = $this->searchUser($keyword);
            if($new_keywords){
                foreach($new_keywords as $k){
                    $this->or_where('user_id', $k->id);
                }
            }


            $this->like('id',$keyword);
            $this->or_like('name',$keyword);
            $this->or_like('origin',$keyword);
            $this->or_like('microchip',$keyword);
            $this->or_like('breed',$keyword);
            $this->or_like('color',$keyword);
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

    public function getTableData1($table, $pet_id){
        $this->db->where('pet_id', $pet_id);
        $result = $this->db->get($table);
        if($result->num_rows() > 0){
            return $result->result();
        }
        return FALSE;
    }

    function searchUser($keyword = ''){
        $this->db->like('username', $keyword);
        $this->db->or_like('first_name', $keyword);
        $this->db->or_like('last_name', $keyword);

        $users = $this->db->get('users');
        if($users->num_rows() > 0){
            return $users->result();
        }
        return FALSE;
    }

    public function detail($id)
    {
        if(!$id)
        {
            return false;
        }
        $this->select('*');
        //$this->join('ew_member_address', 'ew_member_address.memberId = ew_members.id');

        return  $this->find_by('pets.id',$id);
    }

//    public function delete($id)
//    {
//        return $this->db->delete($this->table_name, array('id' => $id));
//    }

    public function getDashboard(){

        $date = getdate();

        $this->db->where($this->created_field .' >=', $this->dayAdd('day', 0, $date));
        $this->db->from($this->table_name);
        $data['today']                 = $this->db->count_all_results();

        $this->db->where($this->created_field .' <', $this->dayAdd('day', 0, $date));
        $this->db->where($this->created_field .' >=', $this->dayAdd('day', -1, $date));
        $this->db->from($this->table_name);
        $data['yesterday']             = $this->db->count_all_results();

        $this->db->where($this->created_field .' <', $this->dayAdd('month', +1, $date));
        $this->db->where($this->created_field .' >=', $this->dayAdd('month', 0, $date));
        $this->db->from($this->table_name);
        $data['this_month']             = $this->db->count_all_results();

        $this->db->where($this->created_field .' <', $this->dayAdd('month', 0, $date));
        $this->db->where($this->created_field .' >=', $this->dayAdd('month', -1, $date));
        $this->db->from($this->table_name);
        $data['last_month']             = $this->db->count_all_results();

        $this->db->where($this->created_field .' <', $this->dayAdd('month', -1, $date));
        $this->db->where($this->created_field .' >=', $this->dayAdd('month', -2, $date));
        $this->db->from($this->table_name);
        $data['month_before_last']      = $this->db->count_all_results();

        $this->db->where($this->created_field .' >=', strtotime($date['year'] . '-1'));
        $this->db->from($this->table_name);
        $data['this_year']              = $this->db->count_all_results();

        $this->db->from($this->table_name);
        $data['total']                  = $this->db->count_all_results();
        return $data;
    }

    function dayAdd($key = false, $value = false, $date = array()){
        if($key == 'month'){
            return strtotime($value.$key, strtotime($date['year'].'-'.$date['mon']));
        }
        elseif($key == 'day'){
            return strtotime($value.$key, strtotime($date['year'].'-'.$date['mon'].'-'.$date['mday']));
        }
        return false;

    }

    public function check_relationship($id){
        return $this->find_by('type', $id);
    }


    public function getTableData($table, $pet_id, $option = 'total' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){
        $this->table_name = $table;
        $this->where('pet_id', $pet_id);

        if($option == 'total'){
            $return  = count($this->find_all());
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
    public function deleteInfo($table, $id){
        $this->table_name = $table;
        if($this->delete($id)){
            return TRUE;
        }
        return FALSE;
    }
}