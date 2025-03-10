<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

/**
 * 宝箱接口
 */
class Box extends Api
{
    protected $noNeedLogin = ['setAwardList','update_box_time'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        $this->waresMod    = Db::name('wares');
        $this->awardlogMod = Db::name('award_log');
        $this->awardMod    = Db::name('award');
        parent::_initialize();
    }


    //lzw   暂停
    // public function update_box_time(){
    //     return true;
    //     $box_time_list=$this->getConfig('box_time_list');
    //     $arr=explode(",",$box_time_list);
    //     $hour=date('H');
    //     if(in_array($hour, $arr)){
    //         $value=$hour.':00';
    //         Db::name('config')->where(array('name'=>'box_starttime'))->update(['value'=>$value]);
    //     }
    // }


    /*
    宝箱备注说明

    */
    public function getRewardInfo(){
        $info = $this->getConfig('box_desc');
        $this->ApiReturn(1, '请求成功',array('remark'=>$info));
    }

    /*
    购买钥匙
    @keysNum 购买数量
    */
    public function actionBuyKeys(){
        setViewNum($this->user_id);
        $params = $this->request->request();
        $keysNum = (int)$params['keysNum'];
        if (empty($keysNum) || ($keysNum <= 0)){
            $this->ApiReturn(0,'参数错误');
        }

        $radio = $this->getConfig('keys_radio');
        $needMizuan = ($keysNum * $radio);

        //检查米钻是否足够
        $mizuan = Db::name('users')->where(array('id'=>$this->user_id))->value('mizuan');
        if (empty($mizuan) || ($mizuan < $needMizuan)){
            $this->ApiReturn(0,'剩余米钻不足');
        }

        //减少users表米钻数量，增加钥匙数量，入库钱包变动记录表store_log
        Db::startTrans();
        try{
            addKeysNum($this->user_id,$keysNum);
            Db::name('users')->where('id',$this->user_id)->setInc('keys_num', $keysNum);
            userStoreDec($this->user_id,$needMizuan,16,'mizuan');

            //提交事务
            Db::commit();
        } catch (\Exception $e) {
            //回滚事务
            Db::rollback();
            $this->ApiReturn(0, '购买失败');
        }

        $this->ApiReturn(1, '购买成功');
        
    }

    /*
    获得奖品/开奖
    *@keysNum 开锁数 1 10 100
    *@class   宝箱类型 1=普通 2=守护
    *首次500积分，拓展卡
    *
    */
    public function getAwardList(){
        //限制该接口1秒请求1次
        setViewNum($this->user_id);

        $params  = $this->request->request();

        $keysNum = $keysNums = $params['keysNum'];  //1 10 100

        if (empty($keysNum) || !in_array($keysNum,array(1,10,100))){
            $this->ApiReturn(0,'参数错误');
        }
        
        //用户钥匙数量是否足够
        $keys_num = Db::name('users')->where(array('id'=>$this->user_id))->value('keys_num');
        if (empty($keys_num) || ($keys_num < $keysNum)){
            $this->ApiReturn(0,'剩余钥匙数量不足');
        }

        //数据是否有效
        $where['enable'] = $wheres['enable'] = array('eq',0);

        //宝箱类型 
        $class = $this->getBoxType();
        //奖池里是否有数据
        $where['class']  = $wheres['class'] = array('eq',$class);
        $originCount     = $this->awardMod->field('id')->where($wheres)->limit(1)->count();
        if ($originCount == 0){
            $this->ApiReturn(0,'数据有误');
        }

        //当前开奖期数
        $currentTerm      = Db::name('award')->where($where)->max('term');
        $special_interval = $this->getConfig('special_interval');
        //echo $currentTerm;die;
        //if ((($currentTerm % 10) != $special_interval) || ($currentTerm == 1)){
        //if (($currentTerm - $special_interval) != 10){
        if ((($currentTerm % 10) != $special_interval) || ($currentTerm < 10)){
            //查出特殊礼物id
            $specialIds = $this->awardMod->field('GROUP_CONCAT(id) as ids')->where(array('status'=>1,'enable'=>0,'class'=>$class,'is_special'=>1))->find();
            $specialIds = $specialIds['ids'];
            if ($specialIds){
                $where['id'] = array('not in',$specialIds);
            }
        }


        

        //奖池里现有奖品数量
        $where['status'] = array('eq',1);
        $field = 'id,type,wares_id,num,class,award_box_id,img,term';
        $count = $this->awardMod->field('count(id) as count')->where($where)->count();
        $list  = $this->awardMod->field($field)->where($where)->limit($keysNum)->select();

        Db::startTrans();
        try{
            //奖池剩余奖品数量 < 用户开箱钥匙数
            $results = array();
            if ($count < $keysNum){
                $results = $list;

                //修改奖池数据状态
                Db::name('award')->where(array('class'=>$class))->update(array('status'=>1));
                Db::name('award')->where(array('class'=>$class))->setInc('term', 1);

                $keysNums = ($keysNum - $count);
            }

            //随机抽取数据
            $lists = $this->awardMod->field($field)->where($where)->orderRaw('rand()')->limit($keysNums)->select();
            //echo $this->awardMod->getlastsql();

            $temp = $lists;
            if ($results){
                
                $lists = array_merge($results,$lists);
            }

            foreach ($temp as $ke => $val) {
                $wap = "id = ".$val['id'];
                Db::name('award')->where($wap)->update(array('status'=>0));
            }
            
            //重复礼物数量+1
            $item = [];
            foreach ($lists as $key => & $value) {

                if(!isset($item[$value['wares_id'].'_'.$value['type']])){
                    $item[$value['wares_id'].'_'.$value['type']] = $value;
                }else{
                    $item[$value['wares_id'].'_'.$value['type']]['num'] += 1;
                }
                
                //$wap = "id = ".$value['id'];
                //Db::name('award')->where($wap)->update(array('status'=>0));
                
            }

            //奖品数据键值重排序
            $item = array_values($item);
          
            $awardTxt = '';

            foreach ($item as $key => &$value) {
                //奖品是否播报
                $is_play = Db::name('award_box')
                    ->field('is_play')
                    ->where(array('id'=>$value['award_box_id']))
                    ->value('is_play');
                $isplay = $is_play == 1 ? 0 : 1;//0=未播报 1=已播报

                //是否公屏
                $is_public_play = Db::name('award_box')
                    ->field('is_public_play')
                    ->where(array('id'=>$value['award_box_id']))
                    ->value('is_public_play');

                //入库中奖记录表
                $awardlogData = array(
                    'user_id' => $this->user_id,
                    'num'     => $value['num'],
                    'addtime' => time(),
                    'type'    => $value['type'],
                    'wares_id'=> $value['wares_id'],
                    'is_play' => $isplay,
                    'box_type'=> $class,
                    'is_public_play'=> $is_public_play,
                    'term'    => $value['term'],
                );
                $this->awardlogMod->insert($awardlogData);
		
		 $aray=array(
                    'user_id' => $this->user_id,
                    'num'     => $value['num'],
                    'addtime' => time(),
                    'type'    => $value['type'],
                    'wares_id'=> $value['wares_id'],
                    'ispg'=>0
                );
                $ad=Db::name("qpgg")->data($aray)->insert();
		
		
                //获得物品有效截止时间
                $expire = $this->getWaresExpire($value['type'],$value['wares_id'],$value['num']);

                //获得奖品加入背包，加入背包前查询背包中是否已存在
                userPackStoreInc($this->user_id,$value['type'],$value['wares_id'],$value['num'],3,3);
                //$this->ifPackWares($value['type'],$value['wares_id'],$value['num'],$expire);

                //获得奖品名字
                $gerWaresInfo = $this->getAwardName($value['type'],$value['wares_id']);
                $value['name'] = $gerWaresInfo['name'];
                $value['price'] = $gerWaresInfo['price'];
                $value['show_img'] = $this->auth->setFilePath($value['img']);
              
                
                    //echo Db::name('award_box')->getlastsql();die;
                if ($is_public_play == 1){
                    
                    $awardTxt .= $value['name'].'×'.$value['num'].' ';
                }
              
              
                unset($value['img'],$value['award_box_id']);
            }

            //加积分
            //开箱赠送一次积分
            Db::name('users')->where('id',$this->user_id )->setInc('points', $keysNum);
            $points = Db::name('users')->where(array('id'=>$this->user_id))->value('points');
            $pointslogData = array(
                'user_id'  => $this->user_id,
                'type'     => 1,
                'get_nums' => $keysNum,
                'now_nums' => $points,
                'addtime'  => time(),
            );
            Db::name('points_log')->insert($pointslogData);

            //修改钥匙数量，查询钥匙是否足够扣除
            $remain_keys_num = Db::name('users')->where(array('id'=>$this->user_id))->value('keys_num');
            $deckeysNums = $keysNum;
            if ($remain_keys_num < $keysNum){
                $deckeysNums = 0;
            }
            addKeysNum($this->user_id,-$deckeysNums,2);
            Db::name('users')->where('id',$this->user_id )->setDec('keys_num',$deckeysNums);

            //判断用户积分是否首次达到500,首次掉落扩展卡
            $result['ispointsfirst'] = 0;
            $userinfo = Db::name('users')->field('points,is_points_first')->where(array('id'=>$this->user_id))->find();
            if ($userinfo['points'] >= 500 && $userinfo['is_points_first'] == 0){
                $waresInfo  = Db::name('wares')->field('id,expire')->where(array('name'=>'扩展卡'))->find();
                $tuozhanId  = $waresInfo['id'];
                userPackStoreInc($this->user_id,3,$tuozhanId,1,3,3);
                $result['ispointsfirst'] = 1;
                Db::name('users')->where(array('id'=>$this->user_id))->update(array('is_points_first'=>1));
                
            }

            $result['awardList'] = $item;
            $result['box_class'] = $class;
          
            //奖品公屏播报
            $classname = $class == 1 ? "普通" : "守护";
            $nickname = Db::name('users')->where(array('id'=>$this->user_id))->value('nickname');
            $awardTxt = rtrim($awardTxt,' ');
            $html = !empty($awardTxt) ? $awardTxt : '';
            $result['user_name']  = $nickname;
            $result['user_id']    = $this->user_id;
            $result['award_tips'] = $html;

            //提交事务
            Db::commit();
        } catch (\Exception $e) {
            //回滚事务
            
            Db::rollback();
            $this->ApiReturn(0, '开奖失败');
        }

        $this->ApiReturn(1, '请求成功',$result);
    }

    /*
    中奖记录
    */
    public function getAwardRecordList(){
        $params  = $this->request->request();
        $page    = isset($params['page']) && (int)$params['page'] > 0 ? (int)$params['page'] : 1;


        $where['user_id'] = array('eq',$this->user_id);
        $field = 'id,wares_id,type,addtime,is_play,num';
        $list = $this->awardlogMod->field($field)->where($where)->whereTime('addtime','>=','-7 days')->page($page,10)->order('id desc')->select();
        foreach ($list as $key => & $value) {
            //获得奖品名字
            $gerWaresInfo      = $this->getAwardName($value['type'],$value['wares_id']);
            $value['name']     = $gerWaresInfo['name'];
            $value['show_img'] = $this->auth->setFilePath($gerWaresInfo['show_img']);
            $value['addtime']  = date('Y-m-d',$value['addtime']);
        }
        $this->ApiReturn(1, '请求成功',$list);
    }

    /*
    积分兑换
    *@waresId 物品ID
    */
    public function actionAwardExchange(){
        setViewNum($this->user_id);
        $params = $this->request->request();
        $waresId = $params['waresId'];
        if (empty($waresId)){
            $this->ApiReturn(0,'参数错误');
        }

        //查询物品
        $where['enable'] = array('eq',1);
        $where['score']  = array('gt',0);
        $where['get_type']  = array('eq',7);
        $where['id']     = array('eq',$waresId);
        $field = 'id,type,score,name,show_img';
        $info = $this->waresMod->field($field)->where($where)->find();
        if (!$info || empty($info['score'])){
            $this->ApiReturn(0,'兑换物品不存在或已下架');
        }

        //验证用户剩余积分是否足够
        $points = Db::name('users')->where(array('id'=>$this->user_id))->value('points');
        if (empty($points) || ($points < $info['score'])){
            $this->ApiReturn(0,'剩余积分不足');
        }

        //入库兑换表、积分记录表，减少用户表积分数量
        Db::startTrans();
        try{
            Db::name('users')->where('id',$this->user_id )->setDec('points', $info['score']);
            $exchangeData = array(
                'user_id'=> $this->user_id,
                'nums'   => $info['score'],
                'wares_id' => $waresId,
                'addtime'  => time(),
            );
            Db::name('points_exchange')->insert($exchangeData);

            $pointslogData = array(
                'user_id'=> $this->user_id,
                'type'   => 2,
                'get_nums'=> $info['score'],
                'now_nums' => $points,
                'addtime'  => time(),
            );
            Db::name('points_log')->insert($pointslogData);

            //放入背包
            $expires = $this->getWaresExpire($info['type'],$info['id'],1);
            $this->ifPackWares($info['type'],$info['id'],1,$expires,7);

            //提交事务
            Db::commit();
        } catch (\Exception $e) {
            //回滚事务
            Db::rollback();
            $this->ApiReturn(0, '兑换失败');
        }

        $this->ApiReturn(1, '兑换成功,已发放至我的背包');
        
    }

    /*
    积分兑换礼物列表

    */
    public function getAwardWaresList(){
        $where['enable']    = array('eq',1);
        $where['score']     = array('gt',0);
        $where['get_type']  = array('eq',7);
        $field = 'id,type,score,name,show_img';
        $list = $this->waresMod->field($field)->order('score asc')->where($where)->select();
        foreach ($list as $key => & $value) {
            $value['show_img'] = $this->auth->setFilePath($value['show_img']);
        }
        $this->ApiReturn(1, '请求成功', $list);
    }

    /**
     * 获取用户剩余积分和
     */
    
    public function insertData(){
        die;

        $data = array();
        for($i = 1; $i <= 1; $i++){

            $data[] = array(
                'num'    =>1,
                'status' =>1,
                'addtime'=>'1567477957',
                'wares_id'=>4,
                'type'    =>1,
                'class'   =>2,
            );

        }

        db::name('award')->insertAll($data);

    }

    /**
     * 守护宝箱还是普通宝箱,开启时间
     * 钥匙数量，积分数量，宝箱状态，开始时间 结束时间
     */
    public function getBoxInfo(){
        //宝箱类型
        $data['boxclass'] = $this->getBoxType();

        $userinfo = Db::name('users')->field('keys_num,points')->where(array('id'=>$this->user_id))->find();
        $data['keys_num'] = $userinfo['keys_num'] ? $userinfo['keys_num'] : '0';
        $data['points']   = $userinfo['points'] ? $userinfo['points'] : '0';

        // $box_starttime = $this->getConfig('box_starttime');
        // $data['boxstartdate']  = strtotime(date('Y-m-d').' '.$box_starttime);
        // $box_long      = $this->getConfig('box_long');
        // $data['boxenddate']    = strtotime(date('Y-m-d H:i',$data['boxstartdate'] + ($box_long * 60)));
        $data['boxstartdate']=2145888000;
        $data['boxenddate']=2145889800;
        $this->ApiReturn(1, '请求成功', $data);
    }



    public function getBoxInfo02(){
        $userinfo = Db::name('users')->field('keys_num,points')->where(array('id'=>$this->user_id))->find();
        $data['keys_num'] = $userinfo['keys_num'] ? $userinfo['keys_num'] : '0';
        $data['points']   = $userinfo['points'] ? $userinfo['points'] : '0';
        $box_time_list=$this->getConfig('box_time_list');
        $time_arr=explode(",", $box_time_list);
        $hour=date('H');
        $box_long      = $this->getConfig('box_long');
        if(in_array($hour, $time_arr)){
            $start=date('Y-m-d H:i:s', mktime($hour, 0, 0, date('m') , date('d') , date('Y')));
        }else{
            $start = 0;
        }
        $end = $start + $box_long * 60;
        $nowtime = time();
        //宝箱类型
        if (($nowtime >= $start) && ($nowtime < $end)) {
            $data['boxclass'] = 2;
        }else{
            $data['boxclass'] = 1;
        }
        $data['boxstartdate']  = $start;
        $data['boxenddate']    = $end;

        $this->ApiReturn(1, '请求成功', $data);
    }




    /*
    *根据钥匙数量计算所需米钻数量
     */
    public function getMizuanNum(){
        $params = $this->request->request();
        $keysNum = $params['keysNum'];

        if (empty($keysNum) || ($keysNum <= 0)){
            $this->ApiReturn(0,'参数错误');
        }

        $radio = $this->getConfig('keys_radio');
        $result['needMizuan'] = (string)($keysNum * (int)$radio);

        $result['needMizuan'] = number_format($result['needMizuan'], 0, '', '');

        $this->ApiReturn(1, '请求成功', $result);

    }

    //修改守护宝箱的开启时间
    // public function updBoxTime_1(){
    //     $rs = Db::name('config')->where(array('name'=>'box_starttime'))->update(array('value'=>'20:00'));
    // }

    //修改守护宝箱的开启时间
    // public function updBoxTime_2(){
    //     $rs = Db::name('config')->where(array('name'=>'box_starttime'))->update(array('value'=>'22:00'));
    // }



    //////////////////////////////////////////////////////内部方法///////////////////////////////////////////////////////////////////////////////////
    private function getAwardName($type,$id){
        $tableName = 'wares';
        $field = 'name,show_img,price';
        if ($type == 2){
            $tableName = 'gifts';
            $field = 'name,img as show_img,price';
        }

        $info = Db::name($tableName)->field($field)->where(array('id'=>$id))->find();

        return $info;
    }

    /**
     * 获得宝箱类型
     */
    private function getBoxType(){
        $box_starttime = $this->getConfig('box_starttime');
        $boxstartdate  = date('Y-m-d').' '.$box_starttime;
        $box_long      = $this->getConfig('box_long');
        $boxenddate    = date('Y-m-d H:i',(strtotime($boxstartdate) + ($box_long * 60)));
        $nowtime       = time();

        $boxclass = 1;
        if (($nowtime >= strtotime($boxstartdate)) && ($nowtime < strtotime($boxenddate))) {
            $boxclass = 2;
        }
        $boxclass = 1;
        return $boxclass;
    }

    /**
     * 获得物品有效时间
     */
    private function getWaresExpire($type,$wares_id,$num){
        $expire = 0;
        if ($type != 2 && $wares_id){
           $expire = Db::name('wares')->where(array('id'=>$wares_id))->value('expire');
           if ($expire){
                $expire = (time() + ($expire * 86400 * $num));
           } 
        }
        return $expire;
    }

    /**
     * 背包中物品是否已存在
     */
    private function ifPackWares($type,$wares_id,$num,$expire,$get_type=3){
        $ifPack = Db::name('pack')
            ->field('id,expire')
            ->where(array('user_id'=>$this->user_id,'type'=>$type,'target_id'=>$wares_id))
            ->find();
        if ($ifPack){
            $expires = $ifPack['expire'];
            if ($expires != 0 && $expires > time()){
                $expire = $expire + ($expires - time());
            }
            
            Db::name('pack')->where(array('id'=>$ifPack['id']))->setInc('num', $num);
            Db::name('pack')->where(array('id'=>$ifPack['id']))->update(array('expire'=>$expire));
        }else{
            $packData = array(
                'user_id'  => $this->user_id,
                'target_id'=> $wares_id,
                'num'      => $num,
                'expire'   => $expire,
                'addtime'  => time(),
                'get_type' => $get_type,
                'type'     => $type,
            );
            Db::name('pack')->insert($packData);
        }
    }

    /**
     * 生成奖池数据
     */
    public function setAwardList(){
        //删除临时表数据，id从1 开始
      
        $query = Db::name('award_temp')->query('TRUNCATE TABLE b_award_temp');

        //宝箱数据写入临时表
        $info = Db::name('award_box')->where('1=1')->select();
        foreach($info as $key=>$val){
            $num = $val['num'];
            for ($i=0; $i < $num; $i++) { 
               $data = array(
                    'num'     => 1,
                    'status'   =>1,
                    'wares_id' => $val['wares_id'],
                    'type'     => $val['type'],
                    'class'    => $val['box_type'],
                    'term'      =>1,
                    'award_box_id'=>$val['id'],
                    'img'     => $val['img'],
                    'addtime' =>time(),
                    'is_special' =>$val['is_special'],
                );
               Db::name('award_temp')->insert($data);
            }
            
        }
      
        //随机从临时表取数据写入正式表
        $query = Db::name('award')->query('TRUNCATE TABLE b_award');
        $info = Db::name('award_temp')->orderRaw('rand()')->select();

        foreach($info as $key=>$val){
             $data = array(
                'num'     => $val['num'],
                'status'   =>1,
                'wares_id' => $val['wares_id'],
                'type'     => $val['type'],
                'class'    => $val['class'],
                'term'      =>$val['term'],
                'award_box_id'=>$val['award_box_id'],
                'img'     => $val['img'],
                'img'     => $val['img'],
                'addtime' =>$val['addtime'],
                'is_special' =>$val['is_special'],
            );   
            Db::name('award')->insert($data); 
        }
        
        
    }




    


   
    



































}
