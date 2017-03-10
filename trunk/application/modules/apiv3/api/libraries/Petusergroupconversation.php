<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Petusergroupconversation {

    private $ci;

    protected $CAN_MEMBER_EDIT_GROUP = true;

    function __construct($params = array()) {

        $this->ci = & get_instance();

        $this->ci->load->model('groupconversation_model');
    }

    function saveNew( $member, $groupName ) {
        $this->ci->groupconversation_model->saveNew(array(
            "name"          => $groupName,
            "created_by"    => $member->id,
            "created_date"  => now(),
        ));
    }

    function saveEdit( $member, $groupId, $groupName = "" ) {
        // Step 1: Check the looged in user is in group
        $userGroup = $this->ci->groupconversation_model->findGroup(array(
            "userId"    => $member->id,
            "groupId"   => $groupId
        ));

        $canEdit = false;

        if( $userGroup ) {
            // Check if logged in user is group owner
            if( $userGroup->created_by == $member->id ) {
                $canEdit = true;
            } elseif( $this->CAN_MEMBER_EDIT_GROUP ) {
                $canEdit = true;
            }
        }

        if( $canEdit ) {
            $params = array();

            if( !empty($groupName)) {
                $params["name"] = $groupName;
            }
            $this->ci->groupconversation_model->save($params, array("id" => $groupId));
        }
    }
}