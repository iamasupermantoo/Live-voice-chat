<?php

namespace app\admin\controller\officialsmanage;

use app\common\controller\Backend;

/**
 * 官方信息管理
 *
 * @icon fa fa-circle-o
 */
class Officialmessages extends Backend
{
    
    /**
     * Officialmessages模型对象
     * @var \app\admin\model\officialsmanage\Officialmessages
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $msgList = array(
            array('id'=>1, 'name' =>'系统消息'),
            array('id'=>2, 'name' =>'系统公告'),
        );




        $this->model = new \app\admin\model\officialsmanage\Officialmessages;
        $this->view->assign("msgList", $msgList);
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
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
