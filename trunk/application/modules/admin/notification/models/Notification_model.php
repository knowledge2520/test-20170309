<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Notification_model extends MY_Model {

    /**
     * Hooks
     *
     * @var object
     * */
    protected $_hooks;
    protected $table_name = "user_notification";
    protected $key = "id";
    protected $soft_deletes = FALSE;
    protected $date_format = "int";
    protected $log_user = FALSE;
    protected $set_created = FALSE;
    protected $set_modified = FALSE;
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
    public $tables = array();
    public $join = array();

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
    public function getItemsUser($option = 'total', $status = 0, $keyword = '', $order_field = 'id', $sort = 'ASC', $limit = ADMIN_ITEMS_PERPAGE, $offset = false) {
        $this->table_name = 'users';

        $this->db->select('u.id, u.first_name, u.last_name, u.email, u.profile_photo, u.profile_photo_thumb, d.device_type, d.device_token, CONCAT_WS(" ", u.first_name, u.last_name) as full_name', false);
        $this->db->from('users u');
        $this->db->join('user_device d', 'd.user_id = u.id', 'left');
        $this->db->where('d.device_token!=', NULL);
        $this->db->where('u.active', 1);
        $this->db->group_by('u.id');

        if ($keyword != '') {
            $where = ' (u.id = "' . $keyword . '" OR u.first_name LIKE "%' . $keyword . '%" OR u.last_name LIKE "%' . $keyword . '%" OR u.email LIKE "%' . $keyword . '%" OR CONCAT_WS(" ", u.first_name, u.last_name) LIKE "%' . $keyword . '%") ';

            $this->db->where($where);
        }

        if ($option == 'total') {
            $results = $this->db->get();
            $return = $results->num_rows();
        } elseif ($option == 'count_list') {
            $this->db->order_by($order_field, $sort);
            $this->db->limit($limit, $offset);
            $results = $this->db->get();
            $return = $results->num_rows();
        } else {
            $this->db->order_by($order_field, $sort);
            $this->db->limit($limit, $offset);
            $return = $this->db->get();

            $return = $return->num_rows() > 0 ? $return->result() : false;
        }
        return $return;
    }

    public function getItems($option = 'total', $status = FALSE, $keyword = '', $order_field = 'id', $sort = 'ASC', $limit = ADMIN_ITEMS_PERPAGE, $offset = false) {
        //"sender_id":"98"
        // $query = "SELECT * FROM user_notification WHERE show_cms = 1 ";
        $query = "SELECT *, substring(data, locate('title', data) + 8, locate('\"', data, locate('title', data) +9 ) - locate('\"', data, locate('title', data) + 7 ) -1 ) as title
            FROM user_notification
            WHERE show_cms = 1 "; 

        if ($keyword != '') {
            $query.= " AND (id = '" . $keyword . "' OR type = '" . $keyword . "' OR substring(data, locate('title', data) + 8, locate('\"', data, locate('title', data) +9 ) - locate('\"', data, locate('title', data) + 7 ) -1 ) LIKE '%" . $keyword . "%')";
        }

        $query.= " GROUP BY CASE WHEN type = 'all' THEN channel ELSE id END ";
        $query.= " ORDER BY " . $order_field . " " . $sort;

        if ($option != 'total') {
            $query.= " LIMIT " . $limit . " OFFSET " . $offset;
        }
//        var_dump($this->db->query($query)->num_rows());exit;
//        if($option == 'total'){
//            $results = $this->find_all();
//            if($results) {
//                $return  = count($results);
//            }
//            else{
//                $return  = 0;
//            }  
//        }
//        elseif($option == 'count_list'){
//            $this->order_by($order_field,$sort);
//            $this->limit($limit,$offset);
//            $return  = count($this->find_all());
//        }
//        else{
//            $this->order_by($order_field,$sort);
//            $this->limit($limit,$offset);
//            $return  = $this->find_all();
//        }

        $results = $this->db->query($query);
        if ($results->num_rows() > 0) {
            if ($option == 'list') {
                return $results->result();
            } else {
                return $results->num_rows();
            }
        }
        return 0;
    }

    public function detail($id) {
        $this->table_name = 'user_notification';
        
       

        if (!$id) {
            return false;
        }
        $this->select('n.*, u.first_name, u.last_name, u.username');
        $this->from('user_notification n');
        $this->join('users u', 'u.id = n.user_id', 'left');
        
        //$this->join('ew_member_address', 'ew_member_address.memberId = ew_members.id');
        
        return $this->find_by('n.id', $id);;
    }
    
    //send_push($title, $message, $data_push, $action_type->id, $user);
    public function send_push($id, $title, $message, $data, $action_type_id, $sender_name){
        $this->load->library('parse');
        $user = $this->get_user_by('id', $id);
        if(!$user){
            return false;
        }
        $this->send_push_notification($user->id, $title, $message, $data, $action_type_id, 'individual', $show_cms = true);
        return true;
    }
    
    public function send_push_all($title, $message, $data, $action_type_id, $sender_name,$sendType='all',$countryPetType){
        $this->load->library('parse');
        if($sendType=='all'){
            $users = $this->get_users_have_device();
        }elseif($sendType=='country'){
            $users = $this->get_users_have_device_with_country($countryPetType);
        }
        else{
            $users = $this->get_users_have_device_with_pettype($countryPetType);
        }
        if(empty($users)){
            return false;
        }
        foreach ($users as $user){
            $this->send_push_notification($user->id, $title, $message, $data, $action_type_id, $sendType, $show_cms = true);
        }
        return true;
    }
    
    public function send_push_notification_all($message, $data = array(), $action_type_id) {
        $this->load->library('parse');

        $this->parse->setDatabase($this->db);

        $data_field = array(
            "alert" => $message,
            "data" => $data,
            "sound" => "cheer",
        );
        $parse_push = $this->parse->ParsePush();

        $parse_push->data = $data_field;

        //send push to parse
        $restult = $parse_push->send();
        if ($restult) {
            return TRUE;
        }
        return FALSE;
    }
    
    public function send_push_notification($member_id, $title, $message, $data = array(), $action_type_id, $type= 'individual', $show_cms = true) {
        $device_members = $this->get_member_device($member_id);
        if (!$device_members || !$action_type_id) {
            return false;
        }

        $this->load->model('members/members_model');    
        $userInfo = $this->members_model->detail($this->session->userdata('user_id'));
        $name_user_action = $userInfo ? $userInfo->display_name : 'N/A';
        $id_user_action = $userInfo ? $userInfo->id : 'N/A';
        $data['sender_id'] = $id_user_action;
        $data['sender_name'] = $name_user_action;

        $bages_unread_notification= $this->count_unread_notification($member_id) + 1;
        if(isset($device_members[0]->device_token_firebase) && !empty($device_members[0]->device_token_firebase)){
            foreach($device_members as $value){
                if(isset($value->device_token_firebase) && !empty($value->device_token_firebase)){
                    $registrationId = $value->device_token_firebase;
                    $registrationIds[] = $registrationId;
                    $subtitle = "";
                    $data_field = array(
                        "bages_unread_notification"=>$bages_unread_notification,
                        "alert" => $name_user_action . ' ' . $message,
                        "data" => $data,
                        "sound" => "cheer",
                        "title"=> $title
                    );
                    $this->pushFireBase($registrationIds,$message,$title,$subtitle,$data);
                }
            }
            //save notification
            $data_insert = array();
            $data_insert['activity_type_id'] = $action_type_id;
            $data_insert['user_id'] = $member_id;
            if(isset($device_members[0]->device_token_firebase) && !empty($device_members[0]->device_token_firebase)){
                 $data_insert['uuid'] = $device_members[0]->device_token_firebase;
            }
            else $data_insert['uuid'] = "";
            $data_insert['data'] = json_encode($data_field);
            $data_insert['push_type'] = isset($data_field['type']) ? $data_field['type'] : "gcm";
            $data_insert['show_cms'] = $show_cms ? 1 : 0;
            $data_insert['type'] = $type;
            $this->create($data_insert);
            return true; 
        } 
    }

    function get_action_type($type_name) {

        if (!empty($type_name)) {
            $row = $this->db->get_where('user_activity_types', array('name' => $type_name))->first_row();
            if ($row) {
                return $row;
            }
        }
        return false;
    }

    public function get_devices() {
        $this->db->group_by('user_id');
        $results = $this->db->get('user_device');

        if ($results->num_rows() > 0) {
            return $results->result();
        }
        return FALSE;
    }

    public function get_users() {
        $this->db->select('u.*, d.device_type, d.device_token');
        $this->db->from('users u');
        $this->db->join('user_device d', 'd.user_id = u.id', 'left');
        $this->db->where('d.device_token!=', NULL);
        $this->db->where('u.active', 1);
        $this->db->group_by('u.id');

        $results = $this->db->get();
        if ($results->num_rows() > 0) {
            return $results->result();
        }
        return FALSE;
    }

    public function get_member_device($memberId, $optional = array()) {
        $query = $this->db->query("SELECT * FROM user_device WHERE user_id = ?", array($memberId));
        if ($query->num_rows() > 0) {
            return $query->result();
        }

        return false;
    }

    public function create($data_create) {
        $data_create['created_date'] = now();
        $this->db->insert('user_notification', $data_create);
        $id = $this->db->insert_id();
        return $id;
    }

    public function update($id, $data_update) {
        if ($id > 0) {
            $this->db->where('id', $id);
            return $this->db->update('user_notification', $data_update);
        }

        return false;
    }

    public function delete($id) {
        if ($id > 0) {
            $this->db->where('id', $id);
            return $this->db->delete('user_notification');
        }

        return false;
    }

    function search_title_user($keyword = '') {
        $this->db->select('*');
//        $this->db->where('show_cms', 1);

        $results = $this->db->get('user_notification');
        if ($results->num_rows() > 0) {
            $results = $results->result();
            $i = 0;
            $array = array();
            foreach ($results as $result) {
                $data = json_decode($result->data);
                $title = isset($data->title) ? $data->title : false;
                $sender_name = isset($data->data->sender_name) ? $data->data->sender_name : false;
                if ($title || $sender_name) {
                    if (stristr($title, $keyword) || stristr($sender_name, $keyword)) {
                        array_push(
                                $array, array(
                                    'id' => $result->id,
                                )
                        );
                    }
                }
            }
            return $array;
        }
        return false;
    }

    function delete_notification($id){
        $notification = $this->detail($id);
        if(!$notification){
            return FALSE;
        }
        
        if($notification->type == 'all'){
            $this->db->where('data', $notification->data);
            $this->db->delete('user_notification');
        }
        else{
            $this->db->where('id', $id);
            $this->db->delete('user_notification');
        }
        return TRUE;
    }
    
    function count_unread_notification($user_id){
        if (!empty($user_id)) {
        $this->db->where(array('user_id' => $user_id, 'is_read' => 0));
        $this->db->from('user_notification');
        return $this->db->count_all_results();
    }
    return false;
    }
    
    function get_users_have_device(){
        $this->db->select('l.*, u.email, o.notifications_announcements');
        $this->db->from('user_logged l');
        $this->db->join('users u', 'u.id = l.user_id', 'left');
        $this->db->join('user_device k', 'k.user_id = u.id', 'left');
        $this->db->join('user_options o', 'o.user_id = u.id', 'left');
        $this->db->where('l.device_token!=', '0');
        $this->db->where('o.notifications_announcements', 'on');
        $this->db->group_by('l.user_id');
        $results = $this->db->get();
        
        return $results->num_rows() > 0 ? $results->result() : false; 
    }

    function get_users_have_device_with_country($countrise){
        $str = "('";
        foreach($countrise as $value){
            $str .= $value . "','";
        }
        $str = rtrim($str,",'");
        $str .= "')";
        $query = "SELECT u.* from pets l
        left join users u on u.id = l.user_id
        left join user_device d on d.user_id = u.id
        left join user_options b on b.user_id = u.id
        where u.last_country_id IN $str and b.notifications_announcements='on'  and d.device_token_firebase is not null group by u.id
        ";
        //echo $query;exit;
        $results = $this->db->query($query);
        return $results->num_rows() > 0 ? $results->result() : false; 
    }

    function get_users_have_device_with_pettype($pettypes){
        $str = "('";
        foreach($pettypes as $value){
            $str .= $value . "','";
        }
        $str = rtrim($str,",'");
        $str .= "')";
        $query = "SELECT u.* from pets l
        left join users u on u.id = l.user_id
        left join user_device d on d.user_id = u.id
        left join pet_types k on k.id = l.type
        left join user_options b on b.user_id = u.id
        where l.type IN $str and b.notifications_announcements='on' and l.status=1 and  d.device_token_firebase is not null group by u.id
        ";
        $results = $this->db->query($query);
        return $results->num_rows() > 0 ? $results->result() : false; 
    }
    
    function get_user_by($field, $value){
        $this->select('u.*, d.device_token, o.notifications_announcements');
        $this->db->from('users u');
        $this->db->join('user_device d', 'd.user_id = u.id', 'left');
        $this->db->join('user_options o', 'o.user_id = u.id', 'left');
        $this->db->where('u.'.$field, $value);
        //$this->db->where('o.notifications_announcements', 'on');
        $result = $this->db->get();
        return $result->num_rows() > 0 ? $result->row() : false;
    }

    public function deleteByKeyWord($params = false) {
        if( $params ) {
            $this->db->query("DELETE FROM user_notification WHERE $params");
        }
    }

    public function getPetType(){
        $this->select('u.*');
        $this->db->from('pet_types u');
        $result = $this->db->get();
        return $result->num_rows() > 0 ? $result->result() : false;
    }

    public function pushFireBase($registrationIds,$message,$title,$subtitle,$data = array()){
        // prep the bundle
        $data['message'] = $message;
        $data['title'] = $title;
        $data['subtitle'] = $subtitle;
        $data['tickerText'] = "";
        $data['vibrate'] = 1;
        $data['sound'] = 1;
        $data['largeIcon'] = 'large_icon';
        $data['smallIcon'] = 'small_icon';

        $notification  = new stdClass;
        $notification->body = $message;
        $notification->sound = "default";
        $fields = array
        (
            'registration_ids'  => $registrationIds,
            'data'          =>  $data,
            'notification'=> $notification,
            'priority'=>'high'
        );

        $headers = array
        (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        $result = json_decode($result,true);
        //echo json_encode($result);exit;
        if($result['success'] == "1"){
            if(isset($result['results'][0]['message_id'])){
                return $result['results'][0]['message_id'];
            }
        }
        else{
            return 0;
        }
    }

    public function updateNotificationSenderName($new_name, $member){
        $params = "data REGEXP '\"sender_id\":(.?)\"$member->id\"'";
        $this->db->query("UPDATE `user_notification` SET `data` = REPLACE(`data`, '\"alert\":\"$member->display_name', '\"alert\":\"$new_name') WHERE $params");
        $this->db->query("UPDATE `user_notification` SET `data` = REPLACE(`data`, '\"sender_name\":\"$member->display_name\"', '\"sender_name\":\"$new_name\"') WHERE $params");
    }
}
