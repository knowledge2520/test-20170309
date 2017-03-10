<?php
class Menu_model extends CI_Model{
    public function listAll(){
        $this->db->order_by('order', 'ASC');
        $results = $this->db->get('menu');
        if($results->num_rows() > 0){
            return $results->result();
        }
        return FALSE;
    }
    
    //count user need approve
    public function count_approve_listing(){
    	$this->db->where('status', 0);
    	$this->db->from('business_items');
    	return $this->db->count_all_results();
    }
    
    public function count_approve_media_listing(){
        $this->db->where('status', 0);
        $this->db->where('pet_id', NULL);
        $this->db->where('business_id!=', NULL);
    	$this->db->from('user_media');
    	return $this->db->count_all_results();
    }
}