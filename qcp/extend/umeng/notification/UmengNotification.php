<?php

abstract class UmengNotification {
	// The host
	protected $host = "http://msg.umeng.com";

	// The upload path
	protected $uploadPath = "/upload";

	// The post path
	protected $postPath = "/api/send";

	/*
	 * $data is designed to construct the json string for POST request. Note:
	 * 1)The key/value pairs in comments are optional.  
	 * 2)The value for key 'payload' is set in the subclass(AndroidNotification or IOSNotification), as their payload structures are different.
	 */ 
	protected $data = array(
			"appkey"           => NULL,
			"timestamp"        => NULL,
			"validation_token" => NULL,
			"type"             => NULL,
			//"device_tokens"  => "xx",
			//"alias"          => "xx",
			//"file_id"        => "xx",
			//"filter"         => "xx",
			//"policy"         => array("start_time" => "xx", "expire_time" => "xx", "max_send_num" => "xx"),
			"production_mode"  => "true",
			//"feedback"       => "xx",
			//"description"    => "xx",
			//"thirdparty_id"  => "xx"
	);

	protected $DATA_KEYS    = array("appkey", "timestamp", "validation_token", "type", "device_tokens", "alias", "alias_type", "file_id", "filter", "production_mode",
								    "feedback", "description", "thirdparty_id");
	protected $POLICY_KEYS  = array("start_time", "expire_time", "max_send_num");

	function __construct() {

	}
	
	//return TRUE if it's complete, otherwise throw exception with details
	function isComplete() {
		$this->checkArrayValues($this->data);
		return TRUE;
	}

	private function checkArrayValues($arr) {
		foreach ($arr as $key => $value) {
			if (is_null($value))
				throw new Exception($key . " is NULL!");
			else if (is_array($value)) {
				$this->checkArrayValues($value);
			}
		}
	}

	// Set key/value for $data array, for the keys which can be set please see $DATA_KEYS, $PAYLOAD_KEYS, $BODY_KEYS, $POLICY_KEYS
	abstract function setPredefinedKeyValue($key, $value);

	//send the notification to umeng, return response data if SUCCESS , otherwise throw Exception with details.
	function send() {
		//check the fields to make sure that they are not NULL
		$this->isComplete();

		$ch = curl_init($this->host . $this->postPath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->data));
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);  
        curl_close($ch);
      	return $result;
        //print($result."\r\n");
        if ($httpCode == "0") {
        	 // Time out
        	throw new Exception("Curl error number:" . $curlErrNo . " , Curl error details:" . $curlErr . "\r\n");
        } else if ($httpCode != "200") {
        	// We did send the notifition out and got a non-200 response
        	throw new Exception("Http code:" . $httpCode .  " details:" . $result . "\r\n");
        } else {
        	return $result;
        }
	}
	
}