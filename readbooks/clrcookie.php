<?php
session_start();
header("Content-type: text/html; charset=utf-8");
require_once './include/common_new.inc.php';
$cookiename = $site_uid . "_auth_openid";
setcookie($cookiename, '', time()-1000);
setcookie('auth_openid', '', time()-1000);

?>