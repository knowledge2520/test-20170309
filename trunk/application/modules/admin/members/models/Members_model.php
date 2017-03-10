<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @todo:
 */
class Members_model extends MY_Model
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
    public function getItems($option = 'total' , $status = array() , $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){
        
        $this->db->select('u.*, c.countryName as location, CONCAT_WS(" ", u.first_name, u.last_name) as user_name, DATE_FORMAT(NOW(), "%Y-%m-%d") - DATE_FORMAT(from_unixtime(u.dob), "%Y-%m-%d") as age', false);
        $this->db->from('users u');
        $this->db->join('countries c', 'c.id = u.last_country_id', 'left');

        $query = [];
        $query[] = ' u.active != -1 ';
        if(is_array($status)  && $status){
            $where = [];
            foreach ($status as $key => $item) {
               $where[] = ' u.active = "'.$item.'" ';
            }
            $query[] = ' ( ' . implode(' OR ', $where) . ' ) ';
        }        

        if($keyword !='')
        {
            $where = [];
            $where[] = " u.id = '".$keyword."' ";
            $where[] = " u.email LIKE '%".$keyword."%' ";
            $where[] = " CONCAT_WS(' ', u.first_name, u.last_name) LIKE '%".$keyword."%' ";
            $where[] = " u.phone LIKE '%".$keyword."%' ";
            $where[] = " u.email LIKE '%".$keyword."%' ";
            $where[] = " c.countryName LIKE '%".$keyword."%' ";

            $query[] = ' ( ' . implode(' OR ', $where) . ' ) ';
        }

        if($query){
            $this->db->where(implode(' AND ', $query));
        }

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
        $this->where('active>=',0);
        $this->where('id',$id);
        $result = $this->find_all($this->table_name);
        return  !empty($result) ? $result[0] : false;
        //return  $this->find_by($this->table_name .'.id',$id);
    }

    public function deleteUser($id){
        if(!$id){
            return false;
        }
        $user = $this->detail($id);
        if($user){
            $this->db->where('id', $id);
            $this->db->update($this->table_name, array('active' => -1));
            return true;
        }
        return false;
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

        $this->db->where($this->created_field .' >=', CMSHelper::dayAdd('day', 0, $date));
        $this->db->from($this->table_name);
        $data['today']                 = $this->db->count_all_results();

        $this->db->where($this->created_field .' <', CMSHelper::dayAdd('day', 0, $date));
        $this->db->where($this->created_field .' >=', CMSHelper::dayAdd('day', -1, $date));
        $this->db->from($this->table_name);
        $data['yesterday']             = $this->db->count_all_results();

        $this->db->where($this->created_field .' <', CMSHelper::dayAdd('month', +1, $date));
        $this->db->where($this->created_field .' >=', CMSHelper::dayAdd('month', 0, $date));
        $this->db->from($this->table_name);
        $data['this_month']             = $this->db->count_all_results();

        $this->db->where($this->created_field .' <', CMSHelper::dayAdd('month', 0, $date));
        $this->db->where($this->created_field .' >=', CMSHelper::dayAdd('month', -1, $date));
        $this->db->from($this->table_name);
        $data['last_month']             = $this->db->count_all_results();

        $this->db->where($this->created_field .' <', CMSHelper::dayAdd('month', -1, $date));
        $this->db->where($this->created_field .' >=', CMSHelper::dayAdd('month', -2, $date));
        $this->db->from($this->table_name);
        $data['month_before_last']      = $this->db->count_all_results();

        $this->db->where($this->created_field .' >=', strtotime($date['year'] . '-1'));
        $this->db->from($this->table_name);
        $data['this_year']              = $this->db->count_all_results();

        $this->db->where($this->created_field .' <', CMSHelper::dayAdd('year', 0, $date));
        $this->db->where($this->created_field .' >=', CMSHelper::dayAdd('year', -1, $date));
        $this->db->from($this->table_name);
        $data['last_year']      = $this->db->count_all_results();
        $data['change_from_last_year'] = 0;
        if($data['last_year'] == 0){
            if($data['this_year'] == 0){
                $data['change_from_last_year'] = 0;
            }else{
                $data['change_from_last_year'] = 100;
            }
        }else{
            $data['change_from_last_year'] = round( ( ( $data['this_year']*100 ) / $data['last_year'] ) - 100, 2);
        }

        $this->db->from($this->table_name);
        $data['total']                  = $this->db->count_all_results();
        return $data;
    }

    public function getOverall(){
        $data['total']                   = $this->count_all();

        $data['male']                   = $this->count_by('gender', 0);

        $data['female']                 = $this->count_by('gender', 1);
        
        $data['active_user']            = $this->count_by('active', 1);
        $this->db->where('device_type', 'ios');
        $this->db->from('user_device');
        $data['ios']                    = $this->db->count_all_results();

        $this->db->where('device_type', 'android');
        $this->db->from('user_device');
        $data['android']                = $this->db->count_all_results();

        // get top country users
        $query = "SELECT u.last_country_id as country_id, COALESCE(c.countryName, 'Unknown Country') as country_name, count(COALESCE(c.countryName, 'Unknown Country')) as total
                    from users u
                    left join countries c on c.id = u.last_country_id
                    GROUP BY u.last_country_id
                    order by count(COALESCE(c.countryName, 'Unknown Country')) DESC, c.countryName ASC
                    limit 3";
        $results = $this->db->query($query);
        $data['top_countries'] = $results->num_rows() > 0 ? $results->result() : array();

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

    public function deletePhoto($id){
        $member = $this->detail($id);
        unlink($_SERVER['DOCUMENT_ROOT'].'/'.$member->profile_photo);
        unlink($_SERVER['DOCUMENT_ROOT'].'/'.$member->profile_photo_thumb);
        $data = array(
            'profile_photo' => '',
            'profile_photo_thumb' => '',
        );
        $this->update($id,$data);
        return TRUE;
    }
    
    /**
     * @todo create the code number to verify user's phone number
     *
     */
    public function _generateCode($length = 4)
    {
    	$pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    	$key = '';
    	$count = strlen($pool);
    	while ($length--) {
    		$key .= $pool[mt_rand(0, $count - 1)];
    	}
    	return $key;
    }
    
    public function get_medias_user_by($field, $value)
    {
    	return $this->db->get_where('user_media',array($field=>$value))->result();
    }
    
    public function delete_media($media_id)
    {
    	//delete media like
    	$likes = $this->get_media_likes($media_id);
    	if($likes){
    		foreach ($likes as $like){
    			$this->delete_like($like->id);
    		}
    	}
    
    	//delete media
    	$this->db->where('id',$media_id);
    	$this->db->delete('user_media');
    }
    public function get_media_likes($media_id)
    {
    	return $this->db->get_where('user_likes',array('media_id'=>$media_id))->result();
    }    
    public function delete_like($id)
    {
    	$this->db->delete('user_likes',array('id'=>$id));
    }
}
