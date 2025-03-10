<?php

namespace app\admin\controller\officialsmanage;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Onepage extends Backend
{
    
    /**
     * Onepage模型对象
     * @var \app\admin\model\officialsmanage\Onepage
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\officialsmanage\Onepage;
        $this->assign('onePateTypeList',$this->getOnePateTypeName());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => &$v) {
                $v['type_text']=$this->getOnePateTypeName($v['type']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    public function getOnePateTypeName($val=null){
        $arr=[
            1=>'星锐,金锐等级说明',
            2=>'vip等级说明',
            3=>'平台协议',
            4=>'大神协议',
            5=>'家族介绍',
        ];
        return $val ? $arr[$val] : $arr;
    }
    public function getOnePateJson(){
        $arr=$this->getOnePateTypeName();
        return json($arr);
    }
    /**
     * 详情
     */
    public function detail($ids)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row->toArray());
        return $this->view->fetch();
    }
    

}
