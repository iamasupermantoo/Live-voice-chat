<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\controller\Rediskey;
use think\Db;
// use think\Config;

/**
 * 宝箱接口
 */
class RedisCache extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    //获取数据
    public   function getRedisData($key=null,$action=null,$time = 18000,$id=null){
        if($id){
            $key = sprintf(Rediskey::getKey($key), $id);
        }else{
            $key = sprintf(Rediskey::getKey($key), $action);
        }
        if(!$key || !$action || !method_exists($this,$action) )   return [];
        $redis = RedisCli();
        //判断缓存的键是否还存在
        if(!$redis->exists($key))
        {
            //缓存不存在
            //mysql获取数据
            $data = $this->$action($id);
            if(!$data)  return [];
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);
            //存入redis
            $redis->set($key,$json);
            //设置过期时间5分钟
            $redis->expire($key,$time);
        }

        $json = $redis->get($key);
        $data = json_decode($json,true);
        return $data;
    }

    // 兑换列表
    public function jb_ex_list(){
        $where['a.enable']=1;
        $where['b.enable']=1;
        $data=Db::name('jb_ex')->alias('a')->join('wares b','a.wares_id = b.id')
                                            ->where($where)
                                            ->field('a.*,b.type,b.name,b.show_img,expire')
                                            ->select();
        foreach ($data as $k => &$v) {
            $v['show_img']=$this->auth->setFilePath($v['show_img']);
            if($v['type']==102){
                $v['name']=$v['name'].'x'.$v['num'];
            }elseif(in_array($v['type'], [4,5])){
                $v['name']=$v['name'].' '.$v['expire'].'天';
            }
        }
        return $data;
    }

    //日常任务
    protected function daily_task(){
        $data=Db::name('task')->where(['type'=>2,'enable'=>1])->select();
        foreach ($data as $k => &$v) {
            $v['img']=$this->auth->setFilePath($v['img']);
        }
        return $data;
    }

    //技能详情
    protected function getSkillDetails($id = 0){
        $data=Db::name('skill')->find($id);
        if(!$data)  return [];
        $data['image']=$this->auth->setFilePath($data['image']);
        return $data;
    }

    protected function ysdConfig(){  
        $config = [
            'wechat' => [
                'appid' => $this->getConfig('wx_appid'),    // APP APPID
                'app_id' => 'wxa18dee43081acc85',           // 公众号 APPID
                // 'miniapp_id' => 'wxb3fxxxxxxxxxxx',         // 小程序 APPID
                'mch_id' => $this->getConfig('wx_mch_id'),  //商户id
                'notify_url' => "http://" . $_SERVER['HTTP_HOST'] . "/api/go_wx_notify",
                'key' => $this->getConfig('wx_key'),
                //=======【证书路径设置】=====================================
                /**
                 * TODO：设置商户证书路径
                 * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
                 * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
                 * @var path
                 */
                'cert_client' => ROOT_PATH.'/addons/epay/library/Yansongda/certificate/apiclient_cert.pem',
                'cert_key' => ROOT_PATH.'/addons/epay/library/Yansongda/certificate/apiclient_key.pem',
            ],
            'alipay'  =>  [
                'app_id' => $this->getConfig('ali_appid'),
                'ali_public_key' => $this->getConfig('alipay_public_key'),
                'private_key' => $this->getConfig('merchant_private_key'),
                'log' => 1,
                'notify_url' => "http://" . $_SERVER['HTTP_HOST'] . "/api/go_ali_notify",
                'return_url' => "http://" . $_SERVER['HTTP_HOST'] . "/api/go_ali_return",
            ],
        ];
        return $config;
    }



    //分类显示房间
    protected function room_list_1(){
        $data = $this->room_list_hand(1);
        return $data;
    }
    protected function room_list_2(){
        $data = $this->room_list_hand(2);
        return $data;
    }
    protected function room_list_3(){
        $data = $this->room_list_hand(3);
        return $data;
    }
    protected function room_list_hand($class){
        $where['a.room_status']=['neq',3];
        $where['a.room_class']=$class;
        $data=DB::name('rooms')->alias('a')
                                ->join('room_categories b','a.room_type = b.id')
                                ->join('users c','a.uid = c.id')
                                ->where($where)
                                ->field('a.room_name,a.uid,a.room_cover,a.hot,b.name,b.pid,c.sex')
                                ->order('a.hot desc')
                                ->limit(16)
                                ->select();
        $data=$this->roomDataFormat($data);
        return $data;
    }

    //房间分类
    protected function room_type(){
        $rooms_cate=DB::name('room_categories')->field('id,pid,name')->where(['enable'=>1,'pid'=>0])->select();
        foreach ($rooms_cate as $kr => &$vr) {
            $data=DB::name('room_categories')->field('id,pid,name')->where(['enable'=>1,'pid'=>$vr['id']])->select();
            $vr['children']=$data;
        }
        return $rooms_cate;
    }
    
    //礼物数据
    protected function gift_list(){
        $data = DB::name('gifts')->where(['hot'=>['elt',900],'enable'=>1])
            ->order('sort desc, price asc')
            ->field(['id','name','price','img','type','show_img','show_img2'])
            ->select();
        $i=0;
        foreach ($data as $key => &$v) {
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
        return $data;
    }

    //宝石数据
    protected function baoshi_list(){
        $data=Db::name('wares')->where(['type'=>1,'enable'=>1])->field('id,name,price,img1,show_img,img2,get_type,type')->select();
        $j=0;
        foreach ($data as $k1 => &$v1) {
            $v1['is_check']= $j ? 0 : 1;
            $j++;
            //四期
            $v1['price_004']= $v1['get_type'] == 4 ? $v1['price'] : get_wares_allway($v1['get_type']);
            //四期前
            $v1['price']= $v1['get_type'] == 4 ? $v1['price'].'米钻' : get_wares_allway($v1['get_type']);
            $v1['img']=$this->auth->setFilePath($v1['img1']);
            $v1['show_img']=$this->auth->setFilePath($v1['show_img']);
            $v1['show_img2']=$this->auth->setFilePath($v1['img2']);
            $v1['type']= $v1['show_img2'] ? 2 : 1;
            $v1['wares_type']=1;
            $v1['e_name']='';
        }
        return $data;
    }

    //表情数据
    public function emoji_list(){
        $data=DB::name('emoji')->field(['id','pid','name','emoji','t_length'])->where(['enable'=>1])->where(['pid'=>0])->order('sort desc,id asc')->select();
        foreach ($data as $k => &$v) {
            $v['emoji'] = $this->auth->setFilePath($v['emoji']);
        }
        unset($v);
        return $data;
    }

    //推荐房间
    public function tuijianRoom(){
        $where['a.room_status']=['neq',3];
        $where['a.is_tj']=1;
        $data=DB::name('rooms')->alias('a')
                                ->join('room_categories b','a.room_type = b.id')
                                ->join('users c','a.uid = c.id')
                                ->where($where)
                                ->field('a.room_name,a.uid,a.room_cover,a.hot,b.name,b.pid,c.sex')
                                ->order('a.hot desc')
                                ->limit(16)
                                ->select();
        $data=$this->roomDataFormat($data);
        return $data;
    }
    //最佳工会
    protected function good_room() {
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
        $data=$this->roomDataFormat($data);
        return $data;
    }

    //C位推荐
    public function c_users(){
        $time_arr[]   = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') - 30 , date('Y')));
        $time_arr[]   = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d') - 1  , date('Y')));
        $res=$this->getRedisData('three_phase','good_room');
        // $res=array_slice($res,0,3);
        $ids=array_column($res,'uid');
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
            unset($v['exp']);
        }
        unset($v);
        return $data;
    }

    // 活动
    public function active_list_three(){
        $data=DB::name('active')->where(['enable'=>1])->order('sort desc')->field('id,img,url')->select();
        foreach ($data as $k => &$v) {
            $v['url']=$v['url'] ? : '';
            $v['contents']='';
            $v['img']=$this->auth->setFilePath($v['img']);
        }
        return $data;
    }

    //轮播
    public function carousel()
    {
        $data = DB::name('home_carousels')->where(['enable'=>1])->field('id,img,url,contents')->order('id desc,sort desc')->select();
        foreach ($data as $k => &$v) {
            $v['img']=$this->auth->setFilePath($v['img']);
            $v['contents']= trim($v['contents']) ? : '';
            $v['url']= trim($v['url']) ? : '';
        }
        return $data;
    }




/************************************************************** 楚 河 汉 界 ********************************************************************************/
    


    //获取缓存数据
    public  function getRedisInfoByWhere($key=null,$funcname,$arr=array()){
        if(!$key)   return [];
        $redis  = RedisCli();

        //判断缓存的键是否还存在
        if(!$redis->exists($key))
        {
            //缓存不存在,mysql获取数据
            $data = $this->$funcname($arr);
            if(!$data)  return [];
            //存入redis
            $a = $redis->hMset($key,$data);
            //设置过期时间5分钟
            //$redis->expire($key,5);
        }

        $data = $redis->hGetAll($key);
        return $data;
    }

    //更新缓存数据
    public  function updRedisInfoByWhere($key=null,$funcname,$arr=array()){
        if(!$key)   return [];
        $redis  = RedisCli();
        //缓存不存在,mysql获取数据
        $data = $this->$funcname($arr);
        //存入redis
        $redis->hMset($key,$data);
    }



    public function getUsersinfo($where){
        $data = DB::name('users')
            ->field('id,nickname,headimgurl,sex,birthday,constellation,city')
            ->where($where)
            ->find(); 
        if (!$data) return false;

        $data['headimgurl'] = $this->auth->setFilePath($data['headimgurl']);

        return $data;
    }

    public function getValueByName($key,$fieldname){
        if(!$key)   return [];
        $redis  = RedisCli();

        //判断缓存的键是否还存在
        if(!$redis->exists($key))
        {
            //缓存不存在,mysql获取数据
            //$data = $this->$funcname($arr);
            $info = Db::name('config')->field('name,value')->order('id asc')->where(array('status'=>1))->select();
            $result = array();
            foreach($info as $k=>$v){
                $result[$v['name']] = $v['value'];
            }
            //存入redis
            $redis->hMset($key,$result);
            //设置过期时间5分钟
            //$redis->expire($key,5);
        }

        $data = $redis->hMget($key,array($fieldname));
        return $data[$fieldname];
    }

    /*
     * 添加记录
     * @param $hash_prefix 前缀
     * @param $id 记录id
     * @param $data 数据
     * @return bool 返回值
     */
    public function set_redis_page_info($hash_prefix,$id,$data){
        $redis      = RedisCli();
        if(!is_numeric($id) || !is_array($data)) return false;
        $hashName = $hash_prefix.'_'.$id;
        //同时设置 hash 的多个 field，已存在会自动更新
        $redis->hmset($hashName, $data);
        //添加元素到有序集合，元素在集合中存在则更新对应的score（权）(key，权，值)
        $redis->zadd($hash_prefix.'_sort',$id,$id);
        return true;
    }

    /*
     * 获取分页数据
     * @param $hash_prefix 前缀
     * @param $page 当前页数
     * @param $pageSize 每页多少条
     * @param $hashName Hash 记录名称
     * @param $SortName Redis SortSet 记录名称
     * @param $redis Redis 对象
     * @param $key 字段数组 不传为取出全部字段
     * @return array
     */
    public function get_redis_page_info($hash_prefix,$page,$pageSize,$key=array()){
        $redis      = RedisCli();
        if(!is_numeric($page) || !is_numeric($pageSize)) return false;
        $limit_s = ($page-1) * $pageSize;
        $limit_e = ($limit_s + $pageSize) - 1;
        //类似lrange操作从集合中去指定区间的元素，返回的是带有 score 值(可选)的有序结果集：
        $range = $redis->zrange($hash_prefix.'_sort',$limit_s,$limit_e); 
        //统计ScoreSet集合元素总数
        $count = $redis->zcard($hash_prefix.'_sort'); 
        $pageCount = ceil($count/$pageSize); //总共多少页
        $pageList = array();
        foreach($range as $qid){
            if(count($key) > 0){
                $pageList[] = $redis->hmget($hash_prefix.'_'.$qid,$key); //获取hash表中的field所有的数据
            }else{
                $pageList[] = $redis->hgetall($hash_prefix.'_'.$qid); //获取hash表中所有的数据
            }
        }
        return $pageList;
    }

    /*
   * 删除记录
   * @param $id id
   * @param $hashName Hash 记录名称
   * @param $SortName Redis SortSet 记录名称
   * @param $redis Redis 对象
   * @return bool
   */
    public function del_redis_page_info($id,$cacheKey){
        if(!is_array($id)) return false;
        $redis      = RedisCli();
        foreach($id as $value){
          $hashName = $cacheKey.'_'.$value;
          $redis->del($hashName);
          $redis->zRem($cacheKey.'_sort',$value);
        }
        return true;
    }


    //获取数据
    public   function getRedisList($key=null,$action=null,$where,$time = 18000){
        if(!$key || !$action || !method_exists($this,$action) )   return [];
        $redis = RedisCli();
        //判断缓存的键是否还存在
        if(!$redis->exists($key))
        {
            //缓存不存在
            //mysql获取数据
            $data = $this->$action($where);
            //print_r($data);die;
            $data = $this->convert_arr_key($data,'id');

            $json = json_encode($data,JSON_UNESCAPED_UNICODE);
            //存入redis
            $redis->set($key,$json);
            //设置过期时间5分钟
            $redis->expire($key,$time);
        }

        $json = $redis->get($key);
        $data = json_decode($json,true);
        
        return $data;
    }

    /**
     * 获取技能段位列表
     * @param  [type] $where [查询条件]
     */
    public function getSkillLevelList($where){
        $data = DB::name('skill_level')
            ->field('id,name,level')
            ->where($where)
            ->select(); 
        if (!$data) return false;

        return $data;
    }

    /**
     * 获取技能列表
     * @param  [type] $where [查询条件]
     */
    public function getSkillList($where){
        $data = DB::name('skill')
            ->field('id,name,image,is_level,two_image,big_image')
            ->where($where)
            ->select();
        foreach ($data as $key => &$value) {
            $value['image'] = $this->auth->setFilePath($value['image']);
            $value['two_image'] = $this->auth->setFilePath($value['two_image']);
            $value['big_image'] = $this->auth->setFilePath($value['big_image']);
        } 
        if (!$data) return false;

        return $data;
    }

    /**
     * 获取可接单大区
     * @param  [type] $where [查询条件]
     */
    public function getSkillAreaList($where){
        $data = DB::name('gm_area')
            ->field('id,name')
            ->where($where)
            ->select(); 
        if (!$data) return false;

        return $data;
    }

    /**
     * 获取擅长位置
     * @param  [type] $where [查询条件]
     */
    public function getSkillPositionList($where){
        $data = DB::name('gm_position')
            ->field('id,name')
            ->where($where)
            ->select(); 
        if (!$data) return false;

        return $data;
    }

    /**
     * 获取价格
     * @param  [type] $where [查询条件]
     */
    public function getSkillPriceList($where){
        $data = DB::name('gm_price')
            ->field('id,price,unit,orders,level')
            ->where($where)
            ->select(); 
        if (!$data) return false;

        return $data;
    }

    /** 
     * @param $arr 
     * @param $key_name 
     * @return array 
     * 将数据库中查出的列表以指定的 id 作为数组的键名  
     */ 
    function convert_arr_key($arr, $key_name) 
    { 
        if (!$arr){return [];}
        $arr2 = array(); 
        foreach($arr as $key => $val){ 
            $arr2[$val[$key_name]] = $val;         
        } 
        return $arr2; 
    } 

}
