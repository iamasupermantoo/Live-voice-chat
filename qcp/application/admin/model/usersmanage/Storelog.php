<?php

namespace app\admin\model\usersmanage;

use think\Model;

class Storelog extends Model
{

    

    

    // 表名
    protected $name = 'store_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'addtime_text'
    ];
    

    



    public function getAddtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['addtime']) ? $data['addtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAddtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function getTranmoneyType($val) {
        $arr = [
            11 => '充值', //增加米钻
            12 => '米币兑换', //增加米钻
            13 => '送礼物', //减少米钻
            14 => '后台增加米钻余额',//增加米钻
            15 => '后台减少米钻余额',//减少米钻
            16 => '购买钥匙',//减少米钻
            17 => '购买宝石',//减少米钻
            18 => '卡片兑换',//增加米钻

            21 => '收礼物', //增加米币
            22 => '取消提现', //增加米币
            23 => '提现', //扣除米币
            24 => '兑换米钻', //扣除米币
            25 => '后台增加米币余额',//增加米币
            26 => '后台减少米币余额',//减少米币

            31 => '房间流水', //增加米币
            32 => '下级分成', //增加米币
            33 => '取消提现', //增加米币
            34 => '提现', //扣除米币
            35 => '兑换米钻', //扣除米币
            36 => '后台增加房间米币余额',//增加房间米币
            37 => '后台减少米币余额',//减少房间米币

            99 => 'admin', //平台所得
        ];
        return $arr[$val];
    }

    public function getTranmoneyTypes() {
        $arr = [
            11 => '充值', //增加米钻
            12 => '米币兑换', //增加米钻
            13 => '送礼物', //减少米钻
            14 => '后台增加米钻余额',//增加米钻
            15 => '后台减少米钻余额',//减少米钻
            16 => '购买钥匙',//减少米钻
            17 => '购买宝石',//减少米钻
            18 => '卡片兑换',//增加米钻

            21 => '收礼物', //增加米币
            22 => '取消提现', //增加米币
            23 => '提现', //扣除米币
            24 => '兑换米钻', //扣除米币
            25 => '后台增加米币余额',//增加米币
            26 => '后台减少米币余额',//减少米币

            31 => '房间流水', //增加米币
            32 => '下级分成', //增加米币
            33 => '取消提现', //增加米币
            34 => '提现', //扣除米币
            35 => '兑换米钻', //扣除米币
            36 => '后台增加房间米币余额',//增加房间米币
            37 => '后台减少米币余额',//减少房间米币

            99 => 'admin', //平台所得
        ];
        return $arr;
    }

    public function users()
    {
        return $this->belongsTo('Users', 'user_id','id','[]','left')->setEagerlyType(0);
    }

public static function  updmoney($type,$dbdata,$data,$uid){
        $_mizuan = bcsub($data,$dbdata,2);
        if ($_mizuan == '0.00' || $_mizuan == '0'){
            return '';
        }
        switch($type){
            case 1:
                $arr['get_type'] = $_mizuan > 0 ? 14 : 15;break;
            case 2:
                $arr['get_type'] = $_mizuan > 0 ? 25 : 26;break;
            case 3:
                $arr['get_type'] = $_mizuan > 0 ? 36 : 37;break;
        }

        $arr['user_id']  = $uid;
        $arr['get_nums'] = abs($_mizuan);
        $arr['now_nums'] = $dbdata;
        $arr['addtime']  = time();
        $arr['adduser']  = session('admin.username');
        $obj = new self();
        $obj::insert($arr);
    }


}
