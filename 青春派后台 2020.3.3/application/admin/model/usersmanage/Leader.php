<?php

namespace app\admin\model\usersmanage;

use think\Model;


class Leader extends Model
{

    

    

    // 表名
    protected $name = 'leader';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'addtime_text',
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

    public function users()
    {
        return $this->belongsTo('Users', 'user_id')->setEagerlyType(0);
    }
    public function users2()
    {
        return $this->belongsTo('Users', 'uid')->setEagerlyType(0);
    }


}
