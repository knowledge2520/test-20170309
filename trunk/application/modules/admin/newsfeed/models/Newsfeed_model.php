<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Newsfeed_model extends CI_Model
{
	function __construct(){
        // Call the Model constructor
        parent::__construct();
        $this->load->helper('upload');
        //$this->load->model('media_model');
    }

    public function getNewsFeedItem($id){
        $this->db->where('id', $id);
        $result = $this->db->get('user_newsfeed_activities');
        return $result->num_rows() > 0 ? $result->row() : false;
    }

    public function getNewsFeedItemBy($key, $value){
        $this->db->where($value, $value);
        $result = $this->db->get('user_newsfeed_activities');
        return $result->num_rows() > 0 ? $result->row() : array();
    }

    public function getNewsfeedPhotos($id){
        $this->db->where('newfeed_id', $id);
        $result = $this->db->get('user_media');
        return $result->num_rows() > 0 ? $result->result() : array();
    }

    // function getNewsFeedBy($key, $value){
    // 	$this->db->where($key, $value);
    // 	$result = $this->db->get('user_newsfeed_activities');

    // 	return $result->num_rows() > 0 ? $result->row() : false;
    // }

    public function deleteCommentByNewsfeed( $newFeedItemId, $newFeedType ) {

        $commentSql = "SELECT comment.id AS commentId, media.source, media.photo_thumb FROM user_media media
        INNER JOIN user_comments comment ON comment.id = media.newfeed_comment_id ";

        $arrDeleteComment = array();

        switch($newFeedType) {

            case ADD_PETTALK_ADOPTION:
            case ADD_PETTALK_FOUND_REPORT:
            case ADD_PETTALK_LOST_REPORT:
                $commentSql .= "WHERE comment.pettalk_info_id = ?";
                $arrDeleteComment = array("pettalk_info_id" => $newFeedItemId);
                break;
            case ADD_CHECKIN:
                $commentSql .= "WHERE comment.checkin_id = ?";
                $arrDeleteComment = array("checkin_id" => $newFeedItemId);
                break;
            case ADD_PET_TOPIC:
                $commentSql .= "WHERE comment.topic_id = ?";
                $arrDeleteComment = array("topic_id" => $newFeedItemId);
                break;
            case ADD_REVIEW:
                $commentSql .= "WHERE comment.review_id = ?";
                $arrDeleteComment = array("review_id" => $newFeedItemId);
                break;
            case ADD_POST_UPDATED:
                $commentSql .= "WHERE comment.post_id = ?";
                $arrDeleteComment = array("post_id" => $newFeedItemId);
                break;
            case ADD_PHOTO_LISTING:
                $commentSql .= "WHERE comment.album_id = ?";
                $arrDeleteComment = array("album_id" => $newFeedItemId);
                break;
            case ADD_SHARING_PHOTO:
                $commentSql .= "WHERE comment.sharing_id = ?";
                $arrDeleteComment = array("sharing_id" => $newFeedItemId);
                break;
        }

        $commentQuery = $this->db->query($commentSql, array($newFeedItemId));

        if( $commentQuery->num_rows() > 0 ) {

            $commentResults = $commentQuery->result();

            foreach( $commentResults as $item ) {
                S3_Upload::removeMedia($item->source);
                S3_Upload::removeMedia($item->photo_thumb);

                // Remove media data of comment
                $this->db->delete("user_media", array("newfeed_comment_id" => $item->commentId));
            }
        }

        $this->db->delete("user_comments", $arrDeleteComment);
    }

    /**
     * @param $newFeedItemId
     * @param $newFeedType
     * @description: delete newsfeed like
     */
    public function deleteLikeByNewsfeed( $newFeedItemId, $newFeedType ) {

        //$this->db->delete("user_likes_newsfeed", array("newsfeed_id" => $newfeedId));
        switch($newFeedType) {

            case ADD_PETTALK_ADOPTION:
            case ADD_PETTALK_FOUND_REPORT:
            case ADD_PETTALK_LOST_REPORT:
                $this->db->delete("user_likes", array("pettalk_info_id" => $newFeedItemId));
                break;
            case ADD_CHECKIN:
                $this->db->delete("user_likes", array("checkin_id" => $newFeedItemId));
                break;
            case ADD_PET_TOPIC:
                $this->db->delete("user_likes", array("topic_id" => $newFeedItemId));
                break;
            case ADD_REVIEW:
                $this->db->delete("user_likes", array("review_id" => $newFeedItemId));
                break;
            case ADD_POST_UPDATED:
                $this->db->delete("user_likes", array("post_id" => $newFeedItemId));
                break;
            case ADD_PHOTO_LISTING:
                $this->db->delete("user_likes", array("album_id" => $newFeedItemId));
                break;
            case ADD_SHARING_PHOTO:
                $this->db->delete("user_likes", array("sharing_id" => $newFeedItemId));
                break;
        }
    }

    /**
     * @param $newFeedId
     * @Description: This method removes ALL the photos of a news feed. It requires
     * the news feed ID. Otherwise we should call the another "remove media" of
     * post updated, checkin, write review or topic discussion
     */
    public function deleteNewsfeedMedia( $newFeedId ) {
        $query = $this->db->get_where("user_media", array("newfeed_id" => $newFeedId ));

        if($query->num_rows() > 0) {
            $results = $query->result();

            if($results){
                foreach ($results as $key => $item) {
                    S3_Upload::removeMedia($item->source);
                    S3_Upload::removeMedia($item->photo_thumb);
                }
            }
            
            $this->db->delete("user_media", array("newfeed_id" => $newFeedId ));
        }
    }

    /**
     * @param $newsFeedId
     * @param $newsFeedType
     * @param $newsFeedItemId
     * @description: Delete sharing newsfeed data
     * @tag: petnewsfeed lib
     */
    public function deleteSharingInfo( $newsFeedId, $newsFeedItemId, $newsFeedType ) {
        $this->db->delete("user_newsfeed_sharing", array("newsFeedId" => $newsFeedId, "newsFeedType" => $newsFeedType, "newsFeedItemId" => $newsFeedItemId));
    }

    /**
     * @param $newsFeedId
     * @param $newsFeedItemId
     * @param $newsFeedType
     * @description: Delete a newsfeed
     * @tag: petnewsfeed lib
     */
    public function deleteNewsfeed( $newsFeedId, $newsFeedItemId, $newsFeedType ) {
        $this->db->delete("user_newsfeed_activities", array("id" => $newsFeedId,
            "newsFeedType" => $newsFeedType,
        ));
    }

    public function deletePostUpdate($postId = null) {
        $this->db->delete("user_post_updated", array("id" => $postId));
    }

    public function deleteUserTag( $condition = array() ) {
        $this->db->delete("user_tag", $condition);
    }

    public function addNew( $data = array() ){
        if( !$data ){
            return false;
        }
        $this->db->insert('user_newsfeed_activities', $data);
        return $this->db->insert_id();
    }
    public function update($id, $data = array()){
        $this->db->where('id', $id);
        $this->db->update('user_newsfeed_activities', $data);
    }

    /**
     * [excuteItem description]
     * @param  [type] $field  [description]
     * @param  [type] $value  [description]
     * @param  string $action delete, restore, remove from trash
     * @return [type]         [description]
     */
    public function excuteItem($field, $value, $action = 'delete', $force_delete = false){
        if(!$field || !$value){
            return false;
        }

        $this->db->where($field, $value);
        $results = $this->db->get($this->table_name);

        if($results->num_rows() > 0){
            switch ($action) {
                case 'restore':
                    $this->db->where($field, $value);
                    $this->db->update($this->table_name, array('status' => 1));
                    break;
                
                case 'remove':
                    $this->db->where($field, $value);
                    if($force_delete){
                        $this->db->delete($this->table_name);
                    }else{
                        $this->db->update($this->table_name, array('status' => 2));
                    }
                    break;

                default:
                    $this->db->where($field, $value);
                    $this->db->update($this->table_name, array('status' => 0));
                    break;
            }    
            
            return true;       
        }

        return false;
    }
}