<?php

require_once '../include/common_new.inc.php';
require_once '../auth_comm_jfsc.php';   			//授权
require_once 'jfscUtil.php';						//工具文件

//jfmall.pcwin68.com/jfsc/share.php?login=yes&sopenid=ou5riw5a5XCuYuYSvg-gwN6Bxmck

$login        	= isset($_REQUEST['login'])       		? trim($_REQUEST['login'])      	: '';
$sopenid        = isset($_REQUEST['sopenid'])       	? trim($_REQUEST['sopenid'])      	: '';
$group			= isset($_REQUEST['group'])       		? trim($_REQUEST['group'])      	: '';


//从海报过来的用户
if($login == "yes" && !empty($sopenid))
{
	$openid = $userinfo["openid"];
	$requestdomain = $site_conf['maindomain'];
	$reqUrl = $requestdomain."/jfsc/jfsc_function.php?method=getShareUserInfo&sopenid=$sopenid&openid=$openid&group=$group";
	//echo $reqUrl."<br>";
	$userData = httpRequest_php($reqUrl);
	var_dump($userData);
}

