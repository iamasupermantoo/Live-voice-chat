<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * 空控制器
 */
class Error extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    //空方法
    public function _empty(){
        $this->ApiReturn(0,'非法操作');
    }


}
