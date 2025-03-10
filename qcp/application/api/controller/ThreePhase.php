<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;


class ThreePhase extends Api
{
	protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

/******************************  四期接口  ***********************************************************************************/

    
    //房间金锐日榜距上一名位置
    public function check_gap(){
        $uid = input('uid/d',0);
        if(!$uid)   $this->ApiReturn(0,'缺少参数');
        $res = $this->check_gap_hand($uid);
        $this->ApiReturn(1,'',['gap'=>$res]);
    }

    public function check_gap_hand($uid,$type = 3,$group = 'uid'){
        if ($type == 1) {
            $time = 'today';
        } elseif ($type == 2) {
            $time = 'week';
        } elseif ($type == 3) {
            $time = 'month';
        }
        $where['uid']= $uid;
        $exp = DB::name('gift_logs')->where($where)->whereTime('created_at', $time)->sum('giftPrice') ? : 0;

        $exp2 = DB::name('gift_logs')->whereTime('created_at', $time)->field("sum(giftPrice) as exp ")->group($group)
                        ->having(" exp >  ".$exp)->order("exp asc")->find()['exp'];
        $res = $exp2 - $exp;
        return (int)$res;
    }

    //开关数值玩法
    public function play_num_switch(){
        $user_id = $this->user_id;
        $uid=input('uid/d',0);
        $type=input('type','');
        $id=DB::name('rooms')->where(['uid'=>$uid])->value('id');
        if(!$id || !in_array($type,['on','off']))    $this->ApiReturn(0,'参数错误');

        //管理员
        $room_admin=DB::name('rooms')->where(['uid'=>$uid])->value('roomAdmin');
        $admin_arr=explode(",", $room_admin);
        //房间人员
        $roomVisitor=DB::name('rooms')->where(['uid'=>$uid])->value('roomVisitor');
        $vis_arr=explode(",", $roomVisitor);
        if($user_id != $uid){
            if(!in_array($user_id, $admin_arr))   $this->ApiReturn(0,'您暂无此操作权限');
            if(!in_array($user_id, $vis_arr))     $this->ApiReturn(0,'请先进入此房间');
        }
        $where['uid']=$uid;
        if($type == 'on'){
            $info['play_num'] = 1;
            $note='开启';
        }elseif($type == 'off'){
            $info['play_num'] = 0;
            $note='关闭';
        }
        $res=DB::name('rooms')->where($where)->update($info);
        if($type == 'off'){
            DB::name('play_num_log')->where($where)->delete(); 
        }
        $this->ApiReturn(1,$note.'成功');
    }

    //获取配置说明
    public function get_shuoming(){
        $name=input('name','');
        if(!$name)  $this->ApiReturn(0,'缺少参数');
        $data['value']=$this->getConfig($name) ? : '';
        $this->ApiReturn(1,'',$data);
    }





/******************************  三期接口  ***********************************************************************************/
    //推荐房间
    public function tuijian_room_three(){
        $limit=input('limit/d',4);
        if(!in_array($limit, [4,16]))    $this->ApiReturn(0,'参数错误');
        $where['a.room_status']=['neq',3];
        $where['a.is_tj']=1;
        $data=DB::name('rooms')->alias('a')
                                ->join('room_categories b','a.room_type = b.id')
                                ->join('users c','a.uid = c.id')
                                ->where($where)
                                ->field('a.room_name,a.uid,a.room_cover,a.hot,b.name,b.pid,c.sex')
                                ->order('a.hot desc')
                                ->limit($limit)
                                ->select();
        foreach ($data as $k => & $v) {
            $v['room_name']= urldecode($v['room_name']);
        }
        $this->ApiReturn(1,'',$data);
    }

    // 活动
    public function active_list_three(){
        $data=DB::name('active')->where(['enable'=>1])->order('sort desc')->field('id,img,url')->select();
        foreach ($data as $k => &$v) {
            $v['url']=$v['url'] ? : '';
            $v['img']=$this->auth->setFilePath($v['img']);
        }
        $this->ApiReturn(1,'',$data);
    }

    // 分类显示房间
    public function room_list_three(){
        $class=input('class/d',0);
        $limit=input('limit/d',4);
        $room_class_ids=DB::name('room_categories')->where(['pid'=>0,'enable'=>1])->column('id');
        if(!in_array($class,$room_class_ids) || !in_array($limit, [4,16]))    $this->ApiReturn(0,'参数错误');
        $where['a.room_status']=['neq',3];
        $where['a.room_class']=$class;
        $data=DB::name('rooms')->alias('a')
                                ->join('room_categories b','a.room_type = b.id')
                                ->join('users c','a.uid = c.id')
                                ->where($where)
                                ->field('a.room_name,a.uid,a.room_cover,a.hot,b.name,b.pid,c.sex')
                                ->order('a.hot desc')
                                ->limit($limit)
                                ->select();
        foreach ($data as $k => & $v) {
            $v['room_name']= urldecode($v['room_name']);
        }
        $this->ApiReturn(1,'',$data);
    }





    // 最佳工会
    public function good_room_three() {
        $data=$this->good_room_hand();
        $this->ApiReturn(1,'',$data);
    }
    
    protected function good_room_hand() {
        $time_arr[]   = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') - 1 , date('Y')));
        $time_arr[]   = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d') -1 , date('Y')));
        $data = DB::name('gift_logs')->alias('a')
                                    ->join('rooms b','a.uid = b.uid')
                                    ->where(['b.room_status'=>['neq',3]])
                                    ->where('a.created_at','between time', $time_arr)
                                    ->field("sum(a.giftPrice) as exp ,a.uid,b.room_cover,b.room_name,b.hot")
                                    ->group('a.uid')
                                    ->order("exp desc")
                                    ->limit(3)
                                    ->select();

        foreach ($data as $k => & $v) {
            $v['room_name']= urldecode($v['room_name']);
            unset($v['exp']);
        }
        unset($v);
        // $kong['img']= '';
        // $kong['name']= '';
        // $kong['uid']= 0;
        // $data[0] = isset($data[0]) ? $data[0] : $kong;
        // $data[1] = isset($data[1]) ? $data[1] : $kong;
        // $data[2] = isset($data[2]) ? $data[2] : $kong;
        return $data;
    }


    //C位推荐
    public function tj_user_three(){
        $user_id=$this->user_id;
        $time_arr[]   = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') - 1 , date('Y')));
        $time_arr[]   = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d') - 1  , date('Y')));
        $ids=array_column($this->good_room_hand(),'uid');
        $data = DB::name('gift_logs')->alias('a')
                                    ->join('users b','a.fromUid = b.id')
                                    ->where(['b.status'=>1])
                                    ->whereNotIn('b.id',$ids)
                                    ->where('a.created_at','between time', $time_arr)
                                    ->group('a.fromUid')
                                    ->field("sum(a.giftPrice) as exp ,b.id,b.nickname,b.headimgurl")
                                    ->order("exp desc")
                                    ->limit(3)
                                    ->select();

        foreach ($data as $k => & $v) {
            $v['headimgurl']= $this->auth->setFilePath($v['headimgurl']);
            $v['is_follow']= IsFollow($user_id,$v['id']);
            unset($v['exp']);
        }
        unset($v);
        //空数据
        // $kong['id']=0;
        // $kong['nickname']='';
        // $kong['headimgurl']='';
        // $data[0] = isset($data[0]) ? $data[0] : $kong;
        // $data[1] = isset($data[1]) ? $data[1] : $kong;
        // $data[2] = isset($data[2]) ? $data[2] : $kong;
        $this->ApiReturn(1,'',$data);
    }

    //新秀
    public function new_user_three(){
        $user_id=$this->user_id;
        $sex=input('sex/d',1);
        $where['sex']=$sex;
        $where['status']=1;
        $time_arr[]   = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') - 7 , date('Y')));
        $time_arr[]   = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d')  , date('Y')));
        $data=DB::name('users')->where($where)
                                ->where('created_at','between time',$time_arr)
                                ->field('id,nickname,headimgurl')
                                ->orderRaw('rand()')
                                ->limit(6)->select();
        foreach ($data as $k => &$v) {
            $v['headimgurl']=$this->auth->setFilePath($v['headimgurl']);
            $v['is_follow']=IsFollow($user_id,$v['id']);
        }
        $this->ApiReturn(1,'',$data);
    }

    


    //删除评论
    public function del_comments_three(){
        $user_id=$this->user_id;
        $id=input('id/d',0);
        $where['id']=$id;
        $where['user_id']=$user_id;
        $comment=DB::name('dynamic_comments')->where($where)->find();
        if(!$comment)   $this->ApiReturn(0,'评论不存在');
        $res=DB::name('dynamic_comments')->where($where)->delete();
        if($res){
            DB::name('likes')->where(['type'=>4,'target_id'=>$id])->delete();
            $this->ApiReturn(1,'评论删除成功!');
        }else{
            $this->ApiReturn(0,'评论删除失败,请刷新后重试!');
        }
    }    

    
    
    //房间分类
    public function room_type_three(){
        $rooms_cate=DB::name('room_categories')->field('id,pid,name')->where(['enable'=>1,'pid'=>0])->select();
        foreach ($rooms_cate as $kr => &$vr) {
            $data=DB::name('room_categories')->field('id,pid,name')->where(['enable'=>1,'pid'=>$vr['id']])->select();
            $vr['children']=$data;
        }
        $this->ApiReturn(1,'',$rooms_cate);
    }

    

    //修改房间信息
    public function edit_room_three(){
        $info = $this->request->request();
        // $this->ApiReturn(1,'',$info);
        $uid = $this->request->request('uid');
        //封面
        if($info['cover']){
            $cover = $this->base64_image_content($info['cover'],2);
            $this->SensitiveImage($cover);
            if($cover)  $arr['room_cover'] = $this->auth->setFilePath($cover);
        }
        //房间公告
        if($info['room_intro']){
            $this->SensitiveWords($info['room_intro']);
            $arr['room_intro']=$info['room_intro'];
        }
        //房间名称
        if(isset($info['room_name']) && $info['room_name']){
            $arr['room_name'] = urlencode($info['room_name']);
        }
      
        //密码
        $arr['room_pass'] = input('room_pass','');
        //背景
        $room_background = input('room_background/d',0);
        if($room_background)    $arr['room_background'] = $room_background;
        //房间类型
        $room_class = input('room_class/d',0);
        if($room_class){
            $class_ids=DB::name('room_categories')->where(['pid'=>0,'enable'=>1])->column('id');
            if(!in_array($room_class, $class_ids))  $this->ApiReturn(0,'房间分类不存在');
            $arr['room_class']=$room_class;
        }
        $room_type = input('room_type/d',0);
        if($room_type){
            $type_ids=DB::name('room_categories')->where(['pid'=>$room_class,'enable'=>1])->column('id');
            if(!in_array($room_type, $type_ids))  $this->ApiReturn(0,'房间子分类不存在');
            $arr['room_type']=$room_type;
        }

        $res = DB::name('rooms')->where(['uid'=>$uid])->update($arr);
        $room_cover = DB::name('rooms')->field(['room_cover'])->where(['uid'=>$uid])->select();
        $this->ApiReturn(1,'修改成功',$room_cover);
    }




























    // 修改一期房间分类
    public function update_room_type(){
        $data=DB::name('rooms')->field('id,room_type,room_class')->select();
        $i=0;
        foreach ($data as $k => &$v) {
            if($v['room_type'] == 1){
                $info['room_class']=2;
                $info['room_type']=11;
            }elseif($v['room_type'] == 2){
                $info['room_class']=3;
                $info['room_type']=16;
            }elseif($v['room_type'] == 3){
                $info['room_class']=1;
                $info['room_type']=9;
            }elseif($v['room_type'] == 4){
                $info['room_class']=1;
                $info['room_type']=4;
            }elseif($v['room_type'] == 5){
                $info['room_class']=1;
                $info['room_type']=4;
            }elseif($v['room_type'] == 6){
                $info['room_class']=1;
                $info['room_type']=8;
            }
            $i+=DB::name('rooms')->where(['id'=>$v['id']])->update($info);
        }
        $this->ApiReturn(1,'',$i);
    }








    
    //删除用户所有记录
    public function shanchu_user_all_qiyong(){
        return false;
        $user_id=input('delete_user_id/d',0);
        if(!$user_id)    return false;
        $where_1['user_id']=$user_id;

        $where_2['user_id|fromUid']=$user_id;

        $where_3['user_id|followed_user_id']=$user_id;

        $where_4['uid|user_id|fromUid']=$user_id;

        DB::name('award_log')->where($where_1)->delete();       //开奖记录
        DB::name('baoyinka')->where($where_2)->delete();        //爆音卡发送记录
        DB::name('black')->where(['user_id|from_uid'=>$user_id])->delete();           //黑名单
        DB::name('cp')->where($where_2)->delete();              //CP
        DB::name('exchange')->where($where_1)->delete();        //米币兑换米钻
        DB::name('face_order')->where($where_1)->delete();      //脸部识别认证记录
        DB::name('feedback')->where($where_1)->delete();        //反馈
        DB::name('follows')->where($where_3)->delete();         //关注
        DB::name('gem_log')->where($where_2)->delete();         //宝石发送记录
        DB::name('gift_logs')->where($where_4)->delete();       //礼物发送记录
        DB::name('off_reads')->where($where_1)->delete();       //消息阅读记录
        DB::name('official_messages')->where($where_1)->delete();//系统通知
        DB::name('order')->where($where_1)->delete();           //订单
        DB::name('pack')->where($where_1)->delete();            //背包
        DB::name('pack_log')->where($where_1)->delete();        //背包记录
        DB::name('points_exchange')->where($where_1)->delete(); //积分兑换
        DB::name('points_log')->where($where_1)->delete();      //积分明细
        DB::name('report')->where($where_1)->delete();          //举报记录
        DB::name('rooms')->where(['uid'=>$user_id])->delete();  //房间
        DB::name('search_history')->where($where_1)->delete();  //搜索历史
        DB::name('store_log')->where($where_1)->delete();       //钱包记录
        DB::name('tixian')->where($where_1)->delete();          //提现
        DB::name('user_musics')->where($where_1)->delete();     //用户音乐
        DB::name('user_token')->where($where_1)->delete();      //用户token
        DB::name('users')->where(['id'=>$user_id])->delete();   //用户
        DB::name('wait')->where($where_1)->delete();            //排麦


        //删除当前用户评论,回复,点赞评论的数据
        $comment=DB::name('dynamic_comments')->where(['hf_uid|user_id'=>$user_id])->select();
        $this->delete_comment($comment);

        //删除当前用户发布过的动态,动态下的评论数据,(用户点赞,收藏,转发过我动态的数据)
        $dynamics=DB::name('dynamics')->where($where_1)->select();
        foreach ($dynamics as $k => &$v) {
            $pinglun=DB::name('dynamic_comments')->where(['b_dynamic_id'=>$v['id']])->select();
            $this->delete_comment($pinglun);
            DB::name('likes')->where(['type'=>['in','1,2,3'],'target_id'=>$v['id']])->delete();
        }
        DB::name('likes')->where($where_1)->delete();           //点赞,收藏,评论,转发动态||当前用户操作的,和其他用户操作当前用户动态的
        $this->ApiReturn(1,'执行成功',rand(1000,9999));
    }

    protected function delete_comment($data=array()){
        foreach ($data as $k => &$v) {
            DB::name('likes')->where(['type'=>4,'target_id'=>$v['id']])->delete();
            DB::name('dynamic_comments')->where(['id'=>$v['id']])->delete();
        }
    }

















    
	
}