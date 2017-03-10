<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/modules/api/api/libraries/REST_Controller.php';

class Newsfeed extends REST_Controller {

    function __construct() {

        // Construct our parent class
        parent::__construct();

        $this->load->library('parse');
        $this->load->library('petcomment');
        $this->load->library('petnewsfeed');
        $this->load->library('petpostupdated');
        $this->load->library('petcheckin');
        $this->load->library('pettopic');
        $this->load->library('petreview');
        $this->load->model('review_model');
        //load lang
        $this->lang->load('api');
        //load helper
        $this->load->helper(array('form', 'url'));

        $this->load->helper('site');
        $this->load->helper('newsfeeds');
        $this->load->helper('notification');
    }

    /**
     * @description: Get newsfeed detail
     */
    public function detail_post() {

        $this->_requireAuthToken();

        $newsFeedId = $this->post('newsFeedId') ? $this->post('newsFeedId') : false;

        $newFeedItemId      = $this->post('newsFeedItemId') ? $this->post('newsFeedItemId') : null;

        $newsFeedType       = $this->post('newsFeedType') ? $this->post('newsFeedType') : null;

        $item               = $this->petnewsfeed->detail( $newsFeedId, $newFeedItemId, $newsFeedType, $this->_member );

        if(count($item)) {
            $response[ITEM]     = $item;
            $this->response($response, 200);
        } else {
            $error['msg'] = "Item not found";
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error, 200);
        }
    }

    /**
     * @description: Get newsfeed Me
     */
    function getNewsfeed_post() {

        $this->_requireAuthToken();

        $userId = $this->post('userId') ? $this->post('userId') : null;

        if( !$userId ) {
            $userId = $this->_member->id;
        }

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        $data = $this->petnewsfeed->getNewsfeedMe( $this->_member, $userId, $start, $limit );

        $this->response($data, 200);
    }

    /**
     * @description: Get newsfeed home
     */
    public function getNewsFeedHome_post() {

        $this->_requireAuthToken();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        $data = $this->petnewsfeed->getNewsfeedHome( $this->_member, $start, $limit );

        $this->response($data, 200);
    }

    /**
     * @description: like a newsfeed
     */
    function like_post() {

        $this->_requireAuthToken();

        $newsFeedId       = $this->post('newsFeedId') ? $this->post('newsFeedId') : false;

        $newFeedItemId    = $this->post('newsFeedItemId') ? $this->post('newsFeedItemId') : false;

        $newsFeedType     = $this->post('newsFeedType') ? $this->post('newsFeedType') : false;

        $receiverId       = $this->post('userId') ? $this->post('userId') : false;

        if( !$newFeedItemId ) {
            $error['msg'] = "Please input news feed item ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if( !$newsFeedType ) {
            $error['msg'] = "Please input news feed type";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if( !$receiverId ) {
            $error['msg'] = "Please input receiver ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $this->petcomment->like( $newsFeedId, $newFeedItemId, $newsFeedType, $receiverId, $this->_member );
        $this->response(array(), 200);
    }

    /**
     * @description: dislike a newsfeed
     */
    function dislike_post() {

        $this->_requireAuthToken();

        $newsFeedId     = $this->post('newsFeedId') ? $this->post('newsFeedId') : false;

        $newFeedItemId  = $this->post('newsFeedItemId') ? $this->post('newsFeedItemId') : false;

        $newsFeedType   = $this->post('newsFeedType') ? $this->post('newsFeedType') : false;

        if( !$newFeedItemId ) {
            $error['msg'] = "Please input news feed item ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $this->petcomment->dislike( $newFeedItemId, $newsFeedType, $this->_member );
        $this->response(array(), 200);
    }

    /**
     * @description: write a comment for a newsfeed
     */
    public function writeComment_post() {
        $this->_requireAuthToken();

        $newsFeedId     = $this->post('newsFeedId') ? $this->post('newsFeedId') : null;

        $newFeedItemId  = $this->post('newsFeedItemId') ? $this->post('newsFeedItemId') : null;

        $newsFeedType   = $this->post('newsFeedType') ? $this->post('newsFeedType') : null;

        $comment        = $this->post('comment') ? $this->post('comment') : "";

        $receiverId     = $this->post('userId') ? $this->post('userId') : null;

        $userTag        = $this->post('tags') ? $this->post('tags') : "";

        if(!$newsFeedId) {
            $error['msg'] = "Please input news feed ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if(!$newFeedItemId) {
            $error['msg'] = "Please input news feed item ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if(!$newsFeedType) {
            $error['msg'] = "Please input news feed type";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if(!$receiverId) {
            $error['msg'] = "Please input user ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if(!(isset($_FILES) && !empty($_FILES))) {

            if(!$comment) {
                $error['msg'] = "Please input comment";
                $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
                $this->response($error, 200);
            }
        }

        $arrData = array(
            "newsFeedId"    => $newsFeedId,
            "newsFeedItemId"=> $newFeedItemId,
            "newsFeedType"  => $newsFeedType,
            "content"       => $comment,
            "receicerId"    => $receiverId
        );

        $data[ITEM] = $this->petcomment->saveNew( $arrData, $this->_member, $userTag );

        $this->response($data, 200);
    }

    /**
     * @description: edit a comment
     */
    function editComment_post() {
        $this->_requireAuthToken();

        $newFeedId      = $this->post('newsFeedId') ? $this->post('newsFeedId') : null;

        $newFeedItemId  = $this->post('newsFeedItemId') ? $this->post('newsFeedItemId') : null;

        $newsFeedType   = $this->post('newsFeedType') ? $this->post('newsFeedType') : null;

        $commentId      = $this->post('commentId') ? $this->post('commentId') : null;

        $comment        = $this->post('comment') ? $this->post('comment') : null;

        $removeMedia    = $this->post('removeMedia') ? $this->post('removeMedia') : null;

        $userTag        = $this->post('tags') ? $this->post('tags') : "";

        if(!$commentId) {
            $error['msg'] = "Please input comment ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if(!$newsFeedType) {
            $error['msg'] = "Please input newsfeed type";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if(!$newFeedItemId) {
            $error['msg'] = "Please input newsfeed item ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $arrParams = array(
            "commentId"     => $commentId,
            "content"       => $comment,
            "newsFeedType"  => $newsFeedType,
            "newsFeedId"    => $newFeedId,
            "newsFeedItemId"=> $newFeedItemId,
            "removeMedia"   => $removeMedia,
        );

        $data[ITEM] = $this->petcomment->save( $arrParams, $this->_member, $userTag );

        $this->response($data, 200);
    }

    /**
     * @description: delete a comment
     */
    function deleteComment_post() {

        $this->_requireAuthToken();

        $commentId     = $this->post('commentId') ? $this->post('commentId') : null;

        if( !$commentId ) {
            $error['msg'] = "Please input news feed ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }
        $result = $this->petcomment->delete( $commentId, $this->_member );

        if( $result == -1 ) {
            $error['msg'] = "Comment not found";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        } elseif($result == -2 ) {
            $error['msg'] = "You do not have permission to delete this comment";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        } else {
            $this->response(array(), 200);
        }
    }

    function getNewsfeedComment_post() {

        $this->_requireAuthToken();

        $newsFeedId     = $this->post('newsFeedId') ? $this->post('newsFeedId') : null;

        $newsFeedItemId = $this->post('newsFeedItemId') ? $this->post('newsFeedItemId') : null;

        $newsFeedType   = $this->post('newsFeedType') ? $this->post('newsFeedType') : null;

        $start = $this->post('start') ? $this->post('start') : 0;

        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        if( !$newsFeedId ) {
            $error['msg'] = "Please input news feed ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if( !$newsFeedItemId ) {
            $error['msg'] = "Please input news feed item ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if( !$newsFeedType ) {
            $error['msg'] = "Please input news feed type";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $data = $this->petcomment->getNewsfeedComments( $newsFeedId, $newsFeedItemId, $newsFeedType, $limit, $start );
        $this->response($data, 200);
    }

    /**
     * @description: edit newsfeed
     */
    function editNewsfeed_post() {

        $this->_requireAuthToken();

        $newsFeedId      = $this->post('newsFeedId') ? $this->post('newsFeedId') : false;

        $newsFeedItemId  = $this->post('newsFeedItemId') ? $this->post('newsFeedItemId') : false;

        $newsFeedType    = $this->post('newsFeedType') ? $this->post('newsFeedType') : false;

        $removeMedia     = $this->post('removeMedia') ? $this->post('removeMedia') : '';

        if( !$newsFeedItemId ) {
            $error['msg'] = "Please input news feed item ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if( !$newsFeedType ) {
            $error['msg'] = "Please input news feed type";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        switch( $newsFeedType ) {

            case ADD_PETTALK_LOST_REPORT:
            case ADD_PETTALK_FOUND_REPORT:
            case ADD_PETTALK_ADOPTION:
                $params = $this->post();
                $data[ITEM] = $this->pettopic->savePetInfo( $newsFeedId, $newsFeedItemId, $newsFeedType, $params, $this->_member, $removeMedia );
                $this->response($data, 200);
                break;

            case ADD_POST_UPDATED:
                $content    = $this->post('content') ? $this->post('content') : null;
                $userTag        = $this->post('tags') ? $this->post('tags') : "";
                $data[ITEM] = $this->petpostupdated->save( $newsFeedId, $newsFeedItemId, $this->_member, $content, $removeMedia, $userTag );
                $this->response($data, 200);
                break;

            case ADD_CHECKIN:
                $comment    = $this->post('comment') ? $this->post('comment') : null;
                $userTag    = $this->post('tags') ? $this->post('tags') : "";
                $data[ITEM] = $this->petcheckin->save( $newsFeedId, $newsFeedItemId, $this->_member, $comment, $userTag );
                $this->response($data, 200);
                break;

            case ADD_PET_TOPIC:
                $title          = $this->post('title') ? $this->post('title') : null;
                $content        = $this->post('content') ? $this->post('content') : null;
                $catId          = $this->post('category_id') ? $this->post('category_id') : null;
                $userTag        = $this->post('tags') ? $this->post('tags') : "";

                if( !$title ) {
                    $error['msg'] = "Please input topic title";
                    $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
                    $this->response($error, 200);
                }

                /*if( !$content ) {
                    $error['msg'] = "Please input topic content";
                    $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
                    $this->response($error, 200);
                }*/

                if( !$catId ) {
                    $error['msg'] = "Please input topic category";
                    $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
                    $this->response($error, 200);
                }
                $data[ITEM] = $this->pettopic->save( $newsFeedId, $newsFeedItemId, $this->_member, $title, $content, $catId, $removeMedia, $userTag );
                $this->response($data, 200);
                break;

            case ADD_REVIEW:
                $content    = $this->post('content') ? $this->post('content') : null;
                $rate       = $this->post('rate') ? $this->post('rate') : null;
                $userTag    = $this->post('tags') ? $this->post('tags') : "";
                $data[ITEM] = $this->petreview->save( $newsFeedId, $newsFeedItemId, $this->_member, $content, $rate, $removeMedia, $userTag );
                $this->response($data, 200);
                break;
        }
    }

    /**
     * @description: delete a newsfeed
     */
    public function deleteNewsfeed_post() {

        $this->_requireAuthToken();

        $newsFeedId      = $this->post('newsFeedId') ? $this->post('newsFeedId') : null;

        $newsFeedItemId  = $this->post('newsFeedItemId') ? $this->post('newsFeedItemId') : null;

        $newsFeedType    = $this->post('newsFeedType') ? $this->post('newsFeedType') : null;

        if( !$newsFeedItemId ) {
            $error['msg'] = "Please input news feed item ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if( !$newsFeedType ) {
            $error['msg'] = "Please input news feed type";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $this->petnewsfeed->delete( $newsFeedId, $newsFeedItemId, $newsFeedType, $this->_member );
        $this->response(array(), 200);
    }

    /**
     * @description: This API is called when user shares a news feed successully
     * on social network
     */
    public function sharingNewsFeed_post() {
        $this->_requireAuthToken();

        $newsFeedId      = $this->post('newsFeedId') ? $this->post('newsFeedId') : false;

        $newsFeedItemId  = $this->post('newsFeedItemId') ? $this->post('newsFeedItemId') : false;

        $newsFeedType    = $this->post('newsFeedType') ? $this->post('newsFeedType') : false;

        if( !$newsFeedItemId ) {
            $error['msg'] = "Please input news feed item ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if( !$newsFeedType ) {
            $error['msg'] = "Please input news feed type";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $this->petnewsfeed->sharingNewsFeed( $newsFeedId, $newsFeedItemId, $newsFeedType, $this->_member );

        $this->response(array(), 200);
    }

    public function search_post() {
        $this->_requireAuthToken();
        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword        = $this->post('keyword') ? trim($this->post('keyword')) : "";
        $search_distance = DISTANCE_LOCATION; // 60km;
        if(empty($keyword)) {
            $error['msg'] = "Keyword is required";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }
        $user_location_latitude = false;
        $user_location_longitude = false;
        $user_options = $this->member_model->get_user_options($this->_member->id);
        if ($user_options) {
            $option_lock = $user_options->location_lock;
            //if ($option_lock == 'on') {
                $user_location_latitude = ! empty ( $user_options->location_city ) && explode ( ',', $user_options->location_city ) [0] ? trim ( explode ( ',', $user_options->location_city ) [0] ) : false;
                $user_location_longitude = ! empty ( $user_options->location_city ) && explode ( ',', $user_options->location_city ) [1] ? trim ( explode ( ',', $user_options->location_city ) [1] ) : false;
            //}
        }
        
        $user_location = array (
                'latitude' => $user_location_latitude,
                'longitude' => $user_location_longitude,
                'search_distance' => $search_distance 
        );
        $response = $this->petreview->searchPreviews($this->_member, $keyword, 'items', $limit, $start,$user_location);

        

        $data['items'] = $this->review_model->searchPreviews($this->_member, $keyword, 'total', $start, $limit,$user_location );
        $data['totalItem'] = $this->review_model->searchPreviews($this->_member, $keyword, 'count', $start, $limit,$user_location );
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);
        //echo json_encode($data['items']);
        if (!empty($data['items'])) {
            $newData = array();
            foreach ($data['items'] as $key => $listing) {
                $item               = $this->petnewsfeed->detail_preview( $listing['id'], $this->_member, $listing['id'] );
                $newData[] = $item;
            }
            $data['items'] = $newData;
        }
        $this->response($data, 200);
    }
}