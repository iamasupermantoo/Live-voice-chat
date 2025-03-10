<?php

namespace app\admin\model;

use think\Model;

class Code extends Model
{
    // 表名
    protected $name = 'code';

    /**
     * 获得超级管理员信息
     * @author chufang
     */
    public static function getCodeByPhone($phone,$aid)
    {
        $obj = new self();
        $ret = $obj::where(['phone' => $phone,'aid'=>$aid])->field('code,addtime')->find();

        $time = time();
        if (!$ret){
            return '';
        }
        if (($time - $ret['addtime']) > 7200){
            return '';
        }

        return $ret['code'];

    }
    
    


}
