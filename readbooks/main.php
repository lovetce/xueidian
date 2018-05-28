<?php
session_start();
header("Content-type: text/html; charset=utf-8");

require_once './include/common.inc.php';
//require_once './include/wx_jsdk_class.php';
$code = isset($_GET["code"]) ? $_GET["code"] : '';
$appid = $_WEITE['appid'];
exit($appid);
?>