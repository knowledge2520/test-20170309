<?php
require_once 'MyParse/autoload.php';


//	   const APP_ID			= "ByRC9O7hmV3GOz0feuEcf0PtYLg3Z7lUVp0lmjzC";
//	   const REST_API_ID 	= "wO0PM7FBjEfEcYk3ZHfrycvYgEc7y49lxM2zOhuA";
//	   const MASTER_ID 		= "JA4qVGUDh1Cu4kPtInXHl9FqVRciYJaVvQI4ebGU";



	
use Parse\ParseClient;
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseInstallation;
use Parse\ParsePush;
class My_parse 
{
	private $_appid = '';
	private $_masterkey = '';
	private $_restkey = '';
	
	public $obj;
	public $table;
	public $MASTER_ID;


	public function __construct($parse_class=null) 
	{
        $ci =& get_instance();

        $ci->load->helper('site');
        $parse_configs = getCRMConfigs(array('parse_appid','parse_masterkey','parse_restkey','parse_parseurl'));
        
        $this->_appid 		= $parse_configs['parse_appid'];
        $this->_masterkey	= $parse_configs['parse_masterkey'];
        $this->_restkey 	= $parse_configs['parse_restkey'];
        
        $appid 		        = $this->_appid;//$ci->config->item('parse_appid');
        $this->MASTER_ID	= $this->_masterkey;//$ci->config->item('parse_masterkey');
        $restkey 	        = $this->_restkey;//$ci->config->item('parse_restkey');

		//var_dump($appid);var_dump($this->MASTER_ID);die(var_dump( $restkey ));
		ParseClient::initialize($appid, $restkey, $this->MASTER_ID);

		$this->init($parse_class);
		/*$this->MASTER_ID = 'rx2NPJMbJyQA26uV2xyC9SIqaBrACUHqCFGl8eeT';
		$this->obj = new ParseObject($parse_class);
		$this->table = $parse_class;*/
	}

	public function init($parse_class=null) {
		//$this->MASTER_ID = 'Txmgi7bSasHY56Ml3BtGHMc17wXMyityrzvWQKH8';
		$this->obj = new ParseObject($parse_class);
		$this->table = $parse_class;
	}
	
	public function setTableObject($table)
	{
		$this->init($table);

		$o = new Parse($table);
		return $o;
	}
	public function ParseUser()
	{
		return new ParseUser();
	}
	public function ParseInstallation()
	{
		return new ParseInstallation();
	}

    public function ParsePush() {
        return new ParsePush();
    }

    public function ParseObject($class_name, $key = null) {
        if($key) {
            return new ParseObject($class_name, $key);
        }
        return new ParseObject($class_name);
    }

    public function ParseQuery($class_name) {
        return new ParseQuery($class_name);
    }

	public function sendPushNotification($data)
	{
		$push = new ParsePush();
		return $push->send($data,$this->MASTER_ID);
	}
	public function insert($data)
	{
	
		$this->obj = new ParseObject($this->table);// insert into parse class
		
		foreach($data as $key => $value)
		{
			if(is_array($value)) {
                $this->obj->setArray($key, $value);
            } else {
                $this->obj->set("$key",$value);
            }
		}
		
		try
		{
			$this->obj->save();
			return $this->obj->getObjectId();
		}
		catch(ParseException $e)
		{

			echo 'Failed to create new object, with error message: '.$e->getMessage();
			
		}
	
	}
	
	public function findByAttributes($data, $table)
	{
		
		$this->obj = new ParseQuery($table);

		if(!empty($data))
		{
			foreach ($data as $key=>$value)
			{
				$this->obj->equalTo($key,$value);
			}
		}
		
		  $result = $this->obj->find($this->MASTER_ID);
		
		  if(!empty($result))
		  	return $result[0];
		  else 
		  	return false;
	}
	public function update($id,$data)
	{
		
		$this->obj = new ParseQuery($this->table);
		try {
		$this->obj = $this->obj->get($id);
			foreach($data as $key => $value)
			{
				$this->obj->set("$key",$value);
		
			}
		
		try
		{
			$this->obj->save();
			return $this->obj->getObjectId();
		}
		catch(ParseException $e)
		{

			echo 'Failed to update object, with error message: '.$e->getMessage();
		}
	
		
		} catch (ParseException $ex) {
		
		}
	}
	
	public function read($id)
	{
		try
		{
			$this->obj=new ParseQuery($this->table);
			$this->obj=$this->obj->get($id);
			return $this->obj;	
		}
		catch(ParseException $e)
		{
			echo 'Failed to read object, with error message: '.$e->getMessage();
		}
			
	}
	
	public function delete($id)
	{
		try
		{
			$this->obj=new ParseQuery($this->table);
			$this->obj=$this->obj->get($id);
			$this->obj->destroy($this->MASTER_ID);
			
			return array('status'=>true);
		}
		catch(ParseException $e)
		{
			echo 'Failed to update object, with error message: '.$e->getMessage();
		}
		
	}
}

?>