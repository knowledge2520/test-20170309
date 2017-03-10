<?php

defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/modules/api/api/libraries/REST_Controller.php';

class Listing extends REST_Controller {

    function __construct() {
        // Construct our parent class
        parent::__construct();

        //load model
        $this->load->model('listing_model');
        $this->load->model('review_model');
        $this->load->model('member_model');
        $this->load->model('tip_model');

        //load lang
        $this->lang->load('api');
    }

    function categories_post() {
// 		$this->_requireAuthToken();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;

        $data['items'] = $this->listing_model->get_listing_categories('all', $start, $limit, $keyword);
        $data['totalItem'] = $this->listing_model->get_listing_categories('count', $start, $limit, $keyword);
        $data['totalPage'] = intval($data['totalItem']) / $limit;
        $data['limit'] = intval($limit);

        if ($data['items']) {
            foreach ($data['items'] as $key => $item) {
                $data['items'][$key] = format_output_data($item);
            }
        }

        $this->response($data, 200);
    }

    function listingItems_post() {

        //require location
        //$this->_requireLocation();
        $this->_requireAuthToken();
        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $category_id = $this->post('category_id') ? $this->post('category_id') : false;
        $latitude = $this->post('latitude') ? $this->post('latitude') : false;
        $longitude = $this->post('longitude') ? $this->post('longitude') : false;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;
        $sort_by = $this->post('sort_by') ? $this->post('sort_by') : false;
        $sort_value = $this->post('sort_value') ? $this->post('sort_value') : 'DESC';
        $search_distance = $this->post('search_distance') ? $this->post('search_distance') : self::DISTANCE_DEFAULT_SEARCH;

        $user_options = $this->member_model->get_user_options($this->_member->id);

        if ($user_options) {
            $option_lock = $user_options->location_lock;
            $option_location_latitude = !empty($user_options->location_city) && explode(',', $user_options->location_city)[0] ? trim(explode(',', $user_options->location_city)[0]) : false;
            $option_location_longitude = !empty($user_options->location_city) && explode(',', $user_options->location_city)[1] ? trim(explode(',', $user_options->location_city)[1]) : false;
        }

        $user_location = array(
            'latitude' => $user_options && $option_lock == 'on' && $option_location_latitude ? $option_location_latitude : $latitude,
            'longitude' => $user_options && $option_lock == 'on' && $option_location_longitude ? $option_location_longitude : $longitude,
            'search_distance' => $search_distance
        );

        if ($sort_by && !in_array($sort_by, array('distance', 'newest', 'average_rating'))) {
            $sort_by = false;
        }

        $data['items'] = $this->listing_model->get_listings_by_category('all', $start, $limit, $category_id, $user_location, $keyword, $sort_by, $sort_value);
        $data['totalItem'] = $this->listing_model->get_listings_by_category('count', $start, $limit, $category_id, $user_location, $keyword, $sort_by, $sort_value);
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);

        if (!empty($data['items'])) {
            foreach ($data['items'] as $key => $listing) {
                $listing_detail = $this->listing_model->get_listing_detail($listing->id, true, true, true, true, true, $this->_member->id);
                if (isset($listing->distance)) {
                    $listing_detail->distance = $listing->distance;
                } else {
                    $listing_detail->distance = '';
                }
                $data['items'][$key] = $listing_detail;
            }
        }
        $this->response($data, 200);
    }

    public function listingDetail_post() {
        $this->_requireAuthToken();

        $id = $this->post('id') ? $this->post('id') : false;
        $lat = $this->post('latitude') ? $this->post('latitude') : false;
        $long = $this->post('longitude') ? $this->post('longitude') : false;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('id', 'Listing id', 'required');

        if ($this->form_validation->run() == FALSE) {
            $response['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('id')) {
                $response['msg'] = strip_tags(form_error('id'));
            }
            $this->response($response, 200);
        }

        $listing_detail = $this->listing_model->get_listing_detail($id, true, true, true, true, true, $this->_member->id);
        if ($listing_detail) {
            $listing_detail->distance = "";
            if ((!empty($lat) && !empty($long)) AND ( !empty($listing_detail->latitude) && !empty($listing_detail->longitude))) {
                $distance = $this->listing_model->calculate_google_distance($lat, $listing_detail->latitude, $long, $listing_detail->longitude);
                $listing_detail->distance = $distance;
            }
            $response['item'] = $listing_detail;
        } else {
            $response['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
            $response['msg'] = lang('Item not found');
        }
        $this->response($response, 200);
    }

    public function addUserLikeTip_post() {
        $this->_requireAuthToken();

        $data['tip_id'] = $this->post('tip_id') ? $this->post('tip_id') : false;
        $data['user_id'] = $this->_member->id;
        $data['type'] = 0;
        $data['created_date'] = now();

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('type', 'Type', 'required');
        $this->form_validation->set_rules('tip_id', 'Tip', 'required');

        if ($this->form_validation->run() == FALSE) {
            if (form_error('tip_id')) {
                $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
                $error['msg'] = strip_tags(form_error('tip_id'));
                $this->response($error, 200);
            }
        }

        //check like
        $check_like = $this->member_model->check_user_like_tip($data['user_id'], $data['tip_id']);
        if ($check_like) {
            $row_id = $check_like->id;
            $this->member_model->delete_like($row_id);
            $response['msg'] = lang('Remove like successful');
            $response['item'] = $this->tip_model->get_tip_detail($data['tip_id'], true);
            $response['like_type'] = strval(1);
            $this->response($response, 200);
        } else {
            //ADD like
            if ($this->member_model->add_like($data)) {
                $response['msg'] = lang('Add new successful');
                $response['item'] = $this->tip_model->get_tip_detail($data['tip_id'], true);
                $response['like_type'] = strval(0);

                //send push to user who post tip
                $this->load->model('notification_model');
                $this->load->helper('notification');
                $actor_user_id = $response['item']->user_id;
                $name_user_action = $this->_member->first_name . ' ' . $this->_member->last_name;
                $message = $name_user_action . ' ' . lang('like your tip');
                $action_type = get_action_type(self::PUSH_TYPE_LIKE_TIP);
                $source_id = $response['item']->id;
                $data_push = array(
                    'action_type' => self::PUSH_TYPE_LIKE_TIP,
                    'sender_id' => $this->_member->id,
                    'sender_name' => $name_user_action,
                    'type' => 'tip',
                    'tip_id' => $source_id,
                    'bages_unread_notification' => count_unread_notification($actor_user_id) + 1 ,
                );
                $this->notification_model->send_push_notification($actor_user_id, $message, $data_push, $action_type->id, $source_id);
                //end push

                $this->response($response, 200);
            } else {
                $error['msg'] = lang('Item not found');
                $error['code'] = self::ERROR_CODE_404;
                $this->response($error, 200);
            }
        }
    }

    public function addUserBookMark_post() {
        $this->_requireAuthToken();

        $data['business_id'] = $this->post('listing_id') ? $this->post('listing_id') : false;
        $data['user_id'] = $this->_member->id;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('listing_id', 'Listing', 'required|integer');
        if ($this->form_validation->run() == FALSE) {
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('listing_id')) {
                $error['msg'] = strip_tags(form_error('listing_id'));
                $this->response($error, 200);
            }
        }
        $check_user_bookmark = $this->listing_model->check_user_added_bookmark($data['user_id'], $data['business_id']);
        if ($check_user_bookmark) {
            //delete
            $this->listing_model->deleteBookMarkListing($check_user_bookmark->id);

            $response['msg'] = lang('delete bookmark successful');
            $response['bookmark_status'] = strval(0);
            $this->response($response, 200);
        } else {
            $this->listing_model->addBookMarkListing($data);

            $response['msg'] = lang('Add new bookmark successful');
            $response['bookmark_status'] = strval(1);
            $this->response($response, 200);
        }
    }

    public function addMediaListing_post() {
        $this->_requireAuthToken();
        $this->load->library('image_lib');

        $listing_id = $this->post('listing_id') ? $this->post('listing_id') : 0;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('listing_id', 'Listing', 'required');
        if ($this->form_validation->run() == FALSE) {
            if (form_error('listing_id')) {
                $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
                $error['msg'] = strip_tags(form_error('listing_id'));
                $this->response($error, 200);
            }
        }

        //upload photo
        $media_files = $this->_doMultiUpload($this->config->item('listings_path'));

        //save media to db
        if ($media_files && $listing_id) {
            $data_insert = array();
            foreach ($media_files as $file) {
                $file_array = array();
                $media_insert = array();

                $file_array = $file['upload_data'];

                $media_insert['business_id'] = $listing_id;
                $media_insert['user_id'] = $this->_member->id;
                $media_insert['source'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_array['file_name'];
                $media_insert['created_date'] = now();
                ;
                if (empty($file_array['image_type'])) {
                    //video
                    $media_insert['type'] = 'VIDEO';
                    $media_insert['photo_thumb'] = null;
                } else {
                    $media_insert['type'] = 'PHOTO';

                    //resize
                    $this->load->helper('image');
                    resizeImage($file_array['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                    $file_name_array = explode('.', $file_array['file_name']);
                    $media_insert['photo_thumb'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];
                }
                array_push($data_insert, $media_insert);
            }
            //insert
            insert_user_media($data_insert);
        }
        $response = array(
            'msg' => lang('photos_added_successfully_pending_for_approval')
        );
        $this->response($response, 200);
    }

    public function addListing_post() {
        $this->_requireAuthToken();
        $this->load->library('image_lib');

        //require
        $data['user_id'] = $this->_member->id;
        $data['name'] = $this->post('name') ? $this->post('name') : null;
        $data['address'] = $this->post('address') ? $this->post('address') : null;
        $data['country'] = $this->post('country') ? $this->post('country') : null;
        $categories = $this->post('category_id') ? json_decode($this->post('category_id')) : false;
        //optional
        $data['hour'] = $this->post('hours') ? $this->post('hours') : null;
        $data['phone'] = $this->post('phone') ? $this->post('phone') : null;
        $data['website'] = $this->post('website') ? $this->post('website') : null;
//     	$data['latitude'] 		= $this->post('latitude') ? $this->post('latitude') : false;
//     	$data['longitude'] 		= $this->post('longitude') ? $this->post('longitude') : false;
        //static
        $data['status'] = 0; //default is disable need confirm
        $data['created_date'] = now();

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('name', 'Listing name', 'required');
        $this->form_validation->set_rules('address', 'Listing address', 'required');
        $this->form_validation->set_rules('category_id[]', 'Categories', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('name')) {
                $error['msg'] = strip_tags(form_error('name'));
                $this->response($error, 200);
            }
            if (form_error('address')) {
                $error['msg'] = strip_tags(lang('address_required'));
                $this->response($error, 200);
            }
            if (form_error('category_id')) {
                $error['msg'] = strip_tags(form_error('category_id'));
                $this->response($error, 200);
            }
        }
        $location = isset($data['country']) && $data['country'] ?  get_location_from_address($data['address'] . ', ' . $data['country']) : get_location_from_address($data['address']);
        if (empty($location)) {
            $error['code'] = self::ERROR_CODE_LOCATION_NOT_FOUND;
            $error['msg'] = lang('Can not found location with your address');
            $this->response($error, 200);
        } else {
            $data['latitude'] = $location['lat'];
            $data['longitude'] = $location['long'];
        }
        $listing_id = $this->listing_model->addListing($data);
        if ($listing_id) {
            $data_business_category = array();
            //add business category
            foreach ($categories as $cat_id) {
                $data_business_category[] = array(
                    'business_id' => $listing_id,
                    'business_category_id' => $cat_id
                );
            }
            if (!empty($data_business_category)) {
                $this->db->insert_batch('business_items_category', $data_business_category);
            }

            //upload photo
            $media_files = $this->_doMultiUpload($this->config->item('listings_path'));

            //save media to db
            if ($media_files) {
                $data_insert = array();
                foreach ($media_files as $file) {
                    $file_array = array();
                    $media_insert = array();

                    $file_array = $file['upload_data'];

                    $media_insert['business_id'] = $listing_id;
                    $media_insert['user_id'] = $this->_member->id;
                    $media_insert['source'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_array['file_name'];
                    $media_insert['status'] = 1;
                    $media_insert['created_date'] = now();
                    ;
                    if (empty($file_array['image_type'])) {
                        //video
                        $media_insert['type'] = 'VIDEO';
                        $media_insert['photo_thumb'] = null;
                    } else {
                        $media_insert['type'] = 'PHOTO';

                        //resize
                        $this->load->helper('image');
                        resizeImage($file_array['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                        $file_name_array = explode('.', $file_array['file_name']);
                        $media_insert['photo_thumb'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];
                    }
                    array_push($data_insert, $media_insert);
                }
                //insert
                insert_user_media($data_insert);

                //update listing photo
                if (!empty($data_insert)) {
                    $this->listing_model->editListing(array('photo' => $data_insert[0]['source']), $listing_id);
                }
            }


            $response['msg'] = lang('Add new successful');
            $response['item'] = $this->listing_model->get_listing_detail($listing_id, true, true, true, true, true);
            $this->response($response, 200);
        } else {
            $error['msg'] = lang('Item not found');
            $error['code'] = self::ERROR_OBJECT_NOT_FOUND;
            $this->response($error, 200);
        }
    }

    public function editListing_post() {
        $this->_requireAuthToken();

        //require
        $data['user_id'] = $this->_member->id;
        $data['name'] = $this->post('name') ? $this->post('name') : false;
        $data['address'] = $this->post('address') ? $this->post('address') : false;
        $categories = $this->post('category_id') ? $this->post('category_id') : false;
        //optional
        $data['hour'] = $this->post('hours') ? $this->post('hours') : false;
        $data['phone'] = $this->post('phone') ? $this->post('phone') : false;
        $data['website'] = $this->post('website') ? $this->post('website') : false;
        //     	$data['latitude'] 		= $this->post('latitude') ? $this->post('latitude') : false;
        //     	$data['longitude'] 		= $this->post('longitude') ? $this->post('longitude') : false;
        //static
        $data['status'] = 0; //default is disable need confirm
        $data['created_date'] = now();
        $id = $this->post('id') ? $this->post('id') : FALSE;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('name', 'Listing name', 'required');
        $this->form_validation->set_rules('address', 'Listing address', 'required');
        $this->form_validation->set_rules('category_id[]', 'Categories', 'required');
        $this->form_validation->set_rules('id', 'Listing ID', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('name')) {
                $error['msg'] = strip_tags(form_error('name'));
                $this->response($error, 200);
            }
            if (form_error('address')) {
                $error['msg'] = strip_tags(form_error('address'));
                $this->response($error, 200);
            }
            if (form_error('category_id')) {
                $error['msg'] = strip_tags(form_error('category_id'));
                $this->response($error, 200);
            }
            if (form_error('id')) {
                $error['msg'] = strip_tags(form_error('id'));
                $this->response($error, 200);
            }
        }

        $location = get_location_from_address($data['address']);
        if (empty($location)) {
            $error['code'] = self::ERROR_CODE_LOCATION_NOT_FOUND;
            $error['msg'] = lang('Can not found location with your address');
            $this->response($error, 200);
        } else {
            $data['latitude'] = $location['lat'];
            $data['longitude'] = $location['long'];
        }

        $edit_process = $this->listing_model->editListing($data, $id);
        if ($edit_process) {
            //remove all category first
            $this->listing_model->remove_all_categories($id);

            $data_business_category = array();
            //add business category
            foreach ($categories as $cat_id) {
                $data_business_category[] = array(
                    'business_id' => $id,
                    'business_category_id' => $cat_id
                );
            }
            if (!empty($data_business_category)) {
                $this->db->insert_batch('business_items_category', $data_business_category);
            }
            $response['msg'] = lang('Edit successful');
            $response['item'] = $this->listing_model->get_listing_detail($id, true, true, true, true, true, $this->_member->id);
            $this->response($response, 200);
        } else {
            $error['msg'] = lang('Item not found');
            $error['code'] = self::ERROR_OBJECT_NOT_FOUND;
            $this->response($error, 200);
        }
    }

    public function getMediaListing_post() {
        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $listing_id = $this->post('listing_id') ? $this->post('listing_id') : false;

        $data['items'] = $this->listing_model->get_media_by_listing('all', $start, $limit, $listing_id, 'id', 'DESC');
        $data['totalItem'] = $this->listing_model->get_media_by_listing('count', $start, $limit, $listing_id);
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);

        if (!empty($data['items'])) {
            foreach ($data['items'] as $key => $file_item) {
                $data['items'][$key] = format_output_data($file_item);
            }
        }
        $this->response($data, 200);
    }

    private function _doMultiUpload($folder_path) {
        $files = $_FILES;
        $this->load->library('upload');
        $this->load->helper('string');

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

    public function getReviewDetail_post() {
        $this->_requireAuthToken();

        $id = $this->post('id') ? $this->post('id') : false;
        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('id', 'Review id', 'required');

        if ($this->form_validation->run() == FALSE) {
            $response['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('id')) {
                $response['msg'] = strip_tags(form_error('id'));
            }
            $this->response($response, 200);
        }
        $review = $this->review_model->get_review_detail($id, true, true, true);
        if ($review) {
            //total friend	of user comment
            $review->total_friend = $this->member_model->get_user_friends('count', $review->user_id);
            //total photo of user comment
            $review->total_photo = $this->member_model->get_photos_by_user('count', 0, false, $review->user_id, 1);
            //total review of user
            $review->total_review = $this->review_model->get_reviews_by_user('count', 0, false, $review->user_id, 'id', 'ASC');

            $business_info = $this->listing_model->get_listing_detail($review->business_id, true, true, true);
            $review->business_info = format_output_data($business_info);

            //check like
            $like_status = $this->member_model->check_user_like_review($this->_member->id, $review->id);
            if ($like_status) {
                $review->like_type = strval($like_status->type);
            } else {
                $review->like_type = '';
            }

            $response['item'] = format_output_data($review);
        } else {
            $response['error'] = lang('Item not found');
            $response['code'] = self::ERROR_CODE_404;
        }
        $this->response($response, 200);
    }

    public function getCommentsReview_post() {
        $id = $this->post('review_id') ? $this->post('review_id') : false;
        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('review_id', 'Review id', 'required');

        if ($this->form_validation->run() == FALSE) {
            $response['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('review_id')) {
                $response['msg'] = strip_tags(form_error('review_id'));
            }
            $this->response($response, 200);
        }

        $total_comments = $this->review_model->get_comments_by_review('count', $start, $limit, 'id', 'DESC', $id, false);
        $comments = $this->review_model->get_comments_by_review('all', $start, $limit, 'id', 'DESC', $id, false);

        if ($comments) {
            foreach ($comments as $key => $comment) {
                $comments[$key] = format_output_data($comment);
            }
        } else {
            //$response['items'] 		= $comments;
        }
        $response['items'] = $comments;
        $response['totalItem'] = $total_comments;
        $response['totalPage'] = ceil(intval($total_comments) / $limit);
        $response['limit'] = intval($limit);

        $this->response($response, 200);
    }

    public function getReviewsByListing_post() {
        $id = $this->post('listing_id') ? $this->post('listing_id') : false;
        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('listing_id', 'Listing id', 'required');

        if ($this->form_validation->run() == FALSE) {
            $response['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('listing_id')) {
                $response['msg'] = strip_tags(form_error('listing_id'));
            }
            $this->response($response, 200);
        }
        $total_reviews = $this->review_model->get_reviews_by_listing('count', $start, $limit, $id, 'id', 'DESC', $keyword);
        $reviews = $this->review_model->get_reviews_by_listing('all', $start, $limit, $id, 'id', 'DESC', $keyword);

        if ($reviews) {
            foreach ($reviews as $key => $review) {
                $review_detail = $this->review_model->get_review_detail($review->id, true, true, true);
                $reviews[$key] = format_output_data($review_detail);
            }
        }
        $response['items'] = $reviews;
        $response['totalItem'] = $total_reviews;
        $response['totalPage'] = ceil(intval($total_reviews) / $limit);
        $response['limit'] = intval($limit);

        $this->response($response, 200);
    }

    public function getTipsByListing_post() {
        $id = $this->post('listing_id') ? $this->post('listing_id') : false;
        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('listing_id', 'Listing id', 'required');

        if ($this->form_validation->run() == FALSE) {
            $response['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('listing_id')) {
                $response['msg'] = strip_tags(form_error('listing_id'));
            }
            $this->response($response, 200);
        }
        $total_tips = $this->tip_model->get_tips_by_listing('count', $start, $limit, $id, 'id', 'DESC');
        $tips = $this->tip_model->get_tips_by_listing('all', $start, $limit, $id, 'id', 'DESC');
        $tips_items = array();

        if ($tips) {
            foreach ($tips as $key => $tip) {
                $tip_detail = $this->tip_model->get_tip_detail($tip->id, true);
                $tips_items[$key] = format_output_data($tip_detail);
            }
        }
        $response['items'] = $tips_items;
        $response['totalItem'] = $total_tips;
        $response['totalPage'] = ceil(intval($total_tips) / $limit);
        $response['limit'] = intval($limit);

        $this->response($response, 200);
    }

    function seachListing_post() {
        //require location
        //$this->_requireLocation();
        $response = array();
        $response['items'] = array();
        $response['categories'] = array();

        $start = 0;
        $limit = API_NUM_RECORD_PER_PAGE;
        $category_id = $this->post('category_id') ? $this->post('category_id') : false;
        $latitude = $this->post('latitude') ? $this->post('latitude') : false;
        $longitude = $this->post('longitude') ? $this->post('longitude') : false;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;
        $category_group = $this->post('category_group') == 1 ? $this->post('category_group') : 0; //true or false
        $search_distance = $this->post('search_distance') ? $this->post('search_distance') : self::DISTANCE_DEFAULT_SEARCH;

        $user_location = array(
            'latitude' => $latitude,
            'longitude' => $longitude,
            'search_distance' => $search_distance //self::DISTANCE_DEFAULT_SEARCH
        );

        if ($category_group) {

            $categories = $this->listing_model->get_listing_categories('all', 0, 4, $keyword); //limit 4
            if ($categories) {
                foreach ($categories as $key => $item) {
                    $response['categories'][$key] = format_output_data($item);
                }
                //rest is listing item
                $limit = $limit - count($categories);
            }
        }

        $listing_items = $this->listing_model->search_listings('all', $start, $limit, $category_id, $user_location, $keyword);

        if (!empty($listing_items)) {
            foreach ($listing_items as $key => $listing) {
                $listing_detail = $this->listing_model->get_listing_detail($listing->id, false, true, false, false, false);
//     			$listing_detail->distance 	= $listing->distance;
                if (isset($listing->distance)) {
                    $listing_detail->distance = $listing->distance;
                } else {
                    $listing_detail->distance = '';
                }
                $response['items'][$key] = $listing_detail;
            }
        }
        $this->response($response, 200);
    }

    function deleteCommentReview_post() {

        $this->_requireAuthToken();

        $comment_id = $this->post('comment_id') ? $this->post('comment_id') : 0;
        $review_id = $this->post('review_id') ? $this->post('review_id') : 0;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('comment_id', 'Comment ID', 'required|integer');
        $this->form_validation->set_rules('review_id', 'Review ID', 'required|integer');
        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('comment_id')) {
                $error['msg'] = strip_tags(form_error('comment_id'));
            }
            if (form_error('review_id')) {
                $error['msg'] = strip_tags(form_error('review_id'));
            }
            $this->response($error, 200);
        }

        if ($this->listing_model->check_exist_comment($review_id, $comment_id)) {
            //delete comment
            $this->listing_model->delete_comment_review($comment_id);

            //log    	
            log_message("info", "===========================");
            log_message("info", "DELETE COMMENT ID $comment_id");
            log_message("info", "===========================");

            $this->response(array('msg' => lang('delete comment review success')), 200);
        } else {
            $error['msg'] = lang('Item not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error, 200);
        }
    }

    public function deleteTip_post() {
        $this->_requireAuthToken();

        $tip_id = $this->post('tip_id') ? $this->post('tip_id') : 0;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('tip_id', 'Note ID', 'required|integer');
        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('tip_id')) {
                $error['msg'] = strip_tags(form_error('tip_id'));
            }
            $this->response($error, 200);
        }
        if ($this->listing_model->get_tip_by('id', $tip_id)) {
            //delete comment
            $this->listing_model->delete_tip($tip_id);

            //log    	
            log_message("info", "===========================");
            log_message("info", "DELETE TIP ID $tip_id");
            log_message("info", "===========================");

            $this->response(array('msg' => lang('delete tip success')), 200);
        } else {
            $error['msg'] = lang('Item not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error, 200);
        }
    }

    public function deleteReview_post() {
        $this->_requireAuthToken();

        $review_id = $this->post('review_id') ? $this->post('review_id') : 0;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('review_id', 'Review ID', 'required|integer');
        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('review_id')) {
                $error['msg'] = strip_tags(form_error('review_id'));
            }
            $this->response($error, 200);
        }
        if ($this->listing_model->get_review_by('id', $review_id)) {
            //delete comment
            $this->listing_model->delete_review($review_id);

            //log    	
            log_message("info", "===========================");
            log_message("info", "DELETE REVIEW ID $review_id");
            log_message("info", "===========================");

            $this->response(array('msg' => lang('delete review success')), 200);
        } else {
            $error['msg'] = lang('Item not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error, 200);
        }
    }

}
