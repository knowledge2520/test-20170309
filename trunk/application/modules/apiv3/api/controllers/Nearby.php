<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/modules/api/api/libraries/REST_Controller.php';

class Nearby extends REST_Controller {

    function __construct() {

        // Construct our parent class
        parent::__construct();

        //load model
        //$this->load->model('postupdated_model');

        //load lang
        $this->lang->load('api');
        //load helper
        $this->load->helper(array('form', 'url'));

        $this->load->helper('site');

        $this->load->helper('newsfeeds');

        $this->load->library('friendnearby');
    }

    public function getNearbyStatus_post() {
        $this->_requireAuthToken();

        $response = $this->friendnearby->items();

        $this->response($response, 200);
    }

    public function setStatus_post() {
        $this->_requireAuthToken();

        $statusId = $this->post('id') ? $this->post('id') : false;

        if(!$statusId) {
            $error['msg'] = "Please input status ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $response = $this->friendnearby->setNearbyStatus($this->_member, $statusId);

        $this->response($response, 200);
    }

    public function setVisible_post() {

        $this->_requireAuthToken();

        $visible = $this->post('visible') ? $this->post('visible') : 0;

        $this->friendnearby->setVisibleStatus( $this->_member, $visible );

        $this->response(array(), 200);
    }

    public function setUserLocation_post() {
        $this->_requireAuthToken();

        $latitude   = $this->post('latitude') && $this->post('latitude') != 0 ? $this->post('latitude') : false;
        $longitude  = $this->post('longitude') && $this->post('longitude') != 0 ? $this->post('longitude') : false;

        if(!$latitude) {
            $error['msg'] = "Latitude is wrong";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if(!$longitude) {
            $error['msg'] = "Longitude is wrong";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $this->friendnearby->setUserLocation( $this->_member, $latitude, $longitude );

        $this->response(array(), 200);
    }

    public function getFriendNearby_post() {
        $this->_requireAuthToken();

        // $latitude   = $this->post('latitude') && $this->post('latitude') != 0 ? $this->post('latitude') : false;
        // $longitude  = $this->post('longitude') && $this->post('longitude') != 0 ? $this->post('longitude') : false;
        //TODO: get last user location
        $userOption = $this->member_model->getUserOptions($this->_member->id);
        $latitude = $userOption[LATITUDE];
        $longitude = $userOption[LONGITUDE];

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        //$this->friendnearby->setUserLocation( $this->_member, $latitude, $longitude );

        $items = $this->friendnearby->getUsersNearby($this->_member, $latitude, $longitude, 'items', $limit, $start);

        if( is_array($items) ) {
            $data[ITEMS]          = $items;
            $data[TOTAL_ITEM]     = (int)$this->friendnearby->getUsersNearby($this->_member, $latitude, $longitude, ALL, $limit, $start);
            $data[TOTAL_PAGE]     = ceil(intval($data['totalItem']) / $limit);
            $data[LIMIT]          = intval($limit);
            $this->response($data, 200);
        } else {
            $error['msg'] = "Your status should be visible to use Search Nearby";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }
    }

    public function search_post() {
        $this->_requireAuthToken();
        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword        = $this->post('keyword') ? trim($this->post('keyword')) : "";
        if(empty($keyword)) {
            $error['msg'] = "Keyword is required";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }
        $items = $this->friendnearby->searchUsers($this->_member, $keyword, 'items', $limit, $start);
        $data[ITEMS]          = $items;
        $data[TOTAL_ITEM]     = (int)$this->friendnearby->searchUsers($this->_member, $keyword, ALL, $limit, $start);
        $data[TOTAL_PAGE]     = ceil(intval($data['totalItem']) / $limit);
        $data[LIMIT]          = intval($limit);
        $this->response($data, 200);
    }

    /*
     * Going up:

SELECT * FROM table WHERE id > 'your_current_id' ORDER BY id LIMIT 1;
Going down:

SELECT * FROM table WHERE id < 'your_current_id' ORDER BY id DESC LIMIT 1;
     */

    public function firebasePush_post() {

        $serverKey = "AIzaSyDb_4uyHtXtAXCzt6UN0v5gshjwyQL6nL8";

        $url = "https://fcm.googleapis.com/fcm/send";

        $message = array("message" => "Testpush");

        $token = array("123");

        $fields = array(
            "registration_ids" => $token,
            "data" => $message,
        );

        $headers = array(
            'Authorization:key='.$serverKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);

        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);

        echo $result;
    }
}