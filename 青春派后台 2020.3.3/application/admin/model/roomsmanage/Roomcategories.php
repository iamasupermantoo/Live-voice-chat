<?php

namespace app\admin\model\roomsmanage;

use think\Model;


class Roomcategories extends Model
{

    

    

    // 表名
    protected $name = 'room_categories';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    //获取房间类型
    public static function getAllRoomCategories(){
        $condition['pid'] = array('eq',0);
        $obj = new self();
        $result = $obj->field('id,name,pid')->where($condition)->select();
        return collection($result)->toArray();
    }

    //获取房间子类型
    public static function getSonRoomCategories(){
        $condition['pid'] = array('neq',0);
        $obj = new self();
        $result = $obj->field('id,name,pid')->where($condition)->select();
        return collection($result)->toArray();
    }
    
    function setEnable($enabled,$id){
        $enabled = $enabled == 1 ? 2 : 1;
        $ret = $this->where(['id' => $id])->update(['enable' => $enabled]);
        return $ret;
    }

    







}
