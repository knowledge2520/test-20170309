<?php
    function do_upload($save_path= DEFAULT_PATH_ADMIN, $field_name , $file_name) {
        $ci = & get_instance();
        // Use "upload" library to select image, and image will store in root directory "uploads" folder.
        $displayMaxSize = ini_get('upload_max_filesize');
        switch ( substr($displayMaxSize,-1) )
        {
            case 'G':
                $displayMaxSize = $displayMaxSize * 1024;
            case 'M':
                $displayMaxSize = $displayMaxSize * 1024;
            case 'K':
            $displayMaxSize = $displayMaxSize * 1024;
        }
            
        $config = array(
            'upload_path' => $save_path,
            'upload_url' => base_url() . $save_path,
            'allowed_types' => "gif|jpg|png|jpeg",
            'max_size' => $displayMaxSize, 
            'file_name' => $file_name
        );
        $ci->load->library('upload', $config);
        // create folder
        if (! is_dir ( $config ['upload_path'] )) {
            mkdir ( $config ['upload_path'], 0777, TRUE );
        }

        if ($ci->upload->do_upload($field_name)) {
            //If image upload in folder, set also this value in "$image_data".
            $image_data = $ci->upload->data();
            return $image_data;
        }
        else
        {
            return false;
        }
    }

    function check_file_upload(){
        $ci = & get_instance();

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $displayMaxSize = ini_get('post_max_size');

            switch (substr($displayMaxSize, -1)) {
                case 'G':
                    $displayMaxSize = $displayMaxSize * 1024;
                case 'M':
                    $displayMaxSize = $displayMaxSize * 1024;
                case 'K':
                    $displayMaxSize = $displayMaxSize * 1024;
            }

            // $error = 'Posted data is too large. ' .
            //         $_SERVER['CONTENT_LENGTH'] .
            //         ' bytes exceeds the maximum size of ' .
            //         $displayMaxSize . ' bytes.';
            $post_size =  round($_SERVER['CONTENT_LENGTH'] / (1024*1024),0);    
            $server_size  = round($displayMaxSize / (1024*1024),0);
            $error = 'Upload failed. Please upload file with size smaller than ' . $server_size . 'MB!';        
            
            return $error;
        }

        return false;
    }

    class S3_Upload{
        /**
         * @param bool|false $id
         * @param bool|false $newsFeedId
         * @param string $field
         * @param $userId
         * @param int $status
         * @description: Save upload files into S3 and save data into user_media table
         */
        // public function saveMedia( $newsFeedId = false, $id = false, $field = '', $userId, $status = 1, $uploadFieldName = 'file', $mediaType = '' ) {
        function saveMedia( $path = DEFAULT_PATH_ADMIN, $uploadFieldName = 'file', $config = array()) {    
            $ci = & get_instance();
            if( $ci->config->item('upload') == 's3-aws' ) {
                $media_file = S3_Upload::S3Upload($uploadFieldName, $config);
            } else {
                $media_file = S3_Upload::localUpload($uploadFieldName, $path, $config);
            }

            return $media_file;
        }


        function localUpload($field_name, $path, $config){
            $ci = & get_instance();

            $file = $_FILES[$field_name];

            if ( !empty($file['name']) ) {
                $data               = S3_Upload::getFileInfo($file, $field_name, $config, $path);

                if($data['code']){
                    $data['uri']        = $path . $data['custom_filename_file'];
                    $data['uri_thumb']  = $path . $data['custom_filename_thumb'];
                }

                return $data;
            }
            return array();
        }

        /**
         * @param bool|false $file
         * @param string $field_name
         * @return array
         * @description: Upload a file to S3
         */
        function S3Upload($field_name = '', $config) {
            $ci = & get_instance();

            $file = $_FILES[$field_name];
 
            if ( !empty($file['name']) ) {
                $data               = S3_Upload::getFileInfo($file, $field_name, $config);

                if($data['code']){
                    $data['uri']        = S3_Upload::putToS3($data['path'] . $data['custom_filename_file'], $data['custom_filename_file']);
                    $data['uri_thumb']  = S3_Upload::putToS3($data['path'] . $data['custom_filename_thumb'], $data['custom_filename_thumb']);                    
                    S3_Upload::putToS3($data['path'] . $data['custom_filename_original'], $data['custom_filename_original']);

                    @unlink($data['path'] . $data['custom_filename_file']);
                    @unlink($data['path'] . $data['custom_filename_thumb']);
                    @unlink($data['path'] . $data['custom_filename_original']);
                }

                return $data;
            }
            return array();
        }

        /**
         * @param $file
         * @return array
         * @description: Rename the upload file, resize and duplicated uploaded file in
         * tmp folder, then return the array information
         */
        function getFileInfo($file, $uploadFieldName = '', $config, $path = DEFAULT_PATH_ADMIN) {
            $ci = & get_instance();
            $ci->load->helper(array('image', 'string'));
            $data = array();
            $file_name                          = basename($file['name']);
            $ext                                = substr($file_name, strrpos($file_name, '.') + 1);
            $time                               = time();
            $name                               = md5(random_string('alnum', 20)) . "_" . time();
            $data['custom_filename']            = strtolower($name);
            $data['custom_filename_extension']  = strtolower($ext);
            $data['custom_filename_original']   = strtolower($name .  "." . $ext);
            $data['custom_filename_file']       = strtolower($name .  "_file." . $ext);
            $data['custom_filename_thumb']      = strtolower($name .  "_thumb." . $ext);
            $data['path']                       = $path;

            if( function_exists('getimagesize')) {
                //create photo thumb                
                try{
                    $image_data = do_upload($path, $uploadFieldName, $data['custom_filename_original']);
                    if(!$image_data){
                        throw new Exception($ci->upload->display_errors(), 1);
                    }
                }
                catch (Exception $e){
                    return array(
                            'code' => 0,
                            'message' => $e->getMessage()
                        );
                }

                $resize_width           = isset($config['resize_width']) ? $config['resize_width'] : IMAGE_RESIZE_WIDTH_THUMB;
                $resize_height          = isset($config['resize_height']) ? $config['resize_height'] : IMAGE_RESIZE_HEIGHT_THUMB;
                $thumb                  = isset($config['resize_thumb']) ? $config['resize_thumb'] : FALSE;
                $ratio                  = isset($config['resize_ratio']) ? $config['resize_ratio'] : TRUE;

                resizeImage($image_data['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT, $thumb, $ratio, $data['custom_filename_file']);
                list($w, $h)            = getimagesize($image_data['file_path'] . $data['custom_filename_file']);
                $data['width']          = $w;
                $data['height']         = $h;                

                resizeImage($image_data['file_path'] . $data['custom_filename_file'], $resize_width, $resize_height, $thumb, $ratio, $data['custom_filename_thumb']);
                list($w, $h)            = getimagesize($image_data['file_path'] . $data['custom_filename_thumb']);
                $data['width_thumb']    = $w;
                $data['height_thumb']   = $h;
                $data['code']           = 1;

                // list($w, $h)    = getimagesize($file['tmp_name']);
                // $data['width']  = $w;
                // $data['height'] = $h;

                // resizeImage($file['tmp_name'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                // list($w, $h) = getimagesize($file['tmp_name'] . "_thumb");
                // $data['width_thumb'] = $w;
                // $data['height_thumb'] = $h;              
            }
           
            return $data;
        }

        /**
         * @param The result set data $params
         * @description: Remove actual media file(s) in hard disk local or S3 AWS
         */
        static function removeMedia( $photo = false ) {
            $ci = & get_instance();
        
           if($photo) {

                if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $photo)) {
                    @unlink($photo);
                } else {
                    $ci->config->load('s3');
                    $ci->load->library('s3', array(
                        "access_key"    => $ci->config->item('access_key'),
                        "secret_key"    => $ci->config->item('secret_key'),
                        "use_ssl"       => false,
                        "verify_peer"   => true
                    ));
                    try {
                        $ci->s3->deleteObject($ci->config->item('s3-bucket'), S3_Upload::getS3KeyFile($photo));
                    } catch(Exception $e) {
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                    }
                }

           }
        }

        /**
         * @param $fullUri
         * @return mixed
         * @description: Get the S3 file name with the folder (develop | production)
         */
        static function getS3KeyFile($fullUri) {
            $ci = & get_instance();
        
            if( preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $fullUri)) {
                preg_match('/'.$ci->config->item('s3-path').'\/(.*)/', $fullUri, $matches);
                return isset($matches[0]) ? $matches[0] : $fullUri;
            }
            return $fullUri;
        }

        /**
         * @param The result set data $params
         * @description: Remove actual media file(s) in hard disk local or S3 AWS
         */
        static function removeByKeyValue( $item = array() ) {
            $ci = & get_instance();
            if($item) {
                if ( preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $item) ) {
                    $ci->config->load('s3');
                    $ci->load->library('s3', array(
                        "access_key" => $ci->config->item('access_key'),
                        "secret_key" => $ci->config->item('secret_key'),
                        "use_ssl" => false,
                        "verify_peer" => true
                    ));
                    try {
                        if( !empty($item) ) {
                            $ci->s3->deleteObject($ci->config->item('s3-bucket'), S3_Upload::getS3KeyFile($item));
                        }
                    } catch (Exception $e) {
                        echo 'Caught exception: ', $e->getMessage(), "\n";
                    }
                } elseif ( !preg_match('/http:\/\/graph\.facebook\.com*/', $item) ) {
                    if( !empty($item) ) {
                        @unlink($item);
                    }
                }
            }
        }

        /**
         * @param $filePath
         * @param $fileName
         * @return string
         * @description: Put the actual file into S3 AWS
         */
        static function putToS3( $filePath, $fileName ) {
            $ci = & get_instance();
        
            $ci->config->load('s3');
            $ci->load->library('s3', array(
                "access_key"    => $ci->config->item('access_key'),
                "secret_key"    => $ci->config->item('secret_key'),
                "use_ssl"       => false,
                "verify_peer"   => true
            ));
            if ($ci->s3->putObjectFile($filePath, $ci->config->item('s3-bucket'), $ci->config->item('s3-path') . "/" . $fileName, S3::ACL_PUBLIC_READ)) {
                return $ci->config->item('s3-uri') . $fileName;
            } else {
                $err['code']    = 15;
                $err['msg']     = lang('Error: Something went wrong while uploading your file... sorry!');
                return $err;
            }
        }
    }