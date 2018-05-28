<?php
header ( "Content-type: text/html; charset=utf-8" );
require_once '../../include/common_new.inc.php';
require_once '../../include/wx_jsdk_class.php';
require_once '../../auth_comm_jfsc.php'; 			// 授权
require_once '../jfscUtil.php'; 					// 工具文件
                                


$login 		= isset ( $_REQUEST ['login'] ) 	? trim ( $_REQUEST ['login'] ) 		: '';
$sopenid 	= isset ( $_REQUEST ['sopenid'] ) 	? trim ( $_REQUEST ['sopenid'] ) 	: '';
$group 		= isset ( $_REQUEST ['group'] ) 	? trim ( $_REQUEST ['group'] ) 		: '';

// 从海报过来的用户
if (!empty($sopenid))
{
	$openid 		= $userinfo ["openid"];
	$unionid 		= $userinfo ["unionid"];
	$requestdomain 	= $site_conf ['maindomain'];
	$appid          = $site_conf ['appid'];
	$reqUrl 		= $requestdomain . "/jfsc/jfsc_function.php?method=getShareUserInfo&sopenid=$sopenid&openid=$openid&group=$group&unionid=$unionid&appid=$appid";

	$userData 		= httpRequest_php ( $reqUrl );
	
	//	var_dump($userData);
	$gzhPic = "images/2.jpg";
	if(!empty($userData['pic']))
	{
		$gzhPic = $userData['pic'];
	}
}

$maindomain = $site_conf ['maindomain'];
$tzdomain 	= $site_conf ['tzdomain'];

//百度统计代码
$baiduCount = "";

$db = new dbstuff();
$db->connect ( $dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8" );
if (!$db)
{
	echo json_encode ( array ( 'status' => - 1, 'msg' => '数据库链接失败') );
	exit();
}

$nickname 	= "";
$headimgurl = "";

$rs = $db->fetch_first ( "select face,username from {$tablepre}openid where  openid ='$sopenid'  order by tid desc limit 1" );
if ($rs)
{
	$nickname 	= $rs ['username'];
	$headimgurl = $rs ['face'];
}
$db->close ();


$imgUrl 	= "http://" . $maindomain . "/jfsc/images/share1.jpg";
$shareurl 	= "http://" . $tzdomain . "/jfsc/html/guanzhu.php?login=" . $login . "&sopenid=" . $sopenid . "&group=" . $group;

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="black" name="apple-mobile-web-app-status-bar-style">
<meta content="telephone=no" name="format-detection">
<meta content="email=no" name="format-detection">
<title>包邮赠书活动</title>
<!-- 公共样式 -->
<link rel="stylesheet" href="../../assets/css/css.css">
<style type="text/css">
	.main_container {
		text-align: center;
	}

	.headimg {
		width: 1.5rem;
		height: 1.5rem;
		border-radius: 50%;
		margin-top: 0.36rem;
	}

	.nickname {
		font-size: 0.4rem;
		color: #151516;
		margin-bottom: 0.38rem;
	}

	.help {
		font-size: 0.36rem;
		color: #151516;
	}

	.banner {
		width: 5.26rem;
		height: 4.68rem;
		margin: 0.36rem 0 0.5rem 0;
	}

	.help_btn {
		width: 5.52rem;
		height: 0.72rem;
		line-height: 0.68rem;
		border-radius: 0.16rem;
		font-size: 0.36rem;
		color: #000;
		/* background-color: #4bb22e; */
		margin-bottom: 0.3rem;
		display: flex;
	}
.help_btn_img{
    width: 35px;
    height: 35px;
	margin: 0 5px 0 0;
}
	.green {
		margin: 0 auto;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 24px;
    color: red;
    width: 100%;
	}

	.bg {
		z-index: 11;
		position: absolute;
		top: 0;
		left: 0;
		/* width: 10rem; */
		width: 100%;
		height: 100%;
		display: none;
		background-color: rgba(33, 33, 33, 0.8);
	}

	.img {
		z-index: 999;
		position: absolute;
		top: 3.4rem;
		left: 0.2rem;
		width: 7.1rem;
		height: 10rem;
		display: none;
	}

	.changan {
		width: 100%;
		text-align: center;
		color: #333333;
		font-size: 0.4rem;
		height: 0.6rem;
		line-height: 0.6rem;
		margin: 0;
		color: white;
	}

	h6 {
		display: block;
		font-size: 0.67em;
		-webkit-margin-before: 2.33em;
		-webkit-margin-after: 2.33em;
		-webkit-margin-start: 0px;
		-webkit-margin-end: 0px;
		font-weight: bold;
	}

	.btn2 {
		display: none;
		margin: 0 auto 0 auto;
		text-align: center;
		font-family: "微软雅黑";
		font-size: 0.6rem;
		line-height: 1rem;
		width: 8rem;
		height: 1rem;
		color: white;
		background-color: #1AAD19;
		border-radius: 0.3rem;
	}
</style>

<!-- 公共js -->
<script src="../../assets/js/jquery-1.11.3.js"></script>
<script src="../../assets/js/xys.js"></script>

</head>
<body>
	<div class="main_container">
		<img src="<?php echo $headimgurl ?>" class="headimg">
		<div class="nickname"><?php echo $nickname ?></div>
		<p class="help">您的好友邀请您一起</p>
		<p class="help">参与包邮赠书活动</p>
		<img src="../<?php echo $gzhPic?>" style="width: 80%;">
		<div class="help_btn green" >
			<img class='help_btn_img' src="../images/up.gif">长按识别 立即参与
		</div>
	</div>
</body>
<script src='http://res.wx.qq.com/open/js/jweixin-1.0.0.js'></script>
<script>
	wx.config({
	    debug		: false,
	    appId		: '<?php echo $signPackage["appId"];?>',
		timestamp	: '<?php echo $signPackage["timestamp"];?>',
		nonceStr	: '<?php echo $signPackage["nonceStr"];?>',
		signature	: '<?php echo $signPackage["signature"];?>',
	    jsApiList	: [
						'onMenuShareTimeline',
						'onMenuShareAppMessage',
						'hideMenuItems',
						'showMenuItems',
						'hideAllNonBaseMenuItem'
					  ]
	});
	wx.ready(function(){
	    wx.error(function(res){
	        console.log(res);
	    });
	    
	    wx.hideAllNonBaseMenuItem();
		
		wx.showMenuItems({
		    menuList: ["menuItem:share:appMessage","menuItem:share:timeline"]
		});
		wx.hideMenuItems({
		    menuList: ["menuItem:copyUrl","menuItem:share:facebook","menuItem:openWithQQBrowser","menuItem:openWithSafari", "menuItem:share:qq","menuItem:share:QZone","menuItem:favorite", "menuItem:profile","menuItem:addContact","menuItem:refresh"] 
		});
		wx.onMenuShareAppMessage({
		    title   : "包邮赠书活动",
		    desc    : "您的好友邀请您一起参与包邮赠书活动!" ,
		    link    : "<?=$shareurl?>",
		    imgUrl  : "<?=$imgUrl ?>",
		    type    : 'link',
		    success: function () 
		    { 
		    	
		    },
		    cancel: function () { 
		    }
		});

		wx.onMenuShareTimeline({
			title  : "包邮赠书活动" ,
		    link   : "<?=$shareurl?>",
		    imgUrl : "<?=$imgUrl ?>",
		    success: function () 
		    { 
		    	
		    },
		    error: function()
		    {
			    
		    },
		    cancel: function () { 
		    }
		});
	});

</script>

<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?<?php echo $baiduCount ?>";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>
</html>