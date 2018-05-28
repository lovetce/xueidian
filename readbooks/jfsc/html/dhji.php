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
    $query = $db->query("select * from {$tablepre}openid where rel_id=$uid");
    $total= $db->num_rows($query);

    $query2 = $db->query("select b.head_url,b.title,a.status,a.crtime,a.id from {$tablepre}order a left join {$tablepre}subject b on a.subject_id=b.id where a.uid=$uid");

?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">        
        <meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
        <meta name="format-detection" content="telephone=no" />
    </head>
<body>

<link href="/assets/css/mobile.css" rel="stylesheet" type="text/css"/>
<link href="/assets/css/bootstrap-3.3.4.css" rel="stylesheet">

<title>包邮赠书活动-我的邀请</title>
<style>
    body html {
        color:#F3F3F3;
    }
    .log-wrap{
        display: block;
        width:96%;
        height:80px;
        background-color: #fff;
        margin:8px auto;
        padding:10px;
        border-radius: 5px;
        color: #000;
    }
    .text-wrap{
        margin:0 20px 0 75px;
    }
    .log-text{
        float:left;
        width: 100%;
        height:60px;
        font-size: 12px;
    }
    .img-wrap{
        float:left;
        width: 60px;
        height: 60px;
        margin-left: -95%;
    }
    .log-img{
        width:100%;
        height: 100%;
    }
    .log-status{
        float: left;
        width: 20px;
        height: 60px;
        line-height: 20px;
        font-size: 12px;
        /*margin-left: -20px;*/
    }
    .no-margin{
        overflow: hidden;
        height:22px;
        line-height: 22px;
        margin-bottom: 0 !important;
    } 
    .log-title{
        font-weight: bold;
    }
</style>

<div class="">
    <style>
        body {background: #F3F3F3;}
        .profile_logo {width: 85px;border-radius: 50%;}
        .nickname {font-size: 16px;font-weight: bold;}
    </style>

    <div class="fansinfo" style="background: #F3F3F3;">
        <div style="overflow: hidden">
            <div class="data_overview">
                <ul class="overview_list">
                    <li class="overview_item">
                        <p class="desc r_line">粉丝数</p>
                        <p class="number"><? echo $total ?></p>
                    </li>
                    <li class="overview_item">
                        <img class="profile_logo" src="<? echo $headimageurl ?>">
                    </li>
                    <li class="overview_item">
                        <p class="desc">积分</p>
                        <p class="number">0</p>
                    </li>
                </ul>
            </div>
        </div>

        <div id="js_main" class="profile_hd" style="display: none">
            <div class="inner">
            <img class="profile_logo" src="<? echo $headimageurl ?>">
                <div style="display: inline-table;text-align: left;">
                <div><? echo $username ?></div>
                    <div>关注时间：<? echo date("Y-m-s h:i:s",$dateline) ?></div>
                    <div>粉丝：<? echo $total ?>                    积分：0                </div>
                </div>
            </div>
        </div>
    </div>

    <div class="profile_fun" style="display: block">
        <ul id="js_menu" class="menu_list">
            <li class="menu_item fans_record">
                <a href="yqji.php" class="inner_item img-circle">                    
                    <span class="text_menu" style="">我的<br>记录</span>
                </a>
            </li>
            <li class="menu_item rank_record">
                <a href="yqji.php" class="inner_item" style="background: #ffbf50;">
                    <span class="text_menu">兑换<br>记录</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- Ad -->

    <style>
        .swiper-container {
            width: 100%;
            margin-bottom: 10px;
        }
        .swiper-container img {
            width: 100%;
        }
        .swiper-slide {
            text-align: center;
            display: flex;
            -webkit-box-pack: center;
            align-items: center;
            justify-content: center;
            -webkit-box-align: center;
        }
    </style>

    <div class="swiper-container">
        <div class="swiper-wrapper">                    
        </div>
    </div>
    <div class="clearfix"></div>
    <div>
        <?
            while($rss=$db->fetch_array($query2)){
        ?>
            <a class="log-wrap clearfix" href="#">
                <div class="text-wrap">
                    <div class="log-text">
                        <p class="no-margin">
                            <span class="label label-success">实物</span>
                            <span class="log-title"><? echo $rss["title"] ?></span>
                        </p>
                        <p class="no-margin">消耗积分：0</p>
                        <p class="no-margin">兑换时间：<? echo date("Y-m-s H:i",$rss["crtime"])?></p>
                    </div>
                </div>
                <div class="img-wrap">
                    <img class="log-img" src="<? echo $rss['head_url'] ?>" alt="缩略图">
                </div>
                <div class="log-status">
                    <!-- 状态为3 && 有物流信息 -->
                        <? echo $rss["status"] == 1 ? "未发货":$rss["status"] == 2 ? "已发货" : "已签收" ?>
                </div>
            </a>
        <?
            }
        ?>        
    </div>
</div>