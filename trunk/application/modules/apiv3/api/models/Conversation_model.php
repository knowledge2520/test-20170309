<?php
class Conversation_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function save( $params = array() ) {
        if( count($params) ) {
            $this->db->insert("user_conversations", $params);
            return $this->db->insert_id();
        }
    }

    public function findOne( $params = false ) {
        if( $params ) {
            $this->db->from("user_conversations");
            $this->db->where($params);
            $query = $this->db->get();
            return $query->num_rows() > 0 ? $query->row() : array();
        } else {
            return array();
        }
    }

    public function conversationToggle( $conversationId, $memberId, $isDelete = true ) {
        $conversation = $this->findOne(array("id" => $conversationId));
        if($conversation) {
                // for safe
            $arrDeletedBy = !empty($conversation->deleted_by) ? explode(",", $conversation->deleted_by) : array();
            if($isDelete) {
                if(!in_array($memberId, $arrDeletedBy)) {
                    array_push($arrDeletedBy, $memberId);
                }
                $strDeletedBy = implode(",", $arrDeletedBy);
            } else {
                    /*if(($key = array_search($memberId, $arrDeletedBy)) !== false) {
                        unset($arrDeletedBy[$key]);
                    }*/
                    // When a user begin chatting, we should remove all deleted user fields in conversation
                $strDeletedBy = '';
            }
            $this->db->query("UPDATE user_conversations SET deleted_by = '$strDeletedBy' WHERE id = $conversationId");
        }
    }
}