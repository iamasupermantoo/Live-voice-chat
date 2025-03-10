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
 * 示例接口
 */
class Demo extends Api
{
	
	
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
    

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['test', 'test1','online_status','everyday_tongji',"room_rankingb","qfbb"];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];
    
    public function qfbb()
    {
    	
    	$list = Db::name("qpgg")->select();
    	
    	$arr=[];
    	foreach($list as $k=>&$v){
    		$time=date("Y-m-d H:i",$v['addtime']);
    		
    		$dqtime=date("Y-m-d H:i",time());
    	
    		if($time == $dqtime){
    		//	echo 1;die;
	    		$usinfo=Db::name("users")->where("id='{$v['user_id']}'")->find();
	    	//	dump($usinfo);
	    		$lwinfo=Db::name("gifts")->where("id='{$v['wares_id']}'")->find();
	    		$arr[$k]['username']=$usinfo['nickname'];
	    		$arr[$k]['lwname']=$lwinfo['name'];
	    			$arr[$k]['user_id']=$v['user_id'];
    				$arr[$k]['num']=$v['num'];
    		}else{
    			//echo 123456;
    			unset($v);
    		}
    	
    		        sort($arr);

    		//$v['cont']=$usinfo['nickname'].'开宝箱中奖获得'.$lwinfo['name'].$v['num'].'个';
    		
    	}
   
    	  $this->ApiReturn(1,'',$arr);
    }
    
    public function pgupdst(){
    	
    	 $id = input('id');
    	 $array=array(
    	 		"ispg"=>1
    	 	);
    	$save=Db::name("qpgg")->where("id='{$id}'")->data($array)->update();
    	
    	 $this->ApiReturn(1,'',[]);
    }
    
    

    /**
     * 测试方法
     *
     * @ApiTitle    (测试名称)
     * @ApiSummary  (测试描述信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/demo/test/id/{id}/name/{name})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="id", type="integer", required=true, description="会员ID")
     * @ApiParams   (name="name", type="string", required=true, description="用户名")
     * @ApiParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
         'code':'1',
         'msg':'返回成功'
        })
     */
    public function test()
    {
        $this->success('返回成功', $this->request->param());
    }

    /**
     * 无需登录的接口
     *
     */
    public function test1()
    {
        $this->success('返回成功', ['action' => 'test1']);
    }

    /**
     * 需要登录的接口
     *
     */
    public function test2()
    {
        $this->success('返回成功', ['action' => 'test2']);
    }

    /**
     * 需要登录且需要验证有相应组的权限
     *
     */
    public function test3()
    {
        $this->success('返回成功', ['action' => 'test3']);
    }
  
    public function online_status(){
    	$postStr = file_get_contents('php://input');
       // $postStr = '[{"userid":"1103863","status":"1","os":"iOS","time":1571130409553,"clientIp":"180.108.223.44:52229"},{"userid":"1103863","status":"0","os":"iOS","time":1571130409590,"clientIp":"180.108.223.44:52231"}]';
         $postArr = json_decode($postStr,true); 
         $lastArr = array_pop($postArr);

         $user_id = $lastArr['userid'];
         $status  = $lastArr['status'];

        //查询用户是否存在
         $userinfo = Db::name('users')->field('id')->where(['status'=>['neq',4]])->find();

         if (!$userinfo){
             $data['status'] = $status;
             $data['user_id'] = $user_id;
             $jsondata = json_encode($data);
             $this->lhlog22($jsondata,'用户不存在');
         }else{
             Db::name('users')->where(array('id'=>$user_id))->update(['isOnline'=>$status,'updated_at'=>date('Y-m-d H:i:s')]);
             $this->lhlog22($postStr,'操作成功');
         }
        
   		 return 200; 
   }

    // 每日统计 计算昨天
    public function everyday_tongji(){
        $time_arr[]   = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') - 1  , date('Y')));
        $time_arr[]   = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d') - 1 , date('Y')));
        //注册
        $info['register']=DB::name('users')->where('created_at','between time',$time_arr)->count();
        //活跃
        $info['active']=DB::name('users')->where('updated_at','between time',$time_arr)->count();
        //充值
        $recharge=DB::name('order')->where(['status'=>2])->where('paytime','between time',$time_arr)->sum('price');
        $info['recharge']=round($recharge,2);
        //提现
        $tixian=DB::name('tixian')->where(['status'=>2])->where('tx_time','between time',$time_arr)->sum('money');
        $info['tixian']=round($tixian,2);
        $info['logtime']=date('Y-m-d', mktime(0, 0, 0, date('m') , date('d') - 1  , date('Y')));
        $info['addtime']=time();
        $id=DB::name('tongji')->where(['logtime'=>$info['logtime']])->value('id');
        if(!$id)    DB::name('tongji')->insert($info);
        $this->ApiReturn(1,'',$info);
    }


  
  	//接口文件日志
    function lhlog22($arg, $file = '', $line = ''){
        $str = "\r\n-- ". date('Y-m-d H:i:s'). " --------------------\r\n";
        $str .= "FILE: $file\r\nLINE: $line\r\n";
        $str .= "_URL: ".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."\r\n";
        if (is_array($arg)){
            $str .= '$arg = array(';
            foreach ($arg AS $key => $list)
            {
                $str .= "'$key' => '$list'\r\n";
            }
            $str .= ")\r\n";
        }else{
            $str .= $arg;
        }
        file_put_contents(ROOT_PATH.'runtime/log/online'.date('Y-m-d').'_log.txt', $str,FILE_APPEND);
    }

}
