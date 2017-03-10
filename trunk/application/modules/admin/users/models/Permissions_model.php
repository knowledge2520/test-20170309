<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * 
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @created_date: Mar 30, 2015
 * @file: Permissions_model
 * @todo:
 */
class Permissions_model extends MY_Model {

    protected $table_name = "groups";
    protected $key = "id";
    protected $soft_deletes = true;
    protected $date_format = "int";
    protected $log_user = FALSE;
    protected $set_created = false;
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

        if ($status) {
            $this->where('active', $status);
        }
        if ($keyword != '') {
            $this->like('id', $keyword);
            $this->or_like('email', $keyword);
            $this->or_like('first_name', $keyword);
            $this->or_like('last_name', $keyword);
        }

        if ($option == 'total') {
            $return = count($this->find_all());
        } else {
            $this->order_by($order_field, $sort);
            $this->limit($limit, $offset);
            $return = $this->find_all();
        }
        return $return;
    }
    
    
    public function get_permissions_user($user_id){
        $this->load->model('groups_model');
        $group = $this->groups_model->get_group_user($user_id);
        if($group){
            return $this->get_permissions_group($group->group_id);
        }
        return FALSE;
    }
    
    public function get_permissions_group($group_id){
        $this->db->select('g.rule_id, g.group_id, r.name');
        $this->db->from('groups_rules as g');
        $this->db->join('rules as r', 'r.id = g.rule_id', 'left');
        $this->db->where('g.group_id', $group_id);
        $results = $this->db->get();
        
        if($results->num_rows() > 0){
            $data = array();
            foreach ($results->result() as $r){
                $data = array_merge($data, array($r->name => true));
            }
            return $data;
        }
        return FALSE;
    }

    public function get_permission_by($field, $value){
        $table = 'rules';
        $this->db->where($field, $value);
        $result = $this->db->get($table);
        if($result->num_rows() > 0){
            return $result->row();
        }
        return FALSE;
    }
    
    public function update_permission($group_id, $data){
        $permissions = $this->get_permissions_group($group_id);
        
        if (is_array($permissions) || is_object($permissions))
        {
            foreach ($permissions as $m=>$p){
                $name = str_replace('.', '-', $m);
                if(!isset($data[$name])){
                    $this->remove_permission($group_id, $m);
                }
            }
        }
        
        if (is_array($data) || is_object($data))
        {
            foreach ($data as $k=>$d){
                $name = str_replace('-', '.', $k);
                if(!isset($permissions[$name])){
                    $this->insert_permission($group_id, $name);
                }
            }
        }
        return TRUE;
    }
    
    
    function insert_permission($group_id, $permission_name){
        $rule = $this->get_permission_by('name', $permission_name);
        if(!$rule){
            return FALSE;
        }
        $table = 'groups_rules';
        $data = array(
            'group_id' => $group_id,
            'rule_id' => $rule->id,
        );
        $this->db->insert($table, $data);
        return TRUE;
    }
    
    function remove_permission($group_id, $permission_name){
        $rule = $this->get_permission_by('name', $permission_name);
        if(!$rule){
            return FALSE;
        }
        $table = 'groups_rules';
        $this->db->where('group_id', $group_id);
        $this->db->where('rule_id', $rule->id);
        $this->db->delete($table);
        return TRUE;
    }
}
