<?php

require_once (dirname(__FILE__) . '../../auth_comm_jfsc_appid.php');	//授权
require_once (dirname(__FILE__) . '/jfscUtil.php');						//工具类

$auth_openid		= $auth_info['openid'];								//服务号openid
$auth_unionid		= $auth_info['unionid'];							//服务号unionid
$auth_uid			= $auth_info['uid'];								//服务号uid
$checkSendCookie	= "checkSend".$auth_openid."#".$appid;				//检查发送Cookie名字
$successSendState	= $checkSendCookie."#1";							//已发送Cookie状态值

$appid 		= isset($_GET['appid']) 			? trim($_GET['appid'])		 : '';
$sendStatus = isset($_COOKIE[$checkSendCookie]) ? $_COOKIE[$checkSendCookie] : '';
$group 		= isset($_GET['group']) 			? trim($_GET['group'])		 : '';
$taskid = "1"; //默认为0
$tasktype = "4";

//没发过的引导关注
// if ($sendStatus != $successSendState)
// {
	$requestdomain = $site_conf['maindomain'];
	$reqUrl = $requestdomain."/jfsc/jfsc_function.php?method=sendMsgByGuide&unionid=$auth_unionid&appid=$appid&quid=$auth_uid&taskid=$taskid&tasktype=$tasktype";
	
	$userData = httpRequest_php($reqUrl);
	//发送状态存cookie
	setcookie($checkSendCookie, $successSendState, time()+31536000, "/");
// }








