<?php
class Usercontact_model extends CI_Model {

    protected $userContact;

    function __construct(){
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * @param array $params
     * @description: add user contact
     * @tag: petuserfriend lib
     */
    function addUserContact( $params = array() ) {
        $this->db->insert("user_contact", $params);
    }

    /**
     * @param bool|false $condition
     * @param array $params
     * @tag: petuserfriend lib
     */
    function updateUserContact( $condition = false, $params = array() ) {
        if( $condition && count($params) ) {
            $this->db->update("user_contact", $params, $condition);
        }
    }

    /**
     * @param $userId
     * @param string $option
     * @param int $start
     * @param int $limit
     * @return $this
     * @description: Get user block list
     * @tag: petuserfriend lib > getUserContactItems
     */
    function getUserContactList( $userId, $status = 0, $option = 'total', $start = 0, $limit = API_NUM_RECORD_PER_PAGE ) {
        $this->db->select("c.registed, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb");
        $this->db->from("user_contact c");
        $this->db->join("users u", "u.id = c.registed", "INNER");
        $this->db->where(array("c.user_id" => $userId, "status" => $status));

        if($option == 'total') {
            $this->db->limit( $limit, $start);
            $query = $this->db->get();
            return $query->num_rows();
        } else {
            $query = $this->db->get();
            $this->userContact = $query->num_rows() > 0 ? $query->result() : array();
            return $this;
        }
    }

    function getUserContactListTransform() {
        $arrData = array();

        if( $this->userContact ) {
            foreach( $this->userContact as $item ) {
                format_output_data($item);
                $arrData[] = array(
                    ID              => $item->registed,
                    FIRST_NAME      => $item->first_name,
                    LAST_NAME       => $item->last_name,
                    PROFILE_PHOTOS  => array(
                        $item->profile_photo, $item->profile_photo_thumb
                    )
                );
            }
        }

        return $arrData;
    }

    function getUserContact( $userId, $blockUserId ) {
        $userId = (int)$userId; $blockUserId = (int)$blockUserId;
        $this->db->from("user_contact");
        $this->db->where("(user_id = $userId AND registed = $blockUserId) OR (registed = $userId AND user_id = $blockUserId)");
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result() : false;
    }

    function findOne( $params = false ) {
        $this->db->from("user_contact");
        $this->db->where($params);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->row() : false;
    }
}