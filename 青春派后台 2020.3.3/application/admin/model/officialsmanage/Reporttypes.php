<?php

namespace app\admin\model\officialsmanage;

use think\Model;


class Reporttypes extends Model
{

    

    

    // 表名
    protected $name = 'report_types';
    
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
    

    







}
