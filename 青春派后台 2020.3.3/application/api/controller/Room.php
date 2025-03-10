<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Validate;
use think\Db;
use app\admin\model\usersmanage\Users;

/**
 * 房间接口
 */
class Room extends Api
{
    protected $noNeedLogin = ["ranking"];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }


		
    /**********************************二期新增***************************************************/
    //房间排行榜 (四期使用,已兼容)
    public function room_ranking() {
    
        $class = $this->request->request('class')  ? : 0; //1贡献榜(金锐)2总榜3星锐
        $type = $this->request->request('type') ? : 1; //1日榜2周榜3月榜
        $uid = input('uid/d',0);
        if ( !in_array($class, [1,2,3]) || !in_array($type, [1, 2, 3])) $this->error('参数错误');

        $room = DB::name('rooms')->where(['uid'=>$uid])->value('id');
        if (!$room) $this->error('房间不存在');
        if ($class == 1) {
            $where['uid']=$uid;
            $keywords='user_id';
        } elseif ($class == 2) {
            $where="1=1";
            $keywords='uid';
        }elseif($class == 3){
            $where['uid']=$uid;
            $keywords='fromUid';
        }

        if ($type == 1) {
            $time = 'today';
        } elseif ($type == 2) {
            $time = 'week';
        } elseif ($type == 3) {
            $time = 'month';
        }
        $query = DB::name('gift_logs')->where($where)->whereTime('created_at', $time);
        $data=$query->field("sum(giftPrice) as exp ,". $keywords)->group($keywords)->order("exp desc")->limit(30)->select();
        $i=0;
        $info=[];
        if(in_array($class, [1,3]) ){
            foreach ($data as $k => & $v) {
                $i++;
                $user = DB::name('users')->field('headimgurl,nickname,sex')->find($v[$keywords]);
                $sm_arr['img']= $this->auth->setFilePath($user['headimgurl']);
                $sm_arr['sort']= $i;
                $sm_arr['name']= $user['nickname'];
                $sm_arr['id']= $v[$keywords];
                $sm_arr['mizuan']= ceil($v['exp']);
                $sm_arr['sex']= $user['sex'];
                $info[]=$sm_arr;
            }
            unset($v);
        }elseif($class == 2){
            foreach ($data as $k => & $v) {
                $i++;
                $room = DB::name('rooms')->where(['uid'=>$v[$keywords]])->field('uid,numid,room_name,room_cover')->find();
                $sm_arr['img']= $room['room_cover'];
                $sm_arr['sort']= $i;
                $sm_arr['name']= urldecode($room['room_name']);
                $sm_arr['id']= $room['numid'];
                $sm_arr['mizuan']= ceil($v['exp']);
                $sm_arr['sex']= getUserField($v[$keywords],'sex');
                $info[]=$sm_arr;
            }
            unset($v);
        }
        $kong['img']= '';
        $kong['sort']= 0;
        $kong['name']= '';
        $kong['id']= '';
        $kong['mizuan']= '';
        $kong['sex']= 0;
        $info[0] = isset($info[0]) ? $info[0] : $kong;
        $info[1] = isset($info[1]) ? $info[1] : $kong;
        $info[2] = isset($info[2]) ? $info[2] : $kong;

        $arr['top'] = array_slice($info, 0, 3);
        $arr['other'] = array_slice($info, 3);
        $this->ApiReturn(1,'',$arr);
    }

    // 获取房间内所有用户
    public function get_room_users(){
        $uid = $this->request->request('uid');
        $user_id = $this->request->request('user_id');
        $rooms  =DB::name('rooms')->where(['uid'=>$uid])->value('id');
        if(!$rooms)   $this->ApiReturn(0,'房间不存在');
        if($uid == $user_id)    $this->ApiReturn(0,'对房主无操作权限');
        //麦上人员
        $microphone=DB::name('rooms')->where(['uid'=>$uid])->value('microphone');
        $mic_arr=explode(',', $microphone);
        foreach ($mic_arr as $k => &$v) {
            if($v == 0 || $v == -1 || $v == $uid)   unset($mic_arr[$k]);
        }
        $roomVisitor=DB::name('rooms')->where(['uid'=>$uid])->value('roomVisitor');
        $vis_arr=explode(",",$roomVisitor);
        if($user_id && !in_array($user_id,$vis_arr))    $this->ApiReturn(0,'用户不在此房间');
        $sea_user=array();
        $vis_arr=array_diff($vis_arr,$mic_arr);
        $mic_user=Db::name('users')->where('id',"in",$mic_arr)->field("id,nickname,headimgurl")->select();
        foreach ($mic_user as $k => &$v){
            $v['headimgurl']=$this->auth->setFilePath($v['headimgurl']);
            $v['is_mic']=1;
            if($user_id == $v['id'])  $sea_user=$v;
        }
        unset($v);
        //房间内人员
        $room_user=Db::name('users')->where('id',"in",$vis_arr)->field("id,nickname,headimgurl")->select();
        foreach ($room_user as $k1 => &$v1){
            $v1['headimgurl']=$this->auth->setFilePath($v1['headimgurl']);
            $v1['is_mic']=0;
            if($user_id == $v1['id']) $sea_user=$v1;
        }
        unset($v1);

        $data['mic_user']= empty($user_id) ? $mic_user : [];
        $data['room_user']=empty($user_id) ? $room_user : [];
        $data['sea_user']=$sea_user ? [$sea_user] : [];
        $this->ApiReturn(1,'',$data);
    }
























    /**********************************二期新增***************************************************/
    //获取房间信息
    public function getRoomInfo(){
        $uid = $this->request->request('uid');
        $rooms  =DB::name('rooms')->where(['uid'=>$uid])->select();
        if(!$rooms)   $this->ApiReturn(0,'房间不存在');
        //房间类型
        $rooms_cate=DB::name('room_categories')->field('id,pid,name')->where(['enable'=>1,'pid'=>0])->select();
        foreach ($rooms_cate as $kr => &$vr) {
            $vr['is_check'] = $vr['id']==$rooms[0]['room_class'] ? 1 : 0;
            $data=DB::name('room_categories')->field('id,pid,name')->where(['enable'=>1,'pid'=>$vr['id']])->select();
            foreach ($data as $ke => &$va) {
                $va['is_check'] = $va['id']==$rooms[0]['room_type'] ? 1 : 0;
            }
            $vr['children']=$data;
        }
        unset($vr);
        $back = DB::name('backgrounds')->where('enable',1)->where(['id'=>$rooms[0]['room_background']])->value('img');
        if(!$back){
            $mr_back=DB::name('backgrounds')->where('enable',1)->order('id', 'asc')->limit(1)->select();
            $back=$mr_back[0]['img'];
            $rooms[0]['room_background'] = $mr_back[0]['id'];
        }
        $rooms[0]['back_img'] = $this->auth->setFilePath($back);
        //房间背景
        $backgrounds=DB::name('backgrounds')->where('enable',1)->select();
        foreach ($backgrounds as $k => &$vb) {
            $vb['img'] = $this->auth->setFilePath($vb['img']);
            $vb['is_check']= $vb['id'] == $rooms[0]['room_background'] ? 1 : 0;
        }
        unset($vb);
        $rooms[0]['room_welcome']=$this->getConfig('room_welcome');
        $rooms[0]['room_name'] = urldecode($rooms[0]['room_name']);
        $rooms[0]['rooms_cate'] = $rooms_cate;
        $rooms[0]['backgrounds'] = $backgrounds;
        $rooms[0]['room_pass'] =   $rooms[0]['room_pass'] ? '****' : '';

        $this->ApiReturn(1,'请求成功',$rooms);
    }

    //修改房间信息
    public function edit_room(){
        $info = $this->request->request();
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
        $room_class = input('room_type/d',0);
        if($room_class){
            $class_ids=DB::name('room_categories')->where(['pid'=>0,'enable'=>1])->column('id');
            if(!in_array($room_class, $class_ids))  $this->ApiReturn(0,'房间分类不存在');
            $arr['room_class']=$room_class;
            $arr['room_type'] = DB::name('room_categories')->where(['pid'=>$room_class,'enable'=>1])->value('id');
        }

        $res = DB::name('rooms')->where(['uid'=>$uid])->update($arr);
        $room_cover = DB::name('rooms')->field(['room_cover'])->where(['uid'=>$uid])->select();
        $this->ApiReturn(1,'设置成功',$room_cover);
        
    }

    //我的音乐
    public function user_musics(){
        $user_id=$this->user_id;
        $page=input('page/d',1);
        $keywords=input('keywords','');
        $where['user_id']=$user_id;
        $where['enable']=1;
        if($keywords)   $where['music_name']=['like','%{$keywords}%'];
        $music=DB::name('user_musics')->where($where)->page($page,10)->select();
        foreach ($music as $k => &$v) {
            $v['music_url'] = $this->auth->setFilePath($v['music_url']);
            $v['is_mymusic'] = 1;
        }
        unset($v);
        $this->ApiReturn(1,'',$music);
    }

    //音乐库
    public function local_musics(){
        $info = $this->request->request();
        $keywords = $info['keywords'];
        $page = $info['page'] ? : 0;
        $info['user_id'] = $this->user_id;
        if(!$info['user_id']) $this->ApiReturn(0,'缺少参数');
        $query=DB::name('musics')->where('enable',1)->where('type',1);

        if(isset($keywords) && !empty($keywords)){
            $query=$query->where('music_name', 'like', '%'.$keywords.'%')
                ->whereor('singer', 'like', '%'.$keywords.'%');
        }
        $music=$query->order('id','asc')->page($page,15)->select();
        foreach ($music as $k => &$v) {
            $v['is_mymusic']=DB::name('user_musics')->where('user_id',$info['user_id'])->where('music_url',$v['music_url'])->value('id') ? 1 : 0;
            $v['music_url']=$this->auth->setFilePath($v['music_url']);
        }
        unset($v);
        $this->ApiReturn(1,'',$music);
    }

    //添加到我的音乐
    public function copy_music(){
        $info = $this->request->request();
        $info['user_id'] = $this->user_id;
        if(!$info['user_id'] || !$info['id'])   $this->ApiReturn(0,'缺少参数');
        $music=DB::name('musics')->where(['id'=>$info['id'],'type'=>1,'enable'=>1])->find();
        if(!$music)  $this->ApiReturn(0,'音乐不存在!');
        $my_music_id=DB::name('user_musics')->where('user_id',$info['user_id'])->where('music_url',$music['music_url'])->value('id');
        if($my_music_id) $this->ApiReturn(0,'请勿重复添加此音乐!');
        $arr['user_id']=$info['user_id'];
        $arr['music_url']=$music['music_url'];
        $arr['music_size']=$music['music_size'];
        $arr['music_name']=$music['music_name'];
        $arr['singer']=$music['singer'];
        $arr['upload_user']=$music['upload_user'];
        $arr['created_at']=date('Y-m-d H:i:s',time());
        $arr['updated_at']=date('Y-m-d H:i:s',time());
        $res=DB::name('user_musics')->insert($arr);
        if($res){
            $this->ApiReturn(1,'添加成功!');
        }else{
            $this->ApiReturn(0,'添加失败,请刷新后重试!');
        }
    }

    //删除我的音乐
    public function del_user_music(){
        $info = $this->request->request();
        $info['user_id'] = $this->user_id;
        if(!$info['user_id'] || !$info['id'])   $this->ApiReturn(0,'缺少参数');
        $my_music=DB::name('user_musics')->field(['id'])->where('user_id',$info['user_id'])->where('id',$info['id'])->select();
        if(!$my_music)  $this->ApiReturn(0,'请求失败!');
        $res=DB::name('user_musics')->where('user_id',$info['user_id'])->where('id',$info['id'])->delete();
        if($res){
            $this->ApiReturn(1,'删除成功!');
        }else{
            $this->ApiReturn(0,'删除失败,请刷新后重试!');
        }
    }

    //当前音乐,音效
    public function now_music(){
        $info = $this->request->request();
        $info['user_id'] = $this->user_id;
        $first=DB::name('user_musics')->where('enable',1)->where(['user_id'=>$info['user_id']])->order('id','asc')->limit(1)->select();
        $yinxiao=DB::name('musics')->field(['id','music_url','music_name','upload_user','singer'])->where('enable',1)->where('type',2)->select();
        foreach ($yinxiao as $k => &$v) {
            $v['music_url'] = $this->auth->setFilePath($v['music_url']);
        }
        unset($v);
        $data['yinxiao']=$yinxiao;
        if(!$first){
            $data['is_music']=0;
            $data['id']='';
            $data['music_url']='';
            $data['music_name']='';
            $data['upload_user']='';
            $data['singer']='';
            $data['size']="";
            $this->ApiReturn(1,'',$data);
        }
        if($info['id']){
            $now_music=DB::name('user_musics')->where('enable',1)->where(['user_id'=>$info['user_id']])->where(['id'=>$info['id']])->select();
        }
        if(!$info['id'] || !$now_music)  $now_music=$first;
        
        $data['is_music']=1;
        $data['id']=$now_music[0]['id'];
        $data['music_url']=$this->auth->setFilePath($now_music[0]['music_url']);
        $data['size']=$now_music[0]['music_size'];
        // $data['size']=$this->getFileSize($now_music[0]['music_url']);
        $data['music_name']=$now_music[0]['music_name'];
        $data['upload_user']='';
        $data['singer']=$now_music[0]['singer'];
        $this->ApiReturn(1,'',$data);
    }

    //下一曲
    public function next_music(){
        $info = $this->request->request();
        $where['enable'] = 1;
        $info['user_id'] = $this->user_id;
        $where['user_id'] = $info['user_id'];
        $first = DB::name('user_musics')->where($where)->order('id','asc')->limit(1)->find();
        $last  = DB::name('user_musics')->where($where)->order('id','desc')->limit(1)->find();
        if(!$first)     $this->ApiReturn(0,'该用户暂未上传音乐');
        if($info['id'])     $now_music=DB::name('user_musics')->where($where)->where(['id'=>$info['id']])->find();
        if(!$info['id'])    $now_music = $first;

        $now_music['music_url']=$this->auth->setFilePath($now_music['music_url']);

        if($info['class'] == 'list'){
            if($info['type'] == 'prev'){
                $music=DB::name('user_musics')->where($where)->where('id','<',$now_music['id'])
                    ->order('id','desc')
                    ->limit(1)
                    ->select();
                if(!$music)   $music=$last;
            }elseif($info['type'] == 'next'){
                $music=DB::name('user_musics')->where($where)->where('id','>',$now_music['id'])
                    ->order('id','asc')
                    ->limit(1)
                    ->select();
                if(!$music)   $music=$first;
            }else{
                $this->ApiReturn(0,'参数错误');
            }
        }elseif($info['class'] == 'rand'){
            $sum=DB::name('user_musics')->where($where)->count();
            if($sum == 1){
                $music =$first;
            }elseif($sum >= 2){
                $ids=DB::name('user_musics')->where('enable',1)->where(['user_id'=>$info['user_id']])->column('id');
                if(in_array($info['id'], $ids)){
                    $key=array_search($info['id'], $ids);
                    unset($ids[$key]);
                }
                $id=$ids[array_rand($ids,1)];
                $music=DB::name('user_musics')->find($id);
            }
        }else{
            $this->ApiReturn(0,'参数错误');
        }
        $data['is_music']=1;
        $data['music_url']=$this->auth->setFilePath($music['music_url']);
        $data['id']=$music['id'];
        $data['music_name']=$music['music_name'];
        $data['upload_user']='';
        $data['singer']=$music['singer'];
        $data['size']=$music['music_size'];
        $this->ApiReturn(1,'',$data);
    }




    //表情列表
    public function emoji_list(){
        $data=DB::name('emoji')->field(['id','pid','name','emoji','t_length'])->where(['enable'=>1])->where(['pid'=>0])->order('sort','desc')->select();
        foreach ($data as $k => &$v) {
            $v['emoji'] = $this->auth->setFilePath($v['emoji']);
        }
        unset($v);
        $this->ApiReturn(1,'',$data);
    }

    //获取表情
    public function get_emoji(){
        $data = $this->request->request();
        $total=DB::name('emoji')->where('pid',$data['id'])->where(['enable'=>1])->count() - 1;
        if($total < 0 ) $this->ApiReturn(0,'请求失败');
        $skip = mt_rand(0, $total);
        $emoji=DB::name('emoji')->where('pid',$data['id'])->where(['enable'=>1])->orderRaw('rand()')->limit(1)->select();
        if(!$emoji){
            $this->ApiReturn(0,'暂无此表情');
        }else{
            $emoji[0]['emoji'] = $this->auth->setFilePath($emoji[0]['emoji']);
            $emoji[0]['is_answer']=$total;
            $this->ApiReturn(1,'',$emoji);
        }
    }

    //添加排麦
    public function addWaid(){
        $info = $this->request->request();
        if(!$info['uid'] || !$info['user_id'])  $this->ApiReturn(0,'缺少参数');
        $res_arr=$this->waitListHand($info['user_id'],$info['uid']);
        if($info['uid'] == $info['user_id'])    $this->ApiReturn(1,'不能参加自己房间的排麦',$res_arr);
        $data = DB::name('wait')->where('user_id',$info['user_id'])->select();
        if($data) $this->ApiReturn(1,'正在排麦中,请勿重新排麦',$res_arr);
        $arr['uid']     = $info['uid'];
        $arr['user_id'] = $info['user_id'];
        $arr['addtime'] = time();
        $res = DB::name('wait')->insert($arr);
        if($res){
            $res_arr=$this->waitListHand($info['user_id'],$info['uid']);
            $this->ApiReturn(1,'排麦成功',$res_arr);
        }else{
            $this->ApiReturn(0,'排麦失败');
        }
    }

    //去除排麦操作
    public function delWaitHand($user_id = null){
        if(!$user_id) return false;
        $res=DB::name('wait')->where('user_id',$user_id)->delete();
        return $res;
    }

    //去除排麦
    public function delWait(){
        $user_id=$this->user_id;
        if(!$user_id) $this->ApiReturn(0,'缺少参数');
        $res=$this->delWaitHand($user_id);
        if($res){
            $this->ApiReturn(1,'下麦成功');
        }else{
            $this->ApiReturn(0,'下麦失败');
        }
    }

    //排麦列表
    public function waitListHand($user_id = null ,$uid = null){
        $total = DB::name('wait')->where('uid',$uid)->count();
        $data=DB::name('wait')
            ->alias('wait')
            ->where('wait.uid',$uid)
            ->join('users users','wait.user_id=users.id','left')
            ->field(['wait.id','wait.uid','wait.user_id','users.headimgurl','users.nickname'])
            ->order('wait.id','asc')
            ->select();

        $i=$sort=0;
        foreach ($data as $k => &$v) {
            $i++;
            $v['headimgurl']=$this->auth->setFilePath($v['headimgurl']);
            if($v['user_id'] == $user_id){
                $sort = $i;
            }
        }
        unset($v);

        //麦位
        $arr['user_id']='';
        $microphone=DB::name('rooms')->where('uid',$uid)->value('microphone');
        $mic_arr=explode(',', $microphone);

        foreach ($mic_arr as $k => &$vm) {
            if($vm == 0 && $data){
                $arr['user_id']=$data[0]['user_id'];
            }
        }
        $arr['sort']=$sort;
        $arr['total']=$total;
        $arr['data']=$data;
        return $arr;
    }

    //排麦列表
    public function waitList(){
        $info=$this->request->request();
        if( !$info['user_id'] || !$info['uid'])  $this->ApiReturn(0,'缺少参数');
        $res=$this->waitListHand($info['user_id'],$info['uid']);
        $this->ApiReturn(1,'',$res);
    }

    //我收藏的房间
    public function get_mykeep()
    {
        $user_id = $this->user_id;
        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $list = DB::name('users')->where('id',$user_id)->value('mykeep');
        $mykeep = explode(',', $list);
        $room = DB::name('rooms')
            ->field(['t1.numid','t1.uid','t1.room_name','t1.room_cover','t1.hot','t1.is_afk','users.sex','users.nickname'])
            ->alias('t1')
            ->whereIn('t1.uid',$mykeep)
            ->join('users','t1.uid = users.id','left')
            ->select();
        $room=$this->roomDataFormat($room);
        $ar1=$ar2=[];
        foreach ($room as $key => &$v) {
            if($v['is_afk'] == 1){
                $ar1[]=$v;
            }else{
                $ar2[]=$v;
            }
        }
        unset($v);
        $arr['on']  = $ar1;
        $arr['off'] = $ar2;
        $this->ApiReturn(1,'',$arr);
    }

    //进入房间
    public function enter_room()
    {
        $data = $this->request->request();
        $room_pass = $data['room_pass'];
        $uid       = $data['uid'];
        $user_id   = $this->user_id;

        if($uid == $user_id){
            $res=DB::name('users')->where('id',$user_id)->value('is_idcard');
            if(!$res)  $this->ApiReturn(0,'请先完成实名认证');
        }
        $blacks=getUserBlackList($uid);
        if(in_array($user_id, $blacks)) $this->ApiReturn(0,'您已被对方拉黑');
        $room_info = DB::name('rooms')
            ->alias('rooms')
            ->join('users users','rooms.uid = users.id','left')
            ->join('room_categories room_categories','rooms.room_type = room_categories.id','left')
            ->where('rooms.uid',$data['uid'])
            ->field(['rooms.numid','rooms.uid','rooms.room_status','rooms.room_name',
                'rooms.room_cover','room_categories.name','rooms.room_cover','rooms.room_intro',
                'rooms.room_pass','rooms.room_type','rooms.hot','rooms.room_background','rooms.roomAdmin',
                'rooms.roomSpeak','rooms.roomSound','rooms.microphone','rooms.roomJudge','rooms.is_afk',
                'users.nickname','users.headimgurl','users.sex','rooms.roomVisitor','rooms.play_num'])
            ->select();

        if($room_info == null)  $this->ApiReturn(0,'还没有房间，请先创建');

        //退出原来房间
        $room_id=$this->userNowRoom($user_id);
        if($room_id && $room_id != $uid){
            $this->quit_hand($room_id,$user_id);
        }

        if($room_info[0]['room_status'] == 3) $this->ApiReturn(0,'房间已被封禁，请联系客服');
        //进入自己房间
        if($uid== $user_id){
            $res_afk=DB::name('rooms')->where('uid',$uid)->update(['is_afk'=>1]);
            if($res_afk)    $room_info[0]['is_afk']=1;
        }
        //踢出房间的人
        $roomBlack = DB::name('rooms')->where('uid',$uid)->value('roomBlack');
        if(!empty($roomBlack)){
            $is_black = explode(',', $roomBlack);
            foreach ($is_black as $k => &$v) {
                $arr=explode("#",$v);
                $sjc= time() - $arr[1];
                if($sjc < 180 && $arr[0] == $user_id ){
                    $this->ApiReturn(0,'踢出房间后5分钟不能进入');
                }

                if($sjc >= 180){
                    unset($is_black[$k]);
                }
            }
            $roomBlack = implode(",", $is_black);
            DB::name('rooms')->where('uid',$uid)->update(['roomBlack'=>trim($roomBlack,',')]);
        }


        //收到的所有礼物总值
        $room_info[0]['giftPrice'] = DB::name('gift_logs')->where('uid',$data['uid'])->sum('giftPrice');
//在该房间收到礼物的值
        $room_info[0]['ml'] =DB::name('gift_logs')->where("uid='{$data['uid']}' and fromUid='{$data['uid']}'")->sum('giftPrice');

        if($room_info[0]['room_pass'] &&  $uid != $user_id){
            if(!$room_pass) $this->ApiReturn(4,'房间已经上锁，请输入密码',$data['uid']);
            if($room_info[0]['room_pass'] != $room_pass )  $this->ApiReturn(4,'密码不正确，请重新输入');
        }
        //一般用户
        $room_info[0]['user_type'] = 5;
        $roomAdmin = explode(',', $room_info[0]['roomAdmin']);
        for ($i=0; $i < count($roomAdmin); $i++) {
            if($roomAdmin[$i] == $user_id){
                $room_info[0]['user_type'] = 2;//管理员
            }
        }

        $roomJudge = explode(',', $room_info[0]['roomJudge']);
        for ($i=0; $i < count($roomJudge); $i++) {
            if($roomJudge[$i] == $user_id){
                $room_info[0]['user_type'] = 4;//评委
            }
        }
        $is_sound = explode(',', $room_info[0]['roomSound']);
        for ($i=0; $i < count($is_sound); $i++) {
            if($is_sound[$i] == $user_id){
                $room_info[0]['is_sound'] = 2;    //用户禁声
            }else{
                $room_info[0]['is_sound'] = 1;    //用户不禁声
            }
        }
        if($user_id == $data['uid']){
            $room_info[0]['user_type'] = 1;       //房主
        }

        $uid_sound = explode(',', $room_info[0]['roomSound']);
        for ($i=0; $i < count($uid_sound); $i++) {
            if($uid_sound[$i] == $data['uid']){
                $room_info[0]['uid_sound'] = 2;   //房主禁声
            }else{
                $room_info[0]['uid_sound'] = 1;   //房主不禁声
            }
        }

        $uid_black = explode(',', $room_info[0]['roomSpeak']);
        for ($i=0; $i < count($uid_black); $i++) {
            if($uid_black[$i] == $data['uid']){
                $room_info[0]['uid_black'] = 2;   //房主禁止打字
            }else{
                $room_info[0]['uid_black'] = 1;   //房主不禁止打字
            }
        }



        $room_info[0]['headimgurl'] = $this->auth->setFilePath($room_info[0]['headimgurl']);
        $mykeep = DB::name('users')->where('id',$user_id)->value('mykeep');
        $mykeep_arr=explode(",", $mykeep);
        //1已收藏2未收藏
        $room_info[0]['is_mykeep'] = in_array($data['uid'], $mykeep_arr)  ? 1 : 2;

        //房间人数添加
        if($room_info[0]['user_type'] != 1){
            $roomVisitor=$room_info[0]['roomVisitor'];
            $visitor_arr=explode(',',$roomVisitor);
            if(!in_array($user_id, $visitor_arr))   array_unshift($visitor_arr,$user_id);
            $visitor=trim(implode(",", $visitor_arr),",");
            DB::name('rooms')->where('uid',$data['uid'])->update(['roomVisitor'=>$visitor]);
            $room_info[0]['roomVisitor']=$visitor;
        }

        $room_info[0]['room_pass'] = !empty($room_info[0]['room_pass']) ? : '';
        //背景
        $back = DB::name('backgrounds')->where('enable',1)->where(['id'=>$room_info[0]['room_background']])->value('img');
        if(!$back){
            $mr_back=DB::name('backgrounds')->where('enable',1)->order('id', 'asc')->limit(1)->select();
            
            $back=$mr_back[0]['img'];
            $room_info[0]['room_background']=$mr_back[0]['id'];
        }
        $room_info[0]['back_img']=  $this->auth->setFilePath($back);
        $room_info[0]['room_welcome']=  $this->getConfig('room_welcome');
        $room_info[0]['room_name']=  urldecode($room_info[0]['room_name']);

        //房主信息
        $user=DB::name('users')->field('id,dress_4,dress_7')->find($uid);
        $txk=DB::name('wares')->where(['id'=>$user['dress_4']])->value('img1');
        $room_info[0]['txk']=$this->auth->setFilePath($txk);
        $room_info[0]['mic_color']=DB::name('wares')->where(['id'=>$user['dress_7']])->value('color') ? : '#ffffff';
        $ThreePhase = new \app\api\controller\ThreePhase;
        $room_info[0]['gap'] = $ThreePhase->check_gap_hand($uid);

        $this->ApiReturn(1,'进入成功',$room_info);
    }

    //退出房间-执行操作
    public function quit_hand($uid,$user_id){
        $Visitor=DB::name('rooms')->where(['uid'=>$uid])->value('roomVisitor');
        $roomVisitor=explode(',', $Visitor);
        //房主退出房间
        if($uid == $user_id){
            DB::name('rooms')->where('uid',$uid)->update(['is_afk'=>0]);
            // return $Visitor;
        }
        if( $uid != $user_id && !in_array($user_id, $roomVisitor)){
            return $Visitor;
        }
        foreach ($roomVisitor as $k => &$v) {
            if($user_id == $v){
                unset($roomVisitor[$k]);
            }
        }
        $new_visitor=trim(implode(',', $roomVisitor),',');
        DB::name('rooms')->where('uid',$uid)->update(['roomVisitor'=>$new_visitor]);
        //下麦
        $this->go_microphone_hand($uid,$user_id);
        //去除排麦
        $this->delWaitHand($user_id);
        return $new_visitor;
    }

    //下麦--执行操作
    public function go_microphone_hand($uid,$user_id){
        $microphone = DB::name('rooms')->where('uid',$uid)->value('microphone');
        $microphone = explode(',', $microphone);
        if(!$microphone || !in_array($user_id, $microphone)){
            return 0;
        }
        for ($i=0; $i < count($microphone); $i++) {
            if($microphone[$i] == $user_id){
                $position = $i;
            }
        }
        $microphone[$position] = 0;
        $microphone = implode(',', $microphone);
        $result = DB::name('rooms')->where('uid',$uid)->update(['microphone'=>$microphone]);
        return $result;
    }

    //获取房间人员
    public function getRoomUsers(){
        $data = $this->request->request();
        $uid = $this->request->request('uid');
        $roomAdmin=DB::name('rooms')->where(['uid'=>$uid])->value('roomAdmin');
        $roomAdmin=explode(',',$roomAdmin);
        $admin=DB::name('users')->field(['id','nickname','headimgurl'])->where('id','in', $roomAdmin)->select();
        for($i=0;$i < count($admin); $i++){
            $admin[$i]['headimgurl'] = $this->auth->setFilePath($admin[$i]['headimgurl']);
            $admin[$i]['is_admin'] = 1;
        }

        $roomVisitor=DB::name('rooms')->where(['uid'=>$uid])->value('roomVisitor');
        $roomVisitor=explode(',',$roomVisitor);
        $roomVisitor=array_values(array_diff($roomVisitor,$roomAdmin));
        $visitor=DB::name('users')->field(['id','nickname','headimgurl'])->where('id','in', $roomVisitor)->select();
        for($j=0;$j < count($visitor); $j++){
            $visitor[$j]['headimgurl'] = $this->auth->setFilePath($visitor[$j]['headimgurl']);
            $visitor[$j]['is_admin'] = 0;
        }
        $res['room_id']=$uid;
        $res['admin']=$admin;
        $res['visitor']=$visitor;
        $this->ApiReturn(1,'',$res);
    }

    //退出房间
    public function quit_room(){
        $data = $this->request->request();
        $user_id=$this->user_id;
        $uid=$data['uid'];
        $res=$this->quit_hand($uid,$user_id);
        $this->ApiReturn(1,'',$res);
    }













    // 麦序列表
    public function microphone_status(){
        $data = $this->request->request();
        $room=DB::name('rooms')->field(['id'])->where('uid',$data['uid'])->select();
        if(!$room)    $this->ApiReturn(0,'参数错误');
        $microphone = DB::name('rooms')->where('uid',$data['uid'])->value('microphone');
        $is_prohibit_sound = DB::name('rooms')->where('uid',$data['uid'])->value('is_prohibit_sound');//麦位是否禁声
        // $roomSpeak = DB::name('rooms')->where('uid',$data['uid'])->value('roomSpeak');   //房间禁言单
        $roomSound = DB::name('rooms')->where('uid',$data['uid'])->value('roomSound');  //房间禁声列表
        $roomSound_arr=explode(",", $roomSound);
        $microphone = explode(',', $microphone);
        $is_prohibit_sound = explode(',', $is_prohibit_sound);
        $mic=[];
      //  $num=0;
        foreach ($microphone as $k => &$v) {
            $ar=[];
            foreach ($is_prohibit_sound as $ke => &$va) {
                if($k == $ke){
                    $ar['shut_sound']  =   $va  ? 2 : 1;
                }
            }
            if($v == 0){
                $ar['status'] = 1;
            }elseif($v == -1){
                $ar['status'] = 3;
            }else{
            	
                $ar['status'] = 2;
                $user=DB::name('users')->field("id,headimgurl,nickname,sex,dress_4,dress_7")->find($v);
                $ar['user_id']=$v;
                $ar['headimgurl']=$this->auth->setFilePath($user['headimgurl']);
               // $num=Db::name("gift_logs")->where("fromUid='{$v}'")->find();
                
                $ar['ml']=Db::name('gift_logs')->where("fromUid='{$v}' and uid='{$data['uid']}'")->sum('giftPrice'); 
                $ar['nickname']=$user['nickname'];
                $ar['sex']=$user['sex'];
                $txk=DB::name('wares')->where(['id'=>$user['dress_4']])->value('img1');
                $ar['txk']=$this->auth->setFilePath($txk);
                $ar['mic_color']=DB::name('wares')->where(['id'=>$user['dress_7']])->value('color') ? : '#ffffff';

                //数值玩法
                $ar['price'] = DB::name('play_num_log')->where(['uid'=>$data['uid'],'user_id'=>$v])->value('price') ? : 0;
                $ar['is_play']=DB::name('rooms')->where(['uid'=>$data['uid']])->value('play_num');
                $ar['is_master']= $data['uid'] == $v ? 1 : 0;
            }
            $ar['is_sound'] = in_array($v,$roomSound_arr) ? 2 : 1;
            $mic[]=$ar;
        }
        $wait=DB::name('wait')->where('uid',$data['uid'])->order('id','asc')->limit(1)->select();
        $arr['user_id'] = !$wait ? '' : $wait[0]['user_id'];
        $arr['microphone']=$mic;
      //  dump($arr);die;
     
        $this->ApiReturn(1,'',$arr);
    }


    // 上麦
    public function up_microphone(){
        $data = $this->request->request();
        $user_id= input('user_id/d',0);
        $phase=input('phase/d',0);
        if(!$data['uid'] || !$user_id) $this->ApiReturn(0,'缺少参数');
        $roomVisitor=DB::name('rooms')->where('uid',$data['uid'])->value('roomVisitor');

        $vis_arr= !$roomVisitor ? [] : explode(",", $roomVisitor);
        if(!in_array($user_id, $vis_arr) && $data['uid'] != $user_id)   $this->ApiReturn(0,'该用户不在此房间');

        $position = $data['position'];//麦序0-8
        if($position <0 || $position >8) $this->ApiReturn(0,'参数错误');
        $microphone = DB::name('rooms')->where('uid',$data['uid'])->value('microphone');

        $mic_arr=explode(',', $microphone);
        if($mic_arr[$position] == -1)   $this->ApiReturn(0,'此麦位已被锁定');
        if($mic_arr[$position] != 0)   $this->ApiReturn(0,'麦位上已有用户');

        //如果在麦上,跳至置顶麦,原麦放空
        if(in_array($user_id, $mic_arr)){
            $key=array_search($user_id,$mic_arr);
            $mic_arr[$key]=0;
        }

        $arr=$mic_arr;


        $nick_color = $this->getConfig('nick_color');
        if($phase < 4)  $arr[]=$data['uid'];
        $cp_arr=[];
        foreach ($arr as $k => &$v) {
            if($v == -1 || $v == 0) continue;
            $cp_id=$this->check_first_cp($user_id,$v,1);
            if($cp_id){
                $level=$this->getVipLevel($v,3);
                $ar['cp_level']=$this->getCpLevel($cp_id);
                $ar['nick_color'] = ($level >= 3) ? $nick_color : '#ffffff';
                $ar['id']=$v;
                $ar['nickname']=DB::name('users')->where(['id'=>$v])->value('nickname');
                $ar['exp']=DB::name('cp')->where(['id'=>$cp_id])->value('exp');
                $img=DB::name('users')->where(['id'=>$v])->value('headimgurl');
                $ar['headimgurl']=$this->auth->setFilePath($img);
                $cp_arr[]=$ar;
            }
        }
        if($cp_arr){
            array_multisort(array_column($cp_arr,'exp'),SORT_DESC,$cp_arr);
        }
        $cp_xssm=$this->getConfig('cp_xssm');
        $i=0;
        foreach ($cp_arr as $k => &$va) {
            if(!$i){
                $va['cp_xssm']= $va['cp_level'] >=5 ? $this->auth->setFilePath($cp_xssm) : '';
            }else{
                $va['cp_xssm']='';
            }
            $i++;
        }
        $mic_arr[$position]=$user_id;
        $mic=implode(',', $mic_arr);
        $res = DB::name('rooms')->where('uid',$data['uid'])->update(['microphone'=>$mic]);

        $user=DB::name('users')->field('id,nickname,headimgurl')->find($user_id);
        $user['headimgurl']=$this->auth->setFilePath($user['headimgurl']);
        $user_level=$this->getVipLevel($user_id,3);
        $user['nick_color']=($user_level >= 3) ? $nick_color : '#ffffff';
        $res_arr['cp']=$cp_arr;
        $res_arr['user']=$user;
        if($res){
            //去除麦序
            $this->delWaitHand($user_id);
            $this->ApiReturn(1,'上麦成功',$res_arr);
        }else{
            $this->ApiReturn(0,'上麦失败');
        }
    }

    //下麦
    public function go_microphone(){
        $data = $this->request->request();
        $result=$this->go_microphone_hand($data['uid'],$data['user_id']);
        if($result){
            $this->ApiReturn(1,'下麦成功');
        }else{
            $this->ApiReturn(0,'下麦失败');
        }
    }






    //锁麦
    public function shut_microphone()
    {
        $data = $this->request->request();
        $position = $data['position'];
        if($position <0 || $position >8) $this->ApiReturn(0,'参数错误');
        $microphone = DB::name('rooms')->where('uid',$data['uid'])->value('microphone');
        $microphone = explode(',', $microphone);
        $microphone[$position] = -1;
        $microphone = implode(',', $microphone);
        $res = DB::name('rooms')->where('uid',$data['uid'])->update(['microphone'=>$microphone]);
        if($res){
            $this->ApiReturn(1,'锁定麦位成功');
        }else{
            $this->ApiReturn(0,'锁定麦位失败');
        }
    }
    //开放麦位
    public function open_microphone()
    {
        $data = $this->request->request();
        $position = $data['position'];
        if($position <0 || $position >8) $this->ApiReturn(0,'参数错误');
        $microphone = DB::name('rooms')->where('uid',$data['uid'])->value('microphone');
        $microphone = explode(',', $microphone);
        $microphone[$position] = 0;
        $microphone = implode(',', $microphone);
        $res = DB::name('rooms')->where('uid',$data['uid'])->update(['microphone'=>$microphone]);
        if($res){
            $this->ApiReturn(1,'解锁麦位成功');
        }else{
            $this->ApiReturn(0,'解锁麦位失败');
        }
    }


    //礼物列表
    public function gift_list()
    {
        $user_id=$this->user_id;
        //礼物
        $gift = DB::name('gifts')->where(['enable'=>1])
            ->order('sort desc, price asc')
            ->field(['id','name','price','img','type','show_img','show_img2'])
            ->select();
        $i=0;
        foreach ($gift as $key => &$v) {
            $v['is_check'] = $i ? 0 : 1;
            $i++;
            //四期
            $v['price_004']=$v['price'];
            //四期前
            $v['price']=$v['price'].'米钻';
            $v['img'] = $this->auth->setFilePath($v['img']);
            $v['show_img'] = $this->auth->setFilePath($v['show_img']);
            $v['show_img2'] = $this->auth->setFilePath($v['show_img2']);
            $v['wares_type']=2;
            $v['e_name']='';

        }
        unset($v);
        $mizuan=DB::name('users')->where('id',$user_id)->value('mizuan');

        //宝石
        $baoshi=Db::name('wares')->where(['type'=>1])->field('id,name,price,img1,show_img,img2,get_type,type')->select();
        $j=0;
        foreach ($baoshi as $k1 => &$v1) {
            $v1['is_check']= $j ? 0 : 1;
            $j++;
            //四期
            $v1['price_004']= $v1['get_type'] == 4 ? $v1['price'] : "";
            //四期前
            $v1['price']= $v1['get_type'] == 4 ? $v1['price'].'米钻' : get_wares_way($v1['get_type']);
            $v1['img']=$this->auth->setFilePath($v1['img1']);
            $v1['show_img']=$this->auth->setFilePath($v1['show_img']);
            $v1['show_img2']=$this->auth->setFilePath($v1['img2']);
            $v1['type']= $v1['show_img2'] ? 2 : 1;
            $v1['wares_type']=1;
            $v1['e_name']='';
        }

        //我的
        //宝石
        $my_baoshi=Db::name('pack')->where(['a.type'=>1,'a.user_id'=>$user_id])
            ->alias('a')
            ->join('wares b','a.target_id = b.id')
            ->field('a.id,a.num,a.target_id,b.get_type,a.expire,b.name,b.price,b.img1,b.img2,b.show_img,b.type')
            ->select();

        //爆音卡
        $my_baoyin=Db::name('pack')->where(['a.type'=>3,'a.user_id'=>$user_id,'a.target_id'=>6])
            ->alias('a')
            ->join('wares b','a.target_id = b.id')
            ->field('a.id,a.num,a.target_id,b.get_type,a.expire,b.name,b.price,b.img1,b.img2,b.show_img,b.type')
            ->select();
        $data=array_merge($my_baoshi,$my_baoyin);
        foreach ($data as $k2 => &$v2) {
            //四期
            $v2['price_004']= $v2['get_type'] == 4 ? $v2['price'] : "";
            //四期前
            $v2['id']=$v2['target_id'];
            $v2['price']= "x".$v2['num'];
            $v2['img']=$this->auth->setFilePath($v2['img1']);
            $v2['show_img']=$this->auth->setFilePath($v2['show_img']);
            $v2['show_img2']=$this->auth->setFilePath($v2['img2']);
            $v2['wares_type']=$v2['type'];
            $v2['type']=$v2['show_img2'] ? 2 : 1;
        }
        unset($v2);
        //我的礼物
        $my_gift=Db::name('pack')->where(['a.type'=>2,'a.user_id'=>$user_id])->alias('a')
            ->join('gifts b','a.target_id = b.id')
            ->field('a.id,a.num,a.target_id,a.get_type,b.name,b.price,b.img,b.show_img,b.show_img2,b.type')
            ->select();
        foreach ($my_gift as $k3 => &$v3){
            //四期
            $v3['price_004']= $v3['price'];
            //四期前
            $v3['id']=$v3['target_id'];
            $v3['wares_type'] = 2;
            $v3['price']="x".$v3['num'];
            $v3['img']=$this->auth->setFilePath($v3['img']);
            $v3['show_img']=$this->auth->setFilePath($v3['show_img']);
            $v3['show_img2']=$this->auth->setFilePath($v3['show_img2']);
        }
        unset($v3);
        $res_arr=array_merge($data,$my_gift);
        $n=0;
        foreach ($res_arr as $k => &$va) {
            $va['is_check']=$n ? 0 : 1;
            $n++;
            $va['e_name']='';
        }
        unset($va);
        $arr['gifts']=$gift;
        $arr['baoshi']=$baoshi;
        $arr['my_wares']=$res_arr;
        $arr['mizuan']=$mizuan;
        $this->ApiReturn(1,'获取成功',$arr);
    }
    //关闭用户麦克风
    public function is_sound(){
        $user_id=$this->request->request('user_id') ? : 0;
        $uid=$this->request->request('uid') ? : 0;
        if(!$uid || !$user_id)  $this->ApiReturn(0,'缺少参数');
        $sound = DB::name('rooms')->where('uid',$uid)->value('roomSound');
        //if(strstr($sound, $user_id))    $this->ApiReturn(0,'该用户已经在禁声单，请不要重复设置');
        $sound_arr=explode(',', $sound);
        if(in_array($user_id , $sound_arr))  $this->ApiReturn(0,'该用户已经在禁声单，请不要重复设置');

        array_push($sound_arr,$user_id);
        $str=implode(',', $sound_arr);
        $res = DB::name('rooms')->where('uid',$uid)->update(['roomSound'=>$str]);
        if($res){
            $this->ApiReturn(1,'加入禁声单成功');
        }else{
            $this->ApiReturn(0,'加入禁声单失败');
        }
    }

    //开放用户声音麦克风
    public function remove_sound(){
        $user_id=$this->request->request('user_id') ? : 0;
        $uid=$this->request->request('uid') ? : 0;
        if(!$uid || !$user_id)  $this->ApiReturn(0,'缺少参数');
        $sound = DB::name('rooms')->where('uid',$uid)->value('roomSound');
        $sound_arr = explode(',', $sound);
        if(!in_array($user_id , $sound_arr))  $this->ApiReturn(0,'该用户已不在禁声单，请不要重复设置');
        $key = array_search($user_id,$sound_arr);
        unset($sound_arr[$key]);
        $sound = implode(',', $sound_arr);
        $res = DB::name('rooms')->where('uid',$uid)->update(['roomSound'=>$sound]);
        if($res){
            $this->ApiReturn(1,'取消禁声成功');
        }else{
            $this->ApiReturn(0,'取消禁声失败');
        }
    }

    //踢出房间
    public function out_room(){
        $data = $this->request->request();
        $uid = $this->request->request('uid') ? : 0;
        $black_id = $this->request->request('user_id') ? : 0;
        if(!$uid || !$black_id)  $this->ApiReturn(0,'缺少参数');
        $black_list = DB::name('rooms')->where('uid',$uid)->value('roomBlack');
        if($black_list == null){
            $black_list = $black_id.'#'.time();
        }else{
            $list = explode(',', $black_list);
            for ($i=0; $i < count($list); $i++) {
                $is_repeat = strstr($list[$i],$black_id);
                if($is_repeat){
                    $black_list = str_replace($is_repeat,'',$black_list);
                    $black_list = preg_replace('#,{2,}#',',',$black_list);
                }
            }
            $black_list = trim($black_list.','.$black_id.'#'.time(),',');
        }
        $result = DB::name('rooms')->where('uid',$uid)->update(['roomBlack'=>$black_list]);

        if($result){
            //退出房间
            $this->quit_hand($uid,$data['user_id']);
            $this->ApiReturn(1,'成功');
        }else{
            $this->ApiReturn(0,'失败');
        }
    }

    //收藏房间
    public function room_mykeep(){
        $data = $this->request->request();
        $uid = $data['uid'];
        $user_id = $this->user_id;
        $mykeep_list = DB::name('users')->where('id',$user_id)->value('mykeep');
        $mykeep_arr=explode(",", $mykeep_list);
        if(in_array($uid, $mykeep_arr)) $this->ApiReturn(0,'请勿重复收藏');

        array_unshift($mykeep_arr,$uid);
        $str=trim(implode(",", $mykeep_arr),",");
        $res=DB::name('users')->where('id',$user_id)->update(['mykeep'=>$str]);
        if($res){
            $this->ApiReturn(1,'收藏成功');
        }else{
            $this->ApiReturn(0,'收藏失败');
        }
    }
    //取消收藏
    public function remove_mykeep(){
        $data = $this->request->request();
        $uid = $data['uid'];
        $user_id = $this->user_id;
        $mykeep_list = DB::name('users')->where('id',$user_id)->value('mykeep');
        $mykeep_arr=explode(",", $mykeep_list);
        if(!in_array($uid, $mykeep_arr)) $this->ApiReturn(0,'尚未收藏此房间');
        $key=array_search($uid,$mykeep_arr);
        unset($mykeep_arr[$key]);
        $str=trim(implode(",", $mykeep_arr),",");
        $res=DB::name('users')->where('id',$user_id)->update(['mykeep'=>$str]);
        if($res){
            $this->ApiReturn(1,'取消成功');
        }else{
            $this->ApiReturn(0,'取消失败');
        }
    }
    //是否设置密码
    public function is_pass(){
        $uid = $this->request->request('uid') ? : 0;
        if(!$uid)   $this->ApiReturn(0,'缺少参数');
        $result = DB::name('rooms')->where('uid',$uid)->value('room_pass');
        if($result){
            $this->ApiReturn(2,'房间已设密码，请输入密码');
        }else{
            $this->ApiReturn(1,'房间没有密码');
        }
    }

    //获取房间其他用户
    public function get_other_user(){
        $data = $this->request->request();
        $uid = $data['uid'];
        $user_id = $data['user_id'];;
        $my_id = $data['my_id'];

        $room_info = DB::name('rooms')->where('uid',$uid)->field(['roomAdmin','roomSpeak','roomJudge','roomSound'])->select();

        $room_info[0]['user_type'] = 5;
        $roomAdmin = explode(',', $room_info[0]['roomAdmin']);
        for ($i=0; $i < count($roomAdmin); $i++) {
            if($roomAdmin[$i] == $user_id){
                $room_info[0]['user_type'] = 2;
            }
        }
        $roomJudge = explode(',', $room_info[0]['roomJudge']);
        for ($i=0; $i < count($roomJudge); $i++) {
            if($roomJudge[$i] == $user_id){
                $room_info[0]['user_type'] = 4;
            }
        }
        $room_info[0]['is_speak'] = 1;
        $is_speak = explode(',', $room_info[0]['roomSpeak']);
        for ($i=0; $i < count($is_speak); $i++) {
            if($is_speak[$i] == $user_id){
                $room_info[0]['is_speak'] = 2;
            }
        }
        $room_info[0]['is_sound'] = 1;
        $is_sound = explode(',', $room_info[0]['roomSound']);
        for ($i=0; $i < count($is_sound); $i++) {
            if($is_sound[$i] == $user_id){
                $room_info[0]['is_sound'] = 2;
            }
        }

        $result = DB::name('users')->where('id',$user_id)->field(['id','nickname','headimgurl','sex','birthday'])->select();

        $is_follows = DB::name('follows')
            ->where('user_id',$my_id)
            ->where('followed_user_id',$user_id)
            ->where('status',1)
            ->value('followed_user_id');

        $result[0]['is_follows'] = $is_follows ? 1 : 2;


        //$result[0]['headimgurl'] = env('APP_URL').'/upload/'.$result[0]['headimgurl'];
        $result[0]['headimgurl'] = $this->auth->setFilePath($result[0]['headimgurl']);
        $result[0]['age'] = getBrithdayMsg($result[0]['birthday'],0);

        $result[0]['user_type'] = $room_info[0]['user_type'];
        $result[0]['is_speak'] = $room_info[0]['is_speak'];
        $result[0]['is_sound'] = $room_info[0]['is_sound'];

        $star_level=$this->getVipLevel($user_id,1);
        $gold_level=$this->getVipLevel($user_id,2);
        $vip_level=$this->getVipLevel($user_id,3);
        $star_img=DB::name('vip')->where('level',$star_level)->where('type',1)->value('img');
        $gold_img=DB::name('vip')->where('level',$gold_level)->where('type',2)->value('img');
        $vip_img=DB::name('vip')->where('level',$vip_level)->where('type',3)->value('img');
        $result[0]['star_img']=$this->auth->setFilePath($star_img);
        $result[0]['gold_img']=$this->auth->setFilePath($gold_img);
        $result[0]['vip_img']=$this->auth->setFilePath($vip_img);
        if($result){
            $this->ApiReturn(1,'获取成功',$result);
        }else{
            $this->ApiReturn(2,'取消失败');
        }

    }


    //是否可以发言
    public function not_speak_status(){
        $uid =  input('uid/d',0);
        $user_id = $this->user_id;
        if(!$uid)   $this->ApiReturn(0,'缺少参数');
        $roomSpeak = DB::name('rooms')->where('uid',$uid)->value('roomSpeak');
        $spe_arr = !$roomSpeak ? [] : explode(',', $roomSpeak);

        $is_speak = 1;
        foreach ($spe_arr as $k => &$v) {
            $arr=explode("#",$v);
            $new_time=$arr[1] + 180;
            if( time() - $new_time   < 0){
                if($arr[0] == $user_id){
                    $is_speak = 0;
                }
            }else{
                unset($spe_arr[$k]);
            }
        }
        $str=trim(implode(",", $spe_arr),",");
        DB::name('rooms')->where(['uid'=>$uid])->update(['roomSpeak'=>$str]);

        if($is_speak){
            $this->ApiReturn(1,'可以发言');
        }else{
            $this->ApiReturn(0,'不能发言');
        }
    }


    //房间背景列表
    public function room_background(){
        $data=DB::name('backgrounds')->where(['enable'=>1])->field('id,img')->select();
        foreach ($data as $k => &$v) {
            $v['img']=$this->auth->setFilePath($v['img']);
        }
        $this->ApiReturn(1,'',$data);
    }

    public function room_type(){
        $data=DB::name('room_categories')->where(['pid'=>0,'enable'=>1])->field("id,name")->select();
        $this->ApiReturn(1,'',$data);
    }


    //设置为管理员
    public function is_admin(){
        $uid=input('uid/d',0);
        $admin_id=input('admin_id/d',0);
        if(!$uid || !$admin_id) $this->ApiReturn(0,'缺少参数');
        if($uid == $admin_id)    $this->ApiReturn(0,'违规操作');
        $roomVisitor=DB::name('rooms')->where('uid',$uid)->value('roomVisitor');
        $vis_arr= !$roomVisitor ? [] : explode(",", $roomVisitor);
        if(!in_array($admin_id, $vis_arr))   $this->ApiReturn(0,'该用户不在此房间');


        $roomAdmin=DB::name('rooms')->where('uid',$uid)->value('roomAdmin');
        $adm_arr= !$roomAdmin ? [] : explode(",", $roomAdmin);
        if(in_array($admin_id, $adm_arr))   $this->ApiReturn(0,'该用户已是管理员,请勿重复设置');
        if(count($adm_arr) >= 12)   $this->ApiReturn(0,'该房间管理员已满12名');

        $adm_arr=array_merge($adm_arr,[$admin_id]);
        $str=implode(",",$adm_arr);

        $res=DB::name('rooms')->where(['uid'=>$uid])->update(['roomAdmin'=>$str]);
        if($res){
            $this->ApiReturn(1,'设置管理员成功');
        }else{
            $this->ApiReturn(0,'设置管理员失败');
        }
    }

    //取消管理员
    public function remove_admin(){
        $uid=input('uid/d',0);
        $admin_id=input('admin_id/d',0);
        if(!$uid || !$admin_id) $this->ApiReturn(0,'缺少参数');
        $roomAdmin=DB::name('rooms')->where('uid',$uid)->value('roomAdmin');
        $adm_arr= !$roomAdmin ? [] : explode(",", $roomAdmin);
        if(!in_array($admin_id, $adm_arr))   $this->ApiReturn(0,'该用户不是此房间管理员');
        $key=array_search($admin_id,$adm_arr);
        unset($adm_arr[$key]);
        $str=implode(",", $adm_arr);
        $res=DB::name('rooms')->where(['uid'=>$uid])->update(['roomAdmin'=>$str]);
        if($res){
            $this->ApiReturn(1,'取消管理员成功');
        }else{
            $this->ApiReturn(0,'取消管理员失败');
        }
    }

    //添加禁言
    public function is_black(){
        $uid=input('uid/d',0);
        $user_id=input('user_id/d',0);
        if(!$uid || !$user_id) $this->ApiReturn(0,'缺少参数');
        if($uid == $user_id)    $this->ApiReturn(0,'违规操作');
        $roomVisitor=DB::name('rooms')->where('uid',$uid)->value('roomVisitor');
        $vis_arr= !$roomVisitor ? [] : explode(",", $roomVisitor);
        if(!in_array($user_id, $vis_arr))   $this->ApiReturn(0,'该用户不在此房间');


        $roomSpeak=DB::name('rooms')->where('uid',$uid)->value('roomSpeak');
        $spe_arr= !$roomSpeak ? [] : explode(",", $roomSpeak);
        foreach ($spe_arr as $k => &$v) {
            $arr=explode("#",$v);
            if($arr[0] == $user_id) $this->ApiReturn(0,'该用户已在禁言列表中');
        }
        $shic=time() + 180;
        $jinyan=$user_id."#".$shic;
        $spe_arr=array_merge($spe_arr,[$jinyan]);
        $str=implode(",", $spe_arr);
        $res=DB::name('rooms')->where(['uid'=>$uid])->update(['roomSpeak'=>$str]);
        if($res){
            $this->ApiReturn(1,'添加禁言成功');
        }else{
            $this->ApiReturn(0,'添加禁言失败');
        }
    }







}
