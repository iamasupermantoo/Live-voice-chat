<?php

namespace app\admin\controller\boxsmanage;

use app\common\controller\Backend;

/**
 * 中奖记录管理
 *
 * @icon fa fa-circle-o
 */
class Awardlog extends Backend
{
    
    /**
     * Awardlog模型对象
     * @var \app\admin\model\boxsmanage\Awardlog
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\boxsmanage\Awardlog;

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
			$sum = $this->model
                ->with(['users'])
                ->where($where)
                ->order($sort, $order)
                ->sum('num');
          
          	
            foreach ($list as $key => & $value) {
                # code...
                if ($value['type'] == 2){
                    $giftsMod = new \app\admin\model\giftsmanage\Gifts;
                    $value['val'] = $giftsMod->getNameById($value['wares_id']);
                    $value['type_name'] = '礼物';
                }else{
                    $waresMod = new \app\admin\model\Wares;
                    $value['val'] = $waresMod->getNameById($value['wares_id']);
                    $getTypeList = $waresMod->getTypeList();
                    $value['type_name'] = $getTypeList[$value['type']];
                }
            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list ,"sum" => $sum);

            return json($result);
        }
        return $this->view->fetch();
    }
    

}
