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
<link rel="stylesheet" href="../css/index.css?id=2">
</head>

<body>
	<div class="wrap">
		<div class="title">商品列表</div>
		<div class="content_list">
			
		</div>
	</div>
</body>
<script src="../../assets/js/jquery-1.11.3.js"></script>
<script src="../../assets/js/xys.js"></script>
<script type="text/javascript">
//下滑加载
var totalheight = 0 //总高度
var page=1 //分页
$(function(){	
	spList(page);


	$(document).on("click",".list_item",function(){
		console.log("tiaozhuan")
		var id = $(this).attr("name");
		window.location.href='details.php?id='+id+"&appid=<?php echo $appid ?>"
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

window.onscroll = function () {
	 if (getScrollTop() + getClientHeight() == getScrollHeight()) {
		 page++
		 spList(page);
	 }
}

//商品列表
function spList(page){
	$.ajax({
		type:'GET',
		url:'../goodsInfo.php',
		data:{method:'getGoodsList',page:page},
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
		html+='<div class="list_item" name="'+list[i].id+'">';
		html+='<div class="item_banner">';
		html+='<img class="item_pic" src="'+list[i].banner+'">';
		html+='</div>';
		html+='<div class="item_title">'+list[i].name+'</div>';
		html+='<p class="item_price">';
		html+='<span class="red left"><i class="font30">'+list[i].score+'</i>积分</span>';
		//html+='<span class="gray right">已兑换'+list[i].exchange+'件</span>';
		html+='</p>';
		html+='</div>';
	}
	$('.content_list').append(html);
}
</script>
</html>
