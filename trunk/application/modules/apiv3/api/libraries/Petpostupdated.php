<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Petpostupdated {

    private $ci;

    function __construct($params = array()) {

        $this->ci = & get_instance();

        $this->ci->load->model('member_model');
        $this->ci->load->model('newsfeed_model');
        $this->ci->load->model('postupdated_model');
        $this->ci->load->model('notification_model');
        $this->ci->load->model('media_model');
        $this->ci->load->model('usertag_model');

        //load lang
        $this->ci->lang->load('api');

        $this->ci->load->library('petnewsfeed');
        $this->ci->load->helper('notification');

        //load helper
        $this->ci->load->helper(array('form', 'url'));
        $this->ci->load->helper('newsfeeds');
    }

    /**
     * @param $content
     * @param $member
     * @return array
     * @description: Create new post updated content
     */
    public function saveNew( $content, $member, $userTag = '' ) {

        $postId = $this->ci->postupdated_model->newItem( $member->id, "", $content );

        if( $postId ) {

            $newsFeedId = $this->ci->petnewsfeed->addNew(array(
                "post_update_id"=> $postId,
                "newsFeedType"  => ADD_POST_UPDATED,
                "user_id"       => $member->id,
                "created_date"  => now(),
                "updated_date"  => now()
            ));

            $this->ci->media_model->saveMedia( $newsFeedId, $postId, 'post_update_id', $member->id );

            // Check and add user tag
            if( !empty($userTag) ) {
                $arrUsers = json_decode($userTag);
                $name_user_action   = $member->first_name . ' ' . $member->last_name;
                $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );
                $action_type = get_action_type(LIKE_POSTED_UPDATE);

                foreach( $arrUsers as $item ) {
                    $this->ci->usertag_model->saveNew(array(
                        "sourceId"      => $newsFeedId,
                        "sourceType"    => NEWSFEED_USER_TAG,
                        "userTag"       => $item->userId,
                        "userId"        => $member->id,
                        "textRange"     => $item->textRange,
                        "created_date"  => now(),
                    ));

                    $data_push_tag = array(
                        'action_type'               => LIKE_POSTED_UPDATE,
                        'sender_id'                 => $member->id,
                        'sender_name'               => $name_user_action,
                        'receiver_id'               => $item->userId,
                        'type'                      => 'feed',
                        'post_id'                   => $postId,
                        'newsFeedItemId'            => $postId,
                        'newsFeedType'              => ADD_POST_UPDATED,
                        'newsFeedId'                => $newsFeedId,
                        'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                    );
                    $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push_tag, $action_type->id, $postId);
                }
            }

            return $this->ci->petnewsfeed->detail( $newsFeedId, $postId, ADD_POST_UPDATED, $member);
        }
        return array();
    }

    /**
     * @param bool|false $newsFeedId
     * @param $newsFeedItemId
     * @param $member
     * @param $content
     * @param array $removeMedia
     * @return mixed
     * @description: Edit post updated content
     */
    public function save( $newsFeedId = false, $newsFeedItemId, $member, $content, $removeMedia = '', $userTag = '' ) {

        //$content    = add_break_link($content);
        $title      = "";

        if(!$newsFeedId) {
            $newsFeedId = $this->ci->newsfeed_model->getNewsfeedIdFromItem( $newsFeedItemId, ADD_POST_UPDATED );
        }

        $this->ci->postupdated_model->updateItem($member->id, $newsFeedId, $newsFeedItemId, $title, $content);

        $this->ci->media_model->saveMedia( $newsFeedId, $newsFeedItemId, 'post_update_id', $member->id );

        if($removeMedia) {
            //removeNewsFeedMedia($removeMedia, $newsFeedId, $member->id);
            $this->ci->newsfeed_model->deleteSelectedNewsfeedMedia( $member->id, $newsFeedId, $removeMedia );
        }

        // We delete user tag first, because user always send tags when they add / edit
        $this->ci->usertag_model->delete(array(
            "sourceId"      => $newsFeedId,
            "sourceType"    => NEWSFEED_USER_TAG,
            "userId"        => $member->id
        ));

        // Check and add user tag
        if( !empty($userTag) ) {

            $name_user_action   = $member->first_name . ' ' . $member->last_name;
            $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );
            $action_type = get_action_type(LIKE_POSTED_UPDATE);
            $arrUsers = json_decode($userTag);
            foreach( $arrUsers as $item ) {
                $this->ci->usertag_model->saveNew(array(
                    "sourceId"      => $newsFeedId,
                    "sourceType"    => NEWSFEED_USER_TAG,
                    "userTag"       => $item->userId,
                    "userId"        => $member->id,
                    "textRange"     => $item->textRange,
                    "created_date"  => now(),
                ));

                $data_push_tag = array(
                    'action_type'               => LIKE_POSTED_UPDATE,
                    'sender_id'                 => $member->id,
                    'sender_name'               => $name_user_action,
                    'receiver_id'               => $item->userId,
                    'type'                      => 'feed',
                    'post_id'                   => $newsFeedItemId,
                    'newsFeedItemId'            => $newsFeedItemId,
                    'newsFeedType'              => ADD_POST_UPDATED,
                    'newsFeedId'                => $newsFeedId,
                    'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                );
                $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push_tag, $action_type->id, $newsFeedItemId);
            }
        }

        return $this->ci->petnewsfeed->detail( $newsFeedId, $newsFeedItemId, ADD_POST_UPDATED, $member);
    }

    protected function getPostUpdatedTransformer($userId,$query) {
        if(!$query) {
            return array();
        }
        if($query->num_rows() > 0) {
            $results = $query->result();
            $arrFinalData = array();
            foreach( $results as $row ) {
                //echo json_encode($item);exit;
                $arrData = array();
                format_output_data($row);
                $arrData[NEWSFEED_TYPE]   = $row->newsFeedType;
                $arrData[NEWSFEED_ID]     = $row->id;
                $arrData[CREATED_TIME]    = $row->created_time;
                $arrData[CREATED_DATE]    = $row->created_date;
                $arrData[USER_TAG]        = $this->ci->usertag_model->getUserTag( $row->id, NEWSFEED_USER_TAG )->transformer();
                $arrData["newsFeedItemId"]  = $row->post_update_id;
                $arrData["totalLikes"]      = getNewsfeedTotalLike($row->post_update_id, $row->newsFeedType);
                $arrData["totalComments"]   = getNewsfeedTotalReview($row->post_update_id, $row->newsFeedType);;
                $arrData["hasLiked"]        = hasLikedNewsfeed( $row->post_update_id, $userId, $row->newsFeedType );
                $arrData["totalSharing"]    = getNewsfeedTotalSharing( $row->post_update_id, $row->newsFeedType );
                $postInfo                   = $this->ci->postupdated_model->item($row->post_update_id)->itemTransformer();
                $infoPhoto                  = $this->ci->newsfeed_model->getNewsfeedMedia($row->id)->getNewsfeedMediaTransformer();
                $postInfo[PHOTOS]           = isset($infoPhoto[PHOTOS]) ? $infoPhoto[PHOTOS] : array();
                $arrData[POST_UPDATED_INFO] = $postInfo;
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
                $arrFinalData[] = $arrData;    
            }
            return $arrFinalData;

        } else {
            return array();
        }
    }

    public function searchPostUpdated($member, $keyword, $option = ALL, $limit, $start) {
        if( $member ) {
            $query= $this->ci->postupdated_model->getPostUpdatedByKeyword( $member->id, $keyword , $option, $limit, $start );
            if($option == 'all') {
                return $query;
            } else {
                return $this->getPostUpdatedTransformer($member->id,$query);
            }
        } else {
            return array();
        }
    }
}
