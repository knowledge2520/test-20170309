<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Friendnearby {

    private $ci;

    protected $error;

    protected $_member;

    function __construct($params = array()) {

        $this->ci = & get_instance();

        $this->ci->load->model('nearbystatus_model');
        $this->ci->load->model('member_model');
        $this->ci->load->model('review_model');
        $this->_member = $params["member"];

        $error = array();
    }

    public function items() {

        $queryStatus = $this->ci->nearbystatus_model->items();

        $results    = $this->statusItemsTransformer($queryStatus);

        return $results;
    }

    public function setNearbyStatus($memberObj, $statusId) {

        $data = array(
            "user_nearby_status" => $statusId
        );

        $this->ci->member_model->update_user_options($data, $memberObj->id);

        $queryStatus = $this->ci->nearbystatus_model->item($statusId);

        return $this->statusItemTransformer($queryStatus);
    }

    public function setVisibleStatus($memberObj, $statusId) {

        $data = array(
            "user_nearby_visible" => $statusId
        );

        $this->ci->member_model->update_user_options($data, $memberObj->id);

        return true;
    }

    public function setUserLocation($member, $gpsLat, $gpsLong) {
        if(!$member){
            return false;
        }

        // Check & add new user option table
        // check user options
        $user_options = $this->ci->member_model->get_user_options($member->id);

        if (empty ($user_options)) {
            // $this->member_model->add_user_options(array('user_id'=>$member->id));
            $data_option = array(
                'user_id' => $member->id,
                'location_lock' => 'off'
            );
            $this->ci->member_model->add_user_options($data_option);
        }

        if( $gpsLat && $gpsLong && $gpsLat != 0 && $gpsLong != 0) {
            // Check & set location in user location table
            $this->ci->nearbystatus_model->setUserLocation($member, $gpsLat, $gpsLong);            
            $this->ci->member_model->update_user_options(array('location_city' => $gpsLat . ',' . $gpsLong), $member->id);
        }
    }

    public function getUsersNearby($member, $gpsLat, $gpsLong, $option = ALL, $limit, $start) {

        if( $member ) {

            // Need to check the current status of logged in user. If he turn "visibility" to
            // off. He cannot interact with the other users
            $userOptions = $this->ci->member_model->get_user_options($member->id);

            if( $userOptions && $userOptions->user_nearby_status == INVISIBLE_MODE ) {
                return -1;
            }

            $queryLocation = $this->ci->nearbystatus_model->getUsersNearby( $member->id, $gpsLat, $gpsLong, $option, $limit, $start );

            if($option == 'all') {
                return $queryLocation;
            } else {
                return $this->getUserNearbyTransformer($queryLocation);
            }
        } else {
            return array();
        }
    }

    protected  function statusItemsTransformer($query) {
        if($query->num_rows() > 0) {

            $results = $query->result();

            $arrData = array();

            foreach($results as $item) {
                $arrTemp = array(
                    ID      => $item->id,
                    TITLE   => $item->title,
                    CONTENT => $item->content,
                );
                $arrData[ITEMS][] = $arrTemp;
            }
            return $arrData;

        } else {
            return array();
        }
    }

    protected function statusItemTransformer($query) {
        if($query->num_rows() > 0) {

            $item = $query->row();

            return array(
                ITEM => array(
                    ID      => $item->id,
                    TITLE   => $item->title,
                    CONTENT => $item->content,
                )
            );

        } else {
            return array();
        }
    }

    protected function getUserNearbyTransformer($query) {

        if(!$query) {
            return array();
        }

        if($query->num_rows() > 0) {

            $results = $query->result();

            $arrData = array();

            foreach( $results as $item ) {
                format_output_data($item);

                $arrData[] = array(
                    USER_INFO => array(
                        ID          => $item->userId,
                        FIRST_NAME  => $item->first_name,
                        LAST_NAME   => $item->last_name,
                        PROFILE_PHOTOS => array(
                            $item->profile_photo,
                            $item->profile_photo_thumb
                        ),
                    ),
                    STATUS_INFO => array(
                        ID      => $item->statusId,
                        TITLE   => $item->statusTitle,
                    ),
                    DISTANCE    => $item->distance,
                    LATITUDE    => $item->latitude,
                    LONGITUDE   => $item->longitude,
                    IS_FRIEND   => (int)$item->isFriend,
                );
            }
            return $arrData;

        } else {
            return array();
        }
    }

    protected function getUsersTransformer($user_id,$query) {

        if(!$query) {
            return array();
        }
        if($query->num_rows() > 0) {
            $results = $query->result();
            $arrData = array();
            foreach( $results as $row ) {
                //load member reviews
                $total_reviews = $this->ci->review_model->get_reviews_by_user('count', 0 , false , $user_id);
                $row->total_reviews = $total_reviews;
           
                $total_checkins = $this->ci->member_model->get_checkins_by_user('count',0,false,$user_id);
                $row->total_checkins = $total_checkins;
            
                $total_photos = $this->ci->member_model->get_user_photos_v4('count', 0, API_NUM_RECORD_PER_PAGE, $user_id, false);
                $row->total_photos = $total_photos;
                         
                $total_friends      = $this->ci->member_model->get_user_friends('count',$user_id,1);
                $row->total_friends = $total_friends;
                $arrData[] = $row;
            }
            return $arrData;
        } else {
            return array();
        }
    }

    public function searchUsers($member, $keyword, $option = ALL, $limit, $start) {
        if( $member ) {
            $query= $this->ci->nearbystatus_model->getUsersByKeyword( $member->id, $keyword , $option, $limit, $start );
            if($option == 'all') {
                return $query;
            } else {
                return $this->getUsersTransformer($member->id,$query);
            }
        } else {
            return array();
        }
    }

}