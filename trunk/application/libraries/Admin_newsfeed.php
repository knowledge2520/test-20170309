<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_newsfeed
{
	 private $ci;

    protected $newsfeedMedia;

    function __construct($params = array()) {

        $this->ci = & get_instance();

        $this->ci->load->model('newsfeed/newsfeed_model');
        $this->ci->load->model('pet_talk/pet_talk_info_model');
        $this->ci->load->model('pet_talk/pet_talk_model');
        $this->ci->load->model('notification/notification_model');
        $this->ci->load->model('business/checkins_model');
        $this->ci->load->model('business/reviews_model');

        //load helper
        $this->ci->load->helper(array('form', 'url'));

    }

	function deletePettalkInfoAdmin($id){		

		$pet_talk_info = $this->ci->pet_talk_info_model->detail($id);

		if($pet_talk_info){
			$newsfeed = $this->ci->newsfeed_model->getNewsFeedBy('pettalk_info_id', $id);

			if($newsfeed){
				$newsFeedId = $newsfeed->id;
				$newsFeedItemId = $id;
				$newsFeedType = $newsfeed->newsFeedType;

				$this->ci->admin_newsfeed->_doDeleteNewsfeed($newsFeedId, $newsFeedItemId, $newsFeedType);
                 //delete pettalk info
                $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"pettalk_info_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
			}
           
            $this->ci->pet_talk_info_model->deletePettalkInfo($id);
            return true;
		}
        return false;
	}

	function deletePettalkAdmin($id){		
		
		$newsfeed = $this->ci->newsfeed_model->getNewsFeedBy('id', $id);

		if($newsfeed && in_array($newsfeed->newsFeedType, array(ADD_POST_UPDATED, ADD_PET_TOPIC))){
            $paramId = $newsfeed->newsFeedType == ADD_POST_UPDATED ? 'post_update_id' : 'topic_id' ;

			$newsFeedId = $id;
			$newsFeedItemId = $newsfeed->$paramId;
			$newsFeedType = $newsfeed->newsFeedType;

			$this->ci->admin_newsfeed->_doDeleteNewsfeed($newsFeedId, $newsFeedItemId, $newsFeedType);

            if($newsfeed->newsFeedType == ADD_POST_UPDATED){
                // delete pettalk topic
                $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"topic_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')"); 
                $this->ci->pet_talk_model->deletePetTalk($id);
            }else{
                //delete pettalk post updated
                $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"post_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                $this->ci->pet_post_updated_model->deletePostUpdate($newsFeedItemId );
            }
            return true;
		}
        return false;
	}

    function deleteReviewAdmin($id){
        $review = $this->ci->reviews_model->detail($id);

        if($review){
            $newsfeed = $this->ci->newsfeed_model->getNewsFeedBy('topic_id', $id);

            if($newsfeed){
                $newsFeedId = $newsfeed->id;
                $newsFeedItemId = $id;
                $newsFeedType = $newsfeed->newsFeedType;

                $this->ci->admin_newsfeed->_doDeleteNewsfeed($newsFeedId, $newsFeedItemId, $newsFeedType);

                //delete pettalk info
                $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"topic_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");                
            }
            $this->ci->reviews_model->deleteReview($id);
            return true;
        }
        return false;
    }

	function deleteNewsfeedAdmin(){
		$newsfeed = $this->ci->newsfeed_model->getNewsfeedFromItem( $newsFeedItemId, $newsFeedType, $member->id );

        if($newsfeed) {
            if( !$newsFeedId ) {
                $newsFeedId = $newsfeed->id;
            }

            $this->ci->_doDeleteNewsfeed($newsFeedId, $newsFeedItemId, $newsFeedType);

            switch( $newsFeedType ) {

                case ADD_PETTALK_ADOPTION:
                case ADD_PETTALK_FOUND_REPORT:
                case ADD_PETTALK_LOST_REPORT:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"pettalk_info_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->pet_talkinfo_model->deletePettalkInfo($newsFeedItemId );
                    break;

                case ADD_POST_UPDATED:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"post_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->newsfeed_model->deletePostUpdate($newsFeedItemId );
                    break;

                case ADD_CHECKIN:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"checkin_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->checkins_model->delete( $newsFeedItemId );
                    break;

                case ADD_PET_TOPIC:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"topic_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->pet_talk_model->deleteTopic( $newsFeedItemId );
                    break;

                case ADD_REVIEW:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"review_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->reviews_model->deleteReview( $newsFeedItemId );
                    break;

                case ADD_PHOTO_LISTING:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"album_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->listing_model->deleteListingPhoto( $newsFeedItemId );
                    break;

                case ADD_SHARING_PHOTO:
                    $this->ci->notification_model->deleteByKeyWord("data REGEXP '\"newsFeedId\":(.?)$newsFeedId' AND (data REGEXP '\"sharing_id\":(.?)$newsFeedItemId' OR data REGEXP '\"newsFeedItemId\":(.?)$newsFeedItemId')");
                    $this->ci->photosharing_model->deleteSharingPhoto( $newsFeedItemId);
                    break;
            }
        }
	}

	function _doDeleteNewsfeed($newsFeedId, $newsFeedItemId, $newsFeedType){

        // Remove like news feed of checkin
        $this->ci->newsfeed_model->deleteLikeByNewsfeed($newsFeedItemId, $newsFeedType);

        // Remove comment news feed of checkin and the comment's photo
        $this->ci->newsfeed_model->deleteCommentByNewsfeed($newsFeedItemId, $newsFeedType);

        // Remove user tags
        $this->ci->newsfeed_model->deleteUserTag(array("sourceId" => $newsFeedId, "sourceType" => NEWSFEED_USER_TAG));

        // Remove review photos
        $this->ci->newsfeed_model->deleteNewsfeedMedia( $newsFeedId );

        // Remove sharing data
        $this->ci->newsfeed_model->deleteSharingInfo( $newsFeedId, $newsFeedItemId, $newsFeedType );

        // Remove user newsfeed activities
        $this->ci->newsfeed_model->deleteNewsfeed( $newsFeedId, $newsFeedItemId, $newsFeedType );

	}
}