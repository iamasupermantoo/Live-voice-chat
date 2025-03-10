<?php

namespace app\admin\controller\usersmanage;

use app\common\controller\Backend;

/**
 * 钱包变动记录
 *
 * @icon fa fa-circle-o
 */
class Storelog extends Backend
{
    
    /**
     * Storelog模型对象
     * @var \app\admin\model\usersmanage\Storelog
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\usersmanage\Storelog;

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
                ->with(['users'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['users'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach($list as $key=>&$val){
                $val['get_type'] = $this->model->getTranmoneyType($val['get_type']);
            }

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

    public function get_types(){
        $result = $this->model->getTranmoneyTypes();
        foreach ($result as $key => $value) {
            $results[$key] = $value;
        }
        return json($results);
    }

}
