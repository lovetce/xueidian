<?php
    //define('IWEITE_ROOT', dirname(__FILE__) + "/");
    $siteconflst = include IWEITE_ROOT . './site_conf.php';
    $site_uid = isset($siteconflst[$_SERVER['HTTP_HOST']]) ? $siteconflst[$_SERVER['HTTP_HOST']] : '';
    if (empty($site_uid))
    {
        $swid = isset($_GET["swid"]) ? $_GET["swid"] : "sp";
        $site_uid = $swid;
    }
    $site_conf = $siteconflst[$site_uid];
//    var_dump($site_conf);
//    die;
    
    // [CH] 以下变量请根据空间商提供的账号参数修改,如有疑问,请联系服务器提供商
	$dbhost = $site_conf['dbinfo']['pubcon'];	// 数据库服务器
	$dbuser = $site_conf['dbinfo']['dbuser'];	// 数据库用户名
	$dbpw = $site_conf['dbinfo']['dbpw'];		// 数据库密码
	$dbname = $site_conf['dbinfo']['dbname'];	// 数据库名
	//end:modified by chenhl,2016-01-21,支持多域名，多公众号
	
	
    // [CH] 如您对 cookie 作用范围有特殊要求, 或录不正常, 请修改下面变量, 否则请保持默认
	$tablepre = 'iweite_huace_';   	// 表名前缀, 同一数据库安装多个请修改此处
	$admin_fdir="admin/";      ///后台管理目录,控制图片上传的,不可修改<br />
	$cookiepre = 'iweite_huace_';			// cookie 前缀
	$cookiedomain = ''; 			// cookie 作用域
	$cookiepath = '/';			// cookie 作用路径
	
    // [CH] 小心修改以下变量, 否则可能导致无法正常使用

	$pconnect = 0;				// 数据库持久连接 0=关闭, 1=打开
	$database = 'mysql';			// 数据库类型，请勿修改
	$databasetype = 'mysql';
	$dbcharset = 'utf8';			// MySQL 字符集, 可选 'gbk', 'big5', 'utf8', 'latin1', 留空为按照字符集设定
	$charset = 'utf-8';			// 页面默认字符集, 可选 'gbk', 'big5', 'utf-8'
	$headercharset = 0;			// 强制页面使用默认字符集，可避免部分服务器空间页面出现乱码，一般无需开启。 0=关闭 1= 开启

	$bot_flag = $site_conf['bot_flag'];
	$auto_reply_flag = $site_conf['auto_reply_flag'];//是否自动回复
	$bot_app_key = $site_conf['bot_app_key'];
	$bot_app_secret = $site_conf['bot_app_secret'];
	
	////////////////////////////////////////
	/*$siteconflst = include IWEITE_ROOT.'./site_conf.php';
	$site_uid = $swid;
	if (empty($site_uid))
	{
	    $site_uid = isset($siteconflst[$_SERVER['HTTP_HOST']]) ? $siteconflst[$_SERVER['HTTP_HOST']] : 'yyxctest';
	}
	$site_conf = $siteconflst[$site_uid];
	*/
	$_WEITE['web_weburl'] = 'http://'.$_SERVER["HTTP_HOST"].'/';
	$_WEITE['web_title'] = $site_conf['web_title'];
	$_WEITE['wx_cache'] = $site_uid;                //微信缓存目录
	
	$_WEITE['domains'] = $site_conf['domains']; //域名
	$_WEITE['rand_domains'] = $site_conf['rand_domains']; //随机主域名
	$_WEITE['subscribe'] = $site_conf['subscribe'];
	
	/////微信公众号设置
	$_WEITE['appid'] = $site_conf['appid'];
	$_WEITE['appsecret'] = $site_conf['appsecret'];
	$_WEITE['original_id'] = $site_conf['original_id'];
	$_WEITE['encodingaeskey'] = $site_conf['encodingaeskey'];
	$_WEITE['token'] = $site_conf['token'];
	$_WEITE['share_appid'] = $site_conf[$_SERVER['HTTP_HOST']]['share_appid'];
	$_WEITE['share_appsecret'] =$site_conf[$_SERVER['HTTP_HOST']]['share_appsecret'];
	$_WEITE['share_original_id'] =$site_conf[$_SERVER['HTTP_HOST']]['share_original_id'];
	
	
	///关注公众号跳转连接
	$_WEITE['guanzhu_url'] = $site_conf['guanzhu_url'];
	$_WEITE['guanzhu_url_gd'] = $site_conf['guanzhu_url_gd'];
	//end:modified by chenhl,2016-01-21,支持多域名，多公众号
	
	
	/////取消关注则删除制作的所有的信息
	$_WEITE['web_isgz']=0;
	
	
	////模版图片调用路径，用来区分七牛和本地，固定参数不可以改
	$_WEITE['web_pic_url']=$_WEITE['web_weburl'].'template/';
	
	/////第三方七牛参数
	$_WEITE['qiniu_isok']=1;//是否开启
	$_WEITE['qiniu_weburl']='weixingrand03.top';//七牛解析域名
	$_WEITE['qiniu_fdir']='huodong1';//存储文件夹名
	$_WEITE['qiniu_access']='bM4p4ih1nvN7L7bHk4R5KCt_y3rO2UETOjfajhTH';//Access Key
	$_WEITE['qiniu_secret']='CaZj702FLZCHvT_wkQT_fXeOKZ4fCELTYRCGR_57';///Secret Key