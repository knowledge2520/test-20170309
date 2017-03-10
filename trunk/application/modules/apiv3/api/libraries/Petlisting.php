<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Petlisting {

    private $ci;

    function __construct($params = array()) {
        $this->ci = & get_instance();

        //load lang
        $this->ci->lang->load('api');
        $this->ci->load->model('notification_model');
        //load helper
        $this->ci->load->helper(array('form', 'url'));
        $this->ci->load->helper('newsfeeds');
        $this->ci->load->helper('notification');

        $this->ci->load->library('petnewsfeed');
    }

    function saveNew( $listingId, $member, $userTag = '' ) {

        // add listing photo album
        $this->ci->db->insert("listing_album_photo", array("business_id" => $listingId, "created_date" => now()));

        $listingAlbumId = $this->ci->db->insert_id();

        $newsFeedId = $this->ci->petnewsfeed->addNew(array(
            "photo_listing_id"  => $listingAlbumId,
            "newsFeedType"      => ADD_PHOTO_LISTING,
            "user_id"           => $member->id,
            "created_date"      => now(),
            "updated_date"      => now()
        ));

        //$this->ci->petnewsfeed->saveNewsFeedMedia( $newsFeedId, $listingId, 'business_id', $member->id, 1 );
        $this->ci->media_model->saveMedia( $newsFeedId, $listingId, 'business_id', $member->id );

        // Check and add user tag
        if( !empty($userTag) ) {
            $arrUsers = json_decode($userTag);
            $name_user_action   = $member->first_name . ' ' . $member->last_name;
            $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );
            $action_type = get_action_type(LIKE_LISTING_PHOTO);
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
                    'action_type'               => LIKE_LISTING_PHOTO,
                    'sender_id'                 => $member->id,
                    'sender_name'               => $name_user_action,
                    'type'                      => 'feed',
                    'album_id'                  => $listingAlbumId,
                    'newsFeedItemId'            => $listingAlbumId,
                    'newsFeedType'              => ADD_PHOTO_LISTING,
                    'newsFeedId'                => $newsFeedId,
                    'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                );
                $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push_tag, $action_type->id, $listingAlbumId);
            }
        }

        return $this->ci->petnewsfeed->detail( $newsFeedId, $listingAlbumId, ADD_PHOTO_LISTING, $member);
    }
}