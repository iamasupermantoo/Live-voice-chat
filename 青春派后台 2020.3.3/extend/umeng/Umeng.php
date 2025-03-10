<?php
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidCustomizedcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSCustomizedcast.php');

class Umeng {
	protected $appkey           = NULL; 
	protected $masterSecret     = NULL;
	protected $timestamp        = NULL;
	protected $validation_token = NULL;

	function __construct($key, $secret) {
		$this->appkey = $key;
		$this->masterSecret = $secret;
		$this->timestamp = strval(time());
		$this->validation_token = md5(strtolower($this->appkey) . strtolower($this->masterSecret) . strtolower($this->timestamp));
	}
	//广播
	function sendAndroidBroadcast($title,$note,$content) {
		$brocast = new AndroidBroadcast();
		$brocast->setPredefinedKeyValue("appkey",           $this->appkey);
		$brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
		$brocast->setPredefinedKeyValue("validation_token", $this->validation_token);
		$brocast->setPredefinedKeyValue("ticker",           "Android broadcast ticker");
		$brocast->setPredefinedKeyValue("title",            $title);
		$brocast->setPredefinedKeyValue("text",             $note);
		$brocast->setPredefinedKeyValue("after_open",       "go_app");
		// Set 'production_mode' to 'false' if it's a test device. 
		// For how to register a test device, please see the developer doc.
		$brocast->setPredefinedKeyValue("production_mode", "true");
		// [optional]Set extra fields
		$brocast->setExtraField("data", $content);
		$res=$brocast->send();
		return $res;
	}

	//单播
	function sendAndroidUnicast($title,$note,$content,$device_token) {
		$unicast = new AndroidUnicast();
		$unicast->setPredefinedKeyValue("appkey",           $this->appkey);
		$unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
		$unicast->setPredefinedKeyValue("validation_token", $this->validation_token);
		// Set your device tokens here
		$unicast->setPredefinedKeyValue("device_tokens",    $device_token); 
		$unicast->setPredefinedKeyValue("ticker",           "Android unicast ticker");
		$unicast->setPredefinedKeyValue("title",            $title);
		$unicast->setPredefinedKeyValue("text",             $note);
		$unicast->setPredefinedKeyValue("after_open",       "go_app");
		// Set 'production_mode' to 'false' if it's a test device. 
		// For how to register a test device, please see the developer doc.
		$unicast->setPredefinedKeyValue("production_mode", "true");
		// Set extra fields
		$unicast->setExtraField("data", $content);
		$res=$unicast->send();
      	return $res;
	}
	//文件播报
	function sendAndroidFilecast() {
		$filecast = new AndroidFilecast();
		$filecast->setPredefinedKeyValue("appkey",           $this->appkey);
		$filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);
		$filecast->setPredefinedKeyValue("validation_token", $this->validation_token);
		$filecast->setPredefinedKeyValue("ticker",           "Android filecast ticker");
		$filecast->setPredefinedKeyValue("title",            "Android filecast title");
		$filecast->setPredefinedKeyValue("text",             "Android filecast text");
		$filecast->setPredefinedKeyValue("after_open",       "go_app");  //go to app
		// print("Uploading file contents, please wait...\r\n");
		// Upload your device tokens, and use '\n' to split them if there are multiple tokens
		$filecast->uploadContents("aa"."\n"."bb");
		// print("Sending filecast notification, please wait...\r\n");
		$res=$filecast->send();
		return $res;
	}
	//群播
	function sendAndroidGroupcast() {
		/* 
	 	 *  Construct the filter condition:
	 	 *  "where": 
	 	 *	{
	 	 *		"and": 
	 	 *		[
  	 	 *			{"tag":"test"},
  	 	 *			{"tag":"Test"}
	 	 *		]
	 	 *	}
	 	 */
		// $filter = 	array(
		// 				"where" => 	array(
		// 					    		"and" 	=>  array(
		// 					    						array(
		// 				     								"tag" => "test"
		// 												),
		// 					     						array(
		// 				     								"tag" => "Test"
		// 					     						)
		// 					     		 			)
		// 					   		)
		// 		  	);
		$filter=array(
			'where'=>[
				'and'=>[
						array("tag" => "test"),
						array("tag" => "Test"),
					],
			],
		);
		$groupcast = new AndroidGroupcast();
		$groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
		$groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
		$groupcast->setPredefinedKeyValue("validation_token", $this->validation_token);
		// Set the filter condition
		$groupcast->setPredefinedKeyValue("filter",           $filter);
		$groupcast->setPredefinedKeyValue("ticker",           "Android groupcast ticker");
		$groupcast->setPredefinedKeyValue("title",            "Android groupcast title");
		$groupcast->setPredefinedKeyValue("text",             "Android groupcast text");
		$groupcast->setPredefinedKeyValue("after_open",       "go_app");
		// Set 'production_mode' to 'false' if it's a test device. 
		// For how to register a test device, please see the developer doc.
		$groupcast->setPredefinedKeyValue("production_mode", "true");
		$res=$groupcast->send();
		return $res;
		
	}
	//自定义播报
	function sendAndroidCustomizedcast() {
		$customizedcast = new AndroidCustomizedcast();
		$customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
		$customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
		$customizedcast->setPredefinedKeyValue("validation_token", $this->validation_token);
		// Set your alias here, and use comma to split them if there are multiple alias.
		// And if you have many alias, you can also upload a file containing these alias, then 
		// use file_id to send customized notification.
		$customizedcast->setPredefinedKeyValue("alias",            "xx");
		// Set your alias_type here
		$customizedcast->setPredefinedKeyValue("alias_type",       "xx");
		$customizedcast->setPredefinedKeyValue("ticker",           "Android customizedcast ticker");
		$customizedcast->setPredefinedKeyValue("title",            "Android customizedcast title");
		$customizedcast->setPredefinedKeyValue("text",             "Android customizedcast text");
		$customizedcast->setPredefinedKeyValue("after_open",       "go_app");
		$res=$customizedcast->send();
		return $res;
	}
	//广播
	function sendIOSBroadcast($title,$content) {
		$brocast = new IOSBroadcast();
		$brocast->setPredefinedKeyValue("appkey",           $this->appkey);
		$brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
		$brocast->setPredefinedKeyValue("validation_token", $this->validation_token);
		$brocast->setPredefinedKeyValue("alert", 			$title);
		//$brocast->setPredefinedKeyValue("badge", 			0);
		$brocast->setPredefinedKeyValue("sound", 			"chime");
		// Set 'production_mode' to 'true' if your app is under production mode
		$brocast->setPredefinedKeyValue("production_mode", "false");
		// Set customized fields
		$brocast->setCustomizedField("data", 				$content);
		$res=$brocast->send();
		return $res;
	}
	//单播
	function sendIOSUnicast($title,$content,$device_token) {
		$unicast = new IOSUnicast();
		$unicast->setPredefinedKeyValue("appkey",           $this->appkey);
		$unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
		$unicast->setPredefinedKeyValue("validation_token", $this->validation_token);
		// Set your device tokens here
		$unicast->setPredefinedKeyValue("device_tokens",    $device_token); 
		$unicast->setPredefinedKeyValue("alert", 			$title);
		$unicast->setPredefinedKeyValue("badge", 			0);
		$unicast->setPredefinedKeyValue("sound", 			"chime");
		// Set 'production_mode' to 'true' if your app is under production mode
		$unicast->setPredefinedKeyValue("production_mode", "false");
		// Set customized fields
		$unicast->setCustomizedField("data", 				$content);
		$res=$unicast->send();
		return $res;		
	}
	// 文件播报
	function sendIOSFilecast() {
		$filecast = new IOSFilecast();
		$filecast->setPredefinedKeyValue("appkey",           $this->appkey);
		$filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);
		$filecast->setPredefinedKeyValue("validation_token", $this->validation_token);
		$filecast->setPredefinedKeyValue("alert", "IOS 文件播测试");
		$filecast->setPredefinedKeyValue("badge", 0);
		$filecast->setPredefinedKeyValue("sound", "chime");
		// Set 'production_mode' to 'true' if your app is under production mode
		$filecast->setPredefinedKeyValue("production_mode", "false");
		// Upload your device tokens, and use '\n' to split them if there are multiple tokens
		$filecast->uploadContents("aa"."\n"."bb");
		$res=$filecast->send();
		return $res;
	}
	//群播
	function sendIOSGroupcast() {
			/* 
		 	 *  Construct the filter condition:
		 	 *  "where": 
		 	 *	{
    	 	 *		"and": 
    	 	 *		[
      	 	 *			{"tag":"iostest"}
    	 	 *		]
		 	 *	}
		 	 */
			$filter =array(
						"where" =>array(
						    		"and"=>array(
						    					array(
					     							"tag" => "iostest"
												)
						     		 		)
						   		)
				  	);
					  
			$groupcast = new IOSGroupcast();
			$groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$groupcast->setPredefinedKeyValue("validation_token", $this->validation_token);
			// Set the filter condition
			$groupcast->setPredefinedKeyValue("filter",           $filter);
			$groupcast->setPredefinedKeyValue("alert", "IOS 组播测试");
			$groupcast->setPredefinedKeyValue("badge", 0);
			$groupcast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			$groupcast->setPredefinedKeyValue("production_mode", "false");
			$res=$groupcast->send();
			return $res;
		
	}
	//自定义广播
	function sendIOSCustomizedcast() {
		$customizedcast = new IOSCustomizedcast();
		$customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
		$customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
		$customizedcast->setPredefinedKeyValue("validation_token", $this->validation_token);
		// Set your alias here, and use comma to split them if there are multiple alias.
		// And if you have many alias, you can also upload a file containing these alias, then 
		// use file_id to send customized notification.
		$customizedcast->setPredefinedKeyValue("alias", "xx");
		$customizedcast->setPredefinedKeyValue("alert", "IOS 个性化测试");
		$customizedcast->setPredefinedKeyValue("badge", 0);
		$customizedcast->setPredefinedKeyValue("sound", "chime");
		// Set 'production_mode' to 'true' if your app is under production mode
		$customizedcast->setPredefinedKeyValue("production_mode", "false");
		$res=$customizedcast->send();
		return $res;
		
	}
}

// Set your appkey and master secret here
//$demo = new Demo("your appkey", "your app master secret");
//$demo->sendAndroidUnicast();		//单播
/* these methods are all available, just fill in some fields and do the test
 * $demo->sendAndroidBroadcast();	//广播
 * $demo->sendAndroidFilecast();	//文件广播
 * $demo->sendAndroidGroupcast();	//群播
 * $demo->sendAndroidCustomizedcast();	//自定义广播
 *
 * $demo->sendIOSBroadcast();		//广播
 * $demo->sendIOSUnicast();			//单播
 * $demo->sendIOSFilecast();		//文件广播
 * $demo->sendIOSGroupcast();		//群播
 * $demo->sendIOSCustomizedcast();	//自定义广播
 */