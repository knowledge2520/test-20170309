<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @todo:
 */
class Bookmarks_model extends MY_Model
{
    protected $table_name	= "user_bookmarks";
    protected $key			= "id";
    protected $soft_deletes	= FALSE;
    protected $date_format	= "int";

    protected $log_user 	= FALSE;

    protected $set_created	= FALSE;
    protected $set_modified = FALSE;
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
    public function getItems($option = 'total' , $status = 0 , $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false, $member_id = false){
        if($member_id){
            $this->where('user_id', $member_id);
        }
        if($status){
            $this->where('active',$status);
        }
        if($keyword != '')
        {
            $business_keyword = $this->searchBusiness($keyword);
            //var_dump($business_keyword);exit;
            if($business_keyword){
                foreach($business_keyword as $k){
                    $this->or_where('business_id', $k->id);
                }
            }

            $this->or_like('id',$keyword);
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


    function searchBusiness($keyword){
        $this->table_name = 'business_items';
        $this->like('name', $keyword);
        return $this->find_all();
//        $this->db->like('name', $keyword);
//
//        $results = $this->db->get('business_items');
//
//        if($results->num_rows() > 0){
//            return $results->result();
//        }
//        return FALSE;
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
