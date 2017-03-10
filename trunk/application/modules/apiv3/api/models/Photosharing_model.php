<?php
class Photosharing_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

    function addNewPhoto( $userId, $content ) {

        $data = array(
            "user_id"       => $userId,
            "content"       => $content,
            "created_date"  => now(),
            "updated_date"  => now(),
        );

        $this->db->insert("user_photo_sharing", $data);

        return $this->db->insert_id();
    }

    function deleteSharingPhoto( $id, $userId ) {
        $this->db->delete("user_photo_sharing", array("id" => $id, "user_id" => $userId));
    }
}