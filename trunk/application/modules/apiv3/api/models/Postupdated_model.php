<?php
class Postupdated_model extends CI_Model {

    protected $dataResults = array();
    protected $postUpdate;

    function __construct(){
        // Call the Model constructor
        parent::__construct();
        //$this->load->library('parse');
        $this->load->helper('site');
    }

    public function newItem($userId, $title, $content) {

        $data = array(
            "title"         => $title,
            "content"       => $content,
            "user_id"       => $userId,
            "created_date"  => now(),
        );

        $this->db->insert("user_post_updated", $data);

        $postId = $this->db->insert_id();

        return $postId;
    }

    public function updateItem($userId, $newFeedId = null, $postId = null, $title, $content) {

        /*$query = $this->db->get_where("user_newsfeed_activities", array("id" => $newFeedId, "user_id" => $userId));

        if($query->num_rows() > 0) {

            $newfeed = $query->row();*/

        $this->db->update("user_post_updated",
            array("title" => $title, "content" => $content, "updated_date" => now()),
            array("id" => $postId, "user_id" => $userId));

        /*return $newfeed->post_update_id;
    }*/
    }

    public function deletePostMedia($newfeedId = null, $postId = null) {

        $query = "";

        if($postId) {
            $query = $this->db->get_where("user_media", array("post_update_id" => $postId));

        } elseif($newfeedId) {
            $query = $this->db->get_where("user_media", array("newfeed_id" => $newfeedId));
        }


        if($query->num_rows() > 0) {

            $items = $query->result();

            foreach ($items as $item) {
                @unlink($item->source);
                @unlink($item->photo_thumb);
            }

            if($postId) {
                $this->db->delete("user_media", array("post_update_id" => $postId));

            } elseif($newfeedId) {
                $this->db->delete("user_media", array("newfeed_id" => $newfeedId));
            }

        }
    }

    public function deletePost($postId = null, $userId = null) {

        //$this->db->delete("user_newsfeed_activities", array("post_update_id" => $postId, "user_id" => $userId));

        $this->db->delete("user_post_updated", array("id" => $postId, "user_id" => $userId));
    }

    public function item( $newsFeedItemId ) {
        $sqlPost   = "SELECT p.id, p.title, p.content
        FROM user_post_updated p
        WHERE p.id = ?";

        $postQuery = $this->db->query($sqlPost, array($newsFeedItemId));

        $this->postUpdate = $postQuery->num_rows() > 0 ? $postQuery->row() : array();

        return $this;
    }

    public function itemTransformer( $item = false ) {

        if( $this->postUpdate ) {
            $item = $this->postUpdate;
        }

        $shortContent = giveShortContent($item->content);

        return array(
            ID              => $item->id,
            TITLE           => $item->title,
            SHORT_CONTENT   => $shortContent,
            CONTENT         => $item->content,
        );
    }

     /**
     * @param $userId
     * @param string $option
     * @param $limit
     * @param $start
     * @return bool
     * @description: Get user list users. Sorting from closest. We also get user info and
     * friend status
     */
    public function getPostUpdatedByKeyword( $userId, $keyword, $option = 'all', $limit, $start ) {
        $userId = (int) $userId;
        $query_keyword ="(
            upo.content LIKE '%" . $this->db->escape_like_str(trim($keyword)) . "%'
            )";

        $sqlPost = "SELECT n.*, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb
                FROM user_newsfeed_activities n
                INNER JOIN users u ON u.id = n.user_id
                LEFT JOIN user_post_updated upo ON upo.id = n.post_update_id
                WHERE newsFeedType = 'ADD_POST_UPDATED' AND $query_keyword";
        if($option == ALL) {
            $query = $this->db->query($sqlPost);
            return $query->num_rows();
        } else {
            $sqlPost .= " LIMIT $start, $limit";
            $query = $this->db->query($sqlPost);
            return $query;
        }
    }
}