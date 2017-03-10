<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class SiteHelper {
    /*
     * name isLogged
     */

    public function isLogged() {
        if (!$this->ion_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        }
    }

    /*
     * name isAdmin
     */

    public function isAdmin() {
        if (!$this->ion_auth->is_admin()) { //remove this elseif if you want to enable this for non-admins
            //redirect them to the home page because they must be an administrator to view this
            return show_error('You must be an administrator to view this page.');
        }
    }

    /*
     * name getAsideData
     * @return all menu array
     */

    public static function getAsideData() {
        $ci = &get_instance();
        $ci->load->model(array('menu_model', 'users/permissions_model', 'users/groups_model'));
        $menu = $ci->menu_model->listAll();
        $group = $ci->groups_model->get_group_user($ci->session->userdata('user_id'));
        $permission = $ci->permissions_model->get_permissions_group($group->group_id);
        $data = array();
        foreach ($menu as $item) {
            if ((SiteHelper::check_menu($item, $permission)) || ($group->group_id == 1) && $item->status) {
                $data[] = array(
                    'id' => $item->id,
                    'module_name' => $item->module_name,
                    'method_name' => $item->method_name,
                    'title' => lang($item->title),
                    'link' => site_url($ci->lang->lang() . $item->link),
                    'parent' => $item->parent, //id of menu
                    'icon_class' => $item->icon_class,
                );
            }
        }

        return SiteHelper::organizeMenuArray($data);
    }

    function check_menu($menu, $permissions) {
        if (is_array($permissions) || is_object($permissions)) {
            foreach ($permissions as $k => $p) {
                $arr_menu = explode('/', $menu->link);
                $arr_permission = explode('.', $k);

                if ($menu->parent == 0) {
                    if ($arr_permission[0] == $arr_menu[1]) {
                        return TRUE;
                    }
                } else {
                    if (sizeof($arr_menu) == 2) {
                        if ($arr_permission[0] == $arr_menu[1] && sizeof($arr_permission) == 2) {
                            return TRUE;
                        }
                    } elseif (sizeof($arr_menu) == 3) {
                        if ($arr_permission[0] == $arr_menu[1] && $arr_permission[1] == $arr_menu[2]) {
                            return TRUE;
                        }
                    }
                }
            }
            return FALSE;
        }
        return FALSE;
    }

    /**
     * @name organizeMenuArray
     * @param unknown $arr
     * @param number $parent
     * @return array was oganized and sort as sub key
     * file_name
     */
    function organizeMenuArray($arr, $parent = 0) {
        $pages = Array();
        if (!empty($arr)) {
            foreach ($arr as $page) {
                if (isset($page ['parent'])) {
                    if ($page ['parent'] == $parent) {
                        $page ['sub'] = isset($page ['sub']) ? $page ['sub'] : SiteHelper::organizeMenuArray($arr, $page ['id']);
                        $pages [] = $page;
                    }
                }
            }
        }
        return $pages;
    }

    /**
     * @name showNavigation
     * @param unknown $menus_data
     * @return string|NULL
     * file_name
     */
    public static function showNavigation($menus_data = array()) {
        $ci = &get_instance();

        if (!empty($menus_data)) {
            $output = '';
            $i = 0;
            foreach ($menus_data as $menu_atr) {
                //is start menu item?
                $start_class = $i == 0 ? 'start' : '';

                //is it has arrow icon
                $arrow_span = !empty($menu_atr['sub']) ? '<span class="arrow "></span>' : '';

                //active root                
                $module_name = $ci->router->fetch_class();
                $active_root = $module_name == $menu_atr['module_name'] || $menu_atr['module_name'] == $ci->uri->segment(2) ? ' active open' : '';
                $icon_class = isset($menu_atr['icon_class']) ? $menu_atr['icon_class'] : '';
                $href = isset($menu_atr['link']) ? $menu_atr['link'] : 'javascript:;';

                $output .= '<li class="nav-item ' . $start_class . $active_root . '"><a href="' . $href . '" class="nav-link nav-toggle"> <i class="' . $icon_class . '"></i>
                    <span class="title">' . $menu_atr['title'] . '</span>' . $arrow_span . '</a>';
                if (!empty($menu_atr['sub'])) {
                    $output .= SiteHelper::generateSubMenuHtml($menu_atr['sub'], null, 'sub-menu');
                }

                $output .= '</li>';
                $i++;
            }
            return $output;
        }
        return null;
    }

    function generateSubMenuHtml($nav, $tabs = "", $nav_class = '', $level = 0) {
        $ci = &get_instance();
        $tab = "    ";
        $html = "\n$tabs<ul class=\"$nav_class\">\n";
        $html .= "\n$tabs\n";

        $i = 1;

        foreach ($nav as $menu) {
            $hasChildClass = '';
            $menu_href = 'javascript:void(0)';
            if (isset($page ['sub'] [0])) {
                $hasChildClass = 'haschild';
            }
            $menu_href = $menu['link'];
            $menu_title = $menu['title'];
            $menu_class = $menu['icon_class'] != '' ? $menu['icon_class'] : '';

            //active sub class
            $method_name = $ci->router->fetch_method();
            $module_name = $ci->router->fetch_class();
            $active_method = $method_name == $menu['method_name'] && $module_name == $menu['module_name'] ? ' active ' : '';

            $html .= '<li class="nav-item ' . $active_method . '">';

            // Don't generate empty lists
            if (isset($menu ['sub'] [0])) {
                if (!$level) {
                    $html .= SiteHelper::generateSubMenuHtml($menu ['sub'], $tabs . $tab, $nav_class);
                }
            }

            //icon class
            if ($menu_class != '') {
                $menu_class = '<i class="' . $menu_class . '"></i>';
            }

            $ci->load->model('menu_model');
            $approve_listing = $ci->menu_model->count_approve_listing();
            $approve_media = $ci->menu_model->count_approve_media_listing();

            if ($menu_title == lang('menu_business_approve') && ($approve_listing > 0)) {
                $html .= "<a href=\"$menu_href\" class=\"nav-link \"> <span class='badge badge-danger'>$approve_listing</span> $menu_title</a>";
            } elseif ($menu_title == lang('menu_media_approve') && ($approve_media > 0)) {
                $html .= "<a href=\"$menu_href\" class=\"nav-link \"> <span class='badge badge-danger'>$approve_media</span> $menu_title</a>";
            } else {
                $html .= "<a href=\"$menu_href\" class=\"nav-link \">$menu_class $menu_title</a>";
            }

            $html .= "</li>\n";
        }
        $html .= $tabs . "</ul>\n";

        return $html;
    }

    public static function recordPerPage() {
        $data = array(
            '50' => 50,
            '100' => 100,
            '200' => 200,
            '500' => 500,
            '1000' => 1000,
        );
        return $data;
    }
}

class MailHelp {

    static function config() {
        $CI = & get_instance();
        $mail_confg = array();

        $mail_confg['protocol'] = $CI->config->item('protocol');
        $mail_confg['smtp_host'] = $CI->config->item('smtp_host');
        $mail_confg['smtp_user'] = $CI->config->item('smtp_user');
        $mail_confg['smtp_pass'] = $CI->config->item('smtp_pass');
        $mail_confg['smtp_port'] = $CI->config->item('smtp_port');
        $mail_confg['mailpath'] = $CI->config->item('mailpath');
        $mail_confg['wordwrap'] = $CI->config->item('wordwrap');
        $mail_confg['mailtype'] = $CI->config->item('mailtype');
        $mail_confg['charset'] = $CI->config->item('charset');
        $mail_confg['newline'] = $CI->config->item('newline');
        return $mail_confg;
    }

}

function getCRMConfigs($config_keys = array()) {
    $CI = &get_instance();
    if (!$config_keys || empty($config_keys)) {
        return false;
    } else {
        $data = array();
        foreach ($config_keys as $key) {
            $result = $CI->db->query('SELECT c.value FROM crm_system_config as c WHERE c.key = ?', $key)->row();
            if ($result) {
                $data[$key] = $result->value;
            } else {
                $data[$key] = false;
            }
        }
        return $data;
    }
}

function getAPIKey() {
    $CI = &get_instance();
    $CI->db->select('key');
    $result = $CI->db->get('keys');
    $result = $result->row();
    if ($result) {
        $data = $result->key;
    } else {
        $data = false;
    }
    return $data;
}

function get_user_location($user_id){
    $CI = &get_instance();
    
    $CI->db->select('*');   
    $CI->db->where('user_id', $user_id);
    $CI->db->order_by('id','DESC');
    $result = $CI->db->get('user_options');
    
    $lat = 0;
    $lng = 0;
    
    if ($result->num_rows() > 0) {
        $result = $result->row();
        $location_city = $result->location_city;
        if($location_city && explode ( ',', $location_city ) > 1){
            $lat = trim(explode ( ',', $location_city )[0]);
            $lng = trim(explode ( ',', $location_city )[1]);
        }
    } 
    
    $data = $lat && $lat ?  ['latitude' => $lat, 'longitude' => $lng] : array();
    
    return $data;
}

function set_time_by_timezone($unix_time, $format = 'd-m-Y H:i:s', $timezone){
    
    if(!$unix_time){
        return '';  
    }
    
    $date = new DateTime('@' . $unix_time);
    $date->setTimeZone(new DateTimeZone($timezone));
    return $date->format($format);
}

function format_output_data($object, $website = false, $timezone = 'Asia/Singapore') {
    $ci = & get_instance();
    $ci->load->helper('date');
    //$timezone = $time_zone ? $time_zone : 'UP8';
    
    if (empty($object)) {
        return $object;
    }
    if(is_array($object)){
        foreach ($object as $property => $value) {
            if (is_array($value) && count($value) < 1) {
                $object['$property'] = array();
            } elseif (!is_array($value) && $value == null && !is_int($value)) {
                $object['$property'] = "";
            } elseif ((is_int($value) && $value == 0) || $value == false) {
                $value = 0;
            }
        }
    }
    else{
        foreach ($object as $property => $value) {
            if (is_array($value) && count($value) < 1) {
                $object->$property = array();
            } elseif (!is_array($value) && $value == null && !is_int($value)) {
                $object->$property = "";
            } elseif ((is_int($value) && $value == 0) || $value == false) {
                $value = 0;
            }
        }
    }
    if (isset($object->source) && !empty($object->source)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->source)) {
            if (substr($object->source, 0, 7) == 'http://') {
                $object->source = str_replace('../', '', $object->source);
            }
            else{
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
                    $object->source = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->source);
                } else {
                    $object->source = siteURL() . str_replace('../', '', $object->source);
                }
            }
        }
    }
    if (isset($object->profile_photo) && !empty($object->profile_photo)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->profile_photo)) {
            if (substr($object->profile_photo, 0, 7) == 'http://') {
                $object->profile_photo = str_replace('../', '', $object->profile_photo);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/') !== FALSE) {
                    $object->profile_photo = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->profile_photo);
                } else {
                    $object->profile_photo = siteURL() . str_replace('../', '', $object->profile_photo);
                }
            }
        }
    }
    if (isset($object->profile_photo_thumb) && !empty($object->profile_photo_thumb)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->profile_photo_thumb)) {
            if (substr($object->profile_photo_thumb, 0, 7) == 'http://') {
                $object->profile_photo_thumb = str_replace('../', '', $object->profile_photo_thumb);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/') !== FALSE) {
                    $object->profile_photo_thumb = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->profile_photo_thumb);
                } else {
                    $object->profile_photo_thumb = siteURL() . str_replace('../', '', $object->profile_photo_thumb);
                }
            }
        }
    }
    if (isset($object->profile_background) && !empty($object->profile_background)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->profile_background)) {
            if (substr($object->profile_background, 0, 7) == 'http://') {
                $object->profile_background = str_replace('../', '', $object->profile_background);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/') !== FALSE) {
                    $object->profile_background = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->profile_background);
                } else {
                    $object->profile_background = siteURL() . str_replace('../', '', $object->profile_background);
                }
            }
        }
    }
    if (isset($object->profile_background_thumb) && !empty($object->profile_background_thumb)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->profile_background_thumb)) {
            if (substr($object->profile_background_thumb, 0, 7) == 'http://') {
                $object->background_thumb = str_replace('../', '', $object->profile_background_thumb);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/') !== FALSE) {
                    $object->profile_background_thumb = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->profile_background_thumb);
                } else {
                    $object->profile_background_thumb = siteURL() . str_replace('../', '', $object->profile_background_thumb);
                }
            }
        }
    }
    if (isset($object->path) && !empty($object->path)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->path)) {
            if (substr($object->path, 0, 7) == 'http://') {
                $object->path = str_replace('../', '', $object->path);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
                    $object->path = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->path);
                } else {
                    $object->path = siteURL() . str_replace('../', '', $object->path);
                }
            }
        }
    }
    if (isset($object->created_on) && !empty($object->created_on)) {
        $object->created_on = date('d-m-Y H:i:s', $object->created_on);
    }
    if (isset($object->purchase_date) && !empty($object->purchase_date)) {
        $object->purchase_date = date('d-m-Y H:i:s', $object->purchase_date);
    }
    if (isset($object->use_date) && !empty($object->use_date)) {
        $object->use_date = date('d-m-Y H:i:s', $object->use_date);
    }
    if (isset($object->date) && !empty($object->date)) {
        $object->date = date('d-m-Y H:i:s', $object->date);
    }
    if (isset($object->result_date) && !empty($object->result_date)) {
        $object->result_date = date('d-m-Y H:i:s', $object->result_date);
    }
    if (isset($object->surgeny_date) && !empty($object->surgeny_date)) {
        $object->surgeny_date = date('d-m-Y H:i:s', $object->surgeny_date);
    }
    if (isset($object->start_date) && !empty($object->start_date)) {
        $object->start_date = date('d-m-Y H:i:s', $object->start_date);
    }
    if (isset($object->end_date) && !empty($object->end_date)) {
        $object->end_date = date('d-m-Y H:i:s', $object->end_date);
    }
    if (isset($object->last_update) && !empty($object->last_update)) {
        $object->last_update = date('d-m-Y H:i:s', $object->last_update);
    }
    if (isset($object->reminder_start_date) && !empty($object->reminder_start_date)) {
        $object->reminder_start_date = date('d-m-Y H:i:s', $object->reminder_start_date);
    }
    if (isset($object->scannedDate) && !empty($object->scannedDate)) {
        $object->scannedDate = gmdate('d-m-Y H:i:s', $object->scannedDate);
        //$object->scannedDate = set_time_by_timezone($object->scannedDate, 'd-m-Y H:i:s', $timezone);
        //$object->scannedDate = date('d-m-Y H:i:s', gmt_to_local(local_to_gmt($object->scannedDate),$timezone));   
    }
    if (isset($object->modified_date) && !empty($object->modified_date)) {
        if($website){
            $object->modified_date = date('d F Y', $object->modified_date);
        }
        else{
            $object->modified_date = date('d-m-Y H:i:s', $object->modified_date);
        }
    }
    if (isset($object->created_date) && !empty($object->created_date)) {
        /*if (is_numeric($object->created_date)) {
            $diff = abs(now() - $object->created_date);
            $diff = gmt_to_local($diff);
            $years = floor($diff / (365 * 60 * 60 * 24));
            $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
            $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
            $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
            $seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60));

            $object->created_date = date('d-m-Y H:i:s', $object->created_date);
            $object->created_time = array('years' => $years, 'months' => $months, 'days' => $days, 'hours' => $hours, 'minutes' => $minutes, 'seconds' => $seconds);
        } else {
            $object->created_date = date('d-m-Y H:i:s', strtotime($object->created_date));
        }*/
        if (is_numeric($object->created_date)) {
            $diff = abs(now() - $object->created_date);
            $diff = gmt_to_local($diff);
            $years = floor($diff / (365 * 60 * 60 * 24));
            $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
            $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
            $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
            $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
            $seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60));
            $object->created_date = gmdate('d-m-Y H:i:s', $object->created_date);
            //$object->created_date = date('d-m-Y H:i:s', $object->created_date);
            $object->created_time = array('years' => $years, 'months' => $months, 'days' => $days, 'hours' => $hours, 'minutes' => $minutes, 'seconds' => $seconds);
        } else {
            //$object->created_date = date('d-m-Y H:i:s', strtotime($object->created_date));
            $object->created_date = gmdate('d-m-Y H:i:s', strtotime($object->created_date));
        }
    }
    if (isset($object->dob) && !empty($object->dob)) {
        if($website){
            if (is_numeric($object->dob)) {
                $diff = abs(now() - $object->dob);
                $diff = gmt_to_local($diff);
                $years = floor($diff / (365 * 60 * 60 * 24));
                $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
                $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
                $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
                $seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60));

                $object->dob = date('d F Y', $object->dob);
                $object->dob_time = array('years' => $years, 'months' => $months, 'days' => $days, 'hours' => $hours, 'minutes' => $minutes, 'seconds' => $seconds);
                $object->dob_years_old = date_diff(date_create(date('Y-m-d', strtotime($object->dob))), date_create(date('Y-m-d',now())))->y;
            } else {
                $object->dob = date('d F Y', strtotime($object->dob));
            }
        }
        else{
            if (is_numeric($object->dob)) {
                $diff = abs(now() - $object->dob);
                $diff = gmt_to_local($diff);
                $years = floor($diff / (365 * 60 * 60 * 24));
                $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
                $hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
                $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
                $seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60));

                $object->dob = date('d-m-Y H:i:s', $object->dob);
                $object->dob_time = array('years' => $years, 'months' => $months, 'days' => $days, 'hours' => $hours, 'minutes' => $minutes, 'seconds' => $seconds);
                $object->dob_years_old = date_diff(date_create(date('Y-m-d', strtotime($object->dob))), date_create(date('Y-m-d',now())))->y;
            } else {
                $object->dob = date('d-m-Y H:i:s', strtotime($object->dob));
            }
        }
    }
    if (isset($object->photo) && !empty($object->photo)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->photo)) {
            if (substr($object->photo, 0, 7) == 'http://') {
                $object->photo = str_replace('../', '', $object->photo);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
                    $object->photo = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->photo);
                } else {
                    $object->photo = siteURL() . str_replace('../', '', $object->photo);
                }
            }
        }
    }
    if (isset($object->photo_thumb) && !empty($object->photo_thumb)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->photo_thumb)) {
            if (substr($object->photo_thumb, 0, 7) == 'http://') {
                $object->photo_thumb = str_replace('../', '', $object->photo_thumb);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
                    $object->photo_thumb = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->photo_thumb);
                } else {
                    $object->photo_thumb = siteURL() . str_replace('../', '', $object->photo_thumb);
                }
            }
        }
    }
    //message
    if (isset($object->sender_profile_photo) && !empty($object->sender_profile_photo)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->sender_profile_photo)) {
            if (substr($object->sender_profile_photo, 0, 7) == 'http://') {
                $object->sender_profile_photo = str_replace('../', '', $object->sender_profile_photo);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
                    $object->sender_profile_photo = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->sender_profile_photo);
                } else {
                    $object->sender_profile_photo = siteURL() . str_replace('../', '', $object->sender_profile_photo);
                }
            }
        }
    }
    if (isset($object->sender_profile_photo_thumb) && !empty($object->sender_profile_photo_thumb)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->sender_profile_photo_thumb)) {
            if (substr($object->sender_profile_photo_thumb, 0, 7) == 'http://') {
                $object->sender_profile_photo_thumb = str_replace('../', '', $object->sender_profile_photo_thumb);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
                    $object->sender_profile_photo_thumb = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->sender_profile_photo_thumb);
                } else {
                    $object->sender_profile_photo_thumb = siteURL() . str_replace('../', '', $object->sender_profile_photo_thumb);
                }
            }
        }
    }

    if (isset($object->accepter_profile_photo) && !empty($object->accepter_profile_photo)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->accepter_profile_photo)) {
            if (substr($object->accepter_profile_photo, 0, 7) == 'http://') {
                $object->accepter_profile_photo = str_replace('../', '', $object->accepter_profile_photo);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
                    $object->accepter_profile_photo = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->accepter_profile_photo);
                } else {
                    $object->accepter_profile_photo = siteURL() . str_replace('../', '', $object->accepter_profile_photo);
                }
            }
        }
    }
    if (isset($object->accepter_profile_photo_thumb) && !empty($object->accepter_profile_photo_thumb)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->accepter_profile_photo_thumb)) {
            if (substr($object->accepter_profile_photo_thumb, 0, 7) == 'http://') {
                $object->accepter_profile_photo_thumb = str_replace('../', '', $object->accepter_profile_photo_thumb);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
                    $object->accepter_profile_photo_thumb = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->accepter_profile_photo_thumb);
                } else {
                    $object->accepter_profile_photo_thumb = siteURL() . str_replace('../', '', $object->accepter_profile_photo_thumb);
                }
            }
        }
    }
    if (isset($object->category_photo) && !empty($object->category_photo)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->category_photo)) {
            if (substr($object->category_photo, 0, 7) == 'http://') {
                $object->category_photo = str_replace('../', '', $object->category_photo);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
                    $object->category_photo = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->category_photo);
                } else {
                    $object->category_photo = siteURL() . str_replace('../', '', $object->category_photo);
                }
            }
        }
    }
    if (isset($object->category_photo_thumb) && !empty($object->category_photo_thumb)) {
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $object->category_photo_thumb)) {
            if (substr($object->category_photo_thumb, 0, 7) == 'http://') {
                $object->category_photo_thumb = str_replace('../', '', $object->category_photo_thumb);
            } else {
                if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
                    $object->category_photo_thumb = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $object->category_photo_thumb);
                } else {
                    $object->category_photo_thumb = siteURL() . str_replace('../', '', $object->category_photo_thumb);
                }
            }
        }
    }
    if (isset($object->average_rating) && !empty($object->average_rating)) {
        $object->average_rating = ceil($object->average_rating);
    }

    if (isset($object->website)) {
        $object->website = get_website_listing_url($object->website);
    }
    return $object;
}

function get_website_listing_url($url) {
    return $url ? ((!filter_var($url, FILTER_VALIDATE_URL) === false) ? $url : 'http://'.$url) : 'Not Available';
}

function siteURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'] . '/';
    return $protocol . $domainName;
}

function allow_file_upload($case = '') {
    switch ($case) {
        case 'review':
            return 'jpg|png|jpeg|mp4|mov|avi|mpeg';
            break;

        default:
            return '';
            break;
    }
}

function insert_user_media($data) {
    $ci = & get_instance();
    if (empty($data)) {
        return false;
    }

    if ($ci->db->insert_batch('user_media', $data)) {
        return true;
    }
}

function is_timestamp($timestamp) {
    return ((string) (int) $timestamp === $timestamp) && ($timestamp <= PHP_INT_MAX) && ($timestamp >= ~PHP_INT_MAX) && (!strtotime($timestamp));
}

function get_location_from_address($address) {
    $result = array();
//  $address = '201 S. Division St., Ann Arbor, MI 48104'; // Google HQ
    if (empty($address)) {
        return $result;
    }
    $prepAddr = str_replace(' ', '+', $address);
    $prepAddr = urlencode($address);
    
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false';
    
    /*
    disable certificate
     */
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    ); 

    $geocode = file_get_contents($url, false, stream_context_create($arrContextOptions));
    $output = json_decode($geocode);
    if (!empty($output->results)) {
        $result = array();
        $i = 0;

        $lat = $output->results[0]->geometry->location->lat;
        $long = $output->results[0]->geometry->location->lng;

        $result = array(
            'lat' => $lat,
            'long' => $long
        );
        return $result;
    } else {
        return $result;
    }
}

function get_address_from_location($lat, $lng) {

    $geocode = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $lat . ',' . $lng . '&sensor=false');

    $output = json_decode($geocode);
    if(!empty($output->results)){
        for ($j = 0; $j < count($output->results[0]->address_components); $j++) {

            $cn = array($output->results[0]->address_components[$j]->types[0]);

            if (in_array("country", $cn)) {
                $country = $output->results[0]->address_components[$j]->long_name;
            }
        }
        return $country;
    }
    
    return false;
}

function get_timezone_from_location($lat, $lng, $timestamp) {
    $response = file_get_contents('https://maps.googleapis.com/maps/api/timezone/json?location='.$lat.','.$lng.'&timestamp='.$timestamp.'&sensor=FALSE');

    $output = json_decode($response);

    if($output->status == 'OK'){
        return $output->timeZoneId; 
    }

    return false;
}

function distance($lat1, $lon1, $lat2, $lon2, $unit = "K") {

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
        return ($miles * 1.609344);
    } 
    else if ($unit == "N") {
        return ($miles * 0.8684);
    } 
    else {
        return $miles;
    }
}

function get_countries() {
    $ci = &get_instance();

    return $ci->db->query("SELECT * FROM countries WHERE status = 1")->result();
}

function output_media_file($path){
    if (strstr($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 'version2/pet-server/trunk/api') !== FALSE) {
        $path = siteURL() . str_replace('../', 'version2/pet-server/trunk/', $path);
    } else {
        $path = siteURL() . str_replace('../', '', $path);
    }
    return $path; 
}

class My_qrcode{
    static function get_tag($code){
        $ci = &get_instance();
        if(strstr($code, 'http://')){
            $code = substr($code,strlen($ci->config->item('qrcode_site')));
        }
        return $code;
    }
    static  function get_full($code){
        $ci = &get_instance();
        if(strstr($code, 'http://') === false){
            $code = $ci->config->item('qrcode_site') . $code;
        }
        return $code;        
    }
    
    static function get_badgeId($code){
        $ci = &get_instance();
        
        $str = explode('/', $code);
        
        foreach ($str as $item){
            $text = $item;
        }
        
        return $text;
    }
}

class My_nfc{
    static function get_tag($code){
        $ci = &get_instance();
        if(strstr($code, 'http://')){
            $code = substr($code,strlen($ci->config->item('nfc_site')));
        }
        return $code;
    }
    static  function get_full($code){
        $ci = &get_instance();
        if(strstr($code, 'http://') === false){
            $code = $ci->config->item('nfc_site') . $code;
        }
        return $code;        
    }
}

function add_break_link($str){
    $str = preg_replace('/\r?\n|\r/','<br/>', $str);
    $str = str_replace(array("\r\n","\r","\n"),"<br/>", $str);
    $str = nl2br($str);
    
    return $str;
}

/**
 *
 */
function pagination($url, $total, $perPage, $crrPage = '', $uri_segment = '' , $keyword = false) {

        $CI = & get_instance();

        $config = array();
        $config['base_url']     = $url;
        $config['total_rows']   = $total;
        $config['per_page']     = $perPage;

        $config['full_tag_open']    = '<ul class="pagination">';
        $config['full_tag_close']   = '</ul>';
        $config['prev_link']    = '<i class="fa fa-long-arrow-left"></i> '.lang('pre');
        $config['next_link']    = lang('next') . ' <i class="fa fa-long-arrow-right"></i>';
        $config['last_link']    = lang('last');
        $config['first_link']   = lang('first');

        $config['first_tag_open']   = '<li>';
        $config['first_tag_close']  = '</li>';
        $config['last_tag_open']    = '<li>';
        $config['last_tag_close']   = '</li>';
        $config['prev_tag_open']    = '<li class="prev">';
        $config['prev_tag_close']   = '</li>';
        $config['next_tag_open']    = '<li>';
        $config['next_tag_close']   = '</li>';
        $config['num_tag_open']     = '<li>';
        $config['num_tag_close']    = '</li>';
        $config['cur_tag_open']     = '<li class="active"><a href="javascript:void(0)">';
        $config['cur_tag_close']    = '</a></li>';

        if ($crrPage != '') {
            $config['cur_page'] = $crrPage;
        }
        if ($uri_segment != '') {
            $config['uri_segment'] = $uri_segment;
        }

        return $config;

}

class CMSHelper{
    public static function output_media($item){
        //   assets/images/uploads/no-image.jpg
        $ci = & get_instance();
        $ci->load->helper('listing');
        
        $no_image = base_url('../assets/images/uploads/no-image.jpg');
        if(!$item) return $no_image;
        if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $item)) {
            if (substr($item, 0, 7) == 'http://' || substr($item, 0, 8) == 'https://') {
                return $item;
            } else {
                $item = substr($item, 0, 3) == '../' ? $item : '../'.$item;
                $checked = fileExists($item);
                if ($checked) {
                    return base_url($item);
                }
                return $no_image;
            }
        }
        return $item;
    }

    public static function dayAdd($key = false, $value = false, $date = array()){
        if($key == 'month'){
            return strtotime($value.$key, strtotime($date['year'].'-'.$date['mon']));
        }
        elseif($key == 'day'){
            return strtotime($value.$key, strtotime($date['year'].'-'.$date['mon'].'-'.$date['mday']));
        }elseif($key == 'year'){
            return strtotime($date['year'].'-01-01 ' . $value . ' ' . $key);
        }
        return false;

    }
}

function get_url($item,$uri) {
    
    return site_url($uri.$item);
}
function check_active_tab($item, $default, $key_input) {
    $ci = &get_instance();
    $input = $ci->input->get($key_input);
    if(!$input) {
        $input = $default;
    }
    return $input == $item;
}