<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings_model extends MY_Model{

    protected $table_name	= "crm_system_config";
    protected $key			= "id";
    protected $soft_deletes	= FALSE;
    protected $date_format	= "datetime";

    protected $log_user 	= FALSE;

    protected $set_created	= false;
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

    public function getSetting(){
        $results = $this->find_all();
        $data = array();
        foreach($results as $record){
            $data = array_merge($data,array($record->key => $record->value));
        }
        return $data;
    }

    public function updateSetting($data = array()){
        if($data){
//            var_dump($data);exit;
            $this->key = 'key';
            foreach($data as $row){
                $array = array(
                    'value' => $row['value']
                );
                $this->update($row['key'], $array);
            }
            return TRUE;
        }
        return FALSE;
    }
    public function getSettingBy(){

    }

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
    public function getItems($option = 'total' , $status = 0 , $keyword = '' , $order_field = 'id' , $sort = 'ASC'  ,$limit = ADMIN_ITEMS_PERPAGE , $offset = false){

        if($status){
            $this->where('active',$status);
        }
        if($keyword !='')
        {
            $this->like('id',$keyword);
            $this->or_like('name',$keyword);
        }

        if($option == 'total'){
            $return  = count($this->find_all());
        }
        else{
            $this->order_by($order_field,$sort);
            $this->limit($limit,$offset);
            $return  = $this->find_all();
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

        return  $this->find_by('id',$id);
    }
    
    public function get_settings(){
    	$data = array('website_address', 'meta_keywords', 'meta_description', 'website_email', 'website_phone', 'radius_nearby_distance', 'listing_distance');
    	return getCRMConfigs($data);
    }
    
    public function get_setting($data = array()){
        return getCRMConfigs($data);
    }
    
    public function save_settings($data = array()){
    	foreach ($data as $k=>$item){
    		$this->update_where('key', $k, array('value' => $item));
    	}
    }
}