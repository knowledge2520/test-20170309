<?php
class Permission{
    public static function check_permission($module_name){
        $ci = &get_instance();
        $ci->load->model(array('users/permissions_model', 'users/groups_model'));
         
        $group = $ci->groups_model->get_group_user($ci->session->userdata('user_id'));
        $permission = $ci->permissions_model->get_permissions_group($group->group_id);
        
        if (is_array($permission) || is_object($permission))
        {
            foreach ($permission as $k=>$p){
                if($module_name == $k){
                    return TRUE;
                }
            }
        
        }
        return FALSE;
    }

    public static function has_permission(){
        $ci = &get_instance();
        $ci->load->model(array('users/permissions_model', 'users/groups_model'));
         
        $group = $ci->groups_model->get_group_user($ci->session->userdata('user_id'));

        if(!$group){
            return false;
        }
        $permission = $ci->permissions_model->get_permissions_group($group->group_id);        
        if (is_array($permission) || is_object($permission))
        {
            return TRUE;        
        }
        return FALSE;
    }
}

