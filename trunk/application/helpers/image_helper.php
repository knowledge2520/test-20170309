<?php

function base64_to_jpeg($base64_string, $output_file) {
	$ifp = fopen($output_file, "wb");
	fwrite($ifp, base64_decode($base64_string));
	fclose($ifp);

	return $output_file;
}

function extractImages($strContent) {
	if (!$strContent || !is_string($strContent) || ($strContent == '')) {
		return null;
	}
	$image = null;

	$images = array();
	preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $strContent, $media);
	$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);
	foreach($data as $url)
	{
		array_push($images, $url);
	}
	$count = count($images);
	return $images;
}

function downloadImage($dir,$url){

	$ext 		= '.'.end(explode(".", $url));;
	$ext		= strtolower($ext);
	$exts		= array('.png', '.jpg', '.jpeg', '.gif');
	$ext		= in_array($ext,$exts) ? $ext : '.jpg';
	$file_name	= md5($url).$ext;
	if(is_file($dir.DS.$file_name)){
		return $file_name;
	}
	$ch = curl_init($url);
	$fp = fopen($dir.DS.$file_name, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	 
	return $file_name;
}

function resizeImage($imageFullPath,$width=IMAGE_RESIZE_WIDTH,$height=IMAGE_RESIZE_HEIGHT,$thumb = TRUE, $ratio= TRUE, $new_image = false)
{
    $ci = & get_instance ();

	$config['image_library']    = 'gd2';
	$config['source_image']     = $imageFullPath;
	$config['create_thumb']     = $thumb;
	$config['maintain_ratio']   = $ratio;
	$config['width']            = $width;
	$config['height']           = $height;

	if($new_image){
		$config['new_image']    = $new_image;
	}

    $ci->image_lib->initialize($config);
	
	if ( ! $ci->image_lib->resize())
	{	   
	   log_message('error', $ci->image_lib->display_errors());
	}
    $ci->image_lib->clear();
}

function cropImage($imageFullPath,$width=IMAGE_CROP_WIDTH,$height=IMAGE_CROP_HEIGHT,$thumb = TRUE, $ratio= TRUE)
{
    $ci = & get_instance ();

    $config['image_library']    = 'gd2';
    $config['source_image']     = $imageFullPath;
    $config['create_thumb']     = $thumb;
    $config['maintain_ratio']   = $ratio;
    $config['width']            = $width;
    $config['height']           = $height;
    $this->image_lib->initialize($config);
    
    if ( ! $ci->image_lib->crop())
    {
        log_message('error', $ci->image_lib->display_errors());
    }
    $ci->image_lib->clear();
}
function checkMediaExist($file){
	$ci = & get_instance ();
	$ci->load->helper('file');
	$ci->load->helper('listing');
	if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $file)) {
		return get_file_info(str_replace('../', '', $file));
	}
	else{
		return fileExists($file);
	}
}