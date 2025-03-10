<?php

namespace app\admin\model\giftsmanage;

use think\Model;


class Giftsbag extends Model
{

    

    

    // 表名
    protected $name = 'gifts_bag';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;
    protected $updTime    = false;

    // 追加属性
    protected $append = [
        'addtime_text',
        'updtime_text',
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



    public function getUpdtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['updtime']) ? $data['updtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setUpdtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }






}
