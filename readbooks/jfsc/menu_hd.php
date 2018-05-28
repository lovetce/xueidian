<?php
header("Content-type: text/html; charset=utf-8");
require_once '../include/common.inc.php';

$menu_rul	= $site_conf['maindomain'];
$menu_appid	= $site_conf['appid'];
$original_id	= $site_conf['original_id'];

 $pjson='{
    "button": [
        {                       
            "type": "view",
            "name": "免费书库",
            "url": "http://freetag.52star.club/book/index.html"           
        },
		{
            "name": "赠书活动",
            "sub_button": [
                {
                    "type": "click",
                    "name": "生成海报",
                    "key": "haibao_jf"
                },
                {
                    "type": "view",
                    "name": "兑换规则",
                    "url": "http://'.$menu_rul.'/jfsc/html/task.php"
                },
                {
                    "type": "view",
                    "name": "领取入口",
                    "url": "http://'.$menu_rul.'/jfsc/html/task.php"
                },
                {
                    "type": "view",
                    "name": "任务查询",            
                    "url": "http://'.$menu_rul.'/jfsc/html/yqji.php"
                },
                {
                    "type": "view",
                    "name": "订阅下期",
                    "url": "http://'.$menu_rul.'/jfsc/html/dingyue.php"
                }
            ]
        },
        {
            "name": "活动咨询",
            "sub_button": [
                {
                    "type": "view",
                    "name": "快递公示",
                    "url": "https://shimo.im/sheet/Qh5zKNMk33wDYlsn/AWjKH"
                },
                {
                    "type": "click",
                    "name": "在线客服",
                    "key": "custom_jf"
                }
            ]
        }
    ]
}';



	if (ini_get('allow_url_fopen') == 1 && function_exists('curl_init')){
		$host = "";
		$port = 6379;
		$pwd ="";
		$redis = new Redis();
		if ($redis->connect($host, $port) == false) {
			die($redis->getLastError());
		}
		if ($redis->auth($pwd) == false) {
			die($redis->getLastError());
		}
		$key = "WX_CONFIG:OriginalId:".$original_id;
        $data = json_decode($redis->get($key),true);        
        $ACC_TOKEN = $data['access_token'];
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$ACC_TOKEN);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)'); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS,$pjson); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			$tmpInfo = curl_exec($ch); 
			if (curl_errno($ch)) {  
			echo  curl_error($ch); 
			}else{
			curl_close($ch); 
			//echo $tmpInfo;
		    $result = json_decode($tmpInfo,true);
		    $errcode = $result['errcode'];
				if($errcode){
					echo $tmpInfo;
				}else{
					echo "生成成功,24小时后生效(有时候几分钟后也生效)";
					
					//modified by chenhl,2016-01-21,支持多域名、多公众号
					//unlink("cache/access_token.json");
					//unlink("cache/jsapi_ticket.json");
					unlink($wxdir.'/access_token.json');
					unlink($wxdir.'/jsapi_ticket.json');
				}
			}
		
	}else{
		echo "空间不支持！请询问空间商是否开启curl和allow_url_fopen";
	}
			
				
					
?>