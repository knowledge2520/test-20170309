<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class UtilHelper {
	static function generateRandomString($length = 6) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	/*
	MailChimp send subscribed
	param : email, firstName, lastName
	*/
	static function syncMailchimp($email,$firstName,$lastName) {
	    $memberId = md5(strtolower($email));
	    $url = 'https://us11.api.mailchimp.com/3.0/lists/' . MAILCHIMP_NEWLETTERS_LIST_ID . '/members/' . $memberId;
	    $json = json_encode([
	        'email_address' => $email,
	        'status'        => 'subscribed', // "subscribed","unsubscribed","cleaned","pending"
	        'merge_fields'  => [
	            'FNAME'     => $firstName,
	            'LNAME'     => $lastName
	        ]
	    ]);
	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . MAILCHIMP_API_KEY);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                                                                 

	    $result = curl_exec($ch);
	    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
	    return $httpCode;
	}
}	