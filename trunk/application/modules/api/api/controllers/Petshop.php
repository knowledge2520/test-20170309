<?php

defined('BASEPATH') OR exit('No direct script access allowed');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/modules/api/api/libraries/REST_Controller.php';

/**
 * 
 * @author: VuDao <vu.dao@apps-cyclone.com>
 * @created_date: May 5, 2015
 */
class Petshop extends REST_Controller {

    function __construct() {
        // Construct our parent class
        parent::__construct();

        //load model
        $this->load->model('petshop_model');
        //load lang
        $this->lang->load('api');
        //load helper
        $this->load->helper(array('form', 'url'));
    }

    function addCategory_post() {
        $this->_requireAuthToken();

        $data['name'] = $this->post('name') ? $this->post('name') : null;

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
        $photo = $this->_doUpload($this->config->item('product_path'));
        if ($photo) {
            //resize
            $this->load->helper('image');
            resizeImage($photo['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);

            $data['photo'] = $this->config->item('api_upload_path') . $this->config->item('product_path') . $photo['file_name'];
            $file_name_array = explode('.', $photo['file_name']);
            $data['photo_thumb'] = $this->config->item('api_upload_path') . $this->config->item('product_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];
        }
        //add category
        $id = $this->petshop_model->addCategory($data);
        if ($id) {
            $response['msg'] = lang('Add new successful');
            $this->response($response, 200);
        }
        $error['code'] = self::ERROR_OBJECT_NOT_FOUND;
        $error['msg'] = lang('Item not found');
        $this->response($error, 200);
    }

    public function categories_post() {
        $this->_requireAuthToken();

        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;
        
        $latitude = $this->post('latitude') ? $this->post('latitude') : false;
        $longitude = $this->post('longitude') ? $this->post('longitude') : false;
        
        $user_options = $this->member_model->get_user_options($this->_member->id);
        
        if ($user_options) {
            $option_lock = $user_options->location_lock;
            $option_location_latitude = !empty($user_options->location_city) && explode(',', $user_options->location_city)[0] ? trim(explode(',', $user_options->location_city)[0]) : false;
            $option_location_longitude = !empty($user_options->location_city) && explode(',', $user_options->location_city)[1] ? trim(explode(',', $user_options->location_city)[1]) : false;
        }

        $user_location = array(
            'latitude' => $user_options && $option_lock == 'on' && $option_location_latitude ? $option_location_latitude : $latitude,
            'longitude' => $user_options && $option_lock == 'on' && $option_location_longitude ? $option_location_longitude : $longitude,
        );
        
        $items = $this->petshop_model->get_list_category('all', $start, $limit, $keyword, 1, 'id', 'desc', 'parent');
        $data['totalItem'] = $this->petshop_model->get_list_category('count', $start, $limit, $keyword, 1, 'id', 'desc', 'parent');
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);
        
        if (!empty($items)) {
            foreach ($items as $key => $item) {

                $categories = $this->petshop_model->get_list_category('all', $start, $limit, $keyword, 1, 'id', 'desc', 'child', $item->id);

                if (!empty($categories)) {
                    foreach ($categories as $ckey => $category) {
//                        $category_detail = $this->petshop_model->get_category_detail($category->id);
//                        $categories[$ckey] = format_output_data($category_detail);
//                        
//                        $cproducts = $this->petshop_model->get_products_by_category($category->id, 'all', $start, $limit, $keyword, 1, 'sort', 'ASC');
//                        if (!empty($cproducts)) {
//                            foreach ($cproducts as $pkey => $product) {
//                                $cproduct_detail = $this->petshop_model->get_product_detail($product->id, true, true, true, true, true, true, $this->_member->id);
//                                $cproducts[$pkey] = format_output_data($cproduct_detail);
//                            }
//                            $categories[$ckey]->products = format_output_data($cproducts);
//                        } else {
//                            $categories[$ckey]->products = "";
//                        }
                        $categories[$ckey] = format_output_data($category);
                    }
                    $items[$key]->subCategories = format_output_data($categories);
                } else {
                    $items[$key]->subCategories = array();
                }
                $items[$key] = format_output_data($item);
            }
        }
        $data['items'] = $items;
        $this->response($data, 200);
    }

    public function subCategories_post() {
        $this->_requireAuthToken();

        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;
        
        $category_id = $this->post('category_id') ? $this->post('category_id') : false;
        $latitude = $this->post('latitude') ? $this->post('latitude') : false;
        $longitude = $this->post('longitude') ? $this->post('longitude') : false;
        
        $user_options = $this->member_model->get_user_options($this->_member->id);
        
        if ($user_options) {
            $option_lock = $user_options->location_lock;
            $option_location_latitude = !empty($user_options->location_city) && explode(',', $user_options->location_city)[0] ? trim(explode(',', $user_options->location_city)[0]) : false;
            $option_location_longitude = !empty($user_options->location_city) && explode(',', $user_options->location_city)[1] ? trim(explode(',', $user_options->location_city)[1]) : false;
        }

        $user_location = array(
            'latitude' => $user_options && $option_lock == 'on' && $option_location_latitude ? $option_location_latitude : $latitude,
            'longitude' => $user_options && $option_lock == 'on' && $option_location_longitude ? $option_location_longitude : $longitude,
        );
        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('category_id', 'Category ID', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error_list = $this->form_validation->error_array();

            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('category_id')) {
                $error['msg'] = strip_tags(form_error('category_id'));
            }
            $this->response($error, 200);
        }
        
        $items = $this->petshop_model->get_list_category('all', $start, $limit, $keyword, 1, 'id', 'desc', 'child', $category_id);
        $data['totalItem'] = $this->petshop_model->get_list_category('count', $start, $limit, $keyword, 1, 'id', 'desc', 'child', $category_id);
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);

        if (!empty($items)) {
            foreach ($items as $key => $item) {
                $products = $this->petshop_model->get_products_by_category($item->id, $user_location, 'all', $start, $limit, $keyword, 1, 'sort', 'asc');
                if (!empty($products)) {
                    foreach ($products as $pkey => $product) {
                        $product_detail = $this->petshop_model->get_product_detail($product->id, true, true, true, true, true, true, $this->_member->id);
                        $products[$pkey] = format_output_data($product_detail);
                    }
                    $items[$key]->products = format_output_data($products);
                } else {
                    $items[$key]->products = array();
                }

                $items[$key] = format_output_data($item);
            }
            $data['items'] = $items;
            $this->response($data, 200);
        }
        $error['msg'] = 'No items found';
        $error['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
        $this->response($error, 200);
    }
    
    public function products_post() {
        $this->_requireAuthToken();
        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;
        $category_id = $this->post('category_id') ? $this->post('category_id') : false;
        $latitude = $this->post('latitude') ? $this->post('latitude') : false;
        $longitude = $this->post('longitude') ? $this->post('longitude') : false;
        
        $user_options = $this->member_model->get_user_options($this->_member->id);
        
        if ($user_options) {
            $option_lock = $user_options->location_lock;
            $option_location_latitude = !empty($user_options->location_city) && explode(',', $user_options->location_city)[0] ? trim(explode(',', $user_options->location_city)[0]) : false;
            $option_location_longitude = !empty($user_options->location_city) && explode(',', $user_options->location_city)[1] ? trim(explode(',', $user_options->location_city)[1]) : false;
        }

        $user_location = array(
            'latitude' => $user_options && $option_lock == 'on' && $option_location_latitude ? $option_location_latitude : $latitude,
            'longitude' => $user_options && $option_lock == 'on' && $option_location_longitude ? $option_location_longitude : $longitude,
        );
        
        $items = $this->petshop_model->get_products_by_category($category_id, $user_location, 'all', $start, $limit, $keyword, 1, 'id', 'desc');
        $data['totalItem'] = $this->petshop_model->get_products_by_category($category_id, $user_location, 'count', $start, $limit, $keyword, 1, 'id', 'desc');
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);

        if (!empty($items)) {
            foreach ($items as $key => $item) {
                $product_detail = $this->petshop_model->get_product_detail($item->id, true, true, true, true, true, true, $this->_member->id);
                $items[$key] = format_output_data($product_detail);
            }
        }
        $data['items'] = $items;
        $this->response($data, 200);
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

    public function addToCart_post() {
        $this->_requireAuthToken();

        $data['product_id'] = $this->post('product_id') ? $this->post('product_id') : false;
        $data['quantity'] = $this->post('quantity') ? $this->post('quantity') : false;
        $data['size_id'] = $this->post('size_id') ? $this->post('size_id') : false;
        $data['color_id'] = $this->post('color_id') ? $this->post('color_id') : false;
        $user_id = $this->_member->id;
        $options = array();

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('product_id', 'Product ID', 'required|integer');
        $this->form_validation->set_rules('size_id', 'Size ID', 'required|integer');
        $this->form_validation->set_rules('color_id', 'Color ID', 'required|integer');
        $this->form_validation->set_rules('quantity', 'Quantity', 'required|integer');

        if ($this->form_validation->run() == FALSE) {
            $error_list = $this->form_validation->error_array();

            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('product_id')) {
                $error['msg'] = strip_tags(form_error('product_id'));
            }
            if (form_error('quantity')) {
                $error['msg'] = strip_tags(form_error('quantity'));
            }
            if (form_error('size_id')) {
                $error['msg'] = strip_tags(form_error('size_id'));
            }
            if (form_error('color_id')) {
                $error['msg'] = strip_tags(form_error('color_id'));
            }
            $this->response($error, 200);
        }

        $product = $this->petshop_model->get_product_detail($data['product_id']);
        if ($product) {
            if ($product->approved_sale_price == 1) {
                //approve price_on_sale
                $price = $product->price_on_sale;
            } else {
                $price = $product->price;
            }
            //add option size
            if ($data['size_id']) {
                $size = $this->db->select('size')->from('pet_shop_product_size')->where('id', $data['size_id'])->get()->first_row();
                if ($size) {
                    $options['Size'] = $size->size;
                }
            }
            //add option color
            if ($data['color_id']) {
                $color = $this->db->select('color')->from('pet_shop_product_color')->where('id', $data['color_id'])->get()->first_row();
                if ($color) {
                    $options['Color'] = $color->color;
                }
            }
            
            $product_option = $this->petshop_model->get_product_with_option($data['product_id'], $data['color_id'], $data['size_id']);
            if(!$product_option){
                //can not find product with option
                $error['msg'] = 'Item not found';
                $error['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
                $this->response($error, 200);
            }
            $product_quantity = $product_option->quantity - $product_option->sell_quantity;

            if($product_quantity - $data['quantity'] < 0){
                //can not find product with option
                $error['msg'] = 'Out of stock';
                $error['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
                $this->response($error, 200);
            }
                       
//            $item = array(
//                'id' => (int) $product->id,
//                'qty' => (int) $data['quantity'],
//                'price' => (float) $price,
//                'name' => (string) $product->name,
//                'user_id' => (int) $user_id,
//                'options' => $options
//            );
////            var_dump($item);exit;
////            
//            //load library cart
//            $this->load->library('cart');
//            //add to cart
//            
//            $this->cart->insert($item);
//            
//            $myCart = new stdClass;
//            $myCart->total_amount = $this->cart->total();
//            $myCart->total_items = $this->cart->total_items();
//            $myCart->contents = array();
//
//            $contents = $this->cart->contents();
//            if ($contents) {
//                foreach ($contents as $rowID => $item) {
//                    array_push($myCart->contents, $item);
//                }
//            }
//            $response['items'] = $myCart;
//            $this->response($response, 200);
            $this->response(array('msg' => lang('add to cart successful')), 200);
        } else {
            //can not find product
            $error['msg'] = lang('Item not found');
            $error['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
            $this->response($error, 200);
        }
    }

    public function deleteItemInCart_post() {
        $this->_requireAuthToken();
        $data['row_id'] = $this->post('row_id') ? $this->post('row_id') : false;

        //load library cart
        $this->load->library('cart');

        // Get the total contents of items in cart
        $cartItems = $this->cart->contents();

        foreach ($cartItems as $key => $item) {
            if ($item['rowid'] == $data['row_id']) {
                $this->cart->remove($item['rowid']);

                $myCart = new stdClass;
                $myCart->total_amount = $this->cart->total();
                $myCart->total_items = $this->cart->total_items();
                $myCart->contents = array();
                if ($this->cart->contents()) {
                    $contents = $this->cart->contents();
                    foreach ($contents as $rowID => $item) {
                        array_push($myCart->contents, $item);
                    }
                }

                $response['items'] = $myCart;
                $response['msg'] = lang('delete successful');

                $this->response($response, 200);
            }
        }

        $error['msg'] = lang('Item not found');
        $error['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
        $this->response($error, 200);
    }

    public function updateItemInCart_post() {
        $this->_requireAuthToken();
        $data['row_id'] = $this->post('row_id') ? $this->post('row_id') : false;
        $data['quantity'] = $this->post('quantity') ? $this->post('quantity') : false;
        $data['size_id'] = $this->post('size_id') ? $this->post('size_id') : false;
        $data['color_id'] = $this->post('color_id') ? $this->post('color_id') : false;
        $user_id = $this->_member->id;
        $options = array();

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('row_id', 'Row', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error_list = $this->form_validation->error_array();

            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('row_id')) {
                $error['msg'] = strip_tags(form_error('row_id'));
            }
            $this->response($error, 200);
        }

        //load library cart
        $this->load->library('cart');

        // Get the total contents of items in cart
        $cartItems = $this->cart->contents();

        foreach ($cartItems as $key => $item) {
            if ($item['rowid'] == $data['row_id']) {
                //add option size
                if ($data['size_id']) {
                    $size = $this->db->select('size')->from('pet_shop_product_size')->where('id', $data['size_id'])->get()->first_row();
                    if ($size) {
                        $options['size'] = $size->size;
                    }
                }
                //add option color
                if ($data['color_id']) {
                    $color = $this->db->select('color')->from('pet_shop_product_color')->where('id', $data['color_id'])->get()->first_row();
                    if ($color) {
                        $options['color'] = $color->color;
                    }
                }

                $update_data = array(
                    'rowid' => $item['rowid'],
                    'qty' => $data['quantity'],
                    'options' => $options
                );

                $this->cart->update($update_data);

                $myCart = new stdClass;
                $myCart->total_amount = $this->cart->total();
                $myCart->total_items = $this->cart->total_items();
                $myCart->contents = $this->cart->contents();

                $response['items'] = $myCart;
                $response['msg'] = lang('Update successful');

                $this->response($response, 200);
            }
        }

        $error['msg'] = lang('Item not found');
        $error['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
        $this->response($error, 200);
    }

    public function destroyCart_post() {
        $this->_requireAuthToken();
        //load library cart
        $this->load->library('cart');

        $this->cart->destroy();
        $response['msg'] = lang('delete successful');
        $this->response($response, 200);
    }

    public function getCart_post() {
        $this->_requireAuthToken();

        //load library cart
        $this->load->library('cart');

        $myCart = $this->cart->contents();
        if (!empty($myCart)) {
            $items = array();
            foreach ($myCart as $rowID => $item) {
                $items[] = $item;
            }
            $response['msg'] = 'Success';
            $response['items']['contents'] = $items;

            $response['items']['total_amount'] = $this->cart->total();
            $response['items']['total_items'] = $this->cart->total_items();

            $this->response($response, 200);
        } else {
            $error['msg'] = lang("Cart empty");
            $error['items'] = array();
            $this->response($error, 200);
        }
    }

    /*
      public function addTransaction_post()
      {
      $this->_requireAuthToken();

      $data['payment_status']	= $this->post('payment_status') ? $this->post('payment_status') : false;
      $data['payment_code']	= $this->post('payment_code') ? $this->post('payment_code') : false;
      $user_id				= $this->_member->id;
      $options				= array();

      $this->load->library('form_validation');
      //Set the form validation rules
      $_POST = $this->post();//set this for validate
      $this->form_validation->set_rules('payment_status', 'Payment status', 'required');
      $this->form_validation->set_rules('payment_code', 'Payment code', 'required');

      if ($this->form_validation->run() == FALSE) {
      $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
      if(form_error('payment_status'))
      {
      $error['msg'] = strip_tags(form_error('payment_status'));
      }
      if(form_error('payment_code'))
      {
      $error['msg'] = strip_tags(form_error('payment_code'));
      }
      $this->response($error,200);
      }
      //load library cart
      $this->load->library('cart');

      $items = $this->cart->contents();
      if(!empty($items))
      {
      foreach($items as $item)
      {

      }
      }
      }
     * */

    public function addUserLikeProduct_post() {
        $this->_requireAuthToken();

        $data['product_id'] = $this->post('product_id') ? $this->post('product_id') : false;
        $data['user_id'] = $this->_member->id;
        $data['type'] = 0; //default is like
        $data['created_date'] = now();

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('product_id', 'Product', 'required');

        if ($this->form_validation->run() == FALSE) {
            if (form_error('product_id')) {
                $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
                $error['msg'] = strip_tags(form_error('product_id'));
                $this->response($error, 200);
            }
        }

        //check like
        $check_like = $this->member_model->check_user_like_product($data['user_id'], $data['product_id']);
        if ($check_like) {
            $row_id = $check_like->id;
            $this->member_model->delete_like($row_id);

            $item = $this->petshop_model->get_product_detail($data['product_id'], false, true);

            $response['msg'] = lang('Remove like successful');
            $response['item'] = format_output_data($item);
            $response['like_type'] = strval(1);
            $this->response($response, 200);
        } else {
            //ADD like
            if ($this->member_model->add_like($data)) {
                $item = $this->petshop_model->get_product_detail($data['product_id'], false, true);

                $response['msg'] = lang('Add new successful');
                $response['item'] = format_output_data($item);
                $response['like_type'] = strval(0);
                $this->response($response, 200);
            } else {
                $error['msg'] = lang('Item not found');
                $error['code'] = self::ERROR_CODE_404;
                $this->response($error, 200);
            }
        }
    }

    public function userSaveTransaction_post() {
        $this->_requireAuthToken();

        $this->load->helper('string');

        $data['payment_status'] = $this->post('payment_status') ? $this->post('payment_status') : false;
        $data['payment_transaction_id'] = $this->post('payment_transaction_id') ? $this->post('payment_transaction_id') : false;
        $data['order_code'] = random_string();
        $data['user_id'] = $this->_member->id;
        $products = $this->post('product_items') ? $this->post('product_items') : false;

        $this->load->library('form_validation');
        //Set the form validation rules
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('payment_status', 'Payment status', 'required');
        $this->form_validation->set_rules('product_items[]', 'Product items', 'required');
        $this->form_validation->set_rules('payment_transaction_id', 'Payment Transaction ID', 'required|is_unique[user_orders.payment_transaction_id]');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('payment_status')) {
                $error['msg'] = strip_tags(form_error('payment_status'));
            }
            if (form_error('payment_transaction_id')) {
                $error['msg'] = strip_tags(form_error('payment_transaction_id'));
            }
            if (form_error('product_items')) {
                $error['msg'] = strip_tags(form_error('product_items'));
            }
            $this->response($error, 200);
        }
        //add order
        $order_id = $this->petshop_model->add_order($data);

        if (is_array($products) && count($products) > 0) {
            $product_data = array();
            foreach ($products as $item) {
                $insert_item['product_id'] = isset($item['product_id']) ? $item['product_id'] : false;
//     			 $insert_item['product_name'] 	= isset($item['product_name']) ? $item['product_name'] : false;
                $insert_item['qty'] = isset($item['qty']) ? $item['qty'] : 0;
                $insert_item['price'] = isset($item['price']) ? $item['price'] : 0;
                $insert_item['subtotal'] = isset($item['subtotal']) ? $item['subtotal'] : 0;
                $insert_item['options'] = isset($item['options']) && !empty($item['options']) ? serialize($item['options']) : null;
                $insert_item['created_date'] = now();
                $insert_item['order_id'] = $order_id;

                array_push($product_data, $insert_item);
            }
            $this->db->insert_batch('user_transaction', $product_data);
            $this->response(array('msg' => 'checkout successful'), 200);
        } else {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $error['msg'] = lang('Item not found');
            $this->response($error, 200);
        }
    }

    public function searchProducts_post() {
        $this->_requireAuthToken();

        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('keyword', 'keyword', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('keyword')) {
                $error['msg'] = strip_tags(form_error('keyword'));
            }
            $this->response($error, 200);
        }

        $items = $this->petshop_model->get_products(false, 'all', $start, $limit, $keyword, 1, 'id', 'DESC');
        $data['totalItem'] = $this->petshop_model->get_products(false, 'count', $start, $limit, $keyword, 1);
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);

        if (!empty($items)) {
            foreach ($items as $key => $item) {
                //$product_detail = $this->petshop_model->get_product_detail($item->id,true,true,true,true,true,true);
                //get user product like
                $like_status = $this->petshop_model->get_product_like_user($this->_member->id, $item->id);
                if ($like_status) {
                    $item->like_type = strval($like_status->type);
                } else {
                    $item->like_type = '';
                }

                $items[$key] = format_output_data($item);
            }
        }
        $data['items'] = $items;
        $this->response($data, 200);
    }

    public function getProductDetail_post() {
        $this->_requireAuthToken();
        $id = $this->post('id') ? $this->post('id') : false;
        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('id', 'Product ID', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('id')) {
                $error['msg'] = strip_tags(form_error('id'));
            }
            $this->response($error, 200);
        }

        $product_detail = $this->petshop_model->get_product_detail($id, true, true, true, true, true, true, $this->_member->id);
        if ($product_detail) {
            $response['item'] = format_output_data($product_detail);
        } else {
            $response['code'] = self::ERROR_CODE_ITEM_NOT_EXIST;
            $response['msg'] = lang('Item not found');
        }
        $this->response($response, 200);
    }

    public function addCommentProduct_post() {
        $this->_requireAuthToken();

        $product_id = $this->post('product_id') ? $this->post('product_id') : false;
        $comment = $this->post('comment') ? $this->post('comment') : false;
        $rating = $this->post('rating') ? $this->post('rating') : false;
        $created_date = now();
        $status = 1;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('product_id', 'Product ID', 'required');
        $this->form_validation->set_rules('comment', 'Comment', 'required');
        $this->form_validation->set_rules('rating', 'Rating ID', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('product_id')) {
                $error['msg'] = strip_tags(form_error('product_id'));
            }
            if (form_error('comment')) {
                $error['msg'] = strip_tags(form_error('comment'));
            }
            if (form_error('rating')) {
                $error['msg'] = strip_tags(form_error('rating'));
            }
            $this->response($error, 200);
        }
        $comment_data = array(
            'user_id' => $this->_member->id,
            'product_id' => $product_id,
            'comment' => $comment,
            'rating' => $rating,
            'created_date' => $created_date,
            'status' => $status,
        );
        $comment_id = $this->petshop_model->add_comment_product($comment_data);
        if ($comment_id) {
            $response['msg'] = lang('shop_add_new_comment_successful');
            $response['item'] = array(
                'comment_id' => $comment_id,
                'product_id' => (int) $product_id,
            );
        } else {
            $response['msg'] = lang('shop_add_new_comment_failure');
        }
        $this->response($response, 200);
    }

    public function getCommentsProduct_post() {
        $data = array();

        $start = $this->post('start') ? $this->post('start') : 0;
        $limit = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;
        $keyword = $this->post('keyword') ? $this->post('keyword') : false;
        $product_id = $this->post('product_id') ? $this->post('product_id') : false;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('product_id', 'Product ID', 'required');

        if ($this->form_validation->run() == FALSE) {
            $error_list = $this->form_validation->error_array();

            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('product_id')) {
                $error['msg'] = strip_tags(form_error('product_id'));
            }
            $this->response($error, 200);
        }

        $items = $this->petshop_model->get_comments_product($product_id, 'all', $start, $limit, $keyword, 1, 'id', 'DESC');
        $data['totalItem'] = $this->petshop_model->get_comments_product($product_id, 'count', $start, $limit, $keyword, 1);
        $data['totalPage'] = ceil(intval($data['totalItem']) / $limit);
        $data['limit'] = intval($limit);
        if ($items) {
            foreach ($items as $key => $item) {
                $items[$key] = format_output_data($item);
            }
        }

        $data['items'] = $items;
        $this->response($data, 200);
    }

    public function deleteCommentProduct_post(){
        $this->_requireAuthToken();
        
        $comment_id = $this->post('comment_id') ? $this->post('comment_id') : 0;

        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $this->form_validation->set_rules('comment_id', 'Comment ID', 'required|integer');
        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('comment_id')) {
                $error['msg'] = strip_tags(form_error('comment_id'));
            }
            $this->response($error, 200);
        }
        if ($this->petshop_model->check_exist_comment($this->_member->id, $comment_id)) {
            //delete comment
            $this->petshop_model->delete_comment($comment_id);

            //log    	
            log_message("info", "===========================");
            log_message("info", "DELETE COMMENT PRODUCT ID $comment_id");
            log_message("info", "===========================");

            $this->response(array('msg' => lang('delete comment product success')), 200);
        } else {
            $error['msg'] = lang('Item not found');
            $error['code'] = self::ERROR_CODE_404;
            $this->response($error, 200);
        }
    }
    
    public function getClientToken_post(){
        $this->_requireAuthToken();
        
        $this->load->library('braintree');
        $response['client_token'] = $this->braintree->clientToken();
        $this->response($response, 200);
    }
    
    public function submitOrder_post(){
        $this->_requireAuthToken();
        
        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $paymentMethodNonce = $this->post('payment_method_nonce') ? $this->post('payment_method_nonce') : false;
        $product_items = $this->post('product_items') ? $this->post('product_items') : false;
        $coupon_code = $this->post('coupon_code') ? $this->post('coupon_code') : false;
        $order_total = $this->post('order_total') ? $this->post('order_total') : false;
        
        $this->form_validation->set_rules('payment_method_nonce', 'Payment Method Nonce', 'required');
        $this->form_validation->set_rules('product_items[]', 'Product item', 'required');
        $this->form_validation->set_rules('order_total', 'Order total', 'required|numeric');
        
        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('payment_method_nonce')) {
                $error['msg'] = strip_tags(form_error('payment_method_nonce'));
            }
            if (form_error('product_items[]')) {
                $error['msg'] = strip_tags(form_error('product_items[]'));
            }
            if (form_error('order_total')) {
                $error['msg'] = strip_tags(form_error('order_total'));
            }
            $this->response($error, 200);
        }
        
//        $data = array();
//        $amount = 0;
//        foreach ($product_items as $item){
//            $amount += isset($item['subtotal']) ? $item['subtotal'] : 0;
//        }
       
        $this->load->library('braintree');
        $transaction = $this->braintree->createrTransaction($order_total, $paymentMethodNonce);
        if($transaction['result']){
            $response['msg'] = $transaction['data']['message'];
            
            //save order
            $order = array(
                'order_code' => $transaction['data']['transaction_id'],
                'user_id' => $this->_member->id,
                'total' => $order_total
            );
            $order_id = $this->petshop_model->add_Order($order);
            
            //save transaction
            $product_data = array();
            foreach ($product_items as $item) {
                $insert_item['product_id'] = isset($item['product_id']) ? $item['product_id'] : false;
                $insert_item['qty'] = isset($item['qty']) ? $item['qty'] : 0;
                $insert_item['price'] = isset($item['price']) ? $item['price'] : 0;
                $insert_item['options'] = serialize(array(
                    'color' => isset($item['color']) ? $item['color'] : 0,
                    'size' => isset($item['size']) ? $item['size'] : 0,
                ));
                $insert_item['created_date'] = now();
                $insert_item['order_id'] = $order_id;

                array_push($product_data, $insert_item);
            }
            $this->db->insert_batch('user_transaction', $product_data);
            
            //save information about coupon
            //$this->db->where('code', $coupon_code);
            //$this->db->update('pet_shop_coupon', array('qty' => 0));
        }
        else{
            $response['msg'] = $transaction['data']['message'];
            $response['code'] = $transaction['data']['code'];
        }
        
        $this->response($response, 200);

    }
    
    public function checkCoupon_post(){
        $this->_requireAuthToken();
        
        $this->load->library('form_validation');
        /* Set the form validation rules */
        $_POST = $this->post(); //set this for validate
        $code = $this->post('code') ? $this->post('code') : false;
        
        $this->form_validation->set_rules('code', 'Coupon Code', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $error['code'] = $this::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            if (form_error('code')) {
                $error['msg'] = strip_tags(form_error('code'));
            }
            $this->response($error, 200);
        }
        
        $data = $this->petshop_model->get_coupon($code);
        
        if($data){
            if($data->qty > 0){
                $response['items'] = array(
                    'code' => (string) $data->code,
                    'sale' => (float) $data->sale,
                    'type' => (int) $data->type,
                );
                $this->response($response, 200);
            }
            else{
                $error['code'] = $this::ERROR_CODE_ITEM_IN_USE;
                $error['msg'] = "Coupon have been used";
                $this->response($error, 200);
            }
        }
        
        $error['code'] = $this::ERROR_CODE_ITEM_NOT_EXIST;
        $error['msg'] = "Invalid Coupon code";
        $this->response($error, 200);
    }
}
