<?php
require APPPATH.'../vendor/autoload.php';
require APPPATH. '../vendor/swiftmailer/swiftmailer/lib/swift_required.php';

function send_active_email($user_id) {
    $ci = &get_instance();

    $mandrill_api = $ci->config->item('mandrill_api');
    if (empty($mandrill_api)) {
        return false;
    }
    if (!$user_id) {
        return false;
    }

    $ci->load->helper('site');
    $ci->load->model('member_model');
   
    $user_detail = $ci->member_model->getMemberByMemberID($user_id);
    if ($user_detail) {
        if (!empty($user_detail->activation_code) || $user_detail->active == 0) {           
            $admin_mail = $ci->config->item('smtp_user');
            $admin_name = $ci->config->item('mail_app_title');
            $API_KEY = getAPIKey();
            $message = site_url($ci->lang->lang() . '/api/member/activeMember/API-KEY/' . $API_KEY . '/activation_code/' . $user_detail->activation_code);
            // $images = array(
            //     array(
            //         'type' => 'image/png',
            //         'name' => 'logo',
            //         'content' => base64_encode(file_get_contents(siteURL() . 'themes/public/images/logo/pet-widget-complete-logo-mini.png'))
            //     )
            // );
            $data_view = array(
                'verify_link' => $message,
                'first_name' => $user_detail->first_name,
                'logo' => '<a href="' . siteURL() . '"><img src="cid:logo" width="100" /></a>',
            );
            $html = $ci->load->view('edm/member_verify_account', $data_view, TRUE);
            $subject = "[Pet Widget] Please confirm your email address";
            $result = sendSwiftMailer($admin_mail, $admin_name, $user_detail->email, $subject, $html);
            
            if ($result) {
                return true;
            }
        } else {
            //user actived
            return false;
        }
    } else {
        return false;
    }
}

function send_forgot_email($user_id, $forgotten_password_code) {
    $ci = &get_instance();

    $mandrill_api = $ci->config->item('mandrill_api');

    if (empty($mandrill_api) || empty($forgotten_password_code)) {
        return false;
    }
    if (!$user_id) {
        return false;
    }

    $ci->load->library('mandrill', $mandrill_api);
    $ci->load->helper('site');
    $ci->load->model('member_model');

    $user_detail = $ci->member_model->getMemberByMemberID($user_id);

    if ($user_detail) {
        $admin_mail = $ci->config->item('smtp_user');
        $admin_name = $ci->config->item('mail_app_title');
        $message = site_url('../../auth/reset_password/' . $forgotten_password_code);
        // $images = array(
        //     array(
        //         'type' => 'image/png',
        //         'name' => 'logo',
        //         'content' => base64_encode(file_get_contents(siteURL() . 'themes/public/images/logo/pet-widget-complete-logo-mini.png'))
        //         //'content' => base64_encode(file_get_contents(siteURL() . 'themes/public/images/logo/App_Icon@2x.png'))
        //     )
        // );
        $data_view = array(
            'reset_link' => $message,
            'logo' => '<a href="' . siteURL() . '"><img src="cid:logo" width="100" /></a>',
        );
        $html = $ci->load->view('edm/member_forgot_password', $data_view, TRUE);
        $subject = "[Pet Widget] Password Reset";    
        $result = sendSwiftMailer($admin_mail, $admin_name, $user_detail->email, $subject, $html);
        if ($result) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function send_new_password_email($user_id, $new_password) {
    $ci = &get_instance();

    $mandrill_api = $ci->config->item('mandrill_api');

    if (empty($mandrill_api) || empty($new_password) || !$user_id) {
        return false;
    }

    $ci->load->library('mandrill', $mandrill_api);
    $ci->load->helper('site');
    $ci->load->model('member_model');

    $user_detail = $ci->member_model->getMemberByMemberID($user_id);

    if ($user_detail) {
        $admin_mail = $ci->config->item('smtp_user');
        $admin_name = $ci->config->item('mail_app_title');
        $message = site_url();
        // $images = array(
        //     array(
        //         'type' => 'image/png',
        //         'name' => 'logo',
        //         'content' => base64_encode(file_get_contents(siteURL() . 'themes/public/images/logo/pet-widget-complete-logo-mini.png'))
        //         //'content' => base64_encode(file_get_contents(siteURL() . 'themes/public/images/logo/App_Icon@2x.png'))
        //     )
        // );


        $data_view = array(
            'reset_link' => $message,
            'new_password' => $new_password,
            'logo' => '<a href="' . siteURL() . '"><img src="cid:logo" width="100" /></a>',
        );
        $html = $ci->load->view('edm/member_change_password', $data_view, TRUE);
        $subject = "[Pet Widget] Password Change Request";
        $result = sendSwiftMailer($admin_mail, $admin_name, $user_detail->email, $subject, $html);
        if ($result) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function sendGmail($from_email, $from_name, $to_emails, $subject, $content){
    $ci = &get_instance();
     $ci->load->library('email');
    $config['protocol']    = 'smtp';
    $config['smtp_host']    = 'ssl://smtp.googlemail.com';
    $config['smtp_port']    = '465';
    $config['smtp_timeout'] = '7';
    $config['smtp_user']    = 'dev.php.webs@gmail.com';
    $config['smtp_pass']    = 'zwkglukgenvgeytl';
    $config['charset']    = 'utf-8';
    $config['newline']    = "\r\n";
    $config['mailtype'] = 'html'; // or html
    $config['validation'] = TRUE; // bool whether to validate email or not      

    $ci->email->initialize($config);

    $ci->email->from($from_email, $from_name);
    $ci->email->to($to_emails); 

    $ci->email->subject($subject);
    $ci->email->message($content);  

    $result = $ci->email->send();
    return $result;
}

function sendSwiftMailer($from_email, $from_name, $to_emails, $subject, $content){
    $ci = &get_instance();
    // Create the Transport
    $transport = Swift_SmtpTransport::newInstance($ci->config->item('mailgun_host'), $ci->config->item('mailgun_port'))
      ->setUsername($ci->config->item('mailgun_user'))
      ->setPassword($ci->config->item('mailgun_pass'))
      ;

    // Create the Mailer using your created Transport
    $mailer = Swift_Mailer::newInstance($transport);

    // Create a message
    $message = Swift_Message::newInstance($subject)
      ->setFrom(array($from_email => $from_name))
      ->setTo(array($to_emails))
      ->setBody($content,'text/html')
      ;
    // Send the message
    $result = $mailer->send($message);
    return $result;
}