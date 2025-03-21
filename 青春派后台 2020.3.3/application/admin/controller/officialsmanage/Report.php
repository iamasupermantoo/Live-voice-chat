<?php

namespace app\admin\controller\officialsmanage;

use app\common\controller\Backend;

/**
 * 举报记录
 *
 * @icon fa fa-circle-o
 */
class Report extends Backend
{
    
    /**
     * Report模型对象
     * @var \app\admin\model\officialsmanage\Report
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\officialsmanage\Report;
        $typeList = array(
            '2'=>'房间',
            '3'=>'动态',
        );
        $this->view->assign("typeList", $typeList);

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

            $wap = '1=1';
            if ($_REQUEST['filter'] == '{}'){

                $wap .= ' and report.type = 1';
            }
            $with = ['reporttypes','users','users2'];
            if ($_REQUEST['filter'] == '{"type":"3"}'){
                $with = ['reporttypes','users','dynamics'];
            }
            $total = $this->model
                ->with($with)
                ->where($where)
                ->where($wap)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with($with)
                ->where($where)
                ->where($wap)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
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
