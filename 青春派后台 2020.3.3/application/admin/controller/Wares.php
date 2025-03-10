<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\controller\Api;

/**
 * 物品
 *
 * @icon fa fa-circle-o
 */
class Wares extends Backend
{
    
    /**
     * Wares模型对象
     * @var \app\admin\model\Wares
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Wares;

        $getTypeList  = $this->model->getTypeList();

        $wareswayList = get_wares_allway();

        $this->view->assign("wareswayList", $wareswayList);
        $this->view->assign("typeList", $getTypeList);

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
            //print_R($_REQUEST);die;
            $wheres = '1=1';
            if (isset($_GET['type']) && $_GET['type'] == 8){
                $wheres .= ' and type = 8';
            }
            $total = $this->model
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $key => &$value) {
                $value['get_type'] = get_wares_allway($value['get_type']);
            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
    
    /**
     * 是否启用   1=启用 2=禁用
     */
    public function change($ids = '')
    {
        $id = $this->request->request('id');
        $this->model->setEnable($ids,$id);
        $this->success("操作成功");
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

    /**
     * 选择附件
     */
    public function select()
    {
       
        return $this->view->fetch();
    }
    
    function wares_get_way(){
        $wareswayList = get_wares_allway();
        foreach ($wareswayList as $key => $value) {
            $result[$key] = $value;
        }
        return json($result);

    }


    public function getTypeList(){
        $waresMod = new \app\admin\model\Wares;
        $targetList = $waresMod->getTypeList();
        $targetList[2] = '礼物';
        foreach ($targetList as $key => $value) {
            $result[$key] = $value;
        }
        return json($result);
    }
    

}
