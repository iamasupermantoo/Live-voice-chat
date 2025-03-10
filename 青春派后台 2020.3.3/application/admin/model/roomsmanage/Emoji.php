<?php

namespace app\admin\model\roomsmanage;

use think\Model;


class Emoji extends Model
{

    

    

    // 表名
    protected $name = 'emoji';
    
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

    public function getNameById($id){
        $obj = new self();
        $condition = [
            'id' => $id,
        ];
        $list = $obj->where($condition)->field('name')->find();
        return $list['name'];
    }

    function setEnable($enabled,$id){
        $enabled = $enabled == 1 ? 2 : 1;
        $ret = $this->where(['id' => $id])->update(['enable' => $enabled]);
        return $ret;
    }

}
