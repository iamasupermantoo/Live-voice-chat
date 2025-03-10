<?php

namespace app\admin\model\usersmanage;

use think\Model;


class Users extends Model
{

    

    

    // 表名
    protected $name = 'users';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'locktime_text'
    ];
    

    



    public function getLocktimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['locktime']) ? $data['locktime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setLocktimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    //密码生成
    public function getEncyPwd($value){
        $salt = substr(md5(time()),0,3);
        return $this->pwdMd5($value,$salt);
    }

    protected function pwdMd5($value, $salt)
    {
        $data['user_pwd'] = md5(md5($value) . $salt);
        $data['salt']     = $salt;
        return $data;
    }

    //获取房间类型
    public static function saveMizuanByUserid($user_id,$mizuan){
        $obj = new self();
        $obj->where('id', $user_id)->setInc('mizuan',$mizuan);
    }

    //根据手机号获得用户信息
    public static function getUserinfoByPhone($phone,$field){
        $obj = new self();
        $res = $obj::field($field)->where('phone', $phone)->find();
        return $res;
    }

    //获取房间类型
    public static function getUserinfoByUserid($user_id,$field){
        $obj = new self();
        $res = $obj::field($field)->where('phone', $phone)->find();
        return $res;
    }


}
