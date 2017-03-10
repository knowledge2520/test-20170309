<?php

function get_action_type($type_name) {
    $ci = &get_instance();

    if (!empty($type_name)) {
        $row = $ci->db->get_where('user_activity_types', array('name' => $type_name))->first_row();
        if ($row) {
            return $row;
        }
    }
    return false;
}

function count_unread_notification($user_id) {
    $ci = &get_instance();

    if (!empty($user_id)) {
        $ci->db->where(array('user_id' => $user_id, 'is_read' => 0));
        $ci->db->from('user_notification');
        return $ci->db->count_all_results();
    }
    return false;
}
