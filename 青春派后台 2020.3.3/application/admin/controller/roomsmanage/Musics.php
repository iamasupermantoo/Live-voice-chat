<?php

namespace app\admin\controller\roomsmanage;

use app\common\controller\Backend;
use think\Db;

/**
 * 音乐库
 *
 * @icon fa fa-circle-o
 */
class Musics extends Backend
{
    
    /**
     * Musics模型对象
     * @var \app\admin\model\roomsmanage\Musics
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\roomsmanage\Musics;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 音乐开启  1=启用 2=关闭
     */
    public function change($ids = '')
    {
        $id = $this->request->request('id');
        $this->model->setEnable($ids,$id);
        $this->success("操作成功");
    }

    /**
     * 设为默认  1=默认 2=非默认
     */
    public function changeDefault($ids = '')
    {
        $id = $this->request->request('id');
        $this->model->setDefault($ids,$id);
        $this->success("操作成功");
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

                //$params['music_size'] = get_headers($params['music_url']);
                //获取文件大小
                $get_headers = get_headers($this->setFilePath($params['music_url']), true);
                $orisize = $get_headers['Content-Length'];
                $params['music_size'] = $this->getFileSize($orisize);
               
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
                //获取文件大小
                $get_headers = get_headers($this->setFilePath($params['music_url']), true);
                $orisize = $get_headers['Content-Length'];
                $params['music_size'] = $this->getFileSize($orisize);
                
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


     //补全文件路径
    public function setFilePath($file = null){
        if(!$file){
            return '';
        }
        $file = str_replace("\\","/",$file);
        /*************************************************/
        if(strstr($file,"http"))    return $file;
        if(strstr($file,"upload"))    return 'http://'.$_SERVER['HTTP_HOST'].$file;
        return "http://47.92.85.75/upload/".$file;
        /*************************************************/
        $pa = "/upload/".$file;
        if(file_exists(".".$pa))
        {
            $path="http://".$_SERVER['HTTP_HOST'].$pa;
            return $path;
        }else{
            return '';
        }
    }

    //获取文件大小
    protected function getFileSize($byte){
        $KB = 1024;
        $MB = 1024 * $KB;
        $GB = 1024 * $MB;
        $TB = 1024 * $GB;
        if ($byte < $KB) {
            return $byte . "B";
        } elseif ($byte < $MB) {
            return round($byte / $KB, 2) . "KB";
        } elseif ($byte < $GB) {
            return round($byte / $MB, 2) . "MB";
        } elseif ($byte < $TB) {
            return round($byte / $GB, 2) . "GB";
        } else {
            return round($byte / $TB, 2) . "TB";
        }
    }

}
