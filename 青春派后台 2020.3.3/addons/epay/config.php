<?php

return array (
  0 => 
  array (
    'name' => 'wechat',
    'title' => '微信',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => 
    array (
      'appid' => '',
      'app_id' => 'wxd2f711b4555cb98a',
      'app_secret' => ' 55d975f715d5610cd642f7f7649fa645',
      'miniapp_id' => '',
      'mch_id' => '1560522441',
      'key' => '5y0XfJWnToUJ9FHnKSBra4blvRlDaBEG',
      'notify_url' => '/addons/epay/api/notifyx/type/wechat',
      'cert_client' => '/epay/certs/apiclient_cert.pem',
      'cert_key' => '/epay/certs/apiclient_key.pem',
      'log' => '1',
    ),
    'rule' => '',
    'msg' => '',
    'tip' => '微信参数配置',
    'ok' => '',
    'extend' => '',
  ),
  1 => 
  array (
    'name' => 'alipay',
    'title' => '支付宝',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => 
    array (
      'app_id' => '2019110769000055',
      'notify_url' => '/addons/epay/api/notifyx/type/alipay',
      'return_url' => '/addons/epay/api/returnx/type/alipay',
      'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvcfcfzarC6aVAcSkz/1KJvidjnkWRdqkoPKd0eeTHwjpKe9PPmMHYOlfeSQI8jM2uEaHix4KzJYfULK0crpwkSsFD182OPO0jtwOHHn8/UJUDpvM3y5sH99OuPgIEMihoYF9nr2ePfQUr0B6TOYJ9GYjDH7NjiopMx7Y0YMNlNKalscvLYq/j2sG607oIdCXFfWUhF1c4XzMfphfveGWdDhnIQ0Dy+j5+gZ85B1fXn9opYFoPQDYgMmLT7iLJzV4c4J6us9L+TWQ9jQ0Q0O7armF5eCV1nKhqCyH8fGiBV0wT0yvbiXUoz12Hly6nMf6PFyV+YUXttHofmaKcveRFQIDAQAB',
      'private_key' => '',
      'log' => '1',
    ),
    'rule' => 'required',
    'msg' => '',
    'tip' => '支付宝参数配置',
    'ok' => '',
    'extend' => '',
  ),
  2 => 
  array (
    'name' => '__tips__',
    'title' => '温馨提示',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => '请注意微信支付证书路径位于/addons/epay/certs目录下，请替换成你自己的证书<br>appid：APP的appid<br>app_id：公众号的appid<br>app_secret：公众号的secret<br>miniapp_id：小程序ID<br>mch_id：微信商户ID<br>key：微信商户支付的密钥',
    'rule' => '',
    'msg' => '',
    'tip' => '微信参数配置',
    'ok' => '',
    'extend' => '',
  ),
);
