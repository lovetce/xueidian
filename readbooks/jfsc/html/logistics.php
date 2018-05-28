<!--物流信息表-->
<?php
    header ( "Content-type: text/html; charset=utf-8" );
    require_once '../../include/common_new.inc.php';
    require_once '../../include/wx_jsdk_class.php';
    require_once '../../auth_comm_jfsc.php';

    $appid = $site_conf['appid'];
    $db = new dbstuff();
    $db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8mb4");
    if (!$db)
    {
        echo json_encode(array('status' => - 1, 'msg'=>'数据库链接失败'));
        exit();
    }
    if (!empty($sopenid))
    {
        $openid 		= $userinfo ["openid"];
        $unionid 		= $userinfo ["unionid"];
        $requestdomain 	= $site_conf ['maindomain'];
        $appid          = $site_conf ['appid'];
        $reqUrl 		= $requestdomain . "/jfsc/jfsc_function.php?method=getShareUserInfo&sopenid=$sopenid&openid=$openid&group=$group&unionid=$unionid&appid=$appid";

        $userData 		= httpRequest_php ( $reqUrl );                
    }

    $unionid = $userinfo ["unionid"];

    $rsu = $db->fetch_first("select * from {$tablepre}openid where appid ='$appid' and unionid ='$unionid' limit 1");
    $uid = $rsu['tid'];
    $headimageurl = $rsu['face'];
    $username = $rsu['username'];
    $dateline = $rsu['dateline'];
    $sql = "select * from {$tablepre}order where uid=$uid and (status = 2 or status = 3)";
    $query = $db->query($sql);

    $total= $db->num_rows($query);

?>

<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="user-scalable=no" >
	<title>快递信息</title>
	<link rel="stylesheet" type="text/css" href="/assets/css/goodsInfo.css"/>
</head>
<body>
    <!--幻灯片-->
    <div>
        <div class="ppt">
            <a href="#"><img src="/assets/images/wl.jpg" style="width: 100%"></a>
        </div>
        <div class="add-info">
            <? if($total<=0){ ?>
                <p class="PromptTxt"><span></span>您没有完成过任务，未获取到您的快递</p>
            <? }else{ 
                while($rss=$db->fetch_array($query)){
            ?>
                <p class="PromptTxt"><span></span>快递：<? echo $rss['wlname'] ?>单号：<? echo $rss['wlno'] ?></p>
            <? 
                }
                }
            ?>
        </div>
    </div>
</body>

</html>