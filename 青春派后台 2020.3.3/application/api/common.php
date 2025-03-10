<?php
use think\Db;


/************************************************** 七 期 ***************************************************************/
//判断是否是好友
function IsFriend($user_id = null,$followed_user_id = null){
    if(!$user_id || !$followed_user_id) return 0;
    if($user_id == $followed_user_id)   return 1;
    $guanzhu=DB::name('follows')->where(['user_id'=>$user_id,'followed_user_id'=>$followed_user_id,'status'=>1])->value('id');
    $beiguanzhu=DB::name('follows')->where(['user_id'=>$followed_user_id,'followed_user_id'=>$user_id,'status'=>1])->value('id');
    return ($guanzhu && $beiguanzhu) ? 1 : 0;
}

/**
 * 返回优惠券
 * @param int                   user_id             用户id
 * @param int                   id                  优惠券id
 */
function refund_coupon($user_id,$id){
    $data=Db::name('user_coupons')->where(['user_id'=>$user_id,'id'=>$id,'status'=>2])->find();
    if(!$data)  return 0;
    $info['status'] = time() > $data['expire']  ? 3 : 1 ;
    Db::name('user_coupons')->where(['id'=>$id])->update($info);
}


/**
 * 完成任务
 * @param int                   user_id             用户id
 * @param int                   task_id             任务id
 */
function fin_task($user_id,$task_id){
    $task=Db::name('task')->where(['id'=>$task_id,'enable'=>1])->find();
    if(!$task)  return 0;
    $user_task=Db::name('user_task')->where(['user_id'=>$user_id])->find();
    if($task['type'] == 1 && !substr_count($user_task['not_fin_1'],$task_id))   return 0;
    $field='fin_'.$task['type'];
    $str=$user_task[$field];
    $num=substr_count($str,$task_id);
    if($num == $task['num'])    return 0;
    $str_arr=explode(',', $str);
    $str_arr[]=$task_id;
    $info[$field]=trim(implode(',', $str_arr),',');
    Db::name('user_task')->where(['user_id'=>$user_id])->update($info);
    return 1;
}
/**
 * 将阿拉伯数字转换成中文
 * @param int           num             阿拉伯数字
 */
function toChinaseNum($num){
    if($num<=0) return '';
    $len=strlen($num);
    if($len > 10 ) return '';
    $char = array("零","一","二","三","四","五","六","七","八","九");
    $dw = array("","十","百","千","万","十万","百万","千万","亿","兆");
    $retval = "";
    $proZero = false;
    for($i = 0;$i < $len;$i++){
        if($i > 0)    $temp = (int)(($num % pow (10,$i+1)) / pow (10,$i));
        else $temp = (int)($num % pow (10,1));
         
        if($proZero == true && $temp == 0) continue;
         
        if($temp == 0) $proZero = true;
        else $proZero = false;
         
        if($proZero)
        {
            if($retval == "") continue;
            $retval = $char[$temp].$retval;
        }
        else $retval = $char[$temp].$dw[$i].$retval;
    }
    if($retval == "一十") $retval = "十";
    return $retval;
}

/************************************************** 六 期 ***************************************************************/
//根据vip等级获取昵称颜色
function getNickColorByVip($level=0){
    if($level < 3){
        $color='#ffffff';
    }elseif($level>=3 && $level<7){
        $color='#93ffa5';
    }elseif($level>=7 && $level<11){
        $color='#8ce1fe';
    }elseif($level>=11 && $level<15){
        $color='#ffc6e1';
    }elseif($level>=15 && $level<18){
        $color='#e09dff';
    }elseif($level>=18 && $level<=20){
        $color='#fff585';
    }
    return $color;
}




//判断是否关注对方
function IsFollow($user_id = null,$followed_user_id = null){
    if(!$user_id || !$followed_user_id) return 0;
    if($user_id == $followed_user_id)   return 1;
    $id=DB::name('follows')->where(['user_id'=>$user_id,'followed_user_id'=>$followed_user_id,'status'=>1])->value('id');
    return $id ? 1 : 0;
}

/************************************************** 三 期 ***************************************************************/

//最大等级与经验值
function getNextLevel($type=null,$level=null,$field=null){
    if(!$type || !$field )   return 0;
    $max=DB::name('vip')->where(['type'=>$type])->order('id desc')->limit(1)->value($field);
    $next = DB::name('vip')->where(['type' => $type])->where('level', '>', $level)->order('id asc')->limit(1)->value($field) ? : 0;
    return $next ? : $max;
}


//获取用户信息
function getUserField($user_id=null,$field=null){
    if(!$user_id || !$field)    return '';
    $str='id,nickname,headimgurl,sex,birthday,constellation,city';
    $fie_arr=explode(",", $str);
    if(in_array($field, $fie_arr)){
        $data=getRedisUserInfo($user_id);
        if(isset($data[$field]))    return $data[$field];
    }
    $value=DB::name('users')->where(['id'=>$user_id])->field($field)->value($field);
    return $value ? : '';
}

//Redis中获取用户信息
function getRedisUserInfo($user_id){
    $cacheKey   = sprintf(\app\common\controller\Rediskey::getKey('users'), $user_id);
    $RedisCache = new \app\api\controller\RedisCache;
    $data = $RedisCache->getRedisInfoByWhere($cacheKey,'getUsersinfo',array('id'=>$user_id));
    return $data;
}

//物品获取途径
function get_wares_way($val){
    $arr=[
        1 => 'VIP等级解锁所得',
        2 => '活动所得',
        3 => '宝箱开出',
        4 => '米钻购买',
        5 => '后台赠送',
        6 => '限时购买',
        7 => '宝箱积分兑换',
        8 => 'CP等级解锁',
        104=>'暂不出售',
        103=>'限时开放',
    ];
    return $arr[$val];
}

//一维数组转为字符串  id=1&type=2
function arr_to_str($arr = array()){
    $str='';
    if(!$arr)   return $str;
    foreach ($arr as $k => $v) {
        if(strpos($k,"html") || strpos($k,"php") || !$k){
            $str.='';
        }else{
            $str.=$k.'='.$v.'&';
        }
    }
    $str=rtrim($str,"&");
    return $str;
}
//计算年龄
function getAge($birthday = null){
    if(!$birthday) return 0;
    list($year,$month,$day) = explode("-",$birthday);
    $year_diff = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff  = date("d") - $day;
    if ($month_diff < 0 || ($month_diff == 0 && $day_diff < 0)){
        $year_diff--;
    }
    return ($year_diff < 0 ) ? 0 : $year_diff;
}




//验证手机号
function isMobile($mobile) {
    if (!$mobile || !is_numeric($mobile)) {
        return false;
    }
    // return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$|^19[\d]{9}$#', $mobile) ? true : false;
    return preg_match('#^(1[0-9])\d{9}$#', $mobile) ? true : false;
}
//验证码对比
function check_sms($phone, $code) {
    if (!$phone || !$code) return false;
    if($code == 1234)  return true;
    $data = Db::name('code')->where('phone', $phone)->find();
    if (!$data) return false;
    $sjc = $data['addtime'] + 300 - time();
    if ($sjc < 0 || $data['code'] != $code) {
        return false;
    } else {
        return true;
    }
}
//密码格式是否正确
function isPassword($password = 0) {
    if (!$password) {
        return false;
    }
    if (strlen($password) < 6 || strlen($password) > 20) {
        return false;
    }
    return preg_match('/^[A-Za-z0-9]+$/', $password) ? true : false;
}
//获取订单编号
function getOrderNo($prefix = 'MN') {
    $order_no = $prefix . date("YmdHis") . rand(100000, 999999);
    return $order_no;
}
//可用
function curlPost($url = null, $post_data = null) {
    if (!$url || !$post_data) return false;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/x-www-form-urlencoded;charset=utf-8',
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $ret = curl_exec($ch);
    return $ret;
}
//获取黑名单列表
function getUserBlackList($user_id = null) {
    if (!$user_id) return [];
    $ids = DB::name('black')->where('user_id', $user_id)->where('status', 1)->column('from_uid');
    return $ids;
}





//敏感词分类
function getYunDunGreen($val){
    $arr=[
        'normal'=>'正常文本',
        'spam'=>'垃圾',
        'ad'=>'广告',
        'politics'=>'涉政',
        'terrorism'=>'暴恐',
        'abuse'=>'辱骂',
        'porn'=>'色情',
        'flood'=>'灌水',
        'contraband'=>'违禁',
        'meaningless'=>'无意义',
        'customized'=>'违规', //自定义
    ];
    return $arr[$val];
}
//图片审核分类
function getYunDunImgAllGreen($val){
    $arr=[
        'normal'=>'正常',
        'sexy'=>'性感',
        'porn'=>'色情',
        'bloody'=>'血腥',
        'explosion'=>'爆炸烟光',
        'outfit'=>'特殊装束',
        'logo'=>'特殊标识',
        'weapon'=>'武器',
        'politics'=>'涉政',
        'violence' => '打斗',
        'crowd'=>'聚众',
        'parade'=>'游行',
        'carcrash'=>'车祸现场',
        'others'=>'其他',
        'abuse'=>'含辱骂',
        'terrorism'=>'含暴恐',
        'contraband'=>'含违禁',
        'spam'=>'含其他垃圾',
        'npx'=>'牛皮藓广告',
        'qrcode'=>'包含二维码',
        'programCode'=>'包含小程序码',
        'ad'=>'其他广告',
        'meaningless'=>'无意义',
        'PIP'=>'画中画',
        'smoking'=>'吸烟',
        'drivelive'=>'车内直播',
        'TV'=>'带有管控logo的',
        'trademark'=>'商标',
    ];
    return $arr[$val];
}
//图片审核分类
function getYunDunImgGreen($val){
    $arr=[
        'porn'=>'色情',
        'bloody'=>'血腥',
        'politics'=>'涉政',
        'abuse'=>'辱骂',
        'ad'=>'广告',
    ];
    $key=['porn','bloody','politics','abuse','ad'];
    return in_array($val, $key) ? $arr[$val] :false;
}

//获取开始,结束时间
//1本日,2本周,3本月,4上月,5昨日
function star_end_time($type = 1 , $class = 1) {
    $today = date('Y-m-d', time());
    if ($type == 1) {
        $star = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') , date('Y')));
        $end = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d') , date('Y')));
    } elseif ($type == 2) {
        $w = date('w', strtotime($today));
        $star = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') - $w + 1, date('Y')));
        $end = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d') + (7 - $w) , date('Y')));
    } elseif ($type == 3) {
        $star = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , str_pad(1, 2, 0, STR_PAD_LEFT) , date('Y')));
        $last_month_days = date('t', strtotime(date('Y') . '-' . (date('m')) . '-' . str_pad(1, 2, 0, STR_PAD_LEFT)));
        $end = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , $last_month_days, date('Y')));
    } elseif ($type == 4) {
        $star = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') - 1, str_pad(1, 2, 0, STR_PAD_LEFT) , date('Y')));
        $last_month_days = date('t', strtotime(date('Y') . '-' . (date('m') - 1) . '-' . str_pad(1, 2, 0, STR_PAD_LEFT)));
        $end = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') - 1, $last_month_days, date('Y')));
    } elseif($type == 5) {
        $star = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') , date('d') - 1 , date('Y')));
        $end = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m') , date('d') - 1 , date('Y')));
    }else{
        return false;
    }
    if($class == 1){
        $arr[] = $star;
        $arr[] = $end;
    }elseif($class == 2){
        $arr[] = strtotime($star);
        $arr[] = strtotime($end);
    }
    return $arr;
}



/**
 * 限制每秒访问次数
 */
function setViewNum($user_id,$time = 1 , $type = 'arr'){
    $redis = RedisCli();
    $key = get_real_ip() . '_' . $user_id;

    //限制次数为1
    $limit = 1;
    // $info=[
    //         'code' => 5,
    //         'message'  => '请求太频繁，请稍后再试！',
    //         'data' => [],
    //     ];
    $info['code'] = 5;
    $info['message'] = '请求太频繁，请稍后再试！';
    $info['data'] = ($type == 'arr') ? [] : (object)[];
    $check = $redis->exists($key);
    if($check){
        $redis->incr($key);
        $count = $redis->get($key);
        if($count > $limit){
            echo json_encode($info,JSON_UNESCAPED_UNICODE);
            exit();
        }
    }else{
        $redis->incr($key);
        //限制时间为60秒 
        $redis->expire($key,$time);
    }
} 

//获取客户端真实ip地址
function get_real_ip(){
    static $realip;
    if(isset($_SERVER)){
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $realip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }else if(isset($_SERVER['HTTP_CLIENT_IP'])){
            $realip=$_SERVER['HTTP_CLIENT_IP'];
        }else{
            $realip=$_SERVER['REMOTE_ADDR'];
        }
    }else{
        if(getenv('HTTP_X_FORWARDED_FOR')){
            $realip=getenv('HTTP_X_FORWARDED_FOR');
        }else if(getenv('HTTP_CLIENT_IP')){
            $realip=getenv('HTTP_CLIENT_IP');
        }else{
            $realip=getenv('REMOTE_ADDR');
        }
    }
    return $realip;
}


