<?php

return array (
  0 => 
  array (
    'name' => 'driver',
    'title' => '驱动',
    'type' => 'select',
    'content' => 
    array (
      'LocalFileSystem' => '本地文件系统',
    ),
    'value' => 'LocalFileSystem',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  1 => 
  array (
    'name' => 'path',
    'title' => '根目录',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'D:\\PHPTutorial\\WWW\\aw\\public\\upload',
    'rule' => 'required',
    'msg' => '',
    'tip' => '文件浏览器的根目录',
    'ok' => '',
    'extend' => '',
  ),
  2 => 
  array (
    'name' => 'url',
    'title' => '根目录访问链接',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => 'http://www.awadmin.com:83',
    'rule' => 'required',
    'msg' => '',
    'tip' => '与上面目录对应,建议看下教程再配',
    'ok' => '',
    'extend' => '',
  ),
  3 => 
  array (
    'name' => 'allow_upload',
    'title' => '允许的上传类型',
    'type' => 'text',
    'content' => 
    array (
    ),
    'value' => 'image,text/plain,application/vnd.ms-excel,application/vnd.ms-office,mp4,m4v,gif, jpg, jpeg, png, bmp, swf, flv, mp3, wav, wma, wmv, mid, avi, mpg, asf, rm, rmvb, doc, docx, xls, xlsx, ppt, htm, html, txt, zip, rar, gz, bz2,pdf,js,md',
    'rule' => 'required',
    'msg' => '',
    'tip' => '支持的文件上传类型',
    'ok' => '',
    'extend' => '',
  ),
  4 => 
  array (
    'name' => 'allow_write',
    'title' => '可写的用户ID',
    'type' => 'string',
    'content' => 
    array (
    ),
    'value' => '1',
    'rule' => 'required',
    'msg' => '',
    'tip' => '多个英文逗号分隔开,未设置的只能只读',
    'ok' => '',
    'extend' => '',
  ),
);
