<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

class Tication extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    //初始化
    public function sfrz_start()
    {
        $data            = $this->request->request();
        $data['user_id'] = $this->user_id;
        if (!$data['user_id'] || !$data['name'] || !$data['idno']) {
            $this->ApiReturn(0, '缺少参数');
        }

        $is_idcard = DB::name('users')->where('id', $data['user_id'])->value('is_idcard');
        if ($is_idcard) {
            $this->ApiReturn(0, '您已完成身份认证,请勿重复认证');
        }

        //$newLog=json_encode($data,JSON_UNESCAPED_UNICODE);
        //file_put_contents('test.txt', $newLog.PHP_EOL, FILE_APPEND);
        // include_once EXTEND_PATH . 'aliserver/aop/AopClient.php';
        include_once ROOT_PATH . '/app/Packages/alipay/aop/AopClient.php';
        // import('aliserver.aop.AopClient', EXTEND_PATH,'.php');
        $aop                     = new \AopClient();
        $aop->gatewayUrl         = 'https://openapi.alipay.com/gateway.do';
        $aop->appId              = $this->getConfig('ali_appid');
        $aop->alipayrsaPublicKey = $this->getConfig('alipay_public_key');
        $aop->rsaPrivateKey      = $this->getConfig('merchant_private_key');
        $aop->apiVersion         = '1.0';
        $aop->signType           = 'RSA2';
        $aop->charset            = 'UTF-8';
        $aop->format             = 'json';
        $order_no                = $this->makeOrderNo();
        $arr                     = [
            'outer_order_no'        => $order_no,
            'biz_code'              => 'FACE',
            'identity_param'        => [
                'identity_type' => 'CERT_INFO',
                'cert_type'     => 'IDENTITY_CARD',
                'cert_name'     => $data['name'],
                'cert_no'       => $data['idno'],
            ],
            'merchant_config'       => [
                'return_url' => "alipay2018121162530291://",
                //'return_url'=>"http://" . $_SERVER['HTTP_HOST'] . "/api/sfrz_start_notify",
            ],
            'face_contrast_picture' => null,
        ];

        $bizcontent = json_encode($arr, JSON_UNESCAPED_UNICODE);

        //include_once EXTEND_PATH . 'aliserver/aop/request/AlipayUserCertifyOpenInitializeRequest.php';
        //import('aliserver/aop/request/AlipayUserCertifyOpenInitializeRequest', EXTEND_PATH,'.php');

        $res = new \AlipayUserCertifyOpenInitializeRequest();

        $res->setBizContent($bizcontent);
        $result       = $aop->execute($res);
        $responseNode = str_replace(".", "_", $res->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            $this->up_face_order($arr['outer_order_no'], $data['user_id'], $data['name'], $data['idno'], $result->$responseNode->certify_id);
            //$this->ApiReturn(1,'',$result->$responseNode);
        } else {
            $this->ApiReturn(0, '');
        }
    }

    //写入数据库
    protected function up_face_order($order_no, $user_id, $name, $idno, $certify_id)
    {
        $data              = DB::name('face_order')->where('user_id', $user_id)->find();
        $arr['order_no']   = $order_no;
        $arr['user_id']    = $user_id;
        $arr['name']       = $name;
        $arr['idno']       = $idno;
        $arr['status']     = 1;
        $arr['certify_id'] = $certify_id;
        if ($data) {
            if ($data['status'] == 3) {
                return false;
            }

            $res = DB::name('face_order')->where('id', $data['id'])->update($arr);
        } else {
            $arr['addtime'] = time();
            $res            = DB::name('face_order')->insertGetId($arr);
        }

        if ($res) {
            $this->sfrz_rz($certify_id);
        } else {
            $this->ApiReturn(0, '请求失败');
        }

    }
    //初始化跳转页面
    public function sfrz_start_notify()
    {
        $data   = $this->request->request();
        $newLog = json_encode($data, JSON_UNESCAPED_UNICODE);
        file_put_contents('test.txt', $newLog . PHP_EOL, FILE_APPEND);
        $this->ApiReturn(1, '', $data);
    }

    //执行身份认证
    protected function sfrz_rz($certify_id)
    {
        include_once ROOT_PATH . '/app/Packages/alipay/aop/AopClient.php';
        $aop                     = new \AopClient();
        $aop->gatewayUrl         = 'https://openapi.alipay.com/gateway.do';
        $aop->appId              = $this->getConfig('ali_appid');
        $aop->alipayrsaPublicKey = $this->getConfig('alipay_public_key');
        $aop->rsaPrivateKey      = $this->getConfig('merchant_private_key');
        $aop->apiVersion         = '1.0';
        $aop->signType           = 'RSA2';
        $aop->charset            = 'UTF-8';
        $aop->format             = 'json';
        //$aop->return_url='http://'.$_SERVER['HTTP_HOST'].'/api/sfrz_rz_notify';   //非必填
        $aop->return_url   = 'alipay2018121162530291://'; //非必填
        $request           = new \AlipayUserCertifyOpenCertifyRequest();
        $arr['certify_id'] = $certify_id;
        $bizcontent        = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($bizcontent);
        //返回一个URL链接,这里一定要用GET
        $result = $aop->pageExecute($request, 'GET');
        $this->ApiReturn(1, '', $result);
    }

    public function sfrz_rz_notify()
    {
        $data   = $this->request->request();
        $newLog = json_encode($data, JSON_UNESCAPED_UNICODE);
        file_put_contents('test.txt', $newLog . PHP_EOL, FILE_APPEND);
        $this->ApiReturn(1, '', $data);
    }

    //认证记录查询
    public function sfrz_query()
    {
        $user_id = $this->user_id;
        if (!$user_id) {
            $this->ApiReturn(0, '缺少参数');
        }

        $data = DB::name('face_order')->where('user_id', $user_id)->find();
        if (!$data) {
            $this->ApiReturn(0, '参数错误');
        }

        if ($data['status'] == 3) {
            $this->ApiReturn(1, '认证成功');
        }

        include_once ROOT_PATH . '/app/Packages/alipay/aop/AopClient.php';
        $aop                     = new \AopClient();
        $aop->gatewayUrl         = 'https://openapi.alipay.com/gateway.do';
        $aop->appId              = $this->getConfig('ali_appid');
        $aop->alipayrsaPublicKey = $this->getConfig('alipay_public_key');
        $aop->rsaPrivateKey      = $this->getConfig('merchant_private_key');
        $aop->apiVersion         = '1.0';
        $aop->signType           = 'RSA2';
        $aop->postCharset        = 'UTF-8';
        $aop->format             = 'json';
        $request                 = new \AlipayUserCertifyOpenQueryRequest();
        $arr['certify_id']       = $data['certify_id'];
        $bizcontent              = json_encode($arr, JSON_UNESCAPED_UNICODE);
        $request->setBizContent($bizcontent);
        $result = $aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            if ($result->$responseNode->passed == 'T') {
                $res = $this->finish($data['certify_id']);
                $this->ApiReturn(1, '认证成功', $res);
            } else {
                $this->ApiReturn(0, '认证失败');
            }
        } else {
            $this->ApiReturn(0, '请求失败');
        }
    }

    //认证成功
    protected function finish($certify_id)
    {
        $data = DB::name('face_order')->where('certify_id', $certify_id)->find();
        if (!$data || $data['status'] == 3) {
            return true;
        }

        DB::name('face_order')->where('id', $data['id'])->update(['status' => 3, 'finish_time' => time()]);
        $arr['name']      = $data['name'];
        $arr['idno']      = $data['idno'];
        $arr['is_idcard'] = 1;
        DB::name('users')->where('id', $data['user_id'])->update($arr);
        $res = $this->createRoom($data['user_id']);
        return $res;

    }

    //创建房间
    protected function createRoom($user_id)
    {
        $room = DB::name('rooms')->where('uid', $user_id)->value('id');
        if ($room) {
            return false;
        }

        $arr = array(
            'numid'      => $user_id,
            'uid'        => $user_id,
            'room_name'  => '用户' . $user_id . '的房间',
            'updated_at' => date('Y-m-d H:i:s', time()),
            'created_at' => date('Y-m-d H:i:s', time()),
             'room_class'=>'14',
             'room_type'=>'17'
        );
        $res = DB::name('rooms')->insertGetId($arr);
        return $res;
    }

    protected function makeOrderNo()
    {
        $str = substr(md5(time()), 8);
        $no  = 'MN' . $str . mt_rand(100000, 999999);
        return $no;
    }

    //获取服务端存储的certify_id
    public function get_facd_order()
    {
        $user_id = $this->user_id;
        if (!$user_id) {
            $this->ApiReturn(0, '缺少参数');
        }

        $certify_id = DB::name('face_order')->where('user_id', $user_id)->value('certify_id');
        $this->ApiReturn(1, '', ['certify_id' => $certify_id]);
    }

    //登录授权
    public function ali_oauth_code()
    {
        $target_id = md5(time() . rand(1000, 9999));
        $data      = array(
            'apiname'    => 'com.alipay.account.auth',
            'app_id'     => '2019110769000055',
            'app_name'   => 'mc',
            'auth_type'  => 'AUTHACCOUNT',
            'biz_type'   => 'openservice',
            'method'     => 'alipay.open.auth.sdk.code.get',
            'pid'        => '2088631773380482',
            'product_id' => 'APP_FAST_LOGIN',
            'scope'      => 'kuaijie',
            'sign_type'  => 'RSA2',
            'target_id'  => $target_id,
        );
        $str = arr_to_str($data);
        include_once ROOT_PATH . '/app/Packages/alipay/aop/AopClient.php';
        $aop         = new \AopClient();
        $privatekey  = $this->getConfig('merchant_private_key');
        $sign        = $aop->alonersaSign($str, $privatekey, 'RSA2', false);
        $sign        = urlencode($sign);
        $arr['sign'] = $str . '&sign=' . $sign;
        $this->ApiReturn(1, '', $arr);
    }

    public function ali_oauth_token()
    {
        $auth_code = $this->request->request('auth_code') ?: '';
        $user_id   = $this->user_id;
        if (!$auth_code || !$user_id) {
            $this->ApiReturn(0, '缺少参数');
        }

        $ali_user_id = DB::name('users')->where('id', $user_id)->value('ali_user_id');
        if ($ali_user_id) {
            $this->ApiReturn(0, '您已通过支付宝认证,请勿重复认证');
        }

        include_once ROOT_PATH . '/app/Packages/alipay/aop/AopClient.php';
        $aop                     = new \AopClient();
        $aop->gatewayUrl         = 'https://openapi.alipay.com/gateway.do';
        $aop->appId              = $this->getConfig('ali_appid');
        $aop->alipayrsaPublicKey = $this->getConfig('alipay_public_key');
        $aop->rsaPrivateKey      = $this->getConfig('merchant_private_key');
        $aop->apiVersion         = '1.0';
        $aop->signType           = 'RSA2';
        $aop->postCharset        = 'UTF-8';
        $aop->format             = 'json';
        $request11               = new \AlipaySystemOauthTokenRequest();
        $request11->setGrantType("authorization_code");
        $request11->setCode($auth_code);
        // $request->setRefreshToken("201208134b203fe6c11548bcabd8da5bb087a83b");
        $result       = $aop->execute($request11);
        $responseNode = str_replace(".", "_", $request11->getApiMethodName()) . "_response";
        if (!isset($result->$responseNode->access_token)) {
            $this->ApiReturn(0, '请求失败,请刷新后重试~', $result);
            // $this->ApiReturn(0,$result->error_response->sub_msg);
        }
        $access_token = $result->$responseNode->access_token;
        if (!empty($access_token)) {
            $this->get_ali_user_info($access_token, $user_id);
        } else {
            $this->ApiReturn(0, '请求失败');
        }
    }

    protected function get_ali_user_info($access_token, $user_id)
    {
        include_once ROOT_PATH . '/app/Packages/alipay/aop/AopClient.php';
        $aop                     = new \AopClient();
        $aop->gatewayUrl         = 'https://openapi.alipay.com/gateway.do';
        $aop->appId              = $this->getConfig('ali_appid');
        $aop->alipayrsaPublicKey = $this->getConfig('alipay_public_key');
        $aop->rsaPrivateKey      = $this->getConfig('merchant_private_key');
        $aop->apiVersion         = '1.0';
        $aop->signType           = 'RSA2';
        $aop->postCharset        = 'UTF-8';
        $aop->format             = 'json';
        $request                 = new \AlipayUserInfoShareRequest();
        $result                  = $aop->execute($request, $access_token);
        $responseNode            = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode              = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            $ali_info = $result->$responseNode;
            $id       = DB::name('users')->where('ali_user_id', $ali_info->user_id)->value('id');
            if ($id) {
                $this->ApiReturn(0, '已有用户绑定该支付宝账号,请勿重复绑定');
            }

            $arr['ali_user_id']   = $ali_info->user_id;
            $arr['ali_avatar']    = isset($ali_info->avatar) ? $ali_info->avatar : '';
            $arr['ali_nick_name'] = isset($ali_info->nick_name) ? $ali_info->nick_name : '';
            $res                  = DB::name('users')->where('id', $user_id)->update($arr);
            if ($res) {
                $this->ApiReturn(1, '绑定成功', $ali_info);
            } else {
                $this->ApiReturn(0, '绑定失败');
            }
        } else {
            $this->ApiReturn(0, '请求失败');
        }
    }

    //提现
    public function ali_tixian()
    {
        $is_tixian = $this->getConfig('is_tixian');
        if (!$is_tixian) {
            $this->ApiReturn(0, '暂不可提现');
        }

        $tx_id   = $this->request->request('tx_id') ?: 0;
        $tx_data = DB::name('tixian')->where('id', $tx_id)->find();
        if (!$tx_data || $tx_data['status'] == 2) {
            $this->ApiReturn(0, '参数错误');
        }

        $ali_user_id = DB::name('users')->where('id', $tx_data['user_id'])->value('ali_user_id');
        if (!$ali_user_id) {
            $this->ApiReturn(0, '参数错误');
        }

        include_once ROOT_PATH . '/app/Packages/alipay/aop/AopClient.php';
        $aop                     = new \AopClient();
        $aop->gatewayUrl         = 'https://openapi.alipay.com/gateway.do';
        $aop->appId              = $this->getConfig('ali_appid');
        $aop->alipayrsaPublicKey = $this->getConfig('alipay_public_key');
        $aop->rsaPrivateKey      = $this->getConfig('merchant_private_key');
        $aop->apiVersion         = '1.0';
        $aop->signType           = 'RSA2';
        $aop->postCharset        = 'UTF-8';
        $aop->format             = 'json';
        $arr                     = [
            'out_biz_no'      => $tx_data['order_no'], //订单编号
            'payee_type'      => 'ALIPAY_USERID',
            'payee_account'   => $ali_user_id,
            'amount'          => $tx_data['money'],
            //'amount'=>'0.1',
            'payer_show_name' => '啾咪语音',
        ];

        $bizcontent = json_encode($arr, JSON_UNESCAPED_UNICODE);

        //include_once EXTEND_PATH . 'alipay/aop/AlipayFundTransToaccountTransferRequest.php';
        $request = new \AlipayFundTransToaccountTransferRequest();
        $request->setBizContent($bizcontent);
        $result       = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            $order_no = $result->$responseNode->out_biz_no;
            $this->updateTixian($order_no);
            $this->ApiReturn(1, '提现成功', $result->$responseNode);
        } else {
            $this->ApiReturn(0, '提现失败');
        }
    }

    protected function updateTixian($order_no)
    {
        $arr['status']  = 2;
        $arr['tx_time'] = time();
        $res            = DB::name('tixian')->where('order_no', $order_no)->update($arr);
        return $res;
    }

    //解绑支付宝
    public function UntyingAli()
    {
        $user_id = $this->user_id;
        if (!$user_id) {
            $this->ApiReturn(0, '缺少参数');
        }

        $ali_user_id = DB::name('users')->where('id', $user_id)->value('ali_user_id');
        if (!$ali_user_id) {
            $this->ApiReturn(0, '暂未绑定支付宝');
        }

        $arr['ali_user_id']   = '';
        $arr['ali_avatar']    = '';
        $arr['ali_nick_name'] = '';
        $res                  = DB::name('users')->where('id', $user_id)->update($arr);
        if ($res) {
            $this->ApiReturn(1, '解绑支付宝成功');
        } else {
            $this->ApiReturn(0, '解绑支付宝失败');
        }
    }

}
