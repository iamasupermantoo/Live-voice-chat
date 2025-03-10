<?php
namespace Index\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
          $dt=array(
            "wx_openid"=>1
        );

        for ($i=0;$i<1308;$i++){
        $add=M('b_users')->Add($dt);
        }
    }
}