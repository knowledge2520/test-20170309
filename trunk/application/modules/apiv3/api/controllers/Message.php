<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'/modules/api/api/libraries/REST_Controller.php';

class Message extends REST_Controller {

    function __construct() {

        // Construct our parent class
        parent::__construct();

        $this->load->library('petmessage');
        //load lang
        $this->lang->load('api');
        //$this->load->library('petmessage2');
    }

    /**
     * @description: API for sending message
     */
    function send_post() {

        $this->_requireAuthToken();

        $content        = $this->post('text') ? $this->post('text') : "";
        $messageType    = $this->post('messageType') ? $this->post('messageType') : false;
        $receiverId     = $this->post('receiverId') ? $this->post('receiverId') : "";

        $allowMsgTypes  = array(TEXT_MESSAGE, IMAGE_MESSAGE, OBJECT_MESSAGE, STICKER_MESSAGE, GIF_MESSAGE);

        if( !$messageType ) {
            $error['msg'] = "Please input message type";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if( !$receiverId ) {
            $error['msg'] = "Please input receiver ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        if( !in_array($messageType, $allowMsgTypes) ) {
            $error['msg'] = "Message type is wrong";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $result = $this->petmessage->send( $this->_member, $receiverId, $content, $messageType );

        if( is_array($result) ) {
            $response[ITEM] = $result;
            $this->response($response, 200);
        } elseif( is_integer($result) && $result == -1 ) {
            $error['msg'] = "Unblock user to send messages to each other again ?";
            $error['code'] = self::ERROR_CODE_USER_INVALID;
            $this->response($error, 200);
        } elseif( is_integer($result) && $result == -2 ) {
            $error['msg'] = "Oops You cannot send message to yourself ?";
            $error['code'] = self::ERROR_CODE_USER_INVALID;
            $this->response($error, 200);
        }
    }

    function items_post() {
        $this->_requireAuthToken();

        $receiverId     = $this->post('receiverId') ? $this->post('receiverId') : "";

        $start          = $this->post('start') ? $this->post('start') : 0;

        $limit          = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        if( !$receiverId ) {
            $error['msg'] = "Please input receiver ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $response       = $this->petmessage->items( $this->_member, $receiverId, $limit, $start );

        $this->response($response, 200);
    }

    function newMessages_post() {
        $this->_requireAuthToken();

        $start          = $this->post('start') ? $this->post('start') : 0;

        $limit          = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        $response       = $this->petmessage->newMessages( $this->_member, $limit, $start );

        $this->response($response, 200);
    }

    function unreadMessages_post() {
        $this->_requireAuthToken();

        $receiverId     = $this->post('receiverId') ? $this->post('receiverId') : "";

        $start          = $this->post('start') ? $this->post('start') : 0;

        $limit          = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        if( !$receiverId ) {
            $error['msg'] = "Please input receiver ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $response       = $this->petmessage->unreadMessagesList( $this->_member, $receiverId, $limit, $start );

        $this->response($response, 200);
    }

    function deleteMessage_post() {
        $this->_requireAuthToken();

        $messageId = $this->post('messageId') ? $this->post('messageId') : 0;

        $this->petmessage->deleteMessage( $this->_member, $messageId );

        $this->response(array(), 200);
    }

    function deleteConversationByUser_post(){
        $this->_requireAuthToken();
        $conversationId = $this->post('conversationId') ? $this->post('conversationId') : 0;
        $this->petmessage->deleteConversationByUser( $this->_member, $conversationId );
        $this->response(array(), 200);
    }

    /*
     * THESE METHODS ABOVE FOR MESSAGE V2
     */
    /*function sendMessage_post() {
        $this->_requireAuthToken();

        $content        = $this->post('text') ? $this->post('text') : "";
        $messageType    = $this->post('messageType') ? $this->post('messageType') : false;
        $receiverId     = $this->post('receiverId') ? $this->post('receiverId') : false;
        $groupId        = $this->post('groupId') ? $this->post('groupId') : false;

        $response[ITEM] = $this->petmessage2->send( $this->_member, $receiverId, $groupId, $content, $messageType );

        $this->response($response, 200);
    }

    function lastMessages_post() {
        $this->_requireAuthToken();

        $start          = $this->post('start') ? $this->post('start') : 0;

        $limit          = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        $response       = $this->petmessage2->lastMessages( $this->_member, $limit, $start );

        $this->response($response, 200);
    }

    function itemsV2_post() {
        $this->_requireAuthToken();

        $receiverId     = $this->post('receiverId') ? $this->post('receiverId') : "";

        $start          = $this->post('start') ? $this->post('start') : 0;

        $limit          = $this->post('limit') ? $this->post('limit') : API_NUM_RECORD_PER_PAGE;

        if( !$receiverId ) {
            $error['msg'] = "Please input receiver ID";
            $error['code'] = self::ERROR_CODE_MISSIG_FIELD_REQUIRED;
            $this->response($error, 200);
        }

        $response       = $this->petmessage2->items( $this->_member, $receiverId, $limit, $start );

        $this->response($response, 200);
    }*/

}