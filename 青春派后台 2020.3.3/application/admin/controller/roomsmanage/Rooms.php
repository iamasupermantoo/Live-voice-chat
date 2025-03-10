<?php

namespace app\admin\controller\roomsmanage;

use app\common\controller\Backend;
use app\admin\model\roomsmanage\Roomcategories;
use think\Db;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Rooms extends Backend
{
    
    /**
     * Rooms模型对象
     * @var \app\admin\model\roomsmanage\Rooms
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\roomsmanage\Rooms;
        $catList = Roomcategories::getAllRoomCategories();
        $this->assign('catList',$catList);

        $result = Roomcategories::getSonRoomCategories();
        $this->view->assign("typeList", $result);



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
                ->with(["category","users","categorys"])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(["category","users","categorys"])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            foreach ($list as $k => &$v) {
                $v['room_name'] = urldecode($v['room_name']) ? : '';
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                //判断房间id是否已存在
                $numid = $params['numid'];
                $wap['numid'] = array('eq',$numid);
                $wap['id']    = array('neq',$ids);
                $roomInfo = Db::name('rooms')->field('id')->where($wap)->find();
                if (!empty($roomInfo)){
                    $this->error(__('房间id已存在,请换一个试试~'));
                }

                if (strlen($numid) < 5 || strlen($numid) > 7){
                    $this->error(__('房间id必须大于5位数小于7位数~'));
                }
                
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $row['room_name'] = urldecode($row['room_name']);
        $this->view->assign("row", $row);
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
     * Selectpage搜索
     *
     * @internal
     */
    public function selectpage()
    {
        return parent::selectpage();
    }
    

}
