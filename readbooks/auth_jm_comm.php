<?php

/*身份验证文件，静默授权，统一身份认证
 * 1.统一到服务器上认证
 * 2.需要在 site_conf文件中配置authurl
 * */
session_start();
header("Content-type: text/html; charset=utf-8");
require_once (dirname(__FILE__) . "/wechat.class.php");
require_once (dirname(__FILE__) . '/include/common_new.inc.php');

//require_once IWEITE_ROOT . './source/include/fun_url.php';
define('AUTH_UT', false);
//取参数
$urlpara = $_SERVER["REQUEST_URI"];
$thisdomain = $_SERVER['HTTP_HOST'];
$maindomain = $thisdomain;
$cookiename = $site_uid . "_auth_unionid";
$authurl = $site_conf['authinfo']['jm_url'];
$appid = $site_conf['authinfo']['appid'];


//分解url参数
$para_arr = parse_url($urlpara);
$arr_query = null;
$opid = null;
if (isset($para_arr['query']))
{
    $arr_query = convertUrlQuery($para_arr['query']);
   
    if (array_key_exists('rtnuserinfo', $arr_query))
    {
        $rtnuserinfo = $arr_query['rtnuserinfo'];
        unset($arr_query['rtnuserinfo']);
        $rtnuserinfo = urldecode($rtnuserinfo);
        $userinfo = json_decode($rtnuserinfo, true);
        
        
    }
}

$para = getUrlQuery($arr_query);
$para = empty($para) ? "" : ("?" . $para);

$mainurl = 'http://'. $maindomain. $para_arr['path'] . $para;

//传过去的参数如果带?号，需要编码
$mainurl_de = urlencode($mainurl); 
$tzurl =  $authurl . "?placeurl=$mainurl_de";

//$rtnuserinfo = isset($_GET["rtnuserinfo"]) ? $_GET["rtnuserinfo"] : '';
$pid = isset($_GET["pid"]) ? intval($_GET["pid"]) : 0;
$crtime = time();
if ($userinfo)
{
    $dbauthtmp = new dbstuff;
    $dbauthtmp->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8");
    if (!$dbauthtmp)
    {
        exit("连接数据库失败");
    }
    $unionid = $userinfo['unionid'];
    $openid = $userinfo['openid'];
    if (empty($unionid))
    {
        $unionid = "~no~" . $openid;
    }
    $rs = $dbauthtmp->fetch_first("select id, unionid from {$tablepre}wx_unionid where unionid='$unionid' order by id desc limit 1");
    if (!empty($rs))
    {
        $uid = $rs['id'];
    }
    else
    {
        
        $sql = "insert into {$tablepre}wx_unionid(unionid,crtime,pid) value('$unionid',$crtime, $pid)";
        //exit($sql);
        $ret = $dbauthtmp->query($sql);
        $uid = $dbauthtmp->insert_id($ret);
    }
    
    $rs = $dbauthtmp->fetch_first("select tid from {$tablepre}wx_openid where uid = $uid order by tid desc limit 1");
    if (empty($rs))
    {
        $sql = "insert into {$tablepre}wx_openid(uid, appid, openid, unionid, crtime, snsapi) value($uid,'$appid','$openid','$unionid',$crtime,0)";
        $ret = $dbauthtmp->query($sql);
        $tid = $dbauthtmp->insert_id($ret);
    }
    else
    {
        $tid = $rs['tid'];
    }
    
    setcookie($cookiename, $unionid, time()+31536000, "/");
    $headerurl = $mainurl;
    $exitmsg = 'success';
    
}
else 
{
    
    $unionid = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
    
    if (!empty($unionid))
    {
        $dbauthtmp = new dbstuff;
        $dbauthtmp->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8");
        if (!$dbauthtmp)
        {
            exit("连接数据库失败");
        }
    
        //$rs = $dbauthtmp->fetch_first("select id, unionid from {$tablepre}wx_unionid where unionid='$unionid' order by id desc limit 1");
        $rs = $dbauthtmp->fetch_first("SELECT u.id, u.unionid, o.tid, o.openid
            FROM {$tablepre}wx_unionid u, {$tablepre}wx_openid o
            WHERE u.unionid='$unionid' AND o.uid = u.id ORDER BY u.id DESC LIMIT 1");
    
        if ($rs)
        {
            $userinfo["uid"] = $rs['id'];
            $userinfo["tid"] = $rs['tid'];
            $userinfo["unionid"] = $rs['unionid'];
            $userinfo["openid"] = $rs['openid'];
            $userinfo["ukey"] = substr($unionid, -8);
        }
        else    //查询失败则跳转到授权页面
        {
            $headerurl = $tzurl;
            $exitmsg = 'success';
        }
	
    }
    else 
    {
        $headerurl = $tzurl;
        $exitmsg = 'success';
    }
}


if (!empty($dbauthtmp))
{
    $dbauthtmp->close();
}
if (!empty($headerurl))
{
    header("location: $headerurl");
    exit();
}
if (!empty($exitmsg))
{
    exit($exitmsg);
}
?>
