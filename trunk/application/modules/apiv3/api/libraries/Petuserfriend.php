<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Petuserfriend {

    private $ci;

    function __construct($params = array()) {

        $this->ci = & get_instance();

        $this->ci->load->model('member_model');
        $this->ci->load->model('usercontact_model');
    }

    /**
     * @param $blockUserId
     * @param $member
     * @description: User block another contact
     */
    function blockUser( $blockUserId, $member ) {
        /*
         * We don't care 2 users are friend or not. We just create / update the records in user_contact table to 5
         * We should check there are any records between 2 users in user_contact table.
         * If there are not any records, we create 2 records as
         *      - Logged in user will be stored in user_id field, status 5
         *      - Blocking user will be stored in registed field, status 0
         * Otherwise we update 2 records as
         *      - Logged in user will be stored in user_id field, status 5
         *      - Blocking user will be stored in registed field, status 0
         */

        $userContact = $this->ci->usercontact_model->getUserContact( $member->id, $blockUserId );

        $contact    = $this->ci->member_model->getMemberByMemberID($blockUserId, false, false, false, false, false);

        if( $userContact ) {

            $this->ci->usercontact_model->updateUserContact(
                array("user_id" => $member->id, "registed" => $contact->id),
                array("status" => CONTACT_BLOCK)
            );
            $this->ci->usercontact_model->updateUserContact(
                array("user_id" => $contact->id, "registed" => $member->id),
                array("status" => CONTACT_DEFAULT)
            );

        } else {

            $this->ci->db->insert_batch("user_contact", array(
                array(
                    "user_id"   => $member->id,
                    "registed"  => $contact->id,
                    "email"     => $contact->email,
                    "first_name"=> $contact->first_name,
                    "last_name" => $contact->last_name,
                    "phone"     => $contact->phone,
                    "social_type" => $contact->facebook_id ? 1 : 0,
                    "status"    => CONTACT_BLOCK
                ),
                array(
                    "user_id"   => $contact->id,
                    "registed"  => $member->id,
                    "email"     => $member->email,
                    "first_name"=> $member->first_name,
                    "last_name" => $member->last_name,
                    "phone"     => $member->phone,
                    "social_type" => $member->facebook_id ? 1 : 0,
                    "status"    => CONTACT_DEFAULT
                )
            ));
        }
    }

    /**
     * @param $blockUserId
     * @param $member
     * @description: User unblock the contact
     */
    function unblockUser( $blockUserId, $member ) {
        $userId = (int)$member->id;
        $blockUserId = (int)$blockUserId;
        $this->ci->usercontact_model->updateUserContact(
            "(user_id = $userId AND registed = $blockUserId) OR (registed = $userId AND user_id = $blockUserId)",
            array("status" => CONTACT_DEFAULT)
        );
    }

    /**
     * @param $member
     * @param int $start
     * @param int $limit
     * @return array
     * @description: Get user block list
     * @tag: member controller > userBlockList_post
     */
    public function getUserContactItems( $member, $status = 0, $start = 0, $limit = API_NUM_RECORD_PER_PAGE ) {

        $items = $this->ci->usercontact_model->getUserContactList( $member->id, $status, 'item', $start, $limit )->getUserContactListTransform();
        $total = $this->ci->usercontact_model->getUserContactList( $member->id, $status, 'total' );

        $response = array();

        $response[ITEMS]        = $items;
        $response[TOTAL_ITEM]   = $total;
        $response[TOTAL_PAGE]   = $total > 0 ? ceil(intval($total) / $limit) : 0;
        $response['limit']      = intval($limit);

        return $response;
    }
}