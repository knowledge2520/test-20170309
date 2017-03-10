<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @todo:
 */
class Login_model extends MY_Model
{
    protected $table_name	= "users";
    protected $key			= "id";
    protected $soft_deletes	= true;
    protected $date_format	= "int";

    protected $log_user 	= FALSE;

    protected $set_created	= true;
    protected $set_modified = false;
    protected $created_field = "created_on";
    protected $modified_field = "updated_on";

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
    public function getItems($option = 'total' , $status = 0 , $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){

        if($status){
            $this->where('active',$status);
        }
        if($keyword !='')
        {
            $this->like('id',$keyword);
            $this->or_like('email',$keyword);
            $this->or_like('first_name',$keyword);
            $this->or_like('last_name',$keyword);
        }

        if($option == 'total'){
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
     * add_to_group
     *
     * @return bool
     * @author Ben Edmunds
     **/
    public function add_to_group($group_id, $member_id=false)
    {
        $this->db->insert('users_groups', array('group_id' => $group_id, 'user_id' => $member_id));
    }

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

    public function getOverall(){
        $data['male']                   = $this->count_by('gender', 0);

        $data['female']                 = $this->count_by('gender', 1);

        $this->db->where('device_type', 'ios');
        $this->db->from('user_device');
        $data['ios']                    = $this->db->count_all_results();

        $this->db->where('device_type', 'android');
        $this->db->from('user_device');
        $data['android']                    = $this->db->count_all_results();

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
}
