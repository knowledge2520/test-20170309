<?php
class Media_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->helper(array('string', 'image'));
        $this->load->library('image_lib');
        //$this->load->library('petupload');
    }

    /**
     * @param bool|false $newsFeedId
     * @param bool|false $id
     * @param string $field
     * @param $userId
     * @param int $status
     * @description: Save upload files into S3 and save data into user_media table
     */
    public function saveMedia( $newsFeedId = false, $id = false, $field = '', $userId, $status = 1, $uploadFieldName = 'file', $mediaType = '' ) {

        if( $this->config->item('upload') == 's3-aws' ) {
            $media_files = $this->S3MultiUpload($uploadFieldName);
        } else {
            $media_files = $this->localMultiUpload($this->config->item('listings_path'));
        }

        if ($media_files) {
            return $this->insertMediaData( $media_files, $newsFeedId, $id, $field, $userId, $status, $mediaType );

            /*$dataInsert = array();

            foreach ($media_files as $file) {

                $media_insert = array();

                $file_array = $this->config->item('upload') == 's3-aws' ? $file : $file['upload_data'];
                $source     = $this->config->item('upload') == 's3-aws' ? $file['uri'] : $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_array['file_name'];

                $media_insert[$field]       = $id;
                $media_insert["newfeed_id"] = $newsFeedId;
                $media_insert['source']     = $source;
                $media_insert['created_date'] = now();
                $media_insert['status']     = $status;
                $media_insert['user_id']    = $userId;
                $media_insert['type']       = 'PHOTO';
                $media_insert['media_type'] = $file['media_type'];

                if($this->config->item('upload') == 's3-aws') { // S3 upload

                    $media_insert['width_source']  = $file['width'];
                    $media_insert['height_source'] = $file['height'];

                    // thumb
                    $media_insert['photo_thumb']  = $file['uri_thumb'];
                    $media_insert['width_thumb']  = $file['width_thumb'];
                    $media_insert['height_thumb'] = $file['height_thumb'];

                } else {    // local upload
                    //resize
                    resizeImage($file_array['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                    $file_name_array = explode('.', $file_array['file_name']);
                    $media_insert['photo_thumb'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];

                    // Get W, H of uploaded image for: original and thumb file
                    if( function_exists('getimagesize')) {
                        list($w, $h) = getimagesize($media_insert['source']);
                        list($wThumb, $hThumb) = getimagesize($media_insert['photo_thumb']);
                        $media_insert['width_thumb']   = $wThumb;
                        $media_insert['height_thumb']  = $hThumb;
                        $media_insert['width_source']  = $w;
                        $media_insert['height_source'] = $h;
                    }
                }

                array_push($dataInsert, $media_insert);
            }
            $this->db->insert_batch('user_media', $dataInsert);
            return $dataInsert;*/
            //insert_user_media($dataInsert);
        }
    }

    /**
     * @param bool|false $newsFeedId
     * @param bool|false $id
     * @param string $field
     * @param $userId
     * @param int $status
     * @param string $uploadFieldName
     * @param string $mediaType
     * @return array
     * @description: Save an upload file into S3 and save data into user_media table
     */
    public function saveSingleMedia( $newsFeedId = false, $id = false, $field = '', $userId, $status = 1, $uploadFieldName = 'file', $mediaType = '' ) {
        $mediaFile = $this->S3Upload( false, $uploadFieldName );
        if ($mediaFile) {
            return $this->insertMediaData( array($mediaFile), $newsFeedId, $id, $field, $userId, $status, $mediaType );
        }
    }

    /**
     * @param $media_files
     * @param bool|false $newsFeedId
     * @param bool|false $id
     * @param string $field
     * @param $userId
     * @param int $status
     * @param string $mediaType
     * @return array
     * @description: Insert the media information into user_media table
     */
    protected function insertMediaData( $media_files, $newsFeedId = false, $id = false, $field = '', $userId, $status = 1, $mediaType = '' ) {
        $media_insert = array();
        $dataInsert = array();

        foreach ($media_files as $file) {
            $media_insert[$field]           = $id;
            $media_insert["newfeed_id"]     = $newsFeedId;
            $media_insert['source']         = $file['uri'];
            $media_insert['created_date']   = now();
            $media_insert['status']         = $status;
            $media_insert['user_id']        = $userId;
            $media_insert['type']           = 'PHOTO';
            $media_insert['media_type']     = $mediaType;
            $media_insert['width_source']   = $file['width'];
            $media_insert['height_source']  = $file['height'];

            // thumb
            $media_insert['photo_thumb']    = $file['uri_thumb'];
            $media_insert['width_thumb']    = $file['width_thumb'];
            $media_insert['height_thumb']   = $file['height_thumb'];

            array_push($dataInsert, $media_insert);
        }
        $this->db->insert_batch('user_media', $dataInsert);
        return $dataInsert;
    }

    /**
     * @param The result set data $params
     * @description: Remove actual media file(s) in hard disk local or S3 AWS
     */
    public function removeMedia( $results = false ) {

       if($results) {
            foreach( $results as $item ) {
                if( !preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $item->source)) {
                    @unlink($item->source);
                    @unlink($item->photo_thumb);
                } else {
                    $this->config->load('s3');
                    $this->load->library('s3', array(
                        "access_key"    => $this->config->item('access_key'),
                        "secret_key"    => $this->config->item('secret_key'),
                        "use_ssl"       => false,
                        "verify_peer"   => true
                    ));
                    try {
                        $this->s3->deleteObject($this->config->item('s3-bucket'), $this->getS3KeyFile($item->source));
                        $this->s3->deleteObject($this->config->item('s3-bucket'), $this->getS3KeyFile($item->photo_thumb));
                    } catch(Exception $e) {
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                    }
                }
            }
       }
    }

    public function getMediaData( $option = 'total', $params = false, $start = 0, $limit = API_NUM_RECORD_PER_PAGE ) {

        $query = $this->db->get_where("user_media", $params);

        if( $option == 'total' ) {
            return $query->num_rows();
        } else {
            $results = $query->result();
            return $results;
        }
    }

    /**
     * @param $fullUri
     * @return mixed
     * @description: Get the S3 file name with the folder (develop | production)
     */
    protected function getS3KeyFile($fullUri) {
        if( preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $fullUri)) {
            preg_match('/'.$this->config->item('s3-path').'\/(.*)/', $fullUri, $matches);
            return isset($matches[0]) ? $matches[0] : $fullUri;
        }
        return $fullUri;
    }

    public function justUpload( $file, $fieldName = '', $mediaType = '' ) {
        //$mediaFiles = $this->petupload->store( $file, $fieldName, $mediaType );
        //return $mediaFiles;
    }

    /**
     * @param The result set data $params
     * @description: Remove actual media file(s) in hard disk local or S3 AWS
     */
    public function removeByKeyValue( $removeMedia = array() ) {

        if($removeMedia) {
            foreach ($removeMedia as $key => $item) {
                if ( preg_match('/https:\/\/petwidget\.s3\.amazonaws\.com*/', $item) ) {
                    $this->config->load('s3');
                    $this->load->library('s3', array(
                        "access_key" => $this->config->item('access_key'),
                        "secret_key" => $this->config->item('secret_key'),
                        "use_ssl" => false,
                        "verify_peer" => true
                    ));
                    try {
                        if( !empty($item) ) {
                            $this->s3->deleteObject($this->config->item('s3-bucket'), $this->getS3KeyFile($item));
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
    }

    /**
     * @param string $field_name
     * @return array
     * @description: Upload multi files to S3
     */
    public function S3MultiUpload($field_name = 'file') {
        if (!empty($_FILES)) {

            $data = array();
            $cpt = isset($_FILES [$field_name]) ? count($_FILES [$field_name] ['name']) : 0;
            $files = $_FILES;

            for ($i = 0; $i < $cpt; $i++) {

                $_FILES [$field_name] ['name']      = $files [$field_name] ['name'] [$i];
                $_FILES [$field_name] ['type']      = $files [$field_name] ['type'] [$i];
                $_FILES [$field_name] ['tmp_name']  = $files [$field_name] ['tmp_name'] [$i];
                $_FILES [$field_name] ['error']     = $files [$field_name] ['error'] [$i];
                $_FILES [$field_name] ['size']      = $files [$field_name] ['size'] [$i];

                $arrTemp                            = $this->getFileInfo($_FILES[$field_name]);
                $arrTemp['uri']                     = $this->putToS3( $_FILES [$field_name]['tmp_name'] . "_thumb", $arrTemp['custom_filename']);
                $arrTemp['uri_thumb']               = $this->putToS3( $_FILES [$field_name]['tmp_name'] . "_thumb_thumb", $arrTemp['custom_filename_thumb']);
                $this->putToS3($_FILES [$field_name]['tmp_name'], $arrTemp['custom_filename_original']);
                $data[$i] = $arrTemp;
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
    public function S3Upload( $file = false, $field_name = '') {
        if( !$file ) {
            if(!empty($field_name)) {
                $file = isset($_FILES[$field_name]) && !empty($_FILES[$field_name]) ? $_FILES[$field_name] : false;
            }
        }

        if ( $file && isset($file['name']) && !empty($file['name']) ) {
            $data               = $this->getFileInfo($file);            
            $data['uri']        = $this->putToS3($file['tmp_name'] . "_thumb", $data['custom_filename']);
            $data['uri_thumb']  = $this->putToS3($file['tmp_name'] . "_thumb_thumb", $data['custom_filename_thumb']);
            $this->putToS3($file['tmp_name'], $data['custom_filename_original']);
            return $data;
        }
        return array();
    }

    public function localMultiUpload($folder_path = '') {
        return "";
    }

    public function localUpload() {

    }

    /**
     * @param $file
     * @return array
     * @description: Rename the upload file, resize and duplicated uploaded file in
     * tmp folder, then return the array information
     */
    protected function getFileInfo($file) {
        $data = array();
        $file_name                      = basename($file['name']);
        $ext                            = substr($file_name, strrpos($file_name, '.') + 1);
        $time                           = time();
        $name                           = md5(random_string('alnum', 20)) . "_" . time();
        $data['custom_filename_original'] = strtolower($name . "." . $ext);
        $data['custom_filename']        = strtolower($name . "_file." . $ext);
        $data['custom_filename_thumb']  = strtolower($name . "_thumb." . $ext);

        if( function_exists('getimagesize')) {
            // list($w, $h)    = getimagesize($file['tmp_name']);
            // $data['width']  = $w;
            // $data['height'] = $h;

            // resizeImage($file['tmp_name'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
            // list($w, $h) = getimagesize($file['tmp_name'] . "_thumb");
            // $data['width_thumb'] = $w;
            // $data['height_thumb'] = $h;

            resizeImage($file['tmp_name'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
            list($w, $h) = getimagesize($file['tmp_name'] . "_thumb");
            $data['width']  = $w;
            $data['height'] = $h;

            resizeImage($file['tmp_name'] . "_thumb", IMAGE_RESIZE_WIDTH_THUMB, IMAGE_RESIZE_HEIGHT_THUMB);
            list($w, $h) = getimagesize($file['tmp_name'] . "_thumb_thumb");
            $data['width_thumb'] = $w;
            $data['height_thumb'] = $h;
        }
        return $data;
    }

    /**
     * @param $filePath
     * @param $fileName
     * @return string
     * @description: Put the actual file into S3 AWS
     */
    protected function putToS3( $filePath, $fileName ) {
        $this->config->load('s3');
        $this->load->library('s3', array(
            "access_key"    => $this->config->item('access_key'),
            "secret_key"    => $this->config->item('secret_key'),
            "use_ssl"       => false,
            "verify_peer"   => true
        ));
        if ($this->s3->putObjectFile($filePath, $this->config->item('s3-bucket'), $this->config->item('s3-path') . "/" . $fileName, S3::ACL_PUBLIC_READ)) {
            return $this->config->item('s3-uri') . $fileName;
        } else {
            $err['code']    = 15;
            $err['msg']     = lang('Error: Something went wrong while uploading your file... sorry!');
            return $err;
        }
    }

        public function updateCoverListing($items){
        if($items){
            $this->load->model('listing_model');
            $this->load->helper('image');
            foreach ($items as $key => $item) {
                $listingDetail = $this->listing_model->get_listing_detail($item);
                if(!checkMediaExist($listingDetail->photo)){
                    $media =  $this->listing_model->get_media_by_listing('all', 0, 1, $listingDetail->id, 'id', 'ASC');
                    $data = array(
                        'photo' => null                        
                    );         
                    if($media){
                        $data = array(
                            'photo' => $media[0]->source                        
                        );                        
                    }
                    $this->listing_model->editListing($data,$listingDetail->id);
                }
               
            }
        }
    }
}