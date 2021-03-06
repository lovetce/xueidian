<?php
/*身份验证文件，静默授权
 * create by chenhl 2016-08-18
 * 使用方法：   1.引用文件：require_once 'auth3_jm.php';
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
require_once (dirname(__FILE__) . "/wechat.class.php");
require_once (dirname(__FILE__) . '/include/common_new.inc.php');

//require_once IWEITE_ROOT . './source/include/fun_url.php';
define('AUTH_UT', false);

//define('WEB_ROOT', substr(dirname(__FILE__), 0, -7));
//require_once $_SERVER["ROOT_DOCUMENT"].'include/common.inc.php';
$appid = $_WEITE['appid'];
$appsecret = $_WEITE['appsecret'];
$cookiename = $site_uid . "_auth_openid";
$userinfo = null;

$maindomain = $site_conf['maindomain'];

//取参数
$urlpara = $_SERVER["REQUEST_URI"];

//分解url参数
$para_arr = parse_url($urlpara);
$arr_query = null;
$opid = null;
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
    
    //带了openid参数则将参数删除掉，以免泄漏
    if (array_key_exists('opid', $arr_query))
    {
        $opid = $arr_query['opid'];
        unset($arr_query['opid']);
    }
    
    //渠道
    if (array_key_exists('placedm', $arr_query))
    {
        $placedm = $arr_query['placedm'];
        unset($arr_query['placedm']);
    }
}

$para = getUrlQuery($arr_query);
$para = empty($para) ? "" : ("?" . $para);


//$placedomain = '';

//取当前域名
$thisdomain = $_SERVER['HTTP_HOST'];

//从缓存中获取openid
$openid = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';

//非授权域名，需要判断是否要跳转
if ($thisdomain != $maindomain)
{
    $placeurl = 'http://'. $thisdomain. $para_arr['path'] . $para;
   
    //如果带了openid参数则将参数写入cookie然后跳转到本页面
    if (!empty($opid))
    {
        setcookie($cookiename, $opid, time()+31536000, "/");
        header("location: $placeurl");
        exit();
    }
    else //没有带openid参数，则判断是否能从缓存中读到cookie，如果读取不到cookie则需要跳转，跳转之前先构造placedm参数，以便能跳回来  
    {
        $openid = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
        
        //这里构造placedm参数，后面跳转前会把该参数带给授权域名
        if (empty($openid))
        {
            $para = empty($para) ? ("?placedm=$thisdomain") : ($para . "&placedm=$thisdomain");
        }
                
    }
    
}

/* //不是渠道过来的域名则判断是否要跳转到渠道域
else
{
    echo("placedm=" . $placedm);
    exit();
    
    if (!empty($placedm))
    {
        if (!empty($opid))
        {
            $para = empty($para) ? ("?opid=" . $opid) : ($para . "&opid=" . $opid);
        }
        $placeurl = 'http://'. $placedm. $para_arr['path'] . $para;
        header("location: $placeurl");
        exit();
    }
    
} */

$mainurl = 'http://'. $maindomain. $para_arr['path'] . $para;

//获取用户网页授权链接
//$tzurl = Wechat::getOauthRedirect($mainurl, AUTH_STATE,"snsapi_base");
//组装获取用户网页授权链接
$tzurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid . "&redirect_uri=" . urlencode($mainurl) . "&response_type=code&scope=snsapi_base&state=wxauthstate#wechat_redirect";

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
        $unionid = '';
        if(array_key_exists('unionid',$tokeninfo))
        {
            $unionid = $tokeninfo['unionid'];
        }
        
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
            $sql = "insert into {$tablepre}openid(appid, openid, unionid, access_token, expires_in, refresh_token) value('".$appid. "','".$openid."','" . $unionid."','" . $tokeninfo['access_token']."'," .time(). ", '" .$tokeninfo['refresh_token']."')";
            //exit($sql);
            $dbauthtmp->query($sql);
            
            $rs1 = $dbauthtmp->fetch_first("select tid, access_token, expires_in, refresh_token from {$tablepre}openid where appid='$appid' and openid='$openid' limit 1");
            $tid = $rs1['tid'];
            
        }
        /*
        //向微信服务器获取用户信息
        $userinfo = $we_chat->getOauthUserinfo($tokeninfo['access_token'], $tokeninfo['openid']);
        if (empty($userinfo))
        {
            header("location: $tzurl");
            exit("2");
        }
        updateuserinfo($dbauthtmp, $tablepre, $appid, $userinfo);
        */
        $userinfo = array(
            'uid' => $tid,
            'openid' => $openid,
            'ukey' => substr($openid, -8),
        );
        
        setcookie($cookiename, $openid, time()+31536000, "/");
        
        //其它域名过来的页面，需要重新跳转到原始域名，把openid带过去，便于重构缓存
        if (!empty($placedm))
        {
            $para = empty($para) ? ("?opid=$openid") : ($para . "&opid=$openid");
            $tmpurl = 'http://'. $placedm. $para_arr['path'] . $para;
            header("location: $tmpurl");
            
        }
        else //不是渠道过来的，直接跳转到主域名
        {
            header("location: $mainurl");
        }
        exit();
    }  
}
else 
{
    $dbauthtmp = new dbstuff;
    $dbauthtmp->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8");
    if (!$dbauthtmp)
    {
        exit("连接数据库失败");
    }
    $openid = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
    //var_dump($openid);
    //$openid = 'o9A2KxHM-W7no0uW6ZHiGaTNq1ek';
    //获取到cookie，先从缓存中查询用户信息
    
    if (!empty($openid))
    {
        $rs = $dbauthtmp->fetch_first("select tid, openid, username, sex, face, city, country, province from {$tablepre}openid where appid='$appid' and openid='$openid' order by tid desc limit 1");
        if ($rs)
        {
            $userinfo["uid"] = $rs['tid'];
            $userinfo["tid"] = $rs['tid'];
            $userinfo["ukey"] = substr($openid, -8);
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
        if (AUTH_UT)
        {
            $userinfo["tid"] = 1;
            $userinfo["openid"] = "ov4D2vsnezbgB9Au7DkRLas85Myg_";
            $userinfo["ukey"] = substr($userinfo["openid"], -8);
            $userinfo["nickname"] = "测试";
            $userinfo["sex"] = 0;
            $userinfo["province"] = "湖南";
            $userinfo["city"] = "长沙";
            $userinfo["country"] = "中国";
            $userinfo["headimgurl"] = "url";
            $userinfo["unionid"] = '';
            $userinfo["privilege"] = "PRIVILEGE1";
        
        }
        else
        {
            header("location: $tzurl");
            exit("5:". $tzurl);
        }
        
    }
}



function updateuserinfo($db, $tablepre, $appid, $userinfo)
{
    $openid = $userinfo['openid'];
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
