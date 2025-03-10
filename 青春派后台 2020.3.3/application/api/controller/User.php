<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use fast\Random;
use think\Validate;
use think\Db;
use app\admin\model\usersmanage\Users;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
/**
 * 会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['user_test',"get_user_info","androidGoodsList","room_rankingb","room_rankingb","fjzxnum"];
    protected $noNeedRight = '*';

	public function fjzxnum(){
		$roomid=input('roomid');
		$usidinfo=Db::name("rooms")->where("numid='{$roomid}'")->find();
		$usid=$usidinfo['roomVisitor'];
		$where["id"]=['in',$usid];
		
		$usinfo = Db::name("users")->where($where)->field(['id', 'headimgurl', 'nickname','sex'])->select();
	//		dump($usinfo);die;
			foreach ($usinfo as $k=>&$v){
				
						
					$c=	strpos($v['headimgurl'],'http');
					
					if ($c !== false) {
					  //  echo 'true';
					} else {
					    $v['headimgurl']="http://b.juchuyuncang.com".$v['headimgurl'];
					}
					
				
			}
		//	die;
			$num=count($usinfo);
			$data['data']=$usinfo;
			$data['num']=$num;
		//	dump($num);die;
		    $this->ApiReturn(1,'',$data);
		
		
		
	}
 public function room_rankingc() {
          $type = $this->request->request('type') ? : 1; //1日榜2周榜3月榜

        
                $user_id = $this->user_id;

                $uid = input('uid/d',0);


        $user = DB::name('users')->field(['id', 'headimgurl', 'nickname','sex'])->find($user_id);
        $where['uid']=$uid;
     //       $keywords = 'fromUid';
        
        if ($type == 1) {
            $time = 'today';
        } elseif ($type == 2) {
            $time = 'week';
        } elseif ($type == 3) {
            $time = 'month';
        }
        
        $keywords="fromUid";
        
        $query = DB::name('gift_logs')->where($where)->whereTime('created_at', $time);
        $data=$query->field("sum(giftPrice) as exp ,". $keywords)->group($keywords)->order("exp desc")->limit(30)->select();
     //   dump($data);
        $i=$l=0;
        foreach ($data as $k => & $v) {
            $i++;
            $users = DB::name('users')->field('headimgurl,nickname,sex')->find($v[$keywords]);
            $v['id'] = $v[$keywords];
            $v['mizuan'] = $v['exp'] + 0;
            $v['img'] = $this->auth->setFilePath($users['headimgurl']);
            $v['name'] = $users['nickname'];
            $v['sex'] = $users['sex'];
            $v['sort'] =0;
          
            if ($v[$keywords] == $user_id) $l = $i;
        }
        unset($v);
        $user['sort'] =0;
        $user['img'] = $this->auth->setFilePath($user['headimgurl']);
   
        $exp=DB::name('gift_logs')->whereTime('created_at', $time)->where($keywords,$user_id)->sum('giftPrice');
        $user['mizuan'] = $exp;
        //$arr['user'][0] = $user;
        //空数据
        $kong['mizuan']=0;
        $kong['id']=0;
        $kong['sex']=0;
        $kong['img']='';
        $kong['name']='';
    
        $data[0] = isset($data[0]) ? $data[0] : $kong;
        $data[1] = isset($data[1]) ? $data[1] : $kong;
        $data[2] = isset($data[2]) ? $data[2] : $kong;
        $arr['top'] = array_slice($data, 0, 3);
        $arr['other'] = array_slice($data, 3);
        $this->ApiReturn(1,'',$arr);
      
    }
    
    
	public function room_rankingb() {
       //   $class = $this->request->request('class')  ? : 0; //1贡献榜(金锐)2总榜3星锐
        $type = $this->request->request('type') ? : 1; //1日榜2周榜3月榜
		
        $user_id = input('uid/d',0);

        $where['uid']=$user_id;
//echo $user_id;die;
        $keywords='fromUid';

        // $roominfo=Db::name("rooms")->where("uid='{$room_id}'")->find();
        // $qb_uid=$roominfo['roomVisitor'];

        if ($type == 1) {
            $time = 'today';
        } elseif ($type == 2) {
            $time = 'week';
        } elseif ($type == 3) {
            $time = 'month';
        }
        $query = DB::name('gift_logs')->where($where)->whereTime('created_at', $time);
        $data=$query->field("sum(giftPrice) as exp ,". $keywords)->group($keywords)->order("exp desc")->limit(30)->select();
   //     dump()
      //  dump($data);die;
         $i=$l=0;
        $info=[];
        foreach ($data as $k => & $v) {
           $i++;
            $users = DB::name('users')->field('headimgurl,nickname,sex')->find($v[$keywords]);
       
            $v['user_id'] = $v[$keywords];
            $v['exp'] = $v['exp'] + 0;
            $v['headimgurl'] = $users['headimgurl'];
            $v['nickname'] = $users['nickname'];
            $v['sex'] = $users['sex'];
                //dump($v);die;
            $v['stars_img'] = $this->getVipLevel($v[$keywords], 1 ,'img');
            $v['gold_img'] = $this->getVipLevel($v[$keywords], 2 ,'img');
            $v['vip_img'] = $this->getVipLevel($v[$keywords], 3 ,'img');
            if ($v[$keywords] == $user_id) $l = $i;
        }
        unset($v);
        $user['sort'] = $l ? (string)$l : '99+';
        $user['headimgurl'] =1;
        $user['stars_img'] = 2;
        $user['gold_img'] = 3;
        $user['vip_img'] = 4;
        $exp=DB::name('gift_logs')->whereTime('created_at', $time)->where($keywords,$user_id)->sum('giftPrice');
        $user['exp'] = $exp;
        $arr['user'][0] = $user;
        //空数据
        $kong['exp']=0;
        $kong['user_id']=0;
        $kong['sex']=0;
        $kong['headimgurl']='';
        $kong['nickname']='';
        $kong['stars_img']='';
        $kong['gold_img']='';
        $kong['vip_img']='';
        $data[0] = isset($data[0]) ? $data[0] : $kong;
        $data[1] = isset($data[1]) ? $data[1] : $kong;
        $data[2] = isset($data[2]) ? $data[2] : $kong;
        
        $arr['top'] = array_slice($data, 0, 3);
        $arr['other'] = array_slice($data, 3);
     
        $this->ApiReturn(1,'',$arr);
    }
    

    public function _initialize()
    {
        $this->usersMod    = Db::name('users');
        parent::_initialize();
    }

    public function user_test(){
      	$this->ApiReturn(1,'');
    }

    



    

    //兑换米钻卡
    public function exchange_mizuan_card(){
        $id=input('id/d',0);
        $user_id=$this->user_id;
        if(!$id)    $this->ApiReturn(0,'参数错误');
        $pack=DB::name('pack')->where(['user_id'=>$user_id,'type'=>3,'target_id'=>$id])->find();
        $wares=DB::name('wares')->find($id);
        // $this->ApiReturn(1,'',$wares);
        if($wares['color'] != 'mizuan')     $this->ApiReturn(0,'参数错误');
        if(!$pack || $pack['num'] < 1)    $this->ApiReturn(0,'米钻卡数量不足');

        $price=$wares['price'];
        $res=userStoreInc($user_id,$price,18,'mizuan');
        if($res){
            userPackStoreDec($user_id,3,$id,1);
            $this->ApiReturn(1,'米钻卡兑换成功');
        }else{
            $this->ApiReturn(0,'米钻卡兑换失败');
        }
    }


    //解除CP
    public function remove_cp(){
        $user_id=$this->user_id;
        $id=input('id/d',0);
        $where['user_id|fromUid']=$user_id;
        $where['id']=$id;
        $where['status']=1;
        $cp=DB::name('cp')->where($where)->find();
        if(!$cp)    $this->ApiReturn(0,'参数错误');
        $res=DB::name('cp')->delete($id);
        if($res){
            DB::name('gem_log')->where(['cp_id'=>$id])->delete();
            //添加个人消息
            $target_user_id= $user_id == $cp['user_id'] ? $cp['fromUid'] : $cp['user_id'];
            $user_nick=getUserField($user_id,'nickname');
            $this->addOfficialMessage('',$target_user_id,'守护'.$user_nick.'与你解除了守护CP');
            //重置最高cp等级
            $level=$this->getUserMaxCpLevel($user_id,'level');
            DB::name('user_total')->where(['user_id'=>$user_id])->update(['cp_level'=>$level]);
            $level2=$this->getUserMaxCpLevel($target_user_id,'level');
            DB::name('user_total')->where(['user_id'=>$target_user_id])->update(['cp_level'=>$level2]);
            $this->ApiReturn(1,'解除CP成功');
        }else{
            $this->ApiReturn(0,'解除CP失败');
        }
    }

    //cp详情
    public function cp_desc(){
        $user_id=$this->user_id;
        $id=input('id/d',0);
        $where['user_id|fromUid']=$user_id;
        $where['id']=$id;
        $where['status']=1;
        $cp=DB::name('cp')->where($where)->field("id,exp,wares_id,user_id,fromUid,agreetime")->select();
        if(!$cp)    $this->ApiReturn(0,'参数错误');
        $cp=$this->cpDataFormat($cp)[0];
        $cp_level=$this->getCpLevel($id);
        $cp['next_cp_num']=getNextLevel(5,$cp_level,'exp');
      	if($cp['exp'] >= $cp['next_cp_num'] )   $cp['exp'] = $cp['next_cp_num'];
        $cp['next_cp_level']=getNextLevel(5,$cp_level,'level');
        $cp_auth = DB::name('vip_auth')->where(['type'=>5])->select();
        foreach ($cp_auth as $k => &$v) {
            $v['is_on'] = ($cp_level >= $v['level']) ? 1 : 0;
            $v['img_0'] = $this->auth->setFilePath($v['img_0']);
            $v['img_1'] = $this->auth->setFilePath($v['img_1']);
        }
        unset($v);
        $arr['cp']=$cp;
        $arr['auth']=$cp_auth;
        $this->ApiReturn(1,'',$arr);
    }

    //装扮
    public function dress_up(){
        $id = $this->request->request('id');
        $type = $this->request->request('type');
        $user_id=$this->user_id;

        $pack=DB::name('pack')->where(['user_id'=>$user_id,'target_id'=>$id])->find();
        if(!$pack)  $this->ApiReturn(0,'背包中没有此物品');
        if($pack['expire']  && $pack['expire'] < time())    $this->ApiReturn(0,'物品已过期');
        if(!in_array($pack['type'],[4,5,6,7]))  $this->ApiReturn(0,'此物品不能装扮');
        $ware=DB::name('wares')->where(['id'=>$id,'type'=>$pack['type']])->find();
        if(!$ware) $this->ApiReturn(0,'参数错误');
        $dre_type='dress_'.$pack['type'];
        $note='';
        if($type == 1){
            $dress=$this->usersMod->where(['id'=>$user_id])->field($dre_type)->value($dre_type);
            if($dress == $id)   $this->ApiReturn(0,'您已装扮了此物品');
            $res=$this->usersMod->where(['id'=>$user_id])->update([$dre_type=>$id]);
        }elseif($type == 2){
            $dress=$this->usersMod->where(['id'=>$user_id])->field($dre_type)->value($dre_type);
            if(!$dress)   $this->ApiReturn(0,'您已取消了此装扮');
            $res=$this->usersMod->where(['id'=>$user_id])->update([$dre_type=>0]);
            $note="取消";
        }
        if($res){
            $this->ApiReturn(1,$note.'装扮成功');
        }else{
            $this->ApiReturn(0,$note.'装扮失败,请刷新后重试');
        }
    }

    //解锁并装扮高级物品
    public function unlock_dress(){
        $data=DB::name('users')->where('1=1')->field('id,nickname')->select();
        foreach ($data as $k => &$v) {
            $this->unlock_dress_hand($v['id']); //根据用户vip等级自动解锁装扮
            $this->unlock_dress_up($v['id']);   //自动装扮高等级装扮
        }
        $this->ApiReturn(1,'');
    }

    //根据用户vip等级自动解锁装扮
    protected function unlock_dress_hand($user_id){
        // $user_id=$this->user_id;
        $vip=$this->getVipLevel($user_id,3);
        $where_pack['user_id']=$user_id;
        $where_pack['type']=['in','4,5,6,7,8'];
        $ids=DB::name('pack')->where($where_pack)->column('target_id');
        $where['get_type']=1;
        $where['enable']=1;
        $where['level']=['elt',$vip];
        $where['type']=['in','4,5,6,7,8'];
        $where['id']=['not in',$ids];
        $wares=DB::name('wares')->where($where)->field('id,type,expire')->select();
        if(!$wares) return 0;
        $i=0;
        foreach ($wares as $k => &$v){
            $pack=DB::name('pack')->where(['user_id'=>$user_id,'type'=>$v['type'],'target_id'=>$v['id']])->value('id');
            if($pack)   continue;
            $arr['user_id']=$user_id;
            $arr['type']=$v['type'];
            $arr['target_id']=$v['id'];
            $arr['addtime']=time();
            $arr['expire']= $v['expire'] ? time()+($v['expire']*86400) : 0;
            $i+=Db::name('pack')->insert($arr);
        }
    }
    //自动装扮高等级装扮
    protected function unlock_dress_up($user_id){
        $type=[4,5,6,7];
        foreach ($type as $k => &$v) {
            $id=DB::name('pack')->where(['user_id'=>$user_id,'get_type'=>1,'type'=>$v])->order('id desc')->limit(1)->value('target_id');
            if($id){
                DB::name('users')->where(['id'=>$user_id])->update(['dress_'.$v=>$id]);
            }
        }
    }




    //我的背包  type
//        1宝石
//        2礼物--未用
//        3卡券
//        4头像框
//        5气泡框
//        6进场特效
//        7麦上光圈
//        8徽章

    public function my_pack(){
        $user_id=$this->user_id;
        $type = $this->request->request('type');
        if(!in_array($type,[1,2,3,4,5,6,7]))    $this->ApiReturn(0,'参数错误');
        $where['a.user_id']=$user_id;
        $where['a.type']=$type;
        $where['b.enable']=1;
        if($type == 2){
            $data=DB::name('pack')->alias('a')->join('gifts b','a.target_id = b.id')
                ->where($where)
                ->field("a.*,b.name,b.show_img,b.price")
                ->select();
        }else{
            $data=DB::name('pack')->alias('a')->join('wares b','a.target_id = b.id')
                ->where($where)
                ->field("a.*,b.name,b.show_img,b.title,b.color")
                ->select();
        }
        if(in_array($type,[4,5,6,7])){
            $dress_id=Db::name('users')->where(['id'=>$user_id])->value("dress_".$type);
        }
        foreach ($data as $k => &$v) {
            $v['show_img']=$this->auth->setFilePath($v['show_img']);
            $v['is_dress']=0;
            if(in_array($type,[4,5,6,7])){
                $v['title']= empty($v['expire']) ? "永久" : date('Y-m-d H:i:s',$v['expire'])."到期";
                $v['is_dress']= $dress_id == $v['target_id']  ? 1 : 0;
                $v['color']= $v['color'] ? : '';
            }elseif($type == 2){
                $v['title'] = "拥有".$v['num']."个  价值".$v['num']*$v['price']."米钻";
                $v['color'] = '';
            }else{
                $v['title']="拥有".$v['num']."个 ".$v['title'];
                $v['color']= $v['color'] ? : '';
            }
        }
        $this->ApiReturn(1,'',$data);
    }



















    /**************************************** 以 上 二 期 新 增 *************************************************************/
    //修改密码
    public function update_pwd(){
        $data = $this->request->request();
        $data['user_id'] = $this->user_id;
        if( empty($data['old_pass']) || empty($data['new_pass']) || empty($data['re_pass']))  $this->ApiReturn(0,'缺少参数');


        $user = DB::name('users')->field(['id','pass','salt'])->where('id',$data['user_id'])->find();

        $pwd = $this->pwdMd5($data['old_pass'],$user['salt']);
        if($user['pass'] != $pwd)  $this->error(__("'原密码错误'"));
        if($data['new_pass'] != $data['re_pass']) $this->error(__("'两次密码输入不一致'"));
        if(!isPassword($data['new_pass']))  $this->error(__("'密码为长度6-20位的数字或字母混合组成!'"));
        $arr['salt'] = substr(md5(time()), 0 , 3);
        $arr['pass'] = $this->pwdMd5($data['new_pass'],$arr['salt']);
        $res=DB::name('users')->where('id',$data['user_id'])->update($arr);
        if($res){
            $this->ApiReturn(1,'修改成功');
        }else{
            $this->ApiReturn(0,'修改失败');
        }
    }
    //搜索用户
    public function search_user() {
        $data = $this->request->request();
        $data['uid'] = $this->user_id;
        $keywords = $data['keywords'];
        $user = DB::name('users')->field(['id', 'nickname', 'headimgurl'])->where(['id' => $data['keywords']])->select();
        if (!$user) {
            $this->success(__('暂无此用户'));
        }
        if ($user[0]['headimgurl']){
            $user[0]['headimgurl'] = $this->auth->setFilePath($user[0]['headimgurl']);
        }
        $rooms = DB::name('rooms')->where('uid', $data['uid'])->value('roomAdmin');
        $room_arr = explode(',', $rooms);
        $user[0]['is_admin'] = in_array($user[0]['id'], $room_arr) ? 1 : 0;
        $this->success(__('请求成功'),$user);
    }

    public function rz($idNo = null, $name = null) {
        if (!$idNo || !$name) return false;
        $host = "https://idenauthen.market.alicloudapi.com";
        $path = "/idenAuthentication";
        $method = "POST";
        $appcode = "8e3ae1c206a34bba8e70830323ce16d6";
        $headers_arr = array();
        array_push($headers_arr, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers_arr, "Content-Type" . ":" . "application/x-www-form-urlencoded; charset=UTF-8");
        $querys = "";
        $bodys = "idNo=" . $idNo . "&name=" . $name;
        $url = $host . $path;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_arr);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        $str = curl_exec($curl);
        $res = substr($str, strpos($str, '{'));
        return json_decode($res, true);
    }

    //排行榜
    public function ranking() {
        $class = $this->request->request('class')  ? : 0; //1星锐2金锐
        $type = $this->request->request('type') ? : 1; //1日榜2周榜3月榜
        $user_id = $this->user_id;
        if (!in_array($class, [1, 2]) || !in_array($type, [1, 2, 3])) $this->error('参数错误');

        $user = DB::name('users')->field(['id', 'headimgurl', 'nickname','sex'])->find($user_id);
        if ($class == 1) {
            $keywords = 'fromUid';
        } elseif ($class = 2) {
            $keywords = 'user_id';
        }
        if ($type == 1) {
            $time = 'today';
        } elseif ($type == 2) {
            $time = 'week';
        } elseif ($type == 3) {
            $time = 'month';
        }
        $query = DB::name('gift_logs')->whereTime('created_at', $time);
        $data=$query->field("sum(giftPrice) as exp ,". $keywords)->group($keywords)->order("exp desc")->limit(30)->select();
        $i=$l=0;
        foreach ($data as $k => & $v) {
            $i++;
            $users = DB::name('users')->field('headimgurl,nickname,sex')->find($v[$keywords]);
            $v['user_id'] = $v[$keywords];
            $v['exp'] = $v['exp'] + 0;
            $v['headimgurl'] = $this->auth->setFilePath($users['headimgurl']);
            $v['nickname'] = $users['nickname'];
            $v['sex'] = $users['sex'];
            $v['stars_img'] = $this->getVipLevel($v[$keywords], 1 ,'img');
            $v['gold_img'] = $this->getVipLevel($v[$keywords], 2 ,'img');
            $v['vip_img'] = $this->getVipLevel($v[$keywords], 3 ,'img');
            if ($v[$keywords] == $user_id) $l = $i;
        }
        unset($v);
        $user['sort'] = $l ? (string)$l : '99+';
        $user['headimgurl'] = $this->auth->setFilePath($user['headimgurl']);
        $user['stars_img'] = $this->getVipLevel($user['id'], 1 ,'img');
        $user['gold_img'] = $this->getVipLevel($user['id'], 2 ,'img');
        $user['vip_img'] = $this->getVipLevel($user['id'], 3 ,'img');
        $exp=DB::name('gift_logs')->whereTime('created_at', $time)->where($keywords,$user_id)->sum('giftPrice');
        $user['exp'] = $exp;
        $arr['user'][0] = $user;
        //空数据
        $kong['exp']=0;
        $kong['user_id']=0;
        $kong['sex']=0;
        $kong['headimgurl']='';
        $kong['nickname']='';
        $kong['stars_img']='';
        $kong['gold_img']='';
        $kong['vip_img']='';
        $data[0] = isset($data[0]) ? $data[0] : $kong;
        $data[1] = isset($data[1]) ? $data[1] : $kong;
        $data[2] = isset($data[2]) ? $data[2] : $kong;
        $arr['top'] = array_slice($data, 0, 3);
        $arr['other'] = array_slice($data, 3);
        $this->ApiReturn(1,'',$arr);
    }

    //未读点赞,收藏,转发,评论,回复数
    public function unreadLookHand($user_id) {
        $ids = DB::name('dynamics')->field('GROUP_CONCAT(id) as ids')->where('user_id', $user_id)->find();
        $ids = $ids['ids'];
        $data[] = DB::name('likes')->where('target_id',"in",$ids)->where('type', 1)->where('is_read', 0)->count(); //点赞
        $data[] = DB::name('likes')->where('target_id',"in",$ids)->where('type', 2)->where('is_read', 0)->count(); //收藏
        $data[] = DB::name('likes')->where('target_id',"in",$ids)->where('type', 3)->where('is_read', 0)->count(); //分享
        $data[] = DB::name('dynamic_comments')->where('b_dynamic_id',"in",$ids)->where('is_read', 0)->count(); //评论
        $data[] = DB::name('dynamic_comments')->where('hf_uid', $user_id)->where('is_read', 0)->count(); //回复
        return $data;
    }

    //未读消息
    public function unreadMessage() {
        $user_id = $this->user_id;
        if (!$user_id) $this->ApiReturn(0, '缺少参数');

        $user = DB::name('users')->field(['id'])->where('id', $user_id)->find();
        if (!$user) $this->ApiReturn(0, '暂无此用户');
        $res = $this->unreadLookHand($user_id);
        $arr['total'] = array_sum($res);
        $this->ApiReturn(1, '请求成功', $arr);
    }

    //消息页面xx
    public function message() {
        $user_id = $this->user_id;
        $page = $this->request->request('page');
        if (!$user_id) $this->ApiReturn(0, '缺少参数');
        $user = DB::name('users')->field('id')->where(array('id'=>$user_id))->find();
        if (!$user) $this->ApiReturn(0, '暂无此用户');
        //echo $user_id;die;
        $ids = DB::name('dynamics')->where('user_id', $user_id)->column('id');
        $data['sign'] = DB::name('dynamics')->where('user_id', $user_id)->sum('praise');
        $data['coll'] = DB::name('likes')->where('target_id','in', $ids)->where('type', 2)->count(); //收藏
        $data['share'] = DB::name('likes')->where('target_id','in', $ids)->where('type', 3)->count(); //分享
        //评论我的
        $arr1=DB::name('dynamic_comments')->where('b_dynamic_id','in',$ids)
                                        ->where(['user_id'=>['neq',$user_id]])
                                        ->page($page,5)
                                        ->order('created_at desc')
                                        ->select();
        //回复我的                                        
        $arr2=DB::name('dynamic_comments')->where(['hf_uid'=>$user_id])
                                        ->page($page,5)
                                        ->order('created_at desc')
                                        ->select();
        $arr=array_merge($arr1,$arr2);
        foreach ($arr as $k => &$v) {
            $v['is_reply'] = $v['hf_uid'] ? 1 : 0;
            $img=DB::name('users')->where('id',$v['user_id'])->value('headimgurl');
            $v['headimgurl']=$this->auth->setFilePath($img);
            $v['nickname']=DB::name('users')->where('id',$v['user_id'])->value('nickname');
            $v['content']= $v['content'] ? urldecode($v['content']) : '';
            $dynamics = DB::name('dynamics')->field(['image', 'audio','audio_time', 'video'])->find($v['b_dynamic_id']);
            if($dynamics){
                $image = json_decode($dynamics['image'], true);
                $v['image'] = empty($image) ? '' : $this->auth->setFilePath($image[0]);
                $v['audio'] = $this->auth->setFilePath($dynamics['audio']);
                $v['video'] = $this->auth->setFilePath($dynamics['video']);
                $v['audio_time'] = $dynamics['audio_time'] ? : '';
            }
        }
        $data['comment'] = $arr;
        //阅读点赞,收藏,转发
        $ids2 = DB::name('dynamics')->where('user_id', $user_id)->column('id');
        DB::name('likes')->where('target_id','in', $ids2)
            ->where('type','in' ,[1,2,3])
            ->update(['is_read' => 1]); //点赞,收藏,分享
        $this->ApiReturn(1, '', $data);
    }



    //好友,关注,粉丝
    public function userFriend() {
        $user_id = $this->user_id;
        $page = $this->request->request('page') ? : 0;
        if (!$user_id) $this->ApiReturn(0, '缺少参数');
        $user = DB::name('users')->field('id')->find($user_id);
        if (!$user) $this->ApiReturn(0, '暂无此用户');
        $type = $this->request->request('type') ? : 1;
        if (!in_array($type, [1, 2, 3])) $this->ApiReturn(0, '参数错误');
        $followed_user_id = DB::name('follows')
            ->where('user_id', $user_id)
            ->where('status', 1)
            ->column('followed_user_id');
        //$this->ApiReturn(1,'',$followed_user_id);
        if ($type == 1) { //好友
            $query = DB::name('follows')
                ->alias('follows')
                ->whereIn('follows.user_id', $followed_user_id)
                ->where('follows.followed_user_id', $user_id)
                ->where('follows.status', 1)
                ->join('users', 'follows.user_id=users.id','left');

        } elseif ($type == 2) { //关注
            $query = DB::name('follows')
                ->alias('follows')
                ->where('follows.user_id', $user_id)
                ->where('follows.status', 1)
                ->join('users', 'follows.followed_user_id=users.id','left');
        } elseif ($type == 3) { //粉丝
            $query = DB::name('follows')
                ->alias('follows')
                ->where('follows.followed_user_id', $user_id)
                ->where('follows.status', 1)
                ->join('users', 'follows.user_id = users.id','left');
        }
        $data=$query->field(['users.id', 'users.headimgurl', 'users.nickname', 'users.sex', 'users.ry_uid'])
            ->page($page,10)
            ->limit(10)
            ->select();
        foreach ($data as $ke => & $va) {
            $va['headimgurl'] = $this->auth->setFilePath($va['headimgurl']);
            $va['type'] = $type;
            $va['ry_uid'] =$va['ry_uid'] ? : '';
            if($type == 3){
                $va['is_follow'] = DB::name('follows')
                    ->where('user_id', $user_id)
                    ->where('followed_user_id', $va['id'])
                    ->where('status', 1)
                    ->value('id') ? 1 : 0;
            }else{
                $va['is_follow'] = 1;
            }
        }
        $this->ApiReturn(1, '', $data);
    }


    //拉黑
    public function pull_black() {
        $user_id = $this->user_id;
        $from_uid = $this->request->request('from_uid') ? : 0;
        if (!$user_id || !$from_uid) $this->ApiReturn(0, '缺少参数');
        if ($user_id == $from_uid) $this->ApiReturn(0, '不能拉黑自己');

        $data = DB::name('black')->where('user_id', $user_id)->field(['id', 'status'])->where('from_uid', $from_uid)->select();
        if (!$data) {
            $arr['user_id'] = $user_id;
            $arr['from_uid'] = $from_uid;
            $arr['addtime'] = time();
            $res = DB::name('black')->insert($arr);
            //取消关注
            $da = DB::name('follows')->where('user_id', $user_id)->where('followed_user_id', $from_uid)->select();
            if ($da) {
                DB::name('follows')->where('user_id', $user_id)->where('followed_user_id', $from_uid)->update(['status' => 2]);
            }
        } else {
            if ($data[0]['status'] == 1) {
                $this->ApiReturn(0, '该用户已经在黑名单，请不要重复设置');
            } elseif ($data[0]['status'] == 2) {
                $res = DB::name('black')->where('id', $data[0]['id'])->update(['status' => 1]);
            }
        }
        if ($res) {
            include_once ROOT_PATH . '/vendor/RongCloud/RongCloud.php';
            $AppKey = $this->getConfig('ry_app_key');
            $AppSecret = $this->getConfig('ry_app_secret');
            $RongSDK = new \RongCloud\RongCloud($AppKey,$AppSecret);
            $res = $RongSDK->getUser()->Blacklist()->add(['id'=>$user_id,'blacklist'=>[$from_uid]]);
            $this->ApiReturn(1, '加入黑名单成功!');
        } else {
            $this->ApiReturn(0, '加入黑名单失败!');
        }
    }

    //取消拉黑
    public function cancel_black() {
        $user_id = $this->user_id;
        $from_uid = $this->request->request('from_uid') ? : 0;
        if (!$user_id || !$from_uid) $this->ApiReturn(0, '缺少参数');

        $data = DB::name('black')->field(['id', 'status'])->where('user_id', $user_id)->where('from_uid', $from_uid)->select();
        if (!$data) {
            $this->ApiReturn(0, '操作失败,对方不在黑名单中');
        } else {
            if ($data[0]['status'] == 1) {
                $res = DB::name('black')->where('id', $data[0]['id'])->update(['status' => 2]);
            } elseif ($data[0]['status'] == 2) {
                $this->ApiReturn(0, '操作失败,对方不在黑名单中');
            }
        }
        if ($res) {
            include_once ROOT_PATH . '/vendor/RongCloud/RongCloud.php';
            $AppKey = $this->getConfig('ry_app_key');
            $AppSecret = $this->getConfig('ry_app_secret');
            $RongSDK = new \RongCloud\RongCloud($AppKey,$AppSecret);
            $res = $RongSDK->getUser()->Blacklist()->remove(['id'=>$user_id,'blacklist'=>[$from_uid]]);
            $this->ApiReturn(1, '取消拉黑成功!');
        } else {
            $this->ApiReturn(0, '取消拉黑失败!');
        }
    }

    //黑名单列表
    public function blackList() {
        $user_id = $this->user_id;
        $page    = $this->request->request('page') ? : 0;

        $ids     = getUserBlackList($user_id);
        $data    = DB::name('users')->field(['id', 'headimgurl', 'nickname'])->whereIn('id', $ids)->limit(10)->select();

        foreach ($data as $k => & $v) {
            $v['headimgurl'] = $this->auth->setFilePath($v['headimgurl']);
        }
        $this->ApiReturn(1, '', $data);
    }

    //添加关注
    public function follow() {
        $data = $this->request->request();
        $user_id = $this->user_id;
        $followed_user_id = $data['followed_user_id'];

        if ($user_id == $followed_user_id) $this->ApiReturn(0, '不能关注自己');
        $res = DB::name('follows')->where('user_id', $user_id)->where('followed_user_id', $followed_user_id)->select();
        if (!$res) {
            $info['user_id'] = $user_id;
            $info['followed_user_id'] = $followed_user_id;
            $info['status'] = 1;
            $info['created_at'] = date('Y-m-d H:i:s', time());
            $info['updated_at'] = date('Y-m-d H:i:s', time());
            $result = DB::name('follows')->insert($info);
        } else {
            if ($res[0]['status'] == 2) {
                $result = DB::name('follows')->where('id', $res[0]['id'])->update(['status' => 1]);
            } else {
                $this->ApiReturn(0, '请勿重复关注');
            }
        }
        if ($result) {
            $this->ApiReturn(1, '关注成功');
        } else {
            $this->ApiReturn(0, '关注失败');
        }
    }

    //取消关注
    public function cancel_follow() {
        $data = $this->request->request();
        $user_id = $this->user_id;
        $followed_user_id = $data['followed_user_id'];
        $res = DB::name('follows')->where('user_id', $user_id)->where('followed_user_id', $followed_user_id)->where('status', 1)->select();
        if ($res) {
            $result = DB::name('follows')->where('id', $res[0]['id'])->update(['status' => 2, 'updated_at' => date('Y-m-d H:i:s', time()) ]);
            if ($result) {
                $this->ApiReturn(1, '取消关注成功');
            } else {
                $this->ApiReturn(0, '取消关注失败');
            }
        } elseif (!$res) {
            $this->ApiReturn(0, '尚未关注对方');
        }
    }

    // 帮助与反馈-获取联系方式
    public function official() {
        $data = DB::name('officials')->select();
        foreach ($data as $key => &$v) {
            $v['img']=$this->auth->setFilePath($v['img']);
        }
        if ($data) {
            $this->ApiReturn(1, '获取成功', $data);
        } else {
            $this->ApiReturn(0, '获取失败');
        }
    }


    //个人主页
    public function user_home_page() {
        $user_id = $this->user_id;
        $page = $this->request->request('page') ? : 0;
        $from_uid = $this->request->request('from_uid') ? : $user_id;
        if (!$user_id || !$from_uid) $this->ApiReturn(0, '缺少参数');
        //是否拉黑
        $is_black = DB::name('black')->where('user_id', $from_uid)->where('from_uid', $user_id)->where('status', 1)->value('id');
        if ($is_black) $this->ApiReturn(2, '暂无权限查看此用户主页');
        // $user = DB::name('users')->field(['id', 'headimgurl', 'nickname', 'sex', 'birthday', 'constellation', 'city','ry_uid'])->where('id', $from_uid)->find();
        $user=DB::name('users')->field("id,headimgurl,nickname,sex,birthday,constellation,city,ry_uid")->where(['id'=>$from_uid])->find();
        $user['ry_uid']=$user['ry_uid'] ? : '';
        $user['headimgurl']=$this->auth->setFilePath($user['headimgurl']);
        $user['constellation']=$user['constellation'] ? : '';
        $user['age'] = getAge($user['birthday']);
        $user['fabu'] = DB::name('dynamics')->where('user_id', $from_uid)->count();
        $user['follows_num'] = DB::name('follows')->where('user_id', $from_uid)->where('status', 1)->count();
        $user['fans_num'] = DB::name('follows')->where('followed_user_id', $from_uid)->where('status', 1)->count();
        $user['star_level'] = $this->getVipLevel($from_uid, 1);
        $user['gold_level'] = $this->getVipLevel($from_uid, 2);
        $user['vip_level'] = $this->getVipLevel($from_uid, 3);
        $user['hz_level'] = $this->getHzLevel($from_uid);
        $user['is_follow'] = DB::name('follows')->where('user_id', $user_id)->where('followed_user_id', $from_uid)->where('status', 1)->value('id') ? 1 : 0;
        //房间信息
        $room_id = $this->userNowRoom($from_uid);
        if ($room_id) {
            $roomInfo = DB::name('rooms')->field(['uid', 'room_name', 'hot','room_cover'])->where('uid', $room_id)->find();
            $roomInfo['room_name'] = urldecode($roomInfo['room_name']);
        } else {
            $roomInfo =(object)[];
        }
        //礼物
        $gifts = DB::name('gift_logs')
            ->alias('gift_logs')
            ->where('fromUid', $from_uid)
            ->join('gifts', 'gift_logs.giftId = gifts.id')
            ->field('giftId,giftName,gifts.show_img' . ",sum(giftNum) as sum")
            ->group('giftId')
            ->order('sum', 'desc')
            ->select();
        foreach ($gifts as $k => & $v) {
            $v['img'] = $this->auth->setFilePath($v['show_img']);
        }
        unset($v);
        //合并爆音卡
        $byk_sum=DB::name('baoyinka')->where(['fromUid'=>$from_uid,'wares_id'=>6])->field("wares_id,sum(num) as sum")->find()['sum'];
        $byk_img=DB::name('wares')->where(['id'=>6])->value('show_img');
        $gifts_byk['giftId']=6;
        $gifts_byk['giftName']='爆音卡';
        $gifts_byk['show_img']='';
        $gifts_byk['sum']=$byk_sum;
        $gifts_byk['img']=$this->auth->setFilePath($byk_img);
        if($byk_sum > 0)    $gifts[]=$gifts_byk;


        //荣誉
        $glory=array();
        if($user['hz_level'] > 0){
            $arr1['type']=4;
            $arr1['level']=$user['hz_level'];
            $wares=DB::name('wares')->where(['get_type'=>1,'type'=>8,'level'=>$user['hz_level']])->field("id,type,name,level,show_img")->find();
            $arr1['name']=$wares['name']."级";
            $arr1['img']=$this->auth->setFilePath($wares['show_img']);
            $glory[]=$arr1;
        }
        $where['get_type']=['neq',1];
        $where['type']=8;
        $where['user_id']=$from_uid;
        $pack_ids=DB::name('pack')->where($where)->column('target_id');
        $wares=DB::name('wares')->whereIn("id",$pack_ids)->field("id,name,level,show_img")->select();
        foreach ($wares as $ke => &$va) {
            $arr2['type']=4;
            $arr2['level']=$va['level'];
            $arr2['name']=$va['name'];
            $arr2['img']=$this->auth->setFilePath($va['show_img']);
            $glory[]=$arr2;
        }




        //cp列表
        $where_cp['user_id|fromUid']=$from_uid;
        $where_cp['status']=1;
        $cplist=DB::name('cp')->where($where_cp)->field("id,wares_id,user_id,fromUid,agreetime")->select();
        $cplist=$this->cpDataFormat($cplist);
        $cp_card=getUserField($from_uid,'cp_card');
        for ($i=0; $i <3 ; $i++) {
            if(!isset($cplist[$i])){
                if( ($i+1) > $cp_card ){
                    $cplist[$i]['cp_type']=3;
                    $cplist[$i]['days']='待开启';
                }else{
                    $cplist[$i]['cp_type']=2;
                    $cplist[$i]['days']='暂无CP';
                }
            }
        }

        $arr['userInfo'] = $user;
        $arr['glory']    = $glory;
        $arr['roomInfo'] = $roomInfo;
        $arr['gifts']    = $gifts;
        $arr['cplist']    = $cplist;
        $this->ApiReturn(1, '', $arr);
    }

    //用户信息
    public function get_user_info(){
    	//echo 1;die;
        $user_id = input('user_id/d',0);
        if(!$user_id )   $this->ApiReturn(0,'缺少参数');
        $user = DB::name('users')->field(['id','headimgurl','nickname','sex','birthday','constellation','city','mizuan','is_idcard','ry_uid' ,'is_sign'])->where('id',$user_id)->select();
        if(!$user)    $this->ApiReturn(0,'未找到此用户');

        $user[0]['headimgurl'] = $this->auth->setFilePath($user[0]['headimgurl']);
        $user[0]['vip_level'] = $this->getVipLevel($user_id,3);
        $user[0]['follows_num'] = DB::name('follows')->where('user_id', $user_id)->where('status', 1)->count();
        $user[0]['fans_num'] = DB::name('follows')->where('followed_user_id', $user_id)->where('status', 1)->count();
        $user[0]['ry_uid'] = $user[0]['ry_uid'] ? : '';
       
        $this->ApiReturn(1,'',$user[0]);
    }

    //修改用户信息
    public function edit_user_info() {
        $data = $this->request->request();
        // $img=$request->file('img');
        // if($img){
        //     $info['headimgurl']=$this->uploadOne($img,1,'image');
        // }
        if($data['img']){
            $info['headimgurl'] = $this->base64_image_content($data['img'],8);
            $this->SensitiveImage($info['headimgurl']);
        }
        $id = $this->user_id;;
        if(!$id)    $this->ApiReturn(0,'缺少参数');
        $info['nickname']=$data['nickname'];
        $info['sex']=$data['sex'];
        $info['birthday']=$data['birthday'];
        $info['constellation']=getBrithdayMsg($info['birthday'],2);
        $info['city']=$data['city'];

        $res=DB::name('users')->where('id',$id)->update($info);
        if($res){
            $u_arr=DB::name('users')->field(['id','nickname','headimgurl','ry_uid','ry_token','phone'])->where('id',$id)->find();
            $u_arr['headimgurl'] = $this->auth->setFilePath($u_arr['headimgurl']);
            $this->ApiReturn(1,'修改成功',$u_arr);
        }else{
            $this->ApiReturn(0,'修改失败');
        }
    }

    //我的收益
    public function user_income(){
        $user_id = $this->user_id;
        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $days=array_map(function($val){
            return strtotime($val);
        }, star_end_time(1));

        $weeks=array_map(function($val){
            return strtotime($val);
        }, star_end_time(2));
        $mons=array_map(function($val){
            return strtotime($val);
        }, star_end_time(3));
        $last_mons=array_map(function($val){
            return strtotime($val);
        }, star_end_time(4));

        $arr['yue']=DB::name('users')->where('id',$user_id)->value('mibi');
        $arr['day_sum']=DB::name('store_log')->where('user_id',$user_id)->where('get_type',21)
            ->where('addtime','between time',$days)
            //->whereBetween('addtime',$days)
            ->sum('get_nums');
        $arr['week_sum']=DB::name('store_log')->where('user_id',$user_id)->where('get_type',21)
            ->where('addtime','between time',$weeks)
            ->sum('get_nums');
        $arr['mon_sum']=DB::name('store_log')->where('user_id',$user_id)->where('get_type',21)
            ->where('addtime','between time',$mons)
            ->sum('get_nums');
        $arr['last_mon_sum']=DB::name('store_log')->where('user_id',$user_id)->where('get_type',21)
            ->where('addtime','between time',$last_mons)
            ->sum('get_nums');
        $arr=array_map(function($val){
            return bcadd($val,0,2);
        }, $arr);
        $is_leader=DB::name('users')->where('id',$user_id)->value('is_sign');
        $arr['is_leader']=$is_leader;

        if($is_leader){
            $room['yue']=DB::name('users')->where('id',$user_id)->value('r_mibi');
            $room['day_sum']=DB::name('store_log')->where('user_id',$user_id)
                ->whereIn('get_type',[31,32])
                ->where('addtime','between time',$days)
                ->sum('get_nums');
            $room['week_sum']=DB::name('store_log')->where('user_id',$user_id)
                ->whereIn('get_type',[31,32])
                ->where('addtime','between time',$weeks)
                ->sum('get_nums');
            $room['mon_sum']=DB::name('store_log')->where('user_id',$user_id)
                ->whereIn('get_type',[31,32])
                ->where('addtime','between time',$mons)
                ->sum('get_nums');
            $room['last_mon_sum']=DB::name('store_log')->where('user_id',$user_id)
                ->whereIn('get_type',[31,32])
                ->where('addtime','between time',$last_mons)
                ->sum('get_nums');
            $room=array_map(function($val){
                return bcadd($val,0,2);
            }, $room);
        }else{
            $room=(object)[];
        }
        $res['gift_income']=$arr;
        $res['room_income']=$room;
        $this->ApiReturn(1,'',$res);
    }

    //等级中心
    public function level_center(){
        $user_id = $this->user_id;
        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $user = DB::name('users')->field(['id','nickname','headimgurl'])->where('id', $user_id)->select();
        $star_num = DB::name('gift_logs')->where('fromUid', $user_id)->sum('giftPrice');
        $gold_num = DB::name('gift_logs')->where('user_id', $user_id)->sum('giftPrice');

        $star_level = $this->getVipLevel($user_id,1);
        $next_star_num = getNextLevel(1,$star_level,'exp');
        $next_star_level = getNextLevel(1,$star_level,'level');

        $gold_level = $this->getVipLevel($user_id,2);
        $next_gold_num = getNextLevel(2,$gold_level,'exp');
        $next_gold_level = getNextLevel(2,$gold_level,'level');

        $user[0]['headimgurl'] = $this->auth->setFilePath($user[0]['headimgurl']);
        $user[0]['star_num'] = $star_num;
        $user[0]['gold_num'] = $gold_num;
        $user[0]['star_level'] = $star_level;
        $user[0]['next_star_num'] = $next_star_num;
        $user[0]['next_star_level'] = $next_star_level;

        $user[0]['gold_level']      =$gold_level;
        $user[0]['next_gold_num']   = $next_gold_num;
        $user[0]['next_gold_level'] = $next_gold_level;
        $this->ApiReturn(1,'',$user);
    }

    //会员中心
    public function vip_center(){
        $user_id = $this->user_id;
        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $user = DB::name('users')->field(['id','nickname','headimgurl'])->where('id', $user_id)->select();
        $vip_num = DB::name('gift_logs')->where('user_id', $user_id)->sum('giftPrice');
        $vip_level = $this->getVipLevel($user_id,3);
        $next_vip_num = getNextLevel(3,$vip_level,'exp');
        $next_vip_level = getNextLevel(3,$vip_level,'level');

        $user[0]['headimgurl'] = $this->auth->setFilePath($user[0]['headimgurl']);
        $user[0]['vip_num'] = $vip_num;
        $user[0]['vip_level'] = $vip_level;
        $user[0]['next_vip_num'] = $next_vip_num;
        $user[0]['next_vip_level'] = $next_vip_level;


        $vip_auth = DB::name('vip_auth')->where(['type'=>3])->select();
        // $this->ApiReturn(1,'',$user_id);
        foreach ($vip_auth as $k => &$v) {
            $v['is_on'] = ($vip_level >= $v['level']) ? 1 : 0;
            $v['img_0'] = $this->auth->setFilePath($v['img_0']);
            $v['img_1'] = $this->auth->setFilePath($v['img_1']);
        }
        unset($v);
        $arr['user']=$user;
        $arr['auth']=$vip_auth;
        $this->ApiReturn(1,'',$arr);
    }

    //我的钱包
    public function my_store(){
        $user_id = $this->user_id;
        if(!$user_id)    $this->ApiReturn(0,'缺少参数');
        $user=DB::name('users')->field(['id','ali_user_id','ali_avatar','ali_nick_name','mizuan','mibi','r_mibi'])->where('id',$user_id)->select();
        foreach ($user as $key => &$v) {
            if($v['ali_user_id']){
                $v['is_ali'] = 1;
                // $v->ali_account = substr_replace($v->ali_account, '****', 3, 4);
            }else{
                $v['ali_user_id'] = '';
                $v['ali_nick_name'] = '';
                $v['ali_avatar'] = '';
                $v['is_ali'] = 0;
            }
            $v['mibi'] = bcadd($v['mibi'],$v['r_mibi'],2);
        }
        $this->ApiReturn(1,'',$user);
    }

    // 提现
    public function tixian(){
        $user_id = $this->user_id;
        $money = $this->request->request('money') ? : 0;
        if(!$user_id || $money <= 0)    $this->ApiReturn(0,'缺少参数');


        $ali_user_id=DB::name('users')->where('id',$user_id)->value('ali_user_id');
        if(!$ali_user_id ) $this->ApiReturn(0,'请先绑定支付宝');


        $mibi=DB::name('users')->where('id',$user_id)->value('mibi');
        $r_mibi=DB::name('users')->where('id',$user_id)->value('r_mibi');
        // $sum=$mibi+$r_mibi;
        $sum=bcadd($mibi,$r_mibi,2);
        $min_tx_num=$this->getConfig('min_tx_num');
        if($money < $min_tx_num )   $this->ApiReturn(0,'提现金额不能小于'.$min_tx_num);
        if($money > $sum)   $this->ApiReturn(0,'余额不足');


        if($mibi >= $money){
            userStoreDec($user_id,$money,23,'mibi');
        }else{
            userStoreDec($user_id,$mibi,23,'mibi');
            $num=$money - $mibi;
            userStoreDec($user_id,$num,34,'r_mibi');
        }
        $arr['order_no']=getOrderNo();
        $arr['user_id']=$user_id;
        $arr['money']=$money;
        $arr['addtime']=time();
        $res=DB::name('tixian')->insert($arr);
        if($res){
            $this->ApiReturn(1,'提现成功');
        }else{
            $this->ApiReturn(0,'提现失败');
        }
    }

    // 提现记录
    public function tixian_log(){
        $user_id = $this->user_id;
        $page    = $this->request->request('page') ? : 0;
        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $data=DB::name('tixian')->where('user_id',$user_id)->order('id','desc')->page($page,10)->limit(10)->select();
        //echo DB::name('tixian')->getLastSql();die;
        foreach ($data as $key => &$v) {
            if($v['status'] == 1){
                $v['title']='审核中';
            }elseif($v['status'] == 2){
                $v['title']='已提现';
            }
            $v['addtime']=date('Y-m-d H:i:s',$v['addtime']);
        }
        $this->ApiReturn(1,'',$data);
    }

    // 兑换
    public function exchange(){
        $user_id = $this->user_id;
        //限制该接口1秒请求1次
        setViewNum($user_id);
        $money = (int)$this->request->request('money') ? : 0;
        $money=abs($money);
        if(!$user_id || !$money) $this->ApiReturn(0,'缺少参数');
        $mibi=DB::name('users')->where('id',$user_id)->value('mibi');
        $r_mibi=DB::name('users')->where('id',$user_id)->value('r_mibi');
        $sum=$mibi+$r_mibi;
        if($sum < $money)   $this->ApiReturn(0,'余额不足');


        if($mibi >= $money){
            userStoreDec($user_id,$money,24,'mibi');
        }else{
            userStoreDec($user_id,$mibi,24,'mibi');
            $num=$money - $mibi;
            userStoreDec($user_id,$num,35,'r_mibi');
        }
        $ratio = $this->getConfig('exchange_ratio'); //兑换返额比例%
        $mizuan=($money * 10) * (100+$ratio) /100;
        $mizuan = floor($mizuan);
        userStoreInc($user_id,$mizuan,12,'mizuan');
        $arr['user_id']=$user_id;
        $arr['mibi']=$money;
        $arr['mizuan']=$money * 10;
        $arr['addtime']=time();
        $res=DB::name('exchange')->insert($arr);
        if($res){
            $this->ApiReturn(1,'兑换成功');
        }else{
            $this->ApiReturn(0,'兑换失败');
        }
    }

    // 兑换校验
    public function exchange_check(){
        $money = (int)$this->request->request('money') ? : 0;
        $money=abs($money);
        if(!$money || $money < 1) $this->ApiReturn(0,'缺少参数');
        $ratio = $this->getConfig('exchange_ratio'); //兑换返额比例%
        $arr['mizuan']=$money *10;
        $give=$money * 10 * $ratio /100;
        $arr['give']=floor($give);
        $this->ApiReturn(1,'',$arr);
    }

    // 兑换记录
    public function exchange_log(){
        $user_id = $this->user_id;
        $page    = $this->request->request('page') ? : 0;

        if(!$user_id)   $this->ApiReturn(0,'缺少参数');
        $data=DB::name('exchange')->where('user_id',$user_id)->order('id','desc')->page($page,10)->limit(10)->select();
        foreach ($data as $key => &$v) {
            $v['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
        }
        $this->ApiReturn(1,'',$data);
    }

    // 举报类型
    public function report(){
        $data=DB::name('report_types')->field(['id','name'])->where('enable',1)->select();
        foreach ($data as $key => &$v) {
            $v['is_check'] = 0;
            $v['report_name'] = $v['name'];
        }
        $this->ApiReturn(1,'',$data);
    }
    // 举报类型
    public function report_type(){
        $data=DB::name('report_types')->field(['id','name'])->where('enable',1)->select();
        foreach ($data as $key => &$v) {
            $v['is_check'] = 0;
            $v['report_name'] = $v['name'];
        }
        $this->ApiReturn(1,'',$data);
    }

    //举报
    public function send_report() {
        $data=$this->request->request();
        $data['user_id'] = $this->user_id;
        if( !$data['type'] || !$data['target'] || !$data['report_type'])   $this->ApiReturn(0,'缺少参数');

        $info['img']=$this->base64_image_content($data['img'],2);
        $info['user_id']=$data['user_id'];
        $info['type']=$data['type'];
        $info['target']=$data['target'];
        $info['report_type']=$data['report_type'];
        $info['addtime']=time();
        $res=DB::name('report')->insert($info);
        if($res){
            $this->ApiReturn(1,'举报成功');
        }else{
            $this->ApiReturn(0,'举报失败');
        }
    }

    // 反馈
    public function feedback(){
        $data=$this->request->request();
        $data['user_id'] = $this->user_id;
        if(!$data['user_id'] || !$data['content'])  $this->ApiReturn(0,'缺少参数');

        $add_time=DB::name('feedback')->where('user_id',$data['user_id'])->value('addtime') ? : 0;
        $sjc = time() - $add_time;
        if($sjc < 300)  $this->ApiReturn(0,'您近期已反馈过,请过段时间再来');
        $info['img']=$this->base64_image_content($data['img'],2);
        $info['user_id']=$data['user_id'];
        $info['content']=$data['content'];
        $info['addtime']=time();
        $res=DB::name('feedback')->insertGetId($info);
        if($res){
            $this->ApiReturn(1,'反馈成功');
        }else{
            $this->ApiReturn(0,'反馈失败');
        }
    }

    //获取星座
    public function getConstellation(){
        $birthday = $this->request->request('birthday')? : '';
        if(!$birthday)  $this->ApiReturn(0,'缺少参数');
        $arr['constellation'] = getBrithdayMsg($birthday,2);
        $this->ApiReturn(1,'',$arr);
    }

    // 判断是否关注对方
    public function is_follow(){
        $user_id = $this->user_id;
        $from_uid = $this->request->request('from_uid') ? : 0;
        if (!$user_id || !$from_uid) $this->ApiReturn(0, '缺少参数');
        $arr['is_follow'] = IsFollow($user_id,$from_uid);
        $this->ApiReturn(1,'',$arr);
    }

    //获取用户vip,徽章微张  图片
    public function get_user_vip(){
        $user_id = $this->user_id;
        $uid=$this->request->request('uid') ? : 0;
        if(!$user_id || !$uid)   $this->ApiReturn(0,'缺少参数');
        $vip_level=$this->getVipLevel($user_id,3);
        $vip_img=DB::name('vip')->where('level',$vip_level)->where('type',3)->value('img');
        $arr['vip_img']=$this->auth->setFilePath($vip_img);
        $hz_level=$this->getHzLevel($user_id);
        $hz_img=DB::name('vip')->where('level',$vip_level)->where('type',4)->value('img');
        $arr['hz_img']=$this->auth->setFilePath($hz_img);

        //聊天框
        $dress=DB::name('users')->field('dress_5,dress_6')->find($user_id);
        $dress_5=DB::name('wares')->where(['id'=>$dress['dress_5'],'enable'=>1])->find();
        $arr['ltk']=$this->auth->setFilePath($dress_5['img3']);
        $ltk_left=$this->auth->setFilePath($dress_5['img1']);
        if($arr['ltk']){
            $arr['ltk_left']= $ltk_left ? : '1';
        }else{
            $arr['ltk_left']= '';
        }
        $arr['ltk_right']=$this->auth->setFilePath($dress_5['img2']);

        //特效
        $dress_6=DB::name('wares')->where(['id'=>$dress['dress_6'],'enable'=>1])->find();
        $vip_tx=$this->auth->setFilePath($dress_6['img1']);
        $arr['vip_tx']= $vip_level >=6 ? $vip_tx : '';

        //昵称颜色
        $nick_color = $this->getConfig('nick_color');
        $arr['nick_color'] = ($vip_level >= 3)  ? $nick_color : '#ffffff';

        $roomVisitor=DB::name('rooms')->where(['uid'=>$uid])->value('roomVisitor');
        if(!$roomVisitor){
            $vis_arr=[];
        }else{
            $vis_arr=explode(",",trim($roomVisitor));
        }
        $vis_arr= $uid==$user_id ? $vis_arr :  array_merge($vis_arr,[$uid])  ;
        $key=array_search($user_id,$vis_arr);
        unset($vis_arr[$key]);
        $cp_arr=[];
        foreach ($vis_arr as $k => &$v) {
            $cp_id=$this->check_first_cp($user_id,$v,1);
            if($cp_id){
                $level=$this->getVipLevel($v,3);
                $ar['cp_level']=$this->getCpLevel($cp_id);
                $ar['nick_color'] = ($level >= 3) ? $nick_color : '#ffffff';
                $ar['id']=$v;
                $ar['nickname']=$this->usersMod->where(['id'=>$v])->value('nickname');
                $ar['exp']=DB::name('cp')->where(['id'=>$cp_id])->value('exp');
                $img=$this->usersMod->where(['id'=>$v])->value('headimgurl');
                $ar['headimgurl']=$this->auth->setFilePath($img);
                $cp_arr[]=$ar;
            }
        }
        if($cp_arr){
            array_multisort(array_column($cp_arr,'exp'),SORT_DESC,$cp_arr);
        }
        $cp_tftx=$this->getConfig('cp_tftx');
        $i=0;
        foreach ($cp_arr as $k => &$va) {
            if(!$i){
                $va['cp_tx']= $va['cp_level'] >=3 ? $this->auth->setFilePath($cp_tftx) : '';
            }else{
                $va['cp_tx']='';
            }
            $i++;
        }
        $arr['cp_users']=$cp_arr;
        $this->ApiReturn(1,'',$arr);
    }





































}
