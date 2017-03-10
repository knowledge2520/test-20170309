<?php
/**
 * 
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @created_date: May 12, 2015
 * @file: file_name
 * @todo:
 */
class Pet_model extends CI_Model {
	
	function __construct(){
        // Call the Model constructor
        parent::__construct();
    }	
    
    public function add($data)
    {    	
    	$this->db->insert('pets',$data);
    	return $this->db->insert_id();
    }
    public function add_pet_vaccinations($data)
    {
    	$this->db->insert('pet_vaccinations',$data);
    	return $this->db->insert_id();
    }
    public function add_pet_medical_examinations($data)
    {
    	$this->db->insert('pet_medical_examinations',$data);
    	return $this->db->insert_id();
    }
	public function add_pet_physical_exams($data)
    {
    	$this->db->insert('pet_physical_exams',$data);
    	return $this->db->insert_id();
    }
    public function add_pet_medications($data)
    {
    	$this->db->insert('pet_medications',$data);
    	return $this->db->insert_id();
    }
	public function add_pet_surgeries($data)
    {
    	$this->db->insert('pet_surgeries',$data);
    	return $this->db->insert_id();
    }
    public function add_pet_estrus($data)
    {
    	$this->db->insert('pet_estrus',$data);
    	return $this->db->insert_id();
    }
    public function add_pet_allergies($data)
    {
        $this->db->insert('pet_allergies',$data);
        return $this->db->insert_id();
    }
    public function add_pet_weight($data)
    {
    	$this->db->insert('pet_weight',$data);
    	return $this->db->insert_id();
    }
    public function add_pet_contact($data)
    {
    	$this->db->insert('pet_contact',$data);
        $id = $this->db->insert_id();
        if(isset($data['is_default']) && $data['is_default'] == 1){
            $this->update_pet_contact_default($id, $data['pet_id']);
        }
        return $id;
    }
    public function add_pet_veterinarian($data)
    {
    	$this->db->insert('pet_veterinarian',$data);
        $id = $this->db->insert_id();
        if(isset($data['is_default']) && $data['is_default'] == 1){
            $this->update_pet_veterinarian_default($id, $data['pet_id']);
        }
    	return $id;
    }
    public function update($data,$pet_id)
    {
    	$this->db->where('id',$pet_id);
    	$this->db->update('pets',$data);
    }
    public function delete($pet_id)
    {
    	$this->db->where('id',$pet_id);
    	$this->db->update('pets',array('status'=>0));
    }
    public function get_pet($id , $information= true, $vaccinations= false, $medical_examinations= false, 
    						$physical_exams= false , $pet_medications= false, $pet_surgeries= false, $pet_allergies = false, $pet_weight = false, $pet_estrus = false , $pet_contact = false, $pet_badge_profile = false )
    {
    	$this->db->select('*');
    	$this->db->from('pets as p');
    	$this->db->where('p.id',$id);
    	$pet = $this->db->get()->first_row();
    	if(!empty($pet))
    	{
    		$pet_id = $id;   

            $qr_codes = $this->get_qrcode_by('pet_id',$pet_id);
            $qr_code = $qr_codes && !empty($qr_codes) ? reset($qr_codes) : false;
            $pet->qr_code = $qr_code ? $qr_code->code : '';
                  
            $nfc_tag = $this->get_nfc_by('pet_id',$pet_id);
            $pet->nfc_tag = $nfc_tag ? $nfc_tag->code : '';

            $result['information']              = format_output_data($pet);

            // add badges code list
            $result['badge_codes'] = $qr_codes;
            if(!empty($result['badge_codes'])){
                // foreach($result['badge_codes'] as $key => $item)
                // {                   
                //     $result['badge_codes'][$key] = format_output_data($item);
                // }
                $qrcode_types = $this->get_qrcode_types();
                $arr = [];
                if(!empty($qrcode_types))
                {
                    foreach($qrcode_types as $key => $qrt)
                    {
                        foreach($result['badge_codes'] as $ikey => $item)
                        {
                            if($item->type == $qrt->id)
                            {
                                $qrcode_detail =  $this->get_qrcode_detail($item->id);

                                $qrcode_types[$key]->list_qrcodes[] = $qrcode_detail;      
                                unset($result['badge_codes'][$ikey]);                      
                            }
                        }
                        // if(!isset($qrcode_types[$key]->list_qrcodes)){
                        //     unset($qrcode_types[$key]);
                        //     $arr[] = $qrt;
                        // }
                        // else
                        // {
                        //     $qrcode_types[$key] = ($qrt);
                        // }
                        if(isset($qrcode_types[$key]->list_qrcodes)){
                            $arr[] = $qrt;
                        }
                    }
                }
                $result['badge_codes'] = format_output_data($arr);
            }

    		if($vaccinations){
    			$result['vaccinations'] 		= $this->get_pet_vaccinations($pet_id);
    			if($result['vaccinations'])
    			{
    				foreach($result['vaccinations'] as $key => $item)
    				{
    					$result['vaccinations'][$key] = format_output_data($item);
    				}
    			}
    		}
    		if($medical_examinations){
    			$result['medical_examinations'] = $this->get_pet_medical_examinations($pet_id);
    			if($result['medical_examinations'])
    			{
    				foreach($result['medical_examinations'] as $key => $item)
    				{
    					$result['medical_examinations'][$key] = format_output_data($item);
    				}
    			}
    		}
    		if($physical_exams){
    			$result['physical_exams'] 		= $this->get_pet_physical_exams($pet_id);
    			if($result['physical_exams'])
    			{
    				foreach($result['physical_exams'] as $key => $item)
    				{
    					$result['physical_exams'][$key] = format_output_data($item);
    				}
    			}
    		}
    		if($pet_medications){
    			$result['medications'] 		= $this->get_pet_medications($pet_id);
    			if($result['medications'])
    			{
    				foreach($result['medications'] as $key => $item)
    				{
                                        $item->reminder_times_per_day = json_decode($item->reminder_times) && json_decode($item->reminder_times)->reminder_times_per_day ? json_decode($item->reminder_times)->reminder_times_per_day : 0;
                                        $item->reminder_times =  json_decode($item->reminder_times) && json_decode($item->reminder_times)->reminder_times ? json_decode($item->reminder_times)->reminder_times : array();
    					$item->pet_name = $pet->name;
                                        
                                        $result['medications'][$key] = format_output_data($item);
    				}
    			}
    		}
    		if($pet_surgeries){
    			$result['surgeries'] 		= $this->get_pet_surgeries($pet_id);
    			if($result['surgeries'])
    			{
    				foreach($result['surgeries'] as $key => $item)
    				{
    					$result['surgeries'][$key] = format_output_data($item);
    				}
    			}
    		}
    		if($pet_allergies){
    			$result['allergies'] 		= $this->get_pet_allergies($pet_id);
    			if($result['allergies'])
    			{
    				foreach($result['allergies'] as $key => $item)
    				{
    					$result['allergies'][$key] = format_output_data($item);
    				}
    			}
    		}
    		if($pet_weight){
    			$result['weight'] 		= $this->get_pet_weight($pet_id) ;
    			if($result['weight'])
    			{
    				foreach($result['weight'] as $key => $item)
    				{
    					$result['weight'][$key] = format_output_data($item);
    				}
    			}
    		}
    		if($pet_estrus){
    			$result['estrus'] 		= $this->get_pet_estrus($pet_id) ;
    			if($result['estrus'])
    			{
    				foreach($result['estrus'] as $key => $item)
    				{
    					$result['estrus'][$key] = format_output_data($item);
    				}
    			}
    		}
    		if($pet_contact){
    			$result['contact']  = $this->get_pet_contact($pet_id);
    			if($result['contact'])
    			{
    				foreach($result['contact'] as $key => $item)
    				{
    					$result['contact'][$key] = format_output_data($item);
    				}
    			}
                        $result['veterinarian'] = $this->get_pet_veterinarian($pet_id);
    			if($result['veterinarian'])
    			{
    				foreach($result['veterinarian'] as $key => $item)
    				{
    					$result['veterinarian'][$key] = format_output_data($item);
    				}
    			}
    		}
    		if($pet_badge_profile){
    			$result['badge_profile'] 		= $this->get_pet_badge_profile($pet_id);
    			$this->load->helper('site_helper');
                if($result['badge_profile'])
    			{   
                    $reward = json_decode($result['badge_profile']->reward);
                    if($reward){
                        $result['badge_profile']->reward = array(
                            'reward_check' => $reward->reward_check,
                            'reward_value' => $reward->reward_value,
                            'reward_currency'  => $reward->reward_currency,
                        );
                    }
                    else{
                        $result['badge_profile']->reward = array(
                            'reward_check' => '',
                            'reward_value' => '',
                            'reward_currency'  => '',
                        );
                    }
                    $result['badge_profile'] = format_output_data($result['badge_profile']);               
                    $result['badge_profile']->badgeId = $qr_code ? My_qrcode::get_badgeId($qr_code->code) : "";
                    $result['badge_profile']->badge_link = $qr_code ? $qr_code->code : "";
                    $result['badge_profile']->pet_info = array(
                        'name' => $result['information']->name,
                        'dob' => $result['information']->dob,
                        'age' => isset($result['information']->dob_time) && !empty($result['information']->dob_time) ? $result['information']->dob_time['years'] : 0,
                        'type' => $this->get_pet_type($result['information']->type),
                        'breed' => $result['information']->breed,
                        'sex' => $result['information']->sex == 0 ? 'Male' : 'Female' ,
                        'color' => $result['information']->color,
                        'microchip' => $result['information']->microchip,
                    );
    			}
    		}
                
    		return $result;
    	}
    	return false;
    }
    /**
     * 
     * @param string $option
     * @param number $start
     * @param string $limit
     * @param string $keyword
     * @param number $pet_type
     *file_name
     */
    public function get_list_pets($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $keyword = false , $pet_type = 0 , $user_id = 0 , $status = 1)
    {
    	$where = " WHERE p.status = $status ";
    	//set keyword
    	if($keyword)
    	{
    		$where .= " AND p.name LIKE '%$keyword%' ";
    	}
    	//set where pet type
    	if($pet_type && $pet_type !=0)
    	{
    		$where .= " AND p.type = $pet_type ";
    	}
    	//set where user
    	if($user_id && $user_id !=0)
    	{
    		$where .= " AND p.user_id = $user_id ";
    	}
    	
    	 
    	$query = "SELECT p.* FROM pets as p $where ";
    		 
    	if($option =='count')
    	{
    		$result =  $this->db->query($query)->num_rows();
    	}
    	else
    	{
    		$start= intval($start);//start
    		$limit= intval($limit);//limit
    		$query .= " ORDER BY p.id DESC LIMIT $start , $limit";
    		 
    		$result = $this->db->query($query)->result();    		
    	}
    	return $result;
    }
    public function get_scan_location($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $pet_id = 0)
    {
    	$where = " WHERE pet_id = $pet_id ";
    	 
    	$query = "SELECT * FROM pet_scan_location $where ";
    		 
    	if($option =='count')
    	{
    		$result =  $this->db->query($query)->num_rows();
    	}
    	else
    	{
    		$start= intval($start);//start
    		$limit= intval($limit);//limit
    		$query .= " ORDER BY id DESC LIMIT $start , $limit";
    		 
    		$result = $this->db->query($query)->result();    		
    	}
    	return $result;
    }
    public function get_pets($user_id = false){
        if(!$user_id){
            return false;
        }
        return $this->db->order_by('id', 'DESC')->get_where('pets',array('user_id' => $user_id, 'status' => 1))->result();
    }
    public function get_pet_types()
    {
    	return $this->db->get('pet_types')->result();
    }
    public function get_pet_vaccinations($pet_id)
    {
        return $this->db->order_by('id', 'DESC')->get_where('pet_vaccinations', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->result();
    }
    public function get_pet_medical_examinations($pet_id)
    {
    	return $this->db->order_by('id', 'DESC')->get_where('pet_medical_examinations', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->result();
    }
    public function get_pet_physical_exams($pet_id)
    {
    	return $this->db->order_by('id', 'DESC')->get_where('pet_physical_exams', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->result();
    }
    public function get_pet_medications($pet_id)
    {
    	return $this->db->order_by('id', 'DESC')->get_where('pet_medications', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->result();
    }
    public function get_pet_surgeries($pet_id)
    {
    	return $this->db->order_by('id', 'DESC')->get_where('pet_surgeries', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->result();
    }
    public function get_pet_allergies($pet_id)
    {
    	return $this->db->order_by('id', 'DESC')->get_where('pet_allergies', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->result();
    }
    public function get_pet_weight($pet_id)
    {
    	return $this->db->order_by('id', 'DESC')->get_where('pet_weight', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->result();
    }
    public function get_pet_estrus($pet_id)
    {
    	return $this->db->order_by('id', 'DESC')->get_where('pet_estrus', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->result();
    }
    public function get_pet_contact($pet_id)
    {
    	return $this->db->order_by('id', 'DESC')->get_where('pet_contact', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->result();
    }
    public function get_pet_veterinarian($pet_id)
    {
    	return $this->db->order_by('id', 'DESC')->get_where('pet_veterinarian', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->result();
    }
    public function get_pet_badge_profile($pet_id)
    {
        $this->db->where('pet_id',  $pet_id);
        $result = $this->db->get('pet_settings');
        if($result->num_rows() == 0) {
            $data = array(
                'pet_id' => $pet_id,
                'contact_name' => 0,
                'contact_primary_number' => 0,
                'contact_alternate_number_1' => 0,
                'contact_alternate_number_2' => 0,
                'contact_email' => 0,
                'veterinarian' => 0,
                'medications' => 0,
                'allergies'=> 0,
                'vaccinations' => 0,
                'notes_check' => 0,
                'notes' => "",
            );
            $this->db->insert('pet_settings', $data);
        }
    	return $this->db->order_by('id', 'DESC')->get_where('pet_settings', array('pet_id'=>$pet_id) ,API_NUM_RECORD_PER_PAGE, 0)->row();
    }
    
    public function update_pet_vaccinations($data,$id)
    {
    	$this->db->where('id',$id);
    	return $this->db->update('pet_vaccinations',$data);
    }
    public function update_pet_medical_examinations($data,$id)
    {
    	$this->db->where('id',$id);
    	return $this->db->update('pet_medical_examinations',$data);
    }
    public function update_pet_physical_exams($data,$id)
    {
    	$this->db->where('id',$id);
    	return $this->db->update('pet_physical_exams',$data);
    }
    public function update_pet_medications($data,$id)
    {
    	$this->db->where('id',$id);
    	return $this->db->update('pet_medications',$data);
    }
    public function update_pet_surgeries($data,$id)
    {
    	$this->db->where('id',$id);
    	return $this->db->update('pet_surgeries',$data);
    }
    public function update_pet_estrus($data,$id)
    {
    	$this->db->where('id',$id);
    	return $this->db->update('pet_estrus',$data);
    }
    public function update_pet_allergies($data,$id)
    {
        $this->db->where('id',$id);
        return $this->db->update('pet_allergies',$data);
    }
    public function update_pet_weight($data,$id)
    {
    	$this->db->where('id',$id);
    	return $this->db->update('pet_weight',$data);
    }
    public function update_pet_contact($data,$id)
    {
        if(isset($data['is_default']) && $data['is_default'] == 1){
            $this->update_pet_contact_default($id, $data['pet_id']);
        }
    	$this->db->where('id',$id);
    	return $this->db->update('pet_contact',$data);
    }
    public function update_pet_veterinarian($data,$id)
    {                
        if(isset($data['is_default']) && $data['is_default'] == 1){
            $this->update_pet_veterinarian_default($id, $data['pet_id']);
        }
    	$this->db->where('id',$id);
    	return $this->db->update('pet_veterinarian',$data);
    }
    private function update_pet_contact_default($id, $pet_id){
        $this->db->where('pet_id',$pet_id);
        $this->db->update('pet_contact', array('is_default' => 0));
        
        $this->db->where('id',$id);
        $this->db->update('pet_contact', array('is_default' => 1));
    }
    private function update_pet_veterinarian_default($id, $pet_id){
        $this->db->where('pet_id',$pet_id);
        $this->db->update('pet_veterinarian', array('is_default' => 0));
        
        $this->db->where('id',$id);
        $this->db->update('pet_veterinarian', array('is_default' => 1));
    }
    public function update_pet_badge_profile($data,$id)
    {
    	$this->db->where('pet_id',$id);
    	return $this->db->update('pet_settings',$data);
    }
    
    public function update_pet_modified_date($id){
        $this->db->where('id',$id);
        $this->db->update('pets', array('modified_date' => now()));
    }
    
    public function delete_pet_additional_data($table_name,$id)
    {
    	if(!empty($table_name) && !empty($id))
    	{
    		$this->db->where('id',$id);
    		return $this->db->delete($table_name);
    	}
    	return false;
    }
    public function get_pet_additional_data($table_name,$id)
    {
    	if(!empty($table_name) && !empty($id))
    	{
    		$row = $this->db->get_where($table_name,array('id'=>$id))->first_row();
    		return $row;
    	}
    	return false;
    }
    
    public function get_qrcode_by($field = false, $value = false, $pet_id = false){
        // if(!$field || !$value){
        //     return false;
        // }
        
        // $this->db->select('*');
        // $this->db->where($field, $value);
        // if($pet_id){
        //     $this->db->where('pet_id!=', NUll);
        // }
        // $result = $this->db->get('pet_qrcode');
        // if($result->num_rows() > 0){
        //     $result = $result->result();
        //     // $this->load->helper('site');
        //    // $result->code = My_qrcode::get_full($result->code);
        //     return $result;
        // }
        // return false;

        if(!$field || !$value){
            return false;
        }

        $where = " qr.$field = '$value'  "; 

        if($pet_id){
            $where.= " AND `qr.pet_id` IS NOT NULL  "; 
        }

        $query = "SELECT qr.id AS id, qr.code AS `code`, qr.code_id AS `code_id`, COALESCE(qr.photo,'') AS `photo`, COALESCE(qr.description, '') AS description, COALESCE(t.id,'') AS `type`, COALESCE(c.name,'Pet Widget')  AS `brand`, COALESCE(qr.pet_id, '') AS pet_id
                    FROM pet_qrcode qr 
                    LEFT JOIN pet_badge_type t ON t.id = qr.type_id
                    LEFT JOIN pet_badge b ON b.id = qr.badge_id
                    LEFT JOIN pet_badge_category c ON c.id = b.category_id
                    WHERE $where 
                    ORDER BY qr.id DESC";

        $result = $this->db->query($query);
        $returnType = $field == 'pet_id' ? 1 : 0;
        if($result->num_rows() > 0){
            $result = $returnType ? $result->result() : $result->row();
            return $result;
        }
        return $returnType ? array() : false;
    }
    
    public function get_nfc_by($field = false, $value = false, $pet_id = false){
        if(!$field || !$value){
            return false;
        }
        
        $this->db->select('*');
        $this->db->like($field, $value, 'before');
        if($pet_id){
            $this->db->where('pet_id!=', NUll);
        }
        $result = $this->db->get('pet_qrcode');
        if($result->num_rows() > 0){
            $result = $result->row();
            $this->load->helper('site');
//            $result->code = My_nfc::get_full($result->code);
            return $result;
        }
        return false;
    }
    
    public function update_qrcode($code, $data){
        //remove pet from old qrcode
        // $this->db->where('pet_id', $pet_id);
        // $this->db->update('pet_qrcode', array('pet_id' => NULL));
        
        if(empty($data)){
            return false;
        }

        $latitude = $longitude = false;

        if(isset($data['latitude']) && isset($data['longitude'])){
            $latitude = $data['latitude'];
            $longitude = $data['longitude'];

            unset($data['latitude']);
            unset($data['longitude']);
        }        

        //add pet to new qrcode
        $this->db->where('code', $code);
        $data = array_merge($data , array( 'modified_date' => now() ));
        $this->db->update('pet_qrcode', $data);
        
        if($latitude && $longitude){
            $this->add_scan_location($data['pet_id'], $latitude, $longitude, 'qr_code');
        }
    }
    
     public function update_nfc($code, $pet_id, $latitude = false, $longitude = false){
        //remove pet from old qrcode
        $this->db->where('pet_id', $pet_id);
        $this->db->update('pet_qrcode', array('pet_id' => NULL));
        
        //add pet to new qrcode
        $this->db->like('code', $code, 'before');
        $data = array(
            'modified_date' => now(),
            'pet_id' => $pet_id
        );
        $this->db->update('pet_qrcode', $data);
        
        if($latitude && $longitude){
            $this->add_scan_location($pet_id, $latitude, $longitude, 'nfc');
        }
    }
    
    public function add_scan_location($pet_id, $latitude, $longitude, $type){
        $data = array(
            'latitude' => $latitude,
            'longitude' => $longitude,
            'scannedDate' => now(),
            'pet_id' => $pet_id,
            'type' => $type,

        );
        $this->db->insert('pet_scan_location', $data);
    }
    
    public function unlink_qrcode($pet_id, $id = false){
        if(!$pet_id){
            return false;
        }
        $this->db->where('pet_id', $pet_id);
        if($id){
            $this->db->where('id', $id);
        }
        $this->db->update('pet_qrcode', array('pet_id' => NULL, 'description' => NULL, 'type_id' => NULL, 'photo' => NULL, 'modified_date' => now()));
    }
    
     public function unlink_nfc($pet_id){
        $this->db->where('pet_id', $pet_id);
        $this->db->update('pet_qrcode', array('pet_id' => NULL));
    }
    
    public function update_pet_location($location, $pet_id, $type){
        if(isset($location) && !empty($location)){
            $data = array(
                'latitude' => $location['latitude'] ? $location['latitude'] : 0,
                'longitude' => $location['longitude'] ? $location['longitude'] : 0,
                'pet_id' => $pet_id,
                'type' => $type,
                'created_date' => now(),
            );
            $this->db->insert('pet_scan_location', $data);
        }
        return FALSE;
    }
    
    public function get_pet_type($type_id){
        $this->db->where('id', $type_id);
        $result = $this->db->get('pet_types');
        if($result->num_rows() > 0){
            $result = $result->row();
            return $result->name;
        }
        return "";
    }

    /**
     * 
     * @param string $option
     * @param number $start
     * @param string $limit
     * @param string $keyword
     * @param number $pet_type
     *file_name
     */
    public function get_list_qrcodes($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $keyword = false , $pet_id = 0)
    {
        $arr = [];
        //set keyword
        if($keyword)
        {
            $arr[] = " qr.code_id LIKE '%$keyword%' ";
        }
        //set where pet
        if($pet_id && $pet_id !=0)
        {
            $arr[] = " qr.pet_id = $pet_id ";
        }
        
        $where = implode(' AND ', $arr);

        $query = "SELECT qr.id AS id, qr.code AS `code`, qr.code_id AS `code_id`, COALESCE(qr.photo,'') AS `photo`, COALESCE(qr.description, '') AS description, COALESCE(t.id,'') AS `type`, COALESCE(c.name,'Pet Widget')  AS `brand`, COALESCE(qr.pet_id, '') AS pet_id
                    FROM pet_qrcode qr 
                    LEFT JOIN pet_badge_type t ON t.id = qr.type_id
                    LEFT JOIN pet_badge b ON b.id = qr.badge_id
                    LEFT JOIN pet_badge_category c ON c.id = b.category_id
                    WHERE $where ";
             
        if($option =='count')
        {
            $result =  $this->db->query($query)->num_rows();
        }
        else
        {
            $start= intval($start);//start
            $limit= intval($limit);//limit
            $query .= " ORDER BY qr.code_id ASC LIMIT $start , $limit";
             
            $result = $this->db->query($query)->result();           
        }
        return $result;
    }

    /**
     * 
     * @param string $option
     * @param number $start
     * @param string $limit
     * @param string $keyword
     * @param number $pet_type
     *file_name
     */
    public function get_list_qrcode_types($option = 'count', $start = 0, $limit = API_NUM_RECORD_PER_PAGE , $keyword = false)
    {

        $arr[] = " t.status = 1 ";

        //set keyword
        if($keyword)
        {
            $arr[] = " t.name LIKE '%$keyword%' ";
        }

        $where = implode(' AND ', $arr);

        $query = "SELECT t.id, COALESCE(t.name, '') AS name, COALESCE(t.description, '') AS description, COALESCE(t.photo, '') AS photo
                    FROM pet_badge_type t 
                    WHERE $where ";
             
        if($option =='count')
        {
            $result =  $this->db->query($query)->num_rows();
        }
        else
        {
            $start= intval($start);//start
            $limit= intval($limit);//limit
            $query .= " ORDER BY t.name ASC LIMIT $start , $limit";
             
            $result = $this->db->query($query)->result();           
        }
        return $result;
    }

    public function get_qrcode_detail($id = false, $code = false){
        if(!$code && !$id){
            return false;
        }

        $where = $code ? " qr.code = '$code'  " : " qr.id = '$id' "; 

        $query = "SELECT qr.id AS id, qr.code AS `code`, qr.code_id AS `code_id`, COALESCE(qr.photo,'') AS `photo`, COALESCE(qr.description, '') AS description, COALESCE(t.id,'') AS `type`, COALESCE(c.name,'Pet Widget')  AS `brand`, COALESCE(qr.pet_id, '') AS pet_id
                    FROM pet_qrcode qr 
                    LEFT JOIN pet_badge_type t ON t.id = qr.type_id
                    LEFT JOIN pet_badge b ON b.id = qr.badge_id
                    LEFT JOIN pet_badge_category c ON c.id = b.category_id
                    WHERE $where ";

        $result = $this->db->query($query);
        if($result->num_rows() > 0){
            $result = $result->row();
            return $result;
        }
        return false;
    }
    
    public function get_qrcode_owner($id, $pet_id){
        $this->db->where('id', $id);
        $this->db->where('pet_id', $pet_id);
        $result = $this->db->get('pet_qrcode');
        return $result->num_rows() > 0 ? $result->row() : false;
    }

    public function check_pet_owner($pet_id, $user_id){
        $this->db->where('id', $pet_id);
        $this->db->where('user_id', $user_id);
        $result = $this->db->get('pets');

        return $result->num_rows() > 0 ? $result->row() : false;
    }

    public function get_qrcode_types(){
        $query = "SELECT t.id, COALESCE(t.name, '') AS name, COALESCE(t.description, '') AS description, COALESCE(t.photo, '') AS photo
                    FROM pet_badge_type t
                    ORDER BY t.name ASC";
        $result = $this->db->query($query);
        return $result->num_rows() > 0 ? $result->result() : array();
    }
}