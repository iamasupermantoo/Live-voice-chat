<?php

namespace app\admin\controller\roomsmanage;

use app\common\controller\Backend;
use fast\Tree;

/**
 * 动态表情管理
 *
 * @icon fa fa-circle-o
 */
class Emoji extends Backend
{
    
    /**
     * Emoji模型对象
     * @var \app\admin\model\roomsmanage\Emoji
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\roomsmanage\Emoji;

        $emojiList = collection($this->model->where(array('pid'=>0))->select())->toArray();
        Tree::instance()->init($emojiList);
        $result = [];
        $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));

        $emojiemojiName = [];
        foreach ($result as $k => $v)
        {
            $emojiName[$v['id']] = $v['name'];
        }

        $this->emojidata = $emojiName;
        $emojidatas = $this->emojidata;
        array_unshift($emojidatas,'==请选择==');
        $this->view->assign('emojidata', $emojidatas);

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
                //->order($sort, $order)
              	->order('pid asc')
                ->count();

            $list = $this->model
                ->where($where)
                //->order($sort, $order)
              	->order('pid asc')
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();

            foreach($list as $k=>&$v){
                $v['pname'] = $this->model->getNameById($v['pid']);
            }


            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 动态表情   1=启用 2=禁用
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
