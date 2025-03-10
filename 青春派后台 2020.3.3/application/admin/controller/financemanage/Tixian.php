<?php

namespace app\admin\controller\financemanage;

use app\admin\controller\Ajax;
use app\common\controller\Backend;
use think\Config;
use think\Db;
use app\admin\model\Admin;
use app\admin\model\Code;

/**
 * 提现管理
 *
 * @icon fa fa-circle-o
 */
class Tixian extends Backend
{
    
    /**
     * Tixian模型对象
     * @var \app\admin\model\financemanage\Tixian
     */
    protected $model = null;
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $typeList = array(
            '1'=>'新申请',
            '2'=>'已提现',
            '3'=>'取消',
        );
        $this->model = new \app\admin\model\financemanage\Tixian;
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
            $list = collection($list)->toArray();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        $phone = Admin::getAdminPhone();
        $aid   = session('admin.id');
        //根据手机号和自己的id去code表里面查询是否有有效的验证码，如果有不许哦发送短信了，没有发送短信
        $code = Code::getCodeByPhone($phone,$aid);
        $this->view->assign("code", $code);
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
     * 编辑
     */
    public function edit($ids = null)
    {

        $row = $this->model->get($ids);
        $phone = Admin::getAdminPhone();
        $aid   = session('admin.id');
        //根据手机号和自己的id去code表里面查询是否有有效的验证码，如果有不许哦发送短信了，没有发送短信
        $code = Code::getCodeByPhone($phone,$aid);

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
                if (empty($params['status'])){
                    $this->error('请选择提现状态');
                }

                //提现验证码（chufang 0807）
                $tixian_code = @$params['tixian_code'];//echo $tixian_code;die;
                unset($params['tixian_code']);
                if (!$tixian_code){
                    $this->error('请输入提现验证码！');
                }


                if(!$code){
                    $this->error('验证码不存在或已失效,请重新发送！');
                }else if ($code != $tixian_code){
                    $this->error('验证码输入错误！');
                }


                //调用提现接口（chufang）
                $return = http(Config::get('API_URL').'api/ali_tixian?tx_id='.$ids,'POST');

                if ($return[0] == '200') {
                    $returnArr = json_decode($return[1],true);
                    if ($returnArr['code'] != 1){
                        $this->error(__($returnArr['message']));
                    }
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }

                /*$result = false;
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
                }*/
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $row['tixian_code'] = $code;
        $this->view->assign("row", $row);
        $user_id = $row['user_id'];
        //print_r($row);die;
        //交易记录
        $list = db::name('store_log')
            ->where(array('user_id'=>$user_id))
          	->where('get_type','in',[21,22,23,24,31,32,33,34,35])
            ->order('addtime desc')
            ->select();
        $models = new \app\admin\model\usersmanage\Storelog;
        foreach($list as $key=>&$val){
            $val['get_type'] = $models->getTranmoneyType($val['get_type']);
            $val['addtime'] = date('Y-m-d H:i:s',$val['addtime']);
        }
        $this->view->assign("list", $list);
        $list = collection($list)->toArray();



        return $this->view->fetch();
    }

    /**
     * 获取短信验证码
     */
    public function getCode(){
        $phone = Admin::getAdminPhone();
        //$phone = '13569340564';
        if (!$phone){
            $this->error(__('请联系超级管理员设置手机号'));
        }

        $aid   = session('admin.id');
        if (!$aid){
            $this->error(__('请重新登陆系统'));
        }
        $return = http(Config::get('API_URL').'api/verification?phone='.$phone.'&aid='.$aid.'','POST');

        if ($return[0] == '200') {
            $returnArr = json_decode($return[1],true);
            if ($returnArr['code'] != 1){
                $this->error(__($returnArr['message']));
            }
            $this->success('发送成功,请联系超级管理员获取');
        } else {
            $this->error(__('发送失败'));
        }
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

    /**
     * 批量修改
     */
    public function batch(){
        return $this->view->fetch();
    }

    /**
     * 批量修改保存
     */
    public function batch_do(){
        $params = $this->request->request();
        if (!$params){
            $this->error('参数错误');
        }

        $tixian_code = $params['tixian_code'];
        $ids = $params['ids'];
        $ids = rtrim($ids, ',');
        //字符串转数组
        $idsArr = explode(',',$ids);

        $phone = Admin::getAdminPhone();
        $aid   = session('admin.id');
        //根据手机号和自己的id去code表里面查询是否有有效的验证码，如果有不许哦发送短信了，没有发送短信
        $code = Code::getCodeByPhone($phone,$aid);
        if(!$code){
            $this->error('验证码不存在或已失效,请重新发送！');
        }else if ($code != $tixian_code){
            $this->error('验证码输入错误！');
        }

        $suc = '';
        $err = '';
        foreach($idsArr as $key=>&$val){
            //调用提现接口（chufang）
            $return = http(Config::get('API_URL').'api/ali_tixian?txt_id='.$val,'POST');
            //print_r($return);die;
            if ($return[0] == '200') {
                //db::name('tixian')->where(array('id'=>$val))->update(array('status'=>2));
                $returnArr = json_decode($return[1],true);
                if ($returnArr['code'] != 1){
                    $err .= $val.',';
                }else{
                    $suc .= $val.',';
                }

            } else {
                $err .= $val.',';
            }
        }


        $suc = $suc ? '提现成功：'.rtrim($suc,',') : '';
        $err = $err ? '提现失败：'.rtrim($err,',') : '';

        $this->success($suc.'<br/>'.$err);
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        //print_r($ids);die;
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $count += $v->delete();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

}
