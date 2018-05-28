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
    body {background: rgb(243, 243, 243);}
    td {
        vertical-align:middle !important;
    }
    .followtime {
        font-size:13px;
        text-align:right;
        color:red;
    }
    body {
        background-color:#eee;
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
                <div>关注时间：<? echo date("Y-m-s H:i",$dateline) ?></div>
                <div>粉丝：<? echo $total ?>                    积分：0                </div>
            </div>
        </div>
      </div>
</div>

<div class="profile_fun" style="display: block">
    <ul id="js_menu" class="menu_list">
        <li class="menu_item fans_record">
            <a href="yqji.php" class="inner_item img-circle">
                <!--<span class="icon_menu"></span>-->
                <!--<img src="../addons/zuizan_shop/plugin/creditshop/template/mobile/default/images/b1.png">-->
                <span class="text_menu" style="">我的<br>记录</span>
                </a>
        </li>                                
        <li class="menu_item rank_record">
            <a href="dhji.php" class="inner_item" style="background: #ffbf50;">
                <!--<span class="icon_menu"></span>-->
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

    <div id="rankpage">
        <section id="ranking">
            <span id="ranking_title">邀请记录(<? echo $total ?>人)</span>
            <section id="ranking_list">
                <? 
                    $i=1;
                    while($rss=$db->fetch_array($query)){
                ?>
                    <section class="box">
                        <section class="col_1" title="<? echo $i ?>"><? echo $i ?></section>
                        <section class="col_2"><img src="<? echo $rss['face'] ?>" /></section>
                        <section class="col_3"><? echo $rss['username'] ?></section>
                        <section class="col_4" style="width: 35%;"><? echo date("Y-m-s H:i",$rss['rel_time']) ?></section>
                    </section>    
                <?
                    $i++;
                    }
                ?>                              
            </section>
        </section>
    </div>
</div>