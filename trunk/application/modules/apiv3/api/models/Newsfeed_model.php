<?php
class Newsfeed_model extends CI_Model {

    protected $newsfeedMedia = array();
    protected $newsfeedData = array();

    function __construct(){
        // Call the Model constructor
        parent::__construct();
        $this->load->model('media_model');
    }

    public function getNewsFeedMe($option = 'all', $userId, $limit, $start) {

        $userId = (int) $userId;

        $sql = "
            SELECT * FROM (

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, checkin.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN user_checkins checkin ON ua.checkin_id = checkin.id AND ua.newsFeedType = 'ADD_CHECKIN'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_CHECKIN') AND ua.user_id = $userId
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, discuss.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN pet_talk_topics discuss ON discuss.id = ua.topic_id AND ua.newsFeedType = 'ADD_PET_TOPIC'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PET_TOPIC') AND ua.user_id = $userId
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, review.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN user_reviews review ON review.id = ua.review_id AND ua.newsFeedType = 'ADD_REVIEW'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_REVIEW') AND ua.user_id = $userId
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, post.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN user_post_updated post ON post.id = ua.post_update_id AND ua.newsFeedType = 'ADD_POST_UPDATED'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_POST_UPDATED') AND ua.user_id = $userId
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, album.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN listing_album_photo album ON album.id = ua.photo_listing_id AND ua.newsFeedType = 'ADD_PHOTO_LISTING'
            INNER JOIN user_media media ON media.newfeed_id = ua.id AND media.status = 1
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PHOTO_LISTING') AND ua.user_id = $userId
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, sharing.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN user_photo_sharing sharing ON sharing.id = ua.photo_sharing_id AND ua.newsFeedType = 'ADD_SHARING_PHOTO'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_SHARING_PHOTO') AND ua.user_id = $userId
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, info.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN pet_talk_info info ON info.id = ua.pettalk_info_id AND (ua.newsFeedType = 'ADD_PETTALK_ADOPTION' OR ua.newsFeedType = 'ADD_PETTALK_LOST_REPORT' OR ua.newsFeedType = 'ADD_PETTALK_FOUND_REPORT' )
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PETTALK_ADOPTION' OR ua.newsFeedType = 'ADD_PETTALK_LOST_REPORT' OR ua.newsFeedType = 'ADD_PETTALK_FOUND_REPORT') AND ua.user_id = $userId
            GROUP BY newsFeedId

            ) AS newsFeed
            ORDER BY newsFeedId DESC
        ";

        if($option == 'all') {

            $sql .= " LIMIT $start, $limit";

            $query = $this->db->query($sql);

            $this->newsfeedData = $query->num_rows() > 0 ? $query->result() : array();

            return $this;

        } else {

            $query = $this->db->query($sql);

            return $query->num_rows();
        }
    }

    /**
     * @param string $option
     * @param $userId
     * @param $limit
     * @param $start
     * @return $this
     * @description: This method for getting newsfeed items in case logged in user
     * view newsfeed item of other user who is not friend
     */
    public function getNewsFeedMeFilter($option = 'all', $userId, $userLogedId, $limit, $start) {

        $userId = (int) $userId;

        $distanceLimit  = NEWSFEED_DISTANCE_LIMIT;

        $arrUserLocation = getUserOptions($userLogedId);

        if( !isset($arrUserLocation[LATITUDE]) || !isset($arrUserLocation[LONGITUDE])
            || empty($arrUserLocation[LATITUDE]) || empty($arrUserLocation[LONGITUDE]) ) {
            $arrUserLocation = array(LATITUDE => 0, LONGITUDE => 0);
        }
        $userLat = $arrUserLocation[LATITUDE];
        $userLong = $arrUserLocation[LONGITUDE];

        $sql = "
            SELECT * FROM (

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, checkin.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM (
              SELECT user_checkins.id, ( 6371 * acos( cos( radians($userLat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($userLong) ) + sin( radians($userLat) ) * sin( radians( latitude ) ) ) )  AS distance
              FROM user_checkins
              INNER JOIN business_items ON business_items.id = user_checkins.business_id
              WHERE user_checkins.user_id = $userId
              GROUP BY user_checkins.id
              HAVING distance <= $distanceLimit
            ) AS checkin
            INNER JOIN user_newsfeed_activities ua ON ua.checkin_id = checkin.id AND ua.newsFeedType = 'ADD_CHECKIN'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_CHECKIN')
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, discuss.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN pet_talk_topics discuss ON discuss.id = ua.topic_id AND ua.newsFeedType = 'ADD_PET_TOPIC'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PET_TOPIC') AND ua.user_id = $userId
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, review.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM (
              SELECT user_reviews.id, ( 6371 * acos( cos( radians($userLat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($userLong) ) + sin( radians($userLat) ) * sin( radians( latitude ) ) ) )  AS distance
              FROM user_reviews
              INNER JOIN business_items ON business_items.id = user_reviews.business_id
              WHERE user_reviews.user_id = $userId
              GROUP BY user_reviews.id
              HAVING distance <= $distanceLimit
            ) AS review
            INNER JOIN user_newsfeed_activities ua ON review.id = ua.review_id AND ua.newsFeedType = 'ADD_REVIEW'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_REVIEW')
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, post.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN user_post_updated post ON post.id = ua.post_update_id AND ua.newsFeedType = 'ADD_POST_UPDATED'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_POST_UPDATED') AND ua.user_id = $userId
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, album.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM (
              SELECT listing_album_photo.id, ( 6371 * acos( cos( radians($userLat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($userLong) ) + sin( radians($userLat) ) * sin( radians( latitude ) ) ) )  AS distance
              FROM listing_album_photo
              INNER JOIN business_items ON listing_album_photo.business_id = business_items.id
              GROUP BY listing_album_photo.id
              HAVING distance <= $distanceLimit
            ) AS album
            INNER JOIN user_newsfeed_activities ua ON album.id = ua.photo_listing_id AND ua.newsFeedType = 'ADD_PHOTO_LISTING'
            INNER JOIN user_media media ON media.newfeed_id = ua.id AND media.status = 1
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PHOTO_LISTING') AND ua.user_id = $userId
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, sharing.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN user_photo_sharing sharing ON sharing.id = ua.photo_sharing_id AND ua.newsFeedType = 'ADD_SHARING_PHOTO'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_SHARING_PHOTO') AND ua.user_id = $userId
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, info.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM (
              SELECT id, user_id, ( 6371 * acos( cos( radians($userLat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians($userLong) ) + sin( radians($userLat) ) * sin( radians( lat ) ) ) )  AS distance
              FROM pet_talk_info
              WHERE user_id = $userId
              GROUP BY id
              HAVING distance <= $distanceLimit
            ) AS info
            INNER JOIN user_newsfeed_activities ua ON info.id = ua.pettalk_info_id AND (ua.newsFeedType = 'ADD_PETTALK_ADOPTION' OR ua.newsFeedType = 'ADD_PETTALK_LOST_REPORT' OR ua.newsFeedType = 'ADD_PETTALK_FOUND_REPORT' )
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PETTALK_ADOPTION' OR ua.newsFeedType = 'ADD_PETTALK_LOST_REPORT' OR ua.newsFeedType = 'ADD_PETTALK_FOUND_REPORT')
            GROUP BY newsFeedId

            ) AS newsFeed
            ORDER BY newsFeedId DESC
        ";

        if($option == 'all') {

            $sql .= " LIMIT $start, $limit";

            $query = $this->db->query($sql);

            $this->newsfeedData = $query->num_rows() > 0 ? $query->result() : array();

            return $this;

        } else {

            $query = $this->db->query($sql);

            return $query->num_rows();
        }
    }

    /**
     * @param string $option
     * @param $userLogedId
     * @param $limit
     * @param $start
     * @return $this|array
     * @description: Get the newsfeed home
     * @notes:
     * - To filter ALL newsfeeds items based on distance we can use:
     *      HAVING distance <= $distanceLimit
     * - To filter the newsfeeds items based on distance but does not apply on the item that belong to logged in user we can use:
     *      HAVING distance <= $distanceLimit OR user_id = $userLogedId
     */
    public function getNewsFeedHome( $option = 'all', $userLogedId, $limit, $start ) {

        $userLogedId    = (int) $userLogedId;

        $limit          = (int) $limit;

        $start          = (int) $start;

        $distanceLimit  = NEWSFEED_DISTANCE_LIMIT;

        $arrUserLocation = getUserOptions($userLogedId);

        if( !isset($arrUserLocation[LATITUDE]) || !isset($arrUserLocation[LONGITUDE])
            || empty($arrUserLocation[LATITUDE]) || empty($arrUserLocation[LONGITUDE]) ) {
            $arrUserLocation = array(LATITUDE => 0, LONGITUDE => 0);
        }
        $userLat = $arrUserLocation[LATITUDE];
        $userLong = $arrUserLocation[LONGITUDE];

        $sql = "
            SELECT * FROM (

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, checkin.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM (
              SELECT user_checkins.id, ( 6371 * acos( cos( radians($userLat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($userLong) ) + sin( radians($userLat) ) * sin( radians( latitude ) ) ) )  AS distance
              FROM user_checkins
              INNER JOIN business_items ON business_items.id = user_checkins.business_id
              GROUP BY user_checkins.id
              HAVING distance <= $distanceLimit
            ) AS checkin
            INNER JOIN user_newsfeed_activities ua ON ua.checkin_id = checkin.id AND ua.newsFeedType = 'ADD_CHECKIN'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_CHECKIN')
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, discuss.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN pet_talk_topics discuss ON discuss.id = ua.topic_id AND ua.newsFeedType = 'ADD_PET_TOPIC'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PET_TOPIC')
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, review.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM (
              SELECT user_reviews.id, ( 6371 * acos( cos( radians($userLat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($userLong) ) + sin( radians($userLat) ) * sin( radians( latitude ) ) ) )  AS distance
              FROM user_reviews
              INNER JOIN business_items ON business_items.id = user_reviews.business_id
              GROUP BY user_reviews.id
              HAVING distance <= $distanceLimit
            ) AS review
            INNER JOIN user_newsfeed_activities ua ON review.id = ua.review_id AND ua.newsFeedType = 'ADD_REVIEW'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_REVIEW')
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, post.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            LEFT JOIN user_post_updated post ON post.id = ua.post_update_id AND ua.newsFeedType = 'ADD_POST_UPDATED'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_POST_UPDATED')
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, album.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM (
              SELECT listing_album_photo.id, ( 6371 * acos( cos( radians($userLat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($userLong) ) + sin( radians($userLat) ) * sin( radians( latitude ) ) ) )  AS distance
              FROM listing_album_photo
              INNER JOIN business_items ON listing_album_photo.business_id = business_items.id
              GROUP BY listing_album_photo.id
              HAVING distance <= $distanceLimit
            ) AS album
            INNER JOIN user_newsfeed_activities ua ON album.id = ua.photo_listing_id AND ua.newsFeedType = 'ADD_PHOTO_LISTING'
            INNER JOIN user_media media ON media.newfeed_id = ua.id AND media.status = 1
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PHOTO_LISTING')
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, sharing.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType
            FROM user_newsfeed_activities ua
            INNER JOIN user_photo_sharing sharing ON sharing.id = ua.photo_sharing_id AND ua.newsFeedType = 'ADD_SHARING_PHOTO'
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_SHARING_PHOTO')
            GROUP BY newsFeedId

            UNION

            SELECT ua.user_id, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb, ua.created_date, info.id AS newsFeedItemId, ua.id AS newsFeedId, newsFeedType FROM(
              SELECT id, user_id, ( 6371 * acos( cos( radians($userLat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians($userLong) ) + sin( radians($userLat) ) * sin( radians( lat ) ) ) )  AS distance
              FROM pet_talk_info
              GROUP BY id
              HAVING distance <= $distanceLimit
            ) AS info
            INNER JOIN user_newsfeed_activities ua ON info.id = ua.pettalk_info_id AND (ua.newsFeedType = 'ADD_PETTALK_ADOPTION' OR ua.newsFeedType = 'ADD_PETTALK_LOST_REPORT' OR ua.newsFeedType = 'ADD_PETTALK_FOUND_REPORT' )
            INNER JOIN users u ON u.id = ua.user_id
            WHERE (ua.newsFeedType = 'ADD_PETTALK_ADOPTION' OR ua.newsFeedType = 'ADD_PETTALK_LOST_REPORT' OR ua.newsFeedType = 'ADD_PETTALK_FOUND_REPORT')
            GROUP BY newsFeedId

            ) AS newsFeed
            ORDER BY newsFeedId DESC
        ";

        if($option == 'all') {

            $sql .= " LIMIT $start, $limit";

            $query = $this->db->query($sql);

            $this->newsfeedData = $query->num_rows() > 0 ? $query->result() : array();
            return $this;
            //return $this->processingNewsFeed( $query, $userLogedId );

        } else {
            $query = $this->db->query($sql);

            return $query->num_rows();
        }
    }

    public function getNewsfeedListingTransformer($userLogedId) {

        $arrFinalData = array();

        if( count($this->newsfeedData) ) {

            for ( $i = 0; $i < count($this->newsfeedData); $i++ ) {
                $result = $this->newsfeedData[$i];

                format_output_data($result);

                $arrFinalData[$i][NEWSFEED_TYPE]        = $result->newsFeedType;

                $arrFinalData[$i][NEWSFEED_ID]          = $result->newsFeedId;

                $arrFinalData[$i][NEWSFEED_ITEM_ID]     = $result->newsFeedItemId;

                $arrFinalData[$i][TOTAL_LIKES]          = getNewsfeedTotalLike($result->newsFeedItemId, $result->newsFeedType);

                $arrFinalData[$i][TOTAL_COMMENTS]       = getNewsfeedTotalReview($result->newsFeedItemId, $result->newsFeedType);

                $arrFinalData[$i][HAS_LIKED]            = hasLikedNewsfeed( $result->newsFeedItemId, $userLogedId, $result->newsFeedType );

                $arrFinalData[$i][TOTAL_SHARING]        = getNewsfeedTotalSharing( $result->newsFeedItemId, $result->newsFeedType );

                $arrFinalData[$i][USER_TAG]              = $this->usertag_model->getUserTag( $result->newsFeedId, NEWSFEED_USER_TAG )->transformer();

                $arrFinalData[$i][CREATED_TIME]         = $result->created_time;

                $arrFinalData[$i][CREATED_DATE]         = $result->created_date;

                // GET USER INFO
                $arrFinalData[$i][USER_INFO] = array(
                    ID            => (int)$result->user_id,
                    FIRST_NAME    => $result->first_name,
                    LAST_NAME     => $result->last_name,
                    PROFILE_PHOTOS => array(
                        $result->profile_photo, $result->profile_photo_thumb
                    ),
                    TOTAL_FRIEND   => getTotalUserFriends($result->user_id),
                    TOTAL_PHOTO    => getTotalUserListingPhotos($result->user_id),
                    TOTAL_REVIEW   => getTotalUserReviews($result->user_id),
                );

                if( $result->newsFeedType == ADD_PET_TOPIC ) {

                    $topicInfo          = $this->pettalk_model->getPettalkDetail($result->newsFeedItemId)->pettalkTopicTransformer();
                    $infoPhoto          = $this->getNewsfeedMedia($result->newsFeedId)->getNewsfeedMediaTransformer();
                    $topicInfo[PHOTOS]  = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    $arrFinalData[$i][TOPIC_INFO] = $topicInfo;

                } elseif( $result->newsFeedType == ADD_CHECKIN ) {

                    $checkinInfo = $this->checkin_model->item($result->newsFeedItemId)->itemTransformer($result->newsFeedItemId);
                    $arrFinalData[$i][CHECKIN_INFO] = $checkinInfo[CHECKIN_INFO];
                    $arrFinalData[$i][LISTING_INFO] = $checkinInfo[LISTING_INFO];

                } elseif($result->newsFeedType == ADD_POST_UPDATED) {

                    $postInfo           = $this->postupdated_model->item($result->newsFeedItemId)->itemTransformer();
                    $infoPhoto          = $this->getNewsfeedMedia($result->newsFeedId)->getNewsfeedMediaTransformer();
                    $postInfo[PHOTOS]   = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    $arrFinalData[$i][POST_UPDATED_INFO] = $postInfo;

                } elseif($result->newsFeedType == ADD_REVIEW) {

                    $reviewInfo                     = $this->review_model->item($result->newsFeedItemId)->itemTransformer();
                    $infoPhoto                      = $this->getNewsfeedMedia($result->newsFeedId)->getNewsfeedMediaTransformer();
                    $reviewInfo[REVIEW_INFO][PHOTOS] = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    $arrFinalData[$i][REVIEW_INFO]  = $reviewInfo[REVIEW_INFO];
                    $arrFinalData[$i][LISTING_INFO] = $reviewInfo[LISTING_INFO];

                } elseif($result->newsFeedType == ADD_PHOTO_LISTING) {

                    $listingPhoto                   = $this->listing_model->listingPhoto( $result->newsFeedId, $result->newsFeedItemId)->listingPhotoTransformer();
                    $infoPhoto                      = $this->getNewsfeedMedia($result->newsFeedId)->getNewsfeedMediaTransformer();
                    $listingPhoto[PHOTOS]           = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    $arrFinalData[$i][LISTING_INFO] = $listingPhoto;

                } elseif($result->newsFeedType == ADD_SHARING_PHOTO) {

                    $infoPhoto                              = $this->getNewsfeedMedia($result->newsFeedId)->getNewsfeedMediaTransformer();
                    $arrFinalData[$i][SHARING_PHOTO_INFO]   = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();

                } elseif($result->newsFeedType == ADD_PETTALK_ADOPTION || $result->newsFeedType == ADD_PETTALK_FOUND_REPORT || $result->newsFeedType == ADD_PETTALK_LOST_REPORT) {

                    $pettalkInfo                    = $this->pettalkinfo_model->item( $result->newsFeedId, $result->newsFeedItemId, $result->user_id)->pettalkInfoTransformer();
                    $infoPhoto                      = $this->getNewsfeedMedia($result->newsFeedId)->getNewsfeedMediaTransformer();
                    $pettalkInfo[PHOTOS]            = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    $pettalkInfo[COVER_PHOTO]       = isset($infoPhoto[COVER_PHOTO]) ? $infoPhoto[COVER_PHOTO] : new stdClass();
                    $arrFinalData[$i][PETTALK_INFO] = $pettalkInfo;
                }
            }
        }

        return $arrFinalData;
    }


    public function detail( $newsFeedId, $userId , $reviewId = false) {      
        if($reviewId){
          $sql = "SELECT n.*, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb
          FROM user_newsfeed_activities n
          INNER JOIN users u ON u.id = n.user_id
          WHERE n.review_id = ? GROUP BY n.id";
          $query = $this->db->query($sql, array("review_id" => $reviewId));
        }
        else {
          $sql = "SELECT n.*, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb
          FROM user_newsfeed_activities n
          INNER JOIN users u ON u.id = n.user_id
          WHERE n.id = ? GROUP BY n.id";
          $query = $this->db->query($sql, array("id" => $newsFeedId));
        }
        $arrData = array();

        if( $query->num_rows() > 0 ) {

            $row = $query->row();

            format_output_data($row);

            $arrData[NEWSFEED_TYPE]   = $row->newsFeedType;
            $arrData[NEWSFEED_ID]     = $newsFeedId;
            $arrData[CREATED_TIME]    = $row->created_time;
            $arrData[CREATED_DATE]    = $row->created_date;
            $arrData[USER_TAG]        = $this->usertag_model->getUserTag( $newsFeedId, NEWSFEED_USER_TAG )->transformer();

            switch( $row->newsFeedType ) {

                case ADD_PETTALK_ADOPTION:
                case ADD_PETTALK_FOUND_REPORT:
                case ADD_PETTALK_LOST_REPORT:
                    $arrData["newsFeedItemId"]  = $row->pettalk_info_id;
                    $arrData["totalLikes"]      = getNewsfeedTotalLike($row->pettalk_info_id, $row->newsFeedType);
                    $arrData["totalComments"]   = getNewsfeedTotalReview($row->pettalk_info_id, $row->newsFeedType);;
                    $arrData["hasLiked"]        = hasLikedNewsfeed( $row->pettalk_info_id, $userId, $row->newsFeedType );
                    $arrData["totalSharing"]    = getNewsfeedTotalSharing( $row->pettalk_info_id, $row->newsFeedType );
                    //$arrData["pettalkInfo"]     = $this->getPettalkInfo( $row->id, $row->pettalk_info_id, $row->user_id, $row->first_name, $row->last_name, $row->profile_photo, $row->profile_photo_thumb );
                    $pettalkInfo                = $this->pettalkinfo_model->item( $newsFeedId, $row->pettalk_info_id, $row->user_id)->pettalkInfoTransformer();
                    $infoPhoto                  = $this->getNewsfeedMedia($newsFeedId)->getNewsfeedMediaTransformer();
                    $pettalkInfo[PHOTOS]        = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    $pettalkInfo[COVER_PHOTO]   = isset($infoPhoto[COVER_PHOTO]) ? $infoPhoto[COVER_PHOTO] : new stdClass();
                    $arrData[PETTALK_INFO]      = $pettalkInfo;
                    break;
                case ADD_CHECKIN:
                    $arrData["newsFeedItemId"]  = $row->checkin_id;
                    $arrData["totalLikes"]      = getNewsfeedTotalLike($row->checkin_id, $row->newsFeedType);
                    $arrData["totalComments"]   = getNewsfeedTotalReview($row->checkin_id, $row->newsFeedType);;
                    $arrData["hasLiked"]        = hasLikedNewsfeed( $row->checkin_id, $userId, $row->newsFeedType );
                    $arrData["totalSharing"]    = getNewsfeedTotalSharing( $row->checkin_id, $row->newsFeedType );
                    //$checkinInfo                = $this->getUserCheckInfo($row->checkin_id);
                    $checkinInfo                = $this->checkin_model->item($row->checkin_id)->itemTransformer($row->checkin_id);
                    $arrData[CHECKIN_INFO]      = $checkinInfo[CHECKIN_INFO];
                    $arrData[LISTING_INFO]      = $checkinInfo[LISTING_INFO];
                    break;
                case ADD_PET_TOPIC:
                    $arrData["newsFeedItemId"]  = $row->topic_id;
                    $arrData["totalLikes"]      = getNewsfeedTotalLike($row->topic_id, $row->newsFeedType);
                    $arrData["totalComments"]   = getNewsfeedTotalReview($row->topic_id, $row->newsFeedType);;
                    $arrData["hasLiked"]        = hasLikedNewsfeed( $row->topic_id, $userId, $row->newsFeedType );
                    $arrData["totalSharing"]    = getNewsfeedTotalSharing( $row->topic_id, $row->newsFeedType );
                    //$arrData["topicInfo"]       = $this->getUserDiscussionInfo($row->topic_id);
                    $infoPhoto                  = $this->getNewsfeedMedia($newsFeedId)->getNewsfeedMediaTransformer();
                    $topicInfo                  = $this->pettalk_model->getPettalkDetail($row->topic_id)->pettalkTopicTransformer();
                    $topicInfo[PHOTOS]          = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    $arrData[TOPIC_INFO]        = $topicInfo;
                    break;
                case ADD_REVIEW:
                    $arrData["newsFeedItemId"]  = $row->review_id;
                    $arrData["totalLikes"]      = getNewsfeedTotalLike($row->review_id, $row->newsFeedType);
                    $arrData["totalComments"]   = getNewsfeedTotalReview($row->review_id, $row->newsFeedType);;
                    $arrData["hasLiked"]        = hasLikedNewsfeed( $row->review_id, $userId, $row->newsFeedType );
                    $arrData["totalSharing"]    = getNewsfeedTotalSharing( $row->review_id, $row->newsFeedType );
                    //$arrReviewInfo              = $this->getUserWroteReviewInfo($row->review_id);
                    $reviewInfo                 = $this->review_model->item($row->review_id)->itemTransformer();
                    $infoPhoto                  = $this->getNewsfeedMedia($newsFeedId)->getNewsfeedMediaTransformer();
                    $reviewInfo[REVIEW_INFO][PHOTOS] = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    $arrData[REVIEW_INFO]       = $reviewInfo[REVIEW_INFO];
                    $arrData[LISTING_INFO]      = $reviewInfo[LISTING_INFO];
                    break;
                case ADD_POST_UPDATED:
                    $arrData["newsFeedItemId"]  = $row->post_update_id;
                    $arrData["totalLikes"]      = getNewsfeedTotalLike($row->post_update_id, $row->newsFeedType);
                    $arrData["totalComments"]   = getNewsfeedTotalReview($row->post_update_id, $row->newsFeedType);;
                    $arrData["hasLiked"]        = hasLikedNewsfeed( $row->post_update_id, $userId, $row->newsFeedType );
                    $arrData["totalSharing"]    = getNewsfeedTotalSharing( $row->post_update_id, $row->newsFeedType );
                    $postInfo                   = $this->postupdated_model->item($row->post_update_id)->itemTransformer();
                    $infoPhoto                  = $this->getNewsfeedMedia($newsFeedId)->getNewsfeedMediaTransformer();
                    $postInfo[PHOTOS]           = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    $arrData[POST_UPDATED_INFO] = $postInfo;
                    //$arrData["postUpdatedInfo"] = $this->getUserPostUpdatedInfo($row->post_update_id);
                    break;
                case ADD_PHOTO_LISTING:
                    $arrData["newsFeedItemId"]  = $row->photo_listing_id;
                    $arrData["totalLikes"]      = getNewsfeedTotalLike($row->photo_listing_id, $row->newsFeedType);
                    $arrData["totalComments"]   = getNewsfeedTotalReview($row->photo_listing_id, $row->newsFeedType);;
                    $arrData["hasLiked"]        = hasLikedNewsfeed( $row->photo_listing_id, $userId, $row->newsFeedType );
                    $arrData["totalSharing"]    = getNewsfeedTotalSharing( $row->photo_listing_id, $row->newsFeedType );
                    //$arrData["listingInfo"]     = $this->getPhotoListingInfo( $row->id, $row->photo_listing_id);
                    $infoPhoto                  = $this->getNewsfeedMedia($newsFeedId)->getNewsfeedMediaTransformer();
                    $listingPhoto               = $this->listing_model->listingPhoto( $newsFeedId, $row->photo_listing_id)->listingPhotoTransformer();
                    $listingPhoto[PHOTOS]       = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    $arrData[LISTING_INFO]      = $listingPhoto;
                    break;
                case ADD_SHARING_PHOTO:
                    $arrData["newsFeedItemId"]  = $row->photo_sharing_id;
                    $arrData["totalLikes"]      = getNewsfeedTotalLike($row->photo_sharing_id, $row->newsFeedType);
                    $arrData["totalComments"]   = getNewsfeedTotalReview($row->photo_sharing_id, $row->newsFeedType);;
                    $arrData["hasLiked"]        = hasLikedNewsfeed( $row->photo_sharing_id, $userId, $row->newsFeedType );
                    $arrData["totalSharing"]    = getNewsfeedTotalSharing( $row->photo_sharing_id, $row->newsFeedType );
                    //$arrData["sharingPhotoInfo"]= $this->getUserSharingPhotoInfo( $row->id, $row->photo_sharing_id, $row->user_id, $row->first_name, $row->last_name, $row->profile_photo, $row->profile_photo_thumb );
                    $infoPhoto                  = $this->getNewsfeedMedia($newsFeedId)->getNewsfeedMediaTransformer();
                    $arrData[SHARING_PHOTO_INFO] = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                    break;
            }

            $arrData["userInfo"] = array(
                "id"            => (int)$row->userId,
                "first_name"    => $row->first_name,
                "last_name"     => $row->last_name,
                "profilePhotos" => array(
                    $row->profile_photo, $row->profile_photo_thumb
                ),
                "totalFriend"   => getTotalUserFriends($row->userId),
                "totalPhoto"    => getTotalUserListingPhotos($row->userId),
                "totalReviews"  => getTotalUserReviews($row->userId),
            );
        }
        return $arrData;
    }

    public function getUserDiscussionInfo($newsFeedId) {

        $sqlDiscuss   = "SELECT discuss.id, discuss.title, discuss.content, talkCat.id as catId, talkCat.name
        FROM pet_talk_topics discuss
        LEFT JOIN pet_talk_category talkCat ON talkCat.id = discuss.category_id
        WHERE discuss.id = ?
        GROUP BY discuss.id
        ";

        $discussQuery = $this->db->query($sqlDiscuss, array($newsFeedId));

        if( $discussQuery->num_rows() > 0 ) {

            $discussResult = $discussQuery->row();

            $fullContent = $discussResult->content;

            $shortContent = "";

            if (mb_strlen($fullContent) <= 200) {

                $counting       = substr_count($fullContent, "<br/>");

                $arrContent = array();

                if( $counting > 7 ) {
                    $arrContent = explode("<br/>", $fullContent);

                    $shortContent =  implode("<br/>", array_slice($arrContent, 0, 6));

                    $shortContent   = $shortContent."...Continue Reading";
                }

                $fullContent    = strip_tags(add_break_link($fullContent), '<br/>');

            } else {

                $shortContent   = character_limiter(strip_tags(add_break_link($fullContent), '<br/>'), 200);

                $shortContent   = str_replace(array("<p>", "</p>"), array("", ""), $shortContent);

                $counting       = substr_count($shortContent, "<br/>");

                $arrContent = array();

                if( $counting > 7 ) {
                    $arrContent = explode("<br/>", $shortContent);

                    $shortContent =  implode("<br/>", array_slice($arrContent, 0, 6));
                }

                $shortContent   = $shortContent."...Continue Reading";
            }


            $arrTopic = array(
                "id"            => $discussResult->id,
                "title"         => $discussResult->title,
                "shortContent"  => $shortContent,
                "fullContent"   => $fullContent,
                "category"      => $discussResult->name,
                "categoryId"    => $discussResult->catId,
                "totalLikes"    => 0,
                "totalComments" => 0,
                "photos"        => array(),
            );

            $arrMedia = $this->getUserPhotosInfo($newsFeedId);

            $arrTopic["photos"] = $arrMedia;

            return $arrTopic;
        } else {
            return array();
        }
    }

    public function getUserPhotosInfo($newsFeedId) {

        $sqlMedia   = "SELECT m.id, m.source, m.photo_thumb, m.width_thumb, m.height_thumb, m.width_source, m.height_source, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb
        FROM user_media m
        INNER JOIN users u ON u.id = m.user_id
        WHERE m.topic_id = ?
        ";

        $mediaQuery = $this->db->query($sqlMedia, array($newsFeedId));

        if($mediaQuery->num_rows() > 0) {

            $mediaResults = $mediaQuery->result();

            //$result->photos = array(format_output_data($mediaResults[0]));

            $arrMedia = array();

            for ( $j = 0; $j < count($mediaResults); $j++) {

                $media = $mediaResults[$j];

                format_output_data($media);

                $arrMedia[$j]["photos"] = array(
                    ID            => $media->id,
                    SOURCE        => $media->source,
                    PHOTO_THUMB   => $media->photo_thumb,
                    THUMB_WIDTH   => $media->width_thumb,
                    THUMB_HEIGHT  => $media->height_thumb,
                    PHOTO_WIDTH   => $media->width_source,
                    PHOTO_HEIGHT  => $media->height_source,

                );

                $arrMedia[$j]["userInfo"]["first_name"]     = $media->first_name;
                $arrMedia[$j]["userInfo"]["last_name"]      = $media->last_name;
                $arrMedia[$j]["userInfo"]["profilePhotos"]  = array($media->profile_photo, $media->profile_photo_thumb);
                $arrMedia[$j]["userInfo"]["id"]             = $media->userId;
                $arrMedia[$j]["userInfo"]["totalFriend"]    = getTotalUserFriends($media->userId);
                $arrMedia[$j]["userInfo"]["totalPhoto"]     = getTotalUserListingPhotos($media->userId);
                $arrMedia[$j]["userInfo"]["totalReviews"]   = getTotalUserReviews($media->userId);
            }
            return $arrMedia;
        } else {
            return array();
        }
    }

    public function getUserCheckInfo($newFeedId) {

        $sqlCheckin   = "SELECT bi.id AS businessId, bi.name, bi.address, bi.country, bi.latitude, bi.longitude, bi.photo, checkin.comment, checkin.id AS checkinId, cat.name AS catName
        FROM business_items bi
        LEFT JOIN user_checkins checkin ON bi.id = checkin.business_id
        INNER JOIN business_items_category biCat ON biCat.business_id = bi.id
        INNER JOIN business_category cat ON cat.id = biCat.business_category_id
        WHERE checkin.id = ?
        GROUP BY checkin.id
        ";

        $checkinQuery = $this->db->query($sqlCheckin, array($newFeedId));

        if($checkinQuery->num_rows() > 0) {

            $checkInResult = $checkinQuery->row();

            $arrCheckin = array(
                "id"            => $checkInResult->checkinId,
                "photos"        => array(),
                "visit"         => "",
                "checkInText"   => !empty($checkInResult->comment) ? $checkInResult->comment : "",
            );

            $convertObj = format_output_data($checkInResult);

            $arrListingInfo = array(
                "id"        => $checkInResult->businessId,
                "latitude"  => $checkInResult->latitude,
                "longitude" => $checkInResult->longitude,
                "country"   => $checkInResult->country,
                "address"   => $checkInResult->address,
                "name"      => $checkInResult->name,
                "website"   => !empty($checkInResult->website) ? $checkInResult->website : "",
                "category"  => $checkInResult->catName,
                "photo"     => $convertObj->photo,
                "totalReview" => $this->getTotalReviews($newFeedId),
                "totalRating" => $this->getTotalRating($newFeedId),
                "visit"     => getTotalVisit((int)$newFeedId)
            );

            return array(
                "checkinInfo"   => $arrCheckin,
                "listingInfo"   => $arrListingInfo,
            );

        } else {
            return array(
                "checkinInfo" => array(),
                "listingInfo" => array(),

            );
        }
    }

    public function getUserWroteReviewInfo($newsFeedId) {

        $sqlReview = "SELECT bi.id AS businessId, bi.name, bi.address, bi.country, bi.latitude, bi.longitude, bi.photo, review.id AS reviewId, review.content, review.rate, cat.name AS catName
        FROM user_reviews review
        LEFT JOIN business_items bi ON bi.id = review.business_id
        INNER JOIN business_items_category biCat ON biCat.business_id = bi.id
        INNER JOIN business_category cat ON cat.id = biCat.business_category_id
        WHERE review.id = ?
        GROUP BY review.id";

        $reviewQuery = $this->db->query($sqlReview, array($newsFeedId));

        if($reviewQuery->num_rows() > 0) {

            $reviewResult = $reviewQuery->row();

            $convertObj = format_output_data($reviewResult);

            $arrListingInfo = array(
                "id"        => $reviewResult->businessId,
                "latitude"  => $reviewResult->latitude,
                "longitude" => $reviewResult->longitude,
                "country"   => $reviewResult->country,
                "address"   => $reviewResult->address,
                "name"      => $reviewResult->name,
                "website"   => !empty($reviewResult->website) ? $reviewResult->website : "",
                "category"  => $reviewResult->catName,
                "photo"     => $convertObj->photo,
                "totalReview" => $this->getTotalReviews($reviewResult->businessId),
                "totalRating" => $this->getTotalRating($reviewResult->businessId),
                "visit"     => getTotalVisit((int)$reviewResult->businessId),
            );

            $reviewPhoto = array();

            $sqlReviewPhoto = "SELECT media.id, media.source, media.photo_thumb, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb
            FROM user_media media
            LEFT JOIN users u ON u.id = media.user_id
            WHERE media.review_id = ?";

            $query = $this->db->query($sqlReviewPhoto, array($reviewResult->reviewId));

            if($query->num_rows() > 0) {

                $reviewPhotoResults = $query->result();

                for($i = 0; $i < count($reviewPhotoResults); $i++) {

                    $item = $reviewPhotoResults[$i];

                    format_output_data($item);

                    $reviewPhoto[$i]["photos"] = array(
                        "id"            => $item->id,
                        "source"        => $item->source,
                        "photo_thumb"   => $item->photo_thumb,
                    );

                    $reviewPhoto[$i]["userInfo"] = array(
                        "id"            => $item->userId,
                        "first_name"    => $item->first_name,
                        "last_name"     => $item->last_name,
                        "profilePhotos" => array($item->profile_photo, $item->profile_photo_thumb)
                    );
                }
            }

            $fullContent = $reviewResult->content;

            $shortContent = "";

            if (mb_strlen($fullContent) <= 200) {

                $counting       = substr_count($fullContent, "\n");

                $arrContent = array();

                if( $counting > 7 ) {
                    $arrContent = explode("\n", $fullContent);

                    $shortContent =  implode("<br/>", array_slice($arrContent, 0, 6));

                    $shortContent   = $shortContent."...Continue Reading";
                }

                $fullContent    = add_break_link($fullContent);
            } else {

                $shortContent   = character_limiter(strip_tags(add_break_link($fullContent), '<br/>'), 200);

                $shortContent   = str_replace(array("<p>", "</p>"), array("", ""), $shortContent);

                $counting       = substr_count($shortContent, "\n");

                $arrContent = array();

                if( $counting > 7 ) {
                    $arrContent = explode("\n", $shortContent);

                    $shortContent =  implode("<br/>", array_slice($arrContent, 0, 6));
                }

                $shortContent   = $shortContent."...Continue Reading";

                $fullContent    = add_break_link($fullContent);
            }

            $arrReviewInfo = array(
                "id"            => $reviewResult->reviewId,
                "content"       => $fullContent,
                "shortContent"  => $shortContent,
                "rate"          => $reviewResult->rate,
                "photos"         => $reviewPhoto,
            );

            return array(
                "listingInfo"   => $arrListingInfo,
                "reviewInfo"    => $arrReviewInfo,
            );

        } else {
            return array(
                "listingInfo"   => array(),
                "reviewInfo"    => array(),
            );
        }
    }

    public function getUserPostUpdatedInfo($newsFeedItemId) {

        $sqlPost   = "SELECT p.id, p.title, p.content
        FROM user_post_updated p
        WHERE p.id = ?
        ";

        $postQuery = $this->db->query($sqlPost, array($newsFeedItemId));

        $postData = array();

        if( $postQuery->num_rows() > 0 ) {

            $postResult = $postQuery->row();

            $postData = array(
                "id"        => $postResult->id,
                "title"     => $postResult->title,
                "content"   => $postResult->content,
                "photos"    => array(),
            );

            //$postPhotos = array();

            $sqlPostPhoto = "SELECT media.id AS mediaId, media.source, media.photo_thumb, u.id AS userId, u.first_name, u.last_name, u.profile_photo, u.profile_photo_thumb
            FROM user_media media
            LEFT JOIN users u ON u.id = media.user_id
            WHERE media.post_update_id = ?";

            $mediaQuery = $this->db->query($sqlPostPhoto, array($postResult->id));

            if($mediaQuery->num_rows() > 0) {

                $postPhotoResults = $mediaQuery->result();

                for ($i = 0; $i < count($postPhotoResults); $i++) {

                    $item = $postPhotoResults[$i];

                    format_output_data($item);

                    $postData["photos"][$i]["photos"] = array(
                        "id"            => $item->mediaId,
                        "source"        => $item->source,
                        "photo_thumb"   => $item->photo_thumb,
                    );

                    $postData["photos"][$i]["userInfo"] = array(
                        "id"            => $item->userId,
                        "first_name"    => $item->first_name,
                        "last_name"     => $item->last_name,
                        "profilePhotos" => array($item->profile_photo, $item->profile_photo_thumb),
                    );
                }
            }
        }

        return $postData;
    }

    public function getPhotoListingInfo( $newFeedId, $newsFeedItemId) {

        $sqlPhotoListing = "SELECT bi.id AS businessId, bi.name, bi.address, bi.country, bi.latitude, bi.longitude, bi.photo, u.id, u.first_name, u.profile_photo, u.profile_photo_thumb, u.last_name, media.id AS mediaId, media.source, media.photo_thumb, cat.name AS catName
        FROM listing_album_photo album
        INNER JOIN business_items bi ON bi.id = album.business_id
        INNER JOIN user_media media ON media.business_id = bi.id AND media.status = 1 AND media.newfeed_id = $newFeedId
        INNER JOIN users u ON media.user_id = u.id
        INNER JOIN business_items_category biCat ON biCat.business_id = bi.id
        LEFT JOIN business_category cat ON cat.id = biCat.business_category_id
        WHERE media.newfeed_id = ? AND album.id = ?";

        $photoListingQuery = $this->db->query($sqlPhotoListing, array($newFeedId, $newsFeedItemId));

        if($photoListingQuery->num_rows() > 0) {

            $photoListingResults = $photoListingQuery->result();

            $convertObj = format_output_data($photoListingResults[0]);

            $arrListingInfo = array(
                "id"        => $photoListingResults[0]->businessId,
                "latitude"  => $photoListingResults[0]->latitude,
                "longitude" => $photoListingResults[0]->longitude,
                "country"   => $photoListingResults[0]->country,
                "address"   => $photoListingResults[0]->address,
                "name"      => $photoListingResults[0]->name,
                "website"   => !empty($photoListingResults[0]->website) ? $photoListingResults[0]->website : "",
                "category"  => $photoListingResults[0]->catName,
                "photo"     => $convertObj->photo,
                "totalReview" => $this->getTotalReviews($photoListingResults[0]->businessId),
                "totalRating" => $this->getTotalRating($photoListingResults[0]->businessId),
                "visit"     => getTotalVisit((int)$photoListingResults[0]->businessId),
                "photos"    => array(),
            );

            $arrListingPhoto = array();

            foreach( $photoListingResults as $item ) {

                //$photoUrls = format_output_data($item);

                format_output_data($item);

                $photoObj = new stdClass();
                $userObj = new stdClass();

                $photoObj->photos = array(
                    "id"        => $item->mediaId,
                    "source"    => $item->source,
                    "photo_thumb" => $item->photo_thumb,
                );

                $userObj->id           = (int)$item->id;
                $userObj->first_name   = $item->first_name;
                $userObj->last_name    = $item->last_name;
                $userObj->profilePhotos= array($item->profile_photo_thumb, $item->profile_photo);
                $userObj->totalFriend  = getTotalUserFriends($item->id);
                $userObj->totalPhoto   = getTotalUserListingPhotos($item->id);
                $userObj->totalReviews = getTotalUserReviews($item->id);

                $photoObj->userInfo = $userObj;

                $arrListingPhoto[] = $photoObj;
            }

            $arrListingInfo["photos"] = $arrListingPhoto;

            return $arrListingInfo;

        } else {
            return array();
        }
    }

    public function getUserSharingPhotoInfo($newFeedId, $newsFeedItemId, $userId, $firstName, $lastName, $profilePhoto, $profilePhotoThumb) {

        $sqlSharing   = "SELECT sharing.id, sharing.content, media.source, media.photo_thumb, media.id AS mediaId
        FROM user_photo_sharing sharing
        INNER JOIN user_newsfeed_activities ua ON ua.photo_sharing_id = sharing.id
        INNER JOIN user_media media ON media.newfeed_id = ua.id AND status = 1
        WHERE sharing.id = ? AND ua.id = ?
        GROUP BY media.id
        ";

        $sharingQuery = $this->db->query($sqlSharing, array($newsFeedItemId, $newFeedId));

        $sharingData = array();

        if( $sharingQuery->num_rows() > 0 ) {

            $sharingResults = $sharingQuery->result();

            $sharingData = array(
                "id"        => $sharingResults[0]->id,
                "content"   => $sharingResults[0]->content,
                "photos"    => array(),
            );

            for ($i = 0; $i < count($sharingResults); $i++) {

                $item = $sharingResults[$i];

                format_output_data($item);

                $sharingData["photos"][$i]["photos"] = array(
                    "id"            => $item->mediaId,
                    "source"        => $item->source,
                    "photo_thumb"   => $item->photo_thumb,
                );

                $sharingData["photos"][$i]["userInfo"] = array(
                    "id"            => $userId,
                    "first_name"    => $firstName,
                    "last_name"     => $lastName,
                    "profilePhotos" => array($profilePhoto, $profilePhotoThumb),
                );
            }
        }

        return $sharingData;
    }

    public function getPettalkInfo( $newFeedId, $newsFeedItemId, $userId, $firstName, $lastName, $profilePhoto, $profilePhotoThumb ) {

        $sql = "SELECT info.*, media.source, media.photo_thumb, media.id AS mediaId, cat.name AS catTitle
        FROM pet_talk_info info
        LEFT JOIN user_media media ON media.pettalk_info_id = info.id AND status = 1
        INNER JOIN pet_talk_category cat ON cat.id = info.catId
        WHERE info.user_id = ? AND info.id = ?
        GROUP BY media.id";

        $query = $this->db->query($sql, array("user_id" => $userId, "id" => $newsFeedItemId));

        $arrData = array();

        if( $query->num_rows() > 0 ) {

            $results = $query->result();

            format_output_data($results[0]);

            $arrData[ID]        = $results[0]->id;
            $arrData[NAME]      = $results[0]->name;
            $arrData[TYPE]      = $results[0]->type;
            $arrData[BREED]     = $results[0]->breed;
            $arrData[COLOR]     = $results[0]->color;
            $arrData[AGE]       = $results[0]->age;
            $arrData[SEX]       = $results[0]->sex;
            $arrData[CONTACT]   = $results[0]->contact;
            $arrData[LATITUDE]  = $results[0]->lat;
            $arrData[LONGITUDE] = $results[0]->lng;
            $arrData[ADDITIONAL_INFO] = $results[0]->additionalInfo;
            $arrData[COVER_PHOTO] = $results[0]->photo;
            $arrData[MICROCHIP] = $results[0]->microchip;

            //if( $results[0]->catId == PET_CAT_FOUND_REPORT || $results[0]->catId == PET_CAT_LOST_REPORT ) {
            $arrData[WHERE] = $results[0]->where;
            $arrData[WHEN] = $results[0]->when;

            //if( $results[0]->catId == PET_CAT_LOST_REPORT ) {
            $arrData[REWARD] = $results[0]->rewardCurrency;
            $arrData[CURRENCY] = $results[0]->currency;
            //}
            //}

            $arrData[CATEGORY]  = array(
                ID      => $results[0]->catId,
                TITLE   => $results[0]->catTitle
            );

            for ( $i = 0; $i < count($results); $i++ ) {

                $item = $results[$i];

                format_output_data($item);

                $arrData[PHOTOS][$i][PHOTOS] = array(
                    ID              => $item->mediaId,
                    SOURCE          => $item->source,
                    PHOTO_THUMB     => $item->photo_thumb,
                );

                $arrData[PHOTOS][$i][USER_INFO] = array(
                    "id"            => $userId,
                    "first_name"    => $firstName,
                    "last_name"     => $lastName,
                    "profilePhotos" => array($profilePhoto, $profilePhotoThumb),
                );
            }

        }
        return $arrData;
    }


    public function updateCheckinInfo( $userId, $newFeedId = null, $checkinId = null, $comment ) {

        /*$query = $this->db->get_where("user_newsfeed_activities", array("id" => $newFeedId, "user_id" => $userId));

        if($query->num_rows() > 0) {

            $newfeed = $query->row();*/

        $this->db->update("user_checkins",
            array("comment" => $comment),
            array("id" => $checkinId, "user_id" => $userId));

        /*    return $newfeed->checkin_id;
        }*/
    }

    public function updateReviewInfo( $userId, $newFeedId = null, $reviewId = null, $content, $rating ) {

        /*$query = $this->db->get_where("user_newsfeed_activities", array("id" => $newFeedId, "user_id" => $userId));

        if($query->num_rows() > 0) {

            $newfeed = $query->row();*/

        $this->db->update("user_reviews",
            array("content" => $content, "rate" => $rating),
            array("id" => $reviewId, "user_id" => $userId));

        /*    return $newfeed->review_id;
        }*/
    }

    /**
     * @param $userId
     * @param $newFeedId
     * @Description: This method removes ALL the photos of a news feed. It requires
     * the news feed ID. Otherwise we should call the another "remove media" of
     * post updated, checkin, write review or topic discussion
     */
    public function deleteNewsfeedMedia( $userId, $newFeedId ) {
        $query = $this->db->get_where("user_media", array("newfeed_id" => $newFeedId, "user_id" => $userId));

        if($query->num_rows() > 0) {
            $results = $query->result();
            $this->media_model->removeMedia($results);
            $this->db->delete("user_media", array("newfeed_id" => $newFeedId, "user_id" => $userId));
        }
    }

    /**
     * @param $userId
     * @param $newsFeedId
     * @param $removeMedia
     * @description: This method remove Selected photo items. We use this methods when user edit newsfeeds
     * @tags: Libraries: Petpostupdated, Pettopic, Petreview, Petlisting
     */
    public function deleteSelectedNewsfeedMedia( $userId, $newsFeedId, $removeMedia) {
        $query = $this->db->query("SELECT * FROM user_media WHERE newfeed_id = ? AND user_id = ? AND id IN ?", array($newsFeedId, $userId, explode(",", $removeMedia)));
        if($query->num_rows() > 0) {
            $results = $query->result();
            $this->media_model->removeMedia($results);
            $this->db->query("DELETE FROM user_media WHERE newfeed_id = ? AND user_id = ? AND id IN ?", array($newsFeedId, $userId, explode(",", $removeMedia)));
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

    public function getNewsfeedIdFromItem( $itemId, $type ) {

        $query = "";

        switch ($type) {

            case ADD_PETTALK_ADOPTION:
            case ADD_PETTALK_FOUND_REPORT:
            case ADD_PETTALK_LOST_REPORT:
                $query = $this->db->get_where("user_newsfeed_activities", array("pettalk_info_id" => $itemId, "newsFeedType" => $type));
                break;
            case ADD_POST_UPDATED:
                $query = $this->db->get_where("user_newsfeed_activities", array("post_update_id" => $itemId, "newsFeedType" => $type));
                break;

            case ADD_CHECKIN:
                $query = $this->db->get_where("user_newsfeed_activities", array("checkin_id" => $itemId, "newsFeedType" => $type));
                break;

            case ADD_PET_TOPIC:
                $query = $this->db->get_where("user_newsfeed_activities", array("topic_id" => $itemId, "newsFeedType" => $type));
                break;

            case ADD_REVIEW:
                $query = $this->db->get_where("user_newsfeed_activities", array("review_id" => $itemId, "newsFeedType" => $type));
                break;

            case ADD_PHOTO_LISTING:
                $query = $this->db->get_where("user_newsfeed_activities", array("photo_listing_id" => $itemId, "newsFeedType" => $type));
                break;

            case ADD_SHARING_PHOTO:
                $query = $this->db->get_where("user_newsfeed_activities", array("photo_sharing_id" => $itemId, "newsFeedType" => $type));
                break;
        }

        if($query->num_rows() > 0) {
            $newfeed = $query->row();
            return $newfeed->id;
        }
        return 0;
    }

    /**
     * @param $itemId
     * @param $type
     * @return int|Object
     */
    public function getNewsfeedFromItem( $itemId, $type, $memberId = false ) {

        $query = "";
        $arrWhere   = array("newsFeedType" => $type);
        if($memberId) {
            $arrWhere["user_id"] = $memberId;
        }

        switch ($type) {
            case ADD_PETTALK_ADOPTION:
            case ADD_PETTALK_FOUND_REPORT:
            case ADD_PETTALK_LOST_REPORT:
                $arrWhere["pettalk_info_id"] = $itemId;
                $query = $this->db->get_where("user_newsfeed_activities", $arrWhere);
                break;

            case ADD_POST_UPDATED:
                $arrWhere["post_update_id"] = $itemId;
                $query = $this->db->get_where("user_newsfeed_activities", $arrWhere);
                break;

            case ADD_CHECKIN:
                $arrWhere["checkin_id"] = $itemId;
                $query = $this->db->get_where("user_newsfeed_activities", $arrWhere);
                break;

            case ADD_PET_TOPIC:
                $arrWhere["topic_id"] = $itemId;
                $query = $this->db->get_where("user_newsfeed_activities", $arrWhere);
                break;

            case ADD_REVIEW:
                $arrWhere["review_id"] = $itemId;
                $query = $this->db->get_where("user_newsfeed_activities", $arrWhere);
                break;

            case ADD_PHOTO_LISTING:
                $arrWhere["photo_listing_id"] = $itemId;
                $query = $this->db->get_where("user_newsfeed_activities", $arrWhere);
                break;

            case ADD_SHARING_PHOTO:
                $arrWhere["photo_sharing_id"] = $itemId;
                $query = $this->db->get_where("user_newsfeed_activities", $arrWhere);
                break;
        }

        if($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }

    public function saveNew($params) {
        $this->db->insert("user_newsfeed_activities", $params);

        return $this->db->insert_id();
    }

    public function saveNewsFeedSharing($params = array()) {
        $this->db->insert("user_newsfeed_sharing", $params);

        return $this->db->insert_id();
    }

    /**
     * @param bool|false $newsFeedId
     * @param bool|false $newsFeedItemId
     * @param bool|false $newsFeedType
     * @return $this
     * @description: Get newsfeed media for a newsfeed
     */
    public function getNewsfeedMedia( $newsFeedId = false, $newsFeedItemId = false, $newsFeedType = false ) {

        if( $newsFeedId ) {

            $sql = "SELECT m.*, u.first_name, u.last_name, u.id AS userId, u.profile_photo, u.profile_photo_thumb
            FROM user_media m
            INNER JOIN user_newsfeed_activities a ON a.id = m.newfeed_id
            INNER JOIN users u on u.id = a.user_id
            WHERE m.newfeed_id = ?
            GROUP BY m.id
            ORDER BY m.created_date DESC";

            $query = $this->db->query($sql, array("newfeed_id" => $newsFeedId));

            $this->newsfeedMedia = $query->num_rows() > 0 ? $query->result() : array();
        }
        return $this;
    }

    public function getNewsfeedMediaTransformer() {
        $arrData = array();

        if(count($this->newsfeedMedia)) {
            foreach( $this->newsfeedMedia as $result ) {
                format_output_data($result);

                if($result->media_type == '' || $result->media_type == 'attachment') {
                    $arrPhoto = array(
                        ID            => $result->id,
                        SOURCE        => $result->source,
                        PHOTO_THUMB   => $result->photo_thumb,
                        THUMB_WIDTH   => $result->width_thumb,
                        THUMB_HEIGHT  => $result->height_thumb,
                        PHOTO_WIDTH   => $result->width_source,
                        PHOTO_HEIGHT  => $result->height_source,
                    );
                    $arrUser = array(
                        ID            => $result->userId,
                        FIRST_NAME    => $result->first_name,
                        LAST_NAME     => $result->last_name,
                        PROFILE_PHOTOS => array(
                            $result->profile_photo,
                            $result->profile_photo_thumb
                        )
                    );
                    $arrData[PHOTOS][] = array(
                        PHOTOS     => $arrPhoto,
                        USER_INFO  => $arrUser
                    );
                } elseif($result->media_type == 'cover') {
                    $arrCover = array(
                        ID            => $result->id,
                        SOURCE        => $result->source,
                        PHOTO_THUMB   => $result->photo_thumb,
                        THUMB_WIDTH   => $result->width_thumb,
                        THUMB_HEIGHT  => $result->height_thumb,
                        PHOTO_WIDTH   => $result->width_source,
                        PHOTO_HEIGHT  => $result->height_source,
                    );
                    $arrData[COVER_PHOTO] = $arrCover;
                }
            }
        }
        return $arrData;
    }

    /**
     * @param $newsFeedId
     * @param $newsFeedItemId
     * @param $newsFeedType
     * @param $userId
     * @description: Delete a newsfeed
     * @tag: petnewsfeed lib
     */
    public function deleteNewsfeed( $newsFeedId, $newsFeedItemId, $newsFeedType, $userId ) {
        $this->db->delete("user_newsfeed_activities", array("id" => $newsFeedId,
            "user_id" => $userId,
            "newsFeedType" => $newsFeedType,
        ));
    }
}