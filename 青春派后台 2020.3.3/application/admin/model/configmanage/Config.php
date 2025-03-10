<?php

namespace app\admin\model\configmanage;

use think\Model;


class Config extends Model
{

    

    

    // 表名
    protected $name = 'config';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'ctime_text'
    ];
    

    



    public function getCtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['ctime']) ? $data['ctime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function setEnable($enabled,$id){
        $enabled = $enabled == 1 ? 2 : 1;
        $ret = $this->where(['id' => $id])->update(['status' => $enabled]);
        return $ret;
    }

    public static function getConfigByName($name){
        $obj = new self();
        $res = $obj::field('value')->where(['name'=>$name])->find();
        return $res['value'];
    }


}
