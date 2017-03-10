<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Petmember {

    private $ci;

    function __construct($params = array()) {

        $this->ci = & get_instance();

        $this->ci->load->model('member_model');
        $this->ci->load->model('usercontact_model');
        $this->ci->load->model('media_model');
        $this->ci->load->model('notification_model');
    }

    function getUserPhotos( $start = 0, $limit = false, $userId = false, $businessId = false ) {

        $items 		        = $this->ci->member_model->get_user_photos_v4('all', $start, $limit, $userId, $businessId );
        $data['totalItem'] 	= $this->ci->member_model->get_user_photos_v4('count', $start, $limit, $userId, $businessId );
        $data['totalPage']	= ceil(intval($data['totalItem']) / $limit);
        $data['limit']		= intval($limit);

        foreach ($items as &$item) {
            format_output_data($item);
            $userInfo       = $this->ci->member_model->getMemberByMemberID($userId, true, true, true, true, $userId);
            format_output_data($userInfo);
            $item->userInfo = $userInfo;
        }
        $data[ITEMS] = $items;
        return $data;
    }

    public function updateMemberProfile( $params = false, $member ) {
        $data       = array();
        $removeMedia= array();

        if($_FILES) {
            foreach ($_FILES as $key => $file) {
                if ((!empty($file) && $file['error'] == 0) && ($key == 'file' || $key = 'background')) {
                    $mediaFiles = $this->ci->media_model->S3Upload( $file, $key, '' );
                    if($key == 'file') {
                        $data['profile_photo'] 		        = $mediaFiles['uri'];
                        $data['profile_photo_thumb']        = $mediaFiles['uri_thumb'];
                        $removeMedia['profile_photo']       = $member->profile_photo;
                        $removeMedia['profile_photo_thumb'] = $member->profile_photo_thumb;
                    } elseif($key == 'background') {
                        $data['profile_background'] 		     = $mediaFiles['uri'];
                        $data['profile_background_thumb']	     = $mediaFiles['uri_thumb'];
                        $removeMedia['profile_background']       = $member->profile_background;
                        $removeMedia['profile_background_thumb'] = $member->profile_background_thumb;
                    }
                }
            }
        }

        if($params) {
            if( !empty($params['first_name']) ) {
                $data['first_name'] = $params['first_name'];
            }
            if( !empty($params['last_name']) ) {
                $data['last_name'] = $params['last_name'];
            }
            if( !empty($params['dob']) ) {
                $data['dob'] = $params['dob'];
            }
            if( isset($params['gender']) && $params['gender'] !== FALSE && $params['gender'] !== NULL) {
                $data['gender'] = $params['gender'];
            }
            // update display name in user notification
            if( !empty($params['display_name']) && ( $params['display_name'] != $member->display_name ) ){
                $this->ci->notification_model->updateNotificationSenderName($params['display_name'], $member);
                $data['display_name'] = $params['display_name'];
            }
        }

        $this->ci->member_model->update($data, $member->id);

        // remove old profile photos
        $this->ci->media_model->removeByKeyValue($removeMedia);

        $user = $this->ci->member_model->getMemberByMemberID($member->id , true , true , true , true);

        return $user;
    }
}