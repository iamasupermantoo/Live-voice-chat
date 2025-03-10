<?php

namespace app\admin\model\roomsmanage;

use think\Model;


class Musics extends Model
{

    

    

    // 表名
    protected $name = 'musics';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public function setEnable($enabled,$id){
        $enabled = $enabled == 1 ? 2 : 1;
        $ret = $this->where(['id' => $id])->update(['enable' => $enabled]);
        return $ret;
    }
    public function setDefault($default,$id){
        $default = $default == 1 ? 2 : 1;
        $ret = $this->where(['id' => $id])->update(['is_default' => $default]);
        return $ret;
    }
    

    







}
