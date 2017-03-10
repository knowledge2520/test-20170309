<?php

class Contact_model extends CI_Model {

    public function send_contact_email($data) {
        $mandrill_api = $this->config->item('mandrill_api');
        if (empty($mandrill_api)) {
            return false;
        }
        $this->load->library('mandrill', $mandrill_api);
        $this->load->helper('site');

        $admin_mail = $this->config->item('email_from_email');
        $admin_name = $this->config->item('mail_app_title');
        $params = array(
            "html" => "<p>Full Name: ". $data['name'] . ".</p>
                        <p>Phone Number: ". $data['phone'] . ".</p>		
                        <p>Email Address: ". $data['email'] . ".</p>
                        <p>tMessage: ". $data['contact_message'] . ".</p>
                        ",
            "text" => null,
            "from_email" => $admin_mail,
            "from_name" => $admin_name,
            "subject" => "[Pet Widget] Contact",
            "to" => array(array("email" => $this->config->item('email_contact_website'))), //$user_detail->email
            "track_opens" => true,
            "track_clicks" => true,
            "auto_text" => true
        );

        $send_status = $this->mandrill->messages->send($params, true);
        if (!empty($send_status) && $send_status[0]['status'] == 'sent') {
            return true;
        }
        return false;
    }

}
