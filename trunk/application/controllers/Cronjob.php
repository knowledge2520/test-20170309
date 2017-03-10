<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Cronjob extends CI_Controller
{
	function __construct()
    {
        // Construct our parent class
        parent::__construct();
        $this->load->model('cron_model');
    }
    
    function genCodes()
    {
       $data = $this->cron_model->run();
       echo 'done';exit;
    }

    function genSubscribed(){
        $data = $this->cron_model->genSubscribed();
        echo 'done';exit;
    }

    function updateCountry(){
        $data = $this->cron_model->updateCountry();
        echo 'done';exit;
    }
}