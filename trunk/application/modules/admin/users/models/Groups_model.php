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
class Groups_model extends MY_Model {

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
            $results = $this->find_all();
            if ($results) {
                $return = count($results);
            } else {
                $return = 0;
            }
        } elseif ($option == 'count_list') {
            $this->order_by($order_field, $sort);
            $this->limit($limit, $offset);
            $return = count($this->find_all());
        } else {
            $this->order_by($order_field, $sort);
            $this->limit($limit, $offset);
            $return = $this->find_all();
        }
        return $return;
    }

    function get_group_user($user_id) {
        $this->db->select('g.name, gu.group_id, gu.user_id');
        $this->db->from('users as u');
        $this->db->join('users_groups as gu', 'gu.user_id = u.id', 'left');
        $this->db->join('groups as g', 'gu.group_id = g.id', 'left');
        $this->db->where('u.id', $user_id);
        $result = $this->db->get();
        return ($result->num_rows() > 0) ? $result->row() : FALSE;
    }

    function delete_group($id) {
        if ($id == 1) {
            $this->messages->add(lang('msg_cannot_delete_group_admin'), 'error');
            return FALSE;
        }
        $group = $this->find($id);
        if ($group) {
            $this->db->where('group_id', $id);
            $result = $this->db->get('users_groups');
            if ($result->num_rows() > 0) {
                $this->messages->add(lang('msg_delete_user_in_group'), 'error');
                return FALSE;
            } else {
                $this->db->where('id', $id);
                $this->db->delete('groups');
                return TRUE;
            }
        }
        return FALSE;
    }

}
