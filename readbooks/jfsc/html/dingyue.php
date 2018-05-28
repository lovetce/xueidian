<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订阅下期</title>
    <script src="/assets/js/jquery.min-2.2.1.js"></script>
    <script src="/assets/js/sweetalert2/sweetalert2.js"></script>
    <link rel="stylesheet" href="/assets/js/sweetalert2/sweetalert2.min.css">
</head>
<body>    
    <script type="text/javascript">
       swal({
		  title: '订阅成功！',
		  text: '',
		  type: 'success',
		}).then(function() {
            WeixinJSBridge.call('closeWindow');
		});
    </script>
</body>
</html>