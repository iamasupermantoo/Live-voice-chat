<?php

namespace app\admin\model\packmanage;

use think\Model;


class Pointsexchange extends Model
{

    

    

    // 表名
    protected $name = 'points_exchange';
    
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

    public function users()
    {
        return $this->belongsTo('\app\admin\model\usersmanage\Users', 'user_id')->setEagerlyType(0);
    }

    public function wares()
    {
        return $this->belongsTo('\app\admin\model\Wares', 'wares_id')->setEagerlyType(0);
    }


}
