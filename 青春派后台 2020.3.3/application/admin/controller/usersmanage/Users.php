<?php

namespace app\admin\controller\usersmanage;

use app\common\controller\Backend;
use think\Db;
use app\admin\model\usersmanage\Storelog;
use think\Config;
use app\admin\model\Wares;

/**
 * 用户管理
 *
 * @icon fa fa-users
 */
class Users extends Backend
{
    
    /**
     * Users模型对象
     * @var \app\admin\model\usersmanage\Users
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\usersmanage\Users;

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
        //print_r($_GET);die;
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
            
            $list = collection($list)->toArray();

            $wap['isOnline'] = 0;
            $personnum = $this->model
                ->where($where)
                ->where($wap)
                ->order($sort, $order)
                ->count();

            $result = array("total" => $total, "rows" => $list,"extend" => ['personnum' => $personnum]);

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

                //手机号码是否存在
                $ifPhone = $this->model->getUserinfoByPhone($params['phone'],'id');
                if ($ifPhone){
                    $this->error(__('手机号码已存在'));
                }

                //处理省市区(chufang)
                if ($params['city']){
                    $cityArr = explode('/',$params['city']);
                    //print_r($cityArr);die();
                    $params['province'] = isset($cityArr[0]) ? $cityArr[0] : '';
                    $params['city']     = isset($cityArr[1]) ? $cityArr[1] : '';
                    $params['district'] = isset($cityArr[2]) ? $cityArr[2] : '';
                }
                //处理密码(chufang)
                $getEncyPwd = $this->model->getEncyPwd($params['pass']);
                $params['pass'] = $getEncyPwd['user_pwd'];
                $params['salt'] = $getEncyPwd['salt'];
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
            //print_r($params);die;
            if ($params) {
                $params = $this->preExcludeFields($params);

                //处理省市区(chufang)
                if ($params['city']){
                    $cityArr = explode('/',$params['city']);
                    //print_r($cityArr);die();
                    $params['province'] = isset($cityArr[0]) ? $cityArr[0] : '';
                    $params['city']     = isset($cityArr[1]) ? $cityArr[1] : '';
                    $params['district'] = isset($cityArr[2]) ? $cityArr[2] : '';
                }else{
                    unset($params['city']);
                }
                //处理密码(chufang)
                if ($params['pass']){
                    $getEncyPwd = $this->model->getEncyPwd($params['pass']);
                    $params['pass'] = $getEncyPwd['user_pwd'];
                    $params['salt'] = $getEncyPwd['salt'];
                }

                if (empty($params['pass'])){
                    unset($params['pass']);
                }

                //处理分成比例(lzw)
                if($params['is_sign'] == 1){
                    if($params['scale']<0 || $params['scale']>5){
                        $this->error('分成比例设置有误!');
                    }
                }else{
                    unset($params['scale']);
                }

                //禁用会员，前台强制学员下线
                if ($params['status'] == 2){
                    if (!$params['locktime']){
                        $this->error('请设置解除锁定时间!');
                    }
                    //print_r($row['id']);die;
                    $return = http(Config::get('API_URL').'api/adminLogin?user_id='.$row['id'],'POST');

                    if ($return[0] == '200') {
                        /*$returnArr = json_decode($return[1],true);
                        if ($returnArr['code'] != 1){
                            $this->error(__($returnArr['message']));
                        }*/
                        //$this->success('发送成功,请联系超级管理员获取');
                    } else {
                        //$this->error(__('发送失败'));
                    }
                    //adminLogin
                }else if($params['status'] == 1){
                    $params['locktime'] = '';
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
        
        $this->view->assign("row", $row);
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

    /*
     * 修改余额
     */
    public function updmoney($ids)
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

                $db_mizuan   = $row['mizuan'];
                $db_mibi     = $row['mibi'];
                $db_r_mibi   = $row['r_mibi'];

                $mizuan   = $params['mizuan'];
                $mibi      = $params['mibi'];
                $r_mibi    = $params['r_mibi'];

                Storelog::updmoney(1,$db_mizuan,$mizuan,$ids);
                Storelog::updmoney(2,$db_mibi,$mibi,$ids);
                Storelog::updmoney(3,$db_r_mibi,$r_mibi,$ids);


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
    
    //用户市场来源
    public function getChannelList(){
        $data=getChannelArr();
        return json($data);
    }

    //用户系统来源
    public function getSystemList(){
        $data=getSystemArr();
        return json($data);
    }

    /*
     * 赠送钥匙
     */
    public function sendkeys($ids)
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
                /*Db::name('users')->where('id',$ids)->setInc('keys_num', $params['keys_num']);
                userStoreDec($ids,$params['keys_num'],16,'mizuan');*/

               /* $db_mizuan   = $row['mizuan'];
                $db_mibi     = $row['mibi'];
                $db_r_mibi   = $row['r_mibi'];

                $mizuan   = $params['mizuan'];
                $mibi      = $params['mibi'];
                $r_mibi    = $params['r_mibi'];

                Storelog::updmoney(1,$db_mizuan,$mizuan,$ids);
                Storelog::updmoney(2,$db_mibi,$mibi,$ids);
                Storelog::updmoney(3,$db_r_mibi,$r_mibi,$ids);*/


                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    addKeysNum($ids,$params['keys_num'],1,session('admin.nickname'));
                    $result = $row->where('id',$ids)->setInc('keys_num', $params['keys_num']);
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
