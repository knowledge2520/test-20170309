<?php
class Newsfeedlike_model extends CI_Model
{

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->load->model('media_model');
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
     * @param $userId
     * @param $commentId
     * @Description: Delete a comment by user who is the author of comment
     */
    public function deleteComment( $userId, $commentId ) {

        $query = $this->db->get_where("user_comments", array("id" => $commentId));

        if( $query->num_rows() > 0 ) {
            $this->db->delete("user_comments", array("id" => $commentId));
        }
    }

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

                @unlink($item->source);
                @unlink($item->photo_thumb);

                // Remove media data of comment
                $this->db->delete("user_media", array("newfeed_comment_id" => $item->commentId));
            }
        }

        $this->db->delete("user_comments", $arrDeleteComment);
    }

    public function writeComment( $newsfeedId, $newFeedItemId, $newsfeedType, $userId, $comment ) {

        /*$data = array(
            "user_id"       => $userId,
            "newsfeed_id"   => $newsfeedId,
            "content"       => $comment,
            "created_at"    => now(),
        );

        $this->db->insert("user_comment_newsfeed", $data);

        return $this->db->insert_id();*/

        $arrData = array(
            "user_id"   => $userId,
            "content"   => $comment,
            "status"    => 1,
            "created_date"=> now(),
        );

        $arrReturn = array();

        switch ($newsfeedType) {

            case 'ADD_CHECKIN':
                $arrData["checkin_id"] = $newFeedItemId;
                $arrReturn["dataPush"] = "checkin_id";
                $arrReturn['type']     = 'feed';
                $arrReturn['message']  = lang('comment your checkin');
                break;
            case 'ADD_PET_TOPIC':
                $arrData["topic_id"] = $newFeedItemId;
                $arrReturn["dataPush"]  = "topic_id";
                $arrReturn['type']      = 'topic';
                $arrReturn['message']   = lang('comment your topic');
                break;
            case 'ADD_REVIEW':
                $arrData["review_id"]   = $newFeedItemId;
                $arrReturn["dataPush"]  = "review_id";
                $arrReturn['type']      = 'review';
                $arrReturn['message']   = lang('comment your review');
                break;
            case 'ADD_POST_UPDATED':
                $arrData["post_id"]     = $newFeedItemId;
                $arrReturn["dataPush"]  = "post_id";
                $arrReturn['type']      = 'feed';
                $arrReturn['message']   = lang('comment your posted');
                break;
            case 'ADD_PHOTO_LISTING':
                $arrData["album_id"]    = $newFeedItemId;
                $arrReturn["dataPush"]  = "album_id";
                $arrReturn['type']      = 'feed';
                $arrReturn['message']   = lang('comment your listing');
                break;
            case 'ADD_SHARING_PHOTO':
                $arrData["sharing_id"]  = $newFeedItemId;
                $arrReturn["dataPush"]  = "sharing_id";
                $arrReturn['type']      = 'feed';
                $arrReturn['message']   = lang('comment your sharing');
                break;
        }

        $this->db->insert("user_comments", $arrData);

        $arrReturn["commentId"] = $this->db->insert_id();

        return $arrReturn;
    }


    public function getNewsfeedComment( $option = 'all', $newsFeedId, $newsFeedItemId, $newsFeedType, $limit, $start ) {

        $field = "";

        switch($newsFeedType) {
            case 'ADD_CHECKIN':
                $field = "checkin_id";
                break;
            case 'ADD_PET_TOPIC':
                $field =  "topic_id";
                break;
            case 'ADD_REVIEW':
                $field =  "review_id";
                break;
            case 'ADD_POST_UPDATED':
                $field = "post_id";
                break;
            case 'ADD_PHOTO_LISTING':
                $field = "album_id";
                break;

            case 'ADD_SHARING_PHOTO':
                $field = "sharing_id";
                break;
        }

        $this->db->from ("user_comments cn");

        $this->db->join('users u', 'u.id = cn.user_id', 'left');

        $this->db->where("cn.$field =". (int)$newsFeedItemId. " AND cn.status = 1" );

        if($option == 'all') {

            $this->db->select("cn.id, cn.content, cn.created_date, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb");
            $this->db->order_by ( "cn.created_date", "DESC" );
            $this->db->group_by("cn.id");
            $this->db->limit ( $limit, $start );
            $results = $this->db->get();

            $arrData = array();

            if( $results->num_rows() > 0 ) {

                $rows = $results->result();

                $i = 0;

                foreach( $rows as $item ) {

                    format_output_data($item);

                    $sql = "SELECT media.id, media.source, media.photo_thumb, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb
                    FROM user_media media
                    INNER JOIN users u ON u.id = media.user_id
                    WHERE media.newfeed_comment_id = ?";

                    $query = $this->db->query($sql, array($item->id));

                    $arrCommentPhotos = array();

                    if($query->num_rows() > 0) {
                        $photoResults = $query->result();

                        $j = 0;

                        foreach($photoResults as $photo) {

                            format_output_data($photo);

                            $arrCommentPhotos[$j]["photos"] = array(
                                "id"            => $photo->id,
                                "source"        => $photo->source,
                                "photo_thumb"   => $photo->photo_thumb,
                            );

                            $arrCommentPhotos[$j]["userInfo"] = array(
                                "id"            => $photo->userId,
                                "first_name"    => $photo->first_name,
                                "last_name"     => $photo->last_name,
                                "profilePhotos" => array($photo->profile_photo, $photo->profile_photo_thumb)
                            );

                            $j++;
                        }
                    }

                    $arrUser    = array(
                        "id"            => $item->userId,
                        "first_name"    => $item->first_name,
                        "last_name"     => $item->last_name,
                        "profilePhotos" => array($item->profile_photo, $item->profile_photo_thumb)
                    );

                    $arrComment = array(
                        "id"                => $item->id,
                        "newsFeedId"        => $newsFeedId,
                        "content"           => $item->content,
                        "created_date"      => $item->created_date,
                        "photos"            => $arrCommentPhotos,
                    );

                    $arrData[$i]["userInfo"]    = $arrUser;
                    $arrData[$i]["commentInfo"] = $arrComment;

                    $i++;
                }
            }

            return $arrData;

        } else {
            $this->db->select ( 'cn.id' );
            $result = $this->db->get();
            return $result->num_rows();
        }
    }

    public function getCommentDetail( $commentId, $newsFeedId = false ) {

        $sql = "SELECT c.id, c.content, c.created_date, m.source, m.photo_thumb, m.id AS photoId, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb
        FROM user_comments c
        INNER JOIN users u ON u.id = c.user_id
        LEFT JOIN user_media m ON m.newfeed_comment_id = c.id
        WHERE c.id = ?";

        $query = $this->db->query($sql, array("id" => $commentId));

        $arrData = array();

        if($query->num_rows() > 0) {
            $results = $query->result();

            $item = $results[0];

            format_output_data($item);

            $arrData["userInfo"] = array(
                "id"            => $item->userId,
                "first_name"    => $item->first_name,
                "last_name"     => $item->last_name,
                "profilePhotos" => array(
                    $item->profile_photo,
                    $item->profile_photo_thumb
                )
            );

            $arrData["commentInfo"] = array(
                "id"            => $item->id,
                "newsFeedId"    => $newsFeedId,
                "content"       => $item->content,
                "created_date"  => $item->created_date,
            );

            for( $i = 0; $i < count($results); $i++ ) {

                $result = $results[0];

                format_output_data($result);

                $arrData["commentInfo"]["photos"][$i] = array(
                    "id"            => $result->photoId,
                    "source"        => $result->source,
                    "photo_thumb"   => $result->photo_thumb,
                );

            }
        }
        return $arrData;
    }

    /**
     * @param $checkinId
     * @param $userId
     * @param $type
     * @description: like newsfeed checkin type
     */
    public function likeCheckin( $checkinId, $userId, $type ) {

        $query = $this->db->get_where("user_likes", array("user_id" => $userId, "checkin_id" => $checkinId));

        if( $query->num_rows() == 0 ) {
            $this->db->insert("user_likes", array("user_id" => $userId, "checkin_id" => $checkinId, "type" => $type, "created_date" => now()));
        } else {
            $this->db->update("user_likes", array("type" => $type), array("user_id" => $userId, "checkin_id" => $checkinId));
        }
    }

    /**
     * @param $topicId
     * @param $userId
     * @param $type
     * @description: like newsfeed pettalk topic type
     */
    public function likePetTopic( $topicId, $userId, $type ) {

        $query = $this->db->get_where("user_likes", array("user_id" => $userId, "topic_id" => $topicId));

        if( $query->num_rows() == 0 ) {
            $this->db->insert("user_likes", array("user_id" => $userId, "topic_id" => $topicId, "type" => $type, "created_date" => now()));
        } else {
            $this->db->update("user_likes", array("type" => $type), array("user_id" => $userId, "topic_id" => $topicId));
        }
    }

    /**
     * @param $reviewId
     * @param $userId
     * @param $type
     * @description: like newsfeed review type
     */
    public function likeReview( $reviewId, $userId, $type ) {

        $query = $this->db->get_where("user_likes", array("user_id" => $userId, "review_id" => $reviewId));

        if( $query->num_rows() == 0 ) {
            $this->db->insert("user_likes", array("user_id" => $userId, "review_id" => $reviewId, "type" => $type, "created_date" => now()));
        } else {
            $this->db->update("user_likes", array("type" => $type), array("user_id" => $userId, "review_id" => $reviewId));
        }
    }

    /**
     * @param $postId
     * @param $userId
     * @param $type
     * @description: like newsfeed post updated type
     */
    public function likePost( $postId, $userId, $type ) {

        $query = $this->db->get_where("user_likes", array("user_id" => $userId, "post_id" => $postId));

        if( $query->num_rows() == 0 ) {
            $this->db->insert("user_likes", array("user_id" => $userId, "post_id" => $postId, "type" => $type, "created_date" => now()));
        } else {
            $this->db->update("user_likes", array("type" => $type), array("user_id" => $userId, "post_id" => $postId));
        }
    }

    public function likePhotoListing( $albumId, $userId, $type ) {

        $query = $this->db->get_where("user_likes", array("user_id" => $userId, "album_id" => $albumId));

        if( $query->num_rows() == 0 ) {
            $this->db->insert("user_likes", array("user_id" => $userId, "album_id" => $albumId, "type" => $type, "created_date" => now()));
        } else {
            $this->db->update("user_likes", array("type" => $type), array("user_id" => $userId, "album_id" => $albumId));
        }
    }

    public function likePhotoSharing( $sharingId, $userId, $type ) {

        $query = $this->db->get_where("user_likes", array("user_id" => $userId, "sharing_id" => $sharingId));

        if( $query->num_rows() == 0 ) {
            $this->db->insert("user_likes", array("user_id" => $userId, "sharing_id" => $sharingId, "type" => $type, "created_date" => now()));
        } else {
            $this->db->update("user_likes", array("type" => $type), array("user_id" => $userId, "sharing_id" => $sharingId));
        }
    }

    public function likePetInfo( $infoId, $userId, $type ) {

        $query = $this->db->get_where("user_likes", array("user_id" => $userId, "pettalk_info_id" => $infoId));

        if( $query->num_rows() == 0 ) {
            $this->db->insert("user_likes", array("user_id" => $userId, "pettalk_info_id" => $infoId, "type" => $type, "created_date" => now()));
        } else {
            $this->db->update("user_likes", array("type" => $type), array("user_id" => $userId, "pettalk_info_id" => $infoId));
        }
    }

    /*
     * THESE FUNCTIONS BELOW IS USED FOR API V4
     */

    /**
     * @param array $data
     * @return array
     * @description: add new a comment
     */
    public function saveNew($data = array()) {
        $this->db->insert("user_comments", $data);

        $id = $this->db->insert_id();

        return $this->getComment($id);
    }

    /**
     * @param array $data
     * @param array $conditions
     * @return array
     * @description: edit a comment
     */
    public function save( $data = array(), $conditions = array() ) {

        if( count($conditions) && count($data) ) {
            $this->db->update("user_comments", $data, $conditions);

            return $this->getComment($conditions["id"]);
        }
    }

    public function findOne($params = array()) {
        $query = $this->db->get_where("user_comments", $params);
        return $query->num_rows() > 0 ? $query->row() : array();
    }

    public function getComment($id) {

        $commentObj = $this->findOne(array("id" => $id));

        if( $commentObj ) {
            $this->db->select("c.*, ua.id AS newsFeedId, ua.newsFeedType, ua.user_id AS ownerId, u.first_name, u.last_name, u.id AS userId, u.profile_photo, u.profile_photo_thumb");
            $this->db->from('user_comments c');
            $this->db->join("users u", "u.id = c.user_id", "INNER");
            $this->db->group_by("c.id");
            $this->db->where(array("c.id" => $id));

            if( !empty($commentObj->checkin_id) ) {
                $this->db->join("user_newsfeed_activities ua", "ua.checkin_id = c.checkin_id");
            } elseif( !empty($commentObj->topic_id) ) {
                $this->db->join("user_newsfeed_activities ua", "ua.topic_id = c.topic_id");
            } elseif( !empty($commentObj->review_id) ) {
                $this->db->join("user_newsfeed_activities ua", "ua.review_id = c.review_id");
            } elseif( !empty($commentObj->post_id) ) {
                $this->db->join("user_newsfeed_activities ua", "ua.post_update_id = c.post_id");
            } elseif( !empty($commentObj->album_id) ) {
                $this->db->join("user_newsfeed_activities ua", "ua.photo_listing_id = c.album_id");
            } elseif( !empty($commentObj->sharing_id) ) {
                $this->db->join("user_newsfeed_activities ua", "ua.sharing_id = c.sharing_id");
            } elseif( !empty($commentObj->pettalk_info_id) ) {
                $this->db->join("user_newsfeed_activities ua", "ua.pettalk_info_id = c.pettalk_info_id");
            }

            $query = $this->db->get();

            return $query->num_rows() > 0 ? $query->row() : array();
        } else {
            return array();
        }
    }

    /**
     * @param string $option
     * @param $newsFeedId
     * @param $newsFeedItemId
     * @param $newsFeedType
     * @param $limit
     * @param $start
     * @return array
     * @description: Get comment list for a newsfeed
     */
    public function getCommentsByNewsFeedItem( $option = 'all', $newsFeedId, $newsFeedItemId, $newsFeedType, $limit, $start ) {
        $field = "";

        switch($newsFeedType) {

            case ADD_PETTALK_ADOPTION:
            case ADD_PETTALK_FOUND_REPORT:
            case ADD_PETTALK_LOST_REPORT:
                $field = "pettalk_info_id";
                $this->db->join("user_newsfeed_activities ua", "ua.pettalk_info_id = cn.pettalk_info_id", "LEFT");
                break;
            case ADD_CHECKIN:
                $field = "checkin_id";
                $this->db->join("user_newsfeed_activities ua", "ua.checkin_id = cn.checkin_id", "LEFT");
                break;
            case ADD_PET_TOPIC:
                $field =  "topic_id";
                $this->db->join("user_newsfeed_activities ua", "ua.topic_id = cn.topic_id", "LEFT");
                break;
            case ADD_REVIEW:
                $field =  "review_id";
                $this->db->join("user_newsfeed_activities ua", "ua.checkin_id = cn.checkin_id", "LEFT");
                break;
            case ADD_POST_UPDATED:
                $field = "post_id";
                $this->db->join("user_newsfeed_activities ua", "ua.post_update_id = cn.post_id", "LEFT");
                break;
            case ADD_PHOTO_LISTING:
                $field = "album_id";
                $this->db->join("user_newsfeed_activities ua", "ua.photo_listing_id = cn.album_id", "LEFT");
                break;
            case ADD_SHARING_PHOTO:
                $field = "sharing_id";
                $this->db->join("user_newsfeed_activities ua", "ua.sharing_id = cn.sharing_id", "LEFT");
                break;
        }

        $this->db->from ("user_comments cn");

        $this->db->join('users u', 'u.id = cn.user_id', 'left');

        $this->db->where("cn.$field =". (int)$newsFeedItemId. " AND cn.status = 1" );

        if($option == 'all') {

            $this->db->select("cn.id, cn.content, cn.created_date, ua.id AS newsFeedId, ua.newsFeedType, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb");
            $this->db->order_by("cn.created_date", "DESC");
            $this->db->group_by("cn.id");
            $this->db->limit($limit, $start);
            $results = $this->db->get();

            return $results->num_rows() > 0 ? $results->result() : array();

        } else {
            $this->db->select ( 'cn.id' );
            $result = $this->db->get();
            return $result->num_rows();
        }
    }

    public function findCommentMedia($commentObj) {

        $arrData = array();

        $this->db->select("m.id, m.source, m.photo_thumb, width_thumb, height_thumb, width_source, height_source, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb");
        $this->db->from("user_media m");
        $this->db->join("users u", "u.id = m.user_id", "INNER");
        $this->db->where(array("m.newfeed_comment_id" => (int)$commentObj->id));
        $query = $this->db->get();

        if( $query->num_rows() > 0 ) {
            $results = $query->result();
            foreach( $results as $item ) {
                format_output_data($item);

                $arrData[] = array(
                    PHOTOS  => array(
                        ID          => $item->id,
                        SOURCE      => $item->source,
                        PHOTO_THUMB => $item->photo_thumb,
                        THUMB_WIDTH   => $item->width_thumb,
                        THUMB_HEIGHT  => $item->height_thumb,
                        PHOTO_WIDTH   => $item->width_source,
                        PHOTO_HEIGHT  => $item->height_source,
                    ),
                    USER_INFO => array(
                        ID          => $item->userId,
                        FIRST_NAME  => $item->first_name,
                        LAST_NAME   => $item->last_name,
                        PROFILE_PHOTOS => array(
                            $item->profile_photo,
                            $item->profile_photo_thumb
                        )
                    )
                );
            }
        }
        return $arrData;
    }

    public function deleteCommentMedia( $userId, $newFeedCommentId, $strMedia ) {
        if($strMedia != 'all') {
            $mediaSql   = "SELECT source, photo_thumb FROM user_media WHERE user_id = ? AND newfeed_comment_id = ? AND id IN (?)";
            $deleteSql  = "DELETE FROM user_media WHERE user_id = ? AND newfeed_comment_id = ? AND id IN (?)";
            $arrConds   = array($userId, $newFeedCommentId, $strMedia);

        } else {
            $mediaSql = "SELECT source, photo_thumb FROM user_media WHERE user_id = ? AND newfeed_comment_id = ?";
            $deleteSql = "DELETE FROM user_media WHERE user_id = ? AND newfeed_comment_id = ?";
            $arrConds   = array($userId, $newFeedCommentId);
        }

        $mediaQuery = $this->db->query($mediaSql, $arrConds);

        if($mediaQuery->num_rows() > 0) {
            $results = $mediaQuery->result();
            $this->media_model->removeMedia($results);
            $this->db->query($deleteSql, $arrConds);
        }
    }
}