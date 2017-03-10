<?php

class Command_model extends CI_Model {
	
	public function getMedia($table = false, $param = false){
		if(!$table){
			return fasle;
		}
		$query = "SELECT * FROM ".$table." WHERE `".$param."` NOT LIKE 'http%' AND `".$param."` is not null";

		$results = $this->db->query($query);

		return $results->result();
	}

	public function getMediaAll($table = false, $param = false){
		if(!$table){
			return fasle;
		}
		$query = "SELECT * FROM ".$table." WHERE `".$param."` NOT LIKE 'http://graph.facebook.com/%' AND `".$param."` is not null";

		$results = $this->db->query($query);

		return $results->result();
	}


	public function getQRCode(){
		$results = $this->db->get('pet_qrcode');
		$data = [];
		if($results->num_rows() > 0){
			$results = $results->result();

			foreach ($results as $key => $item) {
				$data = array_merge($data, array($item->code_id));
			}
			return $data;
		}
		return false;
	}

}
