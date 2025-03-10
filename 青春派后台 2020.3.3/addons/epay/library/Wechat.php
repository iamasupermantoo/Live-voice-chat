<?php

namespace addons\epay\library;

use fast\Http;
use think\Cache;
use think\Session;

/**
 * 微信授权
 *
 */
class Wechat
{
    private $app_id = 'wxd2f711b4555cb98a';
    private $app_secret = '54ef472539e23874df6be33135d094aa';
    private $scope = 'snsapi_userinfo';

    public function __construct($app_id, $app_secret)
    {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
    }

    /**
     * 获取微信授权链接
     *
     * @return string
     */
    public static function getAuthorizeUrl()
    {
        //$redirect_uri = addon_url('epay/api/wechat', [], true, true);
        //$redirect_uri = urlencode($redirect_uri);
        $redirect_uri = urlencode('http://b.juchuyuncang.com/index/index/recharge');
        $state = \fast\Random::alnum();
        Session::set('state', $state);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxd2f711b4555cb98a&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state=dJUbCD#wechat_redirect";
    }

    /**
     * 获取微信openid
     *
     * @return mixed|string
     */
    public static function getOpenid()
    {
        $openid = Session::get('openid');
        if (!$openid) {
            if (!isset($_GET['code'])) {
                $url = self::getAuthorizeUrl();

                Header("Location: $url");
                exit();
            } else {
                $state = Session::get('state');
                //echo $_GET['state'].'//'.$state;die;
                //if ($state == $_GET['state']) {
                    $code = $_GET['code'];
                    $token = self::getAccessToken($code);
                    $openid = isset($token['openid']) ? $token['openid'] : '';
                    if ($openid) {
                        Session::set("openid", $openid);
                    }
                //}
            }
        }
        return $openid;
    }

    /**
     * 获取授权token网页授权
     *
     * @param string $code
     * @return mixed|string
     */
    public static function getAccessToken($code = '')
    {
        $params = [
            'appid'      => 'wxd2f711b4555cb98a',
            'secret'     => '55d975f715d5610cd642f7f7649fa645',
            'code'       => $code,
            'grant_type' => 'authorization_code'
        ];
        $ret = Http::sendRequest('https://api.weixin.qq.com/sns/oauth2/access_token', $params, 'GET');
        //print_r($ret);die();
        if ($ret['ret']) {
            $ar = json_decode($ret['msg'], true);
            return $ar;
        }
        return [];
    }

    public function getJsticket()
    {
        $jsticket = Session::get('jsticket');
        if (!$jsticket) {
            $token = $this->getAccessToken($code);
            $params = [
                'access_token' => 'token',
                'type'         => 'jsapi',
            ];
            $ret = Http::sendRequest('https://api.weixin.qq.com/cgi-bin/ticket/getticket', $params, 'GET');
            if ($ret['ret']) {
                $ar = json_decode($ret['msg'], true);
                return $ar;
            }
        }
        return $jsticket;
    }
}
