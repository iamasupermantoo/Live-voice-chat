<?php

namespace app\admin\controller\usersmanage;

use app\common\controller\Backend;

/**
 * 星锐,金锐,vip等级管理
 *
 * @icon fa fa-circle-o
 */
class Vip extends Backend
{
    
    /**
     * Vip模型对象
     * @var \app\admin\model\usersmanage\Vip
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        // $typeList = array(
        //     array('id'=>1, 'name' =>'星锐'),
        //     array('id'=>2, 'name' =>'金锐'),
        //     array('id'=>3, 'name' =>'vip'),
        // );
        $typeList=getVipType();
        $this->model = new \app\admin\model\usersmanage\Vip;

        $this->view->assign("typeList", $typeList);
        $this->view->assign("typeLists", json_encode($typeList));
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
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
        $type = $this->request->request("type");

        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $wap = '1 = 1';
            if ($type){
                $wap .= $type == 'all' ? '' : " and type = '$type'";
            }

            $total = $this->model
                ->where($where)
                ->where($wap)
                ->count();

            $list = $this->model
                ->where($where)
                ->where($wap)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => &$v) {
                $v['type']= getVipType($v['type']);
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

    /**
     * 选择附件
     */
    public function select()
    {
        return $this->view->fetch();
    }

    public function vip_type(){
        $data=getVipType();
        return json($data);
    }


}
