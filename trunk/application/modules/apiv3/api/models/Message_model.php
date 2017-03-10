<?php
class Message_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function send( $params = array() ) {
        if( count($params) ) {
            $this->db->insert("user_messages", $params);
            return $this->db->insert_id();
        }
    }

    /**
     * @param bool|false $params
     * @description: Only receiver who receives the message can update
     * message status to read
     */
    public function read( $params = false ) {
        if( $params ) {
            $this->db->where($params);
            $this->db->update( "user_messages", array("is_read" => 1) );
        }
    }

    /**
     * @param array $params
     * @return bool
     * @description: check the logged in user can update the message
     * from read to unread or not
     */
    public function canUpdate( $params = array() ) {
        $query = $this->db->get_where("user_messages", $params);

        return $query->num_rows() > 0 ? true : false;
    }

    protected function messageQuery( $params = false ) {
        $this->db->select("m.*, p.id AS photoId, p.photo_thumb, p.source, p.width_thumb, p.height_thumb, p.width_source, p.height_source,
        sender.id AS senderId, sender.first_name AS senderFirstName, sender.last_name AS senderLastName, sender.profile_photo AS sender_profile_photo, sender.profile_photo_thumb AS sender_profile_photo_thumb,
        receiver.id AS receiverId, receiver.first_name AS receiverFirstName, receiver.last_name AS receiverLastName, receiver.profile_photo, receiver.profile_photo_thumb ");
        $this->db->from("user_messages m");
        $this->db->join("users sender", "sender.id = m.senderID", "INNER");
        $this->db->join("users receiver", "receiver.id = m.recievedID", "INNER");
        $this->db->join("user_media p", "p.message_id = m.id", "LEFT");
        //$this->db->group_by("m.id");
        $this->db->order_by("m.created_date DESC, m.is_read ASC");

        if( $params ) {
            $this->db->where($params);
        }
        return $this;
    }

    public function findOne( $params = array() ) {
        $query = $this->messageQuery($params)->db->group_by("m.id")->get();
        return $query->num_rows() > 0 ? $query->row() : array();
    }

    /**
     * @param array $params
     * @param string $option
     * @param $start
     * @param $limit
     * @return array
     */
    public function find( $params = false, $memberId, $option = 'total', $start = 0, $limit = API_NUM_RECORD_PER_PAGE ) {
        $sql = "SELECT m.*, p.id AS photoId, p.photo_thumb, p.source, p.width_thumb, p.height_thumb, p.width_source, p.height_source,
        sender.id AS senderId, sender.first_name AS senderFirstName, sender.last_name AS senderLastName, sender.profile_photo AS sender_profile_photo, sender.profile_photo_thumb AS sender_profile_photo_thumb,
        receiver.id AS receiverId, receiver.first_name AS receiverFirstName, receiver.last_name AS receiverLastName, receiver.profile_photo, receiver.profile_photo_thumb
        FROM (
          SELECT * FROM user_messages WHERE id NOT IN(SELECT messageId FROM user_messages_archive WHERE deletedBy = $memberId) AND $params
        ) AS m
        INNER JOIN users sender ON sender.id = m.senderID
        INNER JOIN users receiver ON receiver.id = m.recievedID
        LEFT JOIN user_media p ON p.message_id = m.id
        GROUP BY m.id
        ORDER BY m.created_date DESC, m.is_read ASC
        ";
        if( $option == 'total' ) {
            //$query = $this->messageQuery( $params )->db->group_by("m.id")->get();
            //return $query->num_rows();
            $query = $this->db->query($sql);
            return $query->num_rows();
        } else {
            //$query = $this->messageQuery( $params )->db->group_by("m.id")->limit($limit, $start)->get();
            //return $query->num_rows() > 0 ? $query->result() : array();
            $sql .= " LIMIT $start, $limit";
            $query = $this->db->query($sql);
            return $query->num_rows() > 0 ? $query->result() : array();
        }
    }

    /**
     * @param bool|false $params
     * @param string $option
     * @param int $start
     * @param int $limit
     * @return array
     * @description: Get the latest message in the conversations
     */
    public function newMessages( $params = false, $option = 'total', $start = 0, $limit = API_NUM_RECORD_PER_PAGE ) {
        $where = $params ? "WHERE $params" : "";

        $sql = "SELECT
        m.*,
        p.id AS photoId, p.photo_thumb, p.source, sender.id AS senderId, sender.first_name AS senderFirstName, sender.last_name AS senderLastName, sender.profile_photo AS sender_profile_photo, sender.profile_photo_thumb AS sender_profile_photo_thumb,
        receiver.id AS receiverId, receiver.first_name AS receiverFirstName, receiver.last_name AS receiverLastName, receiver.profile_photo, receiver.profile_photo_thumb
            FROM (
            SELECT *
            FROM user_messages AS c
            $where
            GROUP BY LEAST( senderID, recievedID ) , GREATEST( senderID, recievedID )
            ORDER BY c.created_date DESC
            ) AS m
        INNER JOIN `users` `sender` ON `sender`.`id` = `m`.`senderID`
        INNER JOIN `users` `receiver` ON `receiver`.`id` = `m`.`recievedID`
        LEFT JOIN `user_media` `p` ON `p`.`message_id` = `m`.`id`
        WHERE m.is_show = 1 AND m.is_delete = 0
        GROUP BY LEAST( m.senderID, m.recievedID ) , GREATEST( m.senderID, m.recievedID )
        ORDER BY m.created_date DESC";

        if( $option == 'total' ) {
            //$query = $this->db->get();
            $query = $this->db->query($sql);
            return $query->num_rows();
        } else {
            //$query = $this->db->limit($limit, $start)->get();echo $this->db->last_query();die();
            $sql .= " LIMIT $start, $limit";
            $query = $this->db->query($sql);
            return $query->num_rows() > 0 ? $query->result() : array();
        }
    }

    /**
     * @param bool|false $params
     * @param string $option
     * @param int $start
     * @param int $limit
     * @return array
     * @description: Get the latest message in the conversations
     */
    public function newMessagesConversation($member, $params = false, $option = 'total', $start = 0, $limit = API_NUM_RECORD_PER_PAGE ) {
        $memberId = $member->id;
        $where = $params ? "WHERE $params" : "";

        $sql = "SELECT
        m.*, sender.id AS senderId, sender.first_name AS senderFirstName, sender.last_name AS senderLastName, sender.profile_photo AS sender_profile_photo, sender.profile_photo_thumb AS sender_profile_photo_thumb,
        receiver.id AS receiverId, receiver.first_name AS receiverFirstName, receiver.last_name AS receiverLastName, receiver.profile_photo, receiver.profile_photo_thumb
            FROM (
            SELECT *
            FROM user_messages AS c
            $where
            ORDER BY c.created_date DESC
            ) AS m
        INNER JOIN `users` `sender` ON `sender`.`id` = `m`.`senderID`
        INNER JOIN `users` `receiver` ON `receiver`.`id` = `m`.`recievedID`
        LEFT JOIN `user_conversations` `con` ON `con`.`id` = `m`.`conversationId`
        WHERE m.is_show = 1 AND m.is_delete = 0 AND (FIND_IN_SET('$memberId', con.deleted_by) = 0 OR FIND_IN_SET('$memberId', con.deleted_by) IS NULL)
        GROUP BY LEAST( m.senderID, m.recievedID ) , GREATEST( m.senderID, m.recievedID )
        ORDER BY m.created_date DESC";

        if( $option == 'total' ) {
            //$query = $this->db->get();
            $query = $this->db->query($sql);
            return $query->num_rows();
        } else {
            //$query = $this->db->limit($limit, $start)->get();echo $this->db->last_query();die();
            $sql .= " LIMIT $start, $limit";
            $query = $this->db->query($sql);
            return $query->num_rows() > 0 ? $query->result() : array();
        }
    }

    public function getLatestMessagesConversation($member, $senderID, $recievedID){
        $memberId = $member->id;
        $where = " (senderID = " . (int) $senderID . " AND recievedID = " . (int) $recievedID . ") OR (senderID = " . (int) $recievedID . " AND recievedID = " . (int) $senderID . ")" ;

        $sql = "SELECT
        m.*, sender.id AS senderId, sender.first_name AS senderFirstName, sender.last_name AS senderLastName, sender.profile_photo AS sender_profile_photo, sender.profile_photo_thumb AS sender_profile_photo_thumb,
        receiver.id AS receiverId, receiver.first_name AS receiverFirstName, receiver.last_name AS receiverLastName, receiver.profile_photo, receiver.profile_photo_thumb
            FROM (
            SELECT *
            FROM user_messages AS c
            WHERE $where
            ORDER BY c.created_date DESC
            ) AS m
        INNER JOIN `users` `sender` ON `sender`.`id` = `m`.`senderID`
        INNER JOIN `users` `receiver` ON `receiver`.`id` = `m`.`recievedID`
        LEFT JOIN `user_conversations` `con` ON `con`.`id` = `m`.`conversationId`
        WHERE m.is_show = 1 AND m.is_delete = 0 AND (FIND_IN_SET('$memberId', con.deleted_by) = 0 OR FIND_IN_SET('$memberId', con.deleted_by) IS NULL)
        ORDER BY m.created_date DESC
        LIMIT 1";
        
        $query = $this->db->query($sql);
        return $query->num_rows() > 0 ? $query->row() : '';
    }

    public function isRead( $params = false ) {
        /*
         * is_read = 0: unread
         * is_read = 1: read
         */
        $this->db->select("id");
        $this->db->from("user_messages");
        $this->db->where($params);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? 0 : 1;
    }

    public function getLatestMessage( $params = false ) {
        $this->db->select("m.*");
        $this->db->from("user_messages m");
        $this->db->where($params);
        $this->db->order_by("created_date DESC");
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->row() : array();
    }

    public function deleteMessage( $params = false, $conditions = false ) {
        if( $params ) {
            $this->db->where($conditions);
            $this->db->update( "user_messages", $params );

            // Delete Message images
            $query = $this->db->get_where("user_media", array("message_id" => $conditions['id'], "user_id" => $conditions['senderID']));
            if($query->num_rows() > 0) {
                $results = $query->result();
                $this->media_model->removeMedia($results);
                $this->db->delete("user_media", array("message_id" => $conditions['id'], "user_id" => $conditions['senderID']));
            }
        }
    }

    public function deleteMessagePermanently($memberId, $conversationId) {
        /*if(!empty($memberId) && !empty($conversationId) ) {

            $sql = "UPDATE user_messages set deleted_by='$memberId' where conversationId='$conversationId' AND (senderID='$memberId' OR recievedID='$memberId')";
            $this->db->query($sql);
        }*/
        $this->db->query("INSERT INTO user_messages_archive (messageId, deletedBy, conversationId, created_at)
        SELECT id, @userId := $memberId, conversationId, UNIX_TIMESTAMP(UTC_TIMESTAMP()) FROM user_messages
        WHERE id NOT IN(SELECT messageId FROM user_messages_archive WHERE deletedBy = $memberId AND conversationId = $conversationId )
        AND ( senderID = $memberId OR recievedID = $memberId) AND is_show = 1 AND conversationId = $conversationId");
    }

    /*public function deleteConversationsPermanently( $params = false, $conditions = false, $memberId = false ) {
        if( $params ) {
            $conversation =

            $this->db->where($conditions);
            $this->db->update( "user_conversations", $params );
        }
    }*/
}