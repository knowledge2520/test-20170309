<?php defined('BASEPATH') OR exit('No direct script access allowed');

function getTotalUserFriends($userId) {

    $ci = &get_instance();

    /*$query = $ci->db->get_where("user_friends", array("user_id" => $userId));

    return $query->num_rows();*/
    $ci->load->model('member_model');
    return $ci->member_model->get_user_friends("count", $userId, 1, 'first_name', 'ASC', 0, API_NUM_RECORD_PER_PAGE, false);
}

function getTotalUserListingPhotos($userId) {

    $ci = &get_instance();

    //$sql = "SELECT id FROM user_media WHERE business_id IS NOT NULL AND review_id IS NULL AND tip_id IS NULL AND user_id = ?";
    /*$sql = "SELECT COUNT(id) FROM user_media WHERE user_id = ? AND status = 1
          AND message_id IS NULL";

    $query = $ci->db->query($sql, array($userId));

    return $query->num_rows();*/
    $ci->load->model('member_model');
    return $ci->member_model->get_user_photos_v4('count', 0, 20, $userId, false );
}

function getTotalUserReviews($userId) {
    $ci = &get_instance();
    $ci->load->model('review_model');
    return $ci->review_model->get_reviews_by_user('count', 0, false, $userId);
}

/**
 * @param $businessId
 * @return mixed
 * @description: return the total visit for a listing
 */
function getTotalVisit($businessId) {

    $ci = &get_instance();

    $query = $ci->db->get_where("user_checkins", array("business_id" => $businessId, "status" => 1));

    return $query->num_rows();
}

function getNewsfeedTotalLike( $newsfeedItemId, $newsfeedType ) {

    $ci = &get_instance();

    /*$query = $ci->db->get_where("user_likes_newsfeed", array("newsfeed_id" => $newsfeedId));

    return $query->num_rows();*/
    $arrCondition = array(
        "type" => 0
    );

    switch ($newsfeedType) {

        case ADD_PETTALK_ADOPTION:
            $arrCondition["pettalk_info_id"] = $newsfeedItemId;
            break;
        case ADD_PETTALK_FOUND_REPORT:
            $arrCondition["pettalk_info_id"] = $newsfeedItemId;
            break;
        case ADD_PETTALK_LOST_REPORT:
            $arrCondition["pettalk_info_id"] = $newsfeedItemId;
            break;
        case ADD_CHECKIN:
            $arrCondition["checkin_id"] = $newsfeedItemId;
            break;
        case ADD_PET_TOPIC:
            $arrCondition["topic_id"] = $newsfeedItemId;
            break;
        case ADD_REVIEW:
            $arrCondition["review_id"] =  $newsfeedItemId;
            break;
        case ADD_POST_UPDATED:
            $arrCondition["post_id"] = $newsfeedItemId;
            break;
        case ADD_PHOTO_LISTING:
            $arrCondition["album_id"] = $newsfeedItemId;
            break;
        case ADD_SHARING_PHOTO:
            $arrCondition["sharing_id"] = $newsfeedItemId;
            break;
    }
    $query = $ci->db->get_where("user_likes", $arrCondition);

    return $query->num_rows();

}

function getNewsfeedTotalReview( $newsfeedItemId, $newsfeedType ) {

    $ci = &get_instance();

    /*$query = $ci->db->get_where("user_comment_newsfeed", array("newsfeed_id" => $newsfeedId));

    return $query->num_rows();*/
    $arrCondition = array();

    switch ($newsfeedType) {

        case ADD_PETTALK_ADOPTION:
        case ADD_PETTALK_FOUND_REPORT:
        case ADD_PETTALK_LOST_REPORT:
            $arrCondition["pettalk_info_id"] = $newsfeedItemId;
            break;
        case ADD_CHECKIN:
            $arrCondition = array("checkin_id" => $newsfeedItemId);
            break;
        case ADD_PET_TOPIC:
            $arrCondition = array("topic_id" => $newsfeedItemId);
            break;
        case ADD_REVIEW:
            $arrCondition = array("review_id" => $newsfeedItemId);
            break;
        case ADD_POST_UPDATED:
            $arrCondition = array("post_id" => $newsfeedItemId);
            break;
        case ADD_PHOTO_LISTING:
            $arrCondition = array("album_id" => $newsfeedItemId);
            break;
        case ADD_SHARING_PHOTO:
            $arrCondition = array("sharing_id" => $newsfeedItemId);
            break;
    }
    $query = $ci->db->get_where("user_comments", $arrCondition);

    return $query->num_rows();
}

function hasLikedNewsfeed( $newsfeedItemId, $userId, $newsfeedType ) {

    $ci = &get_instance();

    $field = "";

    switch ($newsfeedType) {

        case ADD_PETTALK_ADOPTION:
        case ADD_PETTALK_FOUND_REPORT:
        case ADD_PETTALK_LOST_REPORT:
            $field = "pettalk_info_id";
            break;
        case ADD_CHECKIN:
            $field = "checkin_id";
            break;
        case ADD_PET_TOPIC:
            $field =  "topic_id";
            break;
        case ADD_REVIEW:
            $field =  "review_id";
            break;
        case ADD_POST_UPDATED:
            $field = "post_id";
            break;
        case ADD_PHOTO_LISTING:
            $field = "album_id";
            break;
        case ADD_SHARING_PHOTO:
            $field = "sharing_id";
            break;
    }

    $query = $ci->db->get_where("user_likes", array($field => $newsfeedItemId, "user_id" => $userId, "type" => 0));
    return $query->num_rows() > 0 ? 1 : 0;
}

function getNewsfeedTotalSharing( $newsfeedItemId, $newsfeedType ) {

    $ci = &get_instance();

    $query = $ci->db->get_where("user_newsfeed_sharing", array(
        "newsFeedType"      => $newsfeedType,
        "newsFeedItemId"    => $newsfeedItemId
    ));

    return $query->num_rows();
}

function removeNewsFeedMedia($strMedia, $newFeedId, $userId, $newFeedItemId = false, $newFeedType = '') {

    $ci = &get_instance();

    if($strMedia) {

        $strMedia = "(" . $strMedia . ")";

        $mediaSQL   = "";
        $mediaQuery = "";
        $deleteQuery= "";
        $deleteId   = $newFeedId;

        if( $newFeedType == ADD_PET_TOPIC ) {

            $mediaSQL = "SELECT source, photo_thumb FROM user_media WHERE user_id = ? AND topic_id = ? AND id IN $strMedia";

            $mediaQuery = $ci->db->query($mediaSQL, array($userId, $newFeedItemId));

            $deleteQuery = "DELETE FROM user_media WHERE user_id = ? AND topic_id = ? AND id IN $strMedia";

            $deleteId = $newFeedItemId;

        } elseif ( $newFeedType == ADD_REVIEW ) {

            $mediaSQL = "SELECT source, photo_thumb FROM user_media WHERE user_id = ? AND review_id = ? AND id IN $strMedia";

            $mediaQuery = $ci->db->query($mediaSQL, array($userId, $newFeedItemId));

            $deleteQuery = "DELETE FROM user_media WHERE user_id = ? AND review_id = ? AND id IN $strMedia";

            $deleteId = $newFeedItemId;

        } elseif ($newFeedType == ADD_POST_UPDATED) {

            $mediaSQL = "SELECT source, photo_thumb FROM user_media WHERE user_id = ? AND post_update_id = ? AND id IN $strMedia";

            $mediaQuery = $ci->db->query($mediaSQL, array($userId, $newFeedItemId));

            $deleteQuery = "DELETE FROM user_media WHERE user_id = ? AND post_update_id = ? AND id IN $strMedia";

            $deleteId = $newFeedItemId;

        } else {
            $mediaSQL = "SELECT source, photo_thumb FROM user_media WHERE user_id = ? AND newfeed_id = ? AND id IN $strMedia";

            $mediaQuery = $ci->db->query($mediaSQL, array($userId, $newFeedId));

            $deleteQuery = "DELETE FROM user_media WHERE user_id = ? AND newfeed_id = ? AND id IN $strMedia";

            $deleteId = $newFeedId;
        }

        if($mediaQuery->num_rows() > 0) {

            $mediaResults = $mediaQuery->result();

            foreach( $mediaResults as $item ) {

                unlink($item->source);
                unlink($item->photo_thumb);
            }
        }

        $ci->db->query($deleteQuery, array($userId, $deleteId));
    }
}

function removeNewsFeedCommentMedia($strMedia, $newFeedCommentId, $userId) {

    $ci = &get_instance();

    if($strMedia) {

        if($strMedia != 'all') {
            $strMedia = "(" . $strMedia . ")";

            $mediaSQL = "SELECT source, photo_thumb FROM user_media WHERE user_id = ? AND newfeed_comment_id = ? AND id IN $strMedia";
        } else {

            $mediaSQL = "SELECT source, photo_thumb FROM user_media WHERE user_id = ? AND newfeed_comment_id = ?";
        }

        $mediaQuery = $ci->db->query($mediaSQL, array($userId, $newFeedCommentId));

        if($mediaQuery->num_rows() > 0) {

            $mediaResults = $mediaQuery->result();

            foreach( $mediaResults as $item ) {

                if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $item->source)) {
                    @unlink($item->source);
                    @unlink($item->photo_thumb);
                } else {
                    $this->config->load('s3');
                    $this->load->library('s3', array(
                        "access_key"    => $this->config->item('access_key'),
                        "secret_key"    => $this->config->item('secret_key'),
                        "use_ssl"       => false,
                        "verify_peer"   => true
                    ));
                    $this->s3->deleteObject($this->config->item('s3-bucket'), $item->source);
                    $this->s3->deleteObject($this->config->item('s3-bucket'), $item->photo_thumb);
                }
            }
        }

        if($strMedia != 'all') {
            $query = "DELETE FROM user_media WHERE user_id = ? AND newfeed_comment_id = ? AND id IN $strMedia";
        } else {
            $query = "DELETE FROM user_media WHERE user_id = ? AND newfeed_comment_id = ?";
        }


        $ci->db->query($query, array($userId, $newFeedCommentId));
    }
}

/**
 * @param $fullContent
 * @return string
 * @description: Check the input content and give the short content if it is available
 */
function giveShortContent( $fullContent = '', $newLineChar = "\n" ) {
    $shortContent = "";

        if (mb_strlen($fullContent) <= 200) {

            $counting       = substr_count($fullContent, $newLineChar);

            $arrContent = array();

            if( $counting > 7 ) {
                $arrContent = explode($newLineChar, $fullContent);

                $shortContent =  implode($newLineChar, array_slice($arrContent, 0, 6));

                $shortContent   = $shortContent."...Continue Reading";
            }

        } else {
            //$shortContent   = character_limiter(strip_tags(add_break_link($fullContent), $newLineChar), 200);
            $shortContent   = mb_substr($fullContent, 0, 200, mb_detect_encoding($fullContent));

            $counting       = substr_count($shortContent, $newLineChar);

            $arrContent = array();

            if( $counting > 7 ) {
                $arrContent = explode($newLineChar, $shortContent);

                $shortContent =  implode($newLineChar, array_slice($arrContent, 0, 6));
            }
            $shortContent   = $shortContent."...Continue Reading";
        }
        return $shortContent;
}

function getUserOptions($userId) {
    $ci = &get_instance();
    $ci->load->model('member_model');
    return $ci->member_model->getUserOptions($userId);
}