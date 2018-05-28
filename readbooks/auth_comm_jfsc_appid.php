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

//公众号ID
$gzhid		= isset($_REQUEST['appid'])			? 	trim($_REQUEST['appid'])	 	: 		'#';


//取参数
$urlpara 	= $_SERVER["REQUEST_URI"];
$thisdomain = $_SERVER['HTTP_HOST'];
$cookiename = $site_uid . "_auth_unionid_jfsc".$gzhid;
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
        $sql = "insert into {$tablepre}unionid(unionid,crtime,pid) value('$unionid',$crtime, $pid)";
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

    //更新用户openid信息及unionid的身份信息
    updateuserinfo($dbauthtmp, $tablepre, $tid, $userinfo, $uid);
    
    //获取用户的  统一授权  是否与  openid 绑定成功
    $is_lock = checkUserInfoKey($dbauthtmp, $tablepre, $gzhid, $userinfo);
    
    if($is_lock == 1)
    {
    	setcookie($cookiename, $unionid, time()+31536000, "/");
    	$headerurl = $mainurl;
    	$exitmsg   = 'success';
    }
    else
    {
    	echo "请点击  用户信息  更新菜单！！！";
    	die;
    }
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
        
        $rs = $dbauthtmp->fetch_first("SELECT u.id, u.unionid,  o.tid, o.openid, o.username, o.sex, o.face, o.snsapi,o.city,o.country,o.province,o.appid 
                                        FROM {$tablepre}unionid u, {$tablepre}openid o 
                                        WHERE u.unionid='$unionid' AND o.uid = u.id and o.appid = '$gzhid' ORDER BY u.id asc LIMIT 1");
        
        //非静默授权
        if (intval($rs['id'] > 0))
        {
        	$auth_info = array();
        	
			$auth_info["uid"] 		= $rs['id'];
			$auth_info["tid"] 		= $rs['tid'];
			$auth_info["unionid"] 	= $rs['unionid'];
			$auth_info["openid"] 	= $rs['openid'];
			$auth_info["ukey"] 		= substr($unionid, -8);
			$auth_info["nickname"] 	= $rs["username"];
			$auth_info["sex"] 		= $rs["sex"];
			$auth_info["headimgurl"] = $rs["face"];
			$auth_info["city"] 		= $rs["city"];
			$auth_info["country"] 	= $rs["country"];
			$auth_info["province"] 	= $rs["province"];
			$auth_info["appid"] 	= $rs["appid"];
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


//检查用户身份信息
function checkUserInfoKey($db,$tablepre,$gzhid,$userinfo)
{
	$is_lock = 0;
	
	$userinfo['nickname'] 	= stripslashes($userinfo['nickname']);
	$userinfo['nickname'] 	= str_replace("'","",$userinfo['nickname']);
	$userinfo['city'] 		= str_replace("'","",$userinfo['city']);
	$userinfo['province'] 	= str_replace("'","",$userinfo['province']);
	$userinfo['country'] 	= str_replace("'","",$userinfo['country']);
	
	$unionid = $userinfo['unionid']; 
	$openid  = $userinfo['openid'];
	$appid	 = isset($_REQUEST['appid'])			? 	trim($_REQUEST['appid'])	 	: 		'';
	$crtime  = time();
	
	
	$encryptStr = $userinfo['nickname']."#".$userinfo['sex']."#".$userinfo['city']."#".$userinfo['province']."#".$userinfo['country'];
	$encryptStr = hash("sha1",$encryptStr);
	
	
	//判断openid表所属的APPID  是否存在   对应的身份字符
    $sql = " select * from {$tablepre}openid where encrypt = '$encryptStr' and appid = '$gzhid' limit 1 ";
    $rs = $db->fetch_first($sql);

    //含有对应的 身份信息
    if(intval($rs['tid']) > 0)
    {
    	$tid = $rs['tid'];
    	
    	//设定为绑定字段
    	$sql = "update {$tablepre}openid set is_lock = 1 where tid = $tid limit 1";
    	$db->query($sql);
    	
    	//获取unionid 表中的  uid
    	$usql = "select * from {$tablepre}unionid where unionid = '$unionid' limit 1";
    	$urs = $db->fetch_first($usql);
    	if(intval($urs['id']) > 0)
    	{
    		//更新appid  对应的openid  表  的关联  uid
    		$uid = $urs['id'];
    		$sql = "update {$tablepre}openid set uid = $uid where tid = $tid limit 1";
    		$db->query($sql);
    	}
    	
    	//插入关联的id
    	$sql = "INSERT INTO {$tablepre}uid_link_appid(unionid, openid, appid, crtime ) SELECT '$unionid','$openid','$appid',$crtime FROM DUAL WHERE NOT EXISTS(SELECT unionid,openid,appid FROM {$tablepre}uid_link_appid WHERE unionid = '$unionid' and openid = '$openid' and appid = '$appid' )";
    	$ret = $db->query($sql);
    	$uid = $db->insert_id($ret);
    	
    	//改变状态值
    	$is_lock = 1;
    }
    
    return  $is_lock;
}



//更新用户数据
function updateuserinfo($db, $tablepre, $tid, $userinfo, $uid)
{
    $userinfo['nickname'] 	= stripslashes($userinfo['nickname']);
    $userinfo['nickname'] 	= str_replace("'","",$userinfo['nickname']);
    $userinfo['city'] 		= str_replace("'","",$userinfo['city']);
    $userinfo['province'] 	= str_replace("'","",$userinfo['province']);
    $userinfo['country'] 	= str_replace("'","",$userinfo['country']);
    
    $encryptStr = $userinfo['nickname']."#".$userinfo['sex']."#".$userinfo['city']."#".$userinfo['province']."#".$userinfo['country'];
    $encryptStr = hash("sha1",$encryptStr);
    
    //更新openid 表的身份信息
    $sql = " update {$tablepre}openid set snsapi = 1, username = '" . $userinfo['nickname'].
		   "', face = '" . $userinfo['headimgurl'] .
		   "', city = '" . $userinfo['city'] .
		   "', sex = '" . $userinfo['sex'] .
		   "', province = '" . $userinfo['province'] .
		   "', country = '" . $userinfo['country'] .
		   "', encrypt = '" .$encryptStr.
		   "' where tid = " . $tid;
    
    $db->query($sql);
    
    //更新unionid 表的身份信息
    $sql = " update {$tablepre}unionid set encrypt = '$encryptStr' where id = $uid  limit 1";
    $db->query($sql);
}

?>
