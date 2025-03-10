<?php

namespace app\admin\controller\usersmanage;

use app\common\controller\Backend;

/**
 * 点赞,收藏,转发,评论动态记录
 *
 * @icon fa fa-circle-o
 */
class Likes extends Backend
{
    
    /**
     * Likes模型对象
     * @var \app\admin\model\usersmanage\Likes
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        //1点赞动态2收藏动态3转发动态4点赞评论
        $typeList = array(
            //'1'=>'点赞动态',
            '2'=>'收藏动态',
            '3'=>'转发动态',
            '4'=>'点赞评论',
        );
        $this->model = new \app\admin\model\usersmanage\Likes;
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

                $wap .= ' and likes.type = 1';
            }
            $with = ['users','dynamics'];
            $content = 'dynamics';
            if ($_REQUEST['filter'] == '{"type":"4"}'){
                $with = ['users','dynamiccomments'];
                $content = 'dynamiccomments';
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
            //print_r($list);die;
            //echo $this->model->getlastsql();die;
            foreach ($list as $key => &$v) {
                $v[$content]['content'] = urldecode($v[$content]['content']);
                //echo $v['content'];die;
                // if(mb_strlen($v['content'], 'utf8') > 30) {
                //     $v['content'] = mb_substr($v['content'], 0, 30, 'utf8').'...';
                // }
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
    

}
