<?php

require_once '../include/common_new.inc.php';
require_once 'jfscUtil.php';							//工具文件

//调用方法名称
$method = isset($_REQUEST['method']) ? trim($_REQUEST['method']) : '';
$login = isset($_REQUEST['login']) ? trim($_REQUEST['login']) : '';
//当前用户openid
$openid = isset($_REQUEST['openid']) ? trim($_REQUEST['openid']) : '';
//分享用户openid
$sopenid = isset($_REQUEST['sopenid']) ? trim($_REQUEST['sopenid']) : '';
//分组
$group = isset($_REQUEST['group'])	? trim($_REQUEST['group']) : '';
//当前用户appid
$appid = isset($_REQUEST['appid'])	? trim($_REQUEST['appid']) : '';
//当前用户unionid
$unionid = isset($_REQUEST['unionid']) ? trim($_REQUEST['unionid']) : '';
$quid = isset($_REQUEST['quid']) ? trim($_REQUEST['quid']) : '';
$taskid = isset($_REQUEST['taskid']) ? trim($_REQUEST['taskid']) : '0';
$tasktype = isset($_REQUEST['tasktype']) ? trim($_REQUEST['tasktype']) : '4';

if (!isset($db)){
	$db = new mysqli($dbhost,$dbuser,$dbpw,$dbname);
	if(mysqli_connect_errno())
	{
		$returnArray['code']=1;
		$returnArray['err_msg']="数据库连接失败";
		echo json_encode($returnArray);
		exit();
	}
}

//接口 PHP版本为 5.3

/**
 * 展示接口所对应的方法， 防止后续代码过长查看不方便。
 *
 * 获取分享者的用户信息         		-   getShareUserInfo
 * 发送引导关注的客服消息           		-   sendMsgByGuide
 */

if($method == "")
{
	$re = array('code' => 1, 'msg' => '方法有误');
	echo json_encode($re);
	$db->close();
	exit;
}

//获取分享者的用户信息
else if($method == "getShareUserInfo")
{	
	$gzhInfo = getGzhInfo($db,$appid);
	//获取分享者用户信息
	$userInfo = getShareUserInfo($db,$openid,$sopenid,$group,$gzhInfo['appid'],$unionid);
	$re = array_merge($userInfo,$gzhInfo);

	echo json_encode($gzhInfo);
	$db->close();
	exit;
}

//发送引导关注的客服消息
else if($method == "sendMsgByGuide")
{
	$re = sendMsgByGuide($db,$unionid,$group,$appid,$quid);
	
	if($taskid=="1"){ //任务链接加5分积分每天可以领
	    if($tasktype=="4"){
     	    sendMsgByLinkTask($db, $unionid, $group, $appid, $quid,$tasktype);
	    }else if($tasktype=="6"){
	        sendMsgByQunTask($db, $unionid, $group, $appid, $quid, $tasktype);
	    }
	}
	
	echo json_encode($re);
	$db->close();
	exit;
}

function sendMsgByQunTask($db,$unionid,$group,$appid,$quid,$type){
    $res = array('code' => 1, 'msg' => '失败');
    $sql = "select openid,tid,score from iweite_huace_openid where unionid = ? and appid= ? limit 1" ;
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ss', $unionid,$appid);
    $stmt->bind_result($selfopenid,$selftid,$selfscore);
    $stmt->execute();
    $stmt->store_result();
    $num = $stmt->num_rows;
    if($num > 0)
    {
        while ($stmt->fetch())
        {
            $sfopenid = $selfopenid;
            $sftid = $selftid;
            $sfscore = $selfscore;
        }
         
        $crtime = time();
        $link_score = 5;
        $link_score_type = $type;
        $sql1 = "INSERT INTO iweite_huace_score(uid, openid, score, score_type, add_time,sub,q_uid) SELECT ?,?,?,?,?,?,? FROM DUAL WHERE NOT EXISTS(SELECT id FROM iweite_huace_score WHERE openid= ? and score_type=6 and sub= ? )";
        $stmt1 = $db->prepare($sql1);
        $stmt1->bind_param('isiiisiss',$sftid,$sfopenid,$link_score,$link_score_type,$crtime,$appid,$sftid,$sfopenid,$appid);
        $stmt1->execute();
        $stmt1->store_result();
        $affectRows = $stmt1->affected_rows;
        $stmt1->free_result();
        $stmt1->close();
        if($affectRows>0){//每日访问链接成功 发送客服消息
            $sfscore = $sfscore + $link_score;
            //更新用户总积分
            $sql3 = " update iweite_huace_openid set score = ? where tid = ? limit 1 ";
            $stmt3 = $db->prepare($sql3);
            $stmt3->bind_param('ii', $sfscore,$sftid);
            $stmt3->execute();
            $stmt3->store_result();
            $stmt3->free_result();
            $stmt3->close();
            	
            $sql2 = "select token from iweite_huace_token where appid = ? and type = 'token' limit 1";
    
            $stmt2 = $db->prepare($sql2);
            $stmt2->bind_param('s', $appid);
            $stmt2->bind_result($token);
            $stmt2->execute();
            $stmt2->store_result();
            $num2 = $stmt2->num_rows;
            	
            if($num2 > 0)
            {
                while ($stmt2->fetch())
                {
                    $appid_token = $token;
                }
                //发送客服消息
                send_task_mbxx($db, $sfopenid, $appid_token, $sfscore);
            }
            	
    
            	
            $stmt2->free_result();
            $stmt2->close();
        }
    
    }
    $stmt->free_result();
    $stmt->close();
}

//链接任务积分 天天阅读
function sendMsgByLinkTask($db,$unionid,$group,$appid,$quid,$type){
    $res = array('code' => 1, 'msg' => '失败');
    $sql = "select openid,tid,score from iweite_huace_openid where unionid = ? and appid= ? limit 1" ;
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ss', $unionid,$appid);
    $stmt->bind_result($selfopenid,$selftid,$selfscore);
    $stmt->execute();
    $stmt->store_result();
    $num = $stmt->num_rows;
    if($num > 0)
    {
        while ($stmt->fetch())
        {
            $sfopenid = $selfopenid;
            $sftid = $selftid;
            $sfscore = $selfscore;
        }
       
        $crtime = time();
        $link_score = 5;
        $link_score_type = $type;
        $sql1 = "INSERT INTO iweite_huace_score(uid, openid, score, score_type, add_time,sub,q_uid) SELECT ?,?,?,?,?,?,? FROM DUAL WHERE NOT EXISTS(SELECT id FROM iweite_huace_score WHERE openid= ? and score_type=4 and sub= ? and FROM_UNIXTIME(add_time,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d'))";
    	$stmt1 = $db->prepare($sql1);
    	$stmt1->bind_param('isiiisiss',$sftid,$sfopenid,$link_score,$link_score_type,$crtime,$appid,$sftid,$sfopenid,$appid);
    	$stmt1->execute();
    	$stmt1->store_result();
    	$affectRows = $stmt1->affected_rows;
    	$stmt1->free_result();
    	$stmt1->close();
    	if($affectRows>0){//每日访问链接成功 发送客服消息
    	    $sfscore = $sfscore + $link_score;
    	    //更新用户总积分
    	    $sql3 = " update iweite_huace_openid set score = ? where tid = ? limit 1 ";
    	    $stmt3 = $db->prepare($sql3);
    	    $stmt3->bind_param('ii', $sfscore,$sftid);
    	    $stmt3->execute();
    	    $stmt3->store_result();
    	    $stmt3->free_result();
    	    $stmt3->close();
    	    
    	    $sql2 = "select token from iweite_huace_token where appid = ? and type = 'token' limit 1";
    	    	
    	    $stmt2 = $db->prepare($sql2);
    	    $stmt2->bind_param('s', $appid);
    	    $stmt2->bind_result($token);
    	    $stmt2->execute();
    	    $stmt2->store_result();
    	    $num2 = $stmt2->num_rows;
    	    
    	    if($num2 > 0)
    	    {
    	        while ($stmt2->fetch())
    	        {
    	            $appid_token = $token;
    	        }
    	        //发送客服消息
    	        send_task_mbxx($db, $sfopenid, $appid_token, $sfscore);
    	    }
    	    
    	   
    	    
    	    $stmt2->free_result();
    	    $stmt2->close();
    	}
        
    }
    $stmt->free_result();
    $stmt->close();
    
}

//发送引导关注的客服消息
function sendMsgByGuide($db,$unionid,$group,$appid,$quid)
{
	$re = array('code' => 1, 'msg' => '失败');
	
	$sql = "select a.id,a.sappid,a.sopenid,a.sname,b.username from 
				(select * from iweite_huace_share_contact where unionid = ? and appid = ? and issend = 0 limit 1 ) a 
			LEFT JOIN iweite_huace_openid b on a.openid = b.openid ";
	$stmt = $db->prepare($sql);
	$stmt->bind_param('ss', $unionid,$appid);
	$stmt->bind_result($tableid,$sappidTemp,$sopenid,$sname,$username);
	$stmt->execute();
	$stmt->store_result();
	$num = $stmt->num_rows;
	
	if($num > 0)
	{
		while ($stmt->fetch())
		{
			$sappid = $sappidTemp;
		}
		
		$sql = "select token from iweite_huace_token where appid = ? and type = 'token' limit 1";
			
		$stmt1 = $db->prepare($sql);
		$stmt1->bind_param('s', $sappidTemp);
		$stmt1->bind_result($token);
		$stmt1->execute();
		$stmt1->store_result();
		$num1 = $stmt1->num_rows;
		
		if($num1 > 0)
		{
			while ($stmt1->fetch())
			{
				$appid_token = $token;
			}
			//发送客服消息
			send_mbxx($db,$sopenid,$appid_token,$username,$quid);
			$re = array('code' => 0, 'msg' => '成功');
		}
		$stmt1->free_result();
		$stmt1->close();
		
		//更新发送状态
		$sql = "update iweite_huace_share_contact set issend = 1 where id = ? and issend = 0 limit 1";
		$stmt2 = $db->prepare($sql);
		$stmt2->bind_param('i', $tableid);
		$stmt2->execute();
		$stmt2->store_result();
		$stmt2->free_result();
		$stmt2->close();
		
	}
	$stmt->free_result();
	$stmt->close();
	
	return $re;
}

//获取分享者用户信息
function getShareUserInfo($db,$openid,$sopenid,$group,$appid,$unionid)
{
	$re = array('code' => 1, 'username' => '', 'face' => '');

	//获取分享者用户信息
	$sql = "select tid,username,face,appid from iweite_huace_openid where openid = ? limit 1" ;
	$stmt = $db->prepare($sql);
	$stmt->bind_param('s', $sopenid);
	$stmt->bind_result($sid,$username,$face,$sappid);
	$stmt->execute();
	$stmt->store_result();
	$num = $stmt->num_rows;
	if($num > 0)
	{
		while ($stmt->fetch())
		{
			$re = array('code' => 0, 'username' => $username, 'face' => $face);
		}
		
		$crtime = time();
		
		$sql = "INSERT INTO iweite_huace_share_contact(openid,unionid, gp, sopenid, sappid,sname,sid,appid,crtime) SELECT ?,?,?,?,?,?,?,?,? FROM DUAL WHERE NOT EXISTS(SELECT openid,sopenid,appid FROM iweite_huace_share_contact WHERE openid = ? and appid = ?)";
		$stmt1 = $db->prepare($sql);
		$stmt1->bind_param('ssssssssssi',$openid,$unionid,$group,$sopenid,$sappid,$username,$sid,$appid,$crtime,$openid,$appid);
		$stmt1->execute();
		$stmt1->store_result();
		$affectRows = $stmt1->affected_rows;
		$stmt1->free_result();
		$stmt1->close();
	}
	$stmt->free_result();
	$stmt->close();
	return $re;
}

//获取对应的公众号配置信息
function getGzhInfo($db,$appid)
{
	$canShow = 1;

    $sql = "select appid,ewm_url from iweite_huace_ewm where is_show = ? and appid = ? limit 1" ;
    $stmt = $db->prepare($sql);
	$stmt->bind_param('is', $canShow,$appid);
    $stmt->bind_result($appid,$pic);
    $stmt->execute();
    $stmt->store_result();
    $num = $stmt->num_rows;
    if($num>0)
    {
    	while ($stmt->fetch())
    	{
    		$re = array('appid' => $appid, 'pic' => $pic);
    	}
    }
    else
    {
        $re = array('appid' => '0', 'pic' => '');
    }
    $stmt->free_result();
    $stmt->close();
	
	return $re;
}

//链接任务加积分
function send_task_mbxx($db,$openid,$returnaccess,$score){
    
    $content .= "积分变动提醒\n";
    $content .= date("Y-m-d H:i:s")."\\n\\n";
    $content .= "变动原因：链接加分\n";
    $content .= "变动数额：+ 5 \n";
    $content .= "当前积分：$score \\n\\n";
    
    $array = array(
        "touser"	=> $openid,
        "msgtype"	=> "text",
        'text' 		=> array('content' => $content)
    );
    $data = toJson_msg($array);
    return send_textMsg($data,$returnaccess);
}

//发送客服文字消息
function send_mbxx($db,$openid,$returnaccess,$username,$quid)
{
	$re = userGzAddScore($db,$openid,$quid);
	if($re['code'] == 0)
	{
		$score = $re['score'];
		
		$content .= "积分变动提醒\n";
		$content .= date("Y-m-d H:i:s")."\\n\\n";
		$content .= "变动原因：好友关注($username)\n";
		$content .= "变动数额：+ 10 \n";
		$content .= "当前积分：$score \\n\\n";
		
		$array = array(
				"touser"	=> $openid,
				"msgtype"	=> "text",
				'text' 		=> array('content' => $content)
		);
		$data = toJson_msg($array);
		return send_textMsg($data,$returnaccess);
	}
	else
	{
		return array('code' => 1, 'msg' => '没有对应的用户');
	}
}

//用户添加关注的分
function userGzAddScore($db,$openid,$quid)
{
	$re = array('code' => 1, 'uid' => 0, 'score' => 0, 'appid' => '');
	
	
	$score = 0;
	$appid = '';
	$uid = 0;
	
	//添加类型，1邀请好友，2关注,3签到 4链接 5兑换 -2取消关注
	$sql = "select score,appid,tid from iweite_huace_openid where openid = ? limit 1";
	$stmt = $db->prepare($sql);
	$stmt->bind_param('s', $openid);
	$stmt->bind_result($scoreTemp,$appidTemp,$uidTemp);
	$stmt->execute();
	$stmt->store_result();
	$num = $stmt->num_rows;
	
	if($num > 0)
	{
		while ($stmt->fetch())
		{
			$score = $scoreTemp;
			$appid = $appidTemp;
			$uid = $uidTemp;
		}
	}
	$stmt->free_result();
	$stmt->close();
	
	
	if($uid > 0)
	{
		$score = $score + 10;
		$gzScore = 10;
		$scoreType = 1;
		
		
		//插入新增数据
		$crtime = time();
		$sql = " INSERT INTO iweite_huace_score (uid, openid, score, score_type, add_time, sub, q_uid) VALUES (?,?,?,?,?,?,?)";
		$stmt = $db->prepare($sql);
		$stmt->bind_param('isiiisi', $uid,$openid,$gzScore,$scoreType,$crtime,$appid,$quid);
		$stmt->execute();
		$stmt->store_result();
		$stmt->free_result();
		$stmt->close();
		
		//更新用户总积分
		$sql = " update iweite_huace_openid set score = ? where tid = ? limit 1 ";	
		$stmt = $db->prepare($sql);
		$stmt->bind_param('ii', $score,$uid);
		$stmt->execute();
		$stmt->store_result();
		$stmt->free_result();
		$stmt->close();
		
		$re = array('code' => 0, 'uid' => $uid, 'score' => $score, 'appid' => $appid);
		
	}
	
	return $re;
}

//微信发送文本消息
function send_textMsg($data,$returnaccess)
{
	$menu_url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$returnaccess}";
	return url_wx($menu_url, "POST", $data);
}

//发送链接
function url_wx($url, $method = "GET", $postfields = null, $headers = array(), $debug = false)
{
	$ci = curl_init();
	/* Curl settings */
	curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ci, CURLOPT_TIMEOUT, 30);
	curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
	curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false); //不验证证书
	switch ($method) {
		case 'POST':
			curl_setopt($ci, CURLOPT_POST, true);
			if (!empty($postfields)) {
				curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
			}
			break;
	}
	curl_setopt($ci, CURLOPT_URL, $url);
	curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ci, CURLINFO_HEADER_OUT, true);

	$response = curl_exec($ci);
	$http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
	curl_close($ci);
	$code = json_decode($response, true);
	if($debug && isset($code['errcode']) && $code['errcode'] != 0)
	{
		//var_dump($code);
	}
	//var_dump($response);
	return $response;
}

//php数组不乱码的转json
function toJson_msg($data) 
{
	$data = json_encode(urlencodeAry_msg($data));
	return urldecode($data);
}

/**
 * 将数据进行urlencode
 * @param array & string $data
 */
function urlencodeAry_msg($data) 
{
	if(is_array($data)) 
	{
		foreach($data as $key=>$val) 
		{
			$data[$key] = urlencodeAry_msg($val);
		}
		return $data;
	} 
	else 
	{
		return urlencode($data);
	}
}