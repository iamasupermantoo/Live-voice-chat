<?php

namespace app\admin\model\unitymanage;

use think\Model;


class Dynamiccomments extends Model
{

    

    

    // 表名
    protected $name = 'dynamic_comments';
    
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
        return $this->belongsTo('\app\admin\model\usersmanage\Users', 'user_id','','','left')->setEagerlyType(0);
    }

    public function users2()
    {
        return $this->belongsTo('\app\admin\model\usersmanage\Users', 'hf_uid','','','left')->setEagerlyType(0);
    }

    public function dynamics()
    {
        return $this->belongsTo('Dynamics', 'b_dynamic_id','','','left')->setEagerlyType(0);
    }

    public function dynamics2()
    {
        return $this->belongsTo('Dynamics', 'pid','','','left')->setEagerlyType(0);
    }
    

    







}
