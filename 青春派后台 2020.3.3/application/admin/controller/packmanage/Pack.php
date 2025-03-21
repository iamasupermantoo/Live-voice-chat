<?php

namespace app\admin\controller\packmanage;

use app\common\controller\Backend;
use think\Db;

/**
 * 背包
 *
 * @icon fa fa-circle-o
 */
class Pack extends Backend
{
    
    /**
     * Pack模型对象
     * @var \app\admin\model\packmanage\Pack
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\packmanage\Pack;
        //1宝石2=礼物3卡卷4头像框5气泡框6进场特效7麦上光圈8徽章
        $this->models = new \app\admin\model\Wares;
        $getTypeList  = $this->models->getTypeList('id,name');
        $getTypeList[2] = '礼物';
        
        $this->view->assign('typeList',$getTypeList);

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

            foreach ($list as $key => & $value) {
                # code...
                if ($value['type'] == 2){
                    $giftsMod = new \app\admin\model\giftsmanage\Gifts;
                    $value['val'] = $giftsMod->getNameById($value['target_id']);

                }else{
                    $waresMod = new \app\admin\model\Wares;
                    $value['val'] = $waresMod->getNameById($value['target_id']);
                }
                $value['get_type']=get_wares_allway($value['get_type']);
            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }

                    //查询背包里面是否已存在该礼物,存在数量加1 有效期延长
                    //$result = $this->model->allowField(true)->save($params);
                    //print_r($params);die;
                    $result = true;
                    userPackStoreInc($params['user_id'],$params['type'],$params['target_id'],$params['num'],5);
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
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
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
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    

    

}
