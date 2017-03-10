<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/modules/api/api/libraries/REST_Controller.php';

class Photosharing extends REST_Controller {

    function __construct() {

        // Construct our parent class
        parent::__construct();

        //load model
        $this->load->model('photosharing_model');

        //load lang
        $this->lang->load('api');
        //load helper
        $this->load->helper(array('form', 'url'));

        $this->load->helper('site');

        $this->load->helper('newsfeeds');
    }

    function newItem_post() {

        $this->_requireAuthToken();

        $content        = $this->post('content') ? $this->post('content') : null;

        $userId         = $this->_member->id;

        if( !$content ) {
            $error['msg'] = "Please input content for post updated";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $sharingId = $this->photosharing_model->addNewPhoto( $userId, $content );

        if( $sharingId ) {

            $media_files = $this->_doMultiUpload($this->config->item('listings_path'));

            if( $media_files && $sharingId ) {

                $data_insert = array();

                // insert data into newsfeed activities
                $this->db->insert("user_newsfeed_activities", array(
                    "photo_sharing_id" => $sharingId,
                    "newsFeedType" => ADD_SHARING_PHOTO,
                    "user_id"   => $userId,
                    "created_date" => now(),
                    "updated_date" => now()));

                $newfeedId = $this->db->insert_id();

                foreach ($media_files as $file) {

                    $media_insert = array();

                    $file_array = $file['upload_data'];

                    $media_insert["newfeed_id"] = $newfeedId;
                    $media_insert['source'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_array['file_name'];
                    $media_insert['created_date'] = now();
                    $media_insert['status'] = 1;
                    $media_insert['user_id'] = $this->_member->id;

                    $media_insert['type'] = 'PHOTO';

                    //resize
                    resizeImage($file_array['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                    $file_name_array = explode('.', $file_array['file_name']);
                    $media_insert['photo_thumb'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];

                    array_push($data_insert, $media_insert);
                }

                insert_user_media($data_insert);
            }
        }

        $this->response(array(), 200);
    }

    private function _doMultiUpload($folder_path) {
        $files = $_FILES;
        $this->load->library('upload');
        $this->load->helper('string');
        $this->load->helper('image');
        $this->load->library('image_lib');

        // upload an image options
        $config = array();
        $config ['upload_path'] = $this->config->item('api_upload_path') . $folder_path;
        $config ['allowed_types'] = allow_file_upload('review');
        $config ['encrypt_name'] = TRUE;

        if (!empty($_FILES)) {
            $data = array();
            $cpt = count($_FILES ['file'] ['name']);
            for ($i = 0; $i < $cpt; $i ++) {

                $_FILES ['file'] ['name'] = $files ['file'] ['name'] [$i];
                $_FILES ['file'] ['type'] = $files ['file'] ['type'] [$i];
                $_FILES ['file'] ['tmp_name'] = $files ['file'] ['tmp_name'] [$i];
                $_FILES ['file'] ['error'] = $files ['file'] ['error'] [$i];
                $_FILES ['file'] ['size'] = $files ['file'] ['size'] [$i];


                $file_name = basename($_FILES ['file'] ['name']);
                $ext = substr($file_name, strrpos($file_name, '.') + 1);
                $custom_filename = strtolower(random_string('alnum', 20) . "_file." . $ext);

                $config['file_name'] = $custom_filename;

                $this->upload->initialize($config);
                if (!$this->upload->do_upload('file')) {
                    $upload_errors = array('error' => $this->upload->display_errors());

                    $error['code'] = self::ERROR_CODE_UPLOAD_IMAGE_FAIL;
                    $error['msg'] = lang('Error: A problem occurred during file upload!');
                    $this->response($upload_errors, 200);
                } else {
                    $data[$i] = array('upload_data' => $this->upload->data());
                }
            }
            return $data;
        }
    }
}