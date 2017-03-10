<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Petmessage {

    private $ci;

    function __construct($params = array()) {

        $this->ci = & get_instance();

        $this->ci->load->model('message_model');
        $this->ci->load->model('conversation_model');
        $this->ci->load->model('usercontact_model');
        $this->ci->load->model('notification_model');
        $this->ci->load->model('media_model');
        $this->ci->load->model('member_model');

        $this->ci->load->library('petupload');

        $this->ci->load->helper('notification');
    }

    /**
     * @param $senderObj
     * @param $receiverId
     * @param $content
     * @param $messageType
     * @return array of created message
     * @description: Send the message to target user
     */
    public function send( $senderObj, $receiverId, $content, $messageType ) {

        // check does user send message to himself or not
        if($senderObj->id == $receiverId) {
            return -2;
        }

        // Check are there any blocking between 2 users
        $param = "((user_id = $senderObj->id AND registed = $receiverId) OR (registed = $senderObj->id AND user_id = $receiverId)) AND status = " . CONTACT_BLOCK;
        $contact = $this->ci->usercontact_model->findOne($param);

        $isShow = 1;

        if( $contact ) {

            if($contact->user_id == $senderObj->id) {   // Sender blocked receiver before, so we should return the message to ask sender unblock receiver
                return -1;
            } elseif( $contact->user_id == $receiverId ) {  // Sender is blocked by receiver before, so receiver will not be seen the message
                $isShow = 0;
            }
        }

        // Step 1: Check are there any conversation between 2 users
        $strConversation = "( senderId = " . (int) $senderObj->id . " AND receivedId = " . (int) $receiverId . ") OR ".
            "( senderId = " . (int) $receiverId . " AND receivedId = " . (int) $senderObj->id . ")";
        $conversation = $this->ci->conversation_model->findOne($strConversation);

        if( !count($conversation) ) {
            $conversationId = $this->ci->conversation_model->save(array(
                "senderId"      => $senderObj->id,
                "receivedId"    => $receiverId,
                "created_date"  => now()
            ));
        } else {
            $conversationId = $conversation->id;
            /*$this->ci->message_model->deleteConversationsPermanently(
                array(
                    "deleted_by" => 0
                ),
                array(
                    "id" =>  $conversationId
                )
            );*/
            // We should check the user who sends the message has deleted the conversation before
            // If yes, we should remove his ID from deleted_by field
            $this->ci->conversation_model->conversationToggle( $conversationId, $senderObj->id, false );
        }

        $messageId = $this->ci->message_model->send(array(
            "senderID"      => $senderObj->id,
            "recievedID"    => $receiverId,
            "content"       => $content,
            "messageType"   => $messageType,
            "is_read"       => 0,
            "conversationId"=> $conversationId,
            "is_show"       => $isShow,
            "created_date"  => now()
        ));

        if( $messageType == IMAGE_MESSAGE ) {

            $this->ci->media_model->saveMedia( false, $messageId, 'message_id', $senderObj->id );
            /*$mediaFiles = $this->ci->petupload->doMultiUpload($this->ci->config->item('listings_path'));

            if ( $mediaFiles ) {
                $dataInsert = array();

                foreach ($mediaFiles as $file) {
                    $media_insert = array();

                    $file_array = $file['upload_data'];

                    $media_insert["message_id"]     = $messageId;
                    $media_insert['source']         = $this->ci->config->item('api_upload_path') . $this->ci->config->item('listings_path') . $file_array['file_name'];
                    $media_insert['created_date']   = now();
                    $media_insert['status']         = 1;
                    $media_insert['user_id']        = $senderObj->id;
                    $media_insert['type']           = 'PHOTO';

                    //resize
                    resizeImage($file_array['full_path'], IMAGE_RESIZE_WIDTH, IMAGE_RESIZE_HEIGHT);
                    $file_name_array = explode('.', $file_array['file_name']);
                    $media_insert['photo_thumb'] = $this->ci->config->item('api_upload_path') . $this->ci->config->item('listings_path') . $file_name_array[0] . '_thumb.' . $file_name_array[1];

                    if( function_exists('getimagesize')) {
                        // Get W, H of uploaded image for: original and thumb file
                        $objMedia = (object)$media_insert;
                        list($w, $h) = getimagesize($objMedia->source);
                        list($wThumb, $hThumb) = getimagesize($objMedia->photo_thumb);
                        $media_insert['width_thumb'] = $wThumb;
                        $media_insert['height_thumb'] = $hThumb;
                        $media_insert['width_source'] = $w;
                        $media_insert['height_source'] = $h;
                    }
                    array_push($dataInsert, $media_insert);
                }
                insert_user_media($dataInsert);
            }*/
        }

        $userOptions = $this->ci->member_model->get_user_options($receiverId);
        if( $userOptions->notifications_messages == 'on' && $isShow == 1 ) {
            $dataPush = array(
                'action_type'               => 'MESSAGE',
                'sender_id'                 => $senderObj->id,
                'sender_name'               => $senderObj->first_name . ' ' . $senderObj->last_name,
                'receiver_id'               => $receiverId,
                'type'                      => 'message',
                'messageId'                 => $messageId,
                'bages_unread_notification' => count_unread_notification($receiverId) + 1,
            );

            $messageObj     = $this->ci->message_model->findOne(array("m.id" => $messageId));
            $messagePush    = $messageType == TEXT_MESSAGE ? $content : $this->sentPhotoMessage($messageObj);
            $messagePush    = $senderObj->first_name . ' ' . $senderObj->last_name . ': ' . $messagePush;
            $this->ci->notification_model->send_push_notification($receiverId, $messagePush, $dataPush, 20, $messageId);
        }
        return $this->item($messageId);
    }

    /**
     * @param $senderObj
     * @param $receiverId
     * @param int $limit
     * @param $start
     * @return mixed
     * @description: Get the message list between 2 users
     */
    public function items( $senderObj, $receiverId, $limit = API_NUM_RECORD_PER_PAGE, $start ) {
        $memberId = (int) $senderObj->id;
        $strConds = "(( senderID = " . (int) $senderObj->id . " AND recievedID = " . (int) $receiverId . ") OR ".
            "( senderID = " . (int) $receiverId . " AND recievedID = " . (int) $senderObj->id . ")) AND is_show = 1
            ";

        $messages = $this->ci->message_model->find($strConds, $memberId, 'items', $start, $limit);

        $total = $this->ci->message_model->find($strConds, $memberId, 'total');

        // Make the conversation is read
        $this->readMessages($senderObj->id, $receiverId);

        // Get the status of logged in user and another user
        $contact = $this->ci->usercontact_model->findOne(array(
            "user_id" => $senderObj->id,
            "registed" => $receiverId
        ));

        $strUnread = "(( senderID = " . (int) $senderObj->id . " AND recievedID = " . (int) $receiverId . ") OR ".
            "( senderID = " . (int) $receiverId . " AND recievedID = " . (int) $senderObj->id . ")) AND is_show = 1 AND is_read = 0";

        $response[ITEMS]        = $this->itemsTransformer($messages);
        $response[TOTAL_ITEM]   = $total;
        $response[TOTAL_PAGE]   = $total > 0 ? ceil(intval($total) / $limit) : 0;
        $response['limit']      = intval($limit);
        $response[UNREAD_MESSAGE] = $this->ci->message_model->find($strUnread, $memberId, 'total');
        $response[CONTACT_STATUS] = $contact ? $contact->status : CONTACT_DEFAULT;

        return $response;
    }

    /**
     * @param $messageId
     * @return array
     * @description: Get a message object
     */
    public function item($messageId) {
        $messageObj = $this->ci->message_model->findOne(array(
            "m.id"    => $messageId
        ));
        return $this->itemTransformer($messageObj);
    }

    /**
     * @param $member
     * @param int $limit
     * @param $start
     * @return mixed
     * @description: Get the list of users who has the conversation with logged
     * in user
     */
    public function newMessages( $member, $limit = API_NUM_RECORD_PER_PAGE, $start ) {

        //$arrConds = array("m.recievedID" => $member->id);
        //$strConds = " c.senderId = " . (int) $member->id . " OR c.receivedId = " . (int) $member->id;
        $strConds = " senderID = " . (int) $member->id . " OR recievedID = " . (int) $member->id;

        $messages = $this->ci->message_model->newMessagesConversation($member,$strConds, 'items', $start, $limit);

        if( isset($messages) && !empty($messages) ){
            foreach ($messages as $key => $item) {
                $message = $this->ci->message_model->getLatestMessagesConversation($member, $item->senderID, $item->recievedID);
                $messages[$key] = $message;
            }
        }

        $total = $this->ci->message_model->newMessagesConversation($member,$strConds, 'total');

        $response[ITEMS]        = $this->newMessagesTransformer( $member, $messages );
        $response[TOTAL_ITEM]   = $total;
        $response[TOTAL_PAGE]   = $total > 0 ? ceil(intval($total) / $limit) : 0;
        $response['limit']      = intval($limit);

        return $response;
    }

    /**
     * @param $senderId
     * @param $receiverId
     * @description: update the conversation between 2 user is read
     */
    public function readMessages( $senderId, $receiverId ) {
        /*
         * In this case the logged in user should be treated as receiver user to update the message from
         * unread to read. If the user who send the message is in here, they cannot update the message
         */
        $arrCheck = array("senderID" => $receiverId, "recievedID" => $senderId, "is_read" => 0);
        if( $this->ci->message_model->canUpdate($arrCheck) ) {
            $strConds = "senderID = " . (int) $receiverId . " AND recievedID = " . (int) $senderId . " AND is_show = 1";
            $this->ci->message_model->read($strConds);
        }
    }

    public function checkConversation($senderId, $receiverId) {

    }

    /**
     * @param bool|false $messageObj
     * @return array
     * @description: Return the message array with formatted structure
     */
    protected function itemTransformer( $messageObj = false ) {

        if( $messageObj ) {
            format_output_data($messageObj);

            $data = array(
                ID      => $messageObj->id,
                SENDER  => array(
                    ID          => $messageObj->senderId,
                    FIRST_NAME  => $messageObj->senderFirstName,
                    LAST_NAME   => $messageObj->senderLastName,
                ),
                RECEIVER=> array(
                    ID          => $messageObj->receiverId,
                    FIRST_NAME  => $messageObj->receiverFirstName,
                    LAST_NAME   => $messageObj->receiverLastName
                ),
                TEXT            => $messageObj->messageType == TEXT_MESSAGE ? $messageObj->content : "",
                MESSAGE_TYPE    => $messageObj->messageType,
                CREATED_TIME    => $messageObj->created_time,
                CREATED_DATE    => $messageObj->created_date,
                ATTACHMENT      => array(),
                CONVERSATIONID  => $messageObj->conversationId,
            );

            if( $messageObj->messageType == IMAGE_MESSAGE ) {
                $media = $this->ci->media_model->getMediaData('items', array('message_id' => $messageObj->id));
                if($media) {
                    for( $i = 0; $i < count($media); $i++) {
                        $item = $media[$i];
                        format_output_data($item);
                        $data[ATTACHMENT][$i][URL]          = $item->photo_thumb;
                        $data[ATTACHMENT][$i][PHOTO_WIDTH]  = $item->width_thumb;
                        $data[ATTACHMENT][$i][PHOTO_HEIGHT] = $item->height_thumb;
                    }
                }
            }

            if( $messageObj->messageType == OBJECT_MESSAGE ) {
                $data[ATTACHMENT][PET_OBJECT] = $messageObj->content;
            }

            return $data;
        }
    }

    /**
     * @param $arrMessages
     * @return array
     * @description: Return array messages with formatted structure
     */
    protected function itemsTransformer( $arrMessages ) {

        $arrData = array();
        if( count($arrMessages) ) {
            foreach( $arrMessages as $messageObj ) {
                format_output_data($messageObj);

                $data = array(
                    ID      => $messageObj->id,
                    SENDER  => array(
                        ID          => $messageObj->senderId,
                        FIRST_NAME  => $messageObj->senderFirstName,
                        LAST_NAME   => $messageObj->senderLastName,
                        PROFILE_PHOTOS => array(
                            $messageObj->sender_profile_photo, $messageObj->sender_profile_photo_thumb
                        )
                    ),
                    RECEIVER=> array(
                        ID          => $messageObj->receiverId,
                        FIRST_NAME  => $messageObj->receiverFirstName,
                        LAST_NAME   => $messageObj->receiverLastName,
                        PROFILE_PHOTOS => array(
                            $messageObj->profile_photo, $messageObj->profile_photo_thumb
                        ),
                    ),
                    TEXT            => $messageObj->messageType == TEXT_MESSAGE ? $messageObj->content : "",
                    MESSAGE_TYPE    => $messageObj->messageType,
                    CREATED_TIME    => $messageObj->created_time,
                    CREATED_DATE    => $messageObj->created_date,
                    ATTACHMENT      => array(),
                    CONVERSATIONID  => $messageObj->conversationId,
                );

                if( $messageObj->messageType == IMAGE_MESSAGE ) {
                    $media = $this->ci->media_model->getMediaData('items', array('message_id' => $messageObj->id));
                    if($media) {
                        for( $i = 0; $i < count($media); $i++) {
                            $item = $media[$i];
                            format_output_data($item);
                            $data[ATTACHMENT][$i][URL]          = $item->photo_thumb;
                            $data[ATTACHMENT][$i][PHOTO_WIDTH]  = $item->width_thumb;
                            $data[ATTACHMENT][$i][PHOTO_HEIGHT] = $item->height_thumb;
                        }
                    }
                }

                if( $messageObj->messageType == OBJECT_MESSAGE ) {
                    $data[ATTACHMENT][PET_OBJECT] = $messageObj->content;
                }
                //$arrData[] = $data;
                array_unshift($arrData, $data);
            }
        }
        return $arrData;
    }

    protected function newMessagesTransformer( $member, $arrMessages ) {
        $arrData = array();
        if( count($arrMessages) ) {
            foreach( $arrMessages as $messageObj ) {
                format_output_data($messageObj);

                // Check the conversation between 2 users has any unread messages or the messages that is not displayed because of blocking user
                /*$strCheck = "(( senderID = " . (int) $messageObj->senderId . " AND recievedID = " . (int) $messageObj->receiverId . ") OR ".
                    "( senderID = " . (int) $messageObj->receiverId . " AND recievedID = " . (int) $messageObj->senderId . ")) AND is_show = 1";*/

                // Get latest message
                //$latestMsg = $this->ci->message_model->getLatestMessage($strCheck);
                //print_r($latestMsg);die();
                $from = YOU . ": ";

                /*if( $latestMsg ) {
                    format_output_data($latestMsg);
                    if( $member->id != $latestMsg->senderID ) {
                        //$from = $messageObj->senderFirstName . " " . $messageObj->senderLastName;
                        $from = "";
                    }
                    $latest = $latestMsg->messageType == TEXT_MESSAGE ? $latestMsg->content : $this->sentPhotoMessage($latestMsg);
                }*/

                if( $member->id != $messageObj->senderID ) {
                    $from = "";
                }
                $latest = $messageObj->messageType == TEXT_MESSAGE ? $messageObj->content : $this->sentPhotoMessage($messageObj);

                // default profile photo
                $arrProfilePhotos = array(
                    (!empty($messageObj->sender_profile_photo) && $messageObj->sender_profile_photo != "0") ? $messageObj->sender_profile_photo : "",
                    (!empty($messageObj->sender_profile_photo_thumb) && $messageObj->sender_profile_photo_thumb != "0") ? $messageObj->sender_profile_photo_thumb : ""
                );
                if( $member->id == $messageObj->senderId ) {
                    $arrProfilePhotos = array(
                        (!empty($messageObj->profile_photo) && $messageObj->profile_photo != "0") ? $messageObj->profile_photo : "",
                        (!empty($messageObj->profile_photo_thumb) && $messageObj->profile_photo_thumb != "0") ? $messageObj->profile_photo_thumb : ""
                    );
                }

                $data = array(
                    CONTACT  => array(
                        ID          => $member->id == $messageObj->senderId ? $messageObj->receiverId : $messageObj->senderId,
                        FIRST_NAME  => $member->id == $messageObj->senderId ? $messageObj->receiverFirstName : $messageObj->senderFirstName,
                        LAST_NAME   => $member->id == $messageObj->senderId ? $messageObj->receiverLastName : $messageObj->senderLastName,
                        PROFILE_PHOTOS => $arrProfilePhotos,
                    ),
                    //IS_READ         => $member->id == $messageObj->senderID ? 1 : $this->ci->message_model->isRead($strCheck . " AND is_read = 0"),
                    IS_READ         => $member->id == $messageObj->senderId ? 1 : $messageObj->is_read,
                    TEXT            => $from . $latest,
                    MESSAGE_TYPE    => $messageObj->messageType,
                    CREATED_TIME    => $messageObj->created_time,
                    CREATED_DATE    => $messageObj->created_date,
                    ATTACHMENT      => array(),
                    CONVERSATIONID  => $messageObj->conversationId,
                );

                if( $messageObj->messageType == IMAGE_MESSAGE ) {
                    $messageMedia = $this->ci->db->get_where("user_media", array("message_id" => $messageObj->id))->row();
                    //$data[ATTACHMENT][URL] = $messageMedia->photo_thumb;
                    $data[ATTACHMENT][URL] = ""; 
                    if( isset($messageMedia) && !empty($messageMedia)){
                        $data[ATTACHMENT][URL] = $messageMedia->photo_thumb;
                    } 
                }
                $arrData[] = $data;
            }
        }
        return $arrData;
    }

    /**
     * @param $senderObj
     * @param $receiverId
     * @param int $limit
     * @param $start
     * @return mixed
     * @description: Get unread message list
     */
    public function unreadMessagesList( $senderObj, $receiverId, $limit = API_NUM_RECORD_PER_PAGE, $start ) {
        $memberId = (int) $senderObj->id;
        $strConds = "(( senderID = " . (int) $senderObj->id . " AND recievedID = " . (int) $receiverId . ") OR ".
            "( senderID = " . (int) $receiverId . " AND recievedID = " . (int) $senderObj->id . ")) AND is_show = 1 AND is_read = 0";

        $messages = $this->ci->message_model->find($strConds, $memberId, 'items', $start, $limit);

        $total = $this->ci->message_model->find($strConds, $memberId, 'total');

        // Get the status of logged in user and another user
        $contact = $this->ci->usercontact_model->findOne(array(
            "user_id" => $senderObj->id,
            "registed" => $receiverId
        ));

        $response[ITEMS]        = $this->itemsTransformer($messages);
        $response[TOTAL_ITEM]   = $total;
        $response[TOTAL_PAGE]   = $total > 0 ? ceil(intval($total) / $limit) : 0;
        $response['limit']      = intval($limit);
        $response[CONTACT_STATUS] = $contact ? $contact->status : CONTACT_DEFAULT;

        return $response;
    }

    /**
     * @param $member
     * @param $messageId
     * @description: Delete message
     */
    public function deleteMessage( $member, $messageId ) {
        $messageObj = $this->ci->message_model->findOne(
            array("m.id" => $messageId)
        );

        if( $messageObj ) {
            // Update the message status is deleted
            $this->ci->message_model->deleteMessage(
                array(
                    "is_delete" => 1,
                    "content" => lang('message_remove'),
                ),
                array(
                    "id" => $messageId,
                    "senderID" => $member->id
                )
            );
        }
    }

    protected function sentPhotoMessage($messageObj = false) {
        if($messageObj) {
            $total = $this->ci->media_model->getMediaData( 'total', array('message_id' => $messageObj->id) );

            if( $total > 0 ) {
                return $total > 1 ? lang('send_photos') : lang('send_photo');
            } else {
                return '';
            }
        }
        return '';
    }

    public function deleteConversationByUser( $member, $conversationId ){
        $strConversation = "( id='$conversationId')";
        $conversation = $this->ci->conversation_model->findOne($strConversation);
        if(count($conversation) ) {
            $conversationId = $conversation->id;
            /*$this->ci->message_model->deleteConversationsPermanently(
                array(
                    "deleted_by" => $member->id
                ),
                array(
                    "id" =>  $conversationId
                )
            );
            $this->ci->message_model->deleteMessagePermanently($member->id, $conversationId);*/
            $this->ci->conversation_model->conversationToggle( $conversationId, $member->id, true );
            $this->ci->message_model->deleteMessagePermanently($member->id, $conversationId);
        }
    }

}