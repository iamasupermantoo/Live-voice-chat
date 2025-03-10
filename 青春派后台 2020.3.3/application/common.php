<?php
use think\Db;
use think\Config;
use app\common\controller\Rediskey;
use app\api\controller\RedisCache;

/**
 * 获取手续费比例或族长id
 * @param int                   user_id             用户id
 * @param int                   type                返回数据 1:返回手续费比例,2:返回族长id
 */
function getFeeRatio($user_id,$type = 1){
    $family_id=Db::name('family_user')->where(['status'=>1,'user_id'=>$user_id])->value('family_id');
    $f_user_id=Db::name('family_user')->where(['status'=>1,'user_type'=>2,'family_id'=>$family_id])->value('user_id');
    if($type == 1){
        //未加入家族10%,加入家族20%
        return $f_user_id ? 0.2 : 0.1;
    }elseif($type == 2){
        return $f_user_id ? : null ;
    }
}

// 查出二维数组中重复数组
function chongfu($data,$keywords = 'code'){
    $temp=[];
    foreach ($data as $k => &$v) {
        $temp[]=$v[$keywords];
    }
    unset($v);
    $temp2=array_unique($temp);
    $temp3=[];
    foreach ($temp2 as $k => &$v) {
        foreach ($data as $k1 => &$v1) {
            if($v == $v1[$keywords]){
                $temp3[$v][]=$v1;
            }
        }
        unset($v1);
    }
    unset($v);
    return $temp3;
}

//支付方式
function getPayType($val = null) {
    $arr = [
        1 => '支付宝',
        2 => '微信',
        3 => '余额',
        4 => '待支付',
    ];
    return  $val ? $arr[$val] : $arr;
}

// b_store_log表get_type字段说明
function getTranmoneyType($val = null) {
    $arr = [
        //米钻
        11 => '米钻充值',            //增加米钻
        12 => '米币兑换',            //增加米钻
        13 => '送礼物',              //减少米钻
        14 => '后台增加米钻余额',     //增加米钻
        15 => '后台减少米钻余额',     //减少米钻
        16 => '购买钥匙',            //减少米钻
        17 => '购买宝石',            //减少米钻
        18 => '卡片兑换',            //增加米钻
        19 => '购买扩展卡',          //减少米钻
        //米币
        21 => '收礼物',             //增加米币
        22 => '取消提现',           //增加米币
        23 => '提现',               //扣除米币
        24 => '兑换米钻',           //扣除米币
        25 => '后台增加米币余额',    //增加米币
        26 => '后台减少米币余额',    //减少米币
        //房间米币
        31 => '房间流水',           //增加房间米币
        32 => '下级分成',           //增加房间米币
        33 => '取消提现',           //增加房间米币
        34 => '提现',               //扣除房间米币
        35 => '兑换米钻',           //扣除房间米币
        36 => '后台增加房间米币余额',//增加房间米币
        37 => '后台减少米币余额',    //减少房间米币
        //米粒
        41 => '米粒充值',           //增加米粒
        43 => '订单退还',           //增加米粒
        44 => '后台增加米粒余额',    //增加米粒
        45 => '后台减少米粒余额',    //减少米粒
        46 => '订单支付',           //减少米粒
        //米粒值        
        51 => '订单收入',           //增加米粒值
        52 => '米粒值提现',         //减少米粒值
        53 => '族长提成',         //增加米粒值
        //金币
        61 => '签到收入',           //增加金币
        62 => '任务领取',           //增加金币
        63 => '金币兑换',           //减少金币

        99 => 'admin', //平台所得
    ];
    return  $val ? $arr[$val] : $arr;
}
//用户资源分类
function getMoneyType($val = null) {
    $arr = [
        1 => '米钻',
        2 => '米币',
        3 => '房间收益',
        4 => '米粒',
        5 => '米粒值',
        6 => '金币',
    ];
    return  $val ? $arr[$val] : $arr;
}

//充值分类
function getRechargeType($val = null) {
    $arr = [
        1   => '米钻充值',
        2   => '米粒充值',
    ];
    return  $val ? $arr[$val] : $arr;
}

//增加数值
function userStoreInc($user_id, $get_nums, $get_type, $jb_type) {
    $get_nums=abs($get_nums);
    if($get_nums == 0) return false;
    if ($get_type == 99) {
        $now_nums = 0;
        $res = 1;
    } else {
        $res = Db::name('users')->where(['id' => $user_id])->setInc($jb_type, $get_nums);
        $now_nums = Db::name('users')->where(['id' => $user_id])->value($jb_type);
    }
    if (!$res) return false;
    addTranmoney($user_id, $get_nums, $get_type, $now_nums,'');
    return $res;
}
//减少数值
function userStoreDec($user_id, $get_nums, $get_type, $jb_type) {
    $get_nums=abs($get_nums);
    if($get_nums == 0) return false;
    $res = Db::name('users')->where(['id' => $user_id])->setDec($jb_type, $get_nums);
    if (!$res) return false;
    $now_nums = Db::name('users')->where(['id' => $user_id])->value($jb_type);
    addTranmoney($user_id, $get_nums, $get_type, $now_nums,'-');
    return $res;
}
//创建记录
function addTranmoney($user_id, $get_nums, $get_type, $now_nums,$fuhao) {
    $info['user_id']  = $user_id;
    $info['get_nums'] = $get_nums;
    $info['get_type'] = $get_type;
    $info['now_nums'] = $now_nums;
    $info['addtime']  = time();
    $info['fuhao']    = $fuhao;
    $info['types']    = getTypesByGettype($get_type);
    $res = Db::name('store_log')->insertGetId($info);
    return $res;
}
// 用户资源分类
function getTypesByGettype($gettype){
    if (in_array($gettype, [11,12,13,14,15,16,17,18,19])){
        return 1;
    }else if(in_array($gettype, [21,22,23,24,25,26])){
        return 2;
    }else if(in_array($gettype, [31,32,33,34,35,36,37])){
        return 3;
    }else if(in_array($gettype, [41,43,44,45,46])){
        return 4;
    }else if(in_array($gettype, [51,52,53])){
        return 5;
    }else if(in_array($gettype, [61,62,63])){
        return 6;
    }
    return 0;
}


//游戏订单状态分类
//type  0用户1大神
function getGmOrdersText($val = null,$type = 1){
    $user=[
        1   =>  '待支付',
        2   =>  '待接单',
        3   =>  '待服务',
        31  =>  '大神申请立即服务',
        4   =>  '进行中',
        5   =>  '已完成',
        6   =>  '已取消',
        7   =>  '大神已拒绝',

        81  =>  '退款申请',
        82  =>  '退款成功',
        83  =>  '退款失败',
        84  =>  '申诉中',
    ];

    $god=[
        1   =>  '待支付',
        2   =>  '待接单',
        3   =>  '待服务',
        31  =>  '已申请立即服务',
        4   =>  '进行中',
        5   =>  '已完成',
        6   =>  '对方已取消',
        7   =>  '已拒绝',
        
        81  =>  '退款申请',
        82  =>  '同意退款',
        83  =>  '拒绝退款',
        84  =>  '对方申诉中',
    ];
    if($type == 1){
        return $val ? $user[$val] : $user;
    }elseif(in_array($type, [2,3])){
        return $val ? $god[$val] : $god;
    }else{
        return '';
    }
}



function RedisKey($val){
    $rt=Config::get('RedisType');
    $arr=array(
        'users'=>'normal:users:%s',
        'gifts'=>$rt.'gifts:%s',
    );
    return empty($arr[$val]) ? '' : $arr[$val];
}


// 连接Redis
function RedisCli(){
    $redis = new \Redis();
    $redis->connect('127.0.0.1',6379);
    $redis->auth('miniyuyin');
    return $redis;
}


//房间热度处理
function room_hot($hot = null){
    $hot=(int)$hot;
    if(!$hot)   return 0;
    if($hot <= 9999){
        return $hot;
    }elseif($hot > 9999 && $hot <= 99999999){
        $hot = round($hot/10000 , 1);
        return $hot.'w';
    }elseif($hot > 99999999 ){
        $hot = round($hot/100000000 , 2);
        return $hot.'亿';
    }
}

/****************************************** 四 期 *******************************************************************************/
// 公共助手函数

function http_curl($url, $method = 'GET', $postfields = null, $headers = array(), $debug = false){
    $ci = curl_init();
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ci, CURLOPT_TIMEOUT, 30);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case 'POST':
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                $this->postdata = $postfields;
            }
            break;
    }
    curl_setopt($ci, CURLOPT_URL, $url);
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);

    $response = curl_exec($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);

        echo '=====info=====' . "\r\n";
        print_r(curl_getinfo($ci));

        echo '=====$response=====' . "\r\n";
        print_r($response);
    }
    curl_close($ci);
    return array($http_code, $response);
}


//空控制器操作
function error_page(){
    $url="http://".$_SERVER['HTTP_HOST'].'/404.html';
    header("Location:".$url."");exit;
}

/**
 * 根据生日获取对应年龄,属相,星座
 *
 * @param string $birthday 生日    2019-01-01
 * @param int    $type   0年龄1属相2星座
 * @return boolean|string
 */
function getBrithdayMsg($birthday=null,$type = 1)
{
    if(!$birthday)  return false;
    if(!in_array($type, [0,1,2]))   return false;
    list($year,$month,$day) = explode("-",$birthday);
    $year_diff = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff  = date("d") - $day;
    if ($month_diff < 0 || ($month_diff == 0 && $day_diff < 0)){
        $year_diff--;
    }
    $info[]=($year_diff < 0 ) ? 0 : $year_diff;

    $animals = array(
        '鼠', '牛', '虎', '兔', '龙', '蛇',
        '马', '羊', '猴', '鸡', '狗', '猪'
    );
    $key = ($year - 1900) % 12;
    $info[]=$animals[$key];
    $signs = array(
        array('20'=>'宝瓶座'),
        array('19'=>'双鱼座'),
        array('21'=>'白羊座'),
        array('20'=>'金牛座'),
        array('21'=>'双子座'),
        array('22'=>'巨蟹座'),
        array('23'=>'狮子座'),
        array('23'=>'处女座'),
        array('23'=>'天秤座'),
        array('24'=>'天蝎座'),
        array('22'=>'射手座'),
        array('22'=>'摩羯座')
    );
    $arr=$signs[$month-1];
    if($day < key($arr)){
        $arr=$signs[($month-2 < 0) ? 11 : $month-2];
    }
    $info[]=current($arr);
    return $info[$type];
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}








//等级特权分类
function getVipAuthType($val = null){
    $arr=[
        3   => 'VIP等级',
        5   => 'CP等级',
    ];
    return $val ? $arr[$val] : $arr;
}

//等级管理分类
function getVipType($val = null){
    $arr=[
        1   => '星锐',
        2   => '金锐',
        3   => 'VIP',
        4   => '徽章',
        5   => 'CP',
    ];
    return $val ? $arr[$val] : $arr;
}
//用户来源分部
function getChannelArr($val = null){
    $arr=[
        'guanfang'  =>'官方',
        '360'       =>'360应用市场',
        'huawei'    =>'华为应用商店',
        'xiaomi'    =>'小米应用市场',
        'oppo'      =>'OPPO',
        'vivo'      =>'vivo',
        'ali'       =>'阿里',
        'baidu'     =>'百度手机助手',
        'yingyongbao'=>'应用宝',
        'sougou'    =>'搜狗手机助手',
        'qita'      =>'其他',
        'lianxiang' =>'联想',
        'meizu'     =>'魅族',
        'sanxing'   =>'三星',
        'weizhi'    =>'未知',
        'landie'    =>'蓝叠',
        'landie01'    =>'蓝叠01',
        'landie02'    =>'蓝叠02',
        'landie03'    =>'蓝叠03',
        'landie04'    =>'蓝叠04',
        'landie05'    =>'蓝叠05',
    ];
    // return $val ? $arr[$val] : $arr;
    if(!$val)   return $arr;
    if(!array_key_exists($val, $arr))   return;
    return $arr[$val];
}
//用户系统分类
function getSystemArr($val = null){
    $arr=[
        'weizhi'    =>'未知',
        'android'   =>'安卓',
        'ios'       =>'IOS',
    ];
    return $val ? $arr[$val] : $arr;
}


/**
 * 删除文件
 * @param  string $file 删除文件的名称
 * @return boolean           返回true则文件全部删除成功，false有文件删除失败
 */
function deleteFile($file = null)
{
    if(!$file)  return false;
    $file_path="./upload/".$file;
    if(is_file($file_path)){
        if(unlink($file_path)){
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}















/************************************************************************************************************************************************/


//物品全部获取途径
function get_wares_allway($val=null){
    $arr=[
        // '' => '请选择',
        1 => 'VIP等级解锁所得',
        2 => '活动所得',
        3 => '宝箱开出',
        4 => '米钻购买',
        5 => '后台赠送',
        6 => '限时购买',
        7 => '宝箱积分兑换',
        8 => 'cp等级解锁',
        9 => '系统退还',
        10 => '签到所得',
        11 => '金币兑换',
        12 => '新人专享',
        102=> '系统物品',
        103=> '限时开放',
        104=>'暂不出售',
    ];
    return $val ? $arr[$val] : $arr;
}

//物品分类列表
function get_wares_type($val=null){
    $arr=[
        // '' => '请选择',
        1 => '宝石',
        2 => '礼物',
        3 => '卡券',
        4 => '头像框',
        5 => '气泡框',
        6 => '进场特效',
        7 => '麦上光圈',
        8 => '徽章',
        9 => '优惠券',
        102=> '系统物品',
    ];
    return $val ? $arr[$val] : $arr;
}
//背包记录使用,获取分类
function user_pack_get_type($val=null){
    $arr=[
        '' => '请选择',
        1 => '正常使用',
        2 => '后台扣除',
        3 => '开宝箱',
        4 => '系统退还',    //退宝石
        5 => '后台赠送',
        6 => '签到所得',
        7 => '金币兑换所得',
    ];
    return $val ? $arr[$val] : $arr;
}
/**
 * /增加背包数值
 * @param  [type] $user_id   [用户id]
 * @param  [type] $type      [物品分类id]
 * @param  [type] $target_id [物品id]
 * @param  [type] $get_nums  [数量]
 * @param  string $use_type  [背包记录变动分类：1=正常使用2=后台扣除3=开宝箱4=退宝石,5=后台赠送,6=签到所得,7=金币兑换所得]
 * @return [get_type]        [背包物品获取分类]
 */
function userPackStoreInc($user_id,$type,$target_id,$get_nums,$use_type = '5',$get_type='5') {
    $get_nums=abs($get_nums);
    if($get_nums == 0) return false;
    
    $where['user_id']=$user_id;
    $where['type']=$type;
    $where['target_id']=$target_id;
    $num=DB::name('pack')->where($where)->find();
    $wares=DB::name('wares')->find($target_id);
    if(!$num){
        //$params = $_POST['row'];
        $info['user_id']=$user_id;
        $info['get_type']=$get_type;
        $info['type']=$type;
        $info['target_id']=$target_id;
        $info['num']=$get_nums;
        $info['expire']= $wares['expire'] ? time()+($wares['expire'] * 86400 * $get_nums) : 0;
        //$info['expire']= $params['expire'] ? strtotime($params['expire']) : 0;
        $info['addtime']=time();
        $res=DB::name('pack')->insertGetId($info);
        $now_nums=$get_nums;
    }else{
        $res=DB::name('pack')->where($where)->setInc('num',$get_nums);
        $now_nums=DB::name('pack')->where($where)->value('num');
        $pack_expire=DB::name('pack')->where($where)->value('expire');
        $expire=$wares['expire'] ? ($pack_expire + ($wares['expire'] * 86400 * $get_nums)) : 0;
        DB::name('pack')->where($where)->update(array('expire'=>$expire));
    }

    //新增存入redis
    $redisMod  = RedisCli();
    $cacheKey  = sprintf(Rediskey::getKey('pack'), $user_id);
    $redisMod->set($cacheKey,$type);

    if(in_array($type, [1,2,3])){//宝石,礼物,卡券
        addPackLog($user_id,$type,$target_id,$get_nums,$use_type,$now_nums);
    }
    return $res;
}
//减少数值
function userPackStoreDec($user_id,$type,$target_id,$get_nums,$use_type = '1') {
    $get_nums=abs($get_nums);
    if($get_nums == 0) return false;
    $where['user_id']=$user_id;
    $where['type']=$type;
    $where['target_id']=$target_id;
    $num=DB::name('pack')->where($where)->value('num');
    if(!$num || $num < $get_nums)   return false;
    if($num == $get_nums){
        $res = DB::name('pack')->where($where)->delete();
        $now_nums=0;
    }else{
        $res = DB::name('pack')->where($where)->setDec('num',$get_nums);
        $now_nums=DB::name('pack')->where($where)->value('num');
    }
    addPackLog($user_id,$type,$target_id,$get_nums, $use_type, $now_nums);
    return $res;
}
//创建记录
function addPackLog($user_id,$type,$target_id,$get_nums, $use_type, $now_nums) {
    $info['user_id'] = $user_id;
    $info['use_type'] = $use_type;
    $info['type'] = $type;
    $info['target_id'] = $target_id;
    $info['get_nums'] = $get_nums;
    $info['now_nums'] = $now_nums;
    $info['addtime'] = time();
    $res = Db::name('pack_log')->insertGetId($info);
    return $res;
}

/**
 * 添加钥匙记录
 */
function addKeysNum($user_id,$get_nums,$get_type=0,$adduser=''){
   $now_nums = Db::name('users')->where(array('id'=>$user_id))->value('keys_num');
   $now_nums = (int)$now_nums;
   $insertData = array(
        'user_id' => $user_id,
        'get_nums'=> $get_nums,
        'get_type'=> $get_type,
        'now_nums'=> $now_nums,
        'addtime' => time(),
        'adduser' => $adduser,

   );
   $res = Db::name('keys_log')->insertGetId($insertData);
   return true;
}














/************************************************************************************************************************************************/












if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int    $size      大小
     * @param string $delimiter 分隔符
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int    $time   时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }
}

if (!function_exists('human_date')) {

    /**
     * 获取语义化时间
     * @param int $time  时间
     * @param int $local 本地时间
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }
}

if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     * @param string  $url    资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $url = preg_match($regex, $url) ? $url : \think\Config::get('upload.cdnurl') . $url;
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain . $url;
        }
        return $url;
    }
}


if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param    string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return true;
        } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname  目录
     * @param bool   $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }
}

if (!function_exists('copydirs')) {

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest   目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }
}

if (!function_exists('addtion')) {

    /**
     * 附加关联字段数据
     * @param array $items  数据列表
     * @param mixed $fields 渲染的来源字段
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields) {
            return $items;
        }
        $fieldsArr = [];
        if (!is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = isset($v['field']) ? $v['field'] : $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = isset($v['display']) ? $v['display'] : str_replace(['_ids', '_id'], ['_names', '_name'], $v['field']);
            $v['primary'] = isset($v['primary']) ? $v['primary'] : '';
            $v['column'] = isset($v['column']) ? $v['column'] : 'name';
            $v['model'] = isset($v['model']) ? $v['model'] : '';
            $v['table'] = isset($v['table']) ? $v['table'] : '';
            $v['name'] = isset($v['name']) ? $v['name'] : str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model'];
            } else {
                $model = $v['name'] ? \think\Db::name($v['name']) : \think\Db::table($v['table']);
            }
            $primary = $v['primary'] ? $v['primary'] : $model->getPk();
            $result[$v['field']] = $model->where($primary, 'in', $ids[$v['field']])->column("{$primary},{$v['column']}");
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $v[$fieldsArr[$n]['display']] = implode(',', array_intersect_key($result[$n], $curr));
                }
            }
        }
        return $items;
    }
}

if (!function_exists('var_export_short')) {

    /**
     * 返回打印数组结构
     * @param string $var    数组
     * @param string $indent 缩进字符
     * @return string
     */
    function var_export_short($var, $indent = "")
    {
        switch (gettype($var)) {
            case "string":
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? "" : var_export_short($key) . " => ")
                        . var_export_short($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
            case "boolean":
                return $var ? "TRUE" : "FALSE";
            default:
                return var_export($var, true);
        }
    }
}

if (!function_exists('letter_avatar')) {
    /**
     * 首字母头像
     * @param $text
     * @return string
     */
    function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" alignment-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}