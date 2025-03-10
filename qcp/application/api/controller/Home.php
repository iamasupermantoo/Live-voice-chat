<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\usersmanage\Users;
use app\admin\model\officialsmanage\Onepage;
use think\Db;
use vendor\AlibabaCloud\Client\AlibabaCloud;
use vendor\AlibabaCloud\Client\Exception\ClientException;
use vendor\AlibabaCloud\Client\Exception\ServerException;

class Home extends Api
{
    protected $noNeedLogin = ["star_loft"];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        $this->roomMod    = Db::name('rooms');
        parent::_initialize();
    }

    //轮播
    public function carousel()
    {
        $data = DB::name('home_carousels')->where(['enable'=>1])->order('id desc,sort desc')->select();
        foreach ($data as $k => &$v) {
            $v['img']=$this->auth->setFilePath($v['img']);
            $v['contents']= trim($v['contents']) ? : '';
            $v['url']= trim($v['url']) ? : '0';
        }
        $this->ApiReturn(1,'',$data);
    }

    // 房间类别
    public function room_recommend_categories(){
        $data=DB::name('room_categories')->where(['pid'=>0,'enable'=>1])->field("id,name")->select();
        $this->ApiReturn(1,'',$data);
    }

    // 置顶
    public function is_top(){
        $data = $this->roomMod
            ->alias('rooms')
            ->where('is_top',1)
            ->where('room_status','<>',3)
            ->join('room_categories','rooms.room_type=room_categories.id','left')
            ->join('users','rooms.uid=users.id','left')
            ->order("rooms.hot desc")
            ->field(['rooms.room_name','rooms.numid','rooms.openid','rooms.week_star','rooms.uid','rooms.room_cover','rooms.room_type','users.nickname','users.sex','room_categories.name'])
            ->select();
        $data=$this->roomDataFormat($data);
        $this->ApiReturn(1,'',$data);
    }

    //分类房间
    public function room_recommend_room(){
        $categories=input('categories/d',1);
        $page=input('page/d',1);
       
        //if(!$categories)  $this->ApiReturn(0,'参数错误');
        $data=DB::name('rooms')->where('room_class',$categories)
                                  ->where('room_status','<>',3)
                                  ->alias('a')
                                  ->join('users b','a.uid = b.id')
                                  ->join('room_categories c','a.room_type = c.id')
                                  ->field("a.room_name,a.room_cover,a.uid,a.numid,a.hot,a.openid,a.room_type,a.week_star,b.nickname,b.sex,c.name")
                                  ->order('a.hot desc')
                                  ->page($page,10)
                                  ->select();
        $data=$this->roomDataFormat($data);
        $this->ApiReturn(1,'',$data);
    }


    



    //热门
    public function is_popular()
    {   
        $ids=$this->roomMod->where(['is_top'=>1,'room_status'=>['neq',3]])->column('uid');
        $data = $this->roomMod
            ->alias('rooms')
            // ->where('is_popular',1)
            ->where('room_status','<>',3)
            ->whereNotIn('rooms.uid',$ids)
            ->join('users','rooms.uid=users.id')
            ->join('room_categories','rooms.room_type=room_categories.id')
            ->field(['rooms.room_name','rooms.hot','rooms.numid','rooms.openid',
                'rooms.week_star','rooms.uid','rooms.room_cover','users.nickname','users.sex','room_categories.name'])
            ->order('rooms.hot desc')
            ->limit(3)
            ->select();
        $data=$this->roomDataFormat($data);
        $this->ApiReturn(1,'',$data);
    }




    //密聊
    public function secret_chat()
    {
        $ids=$this->roomMod->where(['is_top'=>1,'room_status'=>['neq',3]])->column('uid');
        $data = $this->roomMod
            ->alias('rooms')
            // ->where('secret_chat',1)
            ->where('room_status','<>',3)
            ->whereNotIn('rooms.uid',$ids)
            ->join('users','rooms.uid = users.id')
            ->join('room_categories','rooms.room_type=room_categories.id')
            ->field(['rooms.room_name','rooms.hot','rooms.numid','rooms.openid','rooms.week_star',
                'rooms.uid','rooms.room_cover','users.nickname','users.sex','room_categories.name'])
            ->order('rooms.hot desc')
            ->limit(3,3)
            ->select();
        $data=$this->roomDataFormat($data);
        $this->ApiReturn(1,'',$data);
    }


    //微星阁
    public function star_loft()
    {
        $sex = input('sex/d',1);
        $data=DB::name('rooms')->alias('a')
                                ->join('users b','a.uid = b.id')
                                ->join('room_categories c','a.room_type = c.id')
                                ->where('b.sex',$sex)
                                ->field("a.room_name,a.hot,a.numid,a.openid,a.week_star,a.uid,a.room_cover,b.nickname,b.sex,c.name")
                                ->orderRaw('rand()')->limit(4)
                                ->select();
    //    if(count($data) < 4)    $this->ApiReturn(0,'暂无数据');
        $data=$this->roomDataFormat($data);
        $this->ApiReturn(1,'',$data);
    }

    // 搜索房间
    public function room_search(){
        $search = input('search',0);
        if(!$search)    $this->ApiReturn(0,'缺少参数');
        $where['uid|numid']=$search;
        $where['room_status']=['neq',3];
        $data = $this->roomMod->alias('rooms')
                                ->where($where)
                                ->join('users','rooms.uid=users.id')
                                ->join('room_categories','rooms.room_type=room_categories.id')
                                ->field(['rooms.room_name','rooms.numid','rooms.openid','rooms.week_star','rooms.uid','rooms.room_cover',
                                    'users.nickname','users.sex','room_categories.name'])
                                ->select();
        $data=$this->roomDataFormat($data);
        $this->ApiReturn(1,'',$data);
        
    }




}
