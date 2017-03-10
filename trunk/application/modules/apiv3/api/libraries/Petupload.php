<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Petupload {

    private $ci;

    private $status = true;

    function __construct($params = array())
    {
        $this->ci = &get_instance();
        $this->ci->load->library('upload');
        $this->ci->load->helper(array('form', 'url', 'site'));
    }

    public function doMultiUpload( $folder_path, $fieldName = 'file', $mediaType = '' ) {
        if( $this->ci->config->item('upload') == 's3-aws' ) {
            return $this->S3Upload($folder_path, $fieldName, $mediaType);
        } else {
            return $this->localUpload($folder_path);
        }
    }

    public function store( $file = false, $field_name, $mediaType = '' ) {

        if( $this->ci->config->item('upload') == 's3-aws' ) {
            $this->ci->config->load('s3');
            $this->ci->load->library('image_lib');

            $this->ci->load->library('s3', array(
                "access_key"    => $this->ci->config->item('access_key'),
                "secret_key"    => $this->ci->config->item('secret_key'),
                "use_ssl"       => false,
                "verify_peer"   => true
            ));

            if( !$file ) {
                $file = $_FILES[$field_name];
            }

            if ( !empty($file['name']) ) {

                $data = array();

                $file_name      = basename($file['name']);
                $ext            = substr($file_name, strrpos($file_name, '.') + 1);
                $custom_filename= strtolower(random_string('alnum', 20) . "_file." . $ext);

                    //move the file
                    if ($this->ci->s3->putObjectFile($file['tmp_name'], $this->ci->config->item('s3-bucket'), $this->ci->config->item('s3-path') . "/" . $custom_filename, S3::ACL_PUBLIC_READ)) {
                        if( function_exists('getimagesize')) {
                            list($w, $h)    = getimagesize($file['tmp_name']);
                            $data['width']  = $w;
                            $data['height'] = $h;
                        }
                        $data['uri'] = $this->ci->config->item('s3-uri') . $custom_filename;
                        $data['media_type'] = $mediaType;
                        // resize image
                        $custom_filename_thumb = strtolower(random_string('alnum', 20) . "_thumb." . $ext);
                        resizeImage($file['tmp_name'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                        if ($this->ci->s3->putObjectFile($file['tmp_name'] . "_thumb", $this->ci->config->item('s3-bucket'), $this->ci->config->item('s3-path') . "/" . $custom_filename_thumb, S3::ACL_PUBLIC_READ)) {
                            if( function_exists('getimagesize')) {
                                list($w, $h) = getimagesize($file['tmp_name'] . "_thumb");
                                $data['width_thumb'] = $w;
                                $data['height_thumb'] = $h;
                            }
                            $data['uri_thumb']    = $this->ci->config->item('s3-uri') . $custom_filename_thumb;
                        }
                    } else {
                        $err['code']    = 15;
                        $err['msg']     = lang('Error: Something went wrong while uploading your file... sorry!');
                        return $err;
                    }

                return $data;
            }
        } else {
            $this->ci->load->helper('string');
            $this->ci->load->helper('image');
            $this->ci->load->library('image_lib');

            $config = array();
            $config ['upload_path']     = $this->ci->config->item('api_upload_path') . $this->ci->config->item('listings_path');
            $config ['allowed_types']   = allow_file_upload('review');
            $config ['encrypt_name']    = TRUE;

            $file_name = basename($file['name']);
            $ext = substr($file_name, strrpos($file_name, '.') + 1);
            $custom_filename = strtolower(random_string('alnum', 20) . "_file." . $ext);

            $config['file_name'] = $custom_filename;

            $this->ci->upload->initialize($config);

            if (!$this->ci->upload->do_upload($field_name)) {
                $err['error']   = array('error' => $this->ci->upload->display_errors());
                $err['code']    = 15;
                $err['msg']     = lang('Error: A problem occurred during file upload!');
                return $err;
            } else {
                return $this->ci->upload->data();
            }
        }
    }

    public function localUpload($folder_path = '') {
        $files = $_FILES;
        $this->ci->load->helper('string');
        $this->ci->load->helper('image');
        $this->ci->load->library('image_lib');

        // upload an image options
        $config = array();
        $config ['upload_path']     = $this->ci->config->item('api_upload_path') . $folder_path;
        $config ['allowed_types']   = allow_file_upload('review');
        $config ['encrypt_name']    = TRUE;

        if (!empty($_FILES)) {

            $data = array();
            $cpt = isset($_FILES ['file']) ? count($_FILES ['file'] ['name']) : 0;

            for ($i = 0; $i < $cpt; $i ++) {

                $_FILES ['file'] ['name']       = $files ['file'] ['name'] [$i];
                $_FILES ['file'] ['type']       = $files ['file'] ['type'] [$i];
                $_FILES ['file'] ['tmp_name']   = $files ['file'] ['tmp_name'] [$i];
                $_FILES ['file'] ['error']      = $files ['file'] ['error'] [$i];
                $_FILES ['file'] ['size']       = $files ['file'] ['size'] [$i];

                $file_name = basename($_FILES ['file'] ['name']);
                $ext = substr($file_name, strrpos($file_name, '.') + 1);
                $custom_filename = strtolower(random_string('alnum', 20) . "_file." . $ext);

                $config['file_name'] = $custom_filename;

                $this->ci->upload->initialize($config);

                if (!$this->ci->upload->do_upload('file')) {
                    $this->error['error']   = array('error' => $this->ci->upload->display_errors());
                    $this->error['code']    = 15;
                    $this->error['msg']     = lang('Error: A problem occurred during file upload!');

                } else {
                    $data[$i] = array('upload_data' => $this->ci->upload->data());
                }
            }
            return $data;
        }
    }

    public function S3Upload($folder_path = '', $field_name = 'file', $mediaType = '') {
        $this->ci->load->helper(array('string', 'image'));
        $this->ci->config->load('s3');
        $this->ci->load->library('image_lib');

        $this->ci->load->library('s3', array(
            "access_key"    => $this->ci->config->item('access_key'),
            "secret_key"    => $this->ci->config->item('secret_key'),
            "use_ssl"       => false,
            "verify_peer"   => true
        ));

        if (!empty($_FILES)) {

            $data = array();
            $cpt = isset($_FILES [$field_name]) ? count($_FILES [$field_name] ['name']) : 0;
            $files = $_FILES;

            for ($i = 0; $i < $cpt; $i++) {

                $_FILES [$field_name] ['name'] = $files [$field_name] ['name'] [$i];
                $_FILES [$field_name] ['type'] = $files [$field_name] ['type'] [$i];
                $_FILES [$field_name] ['tmp_name'] = $files [$field_name] ['tmp_name'] [$i];
                $_FILES [$field_name] ['error'] = $files [$field_name] ['error'] [$i];
                $_FILES [$field_name] ['size'] = $files [$field_name] ['size'] [$i];

                $file_name = basename($_FILES [$field_name] ['name']);
                $ext = substr($file_name, strrpos($file_name, '.') + 1);
                $custom_filename = strtolower(random_string('alnum', 20) . "_file." . $ext);

                //move the file
                if ($this->ci->s3->putObjectFile($_FILES [$field_name]['tmp_name'], $this->ci->config->item('s3-bucket'), $this->ci->config->item('s3-path') . "/" . $custom_filename, S3::ACL_PUBLIC_READ)) {
                    if( function_exists('getimagesize')) {
                        list($w, $h) = getimagesize($_FILES [$field_name] ['tmp_name']);
                        $data[$i]['width'] = $w;
                        $data[$i]['height'] = $h;
                    }
                    $data[$i]['uri']    = $this->ci->config->item('s3-uri') . $custom_filename;
                    $data[$i]['media_type'] = $mediaType;
                    // resize image
                    $custom_filename_thumb = strtolower(random_string('alnum', 20) . "_thumb." . $ext);
                    resizeImage($_FILES [$field_name]['tmp_name'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                    if ($this->ci->s3->putObjectFile($_FILES [$field_name]['tmp_name'] . "_thumb", $this->ci->config->item('s3-bucket'), $this->ci->config->item('s3-path') . "/" . $custom_filename_thumb, S3::ACL_PUBLIC_READ)) {
                        if( function_exists('getimagesize')) {
                            list($w, $h) = getimagesize($_FILES [$field_name] ['tmp_name'] . "_thumb");
                            $data[$i]['width_thumb'] = $w;
                            $data[$i]['height_thumb'] = $h;
                        }
                        $data[$i]['uri_thumb']    = $this->ci->config->item('s3-uri') . $custom_filename_thumb;
                    }
                } else {
                    $err['code']    = 15;
                    $err['msg']     = lang('Error: Something went wrong while uploading your file... sorry!');
                    return $err;
                }
            }
            return $data;
        }
    }

    public function getUploadStatus() {
        return $this->status;
    }


}