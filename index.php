<?php
// wechat项目的入口文件
// 引入类文件
require './wechat.class.php';
$wechat = new Wechat();
// 判断是校验还是进行消息发送
if($_GET['echostr']){
  // 校验
  $wechat->valid();
}else{
  // 消息管理
  $wechat->responseMsg();
}
