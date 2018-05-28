<?php
	require_once '../checkGuide.php';
	$uid = $auth_uid;
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
<title>订单记录</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/order.css?id=1">
</head>

<body>
	<div class="title">我的订单</div>
	<div class="order_container">
		<!-- <div class="list_container">
			<div class="list_top">
				<div class="list_top_left">
					<img src="../images/logo.png">
					<div>积分商城</div>
				</div>
				<div class="list_top_right">等待卖家发货</div>
			</div>
			<div class="list_middle">
				<img src="../images/1.jpg" class="list_middle_img">
				<div class="list_middle_right">
					<div class="goods_intro">新品首发iPhone X 新品首发iPhone X 256G手机5.8英寸显示屏 银色/深空灰色  2色任选...</div>
					<div class="goods_times">2017-01-31 13:08:02</div>
				</div>
			</div>
			<div class="price_num">实付：6666积分</div>
			<div class="list_bottom">
				<div class="consult_btn">咨询物流信息</div>
			</div>
		</div> -->
	</div>
	<!-- 遮罩层 -->
	<div class="overlay">
	</div>
	<!-- 二维码 -->
	<div class="ewm_container">
		<img src="../images/kefu2.jpg">
		<p>长按识别添加客服微信  查看物流信息</p>
	</div>
</body>
<script src="../../assets/js/jquery-1.11.3.js"></script>
<script src="../../assets/js/xys.js"></script>
<script type="text/javascript">
	
	//点击遮罩层
	$(".overlay").on("click",function(){
		$(".overlay").hide();
		$(".ewm_container").hide();
	})
	//下滑加载
	var totalheight = 0 //总高度
	var page=1 //分页
	var isJiazai = true;
	$(function(){	
		getMyOrderList(page);
		$(document).on("click",".consult_btn",function(){
			console.log("tiaozhuan")
			$(".overlay").show();
			$(".ewm_container").show();
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
		if (document.body.clientHeight && document.documentElement.clientHeight) { 
			clientHeight = Math.min(document.body.clientHeight, document.documentElement.clientHeight); 
		}else { 
			clientHeight = Math.max(document.body.clientHeight, document.documentElement.clientHeight); 
		} 
		return clientHeight; 
	} 

	//获取文档完整的高度 
	function getScrollHeight() { 
		return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight); 
	}

	window.onscroll = function () 
	{
		if (getScrollTop() + getClientHeight() == getScrollHeight()) 
		{
			if(isJiazai)
			{
				page++;
				getMyOrderList(page);
			}
		}
	}

	//获取订单列表
	function getMyOrderList(page){
		$.ajax({
			type:'GET',
			url:'../orderInfo.php',
			data:{
				method:'getMyOrderList',
				page:page,
				uid:'<?=$uid?>',
				appid:'<?=$appid ?>'
			},
			dataType:"json",
			success:function(data){
				console.log(data);
				if(data.code == 0)
				{ 
					var  list = data.data;
					if(list.length > 0)
					{
						var listHtml = listContent(list);
						$(".order_container").append(listHtml);		
					}else
					{
						isJiazai = false;
					}
				}
				
			}
		})
	}
	// 请求列表
	function listContent(list){
		var html = '';
		for(var i=0,html="";i<list.length;i++){		
			html+='<div class="list_container">';
			html+='<div class="list_top">';
			html+='<div class="list_top_left">';
			html+='<img src="../images/logo.png">';
			html+='<div>积分商城</div>';
			html+='</div>';
			html+='<div class="list_top_right">';
			if(list[i].status == 1)
			{
				html += '等待卖家发货';
			}else if(list[i].status == 2)
			{
				html += '已发货';
			}else if(list[i].status == 3)
			{
				html += '已接收';
			}
			html+='</div>';
			html+='</div>';
			html+='<div class="list_middle">';
			html+='<img src="'+list[i].banner+'" class="list_middle_img">';
			html+='<div class="list_middle_right">';
			html+='<div class="goods_intro">'+list[i].name+'</div>';
			html+='<div class="goods_times">'+list[i].crtime+'</div>';
			html+='</div>';
			html+='</div>';
			if(list[i].status == 2){
				html+='<div class="price_num">'+list[i].wlname+'  单号：'+list[i].wlno+'</div>';
		    }
			html+='<div class="price_num">实付：'+list[i].score+'积分</div>';
			html+='<div class="list_bottom">';
			html+='<div class="consult_btn">咨询物流信息</div>';
			html+='</div>';
			html+='</div>';
			
		}
		return html;
	}
</script>
</html>
