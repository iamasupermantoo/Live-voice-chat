<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\usersmanage\Users;
use app\admin\model\officialsmanage\Onepage;
use think\Db;
use vendor\AlibabaCloud\Client\AlibabaCloud;
use vendor\AlibabaCloud\Client\Exception\ClientException;
use vendor\AlibabaCloud\Client\Exception\ServerException;

/**
 * 礼物接口
 */
class Giftqueue extends Api
{
    protected $noNeedLogin = ['tasks_cp','tasks_pack','reset_room_hot','takss_top_room','down_headimg_hand','clear_user_task'];
    protected $noNeedRight = ['*'];

    // 开启CP栏位
    public function open_cp_card(){
        $user_id=$this->user_id;
        $ware=DB::name('wares')->where(['type'=>3,'color'=>'kzk'])->field('id,price')->find();
        $id=$ware['id'];
        // cp数量
        $where['user_id|fromUid']=$user_id;
        $where['status']=1;
        $cp_num=DB::name('cp')->where($where)->count();
        if($cp_num >= 3)   $this->ApiReturn(0,'CP数量已达到上限');
        // cp栏位
        $cp_card=getUserField($user_id,'cp_card');
        if($cp_card >= 3)   $this->ApiReturn(0,'CP栏位数已达到上限');
        // 扩展卡数量
        $where_pack['user_id']=$user_id;
        $where_pack['type']=3;
        $where_pack['target_id']=$id;
        $pack_num=DB::name('pack')->where($where_pack)->value('num') ? : 0;
        // if($pack_num <= 0)  $this->ApiReturn(0,'扩展卡数量不足');

        
        if($pack_num > 0){
            $res=DB::name('pack')->where($where_pack)->setDec('num',1);
            if(($pack_num - 1) <= 0)    DB::name('pack')->where($where_pack)->delete();
        }else{
            $mizuan = getUserField($user_id,'mizuan');
            if($mizuan < $ware['price'])    $this->ApiReturn(0,'米钻余额不足,请前往充值');
            $res=userStoreDec($user_id,$ware['price'],19,'mizuan');      //购买扩展卡
        }

        if($res){
            DB::name('users')->where(['id'=>$user_id])->setInc('cp_card',1);
            $this->ApiReturn(1,'开启位置成功');
        }else{
            $this->ApiReturn(0,'CP栏位开启失败');
        }
    }

    //接受,拒绝cp
    public function handle_cp(){
        $data = $this->request->request();
        $fromUid=$this->user_id;
        $type=$data['type'];
        $id=$this->check_shenqing_cp($fromUid);
        if(!$id)    $this->ApiReturn(0,'参数错误');
        $cp=DB::name('cp')->find($id);
        $user_id=$cp['user_id'];
        if($type == 1){ //接受    判断双方栏位是否充足
            if(!$this->check_cpcard_num($user_id)){
                $this->back_baoshi($user_id,$id);
                $this->ApiReturn(0,'对方cp栏位不足');
            }
            if(!$this->check_cpcard_num($fromUid)){
                $this->back_baoshi($user_id,$id);
                $this->ApiReturn(0,'cp栏位不足');
            }

            //添加宝石发送记录,增加经验值
            $wares=DB::name('wares')->where(['type'=>1,'id'=>$cp['wares_id']])->field('id,name,price')->find();
            $exp=$wares['price'] * $cp['num'];
            $this->add_send_baoshi($user_id,$fromUid,$cp['wares_id'],$cp['num'],$id,$exp);
            $res=DB::name('cp')->where(['id'=>$id])->update(['status'=>1,'agreetime'=>time(),'exp'=>$exp]);
            //解锁vip,cp等级物品,并装扮最新物品
            $this->unlock_wares($user_id,$id);
            $this->unlock_wares($fromUid,$id);
            $this->update_user_total($user_id,4,1);
            $this->update_user_total($fromUid,4,1);

            //宝石收益
            $this->baoshi_income($cp['wares_id'],$cp['uid'],$cp['num'],$wares['name'],$exp,$user_id,$fromUid);

        }elseif($type == 2){    //拒绝
            //返回发起者宝石
            $this->back_baoshi($user_id,$id);
            //添加个人消息
            $user_nick=getUserField($fromUid,'nickname');
            $this->addOfficialMessage('',$user_id,'与'.$user_nick.'结为守护CP失败，宝石已返还至你的背包');
            $res=DB::name('cp')->where(['id'=>$id])->update(['status'=>4,'refusetime'=>time()]);
        }else{
            $this->ApiReturn(0,'参数错误');
        }
        $user_level=$this->getVipLevel($user_id,3);
        $arr['user_nick']=getUserField($user_id,'nickname');
        $arr['user_color']=  getNickColorByVip($user_level);
        $from_level=$this->getVipLevel($fromUid,3);
        $arr['from_nick']=getUserField($fromUid,'nickname');
        $arr['from_color']=  getNickColorByVip($from_level);
        if($res){
            $this->ApiReturn(1,'',$arr);
        }else{
            $this->ApiReturn(0,'操作失败');
        }
    }

    
    //返还发cp申请者宝石
    protected function back_baoshi($user_id,$cp_id){
        $cp=DB::name('cp')->field("id,wares_id,num")->find($cp_id);
        userPackStoreInc($user_id,1,$cp['wares_id'],$cp['num'],4,9);
    }


    //发送宝石
    public function send_baoshi(){
        $data = $this->request->request();
        $data['user_id'] = $this->user_id;
        if(!$data['id'] || !$data['uid'] || !$data['user_id'] || !$data['fromUid'] || !$data['num'])
            $this->ApiReturn(0,'缺少参数',$data);
        if($data['num'] < 1)    $this->ApiReturn(0,'参数错误');
        $baoshi=DB::name('wares')->where(['id'=>$data['id'],'type'=>1,'enable'=>1])->find();
        if(!$baoshi)    $this->ApiReturn(0,'参数错误');

        //房间内人员
        $roomVisitor=DB::name('rooms')->where(['uid'=>$data['uid']])->value('roomVisitor');
        $vis_arr=explode(",", $roomVisitor);
        $vis_arr=array_merge($vis_arr,[$data['uid']]);
        $from_arr=explode(',', $data['fromUid']);
        $arr=array();
        foreach ($from_arr as $k1 => &$v) {
            // $user_data=DB::name('users')->where(['id'=>$v])->value('id');
            // if(!$user_data)    $this->ApiReturn(0,'用户不存在');
            if($data['user_id'] == $v)     $this->ApiReturn(0,'不能送给自己礼物');
            //是否在同一房间
            if(!in_array($data['user_id'], $vis_arr) || !in_array($v,$vis_arr))   $this->ApiReturn(0,'双方不在同一房间');
            //申请中的不能重复申请
            $cp_info=DB::name('cp')->where(['user_id'=>$data['user_id'],'fromUid'=>$v])->find();
            if($cp_info && $cp_info['status'] == 3)  $this->ApiReturn(0,'请勿重复发送申请');

            //被拒绝24小时内无法发送申请
            if($cp_info && $cp_info['status'] == 4 && ( time() - $cp_info['refusetime']) < 86400){
              $this->ApiReturn(0,'被拒绝24小时内无法发送申请');
            }

            $cp_id=$this->check_first_cp($data['user_id'],$v,1) ? : 0;
            if(!$cp_id){
                // 接受者栏位
                if(!$this->check_cpcard_num($v)) $this->ApiReturn(0,'对方cp栏位不足');
                // 发送者栏位
                if(!$this->check_cpcard_num($data['user_id'])) $this->ApiReturn(0,'cp栏位不足');
            }
            //双发是否第一次发送
            $arr[$v]['is_first']= $cp_id ? 0 : 1;
            $arr[$v]['cp_id']= $cp_id;
        }
        unset($v);

        //查看背包中是否有此宝石
        $bs_num=DB::name('pack')->where(['user_id'=>$data['user_id'],'type'=>1,'target_id'=>$data['id']])->value('num') ? : 0;
        $sum=$data['num'] * count($from_arr);
        if($baoshi['get_type'] == 4){   //可购买宝石
            if($bs_num <= $sum){
                // 删除宝石,扣除米钻
                $yu_num = $sum - $bs_num;
                $mizuan = $yu_num * $baoshi['price'];
                $sum_mizuan = DB::name('users')->where(['id'=>$data['user_id']])->value('mizuan');
                $sum_mizuan = getUserField($data['user_id'],'mizuan');
                if($sum_mizuan < $mizuan)   $this->ApiReturn(0,'米钻余额不足,请前往充值');
            }
        }else{      //非可购买宝石
            if($bs_num < $sum)  $this->ApiReturn(0,'宝石数量不足');    
        }



        Db::startTrans();
        try{
        

            //查看背包中是否有此宝石
            $pack_baoshi=DB::name('pack')->where(['user_id'=>$data['user_id'],'type'=>1,'target_id'=>$data['id']])->find();
            $sum=$data['num'] * count($from_arr);
            if($baoshi['get_type'] == 4){   //可购买宝石
                if($pack_baoshi['num'] > $sum){
                    //物品使用记录 //减去宝石
                    userPackStoreDec($data['user_id'],1,$data['id'],$sum);
                }else{
                    // 删除宝石,扣除米钻
                    $yu_num = $sum - $pack_baoshi['num'];
                    $mizuan = $yu_num * $baoshi['price'];
                    $sum_mizuan = DB::name('users')->where(['id'=>$data['user_id']])->value('mizuan');
                    if($sum_mizuan < $mizuan)   $this->ApiReturn(0,'米钻余额不足,请前往充值');

                    if($mizuan > 0) userStoreDec($data['user_id'],$mizuan,17,'mizuan');      //购买宝石
                    //物品使用记录
                    userPackStoreDec($data['user_id'],1,$data['id'],$pack_baoshi['num']);
                }
            }else{      //非可购买宝石
                if($pack_baoshi['num'] < $sum)  $this->ApiReturn(0,'宝石数量不足');    
                //物品使用记录  //减去宝石
                userPackStoreDec($data['user_id'],1,$data['id'],$sum);
            }

            
            $res=array();
            foreach ($arr as $k => &$va) {
                if($va['is_first']){
                    $info['uid']=$data['uid'];
                    $info['wares_id']=$data['id'];
                    $info['num']=$data['num'];
                    $info['user_id']=$data['user_id'];
                    $info['fromUid']=$k;
                    $info['status']=3;
                    $info['addtime']=time();
                    DB::name('cp')->insertGetId($info);
                }else{
                    $exp=$data['num'] * $baoshi['price'];
                    DB::name('cp')->where(['id'=>$va['cp_id'],'status'=>1])->setInc('exp',$exp);
                    $this->add_send_baoshi($data['user_id'],$k,$data['id'],$data['num'],$va['cp_id'],$exp);
                    //解锁vip,cp等级物品,并装扮最新物品
                    $this->unlock_wares($data['user_id'],$va['cp_id']);
                    $this->unlock_wares($k,$va['cp_id']);
                    $this->update_user_total($data['user_id'],4,1);
                    $this->update_user_total($k,4,1);

                    //添加宝石收益
                    $this->baoshi_income($data['id'],$data['uid'],$data['num'],$baoshi['name'],$exp,$data['user_id'],$k);
                }
                $level= $this->getVipLevel($k,3);
                $res_arr['nick_color'] = getNickColorByVip($level);
                $res_arr['is_first'] = $va['is_first'];
                $user=DB::name('users')->field('id,nickname,headimgurl')->find($k);
                $res_arr['userId']=$k;
                $res_arr['nickname']=$user['nickname'];
                $res_arr['headimgurl']=$this->auth->setFilePath($user['headimgurl']);
                $res[]=$res_arr;
            }
            unset($va);

        //提交事务
            Db::commit();
        } catch (\Exception $e) {
            //回滚事务
            Db::rollback();
            $this->ApiReturn(0,'宝石发送失败');
        }
        $this->ApiReturn(1,'宝石发送成功',$res);
    }

    //添加宝石收益
    protected function baoshi_income($id,$uid,$num,$name,$price,$user_id,$fromUid){
        $info['giftId']=$id;
        $info['uid']=$uid;
        $info['giftNum']=$num;
        $info['giftName']=$name;
        $info['giftPrice']=$price;
        $info['user_id']=$user_id;
        $info['fromUid']=$fromUid;
        $info['is_play']=1;
        $info['type']=1;
        $info['created_at']=$info['updated_at']=date('Y-m-d H:i:s',time());


        //计算分成
        $income=$this->calculate($uid,$fromUid,$info['giftPrice']);
        $info['platform_obtain']=$income['platform'];   //平台
        $info['fromUid_obtain']=$income['fromUid'];     //收礼物者
        $info['uid_obtain']=$income['uid']+$income['uid_yj'];//房主

        $res=DB::name('gift_logs')->insertGetId($info);
        if($res){
            if($income['uid'] > 0)  userStoreInc($uid,$income['uid'],31,'r_mibi');  //房间流水
            if($income['uid_yj'] > 0)  userStoreInc($uid,$income['uid_yj'],32,'r_mibi');    //房主下级分成,佣金
            if($income['fromUid'] > 0)  userStoreInc($fromUid,$income['fromUid'],21,'mibi');//收礼物
            if($income['platform'] > 0)  userStoreInc(0,$income['fromUid'],99,'mibi');  //平台所得
            //增加房间热度
            $this->addRoomHot($uid,$info['giftPrice']);
            //房间流水
            $this->update_user_total($uid,1,$info['giftPrice']);
            // 送出总额
            $this->update_user_total($user_id,2,$info['giftPrice']);
            // 收到总额
            $this->update_user_total($fromUid,3,$info['giftPrice']);

            //数值玩法
            $play_num=Db::name('rooms')->where(['uid'=>$uid])->value('play_num');
            if($play_num == 1){
                $this->add_play_num($uid,$fromUid,$info['giftPrice']);
            }
            return 1;
        }else{
            return 0;
        }
    }


    //添加宝石发送记录
    protected function add_send_baoshi($user_id,$fromUid,$id,$num,$cp_id,$exp){
        $info['user_id']=$user_id;
        $info['fromUid']=$fromUid;
        $info['wares_id']=$id;
        $info['num']=$num;
        $info['cp_id']=$cp_id;
        $info['exp']=$exp;
        $info['addtime']=time();
        DB::name('gem_log')->insertGetId($info);
    }



    //发送爆音卡
    public function send_byk(){
        $data = $this->request->request();
        $data['user_id'] = $this->user_id;
        if( !$data['uid'] || !$data['user_id'] || !$data['fromUid'] || !$data['num'] )
            $this->ApiReturn(0,'缺少参数',$data);
        if($data['num'] < 1)    $this->ApiReturn(0,'参数错误');

        $from_arr=explode(',', $data['fromUid']);
        foreach ($from_arr as $k1 => &$v1) {
            $user_data=DB::name('users')->field(['id'])->where(['id'=>$v1])->find();
            if(!$user_data)    $this->ApiReturn(0,'用户不存在');
            if($data['user_id'] == $v1)     $this->ApiReturn(0,'不能送给自己');
        }
        unset($v1);
        $wares_id=DB::name('wares')->where(['type'=>3,'color'=>'byk'])->value('id');
        $pack_byk_num=DB::name('pack')->where(['type'=>3,'user_id'=>$data['user_id'],'target_id'=>$wares_id])->value('num');
        //总发送数量
        $send_num=$data['num'] * count($from_arr);
        if($pack_byk_num < $send_num)   $this->ApiReturn(0,'爆音卡数量不足');
        $i=0;
        $res=[];
        foreach ($from_arr as $k => &$v) {
            $i++;
            $info['uid']=$data['uid'];
            $info['user_id']=$data['user_id'];
            $info['fromUid']=$v;
            $info['wares_id']=$wares_id;
            $info['num']=$data['num'];
            $info['addtime']=time();
            DB::name('baoyinka')->insertGetId($info);

            $level= $this->getVipLevel($v,3);
            $res_arr['nick_color'] = getNickColorByVip($level);
            $res_arr['is_first'] = 0;
            $user=DB::name('users')->field('id,nickname,headimgurl')->find($v);
            $res_arr['userId']=$v;
            $res_arr['nickname']=$user['nickname'];
            $res_arr['headimgurl']=$this->auth->setFilePath($user['headimgurl']);
            $res[]=$res_arr;
        }
        unset($v);

        if($i == count($from_arr)){
            //物品使用记录
            userPackStoreDec($data['user_id'],3,$wares_id,$send_num);
            $this->ApiReturn(1,'爆音卡发送成功',$res);
        }else{
            $this->ApiReturn(0,'爆音卡发送失败');
        }
    }




/***************************************************** 定 时 任 务 **************************************************************************/



    

    //定时任务--处理cp_每分钟
    public function tasks_cp(){
        $where['status']=['in','3,4'];
        $data=DB::name('cp')->where($where)->select();
        if(!$data) return 0;
        foreach ($data as $k => &$v) {
            if($v['status'] == 3){
                if((time() - $v['addtime']) > 86400 ){
                    //拒绝
                    DB::name('cp')->where(['id'=>$v['id']])->update(['status'=>4,'refusetime'=>time()]);
                    // 返回发起者宝石
                    $this->back_baoshi($v['user_id'],$v['id']);
                    //添加个人消息
                    $user_nick=getUserField($v['user_id'],'nickname');
                    $this->addOfficialMessage('',$v['user_id'],'与'.$user_nick.'结为守护CP失败，宝石已返还至你的背包');
                }
            }elseif($v['status'] == 4){
                if((time() - $v['refusetime']) > 86400 ){
                    //删除记录
                    DB::name('cp')->where(['id'=>$v['id']])->delete();
                }
            }
        }
    }

    //定时任务--处理背包物品_每分钟
    public function tasks_pack(){
        //背包
        DB::name('pack')->where(['num'=>0])->delete();
        $data=DB::name('pack')->where(['expire'=>['neq',0]])->select();
        if(!$data) return 0;
        foreach ($data as $k => &$v) {
            if($v['expire'] > time())   continue;
            $type="dress_".$v['type'];
            if(in_array($v['type'], [4,5,6,7])){
                $dress_id=getUserField($v['user_id'],$type);
                if($dress_id == $v['target_id']){
                    DB::name('users')->where(['id'=>$v['user_id']])->update([$type=>0]);
                }
            }
            DB::name('pack')->delete($v['id']);
        }
        
        //优惠券
        $coupon=Db::name('user_coupons')->where(['status'=>1])->field('id,expire')->select();
        if(!$coupon)    return 0;
        foreach ($coupon as $k1 => &$v1) {
            if($v1['expire'] > time())   continue;
            Db::name('user_coupons')->where(['id'=>$v1['id']])->update(['status'=>3]);
        }
    }

    // 重置房间热度_每日6点
    public function reset_room_hot(){
        $res=DB::name('rooms')->where("hot > 0")->update(['hot'=>0]);
    }


    //定时设置/取消房间置顶
    public function takss_top_room(){
        $hour=date("H");
        $where['start_hour|end_hour']=$hour;
        $data=DB::name('rooms')->where($where)->field('id,start_hour,end_hour')->select();
        foreach ($data as $k => &$v) {
            if($v['start_hour'] == $hour){
                DB::name('rooms')->where(['id'=>$v['id']])->update(['is_top'=>1]);
            }
            if($v['end_hour'] == $hour){
                DB::name('rooms')->where(['id'=>$v['id']])->update(['is_top'=>2]);
            }
        }
    }

    //定时拉下用户头像到本地
    public function down_headimg_hand(){
        $data=Db::name('users')->where(['headimgurl'=>['like','%http%']])->field('id,headimgurl')->limit(50)->select();
        if(!$data) return 0;
        $i=0;
        foreach ($data as $k => &$v) {
            $img=$this->downImgByUrl($v['headimgurl']);
            // $img2=$this->image_thumb($img,1,200,200);
            if($img){
                $info['headimgurl']=$img;
            }else{
                $info['headimgurl']='/upload/cover/default.png';
            }
            $i+=Db::name('users')->where(['id'=>$v['id']])->update($info);
        }
        dump($i);
    }


    /**
     * 定时任务清空用户已完成已领取数据--每日
     */
    public function clear_user_task(){
        Db::name('user_task')->where('1=1')->update(['fin_2'=>null,'receive_2'=>null,'is_open'=>0]);
    }

 public function gift_queue_six()
    {
        setViewNum($this->user_id,1);
        $data=input();
        $data['user_id'] = $this->user_id;
        if(!$data['id'] || !$data['uid'] || !$data['user_id'] || !$data['fromUid'] || !$data['num'] )
            $this->ApiReturn(0,'缺少参数',$data);
        if($data['num'] < 1)    $this->ApiReturn(0,'礼物数量不能小于1');
        $gift=DB::name('gifts')->field(['id','name','type','price','vip_level,is_play,img'])->where(['id'=>$data['id']])->where('enable',1)->find();
        $user=DB::name('users')->field(['id','mizuan'])->where(['id'=>$data['user_id']])->find();
        if(!$gift) $this->ApiReturn(0,'礼物不存在或已下架');
        $room=DB::name('rooms')->where(['uid'=>$data['uid']])->field('id,uid,roomVisitor,play_num')->find();
        if(!$room)  $this->ApiReturn(0,'房间不存在');
        $vis_arr=explode(",",$room['roomVisitor']);
        $vis_arr[]=$data['uid'];
        if(!in_array($data['user_id'],$vis_arr))    $this->ApiReturn(0,'您不在此房间');

        //判断是否能发送
        $vip_level=$this->getVipLevel($data['user_id'],3);
        if($vip_level < $gift['vip_level'])   $this->ApiReturn(0,'vip'.$gift['vip_level'].'才能发送这个礼物');
        $from_arr=explode(',', $data['fromUid']);
        foreach ($from_arr as $k1 => &$v1) {
            if(!in_array($v1,$vis_arr))    $this->ApiReturn(0,'用户不在此房间');
            if($data['user_id'] == $v1)     $this->ApiReturn(0,'不能送给自己礼物');
        }
        unset($v1);

        //背包数量
        $pack_gift_num=DB::name('pack')->where(['type'=>2,'user_id'=>$data['user_id'],'target_id'=>$data['id']])->value('num') ? : 0;

        //总发送数量
        $send_num=$data['num'] * count($from_arr);

        if($pack_gift_num > 0){
            if($pack_gift_num <= $send_num){
                //计算所需米钻
                $shengyu_num=$send_num - $pack_gift_num;
                $sum_gift_price=$shengyu_num * $gift['price'];
                if($user['mizuan'] < $sum_gift_price)   $this->ApiReturn(40,'余额不足,请前往充值!');                
            }
        }else{
            $total_price=$gift['price'] * $send_num;
            if($user['mizuan'] < $total_price)   $this->ApiReturn(40,'余额不足,请前往充值!');
        }

        Db::startTrans();
        try{

            if($pack_gift_num > 0){
                if($pack_gift_num > $send_num){
                    //减去背包中礼物数$send_num,不扣米钻
                    userPackStoreDec($data['user_id'],2,$data['id'],$send_num);
                    $shenngyu_price=0;
                }else{
                    //计算所需米钻
                    $shengyu_num=$send_num - $pack_gift_num;
                    $sum_gift_price=$shengyu_num * $gift['price'];
                    if($user['mizuan'] < $sum_gift_price)   $this->ApiReturn(40,'余额不足,请前往充值!');
                    //删除背包中所有礼物数,扣除差额米钻
                    userPackStoreDec($data['user_id'],2,$data['id'],$pack_gift_num);
                    $shenngyu_price=$sum_gift_price;
                }
            }else{
                $total_price=$gift['price'] * $send_num;
                if($user['mizuan'] < $total_price)   $this->ApiReturn(40,'余额不足,请前往充值!');
                $shenngyu_price=$total_price;
            }
            $i=0;
            $res=$push=[];
            foreach ($from_arr as $k => &$v) {
                $i++;
                $this->sendGifts($data['id'],$data['uid'],$data['num'],$gift['name'],$gift['price'],$data['user_id'],$v,0);
                $level= $this->getVipLevel($v,3);
                $res_arr['nick_color'] = getNickColorByVip($level);
                $res_arr['is_first'] = 0;
                $user=DB::name('users')->field('id,nickname,headimgurl')->find($v);
                $res_arr['userId']=$v;
                $res_arr['nickname']=$user['nickname'];
                $res_arr['headimgurl']=$this->auth->setFilePath($user['headimgurl']);
                

                //数值玩法
                if($room['play_num'] == 1){
                    $price = $data['num'] * $gift['price'];
                    $this->add_play_num($data['uid'],$v,$price);
                }
                //播报
                if($gift['is_play'] == 1){
                    $info['uid']=$data['uid'];
                    $info['user_name']=getUserField($data['user_id'],'nickname');
                    $info['from_name']=getUserField($v,'nickname');
                    $info['num']=$data['num'];
                    $info['gift_name']=$gift['name'];
                    $info['img']=$this->auth->setFilePath($gift['img']);
                    $push[]=$info;
                }

                $res[]=$res_arr;
            }
            unset($v);
            if($shenngyu_price >0)     userStoreDec($data['user_id'],$shenngyu_price,13,'mizuan');      //送礼物
            if($i == count($from_arr)){
                //解锁vip,cp等级物品,并装扮最新物品
                $this->unlock_wares($data['user_id']);
                $total_mizuan= $send_num * $gift['price'];
                //房主总流水
                $this->update_user_total($data['uid'],1,$total_mizuan);
                //用户发出总额
                $this->update_user_total($data['user_id'],2,$total_mizuan);
                // 任务--赠送他人3次礼物
                fin_task($data['user_id'],7);
            }
            //提交事务
            Db::commit();
        } catch (\Exception $e) {
            //回滚事务
            Db::rollback();
               dump($e->getMessage());die; //打印错误
            $this->ApiReturn(0,'礼物发送失败');
        }
        $return_arr['users']=$res;
        $return_arr['push']=$push;
        $this->ApiReturn(1,'礼物发送成功',$return_arr);
    }
    


    // 发送礼物 六期之前
    public function gift_queue()
    {
        setViewNum($this->user_id,1);
        $data=input();
        $data['user_id'] = $this->user_id;
        if(!$data['id'] || !$data['uid'] || !$data['user_id'] || !$data['fromUid'] || !$data['num'] )
            $this->ApiReturn(0,'缺少参数',$data);
        if($data['num'] < 1)    $this->ApiReturn(0,'礼物数量不能小于1');
        $gift=DB::name('gifts')->field(['id','name','type','price','vip_level,is_play'])->where(['id'=>$data['id']])->where('enable',1)->find();
        $user=DB::name('users')->field(['id','mizuan'])->where(['id'=>$data['user_id']])->find();
        if(!$gift) $this->ApiReturn(0,'礼物不存在或已下架');

        $room=DB::name('rooms')->where(['uid'=>$data['uid']])->field('id,uid,roomVisitor,play_num')->find();
        if(!$room)  $this->ApiReturn(0,'房间不存在');
        $vis_arr=explode(",",$room['roomVisitor']);
        $vis_arr[]=$data['uid'];
        if(!in_array($data['user_id'],$vis_arr))    $this->ApiReturn(0,'您不在此房间');

        //判断是否能发送
        $vip_level=$this->getVipLevel($data['user_id'],3);
        if($vip_level < $gift['vip_level'])   $this->ApiReturn(0,'vip'.$gift['vip_level'].'才能发送这个礼物');
        $from_arr=explode(',', $data['fromUid']);
        foreach ($from_arr as $k1 => &$v1) {
            if(!in_array($v1,$vis_arr))    $this->ApiReturn(0,'用户不在此房间');
            if($data['user_id'] == $v1)     $this->ApiReturn(0,'不能送给自己礼物');
        }
        unset($v1);

        //背包数量
        $pack_gift_num=DB::name('pack')->where(['type'=>2,'user_id'=>$data['user_id'],'target_id'=>$data['id']])->value('num') ? : 0;
        //总发送数量
        $send_num=$data['num'] * count($from_arr);

        if($pack_gift_num > 0){
            if($pack_gift_num <= $send_num){
                //计算所需米钻
                $shengyu_num=$send_num - $pack_gift_num;
                $sum_gift_price=$shengyu_num * $gift['price'];
                if($user['mizuan'] < $sum_gift_price)   $this->ApiReturn(40,'余额不足,请前往充值!');                
            }
        }else{
            $total_price=$gift['price'] * $send_num;
            if($user['mizuan'] < $total_price)   $this->ApiReturn(40,'余额不足,请前往充值!');
        }

        Db::startTrans();
        try{

            if($pack_gift_num > 0){
                if($pack_gift_num > $send_num){
                    //减去背包中礼物数$send_num,不扣米钻
                    userPackStoreDec($data['user_id'],2,$data['id'],$send_num);
                    $shenngyu_price=0;
                }else{
                    //计算所需米钻
                    $shengyu_num=$send_num - $pack_gift_num;
                    $sum_gift_price=$shengyu_num * $gift['price'];
                    if($user['mizuan'] < $sum_gift_price)   $this->ApiReturn(40,'余额不足,请前往充值!');
                    //删除背包中所有礼物数,扣除差额米钻
                    userPackStoreDec($data['user_id'],2,$data['id'],$pack_gift_num);
                    $shenngyu_price=$sum_gift_price;
                }
            }else{
                $total_price=$gift['price'] * $send_num;
                if($user['mizuan'] < $total_price)   $this->ApiReturn(40,'余额不足,请前往充值!');
                $shenngyu_price=$total_price;
            }
            $i=0;
            $res=[];
            foreach ($from_arr as $k => &$v) {
                $i++;
             //   echo 22;die;
                $this->sendGifts($data['id'],$data['uid'],$data['num'],$gift['name'],$gift['price'],$data['user_id'],$v,$gift['is_play']);
               // echo 1;die;
                $level= $this->getVipLevel($v,3);
                $res_arr['nick_color'] = getNickColorByVip($level);
                $res_arr['is_first'] = 0;
                $user=DB::name('users')->field('id,nickname,headimgurl')->find($v);
                $res_arr['userId']=$v;
                $res_arr['nickname']=$user['nickname'];
                $res_arr['headimgurl']=$this->auth->setFilePath($user['headimgurl']);
                $res[]=$res_arr;

                //数值玩法
                if($room['play_num'] == 1){
                    $price = $data['num'] * $gift['price'];
                    $this->add_play_num($data['uid'],$v,$price);
                }
                
            }
            unset($v);
            if($shenngyu_price >0)     userStoreDec($data['user_id'],$shenngyu_price,13,'mizuan');      //送礼物
            if($i == count($from_arr)){
                //解锁vip,cp等级物品,并装扮最新物品
                $this->unlock_wares($data['user_id']);
                $total_mizuan= $send_num * $gift['price'];
                //房主总流水
                $this->update_user_total($data['uid'],1,$total_mizuan);
                //用户发出总额
                $this->update_user_total($data['user_id'],2,$total_mizuan);
                // 任务--赠送他人3次礼物
                fin_task($data['user_id'],7);
            }
            //提交事务
            Db::commit();
        } catch (\Exception $e) {
            //回滚事务
            Db::rollback();
            
         
            $this->ApiReturn(0,'礼物发送失败');
        }

        $this->ApiReturn(1,'礼物发送成功',$res);

    }


    //执行发送礼物
    protected function sendGifts($id,$uid,$num,$name,$price,$user_id,$fromUid,$is_play){
        $info['giftId']=$id;
        $info['uid']=$uid;
        $info['giftNum']=$num;
        $info['giftName']=$name;
        $info['giftPrice']=$price * $num;
        $info['user_id']=$user_id;
        $info['fromUid']=$fromUid;
        $info['is_play']=$is_play ? 2 : 1;
        $info['type']=2;
        $info['created_at']=$info['updated_at']=date('Y-m-d H:i:s',time());


        //计算分成
        $income=$this->calculate($uid,$fromUid,$info['giftPrice']);
        $info['platform_obtain']=$income['platform'];   //平台
        $info['fromUid_obtain']=$income['fromUid'];     //收礼物者
        $info['uid_obtain']=$income['uid']+$income['uid_yj'];//房主

        $res=DB::name('gift_logs')->insertGetId($info);
        if($res){
            if($income['uid'] > 0)  userStoreInc($uid,$income['uid'],31,'r_mibi');  //房间流水
            if($income['uid_yj'] > 0)  userStoreInc($uid,$income['uid_yj'],32,'r_mibi');    //房主下级分成,佣金
            if($income['fromUid'] > 0)  userStoreInc($fromUid,$income['fromUid'],21,'mibi');//收礼物
            if($income['platform'] > 0)  userStoreInc(0,$income['fromUid'],99,'mibi');  //平台所得
            // userStoreDec($data['user_id'],$total_price,13,'mizuan');      //送礼物
            //增加房间热度
            $this->addRoomHot($uid,$info['giftPrice']);
            //用户收到总额
            $this->update_user_total($fromUid,3,$info['giftPrice']);
            return 1;
        }else{
            return 0;
        }
    }

    //计算各方所得
    protected function calculate($uid,$fromUid,$total){
        $room_user=DB::name('users')->field(['id','is_sign','scale','is_leader'])->where('id',$uid)->select();
        if(!$room_user[0]['is_sign']){//非签约房主
            $data['uid']=0;
            $data['fromUid']=$total * 0.7 ;
            $data['platform']=$total * 0.3 ;
            $data['uid_yj']=0;
        }else{
            //房间流水
            $stream=$total*$room_user[0]['scale']/100;
            //平台
            $platform=$total*(30-$room_user[0]['scale'])/100;
            if($room_user[0]['is_leader']){
                $scale=DB::name('leader')->where('uid',$uid)->where('user_id',$fromUid)->where('status',2)->value('scale') ? : 100;
            }else{
                $scale = 100;
            }
            //收礼物者
            $get_gift=$total * 0.7 * $scale /100;
            $uid_yj = $total * 0.7 * (100 - $scale)/100;

            $data['uid']=$stream;
            $data['fromUid']=$get_gift;
            $data['platform']=$platform;
            $data['uid_yj']=$uid_yj;
        }
        $data=array_map(function($val){
            return round($val*0.1,2);
        }, $data);
        return $data;
    }

    //增加房间热度
    protected function addRoomHot($uid,$hot){
        DB::name('rooms')->where('uid',$uid)->setInc('hot',$hot);
    }

    //开启房间玩法后增加累加数值  四期新增
    protected function add_play_num($uid,$user_id,$price){
        $where['uid']=$uid;
        $where['user_id']=$user_id;
        $data=DB::name('play_num_log')->where($where)->find();
        if(!$data){
            $info['uid']=$uid;
            $info['user_id']=$user_id;
            $info['price']=$price;
            $info['addtime']=time();
            DB::name('play_num_log')->insertGetId($info);
        }else{
            DB::name('play_num_log')->where($where)->setInc('price',$price);
        }

    }


}
