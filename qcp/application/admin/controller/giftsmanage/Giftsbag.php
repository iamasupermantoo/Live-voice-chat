<?php

namespace app\admin\controller\giftsmanage;

use app\common\controller\Backend;
use think\Db;

/**
 * 礼物包
 *
 * @icon fa fa-circle-o
 */
class Giftsbag extends Backend
{
    
    /**
     * Giftsbag模型对象
     * @var \app\admin\model\giftsmanage\Giftsbag
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\giftsmanage\Giftsbag;

    }

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

            foreach($list as $key=>&$val){
                $gift_ids = $val['gift_ids'];
                $wap['id'] = array('in',$gift_ids);
                $giftList = Db::name('gifts')->field('GROUP_CONCAT(name ORDER BY id) as gift_names')->where($wap)->find();
                $val['gift_names'] = $giftList['gift_names'];
            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    
    function user_list($ids){
        $row = $this->model->get(['id' => $ids]);
        $gift_ids = $row['gift_ids'];
        if ($this->request->isAjax()) {
            $gift_ids_arr = explode(',', $gift_ids);

            $field = 'fromUid,giftId,GROUP_CONCAT(DISTINCT giftId order by giftId) as giftids,nickname';
            $where = '1=1';
            $userinfo = Db::name('gift_logs')
                ->alias('t1')
                ->join('users t2','t1.fromUid = t2.id','inner')
                ->field($field)
                ->where($where)
                ->group('fromUid')
                ->select();

            $result = array();
            foreach($userinfo as $key=>$val){
                $giftidsArr = explode(',', $val['giftids']);
                $array_intersect = array_intersect($gift_ids_arr,$giftidsArr);
                if (count($array_intersect) == count($gift_ids_arr)){
                    $result[$key] = $userinfo[$key];
                }
            }

            $row = array_values($result);
            $results = array("rows" => $row);

            return json($results);
        }

        //print_r($row);die;
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    

}
