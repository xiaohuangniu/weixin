<?php
# 引入回调类
require_once "weixin/WxPayNotify.php";
$obj = new WxNotify();
# 进入回调流程
$obj->WxNotifyNo();
?>