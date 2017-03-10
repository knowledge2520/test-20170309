<?php

    function get_website_listing($url) {
    	return $url ? ((!filter_var($url, FILTER_VALIDATE_URL) === false) ? $url : 'http://'.$url) : 'Not Available';
    }
    
    function get_img_path($photo){
    	$no_image = site_url() . '../assets/images/uploads/no-image.jpg';
    	if(!$photo){
    		return $no_image;
    	}
    	
    	if(strstr($photo, 'http://') !== false || strstr($photo, 'https://') !== false){
    		$path = $photo;
    	}
    	if(!substr($photo, 0, 3) == '../'){
    		$photo = '../'.$photo;
    	}
    	$path = site_url() . $photo;    	
    	return fileExists($path) ? $path : $no_image;
    }
    function fileExists($path){
    	return (@fopen($path,"r")==true);
    }

    function get_url_query($url, $url_query, $data){

        $arr = [];
        $split_query = explode('&', $url_query);
        foreach ($split_query as $key => $item) {
            if($item){
                $tmp = explode('=', $item);
                if(sizeof($tmp > 1)){
                    $query = [$tmp[0] => $tmp[1]];
                    $arr = array_merge($arr, $query);
                }
            }

        }
        $str = [];
        $arr = array_merge($arr, $data);
        foreach ($arr as $key => $item) {
            $str[] = $key.'='.$item;
        }

        return $url . '?' . implode('&', $str);

    }

    function get_time_date($date, $output_time = true){
        if(!$date){
            return false;
        }
        if($output_time){
            return $date && $date > 0 ? gmdate('m-d-Y H:i',$date) : "";
        }
        return $date && $date > 0 ? gmdate('m-d-Y',$date) : "";
    }