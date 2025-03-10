<?php

namespace app\admin\model\financemanage;

use think\Model;


class Tixian extends Model
{

    

    

    // 表名
    protected $name = 'tixian';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;
    protected $txTime = false;
    // 追加属性
    protected $append = [
        'addtime_text',
        'txtime_text',
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

    public function getTxtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['tx_time']) ? $data['tx_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }
    protected function setTxtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function users()
    {
        return $this->belongsTo('\app\admin\model\usersmanage\Users', 'user_id')->setEagerlyType(0);
    }


}
