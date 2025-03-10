<?php

namespace app\admin\model;

use think\Model;


class Wares extends Model
{

    

    

    // 表名
    protected $name = 'wares';
    
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

    public function getTypeList()
    {
        return ['1' => __('宝石'), '3' => __('卡卷'), '4' => __('头像框'), '5' => __('气泡框'), '6' => __('进场特效'), '7' => __('麦上光圈'),'8' => __('徽章')];
    }

    public function setEnable($enabled,$id){
        $enabled = $enabled == 1 ? 2 : 1;
        $ret = $this->where(['id' => $id])->update(['enable' => $enabled]);
        return $ret;
    }

    public static function  getShowimgById($id){
        $obj = new self();
        $show_img = $obj::where(['id' => $id])->value('show_img');
        if (!$show_img){
            return '';
        }
        
        $getImg = getImg($show_img);
        return getImg($show_img);
    }

    public function getListByType($field='*'){
        $obj = new self();
        $getListByType = $obj->field($field)->where(array('enable'=>1))->select();
        return $getListByType;
    }

    public function getNameById($id){
        $ret = $this->where(['id'=>$id])->value('name');
        return $ret;
    }


}
