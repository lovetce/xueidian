<?php
/*身份验证文件
 * create by chenhl 2016-08-18
 * 使用方法：   1.引用文件：require_once 'auth2.php';
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

网页授权获取用户信息过程：
1 第一步：用户同意授权，获取code
2 第二步：通过code换取网页授权access_token
3 第三步：刷新access_token（如果需要）
4 第四步：拉取用户信息(需scope为 snsapi_userinfo)
 * */
session_start();
header("Content-type: text/html; charset=utf-8");
define('AUTH_STATE', 'wxauthstate');
require_once IWEITE_ROOT . "./wechat.class.php";
//require_once IWEITE_ROOT . './source/include/fun_url.php';

//define('WEB_ROOT', substr(dirname(__FILE__), 0, -7));
//require_once $_SERVER["ROOT_DOCUMENT"].'include/common.inc.php';
$appid = $_WEITE['appid'];
$appsecret = $_WEITE['appsecret'];
$cookiename = $site_uid . "_auth_openid";
$userinfo = null;

//取主域名，不能直接通过$_SERVER['SERVER_NAME']获取，因为启用负载后，获取到的是ip
$maindomain = $site_conf['maindomain'];

//取参数
$urlpara = $_SERVER["REQUEST_URI"];

//分解url参数
$para_arr = parse_url($urlpara);
$arr_query = null;
if (isset($para_arr['query']))
{
    $arr_query = convertUrlQuery($para_arr['query']);
    if (array_key_exists('state', $arr_query))
    {
        unset($arr_query['state']);
    }
    if (array_key_exists('code', $arr_query))
    {
        unset($arr_query['code']);
    }
}
$para = getUrlQuery($arr_query);
$para = empty($para) ? "" : ("?" . $para);
$mainurl = 'http://'. $maindomain. $para_arr['path'] . $para;
//var_dump($mainurl);
//用主域名组装用户当前访问的url，包括参数
//$mainurl =  'http://'. $maindomain . $_SERVER["REQUEST_URI"];

//组装获取用户网页授权链接
//$tzurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . $mainurl . "&response_type=code&scope=snsapi_userinfo&state=wxauthstate#wechat_redirect";
$dbauthtmp = new dbstuff;
$dbauthtmp->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8");
if (!$dbauthtmp)
{
    exit("连接数据库失败"); 
}
$options = array(
    'token'=>$_WEITE['token'],
    'appid'=>$appid,
    'appsecret'=>$appsecret,
    'database' => $dbauthtmp,
);

$we_chat = new Wechat($options);

//获取用户网页授权链接
$tzurl = $we_chat->getOauthRedirect($mainurl, AUTH_STATE);
$state = isset($_GET["state"]) ? $_GET["state"] : '';

//用户授权
if (!empty($state) && ($state == 'wxauthstate'))
{
    //第一步获取code
    $code = isset($_GET["code"]) ? $_GET["code"] : '';
    if (empty($code))
    {
        exit('用户授权：获取code失败！');
    }
    
    //第二步：通过code换取网页授权access_token
    $tokeninfo = $we_chat->getOauthAccessToken($code);
    if (empty($tokeninfo))
    {
        
        exit('用户授权：获取access_token失败！' . $we_chat->errCode);
    }
    else
    {   
        //更新用户信息
        $openid = $tokeninfo['openid'];
        $tid = 0;
        $rs = $dbauthtmp->fetch_first("select tid, access_token, expires_in, refresh_token from {$tablepre}openid where appid='$appid' and openid='$openid' limit 1");
        if (!empty($rs))
        {
            $sql = "update {$tablepre}openid set access_token = '" . $tokeninfo['access_token']. "', expires_in = " . time() . ", refresh_token = '" . $tokeninfo['refresh_token']. "' where openid='" .$openid. "' and appid = '" .$appid. "'";
            //exit($sql);
            $dbauthtmp->query($sql);
            $tid = $rs['tid'];
            
        }
        else
        {
            $sql = "insert into {$tablepre}openid(appid, openid, access_token, expires_in, refresh_token) value('".$appid. "','".$openid."','". $tokeninfo['access_token']."'," .time(). ", '" .$tokeninfo['refresh_token']."')";
            //exit($sql);
            $dbauthtmp->query($sql);
            
            $rs1 = $dbauthtmp->fetch_first("select tid, access_token, expires_in, refresh_token from {$tablepre}openid where appid='$appid' and openid='$openid' limit 1");
            $tid = $rs1['tid'];
            
        }
        
        //向微信服务器获取用户信息
        $userinfo['access_token'] = $tokeninfo['access_token'];
        $userinfo = $we_chat->getOauthUserinfo($tokeninfo['access_token'], $tokeninfo['openid']);
        if (empty($userinfo))
        {
            header("location: $tzurl");
            exit("2");
        }
        $userinfo['uid'] = $tid;
        updateuserinfo($dbauthtmp, $tablepre, $appid, $userinfo);
        setcookie($cookiename, $openid, time()+31536000, "/");
        header("location: $mainurl");
        exit();
    }  
}
else 
{
    $openid = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
    //var_dump($openid);
    //$openid = 'o9A2KxHM-W7no0uW6ZHiGaTNq1ek';
    //获取到cookie，先从缓存中查询用户信息
    if (!empty($openid))
    {
        $rs = $dbauthtmp->fetch_first("select tid, openid, username, sex, face, city, country, province from {$tablepre}openid where appid='$appid' and openid='$openid' order by tid desc limit 1");
        if ($rs)
        {
            $userinfo["tid"] = $rs['tid'];
            $userinfo["openid"] = $openid;
            $userinfo["nickname"] = $rs['username'];
            $userinfo["sex"] = $rs['sex'];
            $userinfo["province"] = $rs['province'];
            $userinfo["city"] = $rs['city'];
            $userinfo["country"] = $rs['country'];
            $userinfo["headimgurl"] = $rs['face'];
            $userinfo["unionid"] = '';
            $userinfo["privilege"] = "PRIVILEGE1";
        }
        else    //查询失败则跳转到授权页面
        {
            header("location: $tzurl");
            exit("6". $tzurl);
        }
    }
    else
    {
        header("location: $tzurl");
        exit("5:". $tzurl);
    }
}



function updateuserinfo($db, $tablepre, $appid, $userinfo)
{
    $openid = $userinfo['openid'];
    $userinfo['city'] =  str_replace("'","",$userinfo['city']);
    $userinfo['province'] =  str_replace("'","",$userinfo['province']);
    $userinfo['country'] =  str_replace("'","",$userinfo['country']);
    $sql = "update {$tablepre}openid set username = '" . $userinfo['nickname']. 
            "', face = '" . $userinfo['headimgurl'] . 
            "', city = '" . $userinfo['city'] . 
            "', sex = '" . $userinfo['sex'] .
            "', province = '" . $userinfo['province'] .
            "', country = '" . $userinfo['country'] .
            "' where openid='" . $openid . "' and appid = '" .$appid. "'";
    $db->query($sql);
}
?>
