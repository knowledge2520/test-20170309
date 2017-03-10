<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Business_model extends MY_Model {

    /**
     * Hooks
     *
     * @var object
     * */
    protected $_hooks;
    protected $table_name = "business_items";
    protected $key = "id";
    protected $soft_deletes = FALSE;
    protected $date_format = "int";
    protected $log_user = FALSE;
    protected $set_created = true;
    protected $set_modified = false;
    protected $created_field = "created_date";
    protected $modified_field = "modified_date";

    /*
      Customize the operations of the model without recreating the insert, update,
      etc methods by adding the method names to act as callbacks here.
     */
    protected $before_insert = array();
    protected $after_insert = array();
    protected $before_update = array();
    protected $after_update = array();
    protected $before_find = array();
    protected $after_find = array();
    protected $before_delete = array();
    protected $after_delete = array();

    /*
      For performance reasons, you may require your model to NOT return the
      id of the last inserted row as it is a bit of a slow method. This is
      primarily helpful when running big loops over data.
     */
    protected $return_insert_id = TRUE;
    // The default type of element data is returned as.
    protected $return_type = "object";
    // Items that are always removed from data arrays prior to
    // any inserts or updates.
    protected $protected_attributes = array();

    /*
      You may need to move certain rules (like required) into the
      $insert_validation_rules array and out of the standard validation array.
      That way it is only required during inserts, not updates which may only
      be updating a portion of the data.
     */
    protected $validation_rules = array();
    protected $insert_validation_rules = array();
    protected $skip_validation = FALSE;
    public $tables = array(
        'business_items_category' => 'business_items_category',
        'business_category' => 'business_category',
        'business_items' => 'business_items',
    );
    public $join = array(
        'business_category' => 'business_category_id',
        'business_items' => 'business_id',
    );

    /**
     * caching of categories
     *
     * @var array
     * */
    protected $_cache_categories = array();

    /**
     * caching of business and their categories
     *
     * @var array
     * */
    public $_cache_business_in_category = array();

    /**
     * Where
     *
     * @var array
     * */
    public $_where = array();

    /**
     * Limit
     *
     * @var string
     * */
    public $_limit = NULL;

    /**
     * Offset
     *
     * @var string
     * */
    public $_offset = NULL;

    /**
     * Order By
     *
     * @var string
     * */
    public $_order_by = NULL;

    /**
     * Order
     *
     * @var string
     * */
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
     * file_name
     */
    // status 0:deactivate, 1:active, 2:delete, 3:reject
    public function getItems($option = 'total', $status = array(), $keyword = '', $order_field = 'b.id', $sort = 'ASC', $limit = ADMIN_ITEMS_PERPAGE, $offset = false, $my_listing = false, $user_id = false, $country = '') {
        $this->db->select('b.*, u.first_name, u.last_name, u.email, CONCAT_WS(" ", u.first_name, u.last_name) as full_name', FALSE);
        $this->db->from('business_items b');
        $this->db->join('users u', 'u.id = b.user_id', 'left');
        $this->db->join('business_items_category bc', 'bc.business_id = b.id', 'left');
        $this->db->join('business_category c', 'c.id = bc.business_category_id', 'left');
        
        if($my_listing){
            $this->db->where('b.user_id', $user_id);
            $where = ' (b.status = 0 OR b.status = 1)';
            $this->db->where($where);
        }
        else{
            if (is_array($status)) {
                foreach ($status as $key => $item) {
                    $where = [];
                    $where[] = ' b.status = "' . $item . '" ';  
                }
                $query = ' ( ' . implode(' OR ', $where) . ' ) ';
                $this->db->where($query);
            }
        }
        
        if ($keyword != '') {
            $where = ' (b.id = "' . $keyword . '"';
            $where.= ' OR b.name LIKE "%' . $keyword . '%"';
            $where.= ' OR b.address LIKE "%' . $keyword . '%"';
            $where.= ' OR b.phone LIKE "%' . $keyword . '%"';
            $where.= ' OR b.website LIKE "%' . $keyword . '%"';
            $where.= ' OR c.name LIKE "%' . $keyword . '%"';
            $where.= ' OR u.first_name LIKE "%' . $keyword . '%"';
            $where.= ' OR u.last_name LIKE "%' . $keyword . '%")';
            
            $this->db->where($where);
        }

        if( !empty($country) ) {
            $whereCountry = 'b.country_id = "' . $country . '"' ;
            $this->db->where($whereCountry);
        }
        
        $this->db->group_by('b.id');
        if ($option == 'total') {
             $results = $this->db->get();//echo $this->db->last_query();die();
            if($results->num_rows() > 0) {
                $return  = count($results->result());
            }
            else{
                $return  = 0;
            }            
        }elseif($option == 'count_list'){
            $this->db->order_by($order_field, $sort);
            $this->db->limit($limit, $offset);
            $results = $this->db->get();
            $return = $results->num_rows() > 0 ? count($results->result()) : 0;
        } 
        else {
            $this->order_by($order_field, $sort);
            $this->limit($limit, $offset);
            $results = $this->db->get();
            $return = $results->num_rows() > 0 ? $results->result() : false;
        }
        return $return;
    }

    function searchCategories($keyword = '') {
        $this->db->like('c.name', $keyword);
        $this->db->from('business_category c');
        $categories = $this->db->get();
        if ($categories->num_rows() > 0) {
            $data = array();
            foreach ($categories->result() as $c) {
                $this->db->where('business_category_id', $c->id);
                $this->db->from('business_items_category');
                $business_category = $this->db->get();
                if ($business_category->num_rows() > 0) {
                    foreach ($business_category->result() as $k) {
                        array_push($data, $k);
                    }
                }
            }
            return $data;
        }
        return FALSE;
    }

    /**
     * get_business_categories
     *
     * @return array
     * @author Ben Edmunds
     * */
    public function get_business_categories($id = FALSE) {
        $this->tables['business_items_category'] = 'business_items_category';
        $this->tables['business_category'] = 'business_category';
        $this->tables['business_items'] = 'business_items';
        $this->join['business_category'] = 'business_category_id';
        $this->join['business_items'] = 'business_id';

        $this->trigger('get_business_categories', $id);

        return $this->db->select($this->tables['business_items_category'] . '.' . $this->join['business_category'] . ' as id, ' . $this->tables['business_category'] . '.name, ' . $this->tables['business_category'] . '.description')
                        ->where($this->tables['business_items_category'] . '.' . $this->join['business_items'], $id)
                        ->join($this->tables['business_category'], $this->tables['business_items_category'] . '.' . $this->join['business_category'] . '=' . $this->tables['business_category'] . '.id')
                        ->get($this->tables['business_items_category']);
    }

    public function detail($id) {
        if (!$id) {
            return false;
        }
        $this->select('*');
        //$this->join('ew_member_address', 'ew_member_address.memberId = ew_members.id');

        return $this->find_by($this->table_name . '.id', $id);
    }

    public function deleteBusiness($id) {
        $data = array(
            'status' => 2,
        );
        $this->table_name = 'business_items';
        if ($this->update($id, $data)) {
            return TRUE;
        }
        return false;
    }

    public function restoreBusiness($id) {
        $data = array(
            'status' => 1,
        );
        $this->table_name = 'business_items';
        if ($this->update($id, $data)) {
            return TRUE;
        }
        return false;
    }

    public function removeBusiness($id) {
        $this->table_name = 'business_items';
        $this->db->where('id', $id);
        if ($this->db->delete($this->table_name)) {
            return TRUE;
        }
        return false;
    }

    public function rejectBusiness($id) {
        $data = array(
            'status' => 3,
        );
        $this->table_name = 'business_items';
        if ($this->update($id, $data)) {
            $message = lang('business_push_disapprove');
            $action_type = (object) array(
                'id' => 2,
                'name' => 'Reject Listing',
            );
            $this->sentPush($id, 'Reject Listing', $message);
            return TRUE;
        }
        return false;
    }

    public function activeBusiness($id) {
        $data = array(
            'status' => 1,
        );
        $this->table_name = 'business_items';
        if ($this->update($id, $data)) {
            $message = lang('business_push_approve');
            $action_type = (object) array(
                'id' => 1,
                'name' => 'Active Listing',
            );
            $this->sentPush($id, $action_type, $message);
            return TRUE;
        }
        return FALSE;
    }

    protected function sentPush($id, $action_type, $message) {
        $business = $this->detail($id);
        
        $this->load->model('members/members_model');
        $user = $this->members_model->detail(1);
        
        $this->load->model('notification/notification_model');
        $actor_user_id = $business->user_id;
        $name_user_action = $user->display_name;
        $source_id = $business->id;
        // $data_push = array(
        //     'action_type' => $action_type,
        //     'sender_id' => $user->id,
        //     'sender_name' => $name_user_action,
        // );
         $data_push = array(
            'action_type' => 'SYSTEM_PUSH',
            'listing_id' => $id,
            'sender_id' => $user->id,
            'sender_name' => $name_user_action,
            'profile_photo' => $user->profile_photo_thumb,
            'created_date' => date('d-m-Y H:i:s',now()),
            'type' => 'system',
            'bages_unread_notification' => $this->notification_model->count_unread_notification($business->user_id) + 1
        );
        //$this->notification_model->send_push_notification($actor_user_id, $action_type->name, $message, $data_push, $action_type->id, 'individual', false, false);
        $this->notification_model->send_push($actor_user_id, $action_type->name, $message, $data_push, $action_type->id, $name_user_action, false, false, true);
    }

    protected function sendPushNotification($member_id, $message) {
        $device_member = $this->get_member_device($member_id);

        if (!$device_member) {
            return false;
        }

        $this->my_parse->setTableObject($this->db);
        $parse_query = $this->my_parse->ParseQuery('_Installation');
        //$parse_query->equalTo('deviceType', strtolower($device_member->device_type));
        $parse_query->equalTo('deviceToken', $device_member->device_token);

        $data_field = array(
            "channels" => [""],
            "alert" => $message,
            "sound" => "cheer",
        );
        $parse_push = $this->my_parse->ParsePush();

        //send push to parse
        $parse_push->send(array(
            "where" => $parse_query,
            "data" => $data_field,
        ));

        return TRUE;
    }

    protected function get_member_device($member_id) {
        $this->table_name = 'user_device';
        return $this->find_by('user_id', $member_id);
    }

    /**
     * add_to_catefories
     *
     * @return bool
     * @author Ben Edmunds
     * */
    public function add_to_category($category_ids, $business_id = false) {
        $this->trigger('add_to_category');

        if (!is_array($category_ids)) {
            $category_ids = array($category_ids);
        }
        $return = 0;

        // Then insert each into the database
        foreach ($category_ids as $category_id) {
            $this->db->insert($this->tables['business_items_category'], array($this->join['business_category'] => (int) $category_id, $this->join['business_items'] => (int) $business_id));
            $return += 1;
        }

        return $return;
    }

    /**
     * remove_from_category
     *
     * @return bool
     * */
    public function remove_from_category($category_ids = false, $business_id = false) {
        $this->trigger('remove_from_category');

        // business id is required
        if (empty($business_id)) {
            return FALSE;
        }

        // if category id(s) are passed remove business from the category(s)
        if (!empty($category_ids)) {
            if (!is_array($category_ids)) {
                $category_ids = array($category_ids);
            }

            foreach ($category_ids as $category_id) {
                $this->db->delete($this->tables['business_items_category'], array($this->join['business_category'] => (int) $category_id, $this->join['business_items'] => (int) $business_id));
                if (isset($this->_cache_business_in_category[$business_id]) && isset($this->_cache_business_in_category[$business_id][$category_id])) {
                    unset($this->_cache_business_in_category[$business_id][$category_id]);
                }
            }

            $return = TRUE;
        }
        // otherwise remove user from all groups
        else {
            if ($return = $this->db->delete($this->tables['business_items_category'], array($this->join['business_items'] => (int) $business_id))) {
                $this->_cache_business_in_category[$business_id] = array();
            }
        }
        return $return;
    }

    function check_relationship($id = FALSE) {
        $this->db->where('id', $id);
        $this->db->from('business_category');
        $category = $this->db->get();
        if ($category->num_rows() > 0) {
            $category = $category->row();

            $this->db->where('business_category_id', $category->id);
            $this->db->from('business_items_category');
            $result = $this->db->get();

            if ($result->num_rows() > 0) {
                return TRUE;
            }
            return FALSE;
        }
        return FALSE;
    }

    function import_business($business, $categories) {

        $this->table_name = 'business_items';
        $id = $this->insert($business);

        if ($id) {
            $this->insert_business_to_categories($id, $categories);
        }
        return $id;
    }

    function create_address($address1, $address2, $address3, $address4){
        
        $address = $address1 . ' ';
        $address.= ($address2 != '') ? $address2 : '';
        $address.= ($address3 != '') ? ', ' . $address3 : '';
        $address.= ($address4 != '') ? ', ' . $address4  : '';        
        
        return $address;
    }
    
    function check_duplicate($title, $address, $id = 0, $type = 0) {
        $this->table_name = 'business_items';
        
        if($type == 0){
            $business = $this->find_all_by('name', $title);
            $this->load->helper('site');
            $location = get_location_from_address($address);
            if ($business ) {
                foreach ($business as $b) {
                    $business_lat = (float) $b->latitude;
                    $business_long = (float) $b->longitude;
                    if(empty($location)){
                        $location = array(
                            'lat' => 0,
                            'long' => 0,
                        );
                    }
                    if ($business_lat == $location['lat'] && $business_long == $location['long']) {
                        return TRUE;
                    }
                }
            }
        }
        else{
            $this->where('id!=', $id);
            $this->where('name', $title);
            $this->where('address', $address);
            $this->where('status', 1);
            return $this->find_all();
        }
        return FALSE;
    }

    function insert_business_to_categories($business_id, $categories) {
        $this->load->model('categories_model');
        if (!is_array($categories)) {
            $categories = (array) $categories;
        }
        foreach ($categories as $c) {
            $category = $this->categories_model->find_by('name', trim($c));
            if ($category) {
                $this->add_to_category($category->id, $business_id);
            }
        }
    }

    function check_active_business($business_name, $business_address = false){
        $this->table_name = 'business_items';
        $this->where('status', 1);
        if($business_address){
            $this->where('address', $business_address);
        }        
        return $this->find_by('name', $business_name);
    }
    
    function check_owner($table, $business_id = false, $user_id){
        if(!$business_id){
            return FALSE;
        }
        $this->table_name = $table;
        $this->where('id', $business_id);
        $this->where('user_id', $user_id);
               
        return $this->find_all();
    }

    function getCountries($userId=false) {
        if($userId){
             $query = "SELECT countries.id, countries.countryName FROM business_users_country 
             left join countries on countries.id = business_users_country.country_id
             where user_id='$userId' order by countries.countryName";
            $result = $this->db->query($query);
            if($result->num_rows()>0){
                $results = $result->result();
                $arrData = array('' => '');
                foreach($results as $item) {
                    $arrData[$item->id] = ucfirst(strtolower($item->countryName));
                }
                return $arrData;
            }
            return array();
        }
        else{
            $sql = "select country from business_items where country IS NOT NULL AND country <> '0' GROUP BY country";
            $query = $this->db->query($sql);
            if($query->num_rows() > 0) {
                $results = $query->result();
                $arrData = array('' => '');
                foreach($results as $item) {
                    $arrData[$item->country] = ucfirst(strtolower($item->country));
                }
                return $arrData;
            } else {
                return array();
            }
        }
    }

    public function findCountryIdByName($name){
        $query = "SELECT * from countries where countries.countryName LIKE '%$name%'";
        $result = $this->db->query($query);
        if($result->num_rows()>0){
            return $result->row(0)->id;
        }
        return 0;
    }

    function addActivityLog($action,$entity_type,$entity_id,$field_name,$old_value,$new_value){    
        $eventTime = date("Y-m-d H:i:s");
        $actor_id = $this->session->userdata('user_id');
        $actor_name = $this->session->userdata('username');
        $query = ("
            INSERT INTO `activity_log`(`event_time`, `actor_name`, `actor_id`, `action`, `entity_type`, `entity_id`, `field_name`, `old_value`, `new_value`)
                            VALUES ('$eventTime','$actor_name','$actor_id','$action','$entity_type','$entity_id','$field_name','$old_value','$new_value'); 
        ");
        $this->db->query($query);
    }
}
