<?php
/**
 * TP5版,jssdk类
 * 官方文档：http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
 * 微信支付：http://pay.weixin.qq.com/wiki/doc/api/index.php?chapter=9_1#
 * 官方示例：http://demo.open.weixin.qq.com/jssdk/sample.zip
 * 
 * 新增了调试模式,调用示例如下：
 * $jssdk = new Wxjssdk($appId, $appSecret);
 * $jssdk->debug = true;	//启用本地调试模式,将官方的两个json文件放到入口文件index.php同级目录即可!
 * $signPackage = $jssdk->GetSignPackage();
 */
namespace org\wechat;

class Wxjssdk {
  private $appId;
  private $appSecret;
  public $debug = false;
  public $parameters;//获取prepay_id时的请求参数

  //=======【curl超时设置】===================================
  //本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
  public  $CURL_TIMEOUT = 30;

  public  $prepay_id;


  public function __construct($appId, $appSecret) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
  }

  public function getSignPackage() {
    $jsapiTicket = $this->getJsApiTicket();
//    var_dump($_SERVER);
//    DIE;
  $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      //$url="http://wx.wxxuexi.top";
    $timestamp = time();
    $nonceStr = $this->createNonceStr();

      $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

      $signature = sha1($string);

      $signPackage = array(
          "appId"     => $this->appId,
          "nonceStr"  => $nonceStr,
          "timestamp" => $timestamp,
          "url"       => $url,
          "signature" => $signature,
          "rawString" => $string
      );
      return $signPackage;

//    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
//    $signature = sha1($string);
//
//
//    $signPackage = array(
//      "appId"     => $this->appId,
//      "nonceStr"  => $nonceStr,
//      "timestamp" => $timestamp,
//      "url"       => $url,
//      "signature" => $signature,
//      "rawString" => $string
//    );
//    return $signPackage;
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  /**
   * 获取JsApiTicket
   */
  private function getJsApiTicket() {

    //获取缓存
    if ($rs = cache('jsapi_ticket_'.$this->appId))  {
      return $rs;
    }

    //重新获取数据
    $accessToken = $this->getAccessToken();
    $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=1&access_token=$accessToken";
    $result = $this->httpGet($url);

    if ($result){
      $json = json_decode($result,true);
      if (!$json || !empty($json['errcode'])) {
        return false;
      }
      $jsapi_ticket = $json['ticket'];
      $expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
      //缓存
      cache('jsapi_ticket_'.$this->appId,$jsapi_ticket,$expire);
      return $jsapi_ticket;
    }

    return false;
  }

  /**
   * 获取access_token
   */
  private function getAccessToken() {
    //获取缓存
    if ($rs = cache('access_token_'.$this->appId))  {
      //dump($rs);
      return $rs;
    }
    // echo "no cache";
    // exit();
    //重新获取数据
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";

    $result =$this->httpGet($url);
    if($result){
      $json = json_decode($result,true);
      if (!$json || isset($json['errcode'])) {
        return false;
      }
      $access_token = $json['access_token'];
      $expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
      //缓存
      cache('access_token_'.$this->appId,$access_token,$expire);
      return $access_token;
    }

    return false;

  }

  /**
   * 获取签名
   * @param array $arrdata 签名数组
   * @param string $method 签名方法
   * @return boolean|string 签名值
   */
  public function getSignature($arrdata,$method="sha1") {
    if (!function_exists($method)) return false;
    ksort($arrdata);
    $paramstring = "";
    foreach($arrdata as $key => $value)
    {
      if(strlen($paramstring) == 0)
        $paramstring .= $key . "=" . $value;
      else
        $paramstring .= "&" . $key . "=" . $value;
    }
    $Sign = $method($paramstring);
    return $Sign;
  }

  private function httpGet($url) {
  	$oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
      curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
      return $sContent;
    }else{
      return false;
    }
  }

  /**
   * 	作用：设置jsapi的参数
   */
  public function getParameters()
  {
    $jsApiObj["appId"] = $this->appId;           //请求生成支付签名时需要,js调起支付参数中不需要
    $timeStamp = time();
    $jsApiObj["timeStamp"] = "$timeStamp";      //用大写的timeStamp参数请求生成支付签名
    $jsParamObj["timestamp"] = $timeStamp;      //用小写的timestamp参数生成js支付参数，还要注意数据类型，坑！
    $jsParamObj["nonceStr"] = $jsApiObj["nonceStr"] = $this->createNoncestr();
    $jsParamObj["package"] = $jsApiObj["package"] = "prepay_id=$this->prepay_id";
    $jsParamObj["signType"] = $jsApiObj["signType"] = "MD5";
    $jsParamObj["paySign"] = $jsApiObj["paySign"] = $this->getSign($jsApiObj);

    $jsParam = json_encode($jsParamObj);

    return $jsParam;
  }

  /**
   * 获取prepay_id
   */
  function getPrepayId()
  {
    $result = $this->xmlToArray($this->postXml());
    $prepay_id = $result["prepay_id"];
    return $prepay_id;
  }
  /**
   * 	作用：将xml转为array
   */
  public function xmlToArray($xml)
  {
    //将XML转为array
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
  }
  /**
   * 	作用：post请求xml
   */
  function postXml()
  {
    $xml = $this->createXml();

    return  $this->postXmlCurl($xml,"https://api.mch.weixin.qq.com/pay/unifiedorder",$this->CURL_TIMEOUT);

  }
  /**
   * 	作用：以post方式提交xml到对应的接口url
   */
  public function postXmlCurl($xml,$url,$second=30)
  {
    //初始化curl
    $ch = curl_init();
    //设置超时
    curl_setopt($ch,CURLOP_TIMEOUT, $this->CURL_TIMEOUT);
    //这里设置代理，如果有的话
    //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
    //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
    //设置header
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    //post提交方式
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    //运行curl
    $data = curl_exec($ch);
    curl_close($ch);
    //返回结果
    if($data)
    {
      curl_close($ch);
      return $data;
    }
    else
    {
      $error = curl_errno($ch);
      echo "curl出错，错误码:$error"."<br>";
      echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
      curl_close($ch);
      return false;
    }
  }
  /**
   * 	作用：设置标配的请求参数，生成签名，生成接口参数xml
   */
  function createXml()
  {
    $this->parameters["appid"] = $this->appId;//公众账号ID
    $this->parameters["mch_id"] = $this->MCHID;//商户号
    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
    return  $this->arrayToXml($this->parameters);
  }
   /**
   * 	作用：array转xml
   */
  function arrayToXml($arr)
  {
    $xml = "<xml>";
    foreach ($arr as $key=>$val)
    {
      if (is_numeric($val))
      {
        $xml.="<".$key.">".$val."</".$key.">";

      }
      else
        $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
    }
    $xml.="</xml>";
    return $xml;
  }
  /**
   * 	作用：生成签名
   */
  public function getSign($Obj)
  {
    foreach ($Obj as $k => $v)
    {
      $Parameters[$k] = $v;
    }
    //签名步骤一：按字典序排序参数
    ksort($Parameters);
    $String = $this->formatBizQueryParaMap($Parameters, false);
    //echo '【string1】'.$String.'</br>';
    //签名步骤二：在string后加入KEY
    $String = $String."&key=".$this->KEY;
    //echo "【string2】".$String."</br>";
    //签名步骤三：MD5加密
    $String = md5($String);
    //echo "【string3】 ".$String."</br>";
    //签名步骤四：所有字符转为大写
    $result_ = strtoupper($String);
    //echo "【result】 ".$result_."</br>";
    return $result_;
  }
	
}

