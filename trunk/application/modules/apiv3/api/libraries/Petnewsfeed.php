<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Petnewsfeed {

    private $ci;

    protected $newsfeedMedia;

    protected $_member;

    function __construct($params = array()) {

        $this->ci = & get_instance();

        $this->ci->load->model('newsfeed_model');
        $this->ci->load->model('newsfeedlike_model');
        $this->ci->load->model('postupdated_model');
        $this->ci->load->model('listing_model');
        $this->ci->load->model('checkin_model');
        $this->ci->load->model('review_model');
        $this->ci->load->model('pettalk_model');
        $this->ci->load->model('pettalkinfo_model');
        $this->ci->load->model('usertag_model');
        $this->ci->load->model('notification_model');
        $this->ci->load->model('member_model');

        $this->ci->lang->load('api');
        //load helper
        $this->ci->load->helper(array('form', 'url'));

        $this->ci->load->library('petupload');
        //$this->ci->load->library('pettopic');

        $this->_member = $params["member"];

    }

    public function getNewsfeedHome($member, $start = 0, $limit = API_NUM_RECORD_PER_PAGE) {


        /*$data["items"]          = $this->ci->newsfeed_model->getNewsFeedHome('all', $member->id, $limit, $start);
        $data["totalItem"]      = $this->ci->newsfeed_model->getNewsFeedHome('count', $member->id, $limit, $start);
        $data['totalPage']      = ceil(intval($data['totalItem']) / $limit);
        $data['limit']          = intval($limit);*/

        $data[ITEMS]            = $this->ci->newsfeed_model->getNewsFeedHome('all', $member->id, $limit, $start)->getNewsfeedListingTransformer($member->id);
        $data[TOTAL_ITEM]       = $this->ci->newsfeed_model->getNewsFeedHome('count', $member->id, $limit, $start);
        $data[TOTAL_PAGE]       = ceil(intval($data[TOTAL_ITEM]) / $limit);
        $data[LIMIT]            = intval($limit);
        return $data;
    }

    public function getNewsfeedMe($member, $userId, $start = 0, $limit = API_NUM_RECORD_PER_PAGE) {
        $isFriend = $this->ci->member_model->get_friend_status($member->id, $userId);

        if($userId == $member->id || $isFriend == 1) {
            $data[ITEMS]            = $this->ci->newsfeed_model->getNewsFeedMe('all', $userId, $limit, $start)->getNewsfeedListingTransformer($member->id);
            $data[TOTAL_ITEM]       = $this->ci->newsfeed_model->getNewsFeedMe('count', $userId, $limit, $start);
        } else {
            $data[ITEMS]            = $this->ci->newsfeed_model->getNewsFeedMeFilter('all', $userId, $member->id, $limit, $start)->getNewsfeedListingTransformer($member->id);
            $data[TOTAL_ITEM]       = $this->ci->newsfeed_model->getNewsFeedMeFilter('count', $userId, $member->id, $limit, $start);
        }

        $data[TOTAL_PAGE]       = ceil(intval($data[TOTAL_ITEM]) / $limit);
        $data[LIMIT]            = intval($limit);
        return $data;
    }

    public function detail( $newsFeedId = false, $newFeedItemId = false, $newsFeedType, $memberObj = false ) {

        if( !$newsFeedId ) {
            $newsFeedObj = $this->ci->newsfeed_model->getNewsfeedFromItem( $newFeedItemId, $newsFeedType );

            $newsFeedId = $newsFeedObj->id;
        }
        $result = $this->ci->newsfeed_model->detail( $newsFeedId, $memberObj->id );

        return $result;
    }

    /**
     * @param $params
     * @return mixed
     * @description: Add new data into user_newsfeeds_activities
     */
    public function addNew($params) {
        $newsFeedId = $this->ci->newsfeed_model->saveNew($params);

        return $newsFeedId;
    }

    public function saveNewsFeedMedia( $newsFeedId, $newsFeedItemId, $field, $userId, $status = 1 ) {
        // upload new photos

        $media_files = $this->ci->petupload->doMultiUpload($this->ci->config->item('listings_path'));

        if ($media_files) {

            $dataInsert = array();

            foreach ($media_files as $file) {

                $media_insert = array();

                $file_array = $this->ci->config->item('upload') == 's3-aws' ? $file : $file['upload_data'];
                $source     = $this->ci->config->item('upload') == 's3-aws' ? $file['uri'] : $this->ci->config->item('api_upload_path') . $this->ci->config->item('listings_path') . $file_array['file_name'];

                $media_insert[$field]       = $newsFeedItemId;
                $media_insert["newfeed_id"] = $newsFeedId;
                $media_insert['source']     = $source;
                $media_insert['created_date'] = now();
                $media_insert['status']     = $status;
                $media_insert['user_id']    = $userId;
                $media_insert['type']       = 'PHOTO';

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

                    // Get W, H of uploaded image for: original and thumb file
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
    }

    public function sharingNewsFeed( $newsFeedId = false, $newsFeedItemId, $newsFeedType, $member ) {

        if( !$newsFeedId ) {
            $newsFeedId = $this->ci->newsfeed_model->getNewsfeedIdFromItem( $newsFeedItemId, $newsFeedType );
        }

        $params = array(
            "newsFeedId"    => $newsFeedId,
            "newsFeedType"  => $newsFeedType,
            "newsFeedItemId"=> $newsFeedItemId,
            "userId"        => $member->id,
            "created_date"  => now()
        );

        $this->ci->newsfeed_model->saveNewsFeedSharing($params);
    }

    public function delete( $newsFeedId = false, $newsFeedItemId = false, $newsFeedType, $member ) {
        $newsfeed = $this->ci->newsfeed_model->getNewsfeedFromItem( $newsFeedItemId, $newsFeedType, $member->id );

        if($newsfeed) {
            if( !$newsFeedId ) {
                $newsFeedId = $newsfeed->id;
            }

            // Remove like news feed of checkin
            $this->ci->newsfeedlike_model->deleteLikeByNewsfeed($newsFeedItemId, $newsFeedType);

            // Remove comment news feed of checkin and the comment's photo
            $this->ci->newsfeedlike_model->deleteCommentByNewsfeed($newsFeedItemId, $newsFeedType);

            // Remove user tags
            $this->ci->usertag_model->delete(array("sourceId" => $newsFeedId, "sourceType" => NEWSFEED_USER_TAG));

            // Remove review photos
            $this->ci->newsfeed_model->deleteNewsfeedMedia( $member->id, $newsFeedId );

            // Remove sharing data
            $this->ci->newsfeed_model->deleteSharingInfo( $newsFeedId, $newsFeedItemId, $newsFeedType );

            // Remove user newsfeed activities
            $this->ci->newsfeed_model->deleteNewsfeed( $newsFeedId, $newsFeedItemId, $newsFeedType, $member->id );

            switch( $newsFeedType ) {

                case ADD_PETTALK_ADOPTION:
                case ADD_PETTALK_FOUND_REPORT:
                case ADD_PETTALK_LOST_REPORT:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"pettalk_info_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->pettalkinfo_model->deletePettalkInfo($newsFeedItemId, $member->id);
                    break;

                case ADD_POST_UPDATED:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"post_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->postupdated_model->deletePost($newsFeedItemId, $member->id);
                    break;

                case ADD_CHECKIN:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"checkin_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->checkin_model->delete( $newsFeedItemId, $member->id );
                    break;

                case ADD_PET_TOPIC:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"topic_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->pettalk_model->deleteTopic( $newsFeedItemId, $member->id );
                    break;

                case ADD_REVIEW:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"review_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->review_model->delete( $newsFeedItemId, $member->id );
                    break;

                case ADD_PHOTO_LISTING:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"album_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->listing_model->deleteListingPhoto( $newsFeedItemId );
                    break;

                case ADD_SHARING_PHOTO:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"sharing_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->photosharing_model->deleteSharingPhoto( $newsFeedItemId, $member->id );
                    break;
            }
        }
    }

    public function detail_preview( $newsFeedId, $memberObj = false,$previewId ) {
        $result = $this->ci->newsfeed_model->detail( $newsFeedId, $memberObj->id,$previewId );
        return $result;
    }
}

?>