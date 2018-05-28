<?php
//created by chenhl,2016-01-20,解密后的文件
//modified by chenhl,2016-01-21,支持多域名，多公众号
class JSSDK {
    private $spackage = array();
    private $db = null;

    public function __construct($spackage, $db) {
        $this->spackage = $spackage;
        $this->db = $db;
    }

    public function getSignPackage($bz) {
        $jsapiTicket = $this->getJsApiTicket($bz);

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);
        $appid = $this->spackage['appid'];
        if ($bz == 1)
        {
            $appid = $this->spackage['share_appid'];
        }

        $signPackage = array(
            "appId"     => $appid,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket($original_id) 
    {
        $host = "r-wz95c747eda6f9d4.redis.rds.aliyuncs.com";
		$port = 6379;
		$pwd ="Bstosb6vuIA";
		$redis = new Redis();    
		if ($redis->connect($host, $port) == false) {
			die($redis->getLastError());
		}
		if ($redis->auth($pwd) == false) {
			die($redis->getLastError());
		}
		$key = "WX_CONFIG:OriginalId:".$original_id;		
        $data = json_decode($redis->get($key),true);
        
        return $data['jsapi_ticket'];        
    }

    public function getToken($original_id) {

        // $appid = $this->spackage['appid'];
        // $appsecret = $this->spackage['appsecret'];
        // if ($bz == 1)
        // {
        //     $appid = $this->spackage['share_appid'];
        //     $appsecret = $this->spackage['share_appsecret'];
        // }
       
        // $data = $this->db->get("token",array("expire_time","token(access_token)"),array("AND" => array("appid"=>$appid,'share'=>$bz,'type'=>'token') ));


        // if($data){
        //     if ($data['expire_time'] < time()) {
        //         $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        //         $res = json_decode($this->httpGet($url));
        //         $token = $res->access_token;
        //         if($token){
        //             $data['expire_time'] = time() + 7000;
        //             $data['access_token'] = $token;
        //             $expire_time =  $data['expire_time'];
        //             $this->db->update("token", array("expire_time"=>$expire_time,"token"=>$token),array("AND" => array("appid"=>$appid,'share'=>$bz,'type'=>'token') ));
        //         }
        //     }
        // }else{
        //     $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
        //     $res = json_decode($this->httpGet($url));
        //     $token = $res->access_token;
        //     if($token){
        //         $data['expire_time'] = time() + 7000;
        //         $data['access_token'] = $token;
        //         $expire_time =  $data['expire_time'];
        //         //$this->db->query("INSERT INTO iweite_huace_token (expire_time,token,appid,share,type) SELECT '$expire_time','$token','$appid','$bz','token' FROM DUAL WHERE NOT EXISTS (SELECT * FROM iweite_huace_token WHERE  appid='$appid' and share='$bz' and type='token')");
        //         $this->db->query("INSERT INTO iweite_huace_token (expire_time,token,appid,share,type) VALUES ('$expire_time','$token','$appid',$bz,'token'  )");
        //     }
        // }
        $host = "r-wz95c747eda6f9d4.redis.rds.aliyuncs.com";
		$port = 6379;
		$pwd ="Bstosb6vuIA";
		$redis = new Redis();    
		if ($redis->connect($host, $port) == false) {
			die($redis->getLastError());
		}
		if ($redis->auth($pwd) == false) {
			die($redis->getLastError());
		}
		$key = "WX_CONFIG:OriginalId:".$original_id;		
        $data = json_decode($redis->get($key),true);
        
        return $data['access_token'];
    }


    public function getCardTicket($bz) {
        //global IWEITE_ROOT;

        //modified by chenhl,2016-01-21,支持多域名、多公众号
        // $filename = $this->spackage['wxdir'].'/api_ticket.json';
        // if ($bz == 1)
        // {
        //     $filename = $this->spackage['wxdir'].'/api_ticket_share.json';
        // }
        // $data = json_decode(file_get_contents($filename));
        // if ($data->expire_time < time()) {
        //     $accessToken = $this->getToken($bz);
        //     $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$accessToken."&type=wx_card";
        //     $res = json_decode(https_request($url));
        //     $card_ticket = $res->ticket;
        //     if ($card_ticket) {
        //         $data->expire_time = time() + 7000;
        //         $data->ticket = $card_ticket;

        //         //modified by chenhl,2016-01-21,支持多域名、多公众号
        //         $fp = fopen($filename, "w");
        //         fwrite($fp, json_encode($data));
        //         fclose($fp);
        //     }
        // } else {
        //     $card_ticket = $data->ticket;
        // }
        // return $card_ticket;
        $host = "r-wz95c747eda6f9d4.redis.rds.aliyuncs.com";
		$port = 6379;
		$pwd ="Bstosb6vuIA";
		$redis = new Redis();    
		if ($redis->connect($host, $port) == false) {
			die($redis->getLastError());
		}
		if ($redis->auth($pwd) == false) {
			die($redis->getLastError());
		}
		$key = "WX_CONFIG:OriginalId:".$original_id;		
        $data = json_decode($redis->get($key),true);
        
        return $data['jsapi_ticket'];
    }


    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }
}
//begin:modified by chenhl,2016-01-21,支持多域名、多公众号
$rt= substr(dirname(__FILE__), 0, -7);
require_once $rt.'./include/common_new.inc.php';

$spackage1 = array(
    'appid'=>$_WEITE['appid'],
    'appsecret'=>$_WEITE['appsecret'],
    'original_id'=>$_WEITE['original_id'],
    'wxdir'=>$wxdir,
    'share_appid'=>$_WEITE['share_appid'],
    'share_appsecret'=>$_WEITE['share_appsecret'],
    'share_original_id'=>$_WEITE['share_original_id'],
);
$medb = new medoo(array(
    // 必须配置项
    'database_type' => 'mysql',
    'database_name' => $dbname,
    'server' => $dbhost,
    'username' => $dbuser,
    'password' => $dbpw,
    'charset' => $dbcharset,
    'prefix' => $tablepre, // 可选，定义表的前缀
));
$jssdk = new JSSDK($spackage1, $medb);

//end:modified by chenhl,2016-01-21,支持多域名、多公众号
$signPackage = $jssdk->GetSignPackage($_WEITE['original_id']);
$returnaccess = $jssdk->getToken($_WEITE['original_id']);
if ((!empty($_WEITE['share_appid'])) && ($_WEITE['appid'] != $_WEITE['share_appid']) && ($_WEITE['original_id'] != $_WEITE['share_original_id']))
{
    $signPackage = $jssdk->GetSignPackage($_WEITE['share_original_id']);
    //$returnaccess = $jssdk->getToken(1);
}

?>