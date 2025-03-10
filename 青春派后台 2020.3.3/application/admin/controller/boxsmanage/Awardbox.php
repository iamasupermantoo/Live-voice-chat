<?php

namespace app\admin\controller\boxsmanage;

use app\common\controller\Backend;
use think\Db;

/**
 * 宝箱管理
 *
 * @icon fa fa-circle-o
 */
class Awardbox extends Backend
{
    
    /**
     * Awardbox模型对象
     * @var \app\admin\model\boxsmanage\Awardbox
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\boxsmanage\Awardbox;

        $getTypeList = array(
            '1'=>'普通宝箱',
            '2'=>'守护宝箱',
        );
        $this->view->assign("typeList", $getTypeList);

        //1宝石2=礼物3卡卷4头像框5气泡框6进场特效7麦上光圈8徽章
        $this->models = new \app\admin\model\Wares;
        $getTypeLists  = $this->models->getTypeList();
        $getTypeLists[2] = '礼物';
        
        $this->view->assign('typeLists',$getTypeLists);

        $waresMod = new \app\admin\model\Wares;
        $targetList = $waresMod->getListByType();
        
        $giftsMod = new \app\admin\model\giftsmanage\Gifts;
        $getAllGifts = $giftsMod->getAllGifts('id,name');
        
        foreach ($getAllGifts as $key => & $value) {
            $value['type'] = 2;
        }
        
        $targetdatas = array_merge_recursive($targetList,$getAllGifts);
        $result = array();
        $result = $targetdatas;
        
        $this->view->assign("targetList", $result);

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

            foreach ($list as $key => & $value) {
                # code...
                if ($value['type'] == 2){
                    $giftsMod = new \app\admin\model\giftsmanage\Gifts;
                    $value['val'] = $giftsMod->getNameById($value['wares_id']);
                    $value['type'] = '礼物';
                }else{
                    $waresMod = new \app\admin\model\Wares;
                    $value['val'] = $waresMod->getNameById($value['wares_id']);
                    $getTypeList = $waresMod->getTypeList();
                    $value['type'] = $getTypeList[$value['type']];
                }

            }

            $nums = $this->model
                ->where($where)
                ->order($sort, $order)
                ->sum('num');

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list,"extend" => ['nums' => $nums]);

            return json($result);
        }
        return $this->view->fetch();
    }

    //生成奖池数据
    public function saveAwardData($ids){
        $row = $this->model->get(['id' => $ids]);
        if (!$row){
            $this->error("参数不存在");
        }

        if ($row['num'] <= 0){
            $this->error("参数错误");
        }

        /*if ($row['isUse'] >= 1){
            $this->error("该条记录奖池数据已生成");
        }*/

        //奖池里面10000条记录,查询已生成条数
        $count = Db::name('award')->where(array('class'=>$row['box_type']))->count();
        $needCount = (int)(50000 - $count);
        if ($needCount < 0){
            $this->error("奖池数据有误,请联系技术人员");
        }
        if ($needCount < $row['num']){
            $this->error("奖池所需数据为".$needCount."条，请修改出现次数");
        }

        $data = array();
        for($i = 1; $i <= $row['num']; $i++){
            $data[] = array(
                'num'         =>1,
                'status'      =>1,
                'addtime'     =>  time(),
                'wares_id'    => $row['wares_id'],
                'type'        => $row['type'],
                'class'       => $row['box_type'],
                'img'         => $row['img'],
                'award_box_id'=> $ids,
                'is_special'  => $row['is_special'],
                //'isUse'       => 1,
            );

        }

        db::name('award')->insertAll($data);

        $this->success("生成成功");
    }

}
