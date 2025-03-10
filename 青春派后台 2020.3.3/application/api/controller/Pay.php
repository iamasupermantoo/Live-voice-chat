<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\usersmanage\Users;
use app\admin\model\officialsmanage\Onepage;
use think\Db;
use think\Loader;
use wxPay\WxPay;

class Pay extends Api
{
    protected $noNeedLogin = ['AliPayNotify','wx_notify',"androidGoodsList"];
    protected $noNeedRight = ['*'];


    //安卓充值列表
    public function androidGoodsList($user_id=null) {
         // $user_id = $_GET['user_id'];
          
//echo $user_id;die;        
        if (!$user_id) $this->ApiReturn(0, '缺少参数');
        $ratio = $this->getConfig('pay_ratio'); //返额比例%
        $proportion = 10; //充值比例
        $arr['mizuan'] = Db::name('users')->where('id', $user_id)->value('mizuan');
        $goods = Db::name('goods')->field('id,price')->select();
        foreach ($goods as $k => &$v) {
            $v['mizuan']=$v['price'] * $proportion;
            $v['ratio']=$ratio;
            $v['give']=$v['mizuan'] * $ratio / 100;
        }
        $arr['goods'] = $goods;
        //dump($arr);die;
        $this->ApiReturn(1, '', $arr);
    }
    //IOS充值列表
    public function iosGoodsList() {
        $user_id = $this->user_id;
        if (!$user_id) $this->ApiReturn(0, '缺少参数');
        $ratio = $this->getConfig('pay_ratio'); //返额比例%
        $proportion = 7; //充值比例
        $arr['mizuan'] = Db::name('users')->where('id', $user_id)->value('mizuan');
        $arr['ratio'] = $ratio;
        $arr['ratio_img'] = $ratio ? $this->auth->setFilePath($this->getConfig('ratio_img')) : '';
        $arr['treaty'] = Db::name('one_page')->where('id',6)->value('url');
        $goods = Db::name('goods')->field('id,price')->select();
        foreach ($goods as $k => & $v) {
            $v['mizuan']=$v['price'] * $proportion;
            $v['priceid']='mizuan'.$v['price'];
            $v['give']=$v['price'] * 10 * $ratio / 100;
        }
        $arr['goods'] = $goods;
        $this->ApiReturn(1, '', $arr);
    }

    //支付    1支付宝2微信
    public function rechargePay() {
        $info = $this->request->request();
        $pay_type = $this->request->request('type') ? : 0;
        $info['user_id'] = $this->user_id;
        if (!$info['user_id'] || !$info['goods_id'] || !$pay_type) $this->ApiReturn(0, '缺少参数');
        $arr['user_id'] = $info['user_id'];
        $arr['order_no'] = getOrderNo();
        $price=DB::name('goods')->where('id',$info['goods_id'])->value('price');
        if(!in_array($pay_type, [1,2]) || !$price)  $this->ApiReturn(0,'参数错误1,',$info);
        $ratio = $this->getConfig('pay_ratio'); //返额比例%
        $arr['price'] = $price;
        $arr['mizuan'] = ($price * 10) * (100+$ratio) /100;
        $arr['pay_type'] = $pay_type;
        $arr['remark'] = '';
        $arr['addtime'] = time();
        $res = DB::name('order')->insertGetId($arr);
        if ($res) {
            if($pay_type == 1){
                $this->alipayHand($arr['order_no'], $arr['price']);
            }elseif($pay_type == 2){
                $this->wxpayHand($arr['order_no'], $arr['price']);
            }
        } else {
            $this->ApiReturn(0, '请求失败!');
        }
    }
    //支付宝支付
    public function alipayHand($order_no, $price) {
        //include_once EXTEND_PATH. 'alipay/AopSdk.php';
        include_once ROOT_PATH . '/app/Packages/alipay/aop/AopClient.php';
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->getConfig('ali_appid');
        $aop->alipayrsaPublicKey = $this->getConfig('alipay_public_key');
        $aop->rsaPrivateKey = $this->getConfig('merchant_private_key');
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $arr['body']="购买米钻";
        $arr['subject']="购买米钻";
        $arr['out_trade_no']=$order_no;
        $arr['timeout_express']="30m";
        $arr['total_amount']=$price;
        $arr['product_code']="QUICK_MSECURITY_PAY";

        $bizcontent=json_encode($arr, JSON_UNESCAPED_UNICODE );
        $request->setNotifyUrl("http://" . $_SERVER['HTTP_HOST'] . "/api/AliPayNotify");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        $this->ApiReturn(1,'请求成功!',$response);

    }

    //微信App支付
    public function wxpayHand($order_no, $price) {
        // include_once EXTEND_PATH. 'wxPay/WxPay.php';
        $WxPay = new WxPay();
        $params['body'] = '购买米钻'; //商品描述
        $params['out_trade_no'] = $order_no; //自定义的订单号
        $params['total_fee'] = $price * 100; //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'APP'; //交易类型 JSAPI | NATIVE | APP | WAP
        $params['attach'] = '123'; //附加参数
        $params['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/api/wx_notify";
        $result = $WxPay->unifiedOrder($params);
        if ($result['prepay_id']) {
            $res = $WxPay->getOrder($result['prepay_id']);
            $this->ApiReturn(1, '', $res);
        } else {
            $this->ApiReturn(0, '请求失败!');
        }
    }

    


    //IOS内购
    public function applepay(){
        $info = $this->request->request();
        $user_id = $this->user_id;
        setViewNum($user_id,10);
        $goods_id = $this->request->request('goods_id');
        $receipt_data = $this->request->request('receipt_data') ? : 123;
        if(!$user_id || !$goods_id || !$receipt_data)   $this->ApiReturn(0,'缺少参数');
        $goods=DB::name('goods')->where('id',$goods_id)->find();
        if(!$goods) $this->ApiReturn(0,'商品不存在');

        //查验支付凭证
        $proof=DB::name('order')->where('ios_proof',$receipt_data)->where('pay_type',3)->value('id');
        if($proof)  $this->ApiReturn(0,'凭证已过期');


        //验证收据
        $res = $this->validate_applepay($receipt_data,true); //false沙盒 true正式
        if(intval($res['status'])==0) {  //验证成功
            $ios_bundle_id=$this->getConfig('ios_bundle_id');
            if($ios_bundle_id != $res['receipt']['bundle_id'])  $this->ApiReturn(0,'验签失败');
            $goods_id='mizuan'.$goods['price'];
            $g_id=$res['receipt']['in_app'][0]['product_id'];
            if($goods_id != $g_id)  $this->ApiReturn(0,'参数错误');
            //查验支付流水号
            $transaction_id = $res['receipt']['in_app'][0]['transaction_id'];
            $proof_transaction=DB::name('order')->where('ios_transaction_id',$transaction_id)->where('pay_type',3)->value('id');
            if($proof_transaction)  $this->ApiReturn(0,'订单已被使用');

            $ratio=$this->getConfig('ratio');
            $send=$goods['price'] * 10 * $ratio / 100;
            $mizuan=$goods['price'] * 7 + $send;
            $arr['order_no']=getOrderNo();
            $arr['user_id']=$user_id;
            $arr['mizuan']=ceil($mizuan);
            $arr['price']=$goods['price'];
            $arr['pay_type']=3;
            $arr['status']=2;
            $arr['ios_proof']=$receipt_data;
            $arr['ios_transaction_id']=$transaction_id;
            $arr['addtime']=$arr['paytime']=time();
            DB::name('order')->insertGetId($arr);
            userStoreInc($user_id,$mizuan,11,'mizuan');            
            $this->ApiReturn(1,'购买成功');
        }else{  //验证失败
            $this->ApiReturn(0,'验证失败');
        }
    }

    /**
     * IOS内购验证票据
     * @param  string $receipt_data 付款后凭证
     * @return array                验证是否成功
     */
    private function validate_applepay($receipt_data,$sandbox=false){
        // $apple_secret = config('applepay.apple_secret');
        $jsonData = array('receipt-data'=>$receipt_data);
        $post_json = json_encode($jsonData);
        if($sandbox){
            $url="https://buy.itunes.apple.com/verifyReceipt";//正式环境
        }else{
            $url="https://sandbox.itunes.apple.com/verifyReceipt";//沙盒环境
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result,true);

        //返回status示例:
        // * 0     验证成功
        // * 21000 App Store不能读取你提供的JSON对象
        // * 21002 receipt-data域的数据有问题
        // * 21003 receipt无法通过验证
        // * 21004 提供的shared secret不匹配你账号中的shared secret
        // * 21005 receipt服务器当前不可用
        // * 21006 receipt合法，但是订阅已过期。服务器接收到这个状态码时，receipt数据仍然会解码并一起发送
        // * 21007 receipt是Sandbox receipt，但却发送至生产系统的验证服务
        // * 21008 receipt是生产receipt，但却发送至Sandbox环境的验证服务
    }



    /*************************************************************************************************************************************/
    //支付宝异步回调
    public function AliPayNotify()
    {
        include_once ROOT_PATH . '/app/Packages/alipay/aop/AopClient.php';
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = $this->getConfig('alipay_public_key');
        $data = $this->request->request();
        // $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        // info($flag);
        //验签
        // $newLog=json_encode($data,JSON_UNESCAPED_UNICODE);
        // file_put_contents('test.txt', $newLog.PHP_EOL, FILE_APPEND);
        if($data['app_id'] == $this->getConfig('ali_appid') && $data['sign_type'] == 'RSA2'){
            //处理业务，并从$_POST中提取需要的参数内容
            if($data['trade_status'] == 'TRADE_SUCCESS' ){
                //处理交易完成或者支付成功的通知
                $out_trade_no = $data['out_trade_no'];

                $order=DB::name('order')->where(['order_no'=>$out_trade_no])->find();
                if(!$order || $order['price'] != $data['total_amount']){
                    echo "fail0";exit;
                }
                if($order['status'] == 2){
                    echo "fail";exit;
                }
                //业务逻辑
                $arr['status']=2;
                $arr['paytime']=time();
                $res=DB::name('order')->where('id',$order['id'])->update($arr);
                if($res){
                    //增加用户米钻及充值记录
                    userStoreInc($order['user_id'],$order['mizuan'],11,'mizuan');
                    echo "success";exit;
                }else{
                    echo "fail";exit;
                }

            }else{
                echo "fail";exit;
            }
        }else{
            echo "fail";exit;
        }
    }

    //微信支付回调
    public function wx_notify(){
        $WxPay = new WxPay();
        //接收微信返回的数据数据,返回的xml格式
		$xmlData = file_get_contents('php://input');
        //将xml格式转换为数组
		$data = $WxPay->xml_to_data($xmlData);
        //$data = $this->request->request();
        //打印
        //$newLog=json_encode($data,JSON_UNESCAPED_UNICODE);
        //file_put_contents('test.txt', $newLog.PHP_EOL, FILE_APPEND);
        //为了防止假数据，验证签名是否和返回的一样。
        //记录一下，返回回来的签名，生成签名的时候，必须剔除sign字段。
        $sign = $data['sign'];
        unset($data['sign']);
        //if($data['s'])  unset($data['s']);
        if($sign == $WxPay->MakeSign($data)){
            //签名验证成功后，判断返回微信返回的
            if ($data['result_code'] == 'SUCCESS') {
                //根据返回的订单号做业务逻辑
                $out_trade_no = $data['out_trade_no'];
                $order=DB::name('order')->where(['order_no'=>$out_trade_no])->find();
                $price = $order['price'] * 100;
                if(!$order || $price != $data['total_fee']){
                    echo "fail";exit;
                }
                if($order['status'] == 2){
                    echo "fail";exit;
                }
                //业务逻辑
                $arr['status']=2;
                $arr['paytime']=time();
                $res=DB::name('order')->where('id',$order['id'])->update($arr);
                if($res){
                    //增加用户米钻及充值记录
                    userStoreInc($order['user_id'],$order['mizuan'],11,'mizuan');
                    echo "success";exit;
                }else{
                    echo "fail";exit;
                }
            }else{
                echo "fail";exit;
            }
        }else{
            echo "fail";exit;
        }
    }





    
  
 /***********************************弃用******************************************************/ 
    //微信公众号支付
    public function weixinPay222($order_no,$price) {
        $WxPay = new WxPay();
        $params['body'] = '购买米钻'; //商品描述
        $params['out_trade_no'] = $order_no; //自定义的订单号
        $params['total_fee'] = $price * 100; //订单金额 只能为整数 单位为分
        // $params['total_fee'] = 1;
        $params['trade_type'] = 'NATIVE'; //交易类型 JSAPI | NATIVE | APP | WAP
        $params['attach'] = '123'; //附加参数
        $params['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/api/wx_notify";
        $result = $WxPay->unifiedOrder($params);
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            $this->ApiReturn(1, '请求成功', $result['code_url']);
        } else {
            $this->ApiReturn(0, '请求失败!');
        }
    }
  
  
  


}
