<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\admin\model\officialsmanage\Onepage;

class Agree extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function index()
    {
        $content = Onepage::getAgreeContent(1);
        $this->assign('list',$content);
        return $this->view->fetch();
    }


}
