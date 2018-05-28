<?php 

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
<title>免费生活用品包邮送</title>
<link rel="stylesheet" href="assets/css/css.css">
</head>

	<style>
        .img{
	       width:100%;
        }
    </style>

<body>
	<img class="img" src="huodon_images/ti_1.jpg" />
</body>
<script src="assets/js/jquery-1.11.3.js"></script>
<script src="assets/js/xys.js"></script>

<script type="text/javascript">
	var img=[
			'huodon_images/jfsc0041.jpg'
		     ];
    var img_src = 0;
    img_src = Math.floor(Math.random()*img.length);
    $(".img").attr("src",img[img_src]);
</script>

</html>
