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
<title>积分商城</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/details.css">
</head>

<body>
	<div class="title">
		<div class="back_box"><img src="../images/btn_back.png" class="backimg"></div>
		<div class="title_word">商品详情</div>
	</div>
	<div class="banner">
		<img src="" class="bannerimg">
		<div class="banner_word"></div>
		<div class="banner_notice">
			<img src="../images/tip.png">
			<p>友情提示：兑换成功后请立即添加客服公众号【aaaa】</p>
		</div>
	</div>
	<div class="exchange">
		<div class="price"><span></span>积分</div>
		<div class="begin">立即兑换</div>
	</div>

	<!-- 遮罩层 -->
	<div class="overlay">
	</div>
	<!-- 填写信息 -->
	<div class="write_container">
		<div class="write_title">
			<div>填写收货信息</div>
			<img src="../images/close.png" class="closeimg">
		</div>
		<input type="number" placeholder="请输入手机号(必填)" class="input" id="input1">
		<input type="text" placeholder="请输入收件人(必填)" class="input" id="input2">
		<input type="text" placeholder="收货地址,尽量精确到省市区县镇乡(必填)" class="input" id="input3">
		<input type="text" placeholder="请输入备注(选填)" class="input" id="input4">
		<div class="submit">提交</div>
	</div>
	<!-- 兑换成功 -->
	<div class="help_overlay">
		<div class="help_box">
			<img src="../images/finish.png" class="finishimg">
			<p>兑换成功</p>
		</div>
	</div>
	<!-- 提示弹框 -->
	<div class="notice_overlay">
		<div class="notice_box">
			<p></p>
		</div>
	</div>
</body>
<script src="../../assets/js/jquery-1.11.3.js"></script>
<script src="../../assets/js/xys.js"></script>
<script type="text/javascript">

	var uid			= <?php echo $auth_uid?>;	// 用户id

	//接收页面传过来的参数
	GetRequest();
	var id = theRequest.id==null ? 0 : theRequest.id;//商品id
	var appid = theRequest.appid==null ? 0 : theRequest.appid;//公众号APPID

	//接收参数函数
	function GetRequest() {  
	   var url = location.search; //获取url中"?"符后的字串  
	   theRequest = new Object();  
	   if (url.indexOf("?") != -1) {  
	      var str = url.substr(1);  
	      strs = str.split("&");  
	      for(var i = 0; i < strs.length; i ++) {  
	         theRequest[strs[i].split("=")[0]]=decodeURI(strs[i].split("=")[1]);  
	      }  
	   }  
	}
	$(function(){
		getOneGoods(id);
	})
	//点击返回
	$(".back_box").on("click",function(){
		window.history.go(-1);
	})
	//点击兑换
	$(".begin").on("click",function(){

		if(id == 1 || id == 2)
		{
			alert("此商品不允许兑换");
		}
		else
		{
			$(".overlay").show();
			$(".write_container").show();
		}
	})
	//点击提交
	$(".submit").on("click",function(){
		var mobile = $("#input1").val();
		var name = $("#input2").val().trim();
		var addr = $("#input3").val().trim();
		var memo = $("#input4").val().trim();
		console.log("电话：",mobile);
		console.log("收件人：",name);
		console.log("地址：",addr);
		console.log("备注：",memo);
		if(mobile == ''||name == ''||addr == '')
		{
			$(".notice_overlay p").text("缺少必填参数");
			$(".notice_overlay").fadeIn(500).fadeOut(2000);
		}else
		{
			addOrder(id,name,addr,mobile,memo);
		}
	})
	//点击叉掉弹框
	$(".closeimg").on("click",function(){
		$("#input1").val("");
		$("#input2").val("");
		$("#input3").val("");
		$("#input4").val("");
		$(".overlay").hide();
		$(".write_container").hide();
	})
	//点击遮罩层
	$(".overlay").on("click",function(){
		$("#input1").val("");
		$("#input2").val("");
		$("#input3").val("");
		$("#input4").val("");
		$(".overlay").hide();
		$(".write_container").hide();
	})
	//获取商品详情
	function getOneGoods(id){
		$.ajax({
			type:'GET',
			url:'../goodsInfo.php',
			data:{
				method:'getOneGoods',
				id: id
			},
			dataType:"json",
			success:function(data){
				console.log(data);
				var name = data.data.name;//名称
				var	info = data.data.info;//信息
				var	score = data.data.score;//积分
				var	banner = data.data.banner;//图片
				var	exchange = data.data.exchange;//兑换数
				$(".bannerimg").attr("src",banner);
				$(".banner_word").text(name);
				$(".price>span").text(score);
			}
		})
	} 
	//积分兑换商品
	function addOrder(id,name,addr,mobile,memo){
		$.ajax({
			type:'GET',
			url:'../orderInfo.php',
			data:{
				method:'addOrder',
				uid: uid,						//用户id（必填）
				lid: id,						//商品id （必填）
				name: name,						//接收人姓名 （必填）
				addr: addr,						//接收人地址 （必填）
				mobile: mobile,					//接收人电话 （必填）
				memo: memo, 					//备注 （选填）
				appid:appid						//对应的公众号id（必填）
			},
			dataType:"json",
			success:function(data){
				console.log(data);
				$(".overlay").hide();
				$(".write_container").hide();
				$("#input1").val("");
				$("#input2").val("");
				$("#input3").val("");
				$("#input4").val("");
				// code：0 成功 
				// -1:商品下架
				// -2：积分不足
				// -3:缺少参数
				// -4：失败
				// -5：没有库存
				if(data.code == 0)
				{
					$(".help_overlay p").text("兑换成功");
					$(".help_overlay").fadeIn(500).fadeOut(2000);
				}else if(data.code == -1)
				{
					$(".notice_overlay p").text("商品已下架");
					$(".notice_overlay").fadeIn(500).fadeOut(2000);
				}else if(data.code == -2)
				{
					$(".notice_overlay p").text("您的积分不足");
					$(".notice_overlay").fadeIn(500).fadeOut(2000);
				}else if(data.code == -3)
				{
					$(".notice_overlay p").text("缺少必填参数");
					$(".notice_overlay").fadeIn(500).fadeOut(2000);
				}else if(data.code == -4)
				{
					$(".notice_overlay p").text("兑换失败");
					$(".notice_overlay").fadeIn(500).fadeOut(2000);
				}else if(data.code == -5)
				{
					$(".notice_overlay p").text("没有库存");
					$(".notice_overlay").fadeIn(500).fadeOut(2000);
				}
				
			}
		})
	} 
</script>
</html>
