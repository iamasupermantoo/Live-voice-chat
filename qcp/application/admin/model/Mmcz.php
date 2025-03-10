<?php

namespace app\admin\model;

use think\Model;


class Mmcz extends Model
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


}
