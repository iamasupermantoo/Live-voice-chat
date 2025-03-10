<?php
namespace app\index\controller;
use app\common\controller\Frontend;
use addons\epay\library\Wechat;
use think\Session;
use think\Db;

class Index extends Frontend {
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';
    /*
     * 网站首页
    */
    public function index() {
        $url="http://".$_SERVER['HTTP_HOST'].'/admin_d75KABNWAWSEWWER.php/index/login';
      	//echo $url;exit;  
      	header('Location:'.$url);
      	exit;
        //return $this->view->fetch();
    }
    /*
     * 登陆
    */
    public function login() {
        return $this->view->fetch();
    }
    /*
     * 提现
    */
    public function withdraw() {
        return $this->view->fetch();
    }
    /*
     * 充值
    */
    public function recharge() {
        if (!Session::get('openid')) {
            if (isset($_GET['code'])) {
                \addons\epay\library\Wechat::getOpenid();
            } else {
                $url = \addons\epay\library\Wechat::getAuthorizeUrl();
                //echo $url;die;
                $this->redirect($url);
            }
        }
        return $this->view->fetch();
    }
    /*
     * 充值
    */
    public function chongzhi() {
        return $this->view->fetch();
    }
    /*
     * 兑换
    */
    public function exchange() {
        return $this->view->fetch();
    }
    /*
     * 个人中心
    */
    public function usercenter() {
        return $this->view->fetch();
    }
    /*
     * 兑换充值记录
    */
    public function accountlog() {
        return $this->view->fetch();
    }
    //我的收益
    public function myearnings() {
        return $this->view->fetch();
    }
    //我的收益 --登录后
    public function income() {
        return $this->view->fetch();
    }
    /*
     * 成功
    */
    public function suc_tip() {
        return $this->view->fetch();
    }
    /*
     * 失败
    */
    public function fail_tip() {
        return $this->view->fetch();
    }
    // 用户协议
    public function user_protocol() {
        return $this->view->fetch();
    }
    // 隐私条款
    public function secret_protocol() {
        return $this->view->fetch();
    }
    // 充值协议
    public function recharge_protocol() {
        return $this->view->fetch();
    }
    // 违规协议
    public function violation_protocol() {
        return $this->view->fetch();
    }
    // 下载
    public function mob_down() {
        return $this->view->fetch();
    }
    // 狂欢周
    public function hilarity() {
        return $this->view->fetch();
    }
  	// 公告
    public function notice() {
        $data=DB::name('officials')->select();
        $this->assign('data',$data);
        return $this->view->fetch();
    }
  	// 首页轮播4
    public function home_carousels4() {
        return $this->view->fetch();
    }
}

