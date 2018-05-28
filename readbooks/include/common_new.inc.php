<?php
/**
 * modified by chenhl,2016-08-24,优化后的公共文件
 */
//create by chenhl,2016-01-20,解密出来的文件
error_reporting(0); 
set_magic_quotes_runtime(0);
$mtime = explode(' ', microtime());
$discuz_starttime = $mtime[1] + $mtime[0];
define('UNIT_TEST', FALSE);

define('SYS_DEBUG', FALSE);
define('IN_IWEITE', TRUE);
define('IWEITE_ROOT', substr(dirname(__FILE__), 0, -7));
define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
!defined('CURSCRIPT') && define('CURSCRIPT', '');
if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_POST_FILES;
}
$TIMEZONE="Asia/Shangha";
if (PHP_VERSION >= '5.1' && !empty($TIMEZONE)){
    date_default_timezone_set($TIMEZONE);
}
if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])) {
	exit('Request attempted.');
}

require_once IWEITE_ROOT . './config.inc.php';
require_once IWEITE_ROOT.'./include/global.func.php';
getrobot();
if(defined('NOROBOT') && IS_ROBOT) {
	exit(header("HTTP/1.1 403 Forbidden"));
}
foreach(array('_COOKIE', '_POST', '_GET') as $_request) {
	foreach($$_request as $_key => $_value) {
		$_key{0} != '_' && $$_key = daddslashes($_value);
	}
}

if (!MAGIC_QUOTES_GPC && $_FILES) {
	$_FILES = daddslashes($_FILES);
}
$charset = $dbcharset = '';
$_DCOOKIE = $_DSESSION = $_DCACHE = array();

if(!empty($_SERVER['REQUEST_URI'])) {
	$temp = urldecode($_SERVER['REQUEST_URI']);
	if(strpos($temp, '<') !== false)
	exit('Request Bad url');
}
$prelength = 0;
/*
$prelength = strlen($cookiepre);
foreach($_COOKIE as $key => $val) {
	if(substr($key, 0, $prelength) == $cookiepre) {
		$_DCOOKIE[(substr($key, $prelength))] = MAGIC_QUOTES_GPC ? $val : daddslashes($val);
	}
}
*/
unset($prelength, $_request, $_key, $_value);
$timestamp = time();
//$uoload_url = dirname($_SERVER['PHP_SELF']) . '/';
require_once IWEITE_ROOT.'./include/db_mysql.class.php';
//$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
//$BASESCRIPT = basename($PHP_SELF);$boardurl = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api|archiver|wap)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/');
/*
if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	$onlineip = getenv('HTTP_CLIENT_IP');
} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	$onlineip = getenv('HTTP_X_FORWARDED_FOR');
} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	$onlineip = getenv('REMOTE_ADDR');
} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	$onlineip = $_SERVER['REMOTE_ADDR'];
}
preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
$onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
unset($onlineipmatches);
$http=$_SERVER["ALL_HTTP"];
if(isset($_COOKIE["StopScan"]) && $_COOKIE["StopScan"]){
	die("== WVS PLS Get Out！ ==");
}
if(strpos(strtolower($http),"acunetix")){
	setcookie("StopScan", 1);
	die("== WVS PLS Get Out ==");
}
*/
//$db = new dbstuff;
//$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz,true,"utf8");
require_once IWEITE_ROOT.'./include/medoo.php';
require_once IWEITE_ROOT . './sc/include/fun_url.php';
/*
$database = new medoo(array(
    // 必须配置项
    'database_type' => $database,
    'database_name' => $dbname,
    'server' => $dbhost,
    'username' => $dbuser,
    'password' => $dbpw,
    'charset' => $dbcharset,
    'prefix' => $tablepre, // 可选，定义表的前缀
));
*/
//$dbuser = $dbpw = $dbname = $pconnect = NULL;
//@include IWEITE_ROOT.'./cache/cache_inc.php';
/*
define('FORMHASH', formhash());
$iweite_auth_key = md5("www.iweite.wang");
$iweite_url=$_WEITE['web_weburl'];
list($iweite_pw,$iweite_secques,$iweite_uid) = empty($_DCOOKIE['iweite']) ? array('', '', 0) : daddslashes(explode("\t", authcode($_DCOOKIE['iweite'],'DECODE')), 1);
if($iweite_uid){
		if($iweite_secques==="iweite.com"){
			$rs= $db->fetch_first("select * from {$tablepre}admin  where id=$iweite_uid and password='$iweite_pw' limit 1");
			$admin_username=$rs["username"];
			$admin_uid=$rs["id"];
		}elseif($iweite_secques==="iweite.wsc"){
			$rs= $db->fetch_first("select * from {$tablepre}members  where id=$iweite_uid and password='$iweite_pw' limit 1");
			$weite_username=$rs["username"];
			$weite_uid=$rs["id"];
			$weite_face=$rs["face"];
		}
}	

$action=$action ? daddslashes($action) : "";
$id = intval(isset($id)) && is_numeric($id) ? intval($id) : 0;
$fid = intval(isset($fid)) && is_numeric($fid) ? intval($fid) : 0;
$sid = intval(isset($sid)) && is_numeric($sid) ? intval($sid) : 0;
$tid = intval(isset($tid)) && is_numeric($tid) ? intval($tid) : 0;
$page =intval(isset($page)) ? max(1, intval($page)) : 1;
$pid = intval(isset($pid)) && is_numeric($pid) ? intval($pid) : 0;
$classid = intval(isset($classid)) && is_numeric($classid) ? intval($classid) : 0;

//added by chenhl,2016-01-21,支持多域名，多公众号
$wxdir = IWEITE_ROOT.'./cache/'.$_WEITE['wx_cache']; 
if (!is_dir($wxdir)) mkdir($wxdir, 0777); // 使用最大权限0777创建文件夹
$wx_atta_pic = IWEITE_ROOT.'./uploads/pic/'.$_WEITE['wx_cache'];


//begin:modified by chenhl,2016-03-19,系统盘空间有限，extpic为数据盘，优先使用数据盘
$wx_ext_pic = IWEITE_ROOT.'./uploads/extpic';
if (is_dir($wx_ext_pic))
{
    $wx_atta_pic = $wx_ext_pic."/".$_WEITE['wx_cache'];
}
//end
if (!is_dir($wx_atta_pic)) mkdir($wx_atta_pic, 0777); // 使用最大权限0777创建文件夹

//$file_access_token = $wxdir.'/access_token.json';
//$file_jsapi_ticket = $wxdir.'/jsapi_ticket.json';
//$file_api_ticket = $wxdir.'/api_ticket.json';
//end chenhl,2016-01-21,支持多域名，多公众号
*/
?>