<?php
/**
 * 微信支付 WxPaySdk SPL autoloader.
 * @param string $classname The name of the class to load
 * 2017-06-17
 */

use think\Db;

$wxpay_dirname=dirname(__FILE__).DIRECTORY_SEPARATOR.'WxPay'.DIRECTORY_SEPARATOR;
define('WXPAY_ROOT',$wxpay_dirname);
$app_id='';
// $app_id=Db::table('b_config')->where('name','wx_appid')->where('status',1)->value('value');
$mch_id=Db::table('b_config')->where('name','wx_mch_id')->where('status',1)->value('value');
$app_key=Db::table('b_config')->where('name','wx_key')->where('status',1)->value('value');
$app_secret=Db::table('b_config')->where('name','wx_secret')->where('status',1)->value('value');

define('WXPAY_APP_ID',$app_id);
define('WXPAY_MCH_ID',$mch_id);
define('WXPAY_APP_KEY',$app_key);
define('WXPAY_APP_SECRET',$app_secret);


require_once $wxpay_dirname . "WxPay.Api.php";
require_once $wxpay_dirname . "WxPay.Config.php";
require_once $wxpay_dirname . "WxPay.Data.php";
require_once $wxpay_dirname . "WxPay.Exception.php";
require_once $wxpay_dirname . "WxPay.Notify.php";
require_once $wxpay_dirname . "WxPay.JsApiPay.php";