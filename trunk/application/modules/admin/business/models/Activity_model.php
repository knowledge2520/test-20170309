<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Activity_model extends MY_Model {

    /**
     * Hooks
     *
     * @var object
     * */
    protected $_hooks;
    protected $table_name = "activity_log";
    protected $key = "id";
    protected $soft_deletes = FALSE;
    protected $date_format = "int";
    protected $log_user = FALSE;
    protected $set_created = TRUE;
    protected $set_modified = FALSE;
    protected $created_field = "created_date";
    protected $modified_field = "modified_date";
    public function getItems($option = 'total', $status = array(), $params = array(), $order_field = 't.id', $sort = 'ASC', $limit = ADMIN_ITEMS_PERPAGE, $offset = false,$field_id=false) {
        // $this->db->select('t.*, b.name as entity_name', false);
        // $this->db->from("activity_log t");
        // $this->db->join("user_reviews r", " r.id = l.entity_id AND l.entity_type = 'Business Review' ", "left");
        // $this->db->join("business_items b", " (b.id = l.entity_id AND l.entity_type = 'Business') OR (l.entity_type = 'Business Review' AND r.business_id = b.id) ", "left");

        

        $queryCondition = "1=1";
        if($field_id){
            $queryCondition .= " AND (entity_id='$field_id')";
        }
        if (isset($params['actor_name'])) {
            $tmp = [];
            $queryCondition .=" AND (t.actor_name LIKE '%" . $this->db->escape_like_str($params['actor_name']) . "%' OR";
            $queryCondition .=" t.actor_id LIKE '%" . $this->db->escape_like_str($params['actor_name']) . "%')";
        }
        if(isset($params['action']) && !empty($params['action']) ){
            if($params['action'] != -1){
                $queryCondition .=" AND (t.action LIKE '%" . $params['action'] . "%')";
            }
        }
        if(isset($params['startDate']) && isset($params['endDate'])){
            if(strtotime($params['endDate']) >= strtotime($params['startDate'])){
                $startDate = $this->db->escape_like_str($params['startDate']);
                $endDate = $this->db->escape_like_str($params['endDate']);
                $endDate =  date("Y-m-d",strtotime($endDate)) . " 00:00:00";
                $startDate =    date("Y-m-d",strtotime($startDate)) . " 00:00:00";;
                $queryCondition .= " AND  (event_time >= '$startDate' AND event_time <= '$endDate')";
            }
        }

        // $this->db->where($queryCondition);
        // $this->db->group_by('t.id');

        // cusom query to select table business and review
        $query = "SELECT t.*, b.name as listing_name, b.id as listing_id, CONCAT_WS(' ', u.first_name, u.last_name) as full_name
            FROM activity_log t
            LEFT JOIN user_reviews r ON (r.id = t.entity_id AND t.entity_type = 'Business Review')            
            LEFT JOIN business_items b ON (b.id = t.entity_id AND t.entity_type = 'Business') OR (t.entity_type = 'Business Review' AND r.business_id = b.id)
            LEFT JOIN users u ON (u.id = t.actor_id)
            WHERE $queryCondition
            GROUP BY t.id";

        if ($option == 'total') {
            // $results = $this->db->get();
            // $return = $results->num_rows();
            $results = $this->db->query($query);
            $return = $results->num_rows();
        } else{
            // $this->db->order_by($order_field, $sort);
            // $this->db->limit($limit, $offset);
            // $results = $this->db->get();
            $query .= " ORDER BY '$order_field' $sort
                        LIMIT $offset, $limit ";
            $results = $this->db->query($query);
            if ($option == 'count_list') {
                $return = $results->num_rows();
            } else {
                $return = $results->num_rows() > 0 ? $results->result() : array();
            }
        }
        return $return;
    }
}
