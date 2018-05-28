<?php 
header("Content-type: text/html; charset=utf-8");
require_once '../include/common.inc.php';

// Add by qinjh   如果路径地址不是根目录，把路径加上，保证路径完整
$poz = strrpos($_SERVER["PHP_SELF"],"/api/index.php");
$start_url = "";
if($poz>1){
	$start_url = substr($_SERVER["PHP_SELF"],1,$poz);
}
// End
define("Weburl",$_WEITE['web_weburl'].$start_url);
define("TOKEN",$_WEITE['token']);
define("Subscribe",$_WEITE['subscribe']);

$wechatObj = new wechatCallbackapiTest();
if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}

class wechatCallbackapiTest
{

   	public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            ob_clean();
            echo $echoStr;
            exit;
        }
    }

   	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	//回复图片信息
	private function transmitImage($object, $media_id)
	{
	    // $media_id="1zaSbGQXYUJV1GfHpWzXb2XPZTlB4yuc9nEx8tdIg3KB1QzDSjlGsE9Af3qVm0PB";
	    $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[image]]></MsgType>
					<Image>
					<MediaId><![CDATA[%s]]></MediaId>
					</Image>
					</xml>";
	    $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $media_id);
	    return $result;
	}
	
	//回复文本信息
	private function transmitText($object, $content)
    {
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

	//回复图文信息
	private function transmitNews($object, $arr_item, $flag = 0)
    {
        if(!is_array($arr_item))
            return;

        $itemTpl = "    <item>
							<Title><![CDATA[%s]]></Title>
							<Description><![CDATA[%s]]></Description>
							<PicUrl><![CDATA[%s]]></PicUrl>
							<Url><![CDATA[%s]]></Url>
						</item>
					";
        $item_str = "";
        foreach ($arr_item as $item)
        $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['Picurl'], $item['Url']);

        $newsTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[news]]></MsgType>
					<Content><![CDATA[]]></Content>
					<ArticleCount>%s</ArticleCount>
					<Articles>
					$item_str</Articles>
					<FuncFlag>%s</FuncFlag>
					</xml>";

        $resultStr = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($arr_item), $flag);
        return $resultStr;
    }

	public function https_curl($url, $data = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	
	public function ToParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			if($k != "sign"){
				$buff .= $k . "=" . $v . "&";
			}
		}
			
		$buff = trim($buff, "&");
		return $buff;
	}

	//微信回调
    public function responseMsg()
    {

	   global $db,$tablepre,$timestamp;
 	   $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
       if(!empty($postStr)){
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$keyword = trim($postObj->Content);
			$fromUsername = $postObj->FromUserName;//发送人
			$toUsername = $postObj->ToUserName;//接收人
			$MsgType = $postObj->MsgType;//消息类型
			$Event= trim($postObj->Event); //实践类型
			$MsgId = $postObj->MsgId;//消息id
			$PicUrl = $postObj->PicUrl;//消息id
			$time = time();//当前时间做为回复时间
			$MediaId = $postObj->MediaId;//消息id
	 		
			$jdata = array();
			$jdata["openid"] =$fromUsername;
			$jdata["webapp"] ="iweite.com";
			ksort($jdata);
			$nonce= sha1($this->ToParams($jdata));
			
			$url =Weburl."data/data.php?iweite=".$nonce;
			
			// $file = fopen($MsgId.".txt","w");
			// fwrite($file,"MsgType".$MsgType."Event".$Event."EventKey".$postObj->EventKey."11111111");
			// fclose($file);
			
			if($Event==="CLICK")//点击事件
			{
				if($postObj->EventKey=="iweite_cjm")
				{
					$json ='{
									"action":"cjm",
									"openid":"'.$fromUsername.'",
									"original_id":"'.$toUsername.'"
								}';
					$res=$this->https_curl($url,$json);
					$arr=json_decode($res,true);
					$msg=$arr["errmsg"];
					$resultStr =$this->transmitText($postObj,$msg);
					echo $resultStr;
					exit;
				}

				//每日签到
				if($postObj->EventKey=="score_qd")
				{
					$json ='{
									"action":"score_qd",
									"openid":"'.$fromUsername.'",
									"original_id":"'.$toUsername.'"
								}';
					$res=$this->https_curl($url,$json);
					$arr=json_decode($res,true);
					$msg=$arr["errmsg"];
					$resultStr =$this->transmitText($postObj,$msg);
					echo $resultStr;
					exit;
				}

				//我的积分
				if($postObj->EventKey=="score_my")
				{
					$json ='{
								"action":"score_my",
								"openid":"'.$fromUsername.'",
								"original_id":"'.$toUsername.'"
							}';
					$res=$this->https_curl($url,$json);
					$arr=json_decode($res,true);
					$msg=$arr["errmsg"];
					$resultStr =$this->transmitText($postObj,$msg);
					echo $resultStr;
					exit;
				}

				//海报积分
				if($postObj->EventKey=="haibao_jf")
				{
					$json ='{
								"action":"haibao_jf",
								"openid":"'.$fromUsername.'",
								"original_id":"'.$toUsername.'"
							}';
					$res = $this->https_curl($url, $json);
					$arr = json_decode($res, true);
					$media_id = $arr["media_id"];
					$resultStr = $this->transmitImage($postObj, $media_id);
					echo $resultStr;
					exit;
				}
				if($postObj->EventKey=="custom_jf")
				{					    
					$media_id = "1soDhqZdwsCoP1deGOjf-efkqUpOiBIL17W8sUcb-ik";
					$resultStr = $this->transmitImage($postObj, $media_id);
					echo $resultStr;
					exit;
				}
			}elseif($Event==="subscribe"){//关注事件
				$json ='{
					"action":"subscribe",
					"openid":"'.$fromUsername.'",
					"original_id":"'.$toUsername.'"
				}';
				$res=$this->https_curl($url,$json);
				$arr=json_decode($res,true);
				$code = $arr["errcode"];
				if($code=="2"){//图片
					$media_id = $arr["media_id"];
					$resultStr = $this->transmitImage($postObj, $media_id);
				}else{//文字
					$msg=$arr["errmsg"];
					$resultStr =$this->transmitText($postObj,$msg);
				}
				echo $resultStr;
				exit;										
			}elseif($Event==="unsubscribe"){//取消关注事件
				$json ='{
					"action":"unsubscribe",
					"openid":"'.$fromUsername.'",
					"original_id":"'.$toUsername.'"
				}';
				$res=$this->https_curl($url,$json);
				exit;
			}else{
				if($MsgType == 'image'){//输入图片时间					
				}
				if($MsgType == 'text')
				{					
					if(strtolower($keyword) == "快递"){
						// $json ='{
						// 			"action":"search_wl",
						// 			"openid":"'.$fromUsername.'",
						// 			"original_id":"'.$toUsername.'"
						// 		}';
						// $res=$this->https_curl($url,$json);
						// $arr=json_decode($res,true);
						// $msg= $arr["errmsg"];						
						$msg  = "<a href='https://shimo.im/sheet/Qh5zKNMk33wDYlsn/AWjKH' >快递公示</a>";
						$resultStr =$this->transmitText($postObj,$msg);
						echo $resultStr;
						exit;
					}			
				}							
				//全部结束
			}
		}else{
			echo "<meta http-equiv='refresh' content='1;URL=../index.php' />";
			exit;
		}
	}
}
?>