<?php
class Home_model extends CI_Model{
    public function getBanner(){
        $results = $this->db->get('banners');
        if($results->num_rows() > 0){
            return $results->result();
        }
        return FALSE;
    }

    public function getLastBusiness($limit = 5){
        $this->db->limit($limit);
        $results = $this->db->get('business_items');
        if($results->num_rows() > 0){
            return $results->result();
        }
        return FALSE;
    }

    public function getLastPet($limit = 5){
        $this->db->limit($limit);
        $results = $this->db->get('pets');
        if($results->num_rows() > 0){
            return $results->result();
        }
        return FALSE;
    }

    public function getFaqs(){
        $results = $this->db->get('faqs');
        if($results->num_rows() > 0){
            return $results->result();
        }
        return FALSE;
    }

    public function getSettings(){
        $results = $this->db->get('crm_system_config');
        if($results->num_rows() > 0){
            $data = array();
            foreach($results->result() as $record){
                $data = array_merge($data,array($record->key => $record->value));
            }
            return $data;
        }
        return FALSE;
    }
}