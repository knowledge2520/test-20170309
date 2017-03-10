<?php if (! defined('BASEPATH')) exit('No direct script access allowed'); 

include_once 'ParseRestClient.php';

class ParseUser extends ParseRestClient{

	public $authData;   //?

    //?
	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

    //TODO make more general?
	public function signup($username='',$password='', $email='') {
		if($username != '' && $password != '' && $email != '') {
			$request = $this->request(array(
				'method' => 'POST',
	    		'requestUrl' => 'users',
				'data' => array('username' => $username, 'password' => $password,
                    'email' => $email)
			));
			
	    	return $request;
			
		} else{
			$this->throwError('username and password fields are required for the signup method');
		}
	}

	public function login($username, $password) {
		if(!empty($username) || !empty($password)) {
			$request = $this->request(array(
				'method' => 'GET',
	    		'requestUrl' => 'login',
		    	'data' => array(
		    		'username' => $username,
		    		'password' => $password
		    	)
			));
			
	    	return $request;
	
		} else{
			$this->throwError('username and password field are required for the login method');
		}

	}

	public function requestPasswordReset($email) {
		if(!empty($email)) {
			$this->email - $email;
			$request = $this->request(array(
			'method' => 'POST',
			'requestUrl' => 'requestPasswordReset',
			'data' => array('email' => $email)
			));

			return $request;
		} else{
			$this->throwError('email is required for the requestPasswordReset method');
		}

    }


	public function get($objectId) {
		if($objectId != '') {
			$request = $this->request(array(
				'method' => 'GET',
	    		'requestUrl' => 'users/'.$objectId
			));
			
	    	return $request;			
			
		} else{
			$this->throwError('objectId is required for the get method');
		}
	}

    public function validateSessionToken($sessionToken) {
        if ($sessionToken != '') {
            try {
                $request = $this->request(array(
                    'method' => 'GET',
                    'requestUrl' => 'users/me',
                    'sessionToken' => $sessionToken
                ));
            } catch (Exception $e) {
                return FALSE;
            }

            return TRUE;

        } else {
            return FALSE;
        }
    }

	//TODO: should make the parseUser contruct accept the objectId and update and delete would only require the sessionToken
	public function update($objectId,$sessionToken) {
		if(!empty($objectId) || !empty($sessionToken)) {
			$request = $this->request(array(
				'method' => 'PUT',
				'requestUrl' => 'users/'.$objectId,
	    		'sessionToken' => $sessionToken,
				'data' => $this->data
			));
			
	    	return $request;			
		} else{
			$this->throwError('objectId and sessionToken are required for the update method');
		}
	}

	public function delete($objectId,$sessionToken) {
		if(!empty($objectId) || !empty($sessionToken)) {
			$request = $this->request(array(
				'method' => 'DELETE',
				'requestUrl' => 'users/'.$objectId,
	    		'sessionToken' => $sessionToken
			));
			
	    	return $request;			
		}
		else{
			$this->throwError('objectId and sessionToken are required for the delete method');
		}

	}
	
	public function addAuthData($authArray) {
		if(is_array($authArray)) {			
			$this->authData[$authArray['type']] = $authArray['authData'];
		}
		else{
			$this->throwError('authArray must be an array containing a type key and a authData key in the addAuthData method');
		}
	}

	public function linkAccounts($objectId,$sessionToken) {
		if(!empty($objectId) || !empty($sessionToken)) {
			$request = $this->request(array(
				'method' => 'PUT',
				'requestUrl' => 'users/'.$objectId,
				'sessionToken' => $sessionToken,
				'data' => array(
					'authData' => $this->authData
				)
			));

			return $request;
		}
		else{
			$this->throwError('objectId and sessionToken are required for the linkAccounts method');
		}		

	}

	public function unlinkAccount($objectId,$sessionToken,$type) {
		$linkedAccount[$type] = null;

		if(!empty($objectId) || !empty($sessionToken)) {
			$request = $this->request(array(
				'method' => 'PUT',
				'requestUrl' => 'users/'.$objectId,
				'sessionToken' => $sessionToken,
				'data' => array(
					'authData' => $linkedAccount
				)
			));

			return $request;
		}
		else{
			$this->throwError('objectId and sessionToken are required for the linkAccounts method');
		}		

	}

	
}

