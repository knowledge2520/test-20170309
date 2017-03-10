<?php
require_once './application/modules/apiv3/api/models/Notification_model.php';
require_once './application/modules/apiv3/api/helpers/notification_helper.php';
class Pet_model extends CI_Model{
    public function get_pet_from_qrcode($code){
        $this->db->select('c.code, c.id AS qrcodeId, c.modified_date AS lastRequestTime, p.*, t.name as pet_type, u.first_name, u.last_name, u.id as user_id');
        $this->db->from('pet_qrcode c');
        $this->db->join('pets p','c.pet_id=p.id','left');
        $this->db->join('pet_types t','p.type=t.id','left');
        $this->db->join('users u','p.user_id=u.id','left');
        $this->db->like('code',$code,'before');
        $this->db->where('pet_id!=',NULL);
        
        $result = $this->db->get();
        if($result->num_rows() > 0) {

            $item = $result->row();
            // $requestTime = strtotime(date('Y-m-d H:i:s'));  // current time

            // $lastRequestTime = $item->lastRequestTime + (1 * 60); // last request time + 5 minutes

            // if( $requestTime >= $lastRequestTime ) {
            //     $this->db->update('pet_qrcode', array('modified_date' => now()), array('id' => $item->qrcodeId));

            //     $noti = new Notification_model();

            //     $action_type = get_action_type('PET_BADGE');

            //     $messagePush = $item->name . '\'s badge was just scanned';

            //     $dataPush = array(
            //         'action_type'               => 'PET_BADGE',
            //         'receiver_id'               => $item->user_id,
            //         'type'                      => 'pet-badge',
            //         'pet_id'                    => $item->id,
            //         'bages_unread_notification' => count_unread_notification($item->user_id) + 1,
            //     );
            //     $noti->send_push_notification($item->user_id, $messagePush, $dataPush, $action_type->id, $item->id);
            // }

            return $item;
        }
        return FALSE;
    }
    
    public function get_contact($id){
        $this->db->select('*');
        $this->db->from('pet_contact');
        $this->db->where('pet_id', $id);
        $this->db->where('is_default', 1);
        $this->db->order_by('id', 'DESC');
        $results = $this->db->get();
        
        if($results->num_rows() > 0){
            return $results->row();
        }
        return FALSE;
    }
    
    public function get_veterinarian($id){
        $this->db->select('*');
        $this->db->from('pet_veterinarian');
        $this->db->where('pet_id', $id);
        $this->db->where('is_default', 1);
        $this->db->order_by('id', 'DESC');
        $results = $this->db->get();
        
        if($results->num_rows() > 0){
            return $results->row();
        }
        return FALSE;
    }
    
    public function get_medications($id){
        $this->db->select('*');
        $this->db->from('pet_medications');
        $this->db->where('pet_id', $id);
        //$this->db->where('reminder_active', 1);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(5);
        $results = $this->db->get();
        
        if($results->num_rows() > 0){
            return $results->result();
        }
        return FALSE;
    }
    
    public function get_allergies($id){
        $this->db->select('*');
        $this->db->from('pet_allergies');
        $this->db->where('pet_id', $id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(5);
        $results = $this->db->get();
        
        if($results->num_rows() > 0){
            return $results->result();
        }
        return FALSE;
    }
    
    public function get_vaccinations($id){
        $this->db->select('*');
        $this->db->from('pet_vaccinations');
        $this->db->where('pet_id', $id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(5);
        $results = $this->db->get();
        
        if($results->num_rows() > 0){
            return $results->result();
        }
        return FALSE;
    }
    
    public function get_settings($id){
        $this->db->select('*');
        $this->db->from('pet_settings');
        $this->db->where('pet_id', $id);
        $results = $this->db->get();
        
        if($results->num_rows() > 0){
            return $results->row();
        }
        return FALSE;
    }
    
    public function get_pet_from_code($code){
        $this->db->select('c.*, ');
        $this->db->from('pet_qrcode c');
        $this->db->like('c.code', $code, 'before');
        $results = $this->db->get();
        
        if($results->num_rows() > 0){
            return  $results->row();
        }
        return FALSE;
    }
    
    public function update_pet_location($pet, $latitude, $longitude){
        $distance = 0;

        $this->db->where('pet_id',$pet->pet_id);
        $this->db->order_by('id', 'DESC');
        $result = $this->db->get('pet_scan_location');
        if($result->num_rows() > 0){
            $result = $result->row();
            
            //$limit_distance = 60;
            $this->load->helper('site_helper');
            $distance = distance($result->latitude, $result->longitude, $latitude, $longitude, 'K');
            //if($distance <= $limit_distance){
                $data = array(
                    "pet_id" => $pet->pet_id,
                    "latitude" => $latitude,
                    "longitude" => $longitude,
                    "scannedDate" => now(),
                    "type" => "web",
                );

                $this->db->insert('pet_scan_location', $data);
            //}            
        }
        else{
            $data = array(
                "pet_id" => $pet->pet_id,
                "latitude" => $latitude,
                "longitude" => $longitude,
                "scannedDate" => now(),
                "type" => "web",
            );

            $this->db->insert('pet_scan_location', $data);

        }


        return $distance;
    }
    
    function send_push_scan($code){
        $this->db->select('c.code, c.id AS qrcodeId, c.modified_date AS lastRequestTime, p.*, t.name as pet_type, u.first_name, u.last_name, u.id as user_id');
        $this->db->from('pet_qrcode c');
        $this->db->join('pets p','c.pet_id=p.id','left');
        $this->db->join('pet_types t','p.type=t.id','left');
        $this->db->join('users u','p.user_id=u.id','left');
        $this->db->like('code',$code,'before');
        $this->db->where('pet_id!=',NULL);

        $result = $this->db->get();
        if($result->num_rows() > 0) {

            $item = $result->row();

            $this->db->update('pet_qrcode', array('modified_date' => now()), array('id' => $item->qrcodeId));

            $noti = new Notification_model();

            $action_type = get_action_type('PET_BADGE');

            $messagePush = $item->name . '\'s badge profile has just been viewed';

            $dataPush = array(
                'action_type'               => 'PET_BADGE',
                'receiver_id'               => $item->user_id,
                'type'                      => 'pet-badge',
                'pet_id'                    => $item->id,
                'bages_unread_notification' => count_unread_notification($item->user_id) + 1,
            );
            $noti->send_push_notification($item->user_id, $messagePush, $dataPush, $action_type->id, $item->id);

            return $item;
        }
        return FALSE;

    }
}