<?php
class Media_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->helper(array('string', 'image'));
        $this->load->library('image_lib');
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
     * @param $file
     * @return array
     * @description: Rename the upload file, resize and duplicated uploaded file in
     * tmp folder, then return the array information
     */
    protected function getFileInfo($file) {
        $data = array();
        $file_name                      = basename($file['name']);
        $ext                            = substr($file_name, strrpos($file_name, '.') + 1);
        $data['custom_filename']        = strtolower(random_string('alnum', 20) . "_file." . $ext);
        $data['custom_filename_thumb']  = strtolower(random_string('alnum', 20) . "_thumb." . $ext);

        if( function_exists('getimagesize')) {
            list($w, $h)    = getimagesize($file['tmp_name']);
            $data['width']  = $w;
            $data['height'] = $h;

            resizeImage($file['tmp_name'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
            list($w, $h) = getimagesize($file['tmp_name'] . "_thumb");
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
    public function putToS3( $filePath, $fileName, $path = '' ) {
        $this->config->load('s3');
        $this->load->library('s3', array(
            "access_key"    => $this->config->item('access_key'),
            "secret_key"    => $this->config->item('secret_key'),
            "use_ssl"       => false,
            "verify_peer"   => true
        ));
        $path = $path ? $path : $this->config->item('s3-path');
        if ($this->s3->putObjectFile($filePath, $this->config->item('s3-bucket'), $path . "/" . $fileName, S3::ACL_PUBLIC_READ)) {
            return $this->config->item('s3-uri') . $fileName;
        } else {
            $err['code']    = 15;
            $err['msg']     = lang('Error: Something went wrong while uploading your file... sorry!');
            return $err;
        }
    }

    public function removeS3Media($image){
        $this->config->load('s3');
        $this->load->library('s3', array(
            "access_key"    => $this->config->item('access_key'),
            "secret_key"    => $this->config->item('secret_key'),
            "use_ssl"       => false,
            "verify_peer"   => true
        ));
        try {
            $this->s3->deleteObject($this->config->item('s3-bucket'), $image);
        } catch(Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    public function insertMediaTmpData($data){
        $this->db->insert('image_tmp', $data);
    }
    public function getMediaTmpData($data){
        $results = $this->db->get('image_tmp');

        return $results->num_rows() > 0 ? $results->result() : array(); 
    }
    public function checkFileUpload($image){
        $this->db->where('photo_old', $image);
        $result = $this->db->get('image_tmp');

        return $result->num_rows() > 0 ? $result->row() : false;
    }

    public function removeMediaTmp(){
        $results = $this->db->get('image_tmp');

        if($results->num_rows() > 0){
            $results = $results->result();

            foreach ($results  as $key => $item) {
                $this->removeS3Media($this->getS3KeyFile($item->photo_new));
                $this->db->where('id', $item->id);
                $this->db->delete('image_tmp');
            }
        }
    }

    public function applyMediaFromTmp($table, $field_key, $field_value, $data){
        $this->db->where($field_key, $field_value);
        $this->db->update($table, $data);
    }

    public function removeMediaFromTable($table, $field_key, $field_value, $field_name = ''){
        if($table == 'user_media'){
            $this->db->where($field_key, $field_value);
            $this->db->delete($table);
        }else{
            $this->db->where($field_key, $field_value);
            $this->db->update($table, array($field_name => null));
        }

    }
}