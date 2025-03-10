<?php

namespace app\admin\controller\unitymanage;

use app\common\controller\Backend;
use think\Db;
use app\admin\model\unitymanage\Labels;
use think\Config;

/**
 * 用户动态管理
 *
 * @icon fa fa-circle-o
 */
class Dynamics extends Backend
{
    
    /**
     * Dynamics模型对象
     * @var \app\admin\model\unitymanage\Dynamics
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\unitymanage\Dynamics;

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

            $with = ['users'];

            $total = $this->model
                ->with($with)
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with($with)
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $k => &$v){
                $image = $v['image'];

                $imageArr = $image ? json_decode($image,true) : [];
                $v['image'] = implode(',',$imageArr);

                //标签处理
                $v['tags'] = Labels::getLabelsNameById($v['tags']);
              	
                //lzw
                $v['content']=urldecode($v['content']);
                if(mb_strlen($v['content'], 'utf8') > 30) {
                    $v['content'] = mb_substr($v['content'], 0, 30, 'utf8').'...';
                }
              	/*if(strlen($v['content']) > 11)
                    $v['content']=mb_substr_replace($v['content'],'...',11);*/
            }

            $list = collection($list)->toArray();//print_r($list);die;
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
                if($params['image']){
                    $params['image'] = explode(',',$params['image']);
                    $params['image'] = json_encode($params['image']);

                }else{
                    $params['image'] = '[]';
                }
              	$params['addtime']=time();
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
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
                if($params['image']){
                    $params['image'] = explode(',',$params['image']);
                    $params['image'] = json_encode($params['image']);
                }else{
                    $params['image'] = '[]';
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
        if ( $row['image']){
            $row['image'] = json_decode($row['image'],true);
            $image = $row['image'];
            foreach($image as $k => &$v){
                $v = getImg($v);
            }

            $row['image'] = implode(',',$image);
        }
        $row['content']=urldecode($row['content']);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
    /**
     * 详情
     */
    public function detail($ids)
    {
        $row = $this->model->get(['id' => $ids]);
        $row['content']=urldecode($row['content']);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row->toArray());
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {

        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();
            if ($list){
                $id=$ids;
                $data=DB::name('dynamics')->where(['id'=>$id])->find();
                deleteFile($data['audio']);
                deleteFile($data['video']);
                $img_arr=json_decode($data['image']);
                array_map(function($val){
                    deleteFile($val);
                }, $img_arr);
                $res=DB::name('dynamics')->where('id',$data['id'])->delete();
                if($res){
                    $pinglun=DB::name('dynamic_comments')->where(['b_dynamic_id'=>$id])->select();
                    foreach ($pinglun as $k => &$v) {
                        DB::name('likes')->where(['type'=>4,'target_id'=>$v['id']])->delete();
                        DB::name('dynamic_comments')->where(['id'=>$v['id']])->delete();
                    }
                    DB::name('likes')->where(['type'=>['in','1,2,3'],'target_id'=>$id])->delete();
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }


    
    

}
