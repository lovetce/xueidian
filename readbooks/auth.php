<?php
/*身份验证文件
 * 使用方法：   1.引用文件：require_once '../../auth.php';
 *        2.验证失败会直接跳转到用户授权页面进行授，授权成功后可直接通过$userinfo获取用户信息，这一步使用者无需关注，可直接跳转到第三步;
 *        3.直接使用$userinfo获取用户信息，$userinfo是一个json数组，格式如下
 *           "openid" => "OPENID",  
             "nickname" => 'NICKNAME',   
             "sex" => "1",   
             "province" => "PROVINCE",   
             "city" => "CITY",   
             "country" => "COUNTRY",    
             "headimgurl" =>    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",  
            "privilege" => "PRIVILEGE1",    
             "unionid"=> "o6_bmasdasdsad6_2sgVt7hMZOPfL" 
 * 
 * */
session_start();
header("Content-type: text/html; charset=utf-8");
define('WEB_ROOT', substr(dirname(__FILE__), 0, -7));
//require_once $_SERVER["ROOT_DOCUMENT"].'include/common.inc.php';
$appid = $_WEITE['appid'];
$appsecret = $_WEITE['appsecret'];
$tzurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=http://www.7893812.cn/wx_auth.php&response_type=code&scope=snsapi_userinfo&state=wxauthstate#wechat_redirect";
if (UNIT_TEST)
{
//    $tzurl = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/mbxt/wx_auth.php';
}


$code = isset($_GET["code"]) ? $_GET["code"] : '';
$state = isset($_GET["state"]) ? $_GET["state"] : '';
$userinfo = null;
$ckurl = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
$openid = isset($_COOKIE['auth_openid']) ? $_COOKIE['auth_openid'] : '';
if (!empty($openid))
{
    $access_token = getaccesstokenfromcache($db, $tablepre, $appid, $openid);
    if (empty($access_token))
    {
        setcookie('auth_ckurl', $ckurl, time()+31536000, "/");
        header("location: $tzurl");
        exit("3");
        
    }
    else
    {
        $userinfo = getwuserinfo($openid, $access_token);
        $rs1 = $db->fetch_first("select tid, access_token, expires_in, refresh_token from {$tablepre}openid where appid='$appid' and openid='$openid' limit 1");
        $tid = $rs1['tid'];
        $userinfo['uid'] = $tid;
        if (empty($userinfo))
        {
            setcookie('auth_ckurl', $ckurl, time()+31536000, "/");
            header("location: $tzurl");
            exit("4");
            
        }
        updateuserinfo($db, $tablepre, $appid, $userinfo);
    }
}
else
{
    setcookie('auth_ckurl', $ckurl, time()+31536000, "/");
    header("location: $tzurl");
    exit("5:". $ckurl);
    
}


//value与key都可输出 
/*
foreach($userinfo as $key => $value) 
{ 
    echo  $key."=>".$value; 
} 
*/

function getaccesstokeninfo($db, $tablepre, $appid, $secret, $code)
{
    $rtn = null;
    $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=".$code."&grant_type=authorization_code";
    if (UNIT_TEST)
    {
        $wxret =  array(
        "access_token" => "ACCESS_TOKEN2",    
        "expires_in"=> 7200,    
        "refresh_token"=> "REFRESH_TOKEN2",    
        "openid"=> "OPENID",    
        "scope"=> "SCOPE"        
        );
        
        
    }
    else
    {
        $wxret = https_request($url);
        $wxret = json_decode($wxret, true);
    }
    
    
    if (isset($wxret['access_token']))
    {
        $rtn = $wxret;
    }
    return $rtn;
}

function getaccesstokenfromcache($db, $tablepre, $appid, $openid)
{
    $rlt = null;
    $rs = $db->fetch_first("select access_token, expires_in, refresh_token from {$tablepre}openid where appid='$appid' and openid='$openid' limit 1");
    if (!empty($rs))
    {
        $sytime = time() - $rs['expires_in'];
        if($sytime > 7000)
        {
            $refresh_token = $rs['refresh_token'];
            $tokeninfo = refresh_token($appid, $refresh_token);
            if(!empty($tokeninfo))
            {
                $db->query("update {$tablepre}openid set access_token = '" . $tokeninfo['access_token']. "', expires_in = " . time() . ", refresh_token = '" . $tokeninfo['refresh_token']. "' where openid='" .$openid. "' and appid = '" .$appid. "'");
                $rlt = $tokeninfo['access_token'];
            }
        }
        else
        {
            
            $rlt = $rs['access_token'];
        }
    }
    return $rlt;
}

//刷新token，失败则返回null
function refresh_token($appid, $refresh_token)
{
    $rtn = null;
    $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$appid&grant_type=refresh_token&refresh_token=$refresh_token";
    if (UNIT_TEST)
    {
        $wxret =  array(
            "access_token" => "ACCESS_TOKEN",
            "expires_in"=> 7200,
            "refresh_token"=> "REFRESH_TOKEN",
            "openid"=> "OPENID",
            "scope"=> "SCOPE"
        );
        
    }
    else
    {
        $wxret = https_request($url);
        $wxret = json_decode($wxret, true);
        
    }

    if (isset($wxret['access_token']))
    {
        $rtn = $wxret;
    }
    return $rtn; 
}

function getwuserinfo($openid, $access_token) 
{
    $rtn = null;
    //$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx7b1489cbe2beda82&secret=fd8f763e39e70a3dcfd67260a0ad9392&code=".$code."&grant_type=authorization_code";
    $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
    //https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx7b1489cbe2beda82&secret=fd8f763e39e70a3dcfd67260a0ad9392&code=CODE&grant_type=authorization_code
    
    if (UNIT_TEST)
    {
        $wxret =  array(
            "openid" => "OPENID",  
             "nickname" => 'NICKNAME',   
             "sex" => "1",   
             "province" => "PROVINCE",   
             "city" => "CITY",   
             "country" => "COUNTRY",    
             "headimgurl" =>    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46",  
            "privilege" => "PRIVILEGE1",    
             "unionid"=> "o6_bmasdasdsad6_2sgVt7hMZOPfL",
        );
        
    }
    else
    {
        $wxret = https_request($url);
        $wxret = json_decode($wxret, true);
    }
    
    if (isset($wxret['openid']))
    {
        $rtn = $wxret;
        
    }
    return $rtn;
}

function updateuserinfo($db, $tablepre, $appid, $userinfo)
{
    $openid = $userinfo['openid'];
    $sql = "update {$tablepre}openid set username = '" . $userinfo['nickname']. "',  face = '" . $userinfo['headimgurl']."',  city = '" . $userinfo['city']. "' where openid='" .$openid. "' and appid = '" .$appid. "'";
    $db->query($sql);
}

?>

