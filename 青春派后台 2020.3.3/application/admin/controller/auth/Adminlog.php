<?php

namespace app\admin\controller\auth;

use app\admin\model\AuthGroup;
use app\common\controller\Backend;

/**
 * 管理员日志
 *
 * @icon fa fa-users
 * @remark 管理员可以查看自己所拥有的权限的管理员日志
 */
class Adminlog extends Backend
{

    /**
     * @var \app\admin\model\AdminLog
     */
    protected $model = null;
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('AdminLog');

        $this->childrenAdminIds = $this->auth->getChildrenAdminIds(true);
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds($this->auth->isSuperAdmin() ? true : false);

        $groupName = AuthGroup::where('id', 'in', $this->childrenGroupIds)
                ->column('id,name');

        $this->view->assign('groupdata', $groupName);
    }

    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->where($where)
                    ->where('admin_id', 'in', $this->childrenAdminIds)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->where($where)
                    ->where('admin_id', 'in', $this->childrenAdminIds)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();
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
        $result = $row->toArray();
        //print_r($result);die;
        $url = $result['url'];
        $wap['url'] = array('eq',$url);
        $wap['id'] = array('lt',$ids);
        //$wap['title'] = array('like','%编辑%');

        $return  = '';
        $prevInfo = $this->model->field('content')->where($wap)->where("title like '%编辑%' or title like '%修改%'")->order('id asc')->limit(1)->find();
        //echo ($ids);die;
        //echo $this->model->getlastsql();die;
        if ($prevInfo){
            $content = json_decode($prevInfo['content'],true);
            if (isset($content['row'])){
                    $contentrow = $content['row'];
                    $newcontent = json_decode($result['content'],true);
                    $newcontentrow = $newcontent['row'];
                    
                    foreach ($contentrow as $key => $value) {
                        if($value != $newcontentrow[$key]){
                            $return .= $key.' : '.$value.'=》'.$newcontentrow[$key].'<br/>';
                        }
                    }    
                    
            }
        }
        
        $returns = $return ? $return : '暂无';
        $this->view->assign("prevInfo", $returns);
        $this->view->assign("row", $result);
        return $this->view->fetch();
    }

    /**
     * 添加
     * @internal
     */
    public function add()
    {
        $this->error();
    }

    /**
     * 编辑
     * @internal
     */
    public function edit($ids = NULL)
    {
        $this->error();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if ($ids)
        {
            $childrenGroupIds = $this->childrenGroupIds;
            $adminList = $this->model->where('id', 'in', $ids)->where('admin_id', 'in', function($query) use($childrenGroupIds) {
                        $query->name('auth_group_access')->field('uid');
                    })->select();
            if ($adminList)
            {
                $deleteIds = [];
                foreach ($adminList as $k => $v)
                {
                    $deleteIds[] = $v->id;
                }
                if ($deleteIds)
                {
                    $this->model->destroy($deleteIds);
                    $this->success();
                }
            }
        }
        $this->error();
    }

    /**
     * 批量更新
     * @internal
     */
    public function multi($ids = "")
    {
        // 管理员禁止批量操作
        $this->error();
    }
    
    public function selectpage()
    {
        return parent::selectpage();
    }

}
