<?php

namespace app\admin\model\usersmanage;

use think\Model;


class Follows extends Model
{

    

    

    // 表名
    protected $name = 'follows';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public function users()
    {
        return $this->belongsTo('Users', 'user_id')->setEagerlyType(0);
    }
    public function users2()
    {
        return $this->belongsTo('Users', 'followed_user_id')->setEagerlyType(0);
    }
    

    







}
