<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pet_post_updated_model extends MY_Model {

    /**
     * Hooks
     *
     * @var object
     * */
    protected $_hooks;
    protected $table_name = "user_post_updated";
    protected $key = "id";
    protected $soft_deletes = FALSE;
    protected $date_format = "int";
    protected $log_user = FALSE;
    protected $set_created = true;
    protected $set_modified = true;
    protected $created_field = "created_date";
    protected $modified_field = "updated_date";

    /*
      Customize the operations of the model without recreating the insert, update,
      etc methods by adding the method names to act as callbacks here.
     */
    protected $before_insert = array();
    protected $after_insert = array();
    protected $before_update = array();
    protected $after_update = array();
    protected $before_find = array();
    protected $after_find = array();
    protected $before_delete = array();
    protected $after_delete = array();

    /*
      For performance reasons, you may require your model to NOT return the
      id of the last inserted row as it is a bit of a slow method. This is
      primarily helpful when running big loops over data.
     */
    protected $return_insert_id = TRUE;
    // The default type of element data is returned as.
    protected $return_type = "object";
    // Items that are always removed from data arrays prior to
    // any inserts or updates.
    protected $protected_attributes = array();

    /*
      You may need to move certain rules (like required) into the
      $insert_validation_rules array and out of the standard validation array.
      That way it is only required during inserts, not updates which may only
      be updating a portion of the data.
     */
    protected $validation_rules = array();
    protected $insert_validation_rules = array();
    protected $skip_validation = FALSE;

    /**
     * Where
     *
     * @var array
     * */
    public $_where = array();

    /**
     * Limit
     *
     * @var string
     * */
    public $_limit = NULL;

    /**
     * Offset
     *
     * @var string
     * */
    public $_offset = NULL;

    /**
     * Order By
     *
     * @var string
     * */
    public $_order_by = NULL;

    /**
     * Order
     *
     * @var string
     * */
    public $_order = NULL;

    public function detail($id) {

        if (!$id) {
            return false;
        }
        $this->select('t.*, u.id as user_id, u.email, u.first_name, u.last_name, CONCAT_WS(" ", u.first_name, u.last_name) as user_name', false);
        $this->from('user_post_updated t');
        $this->join('users u', 'u.id = t.user_id', 'left');
        $this->group_by('t.id');
        return $this->find_by('t.id', $id);
    }

    function getPhoto($id){
        $this->db->where('post_update_id', $id);
        $result = $this->db->get('user_media');

        return $result->num_rows() > 0 ? $result->row() : false;
    }

    function deletePostUpdate($id){
        if(!$id){
            return false;
        }
        //delete pettalk info comment
        $this->load->model('comments_model');
        $this->comments_model->deleteCommentBy('post_id', $id);

        //delete pettalk info like
        $this->load->model('members/likes_model');
        $this->likes_model->deleteUserLikeBy('post_id', $id);

        //delete pettalk info media
        $this->load->model('business/media_model');
        $this->media_model->deleteMediaBy('post_update_id', $id);

        //delete pettalk info
        $this->db->delete("user_post_updated", array("id" => $id));
    }
}
