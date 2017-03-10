<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Petreview {

    private $ci;

    function __construct($params = array()) {
        $this->ci = & get_instance();

        $this->ci->load->model('member_model');
        $this->ci->load->model('newsfeed_model');
        $this->ci->load->model('newsfeedlike_model');
        $this->ci->load->model('review_model');
        $this->ci->load->model('notification_model');
        $this->ci->load->model('listing_model');
        $this->ci->load->model('usertag_model');
        //load lang
        $this->ci->lang->load('api');
        //load helper
        $this->ci->load->helper(array('form', 'url'));
        $this->ci->load->helper('notification');
        $this->ci->load->helper('newsfeeds');

        $this->ci->load->library('petnewsfeed');
    }

    function saveNew($params, $member, $userTag = '') {

        $params['created_date'] = now();
        $params['status'] 		= 1;
        $created_by             = $member->id;

        $reviewId = $this->ci->member_model->add_user_review_listing($params);

        if( $reviewId ) {

            $newsFeedId = $this->ci->petnewsfeed->addNew(array(
                "review_id"     => $reviewId,
                "newsFeedType"  => ADD_REVIEW,
                "user_id"       => $member->id,
                "created_date"  => now(),
                "updated_date"  => now()
            ));

            //$this->ci->petnewsfeed->saveNewsFeedMedia( $newsFeedId, $reviewId, 'review_id', $member->id );
            $this->ci->media_model->saveMedia( $newsFeedId, $reviewId, 'review_id', $member->id );

            // push notification
            $name_user_action   = $member->first_name . ' ' . $member->last_name;

            $message            = $name_user_action .' '.lang('review on your listing');

            $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );

            $listingDetailObj   = $this->ci->listing_model->get_listing_detail($params['business_id'] , true , true , true, true , true);

            $receiverId         = $listingDetailObj->user_id;

            $action_type        = get_action_type(LIKE_REVIEW);

            $data_push = array(
                'action_type'               => LIKE_REVIEW,
                'sender_id'                 => $member->id,
                'sender_name'               => $name_user_action,
                'type'                      => 'feed',
                'review_id'                 => $reviewId,
                'newsFeedItemId'            => $reviewId,
                'newsFeedType'              => ADD_REVIEW,
                'newsFeedId'                => $newsFeedId,
                'bages_unread_notification' => count_unread_notification($receiverId) + 1,
            );

            $this->ci->notification_model->send_push_notification($receiverId, $message, $data_push, $action_type->id, $reviewId);

            // Check and add user tag
            if( !empty($userTag) ) {
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
                        'action_type'               => LIKE_REVIEW,
                        'sender_id'                 => $member->id,
                        'sender_name'               => $name_user_action,
                        'receiver_id'               => $item->userId,
                        'type'                      => 'feed',
                        'review_id'                 => $reviewId,
                        'newsFeedItemId'            => $reviewId,
                        'newsFeedType'              => ADD_REVIEW,
                        'newsFeedId'                => $newsFeedId,
                        'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                    );
                    $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push_tag, $action_type->id, $reviewId);
                }
            }

                // We should return news feed object here, because it has the photos now
            $newsFeedObj = $this->ci->petnewsfeed->detail( $newsFeedId, $reviewId, ADD_REVIEW, $member );

            return $newsFeedObj;

        } else {
            return -1;
        }
    }

    /**
     * @param $newsFeedId
     * @param $newsFeedItemId
     * @param $member
     * @param $content
     * @param $rate
     * @param array $removeMedia
     * @return mixed
     * @description: Update review and return an newsfeed review object
     */
    public function save( $newsFeedId, $newsFeedItemId, $member, $content, $rate, $removeMedia = '', $userTag = '' ) {

        $this->ci->newsfeed_model->updateReviewInfo($member->id, $newsFeedId, $newsFeedItemId, $content, $rate);

        //$this->ci->petnewsfeed->saveNewsFeedMedia( $newsFeedId, $newsFeedItemId, 'review_id', $member->id );
        $this->ci->media_model->saveMedia( $newsFeedId, $newsFeedItemId, 'review_id', $member->id );

        if($removeMedia) {
            //removeNewsFeedMedia($removeMedia, $newsFeedId, $member->id);
            $this->ci->newsfeed_model->deleteSelectedNewsfeedMedia( $member->id, $newsFeedId, $removeMedia );
        }

        $this->ci->usertag_model->delete(array(
            "sourceId"      => $newsFeedId,
            "sourceType"    => NEWSFEED_USER_TAG,
            "userId"        => $member->id
        ));

        // Check and add user tag
        if( !empty($userTag) ) {
            $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );
            $action_type        = get_action_type(LIKE_REVIEW);
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

                $data_push = array(
                    'action_type'               => LIKE_REVIEW,
                    'sender_id'                 => $member->id,
                    'sender_name'               => $member->first_name . ' ' . $member->last_name,
                    'receiver_id'               => $item->userId,
                    'type'                      => 'review',
                    'review_id'                 => $newsFeedItemId,
                    'newsFeedItemId'            => $newsFeedItemId,
                    'newsFeedType'              => ADD_REVIEW,
                    'newsFeedId'                => $newsFeedId,
                    'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                );
                $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push, $action_type->id, $newsFeedItemId);
            }
        }

        return $this->ci->petnewsfeed->detail( $newsFeedId, $newsFeedItemId, ADD_REVIEW, $member );
    }

    public function delete() {

    }

    public function searchPreviews($member, $keyword, $option = ALL, $limit, $start,$user_location=array()) {
        $response = array();

        $results = $this->ci->review_model->searchPreviews($member, $keyword, 'total', $start, $limit,$user_location );
        $total  = $this->ci->review_model->searchPreviews($member, $keyword, 'count',$start, $limit,$user_location );
        $response[ITEMS]        = $results;
        $response[TOTAL_ITEM]   = $total;
        $response[TOTAL_PAGE]   = $total > 0 ? ceil(intval($total) / $limit) : 0;
        $response[LIMIT]        = intval($limit);
        return $response; 
    }
}
?>