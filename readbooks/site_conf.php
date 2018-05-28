<?php
return array(    
    'xxx'=>'aa',//主域名
    'xxx'=>'aa', //跳转域名
    
    'aa' => array(
        'web_title' => '',                                   //标题
        'appid' => '',                     //appid
        'appsecret' => '',   //密钥
        'original_id' => '',
        'encodingaeskey' => '',
        'token' => 'yixiaowai',                    //token
        'maindomain' => 'xxx', 		   //主域名，网页授权和负载可能要反向查域名
        'tzdomain' =>'xxx',//跳转域名
        'guanzhu_url' => '', //关注链接
        'guanzhu_pic' => '',
        'needtimes' => 10,//需要助力次数
        'hyscore'=>100,
        'gzscore'=>100,
        'imgurl'=>'http://xxx/jfsc/images/2.jpg',//图片
        'subscribe'  => 'fxmall',
        'hy.mdaling.cn'=>array(
            'share_appid' => '',
            'share_appsecret' => '',
            'share_original_id' => '',
        ),
    
        'domains' => array('xxx','xxx'),
    
        //数据库连接信息
        'dbinfo' => array(
            'pubcon' => '',  //外网连接地址
            'dbuser' => '',
            'dbpw' => '',
            'dbname' => ''
        ),
    
        //统一授权，配置authurl
        'authinfo' => array(
            'url' => 'http://xxx/auth_comm.php',
            'jm_url' => 'http://xxx/auth_comm.php',
            'domain' => '',
            'appid' => '',
            'appsecret' => '',
            'token' => '',
        ),
    ),    
);