<?php

/*身份验证文件，静默授权，统一身份认证
 * 1.统一到服务器上认证
 * 2.需要在 site_conf文件中配置authurl
 * */
session_start();
header("Content-type: text/html; charset=utf-8");
require_once (dirname(__FILE__) . "/wechat.class.php");
require_once (dirname(__FILE__) . '/include/common_new.inc.php');

define('AUTH_UT', false);


//取参数
$urlpara 	= $_SERVER["REQUEST_URI"];
$thisdomain = $_SERVER['HTTP_HOST'];
$cookiename = $site_uid . "_auth_unionid_jfsc";
$authurl 	= $site_conf['authinfo']['url'];
$appid 		= $site_conf['authinfo']['appid'];


//分解url参数
$para_arr 	= parse_url($urlpara);
$arr_query 	= null;
$opid 		= null;

if (isset($para_arr['query']))
{
    $arr_query = convertUrlQuery($para_arr['query']);
   
    if (array_key_exists('rtnuserinfo', $arr_query))
    {
        $rtnuserinfo = $arr_query['rtnuserinfo'];
        unset($arr_query['rtnuserinfo']);
        $rtnuserinfo = urldecode($rtnuserinfo);
        $userinfo 	 = json_decode($rtnuserinfo, true);
    }
}

$para = getUrlQuery($arr_query);
$para = empty($para) ? "" : ("?" . $para);

$mainurl 	=  'http://'. $thisdomain. $para_arr['path'] . $para;
$mainurl_de =  urlencode($mainurl);
$tzurl 		=  $authurl . "?placeurl=$mainurl_de";

$crtime = time();
$pid 	= isset($_GET["pid"]) ? intval($_GET["pid"]) : 0;


if ($userinfo)
{
    $dbauthtmp = new dbstuff;
    $dbauthtmp->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8");
    if (!$dbauthtmp)
    {
        exit("连接数据库失败");
    }
    
    $unionid = $userinfo['unionid'];
    $openid  = $userinfo['openid'];
    
    if (empty($unionid))
    {
        $unionid = "~no~" . $openid;
    }
    
    $rs = $dbauthtmp->fetch_first("select id, unionid from {$tablepre}unionid where unionid='$unionid' order by id asc limit 1");
    if (!empty($rs))
    {
        $uid = $rs['id'];
    }
    else
    {
    	
    	$userinfo['nickname'] 	= stripslashes($userinfo['nickname']);
    	$userinfo['nickname'] 	= str_replace("'","",$userinfo['nickname']);
    	$userinfo['city'] 		= str_replace("'","",$userinfo['city']);
    	$userinfo['province'] 	= str_replace("'","",$userinfo['province']);
    	$userinfo['country'] 	= str_replace("'","",$userinfo['country']);
    	
    	$encryptStr = $userinfo['nickname']."#".$userinfo['sex']."#".$userinfo['city']."#".$userinfo['province']."#".$userinfo['country'];
    	$encryptStr = hash("sha1",$encryptStr);
    	
        $sql = "insert into {$tablepre}unionid(unionid,crtime,pid,encrypt) value('$unionid',$crtime, $pid,'$encryptStr')";
        $ret = $dbauthtmp->query($sql);
        $uid = $dbauthtmp->insert_id($ret);
    }
    
    $rs = $dbauthtmp->fetch_first("select tid, snsapi from {$tablepre}openid where uid = $uid order by tid desc limit 1");
    if (empty($rs))
    {
        $sql = "insert into {$tablepre}openid(uid, appid, openid, unionid, crtime, snsapi) value($uid,'$appid','$openid','$unionid',$crtime,1)";
        $ret = $dbauthtmp->query($sql);
        $tid = $dbauthtmp->insert_id($ret);
    }
    else
    {
        $tid = $rs['tid'];
    }

    updateuserinfo($dbauthtmp, $tablepre, $tid, $userinfo);
    setcookie($cookiename, $unionid, time()+31536000, "/");
    
    $headerurl = $mainurl;
    $exitmsg   = 'success';
    
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
        
        $rs = $dbauthtmp->fetch_first("SELECT u.id, u.unionid,  o.tid, o.openid, o.username, o.sex, o.face, o.snsapi,o.city,o.country,o.province 
                                        FROM {$tablepre}unionid u, {$tablepre}openid o 
                                        WHERE u.unionid='$unionid' AND o.uid = u.id ORDER BY u.id asc LIMIT 1");
        
        //非静默授权
        if (intval($rs['id']) > 0)
        {
            $userinfo["uid"] 		= $rs['id'];
            $userinfo["tid"] 		= $rs['tid'];
            $userinfo["unionid"] 	= $rs['unionid'];
            $userinfo["openid"] 	= $rs['openid'];
            $userinfo["ukey"] 		= substr($unionid, -8);
            $userinfo["nickname"] 	= $rs["username"];
            $userinfo["sex"] 		= $rs["sex"];
            $userinfo["headimgurl"] = $rs["face"];
            $userinfo["city"] 		= $rs["city"];
            $userinfo["country"] 	= $rs["country"];
            $userinfo["province"] 	= $rs["province"];
        }
        else
        {
            $headerurl 	= $tzurl;
            $exitmsg 	= 'success';
        }
    }
    else 
    {
        $headerurl 	= $tzurl;
        $exitmsg 	= 'success';
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



function updateuserinfo($db, $tablepre, $tid, $userinfo)
{
    $userinfo['nickname'] 	= stripslashes($userinfo['nickname']);
    $userinfo['nickname'] 	= str_replace("'","",$userinfo['nickname']);
    $userinfo['city'] 		= str_replace("'","",$userinfo['city']);
    $userinfo['province'] 	= str_replace("'","",$userinfo['province']);
    $userinfo['country'] 	= str_replace("'","",$userinfo['country']);
    
    $encryptStr = $userinfo['nickname']."#".$userinfo['sex']."#".$userinfo['city']."#".$userinfo['province']."#".$userinfo['country'];
    $encryptStr = hash("sha1",$encryptStr);
    
    $sql = " update {$tablepre}openid set snsapi = 1, username = '" . $userinfo['nickname'].
		   "', face = '" . $userinfo['headimgurl'] .
		   "', city = '" . $userinfo['city'] .
		   "', sex = '" . $userinfo['sex'] .
		   "', province = '" . $userinfo['province'] .
		   "', country = '" . $userinfo['country'] .
		   "', encrypt = '" .$encryptStr.
		   "' where tid = " . $tid;
    
    $db->query($sql);
}

?>
