<?php
class Usertag_model extends CI_Model {

    protected $userTags;

    function __construct(){
        // Call the Model constructor
        parent::__construct();
    }

    public function saveNew( $params = array() ) {
        $this->db->insert("user_tag", $params);
    }

    public function save( $condition = array(), $params = array() ) {
        $this->db->update("user_tag", $params, $condition);
    }

    public function delete( $condition = array() ) {
        $this->db->delete("user_tag", $condition);
    }

    public function getUserTag( $sourceId, $tagType ) {
        $this->db->select("t.*, u.id AS userId, u.first_name, u.last_name");
        $this->db->from("user_tag t");
        $this->db->join("users u", "u.id = t.userTag", "INNER");
        $this->db->where(array("sourceId" => $sourceId, "sourceType" => $tagType));
        $query = $this->db->get();
        $this->userTags = $query->num_rows() > 0 ? $query->result() : array();
        return $this;
    }

    public function transformer() {

        $arrData = array();

        if( $this->userTags ) {
            foreach( $this->userTags as $item ) {
                $arrData[] = array(
                    ID          => $item->userId,
                    FIRST_NAME  => $item->first_name,
                    LAST_NAME   => $item->last_name,
                    TEXT_RANGE  => $item->textRange,
                );
            }
        }
        return $arrData;
    }
}