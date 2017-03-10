<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Petcheckin {

    private $ci;

    function __construct($params = array()) {

        $this->ci = & get_instance();

        $this->ci->load->model('member_model');
        $this->ci->load->model('newsfeed_model');
        $this->ci->load->model('usertag_model');
        //load lang
        $this->ci->lang->load('api');

        $this->ci->load->library('petnewsfeed');

        //load helper
        $this->ci->load->helper(array('form', 'url'));
    }

    function saveNew( $params, $member, $userTag = '' ) {

        $params['created_date'] = now();
        $params['user_id'] 		= $member->id;

        $checkinId = $this->ci->member_model->add_user_checkin_listing($params);

        $newsFeedId = $this->ci->petnewsfeed->addNew(array(
            "checkin_id"    => $checkinId,
            "newsFeedType"  => ADD_CHECKIN,
            "user_id"       => $member->id,
            "created_date"  => now(),
            "updated_date"  => now()
        ));

        // Check and add user tag
        if( !empty($userTag) ) {
            $action_type = get_action_type(LIKE_CHECKIN);
            $messagePush = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );
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
                $dataPush = array(
                    'action_type'               => LIKE_CHECKIN,
                    'sender_id'                 => $member->id,
                    'sender_name'               => $member->first_name . ' ' . $member->last_name,
                    'receiver_id'               => $item->userId,
                    'type'                      => 'feed',
                    'checkin_id'                => $checkinId,
                    'newsFeedItemId'            => $checkinId,
                    'newsFeedId'                => $newsFeedId,
                    'newsFeedType'              => ADD_CHECKIN,
                    'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                );
                $this->ci->notification_model->send_push_notification($item->userId, $messagePush, $dataPush, $action_type->id, $checkinId);
            }
        }

        $newsFeedObj = $this->ci->petnewsfeed->detail( $newsFeedId, $checkinId, ADD_CHECKIN, $member );

        return $newsFeedObj;
    }

    /**
     * @param $newsFeedId
     * @param $newsFeedItemId
     * @param $member
     * @param $comment
     * @return mixed
     * @description: Update checkin and return an newsfeed checkin object
     */
    public function save( $newsFeedId = false, $newsFeedItemId, $member, $comment, $userTag = '' ) {

        if(!$newsFeedId) {
            $newsFeedId = $this->ci->newsfeed_model->getNewsfeedIdFromItem( $newsFeedItemId, ADD_CHECKIN );
        }
        $this->ci->newsfeed_model->updateCheckinInfo($member->id, $newsFeedId, $newsFeedItemId, $comment);

        $this->ci->usertag_model->delete(array(
            "sourceId"      => $newsFeedId,
            "sourceType"    => NEWSFEED_USER_TAG,
            "userId"        => $member->id
        ));

        // Check and add user tag
        if( !empty($userTag) ) {

            $action_type = get_action_type(LIKE_CHECKIN);
            $messagePush = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );
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
                $dataPush = array(
                    'action_type'               => LIKE_CHECKIN,
                    'sender_id'                 => $member->id,
                    'sender_name'               => $member->first_name . ' ' . $member->last_name,
                    'receiver_id'               => $item->userId,
                    'type'                      => 'feed',
                    'checkin_id'                => $newsFeedItemId,
                    'newsFeedItemId'            => $newsFeedItemId,
                    'newsFeedId'                => $newsFeedId,
                    'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                );
                //$this->ci->notification_model->send_push_notification($item->userId, $messagePush, $dataPush, $action_type->id, $newsFeedItemId);
            }
        }

        return $this->ci->petnewsfeed->detail( $newsFeedId, $newsFeedItemId, ADD_CHECKIN, $member );
    }
}