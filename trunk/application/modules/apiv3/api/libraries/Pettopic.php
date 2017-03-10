<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pettopic {

    private $ci;

    protected $error;

    protected $_member;

    function __construct($params = array()) {
        $this->ci = & get_instance();

        $this->ci->load->model('pettalk_model');
        $this->ci->load->model('member_model');
        $this->ci->load->model('newsfeed_model');
        $this->ci->load->model('pettalkinfo_model');
        $this->ci->load->model('usertag_model');
        $this->ci->load->model('notification_model');
        $this->ci->load->model('media_model');
        //load lang
        $this->ci->lang->load('api');
        //load helper
        $this->ci->load->helper(array('form', 'url'));
        $this->ci->load->helper('newsfeeds');
        $this->ci->load->helper('notification');

        $this->ci->load->library('petnewsfeed');
    }

    /**
     * @param $title
     * @param $content
     * @param $catId
     * @param $member
     * @return int|mixed
     */
    function saveNew( $title, $content, $catId, $userTag = '', $member ) {

        $status         = 1;
        $created_date   = now();
        $created_by     = $member->id;

        $topic_data = array(
            'category_id'   => $catId,
            'title'         => $title,
            //'content'       =>  '<p>'. add_break_link($content) . '</p>',
            'content'       => $content,
            'status'        => $status,
            'created_date'  => $created_date,
            'created_by'    => $created_by
        );

        $topicId = $this->ci->pettalk_model->add_topic($topic_data);

        if ( $topicId ) {

            $newsFeedId = $this->ci->petnewsfeed->addNew(array(
                "topic_id"      => $topicId,
                "newsFeedType"  => ADD_PET_TOPIC,
                "user_id"       => $member->id,
                "created_date"  => now(),
            ));

            //$this->ci->petnewsfeed->saveNewsFeedMedia( $newsFeedId, $topicId, 'topic_id', $member->id );
            $this->ci->media_model->saveMedia( $newsFeedId, $topicId, 'topic_id', $member->id );

            // Check and add user tag
            if( !empty($userTag) ) {
                $arrUsers = json_decode($userTag);
                $name_user_action   = $member->first_name . ' ' . $member->last_name;
                $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );
                $action_type = get_action_type(LIKE_TOPIC);

                foreach( $arrUsers as $item ) {
                    $this->ci->usertag_model->saveNew(array(
                        "sourceId"      => $newsFeedId,
                        "sourceType"    => NEWSFEED_USER_TAG,
                        "userTag"       => $item->userId,
                        "userId"        => $created_by,
                        "textRange"     => $item->textRange,
                        "created_date"  => $created_date,
                    ));

                    $data_push_tag = array(
                        'action_type'               => LIKE_TOPIC,
                        'sender_id'                 => $member->id,
                        'sender_name'               => $name_user_action,
                        'receiver_id'               => $item->userId,
                        'type'                      => 'feed',
                        'topic_id'                  => $topicId,
                        'newsFeedItemId'            => $topicId,
                        'newsFeedType'              => ADD_PET_TOPIC,
                        'newsFeedId'                => $newsFeedId,
                        'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                    );
                    $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push_tag, $action_type->id, $topicId);
                }
            }

            return $this->ci->petnewsfeed->detail( $newsFeedId, $topicId, ADD_PET_TOPIC, $member);
        } else {
            return -1;
        }
    }

    /**
     * @param bool|false $newsFeedId
     * @param $newsFeedItemId
     * @param $member
     * @param $title
     * @param $content
     * @param $catId
     * @return mixed
     * @description: Update pet topic and return an newsfeed topic object
     */
    public function save( $newsFeedId = false, $newsFeedItemId, $member, $title, $content, $catId, $removeMedia = '', $userTag = '' ) {

        //$content = add_break_link($content);

        if(!$newsFeedId) {
            $newsFeedId = $this->ci->newsfeed_model->getNewsfeedIdFromItem( $newsFeedItemId, ADD_PET_TOPIC );
        }

        $this->ci->pettalk_model->updateTopicNewsfeed( $member->id, $newsFeedId, $newsFeedItemId, $title, $content, $catId );

        //$this->ci->petnewsfeed->saveNewsFeedMedia( $newsFeedId, $newsFeedItemId, 'topic_id', $member->id );
        $this->ci->media_model->saveMedia( $newsFeedId, $newsFeedItemId, 'topic_id', $member->id );

        if( !empty($removeMedia) ) {
            //removeNewsFeedMedia($removeMedia, $newsFeedId, $member->id);
            $this->ci->newsfeed_model->deleteSelectedNewsfeedMedia( $member->id, $newsFeedId, $removeMedia );
        }

        // We delete user tag first, because user always send tags when they add / edit
        $this->ci->usertag_model->delete(array(
            "sourceId"      => $newsFeedId,
            "sourceType"    => NEWSFEED_USER_TAG,
            "userId"        => $member->id
        ));

        // Check and add user tag
        if( !empty($userTag) ) {
            $arrUsers = json_decode($userTag);
            $name_user_action   = $member->first_name . ' ' . $member->last_name;
            $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );
            $action_type = get_action_type(LIKE_TOPIC);
            foreach( $arrUsers as $item ) {
                $this->ci->usertag_model->saveNew(array(
                    "sourceId"      => $newsFeedId,
                    "sourceType"    => NEWSFEED_USER_TAG,
                    "userTag"       => $item->userId,
                    "userId"        => $member->id,
                    "textRange"     => $item->textRange,
                    "created_date"  => now(),
                ));
                $data_push_tag = array(
                    'action_type'               => LIKE_TOPIC,
                    'sender_id'                 => $member->id,
                    'sender_name'               => $name_user_action,
                    'receiver_id'               => $item->userId,
                    'type'                      => 'feed',
                    'topic_id'                  => $newsFeedItemId,
                    'newsFeedItemId'            => $newsFeedItemId,
                    'newsFeedType'              => ADD_PET_TOPIC,
                    'newsFeedId'                => $newsFeedId,
                    'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                );
                $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push_tag, $action_type->id, $newsFeedItemId);
            }
        }

        return $this->ci->petnewsfeed->detail( $newsFeedId, $newsFeedItemId, ADD_PET_TOPIC, $member);
    }

    function categoryList() {
        $results = $this->ci->pettalk_model->getPettalkCategory();
        $response = array();
        $response[ITEMS] = $this->categoryListTransformer($results);
        return $response;
    }

    /**
     * @param int $userId
     * @param int $catId
     * @param string $keyword
     * @param int $start
     * @param int $limit
     * @return array
     * @description: Get the pettalk list (include pettalk topic and pettalk info)
     */
    function pettalkList( $userId = 0, $catId = 0, $keyword = "", $member, $start = 0, $limit = API_NUM_RECORD_PER_PAGE ) {

        $params = array(
            "userId"    => $userId,
            "catId"     => $catId,
            "keyword"   => $keyword,
            "userLoginId" => $member->id
        );
        $response = array();

        $results = $this->ci->pettalkinfo_model->getPetalkList( $params, 'item', $start, $limit )->pettalkListTransformer($member->id);
        $total  = $this->ci->pettalkinfo_model->getPetalkList( $params, 'total' );

        $response[ITEMS]        = $results;
        $response[TOTAL_ITEM]   = $total;
        $response[TOTAL_PAGE]   = $total > 0 ? ceil(intval($total) / $limit) : 0;
        $response[LIMIT]        = intval($limit);

        return $response;
    }

    function getPettopicDetail( $newsFeedId = false, $newsFeedItemId = false ) {
        $result = $this->ci->pettalk_model->getPettalkDetail( $newsFeedId );

        //return $this->pettalkTopicTransformer($result);
    }


    /**
     * @param array $params
     * @param $member
     * @return int
     * @description: Add new Pet info
     */
    public function saveNewPetInfo( $params = array(), $member, $userTag = '' ) {

        $this->ci->load->library('petupload');

        $params["created_date"] = now();
        $params["updated_date"] = now();
        $params["user_id"]      = $member->id;

        $id = $this->ci->pettalkinfo_model->saveNew($params);

        if ($id) {

            $newsFeedId = $this->ci->petnewsfeed->addNew(array(
                "pettalk_info_id"   => $id,
                "newsFeedType"      => $params['infoType'],
                "user_id"           => $member->id,
                "created_date"      => now(),
                "updated_date"      => now()
            ));

            /*if( isset($_FILES['coverPhoto']) && !empty($_FILES['coverPhoto']['name']) ) {
                $file = $this->ci->petupload->store($_FILES['coverPhoto'], 'coverPhoto');
                if(isset($file['file_name'])) {
                    $params['photo'] = $this->ci->config->item('api_upload_path') . $this->ci->config->item('listings_path') . $file['file_name'];
                }
            }*/

            // Save Pettalk Info attachement images
            $this->ci->media_model->saveSingleMedia( $newsFeedId, $id, 'pettalk_info_id', $member->id, 1, 'coverPhoto', 'cover' );

            // Save Pettalk Info attachement images
            //$this->ci->petnewsfeed->saveNewsFeedMedia( $newsFeedId, $id, 'pettalk_info_id', $member->id );
            $this->ci->media_model->saveMedia( $newsFeedId, $id, 'pettalk_info_id', $member->id, 1, 'file', 'attachment' );

            // Check and add user tag
            if( !empty($userTag) ) {

                $acType = LIKE_ADOPTION;

                if( $params['infoType'] == ADD_PETTALK_LOST_REPORT ) {
                    $acType = LIKE_LOST_REPORT;
                } elseif( $params['infoType'] == ADD_PETTALK_FOUND_REPORT ) {
                    $acType = LIKE_FOUND_REPORT;
                }

                $arrUsers = json_decode($userTag);
                $name_user_action   = $member->first_name . ' ' . $member->last_name;
                $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );
                $action_type = get_action_type($acType);

                foreach( $arrUsers as $item ) {
                    $this->ci->usertag_model->saveNew(array(
                        "sourceId"      => $newsFeedId,
                        "sourceType"    => NEWSFEED_USER_TAG,
                        "userTag"       => $item->userId,
                        "userId"        => $member->id,
                        "textRange"     => $item->textRange,
                        "created_date"  => now()
                    ));

                    $data_push_tag = array(
                        'action_type'               => $acType,
                        'sender_id'                 => $member->id,
                        'sender_name'               => $name_user_action,
                        'receiver_id'               => $item->userId,
                        'type'                      => 'feed',
                        'pettalk_info_id'           => $id,
                        'newsFeedItemId'            => $id,
                        'newsFeedType'              => $params['infoType'],
                        'newsFeedId'                => $newsFeedId,
                        'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                    );
                    $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push_tag, $action_type->id, $id);
                }
            }

            return $this->ci->petnewsfeed->detail( $newsFeedId, $id, $params['infoType'], $member);

        } else {
            return -1;
        }
    }

    /**
     * @param $newsFeedId
     * @param $newsFeedItemId
     * @param $newsFeedType
     * @param array $params
     * @param $member
     * @param array $removeMedia
     * @return mixed
     * @description: Update Pet info
     */
    public function savePetInfo( $newsFeedId, $newsFeedItemId, $newsFeedType, $params = array(), $member, $removeMedia = '' ) {

        $this->ci->load->library('petupload');

        $conditions = array();
        $pettalkInfo = false;// We store the pettalk info for editing image

        $params["updated_date"] = now();
        $params["lat"] = $params["latitude"];
        $params["lng"] = $params["longitude"];
        $params["catId"] = $params["category_id"];

        $conditions["id"]        = $newsFeedItemId;
        $conditions["user_id"]   = $member->id;
        $userTag                 = isset($params['tags']) ? $params['tags'] : '';

        // Remove unnessesary info
        unset($params["newsFeedItemId"]);
        unset($params["newsFeedId"]);
        unset($params["newsFeedType"]);
        unset($params["latitude"]);
        unset($params["longitude"]);
        unset($params["category_id"]);
        unset($params["removeMedia"]);
        unset($params["API-KEY"]);
        unset($params["token"]);
        unset($params["tags"]);

        /*if( isset($_FILES['coverPhoto']) && !empty($_FILES['coverPhoto']['name']) ) {
            $file = $this->ci->petupload->store($_FILES['coverPhoto'], 'coverPhoto');
            if(isset($file['file_name'])) {
                $params['photo'] = $this->ci->config->item('api_upload_path') . $this->ci->config->item('listings_path') . $file['file_name'];

                $pettalkInfo = $this->ci->pettalkinfo_model->item($newsFeedId, $newsFeedItemId, $member->id)->get();
            }
        }*/

        $this->ci->pettalkinfo_model->save($params, $conditions);

        // Save Pettalk Info images
        //$this->ci->petnewsfeed->saveNewsFeedMedia( $newsFeedId, $newsFeedItemId, 'pettalk_info_id', $member->id );
        $this->ci->media_model->saveSingleMedia( $newsFeedId, $newsFeedItemId, 'pettalk_info_id', $member->id, 1, 'coverPhoto', 'cover' );

        $this->ci->media_model->saveMedia( $newsFeedId, $newsFeedItemId, 'pettalk_info_id', $member->id, 1, 'file', 'attachment' );

        if($removeMedia) {
            //removeNewsFeedMedia($removeMedia, $newsFeedId, $member->id);
            $this->ci->newsfeed_model->deleteSelectedNewsfeedMedia( $member->id, $newsFeedId, $removeMedia );
        }

        /*if( isset($_FILES['coverPhoto']) && !empty($_FILES['coverPhoto']['name']) && $pettalkInfo ) {
            //@unlink($pettalkInfo->photo);
            $this->ci->newsfeed_model->deleteSelectedNewsfeedMedia( $member->id, $newsFeedId, $removeMedia );
        }*/

        // remove current user tags
        $this->ci->usertag_model->delete(array(
            "sourceId"      => $newsFeedId,
            "sourceType"    => NEWSFEED_USER_TAG,
            "userId"        => $member->id
        ));

        if( !empty($userTag) ) {
            $acType = LIKE_ADOPTION;

            if( $newsFeedType == ADD_PETTALK_LOST_REPORT ) {
                $acType = LIKE_LOST_REPORT;
            } elseif( $newsFeedType == ADD_PETTALK_FOUND_REPORT ) {
                $acType = LIKE_FOUND_REPORT;
            }

            $arrUsers = json_decode($userTag);
            $name_user_action   = $member->first_name . ' ' . $member->last_name;
            $messagePushTag     = sprintf(lang('tag_user'), $member->first_name . ' ' . $member->last_name, 'newsfeed' );
            $action_type = get_action_type($acType);
            foreach( $arrUsers as $item ) {
                $this->ci->usertag_model->saveNew(array(
                    "sourceId"      => $newsFeedId,
                    "sourceType"    => NEWSFEED_USER_TAG,
                    "userTag"       => $item->userId,
                    "userId"        => $member->id,
                    "textRange"     => $item->textRange,
                    "created_date"  => now()
                ));

                $data_push_tag = array(
                    'action_type'               => $acType,
                    'sender_id'                 => $member->id,
                    'sender_name'               => $name_user_action,
                    'receiver_id'               => $item->userId,
                    'type'                      => 'feed',
                    'pettalk_info_id'           => $newsFeedItemId,
                    'newsFeedItemId'            => $newsFeedItemId,
                    'newsFeedType'              => $newsFeedType,
                    'newsFeedId'                => $newsFeedId,
                    'bages_unread_notification' => count_unread_notification($item->userId) + 1,
                );
                $this->ci->notification_model->send_push_notification($item->userId, $messagePushTag, $data_push_tag, $action_type->id, $newsFeedItemId);
            }
        }

        return $this->ci->petnewsfeed->detail( $newsFeedId, $conditions["id"], $newsFeedType, $member);
    }

    protected function categoryListTransformer($results = array()) {
        $arrData = array();
        if( count($results) ) {

            foreach($results as $item) {
                $arrData[] = array(
                    ID          => $item->id,
                    NAME        => $item->name,
                    DESCRIPTION => $item->description,
                    CAT_TYPE    => $item->catType,
                    "photo"     => $item->photo,
                    "photo_thumb" => $item->photo_thumb,
                    "is_popular"=> $item->is_popular,
                    "status"    => $item->status,
                    "sort"      => $item->sort
                );
            }
        }
        return $arrData;
    }

    public function searchPetTalk($member, $keyword, $option = ALL, $limit, $start,$user_location=array()) {
        $response = array();

        $results = $this->ci->pettalkinfo_model->searchPetTalk($member, $keyword, 'item', $start, $limit,$user_location )->pettalkListTransformer($member->id);
        $total  = $this->ci->pettalkinfo_model->searchPetTalk($member, $keyword, 'total',$start, $limit,$user_location );

        $response[ITEMS]        = $results;
        $response[TOTAL_ITEM]   = $total;
        $response[TOTAL_PAGE]   = $total > 0 ? ceil(intval($total) / $limit) : 0;
        $response[LIMIT]        = intval($limit);
        return $response; 
    }
}