<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Session;
use think\Db;
use think\Loader;
use wxPay\WxPay;
use think\Request;

class Pay extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _initialize(){
        //session(null);die;
        //echo $action;die;
        //echo Session::get('openid');
        $action = request()->action();
       if (!Session::get('openid') && $action != 'notifyx'){
            if (isset($_GET['code'])){
                \addons\epay\library\Wechat::getOpenid();
            }else{
                $url = \addons\epay\library\Wechat::getAuthorizeUrl();
                $this->redirect($url);
            }
        }

    }



    public function tests()
    {
        //测试授权
        $url = \addons\epay\library\Wechat::getAuthorizeUrl();
        //echo $url;die;
        //Header("Location: $url");
        //echo $url;die;
        $this->redirect($url);

        //$this->view->assign("title", "FastAdmin微信支付宝整合插件");
        //return $this->view->fetch();
    }

    /*
     * 网站首页
     */
    public function index()
    {
    
  
        header('Access-Control-Allow-Origin:*');
        // 响应类型
        header('Access-Control-Allow-Methods:GET, POST, PUT,DELETE');
        // 响应头设置
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
       /* if ($_GET['code']){
            $result = \addons\epay\library\Wechat::getOpenid();
            //print_r($result);die;
        }*/
        //$url = \addons\epay\library\Wechat::getAuthorizeUrl();
        //Header("Location: $url");
        //echo $url;die;
       // $data = $this->redirect($url);
        //print_r($data);die;

        $params = $this->request->request();

        $userId = $params['uid'];
       
        $info = db::name('users')->where(array('id'=>$userId))->find();
        //$userId = 1766;

        //订单号
        $out_trade_no = "MN" . date("YmdHis") . rand(100000, 999999);
        //订单标题
        $title = '购买米钻';
        $amount = $params['money'];
        /*if ($userId == '1100025'){
            //测试
            $amount = 0.01;
        }*/
        //$amount = 0.01;
        $notifyurl = "http://b.juchuyuncang.com/index/pay/notifyx";
        $returnurl = "http://b.juchuyuncang.com/index/index/index";


        //预支付订单入库
        $arr['user_id'] =$userId;
        $arr['order_no'] = $out_trade_no;
        //$price = DB::name('goods')->where('id',$info['goods_id'])->value('price');
        //if(!in_array($pay_type, [1,2,4]) || !$price)  $this->ApiReturn(0,'参数错误1,',$info);

        $ratio = $this->getConfig('pay_ratio'); //返额比例%
        $pay_type = 4; //1=支付宝 2=微信 4=微信公众号



        $arr['price'] = $amount;
        $arr['mizuan'] = ($amount * 10) * (100+$ratio) /100;
        $arr['pay_type'] = $pay_type;
        $arr['remark'] = '微信公众号购买米钻';
        $arr['addtime'] = time();
        $res = DB::name('order')->insertGetId($arr);

        $params = [
            'amount'   => $amount,
            'orderid'  => $out_trade_no,
            'type'     => "wechat",
            'title'    => $title,
            'notifyurl'=> $notifyurl,
            'returnurl'=> $returnurl,
            'method'   => "mp",
            'openid'   => Session::get('openid'),
            'auth_code'=> "5675",
        ];
        
        return \addons\epay\library\Service::submitOrder($params);
        
        
        
    }
    

    //获取数据库配置信息
    public function getConfig($name=null){
        if(!$name)  return '';
        $val=DB::name('config')->where('name',$name)->where('status',1)->value('value');
        return $val;
    }

    /**
     * 支付成功，仅供开发测试
     */
    public function notifyx()
    {
        //$paytype = $this->request->param('paytype');

        $WxPay = new WxPay();
        //接收微信返回的数据数据,返回的xml格式
        $xmlData = file_get_contents('php://input');
        //将xml格式转换为数组
        $data = $WxPay->xml_to_data($xmlData);

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
                $arr['remark'] = json_encode($data);
                $res=DB::name('order')->where('id',$order['id'])->update($arr);
                if($res){
                    //DB::name('users')->where('id',$order['user_id'])->setInc('mizuan',$order['mizuan']);
                    $this->userStoreInc($order['user_id'],$order['mizuan'],11,'mizuan');
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



        echo 'success';exit();
    }

    //增加数值
    function userStoreInc($user_id, $get_nums, $get_type, $jb_type) {
        if ($get_type == 99) {
            $now_nums = 0;
            $res = 1;
        } else {
            $res = DB::name('users')->where(['id' => $user_id])->setInc($jb_type, $get_nums);
            $now_nums = DB::name('users')->where(['id' => $user_id])->value($jb_type);
        }
        if (!$res) return false;
        $this->addTranmoney($user_id, $get_nums, $get_type, $now_nums);
        return $res;
    }

    //创建记录
    function addTranmoney($user_id, $get_nums, $get_type, $now_nums) {
        $info['user_id'] = $user_id;
        $info['get_nums'] = $get_nums;
        $info['get_type'] = $get_type;
        $info['now_nums'] = $now_nums;
        $info['addtime'] = time();
        $res = DB::name('store_log')->insertGetId($info);
        return $res;
    }




}
