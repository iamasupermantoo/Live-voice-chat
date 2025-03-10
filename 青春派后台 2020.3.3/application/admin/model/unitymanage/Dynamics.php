<?php

namespace app\admin\model\unitymanage;

use think\Model;


class Dynamics extends Model
{

    

    

    // 表名
    protected $name = 'dynamics';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'audio_time_text',
        'addtime_text'
    ];
    

    



    public function getAudioTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['audio_time']) ? $data['audio_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getAddtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['addtime']) ? $data['addtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAudioTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setAddtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function users()
    {
        return $this->belongsTo('\app\admin\model\usersmanage\Users', 'user_id')->setEagerlyType(0);
    }


}
