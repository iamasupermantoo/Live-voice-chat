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
 * 动态接口
 */
class Community extends Api
{
    protected $noNeedLogin = ["topic_dynamic"];
    protected $noNeedRight = ['*'];


    //发布动态
    public function send_dynamic(){
        $data = $this->request->request();
        $info['user_id']=$this->user_id;
        if(!$info['user_id'])   $this->ApiReturn(0,'缺少参数');


        $audio_file= $this->request->file('audio');
        if(!empty($audio_file)){
            $info['audio']=$this->uploadOnes($audio_file,7,'audio');
        }else{
            $info['audio'] = '';
        }

        $video_file=$this->request->file('video');
        if(!empty($video_file)){
            $info['video']=$this->uploadOnes($video_file,4,'video');
        }else{
            $info['video']='';
        }

        $img_arr=array();
        for($i=1;$i<=6;$i++){
            $img_file = $this->request->file('img'.$i);
            if(!empty($img_file)){
                $img=$this->uploadOnes($img_file , 6,'image');
                $this->SensitiveImage($img);
                $img_arr[]=$img;
            }
        }
        $info['image']=json_encode($img_arr);
        $info['content']=$data['content'];
        $info['tags']=$data['tags'];
        $info['audio_time']=$data['audio_time'];
        $info['addtime']=time();
        //敏感词
        if($info['content'])    $this->SensitiveWords($info['content']);
        if(!$img_arr && !$info['audio'] && !$info['video'] && !$info['content'])  $this->ApiReturn(0,'没有要发布的内容');
        $info['content']=urlencode($info['content']);
        $res=DB::name('dynamics')->insertGetId($info);
        if($res){
            $this->ApiReturn(1,'发布成功',$res);
        }else{
            $this->ApiReturn(0,'发布失败');
        }
    }

    //发布动态IOS
    public function send_dynamic_ios(){
        $data=$this->request->request();
        $info['user_id'] = $this->user_id;
        if(!$info['user_id'])   $this->ApiReturn(0,'缺少参数');
        if(!empty($data['audio'])){
            $info['audio']=$this->base64_image_content($data['audio'],7);

        }else{
            $info['audio']='';
        }
        if(!empty($data['video'])){
            $info['video']=$this->base64_image_content($data['video'],4);
        }else{
            $info['video']='';
        }
        $img_arr=array();
        for($i=1;$i<=6;$i++){
            if(!empty($data['img'.$i])){
                $img=$this->base64_image_content($data['img'.$i],6);
                $this->SensitiveImage($img);
                $img_arr[]=$img;
            }
        }
        $info['content']=$data['content'];
        $info['image']=json_encode($img_arr);
        $info['tags']=$data['tags'];
        $info['audio_time']=$data['audio_time'];
        $info['addtime']=time();
        //敏感词
        if($info['content'])    $this->SensitiveWords($info['content']);
        if(!$img_arr && !$info['audio'] && !$info['video'] && !$info['content'])  $this->ApiReturn(0,'没有要发布的内容');
        $info['content']=urlencode($info['content']);
        $res=DB::name('dynamics')->insertGetId($info);
        if($res){
            $this->ApiReturn(1,'发布成功',$res);
        }else{
            $this->ApiReturn(0,'发布失败');
        }
    }

    //删除动态
    public function del_community(){
        $user_id = $this->user_id;
        $id=$this->request->request('id') ? : '';
        if(!$user_id || !$id)   $this->ApiReturn(0,'缺少参数');
        $data=DB::name('dynamics')->field(['id','image','audio','video'])->where('id',$id)->where('user_id',$user_id)->find();

        if(!$data)  $this->ApiReturn(0,'参数错误');
        $this->deleteFile($data['audio']);
        $this->deleteFile($data['video']);
        $img_arr=json_decode($data['image']);
        array_map(function($val){
            $this->deleteFile($val);
        }, $img_arr);
        $res=DB::name('dynamics')->where('id',$data['id'])->delete();
        if($res){
            //删除点赞收藏转发
            DB::name('likes')->whereIn('type',[1,2,3])->where('target_id',$data['id'])->delete();
            //删除动态评论
            $pinglun=DB::name('dynamic_comments')->where(['b_dynamic_id'=>$data['id']])->field('id')->select();
            foreach ($pinglun as $k => &$v) {
                DB::name('likes')->where(['type'=>4,'target_id'=>$v['id']])->delete();
                DB::name('dynamic_comments')->where(['id'=>$v['id']])->delete();
            }
            $this->ApiReturn(1,'删除成功');
        }else{
            $this->ApiReturn(0,'删除失败');
        }
    }

    //获取发布动态标签
    public function get_talk_labels(){
        $data=DB::name('labels')->where('enable',1)->where('type',1)->field(['id','name'])->select();
        foreach ($data as $k => &$v) {
            $v['name']="#".$v['name'];
        }
        unset($v);
        $arr['top'] = array_slice($data, 0, 5);
        $arr['all'] = $data;
        $this->ApiReturn(1,'',$arr);
    }

    //搜索标签
    public function search_labels(){
        $keywords=$this->request->request('keywords') ? : '';
        if(!$keywords)  $this->ApiReturn(0,'缺少参数');
        $data=DB::name('labels')->where('enable',1)->where('name','like','%'.$keywords.'%')->field(['id','name'])->select();
        foreach ($data as $k => &$v) {
            $v['name']='#'.$v['name'];
        }
        $this->ApiReturn(1,'',$data);
    }





    //推荐动态列表
    public function dynamicTjList(){
        $user_id = $this->user_id;
        $page=  $this->request->request('page') ? : 1;
        $ids=getUserBlackList($user_id);
        $data = DB::name('dynamics')
            ->alias('dynamics')
            ->join('users users','dynamics.user_id = users.id')
            ->whereNotIn('user_id',$ids)
            ->where('dynamics.praise','>',50)
            ->whereOr('dynamics.is_tj','=',1)
            ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id','dynamics.image','dynamics.audio','dynamics.video',
                'dynamics.content','dynamics.praise','dynamics.is_top','dynamics.tags','dynamics.addtime',
                'users.headimgurl','users.nickname','users.sex'])
            ->order('dynamics.is_top','asc')
            ->order('dynamics.praise','desc')
            ->page($page,10)
            ->select();
        //格式化处理
        $data = $this->dataFormat($data,$user_id);
        $this->ApiReturn(1,'',$data);
    }

    //最新动态列表
    public function dynamicNewList(){
        $user_id = $this->user_id;
        $page=input('page/d',1);
        $ids=getUserBlackList($user_id);
        $arr[]=time() - 86400 * 7;
        $arr[]=time();
        $data = DB::name('dynamics')
            ->alias('dynamics')
            ->whereNotIn('user_id',$ids)
            ->whereBetween('dynamics.addtime',$arr)
            ->join('users users','dynamics.user_id=users.id','left')
            ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id','dynamics.image',
                'dynamics.audio','dynamics.video','dynamics.content','dynamics.praise','dynamics.tags',
                'dynamics.addtime','users.headimgurl','users.nickname','users.sex'])
            ->order('dynamics.addtime','desc')
            ->page($page,10)
            ->select();
        //格式化处理
        $data=$this->dataFormat($data,$user_id);
        $this->ApiReturn(1,'',$data);
    }

    //关注人动态列表
    public function dynamicFollowList(){
        $user_id = $this->user_id;
        $page=  $this->request->request('page') ? : 1;
        $res=DB::name('follows')->field(['followed_user_id'])->where('user_id',$user_id)->where('status',1)->select();
        if(!$res) $this->ApiReturn(0,'暂无数据');
        foreach ($res as $k => &$v) {
            $ids[]=$v['followed_user_id'];
        }
        unset($v);
        $data = DB::name('dynamics')
            ->alias('dynamics')
            ->whereIn('user_id',$ids)
            ->join('users users','dynamics.user_id = users.id','left')
            ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id','dynamics.image',
                'dynamics.audio','dynamics.video','dynamics.content','dynamics.praise',
                'dynamics.tags','dynamics.addtime','users.headimgurl','users.nickname','users.sex'])
            ->order('dynamics.addtime','desc')
            ->page($page,10)
            ->select();
        //格式化处理
        $data=$this->dataFormat($data,$user_id);
        $this->ApiReturn(1,'',$data);
    }

    //动态详情
    public function dynamic_details(){
        $info=$this->request->request();
        $user_id=$this->user_id;
        $sort=$info['sort'] ? : 'desc';
        if($sort != 'desc' && $sort != 'asc') $this->ApiReturn(0,'参数错误');
        $data = DB::name('dynamics')
            ->alias('dynamics')
            ->where('dynamics.id',$info['id'])
            ->join('users','dynamics.user_id=users.id','left')
            ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id','dynamics.image','dynamics.audio',
                'dynamics.video','dynamics.content','dynamics.praise','dynamics.tags','dynamics.addtime',
                'users.headimgurl','users.nickname','users.sex'])
            ->limit(1)
            ->select();

        if(!$data)    $this->ApiReturn(0,'暂无此动态');
        //格式化处理
        $data=$this->dataFormat($data,$user_id);

        //热门评论
        $hot=DB::name('dynamic_comments')
            ->alias('dynamic_comments')
            ->where('dynamic_comments.b_dynamic_id',$data[0]['id'])
            ->where('dynamic_comments.praise','>=',10)
            ->join('users','dynamic_comments.user_id = users.id','left')
            ->field(['dynamic_comments.id','dynamic_comments.pid','dynamic_comments.user_id',
                'dynamic_comments.content','dynamic_comments.hf_uid','dynamic_comments.praise',
                'dynamic_comments.created_at','users.headimgurl','users.nickname'])
            ->order('praise','desc')
            ->limit(3)
            ->select();
        foreach ($hot as $k => &$vh) {
            $vh['headimgurl']=$this->auth->setFilePath($vh['headimgurl']);
            $vh['vip_level']=$this->getVipLevel($vh['user_id'],3);
            $vh['is_praise']=DB::name('likes')->where('type',4)->where('target_id',$vh['id'])->where('user_id',$user_id)->value('id') ? 1 : 0;
            if($vh['pid']){
                $vh['reply']=DB::name('users')->where('id',$vh['hf_uid'])->value('nickname');
            }else{
                $vh['reply']='';
            }
            $vh['content']=urldecode($vh['content']);
        }
        unset($vh);
        //普通评论
        $query=DB::name('dynamic_comments')->alias('dynamic_comments')
            ->where('dynamic_comments.b_dynamic_id',$data[0]['id'])
            ->join('users','dynamic_comments.user_id = users.id')
            ->field(['dynamic_comments.id','dynamic_comments.pid','dynamic_comments.user_id',
                'dynamic_comments.content','dynamic_comments.hf_uid','dynamic_comments.praise',
                'dynamic_comments.created_at','users.headimgurl','users.nickname']);

        $comments=$query->order('dynamic_comments.created_at',$sort)
            ->page($info['page'],10)
            ->select();

        foreach ($comments as $k => &$vc) {
            $vc['headimgurl']=$this->auth->setFilePath($vc['headimgurl']);
            $vc['vip_level']=$this->getVipLevel($vc['user_id'],3);
            $vc['is_praise']=DB::name('likes')->where('type',4)->where('target_id',$vc['id'])->where('user_id',$user_id)->value('id') ? 1 : 0;
            if($vc['pid']){
                $vc['reply']=DB::name('users')->where('id',$vc['hf_uid'])->value('nickname');
            }else{
                $vc['reply']='';
            }
            $vc['content']=urldecode($vc['content']);
        }
        unset($vc);
        //阅读数+1
        DB::name('dynamics')->where('id',$info['id'])->setInc('reads',1);
        $arr['details']=$data;
        $arr['hot']=$hot;
        $arr['comments']=$comments;
        $this->ApiReturn(1,'',$arr);
    }

    //查看评论
    public function lookComments(){
        $info=$this->request->request();
        $user_id=$this->user_id;
        $hf_uid=$info['hf_uid'];
        $data = DB::name('dynamics')->alias('dynamics')
            ->where('dynamics.id',$info['id'])
            ->join('users','dynamics.user_id=users.id')
            ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id','dynamics.image',
                'dynamics.audio','dynamics.video','dynamics.content','dynamics.praise','dynamics.tags','dynamics.addtime',
                'users.headimgurl','users.nickname','users.sex'])
            ->limit(1)
            ->select();
        if(!$data)    $this->ApiReturn(0,'暂无此动态');
        //格式化处理
        $data=$this->dataFormat($data,$user_id);
        //普通评论
        $comments=DB::name('dynamic_comments')->alias('dynamic_comments')
            ->where('dynamic_comments.b_dynamic_id',$data[0]['id'])
            ->whereIn('dynamic_comments.user_id',[$user_id,$hf_uid])
            ->join('users','dynamic_comments.user_id = users.id','left')
            ->field(['dynamic_comments.id','dynamic_comments.pid','dynamic_comments.user_id','dynamic_comments.content',
                'dynamic_comments.hf_uid','dynamic_comments.praise','dynamic_comments.created_at','users.headimgurl','users.nickname'])
            ->order('dynamic_comments.created_at','asc')
            ->select();

        foreach ($comments as $k => &$vc) {
            $vc['headimgurl']=$this->auth->setFilePath($vc['headimgurl']);
            $vc['vip_level']=$this->getVipLevel($vc['user_id'],3);
            $vc['is_praise'] = DB::name('likes')->where('type',4)
                ->where('target_id',$vc['id'])
                ->where('user_id',$user_id)
                ->value('id') ? 1 : 0;
            if($vc['pid']){
                $vc['reply'] = DB::name('users')->where('id',$vc['hf_uid'])->value('nickname');
            }else{
                $vc['reply']='';
            }
            $vc['content']=urldecode($vc['content']);
        }
        unset($vc);
        //阅读数+1
        DB::name('dynamics')->where('id',$info['id'])->setInc('reads',1);
        $arr['details']=$data;
        $arr['comments']=$comments;

        //阅读此动态下所有评论
        DB::name('dynamic_comments')->where('b_dynamic_id',$info['id'])->whereIn('user_id',[$user_id,$hf_uid])->update(['is_read'=>1]);
        $this->ApiReturn(1,'',$arr);
    }


    //动态下所有评论
    public function allComment(){
        $data=$this->request->request();
        $data['user_id'] = $this->user_id;
        $comments=DB::name('dynamic_comments')
            ->alias('dynamic_comments')
            ->where('dynamic_comments.b_dynamic_id',$data['id'])
            ->join('users','dynamic_comments.user_id=users.id','left')
            ->field(['dynamic_comments.id','dynamic_comments.pid','dynamic_comments.user_id',
                'dynamic_comments.content','dynamic_comments.hf_uid','dynamic_comments.praise',
                'dynamic_comments.created_at','users.headimgurl','users.nickname'])
            ->order('dynamic_comments.created_at','asc')
            ->select();
        //echo DB::name('dynamic_comments')->getLastSql();die;
        foreach ($comments as $k => &$vc) {
            $vc['headimgurl'] = $this->auth->setFilePath($vc['headimgurl']);
            $vc['vip_level'] = $this->getVipLevel($vc['user_id'],3);
            $vc['is_praise'] = DB::name('likes')->where('type',4)
                ->where('target_id',$vc['id'])
                ->where('user_id',$data['user_id'])
                ->value('id') ? 1 : 0;
            if($vc['pid']){
                $vc['reply']=DB::name('users')->where('id',$vc['hf_uid'])->value('nickname');
            }else{
                $vc['reply']='';
            }
            $vc['content']=urldecode($vc['content']);
        }
        unset($vc);
        $this->ApiReturn(1,'',$comments);
    }


    //type 1点赞动态2收藏动态3转发动态4点赞评论
    public function dynamics_hand(){
        $info=$this->request->request();
        $info['user_id'] = $this->user_id;
        $type=$info['type'];
        if(!$type || !$info['user_id'] || !$info['target_id'] || !$info['hand']) $this->ApiReturn(0,'缺少参数');
        if($type == 1){
            $note='点赞动态';
        }elseif($type == 2){
            $note='收藏动态';
        }elseif($type == 3){
            $note='转发动态';
        }elseif($type == 4){
            $note='点赞评论';
        }else{
            $this->ApiReturn(0,'参数错误');
        }

        $data=DB::name('likes')->where('user_id',$info['user_id'])->where('target_id',$info['target_id'])->where('type',$type)->select();

        if($info['hand'] == 'add'){
            if($data)   $this->ApiReturn(0,'请勿重复'.$note);
            $arr['user_id']=$info['user_id'];
            $arr['target_id']=$info['target_id'];
            $arr['type']=$info['type'];
            $arr['addtime']=time();
            $res=DB::name('likes')->insert($arr);
            $title="";
            if($res){
                if($type == 1)  DB::name('dynamics')->where('id',$info['target_id'])->setInc('praise',1);
                if($type == 4)  DB::name('dynamic_comments')->where('id',$info['target_id'])->setInc('praise',1);
            }
        }elseif($info['hand'] == 'del'){
            if(!$data)   $this->ApiReturn(0,'尚未'.$note.'不能取消');
            $res=DB::name('likes')->where('user_id',$info['user_id'])->where('target_id',$info['target_id'])->where('type',$type)->delete();
            $title="取消";
            if($res){
                if($type == 1)  DB::name('dynamics')->where('id',$info['target_id'])->setDec('praise',1);
                if($type == 4)  DB::name('dynamic_comments')->where('id',$info['target_id'])->setDec('praise',1);
            }
        }else{
            $this->ApiReturn(0,'参数错误');
        }

        if($res){
            $this->ApiReturn(1,$title.$note.'成功');
        }else{
            $this->ApiReturn(0,$title.$note.'失败');
        }
    }

    //发布评论
    public function dynamic_comment(){
        $info = $this->request->request();
        $info['user_id'] = $this->user_id;
        $info['pid'] = $info['pid'] ? : 0;
        if(!$info['id'] || !$info['user_id'] || !$info['content'] ) $this->ApiReturn(0,'缺少参数');
        $data=DB::name('dynamics')->where('id',$info['id'])->select();
        if(!$data)    $this->ApiReturn(0,'参数错误');
        $arr['b_dynamic_id'] = $info['id'];
        $arr['pid'] = $info['pid'];
        if($info['pid']){
            $arr['hf_uid']=DB::name('dynamic_comments')->where('id',$info['pid'])->value('user_id');
            if($arr['hf_uid'] == $info['user_id'])  $this->ApiReturn(0,'不能回复自己');
        }
        $arr['user_id'] = $info['user_id'];
        //敏感词检测
        if($info['content'])    $this->SensitiveWords($info['content']);
        $arr['content'] = urlencode($info['content']);
        $arr['created_at'] = date('Y-m-d H:i:s',time());
        $arr['updated_at'] = date('Y-m-d H:i:s',time());
        $res=DB::name('dynamic_comments')->insert($arr);
        if($res){
            $this->ApiReturn(1,'评论成功');
        }else{
            $this->ApiReturn(0,'评论失败');
        }
    }


    //我点赞,收藏,转发,评论过的动态
    //type 1点赞2收藏3转发4评论5关注6单独用户
    public function merge_dynamic(){
        $my_id=$this->user_id;
        $user_id=$this->request->request('user_id') ? : 0;
        $type=$this->request->request('type');
        $page=  input('page/d',1);
        
        if(!$user_id || !$type ) $this->ApiReturn(0,'缺少参数');
        $ids=array();
        if(in_array($type,[1,2,3])){
            $ids=DB::name('likes')->where('type',$type)->where('user_id',$user_id)->column('target_id');
        }elseif($type == 4){
            $ids=DB::name('dynamic_comments')->where('pid',0)->where('user_id',$user_id)->column('b_dynamic_id');
        }elseif($type == 5){
            $ids=DB::name('follows')->where('user_id',$user_id)->where('status',1)->column('followed_user_id');
        }elseif($type == 6){
            $ids[]=$user_id;
            if(!$my_id) $this->ApiReturn(0,'缺少参数');
        }else{
            $this->ApiReturn(0,'参数错误');
        }

        if(!$ids) $this->ApiReturn(0,'暂无此类动态');
        if(in_array($type,[1,2,3])){
            $arr=DB::name('dynamics')->alias('dynamics')
                ->whereIn('dynamics.id',$ids)
                ->join('users','dynamics.user_id = users.id')
                ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id',
                    'dynamics.image','dynamics.audio','dynamics.video','dynamics.content',
                    'dynamics.praise','dynamics.tags','dynamics.addtime as like_time','dynamics.addtime','users.headimgurl',
                    'users.nickname','users.sex'])
                ->order('like_time','desc')
                ->page($page,10)
                ->select();
        }elseif(in_array($type,[4])){
            $arr=DB::name('dynamics')->alias('dynamics')
                ->whereIn('dynamics.id',$ids)
                ->join('users','dynamics.user_id = users.id')
                ->join('dynamic_comments','dynamic_comments.b_dynamic_id = dynamics.id')
                ->where('dynamic_comments.user_id',$user_id)
                ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id',
                    'dynamics.image','dynamics.audio','dynamics.video','dynamics.content',
                    'dynamics.praise','dynamics.tags','dynamics.addtime','users.headimgurl',
                    'users.nickname','users.sex','dynamic_comments.created_at as like_time'])
                ->order('dynamic_comments.created_at','desc')
                ->page($page,10)
                ->select();

        }elseif(in_array($type, [5,6])){
            $arr=DB::name('dynamics')->alias('dynamics')
                ->whereIn('dynamics.user_id',$ids)
                ->join('users','dynamics.user_id=users.id')
                ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id','dynamics.image',
                    'dynamics.audio','dynamics.video','dynamics.content','dynamics.praise','dynamics.tags',
                    'dynamics.addtime','users.headimgurl','users.nickname','users.sex'])
                ->order('dynamics.addtime','desc')
                ->page($page,10)
                ->select();
        }
        $us_id = ($type == 6) ? $my_id : $user_id;
        //格式化处理
        $arr=$this->dataFormat($arr,$us_id);
        $this->ApiReturn(1,'',$arr);
    }

    //热门话题
    public function topic(){
        $type= $this->request->request('type');
        $page=$this->request->request('page') ? : 1;
        $data = DB::name('topics')->field(['topic_img','tags'])
                            ->order('recom desc,id desc')
                            ->page($page,10)
                            ->select();


        foreach ($data as $k => &$v) {
            $v['topic_img'] = $this->auth->setFilePath($v['topic_img']);
            $v['tag_name']=DB::name('labels')->where('id',$v['tags'])->value('name');
            $v['num']=DB::name('dynamics')->where('tags','like','%'.$v['tags'].'%')->count();
            $dynamics=DB::name('dynamics')->where('tags','like','%'.$v['tags'].'%')->field(['id','reads']);
            $sum=$reads=0;
            foreach ($dynamics as $k1 => &$v1) {
                $sum+= DB::name('dynamic_comments')->where('b_dynamic_id',$v1->id)->count();
                $reads+= $v1['reads'];
            }
            unset($v1);
            $v['talk_num']=$sum;
            $v['reads']=$reads;
        }
        unset($v);
        //$arr=json_decode($data,true);
        $arr = $data;
        //array_multisort(array_column($arr,'num'),SORT_DESC,$arr);
        if($type != 'all'){
            $arr=array_slice($arr,0,4);
        }
        $this->ApiReturn(1,'',$arr);
    }

    //热门话题动态
    public function topic_dynamic(){
        $info = $this->request->request();
        $page=  $this->request->request('page') ? : 1;
        $user_id=1;
       //dump($info['tags']);die;
        if(!$info['tags'] || !$user_id || !$info['type']) $this->ApiReturn(0,'缺少参数');

        if($info['type'] == 'hot'){
            $data = DB::name('dynamics')
                ->alias('dynamics')
                ->where('dynamics.tags','like','%'.$info['tags'].'%')
                ->where('dynamics.praise','>=','50')
                ->join('users','dynamics.user_id=users.id')
                ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id','dynamics.image',
                    'dynamics.audio','dynamics.video','dynamics.content','dynamics.praise',
                    'dynamics.is_top','dynamics.tags','dynamics.addtime','users.headimgurl',
                    'users.nickname','users.sex'])
                ->order('dynamics.praise','desc')
                ->page($page,10)
                ->select();

        }elseif($info['type'] == 'new'){
        //	dump($info['tags']);die;
            $data = DB::name('dynamics')
                ->alias('dynamics')
                ->where('dynamics.tags','like','%'.$info['tags'].'%')
                ->join('users','dynamics.user_id=users.id')
                ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id','dynamics.image',
                    'dynamics.audio','dynamics.video','dynamics.content','dynamics.praise','dynamics.is_top',
                    'dynamics.tags','dynamics.addtime','users.headimgurl','users.nickname','users.sex'])
                ->order('dynamics.addtime','asc')
                ->page($page,10)
                ->select();
                               // dump($data);die;
        }else{
            $this->ApiReturn(0,'参数错误');
        }

        //格式化处理
        $data=$this->dataFormat($data,$user_id);

        $dynamics=DB::name('dynamics')->where('tags','like','%'.$info['tags'].'%')->select(['id','reads']);
        $talk=$reads=0;
        foreach ($dynamics as $k1 => &$v1) {
            //讨论人数
            $talk+= DB::name('dynamic_comments')->where('b_dynamic_id',$v1->id)->count();
            //阅读数
            $reads+= $v1->reads;
        }
        unset($v1);
        $img=DB::name('topics')->where('tags',$info['tags'])->value('topic_img');
        $arr['img']=$this->auth->setFilePath($img);
        $arr['tags_name']=DB::name('labels')->where('id',$info['tags'])->value('name');
        $arr['talk_num']=$talk;
        $arr['read_num']=$reads;
        $arr['dynamics']=$data;
        $this->ApiReturn(1,'',$arr);
    }


    //添加标签
    public function addLabels(){
        $info=$this->request->request();
        $name = trim($info['name']);
        $data = DB::name('labels')->where('name',$name)->select();
        if($data) $this->ApiReturn(0,'该标签已存在,请勿重复添加');
        $arr['name']=$name;
        $arr['addtime']=time();
        $arr['created_at']=date('Y-m-d H:i:s',time());
        $arr['updated_at']=date('Y-m-d H:i:s',time());
        $res=DB::name('labels')->insert($arr);
        if($res){
            $this->ApiReturn(1,'添加成功',$res);
        }else{
            $this->ApiReturn(0,'添加失败');
        }
    }

    //搜索
    public function merge_search(){
        $keywords = $this->request->request('keywords');
        $user_id = $this->user_id;
        if(!$keywords || !$user_id) $this->ApiReturn(0,'缺少参数');
        $search=DB::name('search_history')
            ->where('user_id',$user_id)
            ->where('search',$keywords)
            ->where('type',2)
            ->select();
        if(!$search){
            $info['search']=$keywords;
            $info['user_id']=$user_id;
            $info['addtime']=time();
            DB::name('search_history')->insert($info);
        }
        //用户
        $user=array_slice($this->user_search_hand($user_id,$keywords),0,2);
        //房间
        $rooms=array_slice($this->room_search_hand($user_id,$keywords),0,2);
        //动态
        $dynamics=array_slice($this->dynamics_search_hand($user_id,$keywords),0,2);
        $arr['user']=$user;
        $arr['rooms']=$rooms;
        $arr['dynamics']=$dynamics;
        $this->ApiReturn(1,'',$arr);

    }

    //搜索记录
    public function searhList(){
        $user_id=$this->user_id;
        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $hot=DB::name('search_history')->field(['id','search'])->where('type',1)->order('sort','desc')->select();
        $history=DB::name('search_history')->field(['id','search'])->where('type',2)->where('user_id',$user_id)->select();
        $data['hot']=$hot;
        $data['histor']=$history;
        $this->ApiReturn(1,'',$data);
    }
    //清空搜索记录
    public function cleanSarhList(){
        $user_id=$this->user_id;
        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $res=DB::name('search_history')->where('type',2)->where('user_id',$user_id)->delete();
        if($res){
            $this->ApiReturn(1,'清空成功');
        }else{
            $this->ApiReturn(0,'清空失败');
        }

    }


    //搜索用户
    public function user_search_hand($user_id = null,$keywords = null,$page = 1){
        if(!$user_id || !$keywords) return [];
        //用户
        $user=DB::name('users')
            ->where('nickname','like','%'.$keywords.'%')
            ->whereOr('id',$keywords)
            ->page($page,10)
            ->field(['id','headimgurl','nickname','sex'])
            ->select();
        foreach ($user as $ku => &$vu){
            $vu['headimgurl'] = $this->auth->setFilePath($vu['headimgurl']);
            $vu['is_follow'] = DB::name('follows')->where('user_id',$user_id)->where('followed_user_id',$vu['id'])->where('status',1)->value('id') ? 1 :0;
        }
        unset($vu);
        //return json_decode($user,true);
        return $user;
    }
    //搜索房间
    public function room_search_hand($user_id = null,$keywords = null,$page = 1){
        if(!$user_id || !$keywords) return [];
        //用户
        $rooms=DB::name('rooms')
            ->alias('rooms')
            ->where('rooms.room_name','like','%'.$keywords.'%')
            ->whereOr('rooms.numid',$keywords)
            ->join('users','rooms.uid=users.id','left')
            ->field(['rooms.room_name','rooms.uid','rooms.numid','rooms.hot','rooms.room_cover',
                'users.headimgurl','users.nickname','users.sex'])
            ->order('rooms.hot','desc')
            ->page($page,10)
            ->select();
        foreach ($rooms as $k => &$vr) {
            $vr['headimgurl'] = $this->auth->setFilePath($vr['headimgurl']);
            $vr['room_name'] = urldecode($vr['room_name']);
        }
        unset($vr);
        //return json_decode($rooms,true);
        return $rooms;
    }
    //搜索动态
    public function dynamics_search_hand($user_id = null,$keywords = null,$page = 1){
        if(!$user_id || !$keywords) return [];
        $start=strpos($keywords,'#');
        if($start !== false){
            $name=substr($keywords,$start+1);
            $tags=DB::name('labels')->where('name',$name)->value('id');
        }else{
            $tags='null';
        }
        $dynamics=DB::name('dynamics')
            ->alias('dynamics')
            ->where('dynamics.content','like','%'.$keywords.'%')
            ->whereOr('dynamics.tags','like','%'.$tags.'%')
            ->join('users','dynamics.user_id=users.id')
            ->field(['dynamics.id','dynamics.audio_time','dynamics.user_id','dynamics.image','dynamics.audio','dynamics.video',
                'dynamics.content','dynamics.praise','dynamics.tags','dynamics.addtime','users.headimgurl','users.nickname','users.sex'])
            ->page($page,10)
            ->select();
        if(!$dynamics){
            return [];
        }else{
            $dynamics=$this->dataFormat($dynamics,$user_id);
            //return json_decode($dynamics,true);
            return $dynamics;
        }
    }

    //搜索用户,房间,动态
    public function search_all(){
        $info=$this->request->request();
        $page=  $this->request->request('page') ? : 1;
        $info['user_id'] = $this->user_id;
        if(!$info['user_id'] || !$info['keywords'] || !$info['type']) $this->ApiReturn(0,'缺少参数');
        if($info['type'] == 'user'){
            $data=$this->user_search_hand($info['user_id'],$info['keywords'],$page);
        }elseif($info['type'] == 'room'){
            $data=$this->room_search_hand($info['user_id'],$info['keywords'],$page);
        }elseif($info['type'] == 'dynamic'){
            $data=$this->dynamics_search_hand($info['user_id'],$info['keywords'],$page);
        }else{
            $this->ApiReturn(0,'参数错误');
        }
        $this->ApiReturn(1,'',$data);
    }

    //官方消息
    public function official_message(){
        $user_id=$this->user_id;
        $page=$this->request->request('page') ? : 1;
        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $ids=DB::name('off_reads')->where('user_id',$user_id)
            ->where('is_read',2)
            ->column('off_id');
        $data = DB::name('official_messages')
                            // ->where('id','not in',$ids)
                            ->where('user_id','in',[0,$user_id])
                            ->order('created_at','desc')
                            ->page($page,10)
                            ->select();

        foreach ($data as $k => &$v) {
            $v['img']=$this->auth->setFilePath($v['img']);
            $v['url']=$v['url'] ? : '';
            $v['is_read']=DB::name('off_reads')->where('user_id',$user_id)->where('off_id',$v['id'])->value('id') ? 1 : 0;
            //将未读的消息设为已读
            if($v['is_read'] == 0){
                $arr['off_id']=$v['id'];
                $arr['user_id']=$user_id;
                $arr['addtime']=time();
                DB::name('off_reads')->insert($arr);
            }
        }
        $this->ApiReturn(1,'',$data);
    }

    //未读官方消息数
    public function unread_official_message($user_id){
        //所有官方消息
        $ids=DB::name('official_messages')->where('user_id','in',[0,$user_id])->column('id');
        //已读
        $sum=DB::name('off_reads')->whereIn('off_id',$ids)->where('user_id',$user_id)->count();
        $total=count($ids)-$sum;
        return $total;
    }

    //mini官方
    public function mini_official(){
        $user_id=$this->user_id;
        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $arr['img']=$this->auth->setFilePath($this->getConfig('logo'));
        $arr['name']='啾咪语音';
        $arr['unread']=$this->unread_official_message($user_id);
        $ids=DB::name('off_reads')->where('user_id',$user_id)->where('is_read',1)->column('off_id');
        $data = DB::name('official_messages')->alias('official_messages')
            ->field(['id','title','content','created_at'])
            ->where('official_messages.id','not in',$ids)
            ->where('user_id','in',[0,$user_id])
            ->order('official_messages.created_at','desc')
            ->find();
        $arr['title']= !$data ? '暂无消息' : $data['title'];
        $arr['created_at']= !$data ? '' : $data['created_at'];
        $this->ApiReturn(1,'',$arr);
    }

    //阅读
    public function read_official_message(){
        $id=$this->request->request('id') ? : 0;
        $user_id = $this->user_id;
        if(!$id || !$user_id)   $this->ApiReturn(0,'缺少参数');
        $data=DB::name('off_reads')->where('off_id',$id)->where('user_id',$user_id)->select();
        if($data)   $this->ApiReturn(0,'消息已读或删除');
        $arr['off_id']=$id;
        $arr['user_id']=$user_id;
        $arr['addtime']=time();
        $res=DB::name('off_reads')->insert($arr);
        if($res){
            $this->ApiReturn(1,'阅读成功');
        }else{
            $this->ApiReturn(0,'阅读失败');
        }
    }

    //清空消息--弃用
    public function clear_message(){
        $user_id=$this->user_id;
        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $mess1=DB::name('official_messages')->where('user_id',$user_id)->where('type',1)->column('id');
        $i=0;
        foreach ($mess1 as $k1 => &$v1) {
            $res1=DB::name('official_messages')->where('id',$v1)->delete();
            $res2=DB::name('off_reads')->where('off_id',$v1)->where('user_id',$user_id)->delete();
            $i+=$res1;
            $i+=$res2;
        }
        unset($v1);
        $mess2=DB::name('official_messages')->where('type',2)->column('id');
        foreach ($mess2 as $k2 => &$v2) {
            $data=DB::name('off_reads')->where('off_id',$v2)->where('user_id',$user_id)->where('is_read',1)->select();
            if(!$data){
                $i+=DB::name('off_reads')->insertGetId(['off_id'=>$v2,'user_id'=>$user_id,'is_read'=>2,'addtime'=>time()]);
            }else{
                $i+=DB::name('off_reads')->where('id',$data[0]['id'])->update(['is_read'=>2]);
            }
        }
        unset($v2);
        if($i>0){
            $this->ApiReturn(1,'清空成功');
        }else{
            $this->ApiReturn(0,'清空失败');
        }
    }

    //社区活动
    public function activeList(){
        $data=DB::name('active')->where('enable',1)->order('addtime','desc')->select();
        foreach ($data as $k => &$v) {
            $v['img']=$this->auth->setFilePath($v['img']);
            $v['addtime']=date('Y-m-d H:i:s',$v['addtime']);
        }
        unset($v);
        $this->ApiReturn(1,'',$data);
    }

    //分享动态
    public function share_dynamic(){
        $info=$this->request->request();
        $info['user_id'] = $this->user_id;
        if( !$info['user_id'] || !$info['target_id'] || !$info['hand']) $this->ApiReturn(0,'缺少参数');
        if($info['hand'] == 'add'){
            $data=DB::name('likes')->where('user_id',$info['user_id'])->where('target_id',$info['target_id'])->where('type',3)->select();
            if(!$data){
                $arr['user_id']=$info['user_id'];
                $arr['target_id']=$info['target_id'];
                $arr['type']=3;
                $arr['addtime']=time();
                DB::name('likes')->insertGetId($arr);
            }
            $res=DB::name('dynamics')->where('id',$info['target_id'])->setInc('share',1);
        }elseif($info['hand'] == 'del'){
            $res=DB::name('dynamics')->where('id',$info['target_id'])->setDec('share',1);
        }else{
            $this->ApiReturn(0,'参数错误');
        }
        if($res){
            $this->ApiReturn(1,'操作成功');
        }else{
            $this->ApiReturn(0,'操作失败');
        }
    }












}
