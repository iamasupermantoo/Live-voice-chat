<?php

namespace app\admin\controller\giftsmanage;

use app\common\controller\Backend;
use think\Db;

/**
 * 发送礼物记录
 *
 * @icon fa fa-circle-o
 */
class Giftlogs extends Backend
{
    
    /**
     * Giftlogs模型对象
     * @var \app\admin\model\giftsmanage\Giftlogs
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\giftsmanage\Giftlogs;

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


            //兼容厅主登陆
            $wheres = '1=1';
            $usernames = $_SESSION['think']['admin']['username'];
            $isShow = 0;
            if ($usernames){
               //print_R($usernames);die;
                $usernamesArr = explode('_', $usernames);
                if(in_array('mini',$usernamesArr)){
                    $info = Db::name('users')->where(array('id'=>$usernamesArr[1]))->find();
                   
                    if ($info){
                        $username = $usernamesArr[1];
                        $wheres .= " and uid = '$username'";
                        $isShow = 1;
                    }
                }     
            }
          
            $total = $this->model
                ->with(['gifts','users','users2','users3'])
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['gifts','users','users2','users3'])
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
          
           $money = $this->model
                ->with(['gifts','users','users2','users3'])
                ->where($where)
                ->where($wheres)
                ->order($sort, $order)
                ->sum('giftPrice');

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list,"extend" => ['money' => $money,'isShow'=>$isShow]);

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
