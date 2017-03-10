<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Statistic_model extends CI_Model {

    public function apiDailyStatistic() {
        $sql    = "SELECT COUNT(id) AS totalAipCall FROM api_log WHERE DATE_FORMAT(created_at, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')";
        $query  = $this->db->query($sql);
        return $query->row()->totalAipCall;
    }

    public function apiUserLoginStatistic() {
        $sql = "SELECT COUNT(id) AS totalLogin FROM users WHERE DATE_FORMAT(FROM_UNIXTIME(last_login), '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')";
        $query  = $this->db->query($sql);
        return $query->row()->totalLogin;
    }

    public function apiTotalRegistedToday() {
        $sql = "SELECT COUNT(id) AS totalRegistered FROM users WHERE DATE_FORMAT(FROM_UNIXTIME(created_on), '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')";
        $query  = $this->db->query($sql);
        return $query->row()->totalRegistered;
    }
}