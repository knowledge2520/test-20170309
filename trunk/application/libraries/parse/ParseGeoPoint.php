<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

include_once 'ParseRestClient.php';

class ParseGeoPoint extends ParseRestClient{

	public $lat;
	public $long;
	public $location;
	
	public function __construct($lat,$long){
		$this->lat = $lat;
		$this->long = $long;
		$this->location = $this->dataType('geopoint', array($this->lat, $this->long));
	}

	public function __toString(){		
		return json_encode($this->location);

	}
	
	public function get(){		
		return json_encode($this->location);

	}	

}

