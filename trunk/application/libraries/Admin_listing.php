<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_listing
{
	 private $ci;

    protected $newsfeedMedia;

    function __construct() {

        $this->ci = & get_instance();

        $this->ci->load->model('newsfeed/newsfeed_model');
        $this->ci->load->model('pet_talk/pet_talk_info_model');
        $this->ci->load->model('pet_talk/pet_talk_model');
        $this->ci->load->model('notification/notification_model');
        




        $this->ci->load->model('business/business_model');
        $this->ci->load->model('business/checkins_model');
        $this->ci->load->model('business/reviews_model');
        $this->ci->load->model('business/media_model');

        $this->ci->load->model('members/bookmarks_model');
        $this->ci->load->model('members/likes_model');
        //load helper
        $this->ci->load->helper(array('form', 'url'));

    }

	function deleteListing($id){		
        $listing = $this->ci->business_model->detail($id);
        if($listing && in_array($listing->status, array(1,0))){
            Admin_listing::_doListing($id, 'delete');
            $this->ci->business_model->deleteBusiness($id);
            return true;
        }
        return false;
	}

    function restoreListing($id){
        $listing = $this->ci->business_model->detail($id);

        if($listing && in_array($listing->status, array(2))){
            Admin_listing::_doListing($id, 'restore');
            $this->ci->business_model->restoreBusiness($id);
            return true;
        }
        return false;
    }

    function removeListingFromTrash($id){
        $listing = $this->ci->business_model->detail($id);
        if($listing && in_array($listing->status, array(2))){
             Admin_listing::_doListing($id, 'remove');
            $this->ci->business_model->RemoveBusiness($id);
            return true;
        }
        return false;
    }

    // action : delete, restore, remove from trash
    function _doListing($id, $action = 'delete'){
        // check in
        $this->ci->checkins_model->excuteItem('business_id', $id, $action);

        // review
        $this->ci->reviews_model->excuteItem($id, $action);

        // bookmark 
        $this->ci->bookmarks_model->excuteItem($id, $action);

        // like 
        $this->ci->likes_model->excuteItem($id, $action);

        // media 
        $this->ci->media_model->excuteItem($id, $action);
    }
}