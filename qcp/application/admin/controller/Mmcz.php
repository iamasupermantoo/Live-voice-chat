<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use Think\Db;
/**
 * 用户
 *
 * @icon fa fa-users
 */
class Mmcz extends Backend
{
    
       
      public function edit($ids = null)
    {
    	// $ids 用户id
    	// $adminid 分销商id
    	// $mizuan  金额
    	
         $row = $this->model->get($ids);
         $this->view->assign("row", $row);
         $params = $this->request->post("row/a");
        	
        	
        $num=$params['mizuan'];//充值数量
        $adminid=$params['adminid'];
        if($adminid == 1){
        	  $this->error(__("系统限制admin账户,不可作为分销商充值"));die;
        }
        //查询分销商的余额是否够
        $iszg=Db::name("admin")->where("id='{$adminid}'")->find();
        
        $syye=$iszg['mzye'];
        if($num > $syye){
        	  $this->error(__("余额不足,剩余可充值余额为{$syye}"));
        }else{
        	
        	$dt=array(
        		"mizuan"=>$num
        		);
        	$saveus=Db::name("users")->where("id='{$ids}'")->setInc("mizuan",$num);
        	$usinfo=Db::name("users")->where("id='{$ids}'")->find();
        	$saveadmin=Db::name("admin")->where("id='{$adminid}'")->setDec("mzye",$num);
        	if($saveus and $saveadmin){
        		$ad=array(
        			"userid"=>$ids,
        			"adminid"=>$adminid,
        			"cnt"=>'分销商'.$iszg['username'].'给用户'.$usinfo['nickname'].'充值米钻'.$num.'个',
        			'username'=>$usinfo['nickname'],
        			'adminname'=>$iszg['username']
        			);
        			$add=Db::name("fxsczjl")->data($ad)->insert();
        		 $this->success(__("充值成功,剩余可充值余额为{$syye}"));
        		
        	}
        	
        }
        
        
        
        
        return $this->view->fetch();
        
    }


    /*
      
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
*/

	
		    /**
     * Mmcz模型对象
     * @var \app\admin\model\Mmcz
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Mmcz;

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
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
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

            foreach ($list as $row) {
                $row->visible(['id','nickname','mizuan']);
                
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}
