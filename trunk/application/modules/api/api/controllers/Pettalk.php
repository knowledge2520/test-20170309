<?php

defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/modules/api/api/libraries/REST_Controller.php';

/**
 * 
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @created_date: May 5, 2015
 */
class Pettalk extends REST_Controller {

    function __construct() {
        // Construct our parent class
        parent::__construct();

        //load model
        $this->load->model('pettalk_model');
        $this->load->model('member_model');
        //load lang
        $this->lang->load('api');
        //load helper
        $this->load->helper(array('form', 'url'));
    }

    function add_post() {
        $this->_requireAuthToken();

        $data['name'] = $this->post('name') ? $this->post('name') : null;
        $data['is_popular'] = $this->post('is_popular') ? $this->post('is_popular') : false;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('name', 'Name', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error_list = $this->form_validation->error_array();

            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('name')) {
                $error['msg'] = strip_tags(form_error('name'));
            }
            $this->response($error, 200);
        }
        //upload photo and overwrite profile photo
        $photo = $this->_doUpload($this->config->item('pet_path'));
        if ($photo) {
            //resize
            $this->load->helper('image');
            resizeImage($photo['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);

            $data['photo'] = $this->config->item('api_upload_path') . $this->config->item('pet_path') . $photo['file_name'];
            $file_name_array = explode('.', $photo['file_name']);
            $data['photo_thumb'] = $this->config->item('api_upload_path') . $this->config->item('pet_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];
        }
        //add category
        $id = $this->pettalk_model->add($data);
        if ($id) {
            $category_info = $this->pettalk_model->get_pettalk($id);
            $response = $category_info;

            $this->response($response, 200);
        }
        $error['msg'] = lang('Item not found');
        $error['code'] = self::ERROR_CODE_404;
        $this->response($error, 200);
    }

    function addTopic_post() {
        $this->_requireAuthToken();

        $category_id = $this->post('category_id') ? $this->post('category_id') : false;
        $title = $this->post('title') ? $this->post('title') : false;
        $content = $this->post('content') ? $this->post('content') : false;
        $status = 1;
        $created_date = now();
        $created_by = $this->_member->id;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('category_id', 'Category', 'required');
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('content', 'Content', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('category_id')) {
                $error['msg'] = strip_tags(form_error('category_id'));
            }
            if (form_error('title')) {
                $error['msg'] = strip_tags(form_error('title'));
            }
            if (form_error('content')) {
                $error['msg'] = strip_tags(form_error('content'));
            }
            $this->response($error, 200);
        }

        //add topic
        $topic_data = array(
            'category_id' => $category_id,
            'title' => $title,
            'content' =>  '<p>'. add_break_link($content) . '</p>',
            'status' => $status,
            'created_date' => $created_date,
            'created_by' => $created_by
        );
        $topic_id = $this->pettalk_model->add_topic($topic_data);
        if ($topic_id) {
            //upload photo and overwrite profile photo
            $media_files = $this->_doMultiUpload($this->config->item('listings_path'));

            //save media to db
            if ($media_files && $topic_id) {
                $data_insert = array();


                foreach ($media_files as $file) {
                    $file_array = array();
                    $media_insert = array();

                    $file_array = $file['upload_data'];

                    $media_insert['topic_id'] = $topic_id;
                    $media_insert['source'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_array['file_name'];
                    $media_insert['created_date'] = now();
                    $media_insert['status'] = 1;
                    $media_insert['user_id'] = $this->_member->id;
                    if (empty($file_array['image_type'])) {
                        //video
                        $media_insert['type'] = 'VIDEO';
                        $media_insert['photo_thumb'] = null;
                    } else {
                        $media_insert['type'] = 'PHOTO';

                        //resize
                        resizeImage($file_array['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                        $file_name_array = explode('.', $file_array['file_name']);
                        $media_insert['photo_thumb'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];
                    }
                    array_push($data_insert, $media_insert);
                }
                //insert
                insert_user_media($data_insert);
            }
            $topic_info = $this->pettalk_model->get_topic($topic_id, true, true, true, true);
            $response['item'] = $topic_info;

            $this->response($response, 200);
        } else {
            $error['msg'] = lang('Item not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error, 200);
        }
    }

    public function list_post() {
        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;

        $items = $this->pettalk_model->get_list_pettalk('all', $start, $limit, $keyword, 1, 'sort', 'ASC');
        $data['totalItem'] = $this->pettalk_model->get_list_pettalk('count', $start, $limit, $keyword);
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);

        if (!empty($items)) {
            foreach ($items as $key => $item) {
//     			$topics = $this->pettalk_model->get_topics_pettalk($item->id,'all',0,API_NUM_RECORD_PER_PAGE,false,1,'created_date','DESC');
//     			if(!empty($topics))
//     			{
//     				foreach($topics as $tpkey => $topic)
//     				{
//     					$topic_detail = $this->pettalk_model->get_topic($topic->id);
//     					if($topic_detail)
//     					{
//     						$topics[$tpkey] = $topic_detail;
//     					}    					
//     				}
//     				$items[$key]->topics = format_output_data($topics);
//     			}
//     			else
//     			{
//     				$items[$key]->topics = array();
//     			}
                $items[$key] = format_output_data($item);
            }
        }
        $data['items'] = $items;
        $this->response($data, 200);
    }

    public function getCategoryDetail_post() {
        $this->_requireAuthToken();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $id = $this->post('id') ? $this->post('id') : false;
        $topic_id = $this->post('topic_id') ? $this->post('topic_id') : false;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('id', 'Category ID', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('id')) {
                $error['msg'] = strip_tags(form_error('id'));
            }
            $this->response($error, 200);
        }
        $category_detail = $this->pettalk_model->get_pettalk($id);
        if (!empty($category_detail)) {
            $category_detail = format_output_data($category_detail);
            if ($topic_id) {
                $topic_detail = $this->pettalk_model->get_topic($topic_id, true, true, true, true);
                if (!empty($topic_detail['created_by'])) {
                    $this->load->model('member_model');
                    $author = $this->member_model->getMemberByMemberID($topic_detail['created_by']);
                    if ($author) {
                        $author = format_output_data($author);
                        $author = array(
                            'id' => $author->id,
                            'first_name' => $author->first_name,
                            'last_name' => $author->last_name,
                            'profile_photo' => $author->profile_photo,
                        );
                        $topic_detail['created_by'] = $author;
                    }
                }
                $topics = array($topic_detail);
                $total_topics = 1;
            } else {
                $topics = $this->pettalk_model->get_topics_pettalk($id, 'all', $start, $limit, false, 1, 'id', 'DESC');
                $total_topics = $this->pettalk_model->get_topics_pettalk($id, 'count', $start, $limit, false, 1, 'id', 'DESC');

                if ($topics) {
                    foreach ($topics as $key => $topic) {
                        $topic_detail = $this->pettalk_model->get_topic($topic->id, true, true, true, true);
                        if (!empty($topic_detail['created_by'])) {
                            $this->load->model('member_model');
                            $author = $this->member_model->getMemberByMemberID($topic_detail['created_by']);
                            if ($author) {
                                $author = format_output_data($author);
                                $author = array(
                                    'id' => $author->id,
                                    'first_name' => $author->first_name,
                                    'last_name' => $author->last_name,
                                    'profile_photo' => $author->profile_photo,
                                );
                                $topic_detail['created_by'] = $author;
                            }

                            //get user topic like
                            $like_status = $this->pettalk_model->get_user_like_topic($this->_member->id, $topic->id);
                            if ($like_status) {
                                $topic_detail['like_type'] = strval($like_status->type);
                            } else {
                                $topic_detail['like_type'] = '';
                            }
                        }
                        $topics[$key] = $topic_detail;
                    }
                }
            }
            $category_detail->topics = $topics;

            $response['item'] = $category_detail;
            $response['totalItem'] = $total_topics;
            $response['totalPage'] = ceil(intval($response['totalItem']) / $limit);
            $response['limit'] = intval($limit);
        } else {
            $response['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
            $response['msg'] = lang('Item not found');
        }
        $this->response($response, 200);
    }

    public function listTopic_post() {
        $this->_requireAuthToken();

        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;
        $category_id = $this->post('category_id') ? $this->post('category_id') : false;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('category_id', 'Category', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error_list = $this->form_validation->error_array();

            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('category_id')) {
                $error['msg'] = strip_tags(form_error('category_id'));
            }
            $this->response($error, 200);
        }

        $items = $this->pettalk_model->get_topics_pettalk($category_id, 'all', $start, $limit, $keyword, 1, 'id', 'DESC');
        $data['totalItem'] = $this->pettalk_model->get_topics_pettalk($category_id, 'count', $start, $limit, $keyword, 1, 'id', 'DESC');
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);

        if (!empty($items)) {
            foreach ($items as $key => $item) {
                $comments = $this->pettalk_model->get_comments_topics($item->id, 'all', 0, false, false, 'created_date', 'DESC');
                if (!empty($comments)) {
                    $items[$key]->comments = format_output_data($comments);
                } else {
                    $items[$key]->comments = "";
                }
                $topic_detail = $this->pettalk_model->get_topic($item->id, true, true, true, true);
                if (!empty($topic_detail['created_by'])) {
                    $this->load->model('member_model');
                    $author = $this->member_model->getMemberByMemberID($topic_detail['created_by']);
                    if ($author) {
                        $author = format_output_data($author);
                        $author = array(
                            'id' => $author->id,
                            'first_name' => $author->first_name,
                            'last_name' => $author->last_name,
                            'profile_photo' => $author->profile_photo,
                        );
                        $topic_detail['created_by'] = $author;
                    }
                }
                //get user topic like
                $like_status = $this->pettalk_model->get_user_like_topic($this->_member->id, $item->id);
                if ($like_status) {
                    $topic_detail['like_type'] = strval($like_status->type);
                } else {
                    $topic_detail['like_type'] = '';
                }

                $items[$key] = $topic_detail;
            }
        }
        $data['items'] = $items;
        $this->response($data, 200);
    }

    public function listComment_post() {
        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;
        $topic_id = $this->post('topic_id') ? $this->post('topic_id') : false;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('topic_id', 'Topic', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error_list = $this->form_validation->error_array();

            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('topic_id')) {
                $error['msg'] = strip_tags(form_error('topic_id'));
            }
            $this->response($error, 200);
        }

        $items = $this->pettalk_model->get_comments_topics($topic_id, 'all', $start, $limit, 1, 'id', 'DESC');
        $data['totalItem'] = $this->pettalk_model->get_comments_topics($topic_id, 'count', $start, $limit, 1);
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);
        if ($items) {
            foreach ($items as $key => $item) {
                $item->media = array();
                $medias = $this->pettalk_model->get_media_comment('all', 0, API_NUM_RECORD_PER_PAGE, 'id', 'DESC', $item->id, 1);
                if ($medias) {
                    foreach ($medias as $mkey => $media) {
                        $item->media[$mkey] = format_output_data($media);
                    }
                }
                $items[$key] = format_output_data($item);
            }
        }

        $data['items'] = $items;
        $this->response($data, 200);
    }
    
    function updateTopic_post(){
        $this->_requireAuthToken();
        
        $topic_id = $this->post('id') ? $this->post('id') : false;
        $title = $this->post('title') ? $this->post('title') : false;
        $content = $this->post('content') ? $this->post('content') : false;
        $category_id = $this->post('category_id') ? $this->post('category_id') : false;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('id', 'ID', 'required');
        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('content', 'Content', 'required');
        $this->form_validation->set_rules('category_id', 'Category ID', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('id')) {
                $error['msg'] = strip_tags(form_error('id'));
            }
            if (form_error('category_id')) {
                $error['msg'] = strip_tags(form_error('category_id'));
            }
            if (form_error('title')) {
                $error['msg'] = strip_tags(form_error('title'));
            }
            if (form_error('content')) {
                $error['msg'] = strip_tags(form_error('content'));
            }
            $this->response($error, 200);
        }

        //edit topic
        $topic_data = array(
            'title' => $title,
            'content' =>  '<p>'. add_break_link($content) . '</p>',
        );
        
        $item = (object) $this->pettalk_model->get_topic($topic_id);
        if ($item) {
            $this->pettalk_model->update_topic($topic_data, $topic_id);

            $comments = $this->pettalk_model->get_comments_topics($item->id, 'all', 0, false, false, 'created_date', 'DESC');
            if (!empty($comments)) {
                $item->comments = format_output_data($comments);
            } else {
                $item->comments = "";
            }
            $topic_detail = $this->pettalk_model->get_topic($item->id, true, true, true, true);
            if (!empty($topic_detail['created_by'])) {
                $this->load->model('member_model');
                $author = $this->member_model->getMemberByMemberID($topic_detail['created_by']);
                if ($author) {
                    $author = format_output_data($author);
                    $author = array(
                        'id' => $author->id,
                        'first_name' => $author->first_name,
                        'last_name' => $author->last_name,
                        'profile_photo' => $author->profile_photo,
                    );
                    $topic_detail['created_by'] = $author;
                }
            }
            //get user topic like
            $like_status = $this->pettalk_model->get_user_like_topic($this->_member->id, $item->id);
            if ($like_status) {
                $topic_detail['like_type'] = strval($like_status->type);
            } else {
                $topic_detail['like_type'] = '';
            }

            $item = $topic_detail;
            $response['msg'] = lang('edit topic success');
            $response['items'] = $item;
            $this->response($response, 200);
        } else{
            $error['msg'] = lang('Topic not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error,200);            
        }
    }
    
    function updateComment_post(){
        $this->_requireAuthToken();

        $topic_id = $this->post('topic_id') ? $this->post('topic_id') : false;
        $content = $this->post('content') ? $this->post('content') : null;
        $id = $this->post('id') ? $this->post('id') : false;
        
        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('content', 'Content', 'required');
        $this->form_validation->set_rules('topic_id', 'Topic', 'required');
        $this->form_validation->set_rules('id', 'Comment ID', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('topic_id')) {
                $error['msg'] = strip_tags(form_error('topic_id'));
            }
            if (form_error('id')) {
                $error['msg'] = strip_tags(form_error('id'));
            }
            if (form_error('content')) {
                $error['msg'] = strip_tags(form_error('content'));
            }
            $this->response($error, 200);
        }

        //edit comment
        $data = array(
            'content' => $content,
        );
        
       if ($this->pettalk_model->check_exist_comment($topic_id, $id)) {
            $this->pettalk_model->update_comment($data, $id);
            
            $items = $this->pettalk_model->get_comments_topics($topic_id, 'all', 0, API_NUM_RECORD_PER_PAGE, 1, 'id', 'DESC');
            if (!empty($items)) {
                $response['msg'] = lang('edit comment topic success');
                foreach ($items as $key => $item) {
                    $item->media = array();
                    $medias = $this->pettalk_model->get_media_comment('all', 0, API_NUM_RECORD_PER_PAGE, 'id', 'DESC', $item->id, 1);
                    if ($medias) {
                        foreach ($medias as $mkey => $media) {
                            $item->media[$mkey] = format_output_data($media);
                        }
                    }
                    $response['item'][$key] = format_output_data($item);
                }
            }
            $this->response($response, 200);
        }
        else{
            $error['msg'] = lang('Comment topic not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error,200);            
        }
    }
    
    function deleteTopic_post() {
        $this->_requireAuthToken();
        $topic_id = $this->post('id') ? $this->post('id') : 0;
        $category_id = $this->post('category_id') ? $this->post('id') : 0;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('id', 'Topic ID', 'required|integer');
        $this->form_validation->set_rules('category_id', 'Category ID', 'required|integer');
        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('id')) {
                $error['msg'] = strip_tags(form_error('id'));
            }
            if (form_error('category_id')) {
                $error['msg'] = strip_tags(form_error('category_id'));
            }
            $this->response($error, 200);
        }
        if($this->pettalk_model->get_topic($topic_id)){
            //delete topic
            $this->pettalk_model->delete_topic($topic_id);
            //log    	
            log_message("info", "===========================");
            log_message("info", "DELETE TOPIC ID $topic_id");
            log_message("info", "===========================");

            $this->response(array('msg' => lang('Delete topic success')), 200);            
        }
        else{
            $error['msg'] = lang('Topic not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error,200);            
        }
        
    }

    function deleteComment_post() {
        
        $this->_requireAuthToken();
        
        $comment_id = $this->post('id') ? $this->post('id') : 0;
        $topic_id = $this->post('topic_id') ? $this->post('topic_id') : 0;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('id', 'Comment ID', 'required|integer');
        $this->form_validation->set_rules('topic_id', 'Topic ID', 'required|integer');
        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('id')) {
                $error['msg'] = strip_tags(form_error('id'));
            }
            if (form_error('topic_id')) {
                $error['msg'] = strip_tags(form_error('topic_id'));
            }
            $this->response($error, 200);
        }
        if ($this->pettalk_model->check_exist_comment($topic_id, $comment_id)) {
            //delete pet
            $this->pettalk_model->delete_comment($comment_id);

            //log    	
            log_message("info", "===========================");
            log_message("info", "DELETE COMMENT ID $comment_id");
            log_message("info", "===========================");

            $this->response(array('msg' => lang('delete comment topic success')), 200);
        }
        else{
            $error['msg'] = lang('Comment topic not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error,200);            
        }
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

    private function _doUpload($folder_path) {
        if (!empty($_FILES)) {
            $this->load->helper('string');
            foreach ($_FILES as $key => $file) {
                if ((!empty($file) && $file['error'] == 0) && $key == 'file') {
                    $file_name = basename($file['name']);
                    $ext = substr($file_name, strrpos($file_name, '.') + 1);
                    $custom_filename = strtolower(random_string('alnum', 20) . "_" . $key . "." . $ext);

                    $config['upload_path'] = $this->config->item('api_upload_path') . $folder_path;
                    $config['allowed_types'] = 'jpg|png';
                    $config['file_name'] = $custom_filename;

                    $this->load->library('upload', $config);

                    if (!$this->upload->do_upload($key)) {
                        $upload_errors = array('error' => $this->upload->display_errors());

                        $error['code'] = self::ERROR_CODE_UPLOAD_IMAGE_FAIL;
                        $error['msg'] = lang('Error: A problem occurred during file upload!');
                        $this->response($error, 200);
                        return false;
                    } else {
                        $data = array('upload_data' => $this->upload->data());
                        return $data['upload_data'];
                    }
                } else {
                    $error['code'] = self::ERROR_CODE_FILE_ERROR;
                    $error['msg'] = lang('File error or not allow');
                    $this->response($error, 200);
                    return false;
                }
            }
        }
        return false;
    }

    public function addComment_post() {
        $this->_requireAuthToken();

        $data['topic_id'] = $this->post('topic_id') ? $this->post('topic_id') : false;
        $data['content'] = $this->post('content') ? $this->post('content') : null;
        $data['user_id'] = $this->_member->id;
        $data['status'] = 1; //default active = 1
        $data['created_date'] = now();

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('content', 'Content', 'required');
        $this->form_validation->set_rules('topic_id', 'Topic', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('topic_id')) {
                $error['msg'] = strip_tags(form_error('topic_id'));
            }
            if (form_error('content')) {
                $error['msg'] = strip_tags(form_error('content'));
            }
            $this->response($error, 200);
        }

        //ADD comment
        $comment_id = $this->pettalk_model->add_comment($data);
        if ($comment_id) {
            //upload photo and overwrite profile photo
            $media_files = $this->_doMultiUpload($this->config->item('listings_path'));

            //save media to db
            if ($media_files && $comment_id) {
                $data_insert = array();


                foreach ($media_files as $file) {
                    $file_array = array();
                    $media_insert = array();

                    $file_array = $file['upload_data'];

                    $media_insert['topic_comment_id'] = $comment_id;
                    $media_insert['source'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_array['file_name'];
                    $media_insert['created_date'] = now();
                    $media_insert['status'] = 1;
                    $media_insert['user_id'] = $this->_member->id;
//                    $media_insert['topic_id'] = $data['topic_id'];
                    if (empty($file_array['image_type'])) {
                        //video
                        $media_insert['type'] = 'VIDEO';
                        $media_insert['photo_thumb'] = null;
                    } else {
                        $media_insert['type'] = 'PHOTO';

                        //resize
                        resizeImage($file_array['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                        $file_name_array = explode('.', $file_array['file_name']);
                        $media_insert['photo_thumb'] = $this->config->item('api_upload_path') . $this->config->item('listings_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];
                    }
                    array_push($data_insert, $media_insert);
                }
                //insert
                insert_user_media($data_insert);
            }

            $response['msg'] = lang('Add new successful');
            $response['item'] = array();

            //send push to user owner of topic
            $this->load->model('notification_model');
            $this->load->helper('notification');

            $topic = (object) $this->pettalk_model->get_topic($data['topic_id']);
            $actor_user_id = $topic->created_by;
            $name_user_action = $this->_member->first_name . ' ' . $this->_member->last_name;
            $message = $name_user_action . ' ' . lang('comment your topic');
            $action_type = get_action_type(self::PUSH_TYPE_ADD_COMMENT_TOPIC);
            $source_id = $topic->id;
            $data_push = array(
                'action_type' => self::PUSH_TYPE_ADD_COMMENT_TOPIC,
                'sender_id' => $this->_member->id,
                'sender_name' => $name_user_action,
                'type' => 'topic',
                'topic_id' => $source_id,
                'category_id' => $topic->category_id,
                'bages_unread_notification' => count_unread_notification($actor_user_id) + 1 ,
            );
            $this->notification_model->send_push_notification($actor_user_id, $message, $data_push, $action_type->id, $source_id);
            //end push

            $items = $this->pettalk_model->get_comments_topics($data['topic_id'], 'all', 0, API_NUM_RECORD_PER_PAGE, 1, 'id', 'DESC');
            if (!empty($items)) {
                foreach ($items as $key => $item) {
                    $item->media = array();
                    $medias = $this->pettalk_model->get_media_comment('all', 0, API_NUM_RECORD_PER_PAGE, 'id', 'DESC', $item->id, 1);
                    if ($medias) {
                        foreach ($medias as $mkey => $media) {
                            $item->media[$mkey] = format_output_data($media);
                        }
                    }
                    $response['item'][$key] = format_output_data($item);
                }
            }
            $this->response($response, 200);
        } else {
            $error['msg'] = lang('Item not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error, 200);
        }
    }

    public function addLike_post() {
        $this->_requireAuthToken();

        $data['topic_id'] = $this->post('topic_id') ? $this->post('topic_id') : false;
        $data['user_id'] = $this->_member->id;
        $data['type'] = $this->post('type') ? $this->post('type') : 0;
        $data['created_date'] = now();

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('type', 'Type', 'required');
        $this->form_validation->set_rules('topic_id', 'Topic', 'required');

        if ($this->form_validation->run() == FALSE) {

            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('topic_id')) {
                $error['msg'] = strip_tags(form_error('topic_id'));
            }
            if (form_error('type')) {
                $error['msg'] = strip_tags(form_error('type'));
            }
            $this->response($error, 200);
        }

        //check like
        $check_like = $this->member_model->check_user_like_topic($data['user_id'], $data['topic_id']);
        if ($check_like) {
            $row_id = $check_like->id;
            $this->member_model->delete_like($row_id);
            $response['msg'] = lang('Remove like successful');
            $response['item'] = $this->pettalk_model->get_topic($data['topic_id'], false, true, true);
            $response['like_type'] = strval(1);
            $this->response($response, 200);
        } else {
            //ADD like
            if ($this->member_model->add_like($data)) {
                $response['msg'] = lang('Add new successful');
                $response['item'] = (object) $this->pettalk_model->get_topic($data['topic_id'], false, true, true);

                //send push to user who like topic
                $this->load->model('notification_model');
                $this->load->helper('notification');
                $actor_user_id = $response['item']->created_by;
                $name_user_action = $this->_member->first_name . ' ' . $this->_member->last_name;
                $message = $name_user_action . ' ' . lang('like your topic');
                $action_type = get_action_type(self::PUSH_TYPE_LIKE_TOPIC);
                $source_id = $response['item']->id;
                $data_push = array(
                    'action_type' => self::PUSH_TYPE_LIKE_TOPIC,
                    'sender_id' => $this->_member->id,
                    'sender_name' => $name_user_action,
                    'type' => 'topic',
                    'topic_id' => $source_id,
                    'category_id' => $response['item']->category_id,
                    'bages_unread_notification' => count_unread_notification($actor_user_id) + 1,
                );
                $this->notification_model->send_push_notification($actor_user_id, $message, $data_push, $action_type->id, $source_id);
                //end push

                $response['like_type'] = strval(0);
                $this->response($response, 200);
            } else {
                $error['msg'] = lang('Item not found');
                $error['code'] = self::ERROR_CODE_404;
                $this->response($error, 200);
            }
        }
    }

    public function searchTopic_post() {
        $this->_requireAuthToken();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('keyword', 'keyword', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('keyword')) {
                $error['msg'] = strip_tags(form_error('keyword'));
            }
            $this->response($error, 200);
        }

        $items = $this->pettalk_model->get_topics_pettalk(false, 'all', $start, $limit, $keyword, 1, 'id', 'DESC');
        $data['totalItem'] = $this->pettalk_model->get_topics_pettalk(false, 'count', $start, $limit, $keyword, 1, 'id', 'DESC');
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);

        if (!empty($items)) {
            foreach ($items as $key => $item) {
                $topic_detail = $this->pettalk_model->get_topic($item->id);
                if (!empty($topic_detail['created_by'])) {
                    $this->load->model('member_model');
                    $author = $this->member_model->getMemberByMemberID($topic_detail['created_by']);
                    if ($author) {
                        $author = format_output_data($author);
                        $author = array(
                            'id' => $author->id,
                            'first_name' => $author->first_name,
                            'last_name' => $author->last_name,
                            'profile_photo' => $author->profile_photo,
                        );
                        $topic_detail['created_by'] = $author;
                    }
                }
                //get user topic like
                $like_status = $this->pettalk_model->get_user_like_topic($this->_member->id, $item->id);
                if ($like_status) {
                    $topic_detail['like_type'] = strval($like_status->type);
                } else {
                    $topic_detail['like_type'] = '';
                }

                $items[$key] = format_output_data($topic_detail);
            }
        }
        $data['items'] = $items;
        $this->response($data, 200);
    }

}
