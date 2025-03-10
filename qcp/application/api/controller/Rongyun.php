<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\usersmanage\Users;
use app\admin\model\officialsmanage\Onepage;
use think\Db;
use vendor\AlibabaCloud\Client\AlibabaCloud;
use vendor\AlibabaCloud\Client\Exception\ClientException;
use vendor\AlibabaCloud\Client\Exception\ServerException;

class Rongyun extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function rongtest(){
        include base_path() . '/vendor/RongCloud/RongCloud.php';
        define("APPKEY", '82hegw5u8xjux');
        define('APPSECRET','A3oc8RDEyQVlDB');

        $RongSDK = new RongCloud(APPKEY,APPSECRET);
        // $user = [
        //     'id'=> 'test1',
        //     'name'=> 'user001',//用户名称
        //     'portrait'=> 'http://7xogjk.com1.z0.glb.clouddn.com/IuDkFprSQ1493563384017406982' //用户头像
        // ];
        $user = [
            'id'=> '1766',//用户 id
            //'blacklist'=> ['18601'] //需要移除黑名单的人员列表
        ];
        //$res = $RongSDK->getUser()->register($user);
        // $update = $RongSDK->getUser()->update($user);
        // $res = $RongSDK->getUser()->MuteGroups()->getList(['test1']);
        //$res = $RongSDK->getUser()->Blacklist()->add($user);	//加入黑名单
        //$res = $RongSDK->getUser()->Blacklist()->remove($user);	//移出黑名单
        $res = $RongSDK->getUser()->Blacklist()->getList($user);//黑名单列表
        $res = $RongSDK->getUser()->Onlinestatus()->check($user);
        $this->ApiReturn(1,'',$res);
    }

    public function ry_test(){
        set_time_limit(0);
        $user_id = input('user_id/d',0);
        $data=DB::name('users')->where(['status'=>['neq',4]])->where('id=1103')->order('id asc')->column('id');
        //print_r($data);die;
        $i=0;
        $str = '';
        foreach ($data as $k => &$v) {
            $status = $this->ryHand($v);
            if ($status == 1){
                $str .= $v.',';
            }
            
        }
        echo $str;die;
        $this->ApiReturn(1,'',$i);
    }


    //融云
    protected function ryHand($ry_uid){
        import('RongCloud/RongCloud', VENDOR_PATH);
        $AppKey = $this->getConfig('ry_app_key');
        $AppSecret = $this->getConfig('ry_app_secret');
        $RongSDK = new \RongCloud\RongCloud($AppKey,$AppSecret);
        $user = [
            'id'=> $ry_uid,
        ];
        $res = $RongSDK->getUser()->Onlinestatus()->check($user);
        if($res['code'] == 200){
            return $res['status'];
        }else{
            return 0;
        }
    }


    
/***********************************************************************************************************************/

    //检测字符串是否是汉字
    public function isAllChinese($str){
        //新疆等少数民族可能有·
        if(strpos($str,'·')){
            //将·去掉，看看剩下的是不是都是中文
            $str=str_replace("·",'',$str);
            if(preg_match('/^[\x7f-\xff]+$/', $str)){
                return true;//全是中文
            }else{
                return false;//不全是中文
            }
        }else{
            if(preg_match('/^[\x7f-\xff]+$/', $str)){
                return true;//全是中文
            }else{
                return false;//不全是中文
            }
        }
    }


    // 一键生成奖池数据(打乱排放)
    public function create_award(){
        $data=DB::name('award_box')->where(['box_type'=>1])->select();
        $res=[];
        foreach ($data as $key => &$v) {
            for ($i=0; $i <$v['num'] ; $i++) { 
                $res[]=$v;
            }
        }
        $count=count($res);
        $query = Db::name('award')->query('TRUNCATE TABLE b_award');
        $re1=[];
        for ($i=0; $i <$count ; $i++) { 
            $key=array_rand($res);
            $val=$res[$key];
            $re1[]=$res[$key];
            $arr = array(
                'num'     => $val['num'],
                'status'   =>1,
                'wares_id' => $val['wares_id'],
                'type'     => $val['type'],
                'class'    => $val['box_type'],
                'term'      =>5,
                'award_box_id'=>$val['id'],
                'img'     => $val['img'],
                'addtime' =>time(),
            );   
            Db::name('award')->insert($arr); 
            unset($res[$key]);
        }
        dump($re1);exit;
    }


    //检测文件夹下文件是否被使用
    public function check_file_use(){
        $query = Db::name('config')->query('TRUNCATE TABLE b_award');
        $this->ApiReturn(1,'12342');
        set_time_limit(0);
        $res2=[];
        $dir  =  ROOT_PATH . 'public/upload/audio';
        //开始运行
        $arr_file = array();
        $res = $this->tree($arr_file, $dir);
        foreach ($res as $k => &$v) {
            $is_use=$this->file_is_use($v);
            if(!$is_use)    $res2[]=$v;
        }
        dump($res2);
    }   


    //找出文件夹下所有文件路径
    public function tree(&$arr_file, $directory, $dir_name='') 
    {
        $mydir = dir($directory);
        while($file = $mydir->read())
        {
            if((is_dir("$directory/$file")) AND ($file != ".") AND ($file != ".."))
            {
                $this->tree($arr_file, "$directory/$file", "$dir_name/$file");
            }
            else if(($file != ".") AND ($file != ".."))
            {
                $arr_file[] = "$dir_name/$file";
            }
        }

        $mydir->close();
        return $arr_file;
    }

    //判断文件是否被使用
    public function file_is_use($files= null){
        if(!$files) return false;
        $i=0;
        $where=['like',"%$files%"];
        $files_json=json_encode($files);
        $where2=['like',"%$files_json%"];
        $i += DB::name('active')->where(['img'=>$where])->count();
        $i += DB::name('admin')->where(['avatar'=>$where])->count();
        $i += DB::name('attachment')->where(['url'=>$where])->count();
        $i += DB::name('award_box')->where(['img'=>$where])->count();
        $i += DB::name('award_temp')->where(['img'=>$where])->count();
        $i += DB::name('backgrounds')->where(['img'=>$where])->count();
        $i += DB::name('config')->where(['value'=>$where])->count();
        $i += DB::name('dynamics')->where(['audio|video'=>$where])->count();    //需在单独处理
        $i += DB::name('dynamics')->where(['image'=>$where2])->count();    //需在单独处理
        $i += DB::name('emoji')->where(['emoji'=>$where])->count();
        $i += DB::name('gifts')->where(['img|show_img|show_img2'=>$where])->count();
        $i += DB::name('home_carousels')->where(['img'=>$where])->count();
        $i += DB::name('musics')->where(['music_url'=>$where])->count();
        $i += DB::name('official_messages')->where(['img'=>$where])->count();
        $i += DB::name('officials')->where(['img'=>$where])->count();
        $i += DB::name('report')->where(['img'=>$where])->count();
        $i += DB::name('rooms')->where(['room_cover'=>$where])->count();
        $i += DB::name('topics')->where(['topic_img'=>$where])->count();
        $i += DB::name('user_musics')->where(['music_url'=>$where])->count();
        $i += DB::name('users')->where(['headimgurl'=>$where])->count();
        $i += DB::name('vip')->where(['img'=>$where])->count();
        $i += DB::name('vip_auth')->where(['img_0|img_1'=>$where])->count();
        $i += DB::name('wares')->where(['show_img|img1|img2|img3'=>$where])->count();
        return $i;
    }



}
