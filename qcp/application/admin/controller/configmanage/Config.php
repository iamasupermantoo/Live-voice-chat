<?php

namespace app\admin\controller\configmanage;

use app\common\controller\Backend;

/**
 * 配置管理
 *
 * @icon fa fa-circle-o
 */
class Config extends Backend
{
    
    /**
     * Config模型对象
     * @var \app\admin\model\configmanage\Config
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\configmanage\Config;

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
                if(strlen($v['value']) > 30)
                    $v['value']=substr_replace($v['value'],'...',30);
                    $v['value'] = mb_convert_encoding( $v['value'], 'UTF-8', 'UTF-8,GBK,GB2312,BIG5' );
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
    

}
