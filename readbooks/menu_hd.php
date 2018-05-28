<?php
header("Content-type: text/html; charset=utf-8");
require_once 'include/common.inc.php';
$pjson='{
    "button": [
        {
            "type": "click",
            "name": "我要报名",
            "key": "iweite_wybm"
        },
		{
            "type": "click",
            "name": "抽奖码",
            "key": "iweite_cjm"
        },
        {
            "type": "view",
            "name": "活动规则",
            "url": "http://mp.weixin.qq.com/s?__biz=MzIyMTI2NjMzNQ==&mid=100000038&idx=2&sn=d566bb3c96afb07fe2b66994599eb2a0#rd"
        }
    ]
}';
if ($site_conf['subscribe']== "subs_hd")
{
    $pjson='{
    "button": [
        {
            "type": "click",
            "name": "我要报名",
            "key": "iweite_wybm"
        },
		{
            "type": "click",
            "name": "抽奖码",
            "key": "iweite_cjm"
        },
        {
            "type": "view",
            "name": "活动规则",
            "url": "http://mp.weixin.qq.com/s?__biz=MzI0MDI2NzM5Mw==&mid=521911514&idx=2&sn=5ed5a3e85a7f520d489d8ff3102d7179#rd"
        }
    ]
    }';
}						

if ($site_conf['subscribe']=="daihao")
{
    $pjson='{
    "button": [
        {
            "type": "click",
            "name": "一点就变成D罩杯",
            "key": "iweite_daihao"
        },
    ]
    }';
}
if ($site_conf['subscribe']=="dzt")
{
    $pjson='{
    "button": [
        {
            "type": "view",
            "name": "我的售价",
            "url": "http://www.nihaoyuming07.cn/source/plugin/tb/index.php"
        },
        {
            "type": "view",
            "name": "小目标",
            "url": "http://www.nihaoyuming07.cn/source/plugin/zhuangbi/mb/index.php"
        }
    ]
    }';
}
if (($site_conf['subscribe']=="shoushenmiji") ||
    ($site_conf['subscribe']=="meibaishoushen") ||
    ($site_conf['subscribe']=="jianfeishoushen"))

{
    $pjson='{
    "button": [
        {
            "type": "click",
            "name": "一点就能瘦10斤>>",
            "key": "iweite_meixiong"
        },
    ]
    }';
}

if ($site_conf['subscribe']== "qx")
{
    $pjson='{
    "button": [
        {
            "name": "免费制作",
            "sub_button": [
                {
                   "type": "view",
                    "name": "立即制作",
                    "url": "http://afx01.taijixingzhe.com/loveshare/index.aspx?publicid=49"
                },
                {
                    "type": "view",
                    "name": "我的作品",
                    "url": "http://afx01.taijixingzhe.com/loveshare/mine.aspx?publicid=49"
                }
            ]
        },
		{
            "type": "click",
            "name": "七夕密语",
            "key": "iweite_qx"
        },
        {
            "type": "view",
            "name": "热门作品",
            "url": "http://afx01.taijixingzhe.com/loveshare/hot.aspx?publicid=49"
        }
    ]
}';
}

$TOKEN_URL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$_WEITE['appid']."&secret=".$_WEITE['appsecret'];
	if (ini_get('allow_url_fopen') == 1 && function_exists('curl_init')){
		$json = file_get_contents($TOKEN_URL);
		if (empty($json)){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt ($ch, CURLOPT_URL, $TOKEN_URL);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			$json = curl_exec($ch);
			curl_close($ch);
		}
		
		$result = json_decode($json,true);
		$ACC_TOKEN = $result['access_token'];
		
		
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