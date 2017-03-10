<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Statistic extends Front_Controller {

    var $data = array();

    public function __construct() {
        parent::__construct();
        $this->load->model('statistic_model');
        $this->load->helper(array('url','language'));
    }

    public function index() {

        if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != 'petwidget' || $_SERVER['PHP_AUTH_PW'] != 'petwidget@123') {
            header('WWW-Authenticate: Basic realm="PetWidget"');
            header('HTTP/1.0 401 Unauthorized');
            die('Access Denied');
        } elseif(isset($_SERVER['HTTP_AUTHORIZATION'])) {
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));

            if( $_SERVER['PHP_AUTH_USER'] != 'petwidget' || $_SERVER['PHP_AUTH_PW'] != 'petwidget@123' ) {
                header('WWW-Authenticate: Basic realm="PetWidget"');
                header('HTTP/1.0 401 Unauthorized');
                die('Access Denied');
            }
        }
        $data['totalApiCall']           = $this->statistic_model->apiDailyStatistic();
        $data['totalUserLoginToday']    = $this->statistic_model->apiUserLoginStatistic();
        $data['totalRegistered']        = $this->statistic_model->apiTotalRegistedToday();
        $this->load->view("index", $data);
    }
}