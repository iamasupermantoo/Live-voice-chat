<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 会员接口
 */
class Error extends Backend
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';

    //空方法
    public function _empty(){
        error_page();
    }


}
