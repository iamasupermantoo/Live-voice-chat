<?php

namespace app\common\controller;
use think\Config;
/**
 * Redis缓存key
 */
class Rediskey
{

	
    public static  function getKey($key){
    	if(!$key) return '';
    	$RedisType = Config::get('RedisType');//值为   test:


    	$users=$RedisType.'users:getUsersInfo:%s';      //用户信息
    	$gifts=$RedisType.'gifts:%s';					//礼物数据
    	$baoshi=$RedisType.'baoshi:%s';					//宝石数据
    	$three_phase=$RedisType.'three_phase:%s';		//三期首页数据
        $room=$RedisType.'room:%s';                     //房间数据
        $skill=$RedisType.'skill:kill%s';               //技能详情
        $ysdConfig=$RedisType.'ysdConfig:%s';           //yansongda配置信息



        //配置信息
        $configinfo=$RedisType.'configinfo';
        //房间历史收入
        $roomIncomeCount = $RedisType.'roomIncomeCount:%s';
        //技能段位
        $skilllevel = $RedisType.'skillLevel:%s';
        //技能
        $skillList = $RedisType.'skillList';
        //可接单大区
        $areaList = $RedisType.'areaList:%s';
        //擅长位置
        $positionList = $RedisType.'positionList:%s';
        //价格
        $priceList = $RedisType.'priceList:%s';
        //派单中心
        $pdcenter = $RedisType.'pdcenter:%s';
        //背包物品
        $pack = $RedisType.'pack:%s';
        //家族
        $apply_family = $RedisType.'family:%s:apply_family';
        $user_apply_family = $RedisType.'family:%s:apply_family:%s';
    	return isset($$key) ? $$key : '';
    }



}

    