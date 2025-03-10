<?php
define("BIND_MODULE","Admin");//指定网站的前台模块或后台模块,意思是指定好我坐的是前台入口还是后台管理的入口
 
define("APP_PATH","application/");//意思是应用程序的目录

	//  开启调试模式                                DEBUG:调试程序  	  BUg:程序错误
define("APP_DEBUG",true);//页面是否报错

	//  构建目录安全
define("BUILD_DIR_SECURE",true);//创建目录时,自动添加index.html空白的,别人访问时看不到你的目录结构

include_once 'lirbary/ThinkPHP/ThinkPHP.php';







