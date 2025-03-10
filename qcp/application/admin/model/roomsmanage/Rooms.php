<?php

namespace app\admin\model\roomsmanage;

use think\Model;


class Rooms extends Model
{

    

    

    // 表名
    protected $name = 'rooms';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'freshTime_text'
    ];
    

    



    public function getFreshtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['freshTime']) ? $data['freshTime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setFreshtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function category()
    {
        return $this->belongsTo('Roomcategories', 'room_type','id')->setEagerlyType(0);
    }

    public function users()
    {
        return $this->belongsTo('\app\admin\model\usersmanage\Users', 'uid')->setEagerlyType(0);
    }

    public function categorys()
    {
        return $this->belongsTo('Roomcategories', 'room_class','id')->setEagerlyType(0);
    }



}
