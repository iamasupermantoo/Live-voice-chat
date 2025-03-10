<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\usersmanage\Users;
use app\admin\model\officialsmanage\Onepage;
use think\Db;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use app\admin\model\configmanage\Config as Configs;
use think\Log;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];




    

    public function room_test2(){
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        sleep(3);
        echo rand(1000,9999);
    }
    public function redisSet(){
        set_time_limit(0);
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        // $arr = array('h','e','l','l','o','w','o','r','l','d');
        // foreach($arr as $k=>$v){
        //   $redis->rpush("mylist",$v);
        // }
        /*$value=$this->request->request('value');
        $redis->rpush("mylist01",$value);
        $len=$redis->lLen("mylist01");
        if($len > 1){
            sleep($len*5);
            $res=$redis->lpop("mylist01");
        }else{
            $res=$value;
        }
        echo $res;*/
    }



    public function redisGet(){
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        //list类型出队操作
        $value = $redis->lpop('mylist');
        if($value){
            echo "出队的值".$value;
        }else{
            echo "出队完成";
        }
    }








    /********************************************************************************************************************************/
     /**
     * 首页
     */
    public function index()
    {
        $this->success('请求成功');
    }

    /**
     * 注册会员
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email 邮箱
     * @param string $mobile 手机号
     */
    public function registered()
    {
        $phone = $this->request->request('phone');
        $pass = $this->request->request('pass');
        $nickname = $this->request->request('nickname');
        $sex      = $this->request->request('sex');
        $headimgurl = $this->request->request('headimgurl');
        $headimgurl = $this->base64_image_content($headimgurl,1);//echo $headimgurl;die;
        $this->SensitiveImage($headimgurl);
        $birthday   = $this->request->request('birthday') ? : date('Y-m-d',time());
        $system   = $this->request->request('system') ? : '';      //系统,android或ios
        $channel   = $this->request->request('channel') ? : '';    //渠道

        if (!$phone || !$pass)
        {
            $this->error(__('Invalid parameters'));
        }

        $ret = $this->auth->register($pass,$phone,$nickname,$sex,$headimgurl,$birthday,$system,$channel);
        if ($ret)
        {
            $getUserinfo = $this->auth->getUserinfo();
            DB::name('user_total')->insertGetId(['user_id'=>$getUserinfo['id'],'addtime'=>time()]);
            $img = $this->auth->setFilePath($headimgurl);
            $res = $this->ryHand(1,$getUserinfo['id'],$getUserinfo['nickname'],$img);
            if($res['code'] == 200){
                DB::name('users')->where('id',$getUserinfo['id'])->update(['ry_uid'=>$res['userId'],'ry_token'=>$res['token']]);
            }
            $getUserinfo['ry_uid'] = $res['userId'];
            $getUserinfo['ry_token'] = $res['token'];
            //$data = ['userinfo' => $getUserinfo];
            $this->success(__('Sign up successful'), $getUserinfo);
        }
        else
        {
            $this->error($this->auth->getError());
        }
    }


    //第三方注册
    public function OtherRegister(){
        $data = $this->request->request();
        $type = $this->request->request('type') ? : '';
        if(!in_array($type,['wx','wb','qq']))  $this->ApiReturn(0,'参数错误');
        $phone = DB::name('users')->where('phone',$data['phone'])->value('phone');
        if($phone)  $this->ApiReturn(0,'该手机号已被注册');
        if(!check_sms($data['phone'],$data['code']))    $this->ApiReturn(0,'验证码错误或已过期');
        $user=DB::name('users')->where($type.'_openid',$data['openid'])->value('id');
        if($user)   $this->ApiReturn(0,'此账号已被注册');
        $arr['phone']=$data['phone'];
        $arr[$type.'_openid']=$data['openid'];
        $arr['sex']=$data['sex'];
        $arr['birthday']=date('Y-m-d',time());
        $arr['constellation']=getBrithdayMsg($arr['birthday'],2);
        if(!isPassword($data['pass']))  $this->ApiReturn(0,'密码为长度6-20位的数字或字母混合组成!');
        $arr['salt']=substr(md5(time()),0,3);
        $arr['pass']=$this->pwdMd5($data['pass'],$arr['salt']);
        $arr['nickname']=$data['nickname'];
        $headimgurl=str_replace('amp;', '',$data['headimgurl']);
        $arr['headimgurl'] = !empty($headimgurl) ? $headimgurl : '/upload/cover/default.png';
        $arr['login_ip']=get_client_ip();
        $arr['system']=$this->request->request('system') ? : '';
        $arr['channel']=$this->request->request('channel') ? : '';

        $arr['created_at']=date('Y-m-d H:i:s',time());
        $arr['updated_at']=date('Y-m-d H:i:s',time());


        $add_id=DB::name('users')->insertGetId($arr);
        if($add_id){
            DB::name('user_total')->insertGetId(['user_id'=>$add_id,'addtime'=>time()]);
            $img=$this->auth->setFilePath($arr['headimgurl']);
            $nickname='用户'.$add_id;
            $res=$this->ryHand(1,$add_id,$nickname,$img);
            if($res['code'] == 200){
                DB::name('users')->where('id',$add_id)->update(['ry_uid'=>$res['userId'],'ry_token'=>$res['token']]);
            }
            $this->auth->direct($add_id);
            $data = $this->auth->getUserinfo();
            $this->ApiReturn(1,'注册成功',$data);
        }else{
            $this->ApiReturn(0,'注册失败');
        }
    }

    //登录
    public function login(){
        $account = $this->request->request('phone');
        $pass = $this->request->request('pass');

        if (!$account || !$pass) {
            $this->error(__('Invalid parameters'));
        }

        $ret = $this->auth->login($account, $pass);
        if ($ret) {
            $user = $this->auth->getUserinfo();
            $this->ApiReturn(1,'',$user);
        } else {
            $this->error($this->auth->getError());
        }
    }

    //第三方登录
    public function OtherLogin(){
        $openid = $this->request->request('openid');
        $type = $this->request->request('type') ? : '';

        if(!in_array($type,['wx','wb','qq']))  $this->error(__('参数错误'));

        $user = DB::name('users')->where($type.'_openid',$openid)->field(['id','status','locktime','nickname','headimgurl','ry_uid','ry_token','phone','pass','salt'])->select();
        //echo DB::name('users')->getLastSql();die;
        if(!$user)    $this->error(__('暂无此用户，请先注册'),'',2);
        if($user[0]['status'] == 2){
            if($user[0]['locktime'] > time()){
                $da = date('Y-m-d H:i:s',$user[0]['locktime']);
                $this->error(__("'账号已被锁定,请于'.$da.'之后登陆'"));
            }else{
                DB::name('users')->where('phone',$phone)->update(['status'=>1]);
            }
        }

        //accid存在更新ry_uid
        if($user[0]['ry_uid']){
            $img = $this->auth->setFilePath($user[0]['headimgurl']);
            $res = $this->ryHand(1,$user[0]['ry_uid'],$user[0]['nickname'],$img);
            if($res['code'] == 200){
                DB::name('users')->where('id',$user[0]['id'])->update(['ry_token'=>$res['token']]);
            }
        }
        //$u_arr = DB::name('users')->where('id',$user[0]['id'])->field(['id','nickname','headimgurl','ry_uid','ry_token','phone'])->find();
        //$u_arr['headimgurl'] = $this->auth->setFilePath($u_arr['headimgurl']);
        //$token = md5($phone.time().mt_rand(10000,99999));
        $ip = get_client_ip();
        DB::name('users')->where('id',$user[0]['id'])->update(['login_ip'=>$ip]);
        $this->auth->direct($user[0]['id']);
        $data = $this->auth->getUserinfo();
        $this->success(__('登录成功'),$data);
    }

    

    //忘记密码
    public function forget_pwd(){
        $data = $this->request->request();
        if(empty($data['phone']) || empty($data['code']) || empty($data['pass']) )    $this->error(__('Invalid parameters'));

        //if(!check_sms($data['phone'],$data['code']))    $this->error(__('验证码错误或已过期'));

        if(!isPassword($data['pass']))  $this->error(__('密码为长度6-20位的数字或字母混合组成!'));

        $arr['salt']=substr(md5(time()), 0 , 3);

        $arr['pass'] = $this->pwdMd5($data['pass'],$arr['salt']);

        $res = Users::where('phone',$data['phone'])->update($arr);
        if($res){
            $this->success(__('修改成功'));
        }else{
            $this->error(__('修改失败'));
        }
    }

    //融云
    protected function ryHand($type,$ry_uid,$nickname='',$headimg=''){
        import('RongCloud/RongCloud', VENDOR_PATH);
        $AppKey = $this->getConfig('ry_app_key');
        $AppSecret = $this->getConfig('ry_app_secret');
        $RongSDK = new \RongCloud\RongCloud($AppKey,$AppSecret);
        $user = [
            'id'=> $ry_uid,
            'name'=> $nickname,//用户名称
            'portrait'=> $headimg //用户头像
        ];
        if($type == 1){
            $res = $RongSDK->getUser()->register($user);
           
        }elseif($type == 2){
            $res = $RongSDK->getUser()->register($user);
        }else{
            return ['code'=>0,'info'=>'not found type'];
        }
        
        // $update = $RongSDK->getUser()->update($user);
        // $res = $RongSDK->getUser()->MuteGroups()->getList(['test1']);
        return $res;
    }



    //发送短信
    public function verification(){


        $phone = $this->request->request("phone");

        if(!$phone) $this->ApiReturn(0,'缺少参数');


        if(!isMobile($phone))   $this->ApiReturn(0,'请输入正确手机号');
        $aid = $this->request->request("aid") ? : 0;
        $type = $this->request->request("type") ? : '';
        if($type == 'reg'){
            $uid = DB::name('users')->where('phone',$phone)->value('id');
            if($uid)    $this->ApiReturn(0,'该手机号已被注册');
        }

        $data = DB::name('code')->where('phone',$phone)->find();
        if($data){
            $sjc = $data['addtime'] + 300 - time();
            if($sjc > 0)  $this->ApiReturn(0,'请'.$sjc.'秒之后再试');
        }
        $code = mt_rand(1000,9999);

        $key_id=$this->getConfig('ali_sms_key_id');
        $key_secret=$this->getConfig('ali_sms_key_secret');
        $sms_code=$this->getConfig('ali_sms_code');
        AlibabaCloud::accessKeyClient($key_id,$key_secret)
            ->regionId('cn-hangzhou')
            ->asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $phone,
                        'SignName' => "青春派语音",
                        'TemplateCode' => $sms_code,
                        'TemplateParam' => json_encode(['code'=>$code]),
                    ],
                ])
                ->request();
            $res = $result->toArray();
            if($res['Message'] == 'OK'){
                $arr['phone']=$phone;
                $arr['code']=$code;
                $arr['aid']=$aid;
                $arr['addtime']=time();
                if($data){
                    DB::name('code')->where('id',$data['id'])->update($arr);
                }else{
                    DB::name('code')->insertGetId($arr);
                }
                $this->ApiReturn(1,'短信发送成功');
            }else{
                $this->ApiReturn(0,'短信发送失败',$res);
            }
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }









    //校验验证码
    public function is_verification(){
        $phone = $this->request->request("phone");
        $code = $this->request->request("code");
        if(!$phone || !$code)   $this->ApiReturn(0,'缺少参数');
        // if($code == '1234') $this->ApiReturn(1,'');

        $data = Db::name('code')->where('phone', $phone)->find();
        if (!$data) $this->ApiReturn(0,'参数错误');
        $sjc = $data['addtime'] + 300 - time();
        if ($sjc < 0 || $data['code'] != $code) {
            $this->ApiReturn(0,'验证码错误或已过期');
        } else {
            $this->ApiReturn(1,'验证码正确');

        }
    }

    //单页
    public function one_page(){
        $type = $this->request->request('type');
        $type = $type ? : 0;
        if(!$type)  $this->ApiReturn(0,'缺少参数');
        $data=DB::name('one_page')->where(['type'=>$type])->select();
        foreach ($data as $k => &$v) {
            $v['content']= $v['content'] ? : '';
            $v['url']= $v['url'] ? : '';
        }
        $this->ApiReturn(1,'',$data);
    }

    //后台登录,刷新ry_token
    public function adminLogin(){
        $id=input('user_id/d',0);
        if(!$id)    $this->ApiReturn(0,'缺少参数');
        $user=DB::name('users')->field('id,nickname,headimgurl,ry_uid,ry_token')->find($id);
        if(!$user)    $this->ApiReturn(0,'查无此人');
        //accid存在更新ry_uid
        if($user['ry_uid']){
            $img=$this->setFilePath($user['headimgurl']);
            $res=$this->ryHand(1,$user['ry_uid'],$user['nickname'],$img);
            if($res['code'] == 200){
                DB::name('users')->where(['id'=>$user_id['id']])->update(['ry_token'=>$res['token']]);
            }
        }
        $this->ApiReturn(1,'');
    }

}
