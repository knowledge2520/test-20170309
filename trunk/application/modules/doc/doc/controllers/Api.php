<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends MX_Controller {

    function __construct() {
        parent::__construct();
    }

    public function index()
    {
        /* if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != 'wayposh' || $_SERVER['PHP_AUTH_PW'] != 'dev@123') {
             header('WWW-Authenticate: Basic realm="MyProject"');
             header('HTTP/1.0 401 Unauthorized');
             die('Access Denied');
         }*/

        $this->load->view('doc/index');
    }

    public function json($json_file) {
        /* if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != 'wayposh' || $_SERVER['PHP_AUTH_PW'] != 'dev@123') {
             header('WWW-Authenticate: Basic realm="MyProject"');
             header('HTTP/1.0 401 Unauthorized');
             die('Access Denied');
         }*/
        $this->load->helper('file');
        $string = file_get_contents("swagger/wayposh_api.json");

    }

}