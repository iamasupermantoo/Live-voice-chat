<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:70:"/www/wwwroot/qcp/public/../application/admin/view/dashboard/index.html";i:1573206790;s:59:"/www/wwwroot/qcp/application/admin/view/layout/default.html";i:1564710190;s:56:"/www/wwwroot/qcp/application/admin/view/common/meta.html";i:1565258738;s:58:"/www/wwwroot/qcp/application/admin/view/common/script.html";i:1562338654;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<link rel="shortcut icon" href="/assets/img/favicon.png" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>
    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !$config['fastadmin']['multiplenav']): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <style type="text/css">
    .sm-st {
        background: #fff;
        padding: 20px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        margin-bottom: 20px;
        -webkit-box-shadow: 0 1px 0px rgba(0, 0, 0, 0.05);
        box-shadow: 0 1px 0px rgba(0, 0, 0, 0.05);
    }

    .sm-st-icon {
        width: 60px;
        height: 60px;
        display: inline-block;
        line-height: 60px;
        text-align: center;
        font-size: 30px;
        background: #eee;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        float: left;
        margin-right: 10px;
        color: #fff;
    }

    .sm-st-info {
        font-size: 12px;
        padding-top: 2px;
    }

    .sm-st-info span {
        display: block;
        font-size: 24px;
        font-weight: 600;
    }

    .orange {
        background: #fa8564 !important;
    }

    .tar {
        background: #45cf95 !important;
    }

    .sm-st .green {
        background: #86ba41 !important;
    }

    .pink {
        background: #AC75F0 !important;
    }

    .yellow-b {
        background: #fdd752 !important;
    }

    .stat-elem {

        background-color: #fff;
        padding: 18px;
        border-radius: 40px;

    }

    .stat-info {
        text-align: center;
        background-color: #fff;
        border-radius: 5px;
        margin-top: -5px;
        padding: 8px;
        -webkit-box-shadow: 0 1px 0px rgba(0, 0, 0, 0.05);
        box-shadow: 0 1px 0px rgba(0, 0, 0, 0.05);
        font-style: italic;
    }

    .stat-icon {
        text-align: center;
        margin-bottom: 5px;
    }

    .st-red {
        background-color: #F05050;
    }

    .st-green {
        background-color: #27C24C;
    }

    .st-violet {
        background-color: #7266ba;
    }

    .st-blue {
        background-color: #23b7e5;
    }

    .stats .stat-icon {
        color: #28bb9c;
        display: inline-block;
        font-size: 26px;
        text-align: center;
        vertical-align: middle;
        width: 50px;
        float: left;
    }

    .stat {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        margin-right: 10px;
    }

    .stat .value {
        font-size: 20px;
        line-height: 24px;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 500;
    }

    .stat .name {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stat.lg .value {
        font-size: 26px;
        line-height: 28px;
    }

    .stat.lg .name {
        font-size: 16px;
    }

    .stat-col .progress {
        height: 2px;
    }

    .stat-col .progress-bar {
        line-height: 2px;
        height: 2px;
    }

    .item {
        padding: 30px 0;
    }
</style>
<?php if(preg_match('/\/admin\/|admin\.php|admin_d75KABNWt\.php/i', url())): ?>
<div class="alert alert-danger-light">
    <?php echo __('Security tips'); ?>
</div>
<?php endif; ?>
<div class="panel panel-default panel-intro">
    <div class="panel-heading">
        <?php echo build_heading(null, false); ?>
        <ul class="nav nav-tabs">
            <li class="active"><a href="#one" data-toggle="tab"><?php echo __('Dashboard'); ?></a></li>
           <!-- <li><a href="#two" data-toggle="tab">每日数据统计</a></li>-->
        </ul>
    </div>
    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">

                <div class="row">
                    <div class="col-sm-3 col-xs-6">
                        <div class="sm-st clearfix">
                            <span class="sm-st-icon st-red"><i class="fa fa-users"></i></span>
                            <div class="sm-st-info">
                                <span><?php echo $totaluser; ?></span>
                                <?php echo __('总注册会员数'); ?>
                            </div>
                        </div>
                    </div>
                    <!--<div class="col-sm-3 col-xs-6">
                        <div class="sm-st clearfix">
                            <span class="sm-st-icon st-violet"><i class="fa fa-book"></i></span>
                            <div class="sm-st-info">
                                <span><?php echo $totalviews; ?></span>
                                <?php echo __('Total view'); ?>
                            </div>
                        </div>
                    </div>-->
                    <div class="col-sm-3 col-xs-6">
                        <div class="sm-st clearfix">
                            <span class="sm-st-icon st-blue"><i class="fa fa-shopping-bag"></i></span>
                            <div class="sm-st-info">
                                <span><?php echo $totalorder; ?></span>
                                <?php echo __('累计充值金额'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="sm-st clearfix">
                            <span class="sm-st-icon st-green"><i class="fa fa-cny"></i></span>
                            <div class="sm-st-info">
                                <span><?php echo $totalcomplatetixian; ?></span>
                                <?php echo __('累计提现金额'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-xs-6">
                        <div class="sm-st clearfix">
                            <span class="sm-st-icon st-green"><i class="fa fa-cny"></i></span>
                            <div class="sm-st-info">
                                <span><?php echo $totalorderamount; ?></span>
                                <?php echo __('累计申请提现金额'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    
                    <div class="col-lg-12">
                        <div class="card sameheight-item stats">
                            <div class="card-block">
                                <div class="row row-sm stats-container">
                                    <div class="col-xs-3 stat-col">
                                        <div class="stat-icon"><i class="fa fa-rocket"></i></div>
                                        <div class="stat">
                                            <div class="value"> <?php echo $todayusersignup; ?></div>
                                            <div class="name"> <?php echo __('今日注册会员数'); ?></div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success" style="width: 30%"></div>
                                        </div>
                                    </div>
                                    <div class="col-xs-3  stat-col">
                                        <div class="stat-icon"><i class="fa fa-line-chart"></i></div>
                                        <div class="stat">
                                            <div class="value"> <?php echo $todayorder; ?></div>
                                            <div class="name"> <?php echo __('今日充值金额'); ?></div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success" style="width: 25%"></div>
                                        </div>
                                    </div>
                                    <div class="col-xs-3 stat-col">
                                        <div class="stat-icon"><i class="fa fa-shopping-cart"></i></div>
                                        <div class="stat">
                                            <div class="value"> <?php echo $todaycomplatetixian; ?></div>
                                            <div class="name"> <?php echo __('今日提现金额'); ?></div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success" style="width: 25%"></div>
                                        </div>
                                    </div>
                                    <div class="col-xs-3 stat-col">
                                        <div class="stat-icon"><i class="fa fa-shopping-cart"></i></div>
                                        <div class="stat">
                                            <div class="value"> <?php echo $todayuserlogin; ?></div>
                                            <div class="name"> <?php echo __('今日申请提现'); ?></div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success" style="width: 25%"></div>
                                        </div>
                                    </div>
                                    <!--
                                    <div class="col-xs-6  stat-col">
                                        <div class="stat-icon"><i class="fa fa-users"></i></div>
                                        <div class="stat">
                                            <div class="value"> <?php echo $unsettleorder; ?></div>
                                            <div class="name"> <?php echo __('Unsettle order'); ?></div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success" style="width: 25%"></div>
                                        </div>
                                    </div>
                                    <div class="col-xs-6  stat-col">
                                        <div class="stat-icon"><i class="fa fa-list-alt"></i></div>
                                        <div class="stat">
                                            <div class="value"> <?php echo $sevendnu; ?></div>
                                            <div class="name"> <?php echo __('Seven dnu'); ?></div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success" style="width: 25%"></div>
                                        </div>
                                    </div>
                                    <div class="col-xs-6 stat-col">
                                        <div class="stat-icon"><i class="fa fa-dollar"></i></div>
                                        <div class="stat">
                                            <div class="value"> <?php echo $sevendau; ?></div>
                                            <div class="name"> <?php echo __('Seven dau'); ?></div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-success" style="width: 25%"></div>
                                        </div>
                                    </div>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                        <div id="echart" style="height:500px;width:100%;"></div>
                    </div> 

                <!--<div class="row" style="margin-top:15px;">

                    <div class="col-lg-12">
                    </div>
                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-blue">
                            <div class="panel-body">
                                <div class="panel-title">
                                    <span class="label label-success pull-right"><?php echo __('Real time'); ?></span>
                                    <h5><?php echo __('Category count'); ?></h5>
                                </div>
                                <div class="panel-content">
                                    <h1 class="no-margins">1234</h1>
                                    <div class="stat-percent font-bold text-gray"><i class="fa fa-commenting"></i> 1234</div>
                                    <small><?php echo __('Category count tips'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-aqua-gradient">
                            <div class="panel-body">
                                <div class="ibox-title">
                                    <span class="label label-info pull-right"><?php echo __('Real time'); ?></span>
                                    <h5><?php echo __('Attachment count'); ?></h5>
                                </div>
                                <div class="ibox-content">
                                    <h1 class="no-margins">1043</h1>
                                    <div class="stat-percent font-bold text-gray"><i class="fa fa-modx"></i> 2592</div>
                                    <small><?php echo __('Attachment count tips'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-purple-gradient">
                            <div class="panel-body">
                                <div class="ibox-title">
                                    <span class="label label-primary pull-right"><?php echo __('Real time'); ?></span>
                                    <h5><?php echo __('Article count'); ?></h5>
                                </div>
                                <div class="ibox-content">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <h1 class="no-margins">1234</h1>
                                            <div class="font-bold"><i class="fa fa-commenting"></i>
                                                <small><?php echo __('Comment count'); ?></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h1 class="no-margins">6754</h1>
                                            <div class="font-bold"><i class="fa fa-heart"></i>
                                                <small><?php echo __('Like count'); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 col-md-3">
                        <div class="panel bg-green-gradient">
                            <div class="panel-body">
                                <div class="ibox-title">
                                    <span class="label label-primary pull-right"><?php echo __('Real time'); ?></span>
                                    <h5><?php echo __('News count'); ?></h5>
                                </div>
                                <div class="ibox-content">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <h1 class="no-margins">5302</h1>
                                            <div class="font-bold"><i class="fa fa-commenting"></i>
                                                <small><?php echo __('Comment count'); ?></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h1 class="no-margins">8205</h1>
                                            <div class="font-bold"><i class="fa fa-user"></i>
                                                <small><?php echo __('Like count'); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->

                <!--<div class="row">
                    <div class="col-lg-4">
                        <div class="box box-danger">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?php echo __('Recent news'); ?></h3>

                                <div class="box-tools pull-right">
                                </div>
                            </div>
                            <div class="box-body">
                                <ul class="products-list product-list-in-box">
                                    <?php $__FOR_START_427665521__=1;$__FOR_END_427665521__=8;for($i=$__FOR_START_427665521__;$i < $__FOR_END_427665521__;$i+=1){ ?>
                                    <li class="item">
                                        <div class="product-img">
                                            <img src="/assets/img/avatar.png" style="height:40px;width:40px;">
                                        </div>
                                        <div class="product-info">
                                            <a href="https://www.fastadmin.net" target="_blank" class="product-title">
                                                FastAdmin
                                                <span class="label label-<?php echo $i%3===0?'warning':($i%2===0?'success':'info'); ?> pull-right">开源免费</span>
                                            </a>
                                            <span class="product-description">
                                              一款基于ThinkPHP5+Bootstrap的极速后台开发框架
                                            </span>
                                        </div>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?php echo __('Recent discussion'); ?></h3>

                                <div class="box-tools pull-right">
                                </div>
                            </div>
                            <div class="box-body">
                                <ul class="nav nav-pills nav-stacked">
                                    <li><a href="https://www.fastadmin.net" target="_blank">一款基于ThinkPHP5+Bootstrap的极速后台开发框架<span class="pull-right text-red"><i class="fa fa-angle-down"></i> 12%</span></a></li>
                                    <li><a href="https://www.fastadmin.net" target="_blank">一键生成CRUD控制器模型和视图 <span class="pull-right text-green"><i class="fa fa-angle-up"></i> 4%</span></a></li>
                                    <li><a href="https://www.fastadmin.net" target="_blank">一键压缩打包JS和CSS文件 <span class="pull-right text-red"><i class="fa fa-angle-down"></i> 3%</span></a></li>
                                    <li><a href="https://www.fastadmin.net" target="_blank">一键生成控制器菜单和规则 <span class="pull-right text-green"><i class="fa fa-angle-up"></i> 8%</span></a></li>
                                    <li><a href="https://www.fastadmin.net" target="_blank">一键生成API接口文档 <span class="pull-right text-yellow"><i class="fa fa-angle-left"></i> 0%</span></a></li>
                                    <li><a href="https://www.fastadmin.net" target="_blank">强大的插件扩展功能，在线安装卸载升级插件 <span class="pull-right text-red"><i class="fa fa-angle-down"></i> 10%</span></a></li>
                                    <li><a href="https://www.fastadmin.net" target="_blank">通用的会员模块和API模块 <span class="pull-right text-green"><i class="fa fa-angle-up"></i> 2%</span></a></li>
                                    <li><a href="https://www.fastadmin.net" target="_blank">共用同一账号体系的Web端会员中心权限验证和API接口会员权限验证 <span class="pull-right text-red"><i class="fa fa-angle-down"></i> 6%</span></a></li>
                                    <li><a href="https://www.fastadmin.net" target="_blank">二级域名部署支持，同时域名支持绑定到插件 <span class="pull-right text-yellow"><i class="fa fa-angle-left"></i> 0%</span></a></li>
                                    <li><a href="https://www.fastadmin.net" target="_blank">多语言支持，服务端及客户端支持 <span class="pull-right text-yellow"><i class="fa fa-angle-left"></i> 0%</span></a></li>
                                    <li><a href="https://www.fastadmin.net" target="_blank">基于Bootstrap开发，自适应手机、平板、PC <span class="pull-right text-green"><i class="fa fa-angle-up"></i> 4%</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="box box-info">
                            <div class="box-header"><h3 class="box-title"><?php echo __('Server info'); ?></h3></div>
                            <div class="box-body" style="padding-top:0;">
                                <table class="table table-striped">
                                    <tbody>
                                    <tr>
                                        <td width="140"><?php echo __('FastAdmin version'); ?></td>
                                        <td><?php echo \think\Config::get('fastadmin.version'); ?> <a href="javascript:;" class="btn btn-xs btn-checkversion">检查最新版</a></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('FastAdmin addon version'); ?></td>
                                        <td><?php echo $addonversion; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('Thinkphp version'); ?></td>
                                        <td><?php echo THINK_VERSION; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('Sapi name'); ?></td>
                                        <td><?php echo php_sapi_name(); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('Debug mode'); ?></td>
                                        <td><?php echo \think\Config::get('app_debug')?__('Yes'):__('No'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('Software'); ?></td>
                                        <td><?php echo \think\Request::instance()->server('SERVER_SOFTWARE'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('Upload mode'); ?></td>
                                        <td><?php echo $uploadmode; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('Upload url'); ?></td>
                                        <td><?php echo $config['upload']['uploadurl']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('Upload Cdn url'); ?></td>
                                        <td><?php echo $config['upload']['cdnurl']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('Timezone'); ?></td>
                                        <td><?php echo date_default_timezone_get(); ?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('Cdn url'); ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo __('Language'); ?></td>
                                        <td><?php echo $config['language']; ?></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>-->
            </div>
            <div class="tab-pane fade" id="two">
                <div class="row">
                    <div class="col-xs-12">
                        <div id="echarts" style="height:250px;width:1200px;"></div>
                        <div id="echarts_money" style="margin-top:20px;height:250px;width:1200px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <!--form表单必须添加form-commsearch这个类-->
    
<script>
    var Orderdata = {
        paydata: <?php echo json_encode(array_values($res)); ?>,
        createdata: <?php echo json_encode(array_values($createlist)); ?>,
        days      : <?php echo json_encode(array_values($days)); ?>,
        register  : <?php echo json_encode(array_values($register)); ?>,
        active    : <?php echo json_encode(array_values($active)); ?>,
        recharge  : <?php echo json_encode(array_values($recharge)); ?>,
        tixian    : <?php echo json_encode(array_values($tixian)); ?>,
    };
</script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>