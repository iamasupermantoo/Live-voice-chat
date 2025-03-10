<?php

namespace app\admin\model\giftsmanage;

use think\Model;


class Giftlogs extends Model
{

    

    

    // 表名
    protected $name = 'gift_logs';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    public function gifts(){
        return $this->belongsTo('Gifts', 'giftId')->setEagerlyType(0);
    }

    public function users()
    {
        return $this->belongsTo('\app\admin\model\usersmanage\Users', 'uid')->setEagerlyType(0);
    }

    public function users2()
    {
        return $this->belongsTo('\app\admin\model\usersmanage\Users', 'user_id')->setEagerlyType(0);
    }
    public function users3()
    {
        return $this->belongsTo('\app\admin\model\usersmanage\Users', 'fromUid')->setEagerlyType(0);
    }


    

    







}
