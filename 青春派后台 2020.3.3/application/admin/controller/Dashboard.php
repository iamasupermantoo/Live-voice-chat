<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use think\Db;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {

        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }

        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');

        //根据users表获得总会员数和当天注册会员数
        $totaluser = Db::name('users')->count();
        $where = "created_at>=date(now()) and created_at<DATE_ADD(date(now()),INTERVAL 1 DAY)";
        $totayuser = Db::name('users')->where($where)->count();
        //根据order表获得累计充值金额和当天充值金额
        $where2 = "status = 2";
        $totalorder = Db::name('order')->where($where2)->sum('price');
        $date_s = strtotime(date('Y-m-d'));
        $date_e = strtotime(date('Y-m-d 23:59:59'));
        $where2 .= " and addtime>=$date_s and addtime < $date_e";
        $todayorder = Db::name('order')->where($where2)->sum('price');
        //echo Db::name('order')->getLastSql();die;
        //根据tixian表获得累计提现金额和当天提现金额
        $where3 = 'status = 1';
        $totaltixian = Db::name('tixian')->where($where3)->sum('money');
        $where3 .= " and addtime>=$date_s and addtime < $date_e";
        $todaytixian = Db::name('tixian')->where($where3)->sum('money');

        $where4 = 'status = 2';
        $totalcomplatetixian = Db::name('tixian')->where($where4)->sum('money');
        $where4 .= " and tx_time>=$date_s and tx_time < $date_e";
        $todaycomplatetixian = Db::name('tixian')->where($where4)->sum('money');

        //滞留金额
        $mizuan=Db::name('users')->where(['status'=>1])->sum('mizuan');
        $mibi=Db::name('users')->where(['status'=>1])->sum('mibi');
        $r_mibi=Db::name('users')->where(['status'=>1])->sum('r_mibi');
        $mizuan=ceil($mizuan/10);
        $total_zlj=$mizuan+$mibi+$r_mibi;


        $createlist=getChannelArr();
        foreach ($createlist as $k => &$v) {
            $arr['value']=Db::name('users')->where(['channel'=>$k])->count();
            $arr['name']=$v;
            if($arr['value']>0){
                $res[]=$arr;
            }
        }


        //获得每日统计数据============================
        $register = array();
        $active   = array();
        $recharge = array();
        $tixian   = array();
        $enddate  = date('Y-m-d', strtotime('-16 days'));

        $infos = Db::name('tongji')
            ->field('id,logtime,addtime,register,active,recharge,tixian')
            ->where("logtime >= '$enddate'")
            ->order('logtime asc')
            ->select();
        
        foreach ($infos as $key => $info) {
            $register[] = $info['register'] ? $info['register'] : 0;
            $active[]   = $info['active'] ? $info['active'] : 0;
            $recharge[] = $info['recharge'] ? $info['recharge'] : 0;
            $tixian[]   = $info['tixian'] ? $info['tixian'] : 0;
            $days[]     = date('m-d',strtotime($info['logtime']));
        }  
$days[]=1;
        $this->view->assign([
            'totaluser'        => $totaluser,
            'totalviews'       => 219390,
            'totalorder'       => $totalorder,
            'totalorderamount' => $totaltixian,
            'todayuserlogin'   => $todaytixian,
            'todayusersignup'  => $totayuser,
            'todayorder'       => $todayorder,
            'unsettleorder'    => 132,
            'sevendnu'         => '80%',
            'sevendau'         => '32%',
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'     => $addonVersion,
            'uploadmode'       => $uploadmode,
            'totalcomplatetixian'=>$totalcomplatetixian,
            'todaycomplatetixian'=>$todaycomplatetixian,
            'total_zlj'        =>$total_zlj,
            'res'              =>$res,
            //每日统计数据
            'days'             =>$days,
            'register'         =>$register,
            'active'           =>$active,
            'recharge'         =>$recharge,
            'tixian'           =>$tixian,
        ]);

        return $this->view->fetch();
    }

}
