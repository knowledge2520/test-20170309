<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Log extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('log_model');
        $this->load->library('pagination');
        $this->load->helper('site');
    }

    public function index() {

        $data = array();

        $segment = 0;

        $limit = $this->pagination->per_page;
        $start = $this->input->post('start');
        $start = $start ? $start : 0;

        $keyword = $this->input->post('keyword');

        $data["items"] = $this->log_model->items('item', $keyword, $start, $limit);
        $data["total"] = $this->log_model->items('total', $keyword);
        $data["start"] = $start;
        $data['keyword'] = $keyword;

        $pagingUrl = site_url('doc/log/index/');

        $config = pagination($pagingUrl, $data["total"], 20, $start, $segment, false);
        $this->pagination->initialize($config);
        $data['pagging'] = $this->pagination;

        $this->load->view('doc/log', $data);
    }
}