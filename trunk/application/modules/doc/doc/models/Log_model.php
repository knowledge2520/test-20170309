<?php
class Log_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function items( $option = 'total', $keyword = '', $start = 0, $limit = 20 ) {

        $sql = 'SELECT * FROM api_log';
        $arrWhere = array();

        if( !empty($keyword) ) {
            $sql .= ' WHERE apiUrl LIKE ?';
            $arrWhere = array('%' . $keyword . '%');
        }
        $sql .= ' ORDER BY id DESC';

        if( $option == 'total' ) {
            $query = $this->db->query($sql, $arrWhere);//echo $this->db->last_query();die();
            return $query->num_rows();
        } else {
            $sql .= ' LIMIT '.$start .', '.$limit;
            $query = $this->db->query($sql, $arrWhere);
            return $query->num_rows() > 0 ? $query->result() : array();
        }
    }
}