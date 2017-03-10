<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * 
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @created_date: Mar 30, 2015
 * @file: Member_address_model
 * @todo:
 */
class Users_model extends MY_Model {

    protected $table_name = "users";
    protected $key = "id";
    protected $soft_deletes = true;
    protected $date_format = "int";
    protected $log_user = FALSE;
    protected $set_created = true;
    protected $set_modified = false;
    protected $created_field = "created_on";
    protected $modified_field = "updated_on";

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
    public function getItems($option = 'total', $status = 0, $keyword = '', $order_field = 'id', $sort = 'ASC', $limit = ADMIN_ITEMS_PERPAGE, $offset = false) {

        $this->db->select('u.*, CONCAT_WS(" ", u.first_name, u.last_name) as user_name, c.countryName as country', false);       
        $this->db->from('users u');
        $this->db->join('countries c', 'c.id = u.last_country_id', 'left');

        $query = [];

        if(is_array($status)  && $status){
            $where = [];
            foreach ($status as $key => $item) {
               $where[] = ' u.active = "'.$item.'" ';
            }
            $query[] = ' ( ' . implode(' OR ', $where) . ' ) ';
        }
        $query[] = ' u.active != -1 ';

        if($keyword !='')
        {
            $where = [];
            $where[] = " u.id = '".$keyword."' ";
            $where[] = " u.email LIKE '%".$keyword."%' ";
            $where[] = " CONCAT_WS(' ', u.first_name, u.last_name) LIKE '%".$keyword."%' ";
            $where[] = " u.phone LIKE '%".$keyword."%' ";
            $where[] = " u.email LIKE '%".$keyword."%' ";
            $where[] = " c.countryName LIKE '%".$keyword."%' ";
            //$where[] = " DATE_FORMAT(NOW(), '%Y-%m-%d') - DATE_FORMAT(from_unixtime(dob), '%Y-%m-%d') = '".$keyword."' ";

            $query[] = ' ( ' . implode(' OR ', $where) . ' ) ';
        }

        if($query){
            $this->db->where(implode(' AND ', $query));
        }

        if($option == 'total'){
            $results = $this->db->get();
            $return = $results->num_rows();
        }
        else{
            $this->db->order_by($order_field,$sort);
            $this->db->limit($limit,$offset);
            $results = $this->db->get();

            if($option == 'count_list'){
                $return = $results->num_rows();
            }
            else{
                $return = $results->num_rows() > 0 ? $results->result() : array();
            }
        }
        return $return;        
    }

    public function detail($id) {
        if (!$id) {
            return false;
        }
        $this->select('*');
        //$this->join('ew_member_address', 'ew_member_address.memberId = ew_members.id');
        $this->where('active>=',0);
        $this->where('id',$id);
        $result = $this->find_all($this->table_name);
        return  !empty($result) ? $result[0] : false;
        //return $this->find_by($this->table_name . '.id', $id);
    }
    
    public function deleteUser($id = false){
        if(!$id){
            return false;
        }
        $user = $this->detail($id);
        if($user){
            $this->db->where('id', $id);
            $this->db->update($this->table_name, array('active' => -1));
            return true;
        }
        return false;
    }
    
    public function checkEmail($email){
    	$query = "SELECT * FROM users WHERE `email`='".$email."' AND (`active`=1 OR `active`=0)";
    	$result = $this->db->query($query);
    	return $result->result() ? TRUE : FALSE;
    }

    public function update_status($id, $status){
        $data = array(
            'active' => $status,
        );
        $this->update($id, $data);
        return TRUE;
    }

    public function loadCountry(){
        $query = "SELECT * FROM countries order by countryName";
        $result = $this->db->query($query);
        return $result->result() ? $result->result() : FALSE;
    }

    public function loadCountryByUser($userId){
        $query = "SELECT * FROM business_users_country where user_id='$userId'";
        $result = $this->db->query($query);
        if($result->num_rows()>0){
            return $result->result_array();
        }
        return array();
    }
}
