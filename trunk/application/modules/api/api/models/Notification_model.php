<?php

class Notification_model extends CI_Model {

    public function __construct() {
        log_message('info', '===============START PUSH NOTIFICATION===================');
        $this->load->library('parse');
    }

    public function send_push_notification($member_id, $message, $data = array(), $action_type_id, $source_id) {
        $device_members = $this->get_member_device($member_id);
        $member = $this->get_member_by_id($member_id);

        if (!$device_members || !$action_type_id) {
            return false;
        }
        $this->parse->setDatabase($this->db);

        $data_field = array(
            "alert" => $message,
            "data" => $data,
            "sound" => "cheer",
        );
        $parse_push = $this->parse->ParsePush();

        $parse_queries = $this->parse->ParseQuery('_User');
        $parse_queries->where("email", $member->email);
        $results = $parse_queries->find()->results;
        
        if(!empty($results)){
            
            $parse_push->data = $data_field;
            $parse_push->where =  (array('MemberObjectId' => $results[0]->objectId));
            //send push to parse
            $result = $parse_push->send();
            if ($result) {
                log_message('info', "SEND PUSH SUCCESS TO MEMBER ID#" . $member_id);
                log_message('info', '===============END PUSH NOTIFICATION================');
            } else {
                log_message('error', "SEND PUSH FAIL TO MEMBER ID#" . $member_id);
                log_message('error', '===============END PUSH NOTIFICATION================');
            }
            
        }
//        foreach ($device_members as $device_member) {
//            $installation = $this->parse->ParseQuery('_Installation');
//            //$query = $installation->query();
//            $installation->where('MemberObjectId', $results[0]->objectId);
//            var_dump($installation->find());exit;
//            
//            
//            $parse_push->data = $data_field;
//            $parse_push->where = (array('deviceToken' => $device_member->device_token));
//            //send push to parse
//            $result = $parse_push->send();
//            if ($result) {
//                log_message('info', "SEND PUSH SUCCESS TO MEMBER ID#" . $member_id);
//                log_message('info', '===============END PUSH NOTIFICATION================');
//            } else {
//                log_message('error', "SEND PUSH FAIL TO MEMBER ID#" . $member_id);
//                log_message('error', '===============END PUSH NOTIFICATION================');
//            }
//        }
        //save notification
        $data_insert = array();
        $data_insert['activity_type_id'] = $action_type_id;
        $data_insert['source_id'] = $source_id;
        $data_insert['user_id'] = $member_id;
        $data_insert['uuid'] = $device_members[0]->device_token;
        //$data_insert['message'] 			= $message;
        $data_insert['data'] = json_encode($data_field);
        $data_insert['push_type'] = isset($data_field['type']) ? $data_field['type'] : "gcm";

        $this->create($data_insert);
        return true;
    }

    public function get_notification_by_user($option = 'count', $user_id, $start = 0, $limit = API_NUM_RECORD_PER_PAGE, $sort_field = 'id', $sort_value = 'ASC') {
        if (!$user_id) {
            return false;
        }

        $this->db->select('n.id,n.user_id,n.source_id,n.created_date,n.is_read,n.data');
        $this->db->from('user_notification as n');
        $this->db->where('n.user_id', $user_id);
        
        $query = 'n.user_id IS NULL AND type = "all"';
        $this->db->or_where($query);
        
        
        if ($option == 'count') {
            $result = $this->db->get()->num_rows();
        } else {
            if ($limit) {
                $this->db->limit($limit, $start);
            }
            $this->db->order_by('n.' . $sort_field, $sort_value);
            $result = $this->db->get()->result();
        }
        return $result;
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
    
    public function update_where($field, $value, $data_update) {
        
        $this->db->where($field, $value);
        return $this->db->update('user_notification', $data_update);
        

        return false;
    }
    
    public function delete($id) {
        if ($id > 0) {
            $this->db->where('id', $id);
            return $this->db->delete('user_notification');
        }

        return false;
    }

    private function get_member_by_id($id){
        $this->db->where('id', $id);
        $result = $this->db->get('users');
        
        return $result->num_rows() > 0 ? $result->row() : false;
    }
}
