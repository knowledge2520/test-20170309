<?php defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/modules/api/api/libraries/REST_Controller.php';

class Postupdated extends REST_Controller {

    function __construct() {

        // Construct our parent class
        parent::__construct();

        $this->load->library('petpostupdated');

        //load lang
        $this->lang->load('api');
        //load helper
        $this->load->helper(array('form', 'url'));
        $this->load->helper('site');
        $this->load->helper('newsfeeds');
    }

    function newItem_post() {

        $this->_requireAuthToken();

        $content        = $this->post('content') ? $this->post('content') : "";

        $userTag        = $this->post('tags') ? $this->post('tags') : "";

        $response[ITEM] = $this->petpostupdated->saveNew( $content, $this->_member, $userTag );

        $this->response($response, 200);
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
        $items = $this->petpostupdated->searchPostUpdated($this->_member, $keyword, 'items', $limit, $start);
        $data[ITEMS]          = $items;
        $data[TOTAL_ITEM]     = (int)$this->petpostupdated->searchPostUpdated($this->_member, $keyword, ALL, $limit, $start);
        $data[TOTAL_PAGE]     = ceil(intval($data['totalItem']) / $limit);
        $data[LIMIT]          = intval($limit);
        $this->response($data, 200);
    }
}