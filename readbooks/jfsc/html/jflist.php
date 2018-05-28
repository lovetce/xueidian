<?php
require_once '../checkGuide.php';
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
<title>我的积分</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/jslist.css">
</head>

<body>
	<div class="title">我的积分</div>
	<div class="jfcontent">
		<div class="jflist_info">
			<div><img class="userimg" src="../images/1.jpg"></div>
			<div class="info_box">
				<div class="nickname"></div>
				<div class="jfnum">
					<span>剩余积分：</span><span class="numcolor"></span>
				</div>
			</div>
			<div class="info_right">
				<div class="dhclick">兑换商品</div>
			</div>
		</div>
		<div class="jflist">
<!-- 			<div class="jflist_item">
				<div class="item_left">
					<div class="item_l_title">邀请好友</div>
					<div class="item_l_time">2018-01-31</div>
				</div>
				<div class="item_right">
					<div class="jscolor"><i class="addicon">+</i>100积分</div>
				</div>
			</div> -->
	
		</div>
	</div>
</body>
<script src="../../assets/js/jquery-1.11.3.js"></script>
<script src="../../assets/js/xys.js"></script>
<script type="text/javascript">
//下滑加载
var totalheight = 0 ;						// 总高度
var page		= 1 ;						// 分页
var uid			= <?php echo $auth_uid?>;	// 用户id

$(function(){
    userInfo();	
	spList(page);
	
	$(".dhclick").on("click",function(){
		window.location.href="index.php?appid=<?php echo $appid?>";
	})
})
//获取滚动条当前的位置 
function getScrollTop() { 
var scrollTop = 0; 
if (document.documentElement && document.documentElement.scrollTop) { 
scrollTop = document.documentElement.scrollTop; 
}else if (document.body) { 
scrollTop = document.body.scrollTop; 
} 
return scrollTop; 
} 

//获取当前可视范围的高度 
function getClientHeight() { 
	var clientHeight = 0; 
	if (document.body.clientHeight && document.documentElement.clientHeight) 
	{ 
		clientHeight = Math.min(document.body.clientHeight, document.documentElement.clientHeight); 
	}
	else 
	{ 
		clientHeight = Math.max(document.body.clientHeight, document.documentElement.clientHeight); 
	} 
	return clientHeight; 
} 

//获取文档完整的高度 
function getScrollHeight() 
{ 
	return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight); 
}

window.onscroll = function () 
{
	 if (getScrollTop() + getClientHeight() == getScrollHeight()) {
		 page++
		 spList(page);
	 }
}

// 用户信息
function userInfo(){
	$.ajax({
		type:'GET',
		url:'../userInfo.php',
		data:{method:'getUserInfo',uid:uid,appid:'<?php echo $appid?>'},
		dataType:"json",
		success:function(data){
			var  userInfo = data;
			$(".nickname").text(userInfo.username);
			$(".numcolor").text(userInfo.score);
			$(".userimg").attr("src",userInfo.face);
		}
	})
}
//积分列表
function spList(page){
	$.ajax({
		type:'GET',
		url:'../userInfo.php',
		data:{method:'getMyScoreList',page:page,uid:uid,appid:'<?php echo $appid?>'},
		dataType:"json",
		success:function(data){
			var  list = data.data;
			listContent(list)
		}
	})
}
// 请求列表
function listContent(list){

	for(var i=0,html="";i<list.length;i++){		
		html+='<div class="jflist_item">';
		html+='<div class="item_left">';
		if(list[i].type==1){
			html+='<div class="item_l_title">邀请好友&nbsp;&nbsp;&nbsp;('+list[i].q_name +')</div>';
		}else if(list[i].type==2){
			html+='<div class="item_l_title">关注公众号</div>';
		}else if(list[i].type==3){
			html+='<div class="item_l_title">每日签到</div>';
		}else if(list[i].type==4){
			html+='<div class="item_l_title">访问链接</div>';
		}else if(list[i].type==5){

			var gift_name = "" ;
			gift_name = list[i].gift_name.substr(0,6);
			
			html+='<div class="item_l_title">积分兑换&nbsp;&nbsp;&nbsp;('+gift_name +'...)</div>';
		}else if(list[i].type==6){
			html+='<div class="item_l_title">进群加分</div>';
		}else if(list[i].type==-2){
			html+='<div class="item_l_title">取消关注&nbsp;&nbsp;&nbsp;('+list[i].q_name +')</div>';
		}
		
		
		html+='<div class="item_l_time">'+list[i].crtime+'</div>';
		html+='</div>';
		html+='<div class="item_right">';	
		if(list[i].type==5 || list[i].type== -2){
			html+='<div class="delcolor"><i class="delicon">-</i>'+list[i].score+'积分</div>';
		}else {
			html+='<div class="addcolor"><i class="addicon">+</i>'+list[i].score+'积分</div>';
		}
		html+='</div>';
		html+='</div>';
	}
	$('.jflist').append(html);
}
</script>
</html>
