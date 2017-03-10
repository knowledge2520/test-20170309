<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Petcomment {

    private $ci;

    protected $error;

    protected $_member;

    const PUSH_TYPE_ADD_REVIEW 				= 'ADD_REVIEW';
    const PUSH_TYPE_ADD_CHECKIN				= 'ADD_CHECKIN';
    const PUSH_TYPE_ADD_LISTING 			= 'ADD_LISTING';
    const PUSH_TYPE_ADD_PET_TOPIC 			= 'ADD_PET_TOPIC';
    const PUSH_TYPE_ADD_POSTED_UPDATE       = 'ADD_POST_UPDATED';
    const PUSH_TYPE_ADD_PHOTO_SHARING       = 'ADD_SHARING_PHOTO';
    const PUSH_TYPE_ADD_PHOTO_LISTING       = 'ADD_PHOTO_LISTING';
    const PUSH_TYPE_ADD_PET_INFO            = 'ADD_PET_INFO';
    const PUSH_TYPE_ADD_PET_ADOPTION        = 'ADD_PETTALK_ADOPTION';
    const PUSH_TYPE_ADD_PET_LOST_REPORT     = 'ADD_PETTALK_LOST_REPORT';
    const PUSH_TYPE_ADD_PET_FOUND_REPORT    = 'ADD_PETTALK_FOUND_REPORT';

    const PUSH_TYPE_LIKE_REVIEW 			= 'LIKE_REVIEW';
    const PUSH_TYPE_LIKE_TOPIC				= 'LIKE_TOPIC';
    const PUSH_TYPE_LIKE_CHECKIN            = 'LIKE_CHECKIN';
    const PUSH_TYPE_LIKE_POSTED             = 'LIKE_POSTED_UPDATE';
    const PUSH_TYPE_LIKE_PHOTO_LISTING      = 'LIKE_LISTING_PHOTO';
    const PUSH_TYPE_LIKE_PHOTO_SHARING      = 'LIKE_SHARING_PHOTO';
    const PUSH_TYPE_LIKE_PET_INFO           = 'LIKE_PET_INFO';
    const PUSH_TYPE_LIKE_PET_ADOPTION       = 'LIKE_ADOPTION';
    const PUSH_TYPE_LIKE_PET_LOST_REPORT    = 'LIKE_LOST_REPORT';
    const PUSH_TYPE_LIKE_PET_FOUND_REPORT   = 'LIKE_FOUND_REPORT';

    function __construct($params = array()) {

        $this->ci = & get_instance();

        $this->ci->load->model('newsfeedlike_model');
        $this->ci->load->model('newsfeed_model');
        $this->ci->load->model('pettalk_model');
        $this->ci->load->model('notification_model');
        $this->ci->load->model('usertag_model');
        $this->ci->load->model('media_model');

        $this->ci->load->library('petupload');

        $this->ci->load->helper('notification');
        $this->ci->load->helper('newsfeeds');

        $this->_member = $params["member"];

        $error = array();
    }

    /**
     * @param $data
     * @description: add new a comment to news feed
     */
    public function saveNew($data, $member, $userTag = '') {

        $newsFeedId     = $data["newsFeedId"] ? $data["newsFeedId"] : false;
        $newsFeedItemId = $data['newsFeedItemId'] ? $data['newsFeedItemId'] : null;
        $newsFeedType   = $data["newsFeedType"] ? $data["newsFeedType"] : false;
        $comment        = $data["content"] ? $data["content"] : "";
        $receiverId     = $data["receicerId"] ? $data["receicerId"] : false;
        //$parentId       = $data["parentId"] ? $data["parentId"] : 0;
        $userId         = $member->id;

        // Data for inserting comment
        $arrData = array(
            "user_id"   => $userId,
            "content"   => $comment,
            "status"    => 1,
            "created_date"=> now(),
        );

        // Data for pushnotification
        $arrPushData = array();

        switch ($newsFeedType) {

            case ADD_PETTALK_ADOPTION:
            case ADD_PETTALK_FOUND_REPORT:
            case ADD_PETTALK_LOST_REPORT:
                $newsFeedObj = $this->ci->newsfeed_model->getNewsfeedFromItem( $newsFeedItemId, $newsFeedType );
                $arrData["pettalk_info_id"]  = $newsFeedItemId;
                $arrPushData["dataPush"] = "pettalk_info_id";
                $arrPushData['type']     = 'feed';
                $arrPushData['message']  = lang('comment your topic');
                break;
            case ADD_CHECKIN:
                $newsFeedObj = $this->ci->newsfeed_model->getNewsfeedFromItem( $newsFeedItemId, ADD_CHECKIN );
                $arrData["checkin_id"]  = $newsFeedItemId;
                $arrPushData["dataPush"] = "checkin_id";
                $arrPushData['type']     = 'feed';
                $arrPushData['message']  = lang('comment your checkin');
                break;
            case ADD_PET_TOPIC:
                $newsFeedObj = $this->ci->newsfeed_model->getNewsfeedFromItem( $newsFeedItemId, ADD_PET_TOPIC );
                $arrData["topic_id"] = $newsFeedItemId;
                $arrPushData["dataPush"]  = "topic_id";
                $arrPushData['type']      = 'feed';
                $arrPushData['message']   = lang('comment your topic');
                break;
            case ADD_REVIEW:
                $newsFeedObj = $this->ci->newsfeed_model->getNewsfeedFromItem( $newsFeedItemId, ADD_REVIEW );
                $arrData["review_id"] = $newsFeedItemId;
                $arrPushData["dataPush"]  = "review_id";
                $arrPushData['type']      = 'feed';
                $arrPushData['message']   = lang('comment your review');
                break;
            case ADD_POST_UPDATED:
                $newsFeedObj = $this->ci->newsfeed_model->getNewsfeedFromItem( $newsFeedItemId, ADD_POST_UPDATED );
                $arrData["post_id"] = $newsFeedItemId;
                $arrPushData["dataPush"]  = "post_id";
                $arrPushData['type']      = 'feed';
                $arrPushData['message']   = lang('comment your posted');
                break;
            case ADD_PHOTO_LISTING:
                $newsFeedObj = $this->ci->newsfeed_model->getNewsfeedFromItem( $newsFeedItemId, ADD_PHOTO_LISTING );
                $arrData["album_id"]    = $newsFeedItemId;
                $arrPushData["dataPush"]  = "album_id";
                $arrPushData['type']      = 'feed';
                $arrPushData['message']   = lang('comment your listing');
                break;
            case ADD_SHARING_PHOTO:
                $newsFeedObj = $this->ci->newsfeed_model->getNewsfeedFromItem( $newsFeedItemId, ADD_SHARING_PHOTO );
                $arrData["sharing_id"]  = $newsFeedItemId;
                $arrPushData["dataPush"]  = "sharing_id";
                $arrPushData['type']      = 'feed';
                $arrPushData['message']   = lang('comment your sharing');
                break;
        }

        if( !$newsFeedId ) {
            $newsFeedId = $newsFeedObj->id;
        }

        if( !$receiverId ) {
            $receiverId = $newsFeedObj->user_id;
        }

        $commentObj = $this->ci->newsfeedlike_model->saveNew( $arrData );

        if ($commentObj->id) {

            //$this->saveCommentMedia($commentObj);
            $this->ci->media_model->saveMedia( false, $commentObj->id, 'newfeed_comment_id', $member->id );

            $arrPushTypeMapping = array(
                self::PUSH_TYPE_ADD_CHECKIN         => self::PUSH_TYPE_LIKE_CHECKIN,
                self::PUSH_TYPE_ADD_REVIEW          => self::PUSH_TYPE_LIKE_REVIEW,
                self::PUSH_TYPE_ADD_PET_TOPIC       => self::PUSH_TYPE_LIKE_TOPIC,
                self::PUSH_TYPE_ADD_POSTED_UPDATE   => self::PUSH_TYPE_LIKE_POSTED,
                self::PUSH_TYPE_ADD_PHOTO_LISTING   => self::PUSH_TYPE_LIKE_PHOTO_LISTING,
                self::PUSH_TYPE_ADD_PHOTO_SHARING   => self::PUSH_TYPE_LIKE_PHOTO_SHARING,
                self::PUSH_TYPE_ADD_PET_INFO        => self::PUSH_TYPE_LIKE_PET_INFO,
                self::PUSH_TYPE_ADD_PET_ADOPTION    => self::PUSH_TYPE_LIKE_PET_ADOPTION,
                self::PUSH_TYPE_ADD_PET_FOUND_REPORT=> self::PUSH_TYPE_LIKE_PET_FOUND_REPORT,
                self::PUSH_TYPE_ADD_PET_LOST_REPORT => self::PUSH_TYPE_LIKE_PET_LOST_REPORT,
            );

            $action_type = get_action_type($arrPushTypeMapping[$newsFeedType]);
            $name_user_action   = $member->first_name . ' ' . $member->last_name;

            // Check and add user tag
            if( !empty($userTag) ) {
                $arrUsers = json_decode($userTag);
                $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'comment' );
                foreach( $arrUsers as $item ) {
                    $this->ci->usertag_model->saveNew(array(
                        "sourceId"      => $commentObj->id,
                        "sourceType"    => COMMENT_USER_TAG,
                        "userTag"       => $item->userId,
                        "userId"        => $member->id,
                        "textRange"     => $item->textRange,
                        "created_date"  => now(),
                    ));

                    $data_push_tag = array(
                        'action_type'               => $arrPushTypeMapping[$newsFeedType],
                        'sender_id'                 => (int)$member->id,
                        'sender_name'               => $name_user_action,
                        'receiver_id'               => (int)$item->userId,
                        'type'                      => $arrPushData["type"],
                        'newsFeedItemId'            => (int)$newsFeedItemId,
                        'newsFeedType'              => $newsFeedType,
                        'newsFeedId'                => (int)$newsFeedId,
                        'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                    );
                    $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push_tag, $action_type->id, $newsFeedItemId);
                }
            }

            if( $receiverId != $userId ) {
                $userOptions = $this->ci->member_model->get_user_options($receiverId);
                if( $userOptions->notifications_likes_and_comments == 'on') {
                    $message = $name_user_action . ' ' . $arrPushData['message'];

                    $data_push = array(
                        'action_type'               => $arrPushTypeMapping[$newsFeedType],
                        'sender_id'                 => (int)$userId,
                        'sender_name'               => $name_user_action,
                        'type'                      => $arrPushData["type"],
                        $arrPushData["dataPush"]    => (int)$newsFeedItemId,
                        'newsFeedItemId'            => (int)$newsFeedItemId,
                        'newsFeedType'              => $newsFeedType,
                        'newsFeedId'                => (int)$newsFeedId,
                        'totalLikes'                => getNewsfeedTotalLike($newsFeedItemId, $newsFeedType),
                        'totalComments'             => getNewsfeedTotalReview($newsFeedItemId, $newsFeedType),
                        'bages_unread_notification' => count_unread_notification($receiverId) + 1,
                    );

                    if ($newsFeedType == self::PUSH_TYPE_ADD_PET_TOPIC) {
                        $topic = (object)$this->ci->pettalk_model->get_topic($newsFeedItemId);

                        $data_push['category_id'] = $topic->category_id;
                    }

                    $this->ci->notification_model->send_push_notification($receiverId, $message, $data_push, $action_type->id, $newsFeedItemId);
                }
            }
            return $this->getNewsfeedCommentTransformer($commentObj);
        }
    }

    public function save($params, $member, $userTag = '') {

        $arrData = array(
            "content"   => $params["content"]
        );
        $arrConds = array(
            "id" => $params["commentId"]
        );
        $commentObj = $this->ci->newsfeedlike_model->save( $arrData, $arrConds );

        //$this->saveCommentMedia($commentObj);
        $this->ci->media_model->saveMedia( false, $commentObj->id, 'newfeed_comment_id', $member->id );

        if( $params["removeMedia"] ) {
            //removeNewsFeedCommentMedia($params["removeMedia"], $params["commentId"], $member->id);
            $this->ci->newsfeedlike_model->deleteCommentMedia( $member->id, $commentObj->id, $params["removeMedia"] );
        }

        $this->ci->usertag_model->delete(array(
            "sourceId"      => $params["commentId"],
            "sourceType"    => COMMENT_USER_TAG,
            "userId"        => $member->id
        ));

        $arrPushTypeMapping = array(
            self::PUSH_TYPE_ADD_CHECKIN         => self::PUSH_TYPE_LIKE_CHECKIN,
            self::PUSH_TYPE_ADD_REVIEW          => self::PUSH_TYPE_LIKE_REVIEW,
            self::PUSH_TYPE_ADD_PET_TOPIC       => self::PUSH_TYPE_LIKE_TOPIC,
            self::PUSH_TYPE_ADD_POSTED_UPDATE   => self::PUSH_TYPE_LIKE_POSTED,
            self::PUSH_TYPE_ADD_PHOTO_LISTING   => self::PUSH_TYPE_LIKE_PHOTO_LISTING,
            self::PUSH_TYPE_ADD_PHOTO_SHARING   => self::PUSH_TYPE_LIKE_PHOTO_SHARING,
            self::PUSH_TYPE_ADD_PET_INFO        => self::PUSH_TYPE_LIKE_PET_INFO,
            self::PUSH_TYPE_ADD_PET_ADOPTION    => self::PUSH_TYPE_LIKE_PET_ADOPTION,
            self::PUSH_TYPE_ADD_PET_FOUND_REPORT=> self::PUSH_TYPE_LIKE_PET_FOUND_REPORT,
            self::PUSH_TYPE_ADD_PET_LOST_REPORT => self::PUSH_TYPE_LIKE_PET_LOST_REPORT,
        );

        $action_type        = get_action_type($arrPushTypeMapping[$params['newsFeedType']]);
        $name_user_action   = $member->first_name . ' ' . $member->last_name;
        $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'comment' );
        // Check and add user tag
        if( !empty($userTag) ) {

            $arrUsers = json_decode($userTag);
            foreach( $arrUsers as $item ) {
                $this->ci->usertag_model->saveNew(array(
                    "sourceId"      => $params["commentId"],
                    "sourceType"    => COMMENT_USER_TAG,
                    "userTag"       => $item->userId,
                    "userId"        => $member->id,
                    "textRange"     => $item->textRange,
                    "created_date"  => now(),
                ));

                $data_push_tag = array(
                    'action_type'               => $arrPushTypeMapping[$params['newsFeedType']],
                    'sender_id'                 => (int)$member->id,
                    'sender_name'               => $name_user_action,
                    'receiver_id'               => (int)$item->userId,
                    'type'                      => "feed",
                    'newsFeedItemId'            => (int)$params['newsFeedItemId'],
                    'newsFeedType'              => $params['newsFeedType'],
                    'newsFeedId'                => (int)$params['newsFeedId'],
                    'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                );
                $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push_tag, $action_type->id, $params['newsFeedItemId']);
            }
        }

        return $this->getNewsfeedCommentTransformer($commentObj);
    }

    /*public function saveCommentMedia($commentObj) {

        $dataInsert = array();

        $media_files = $this->ci->petupload->doMultiUpload($this->ci->config->item('listings_path'));

        if($media_files) {
            foreach ($media_files as $file) {

                $media_insert = array();

                $file_array = $this->ci->config->item('upload') == 's3-aws' ? $file : $file['upload_data'];
                $source     = $this->ci->config->item('upload') == 's3-aws' ? $file['uri'] : $this->ci->config->item('api_upload_path') . $this->ci->config->item('listings_path') . $file_array['file_name'];

                $media_insert['newfeed_comment_id'] = $commentObj->id;
                $media_insert['source'] = $source;
                $media_insert['created_date'] = now();
                $media_insert['status'] = 1;
                $media_insert['user_id'] = $commentObj->user_id;

                $media_insert['type'] = 'PHOTO';

                if($this->ci->config->item('upload') == 's3-aws') {
                    $media_insert['width_source']  = $file['width'];
                    $media_insert['height_source'] = $file['height'];

                    // thumb
                    $media_insert['photo_thumb']  = $file['uri_thumb'];
                    $media_insert['width_thumb']  = $file['width_thumb'];
                    $media_insert['height_thumb'] = $file['height_thumb'];
                } else {
                    //resize
                    resizeImage($file_array['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                    $file_name_array = explode('.', $file_array['file_name']);
                    $media_insert['photo_thumb'] = $this->ci->config->item('api_upload_path') . $this->ci->config->item('listings_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];

                    if( function_exists('getimagesize')) {
                        list($w, $h) = getimagesize($media_insert['source']);
                        list($wThumb, $hThumb) = getimagesize($media_insert['photo_thumb']);
                        $media_insert['width_thumb']   = $wThumb;
                        $media_insert['height_thumb']  = $hThumb;
                        $media_insert['width_source']  = $w;
                        $media_insert['height_source'] = $h;
                    }
                }

                array_push($dataInsert, $media_insert);
            }
            insert_user_media($dataInsert);
        }
    }*/

    public function delete( $commentId, $member ) {

        $commentObj = $this->ci->newsfeedlike_model->getComment($commentId);

        if( $commentObj ) {
            $arrWhoCanDelete = array($commentObj->userId, $commentObj->ownerId);

            if( in_array($member->id, $arrWhoCanDelete) ) {
                // Delete Comment Photo
                //removeNewsFeedCommentMedia('all', $commentId, $member->id);
                $this->ci->newsfeedlike_model->deleteCommentMedia( $member->id, $commentId, 'all' );
                $this->ci->newsfeedlike_model->deleteComment( $member->id, $commentId );

                return 1;
            }
            return -2;
        }
        return -1;
    }

    public function getNewsfeedComments($newsFeedId = false, $newsFeedItemId, $newsFeedType, $limit = API_NUM_RECORD_PER_PAGE, $start) {
        if( !$newsFeedId ) {
            $newsFeedObj = $this->ci->newsfeed_model->getNewsfeedFromItem( $newsFeedItemId, ADD_CHECKIN );

            $newsFeedId = $newsFeedObj->id;
        }

        $items                  = $this->ci->newsfeedlike_model->getCommentsByNewsFeedItem('all', $newsFeedId, $newsFeedItemId, $newsFeedType, $limit, $start);
        $data["totalItem"]      = $this->ci->newsfeedlike_model->getCommentsByNewsFeedItem('count', $newsFeedId, $newsFeedItemId, $newsFeedType, $limit, $start);
        $data['totalPage']      = $data['totalItem'] > 0 ? ceil(intval($data['totalItem']) / $limit) : 0;
        $data['limit']          = intval($limit);
        $data["items"]          = $this->getNewsfeedCommentsTransformer($items);
        return $data;
    }

    protected function getNewsfeedCommentTransformer($commentObj) {
        if($commentObj) {
            format_output_data($commentObj);
            $commentPhotos = $this->ci->newsfeedlike_model->findCommentMedia($commentObj);
            return array(
                USER_INFO => array(
                    ID          => $commentObj->userId,
                    FIRST_NAME  => $commentObj->first_name,
                    LAST_NAME   => $commentObj->last_name,
                    PROFILE_PHOTOS => array(
                        $commentObj->profile_photo,
                        $commentObj->profile_photo_thumb
                    ),
                    TOTAL_FRIEND => getTotalUserFriends($commentObj->userId),
                    TOTAL_PHOTO  => getTotalUserListingPhotos($commentObj->userId),
                    TOTAL_REVIEW => getTotalUserReviews($commentObj->userId)
                ),
                COMMENT_INFO => array(
                    ID              => $commentObj->id,
                    NEWSFEED_ID     => $commentObj->newsFeedId,
                    NEWSFEED_TYPE   => $commentObj->newsFeedType,
                    CONTENT         => $commentObj->content,
                    CREATED_DATE    => $commentObj->created_date,
                    CREATED_TIME    => $commentObj->created_time,
                    PHOTOS          => $commentPhotos
                ),
                USER_TAG => $this->ci->usertag_model->getUserTag( $commentObj->id, COMMENT_USER_TAG )->transformer(),
            );
        }
    }

    protected function getNewsfeedCommentsTransformer($arrCommentObj = array()) {

        if( count($arrCommentObj) ) {

            $arrData = array();

            foreach( $arrCommentObj as $commentObj ) {

                format_output_data($commentObj);

                $commentPhotos = $this->ci->newsfeedlike_model->findCommentMedia($commentObj);

                $arrData[] = array(
                    USER_INFO => array(
                        ID          => $commentObj->userId,
                        FIRST_NAME  => $commentObj->first_name,
                        LAST_NAME   => $commentObj->last_name,
                        PROFILE_PHOTOS => array(
                            $commentObj->profile_photo,
                            $commentObj->profile_photo_thumb
                        )
                    ),
                    COMMENT_INFO => array(
                        ID              => $commentObj->id,
                        NEWSFEED_ID     => $commentObj->newsFeedId,
                        NEWSFEED_TYPE   => $commentObj->newsFeedType,
                        CONTENT         => $commentObj->content,
                        CREATED_DATE    => $commentObj->created_date,
                        CREATED_TIME    => $commentObj->created_time,
                        PHOTOS          => $commentPhotos
                    ),
                    USER_TAG => $this->ci->usertag_model->getUserTag( $commentObj->id, COMMENT_USER_TAG )->transformer(),
                );
            }
            return $arrData;

        } else {
            return $arrCommentObj;
        }
    }

    /**
     * @param bool|false $newsFeedId
     * @param $newFeedItemId
     * @param $newsFeedType
     * @param $receiverId
     * @param $senderObj
     * @description: like a newsfeed
     */
    public function like( $newsFeedId = false, $newFeedItemId, $newsFeedType, $receiverId, $senderObj ) {

        if( !$newsFeedId ) {
            $newsFeedObj = $this->ci->newsfeed_model->getNewsfeedFromItem( $newFeedItemId, $newsFeedType );

            $newsFeedId = $newsFeedObj->id;
        }

        $arrPushTypeMapping = array(
            self::PUSH_TYPE_ADD_CHECKIN         => self::PUSH_TYPE_LIKE_CHECKIN,
            self::PUSH_TYPE_ADD_REVIEW          => self::PUSH_TYPE_LIKE_REVIEW,
            self::PUSH_TYPE_ADD_PET_TOPIC       => self::PUSH_TYPE_LIKE_TOPIC,
            self::PUSH_TYPE_ADD_POSTED_UPDATE   => self::PUSH_TYPE_LIKE_POSTED,
            self::PUSH_TYPE_ADD_PHOTO_LISTING   => self::PUSH_TYPE_LIKE_PHOTO_LISTING,
            self::PUSH_TYPE_ADD_PHOTO_SHARING   => self::PUSH_TYPE_LIKE_PHOTO_SHARING,
            self::PUSH_TYPE_ADD_PET_INFO        => self::PUSH_TYPE_LIKE_PET_INFO,
            self::PUSH_TYPE_ADD_PET_ADOPTION    => self::PUSH_TYPE_LIKE_PET_ADOPTION,
            self::PUSH_TYPE_ADD_PET_FOUND_REPORT=> self::PUSH_TYPE_LIKE_PET_FOUND_REPORT,
            self::PUSH_TYPE_ADD_PET_LOST_REPORT => self::PUSH_TYPE_LIKE_PET_LOST_REPORT,
        );

        $pushType = $arrPushTypeMapping[$newsFeedType];

        $senderName         = $senderObj->first_name . ' ' . $senderObj->last_name;

        //$action_type        = get_action_type($pushType);

        $data_push 			= array(
            'action_type'               => $pushType,
            'sender_id'                 => (int)$senderObj->id,
            'sender_name'               => $senderName,
            'totalLikes'                => getNewsfeedTotalLike( $newFeedItemId, $newsFeedType ),
            'totalComments'             => getNewsfeedTotalReview( $newFeedItemId, $newsFeedType ),
            'newsFeedItemId'            => (int)$newFeedItemId,
            'newsFeedId'                => (int)$newsFeedId,
            'newsFeedType'              => $newsFeedType,
            'bages_unread_notification' => count_unread_notification($receiverId) + 1,
        );

        switch ($newsFeedType ) {

            case ADD_PETTALK_ADOPTION:
            case ADD_PETTALK_FOUND_REPORT:
            case ADD_PETTALK_LOST_REPORT:
                $this->ci->newsfeedlike_model->likePetInfo( $newFeedItemId, $senderObj->id, 0 );
                $data_push['message'] 			= $senderName .' '.lang('like your topic');
                $data_push['type']              = 'feed';
                $data_push['pettalk_info_id']   = $newFeedItemId;
                break;

            case ADD_CHECKIN:
                $this->ci->newsfeedlike_model->likeCheckin( $newFeedItemId, $senderObj->id, 0 );
                $data_push['type']          = 'feed';
                $data_push['newsFeedId']    = $newsFeedId;
                $data_push['review_id']     = $newFeedItemId;
                $data_push['message']       = $senderName .' '.lang('like your checkin');
                break;

            case ADD_PET_TOPIC:
                $this->ci->newsfeedlike_model->likePetTopic( $newFeedItemId, $senderObj->id, 0 );
                $petTalkQuery = $this->ci->db->get_where("pet_talk_topics", array("id" => $newFeedItemId));
                $data_push['type']          = 'feed';
                $data_push['category_id']   = $petTalkQuery->num_rows() > 0 ? $petTalkQuery->row()->category_id : 0;
                $data_push['message'] 	    = $senderName .' '.lang('like your topic');
                $data_push['topic_id']      = $newFeedItemId;
                break;

            case ADD_REVIEW:
                $this->ci->newsfeedlike_model->likeReview( $newFeedItemId, $senderObj->id, 0 );
                $data_push['message'] 	    = $senderName .' '.lang('like your review');
                $data_push['type']          = 'feed';
                $data_push['review_id']     = $newFeedItemId;
                break;

            case ADD_POST_UPDATED:
                $this->ci->newsfeedlike_model->likePost( $newFeedItemId, $senderObj->id, 0 );
                $data_push['message'] 		= $senderName .' '.lang('like your posted');
                $data_push['type']          = 'feed';
                $data_push['post_id']       = $newFeedItemId;
                break;

            case ADD_PHOTO_LISTING:
                $this->ci->newsfeedlike_model->likePhotoListing( $newFeedItemId, $senderObj->id, 0 );
                $data_push['message'] 		= $senderName .' '.lang('like your photo listing');
                $data_push['type']          = 'feed';
                $data_push['photo_listing_id'] = $newFeedItemId;
                break;

            case ADD_SHARING_PHOTO:
                $this->ci->newsfeedlike_model->likePhotoSharing( $newFeedItemId, $senderObj->id, 0 );
                $data_push['message'] 			= $senderName .' '.lang('like your photo sharing');
                $data_push['type']              = 'feed';
                $data_push['photo_sharing_id']  = $newFeedItemId;
                break;
        }

        if( $receiverId != $senderObj->id ) {

            $userOptions = $this->ci->member_model->get_user_options($receiverId);

            if( $userOptions->notifications_likes_and_comments == 'on') {
                $message = $data_push['message'];

                unset($data_push['message']);

                $action_type        = get_action_type($pushType);

                $this->ci->notification_model->send_push_notification($receiverId, $message, $data_push, $action_type->id, $newFeedItemId);
            }
        }
    }

    /**
     * @param $newFeedItemId
     * @param $newsFeedType
     * @param $member
     * @description: dislike a newsfeed
     */
    public function dislike( $newFeedItemId, $newsFeedType, $member ) {
        switch ($newsFeedType ) {

            case ADD_PETTALK_ADOPTION:
            case ADD_PETTALK_FOUND_REPORT:
            case ADD_PETTALK_LOST_REPORT:
                $this->ci->newsfeedlike_model->likePetInfo( $newFeedItemId, $member->id, 1 );
                break;
            case ADD_CHECKIN:
                $this->ci->newsfeedlike_model->likeCheckin( $newFeedItemId, $member->id, 1 );
                break;
            case ADD_PET_TOPIC:
                $this->ci->newsfeedlike_model->likePetTopic( $newFeedItemId, $member->id, 1 );
                break;
            case ADD_REVIEW:
                $this->ci->newsfeedlike_model->likeReview( $newFeedItemId, $member->id, 1 );
                break;
            case ADD_POST_UPDATED:
                $this->ci->newsfeedlike_model->likePost( $newFeedItemId, $member->id, 1 );
                break;
            case ADD_PHOTO_LISTING:
                $this->ci->newsfeedlike_model->likePhotoListing( $newFeedItemId, $member->id, 1 );
                break;
            case ADD_SHARING_PHOTO:
                $this->ci->newsfeedlike_model->likePhotoSharing( $newFeedItemId, $member->id, 1 );
                break;
        }
    }
}