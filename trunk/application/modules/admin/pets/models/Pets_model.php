<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pets_model extends MY_Model{

    protected $table_name	= "pets";
    protected $key			= "id";
    protected $soft_deletes	= false;
    protected $date_format	= "int";

    protected $log_user 	= FALSE;

    protected $set_created	= true;
    protected $set_modified = false;
    protected $created_field = "created_date";
    protected $modified_field = "modified_date";

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
    protected $validation_rules         = array();
    protected $insert_validation_rules  = array();
    protected $skip_validation          = false;
    protected $empty_validation_rules   = array();

    /**
     *
     * @param string $option
     * @param number $status
     * @param string $keyword
     * @param string $order_field
     * @param string $sort
     * @param string $limit
     * @param string $offset
     * @return Ambigous <number, mixed, boolean, string>
     *file_name
     */
    public function getItems($option = 'total' , $status = 0 , $keyword = '' , $order_field = 'p.id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){
        
        $this->db->select('p.*, u.first_name, u.last_name, u.email, u.username, c.code, c.code_id as badge_id_code, CONCAT_WS(" ", u.first_name, u.last_name) as user_name, t.name as pet_type_name', false);
        $this->db->from('pets p');
        $this->db->join('pet_types t', 't.id = p.type', 'left');
        $this->db->join('users u', 'u.id = p.user_id', 'left');
        $this->db->join('pet_qrcode c', 'c.pet_id = p.id', 'left');
        $this->db->group_by('p.id');
        
        $query = [];

        if($status){
            $query[] = "p.status = '$status'";
        }
        if($keyword != '' )
        {
            $where = [];
           	$where[] = " p.id LIKE '%$keyword%'";
           	$where[] = " p.name LIKE '%$keyword%'";
           	$where[] = " p.origin LIKE '%$keyword%'"; 
           	$where[] = " p.microchip LIKE '%$keyword%'";
           	$where[] = " p.breed LIKE '%$keyword%'"; 
            $where[] = " p.color LIKE '%$keyword%'"; 
            $where[] = " t.name LIKE '%$keyword%'"; 
           	$where[] = " c.code_id LIKE '%$keyword%'"; 
           	$where[] = " CONCAT_WS(' ', u.first_name, u.last_name) LIKE '%$keyword%'";

            $query[] = implode(' OR ', $where);		
        }

        if($query){
            $this->db->where(implode(' AND ', $query));
        }

        if($option == 'total'){
            $results = $this->db->get();
            $return = $results->num_rows(); 
        }
        elseif($option == 'count_list'){
            $this->db->order_by($order_field,$sort);
            $this->db->limit($limit,$offset);
            $results = $this->db->get();
            $return = $results->num_rows(); 
        }
        else{
            $this->db->order_by($order_field,$sort);
            $this->db->limit($limit,$offset);
            $results = $this->db->get();
            $return  = $results->num_rows() > 0 ? $results->result() : array(); 
        }
        return $return;
    }

    public function getTableData1($table, $pet_id){
        $this->db->where('pet_id', $pet_id);
        $result = $this->db->get($table);
        if($result->num_rows() > 0){
            return $result->result();
        }
        return FALSE;
    }

    public function detail($id)
    {
        if(!$id)
        {
            return false;
        }
        $this->select('pets.*, pet_qrcode.code_id');
        $this->join('pet_qrcode', 'pet_qrcode.pet_id = pets.id', 'left');

        return  $this->find_by('pets.id',$id);
    }

//    public function delete($id)
//    {
//        return $this->db->delete($this->table_name, array('id' => $id));
//    }

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

    function dayAdd($key = false, $value = false, $date = array()){
        if($key == 'month'){
            return strtotime($value.$key, strtotime($date['year'].'-'.$date['mon']));
        }
        elseif($key == 'day'){
            return strtotime($value.$key, strtotime($date['year'].'-'.$date['mon'].'-'.$date['mday']));
        }
        return false;

    }

    public function check_relationship($id){
        return $this->find_by('type', $id);
    }


    public function getTableData($table, $pet_id, $option = 'total' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){
        $this->table_name = $table;
        $this->where('pet_id', $pet_id);

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
    public function deleteInfo($table, $id){
        $this->table_name = $table;
        if($this->delete($id)){
            return TRUE;
        }
        return FALSE;
    }

    public function deletePhoto($id){
        $pet = $this->detail($id);
        unlink($_SERVER['DOCUMENT_ROOT'].'/'.$pet->profile_photo);
        unlink($_SERVER['DOCUMENT_ROOT'].'/'.$pet->profile_photo_thumb);
        $data = array(
            'profile_photo' => '',
            'profile_photo_thumb' => '',
        );
        $this->update($id,$data);
        return TRUE;
    }
    
    public function deletePet($id){
        $data = array(
            'status' => 0,
        );
        $this->update($id, $data);
        $this->unlinkBadgeId($id);
        
        return TRUE;
    }
    
    public function updateBadgeId($pet_id, $badge_id){
    	//get code
    	$code = $this->getBadgeId($badge_id);
    	if(!$code){
    		return array('result' => false, 'message' => 'badge ID is not exist');
    	}
    	
    	//check badge id in use
    	if(!$this->checkBadgeId($pet_id, $badge_id)){
    		return array('result' => false, 'message' => 'badge ID is in use');
    	}
    	
    	//update
    	
    	$data = array(
    			'pet_id' => NULL,
    	);
    	$this->db->where('pet_id', $pet_id);
    	$this->db->update('pet_qrcode', $data);
    	
    	$data = array(
    			'pet_id' => $pet_id,
    	);
    	$this->db->where('code_id', $badge_id);
    	$this->db->update('pet_qrcode', $data);
    	return array('result' => true);
    }
   
    public function getBadgeId($badge_id){
    	$this->db->where('code_id', $badge_id);
    	$result = $this->db->get('pet_qrcode');
    	
    	if($result->num_rows() > 0){
    		return $result->row();
    	}
    	return false;
    }
    
    //check badge id not use
    public function checkBadgeId($pet_id = false, $badge_id = false){
    	$code = $this->getBadgeId($badge_id);
    	if($code){
    		if($code->pet_id == NULL){
    			return true;
    		}else{
    			if($code->pet_id == $pet_id){
    				return true;
    			}
    			return false;
    		}
    	}
    	return false;
    }
    
    public function unlinkBadgeId($pet_id){
    	$data = array(
    			'pet_id' => NULL,
    	);
    	$this->db->where('pet_id', $pet_id);
    	$this->db->update('pet_qrcode', $data);
    }

    public function getOverall(){
        // get top pet type
        $query = "SELECT t.name, t.id, COUNT(t.id) AS total
                    FROM pets p
                    LEFT JOIN pet_types t ON  t.id = p.type
                    WHERE p.status = 1
                    GROUP BY t.id
                    ORDER BY COUNT(t.id) DESC
                    LIMIT 3";
        $results = $this->db->query($query);
        $data['top_pet_types'] = $results->num_rows() > 0 ? $results->result() : array();

        if(isset($data['top_pet_types']) && !empty($data['top_pet_types'])){
            foreach ($data['top_pet_types'] as $key => $item) {
                $data['top_pet_types'][$key]->male = $this->countGenderByType($item->id, '0');
                $data['top_pet_types'][$key]->female = $this->countGenderByType($item->id, '1');
            }
        }

        $this->db->where('status', 1);
        $this->db->from($this->table_name);
        $data['total']                  = $this->db->count_all_results();

        return $data;   
    }

    public function countGenderByType($type, $gender){
        $query = "SELECT *
                    FROM `pets`
                    WHERE `status` = 1 AND `type` = '$type' AND `sex` = '$gender'";
        $results = $this->db->query($query);
        return $results->num_rows();
    }
}