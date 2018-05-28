<?php
session_start();
header("Content-type: text/html; charset=utf-8");

require_once '../include/common_new.inc.php';
global $site_conf;


// 接受的方法参数
$method         = isset($_GET["method"])        ? $_GET["method"]               : 'addScoreByShare';
$uid            = isset($_GET['uid'])           ? intval($_GET['uid'])          : 1;
$openid         = isset($_GET['openid'])        ? $_GET['openid'] 				: '123';
$appid          = isset($_GET['appid'])         ? $_GET['appid'] 				: '';
$page           = isset($_GET['page'])          ? intval($_GET['page']) 		: 1;

// $db = new dbstuff;
// $db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8");
//$db->connect('rm-wz90046qx6i6vu3cp0o.mysql.rds.aliyuncs.com', 'jscommon', 'jscommon_123', 'fxmall', $pconnectz, true, "utf8");

if (!isset($db))
{
	$db = new mysqli($dbhost,$dbuser,$dbpw,$dbname);
	if(mysqli_connect_errno())
	{
		$returnArray['code'] = -1;
		$returnArray['msg']="数据库连接失败";
		echo json_encode($returnArray);
		exit();
	}
}


//项目 PHP版本为 5.3


/**
 * 展示接口所对应的方法， 防止后续代码过长查看不方便。
 *
 * 获取用户信息         			-   getUserInfo
 * 获取用户的积分列表		-	getMyScoreList
 */




if($method == "")
{
	$returnArray = array('code' => 1);
	echo json_encode($returnArray);
	$db->close();
	exit;
}



//获取用户信息
else if($method == "getUserInfo")
{
 	$re = getUserInfo($db,$uid,$appid);
	echo json_encode($re);
	$db->close();
	exit;
}

//获取用户的积分列表
else if($method == "getMyScoreList")
{
	$re = getMyScoreList($db,$uid, $appid,$page);
	echo json_encode($re);
	$db->close();
	exit;
}


//获取用户信息
function getUserInfo($db,$uid,$appid)
{
	$re = array('code' => -1, 'tid' => 0, 'username' => '','score' => 0, 'face' => '');
	
	$sql = "select tid,username,score as total_score,face from iweite_huace_openid where uid = ? and appid = ? limit 1";
	$stmt = $db->prepare($sql);
	$stmt->bind_param('is',$uid,$appid);
	$stmt->bind_result($tid,$username,$score,$face);
	$stmt->execute();
	$stmt->store_result();
	$num = $stmt->num_rows;
	while($stmt->fetch())
	{
		$re = array('code' => 0, 'tid' => $tid, 'username' => $username,'score' => $score, 'face' => $face);
	}
	$stmt->free_result();
	$stmt->close();
	
	return $re;
}


//获取用户积分列表
function getMyScoreList($db, $uid, $appid,$page)
{
	$re = array('code' => -1, 'data' => '');
	
	$link_uid  = array();
	
	$pageSize = 10;
	if(intval($page) > 0)
	{
		$page = $page - 1;
		$startNum = intval( $page * $pageSize);
		$limit = $startNum .",".$pageSize;
	}
	else
	{
		$limit = "0,10";
	}
	
	$sql = "select 	a.*,b.name as gift_name from 
					
			(	select 
						a.*,
						b.id,
						b.score as jf_score,
						b.score_type,
						FROM_UNIXTIME(b.add_time) as add_time,
						b.list_id as gift_id,
						b.q_uid 
				from
				
				(select tid,username,score from iweite_huace_openid where uid = ? and appid = ? limit 1) a
					LEFT JOIN iweite_huace_score  b
					
				on a.tid = b.uid where b.sub = ? ORDER BY b.add_time desc limit $limit 
			)  a
			LEFT JOIN iweite_huace_list b on a.gift_id = b.id";
	
	
	$stmt = $db->prepare($sql);
	$stmt->bind_param('iss',$uid,$appid,$appid);
	$stmt->bind_result($tid,$username,$total_score,$scoreList_id,$score,$score_type,$crtime,$gift_id,$q_uid,$gift_name);
	$stmt->execute();
	$stmt->store_result();
	$num = $stmt->num_rows;
	
	if($num > 0)
	{
		$index = 0;
		while($stmt->fetch())
		{
			
			// 	添加类型，
			// 		1邀请好友
			// 		2关注
			// 		3签到
			// 		4链接
			// 		5兑换
			// 		-2取消关注
			
			$data[] = array(
					'tid'			=> $tid,
					'username'		=> $username,
					'total_score'	=> $total_score,
					'score'			=> abs($score),
					'type'			=> $score_type,
					'crtime'		=> $crtime,
					'list_id'		=> $gift_id,
					'q_uid'			=> $q_uid,
					'gift_name'		=> trim($gift_name),
					'q_name'		=> '',
			);
			
			//邀请|取消关注   特殊查询其他信息	
			if($score_type == 1 || $score_type == -2)
			{
				$link_uid[$index]  = array('type' => $score_type, 'q_uid' => $q_uid );
			}
			
			$index++;
		}
		
		//关联用户
		if(!empty($link_uid))
		{
			$ids = "";
			foreach ($link_uid as $key => $value)
			{
				$ids .= $value['q_uid'].","; 
			}
			$ids = substr($ids,0,-1);
			
			$u_sql = "select uid,username from iweite_huace_openid where uid in($ids) GROUP BY uid";
			
			$stmt1 = $db->prepare($u_sql);
			$stmt1->bind_result($uidTemp,$usernameTemp);
			$stmt1->execute();
			$stmt1->store_result();
			$num1 = $stmt1->num_rows;
			
			if($num1 > 0)
			{
				while($stmt1->fetch())
				{
					$n_array[$uidTemp] = array('uid' => $uidTemp,'qname' => $usernameTemp);
				}
				
				//用户名字绑定
				foreach ($link_uid as $key => $value)
				{
					$k = $data[$key]['q_uid'];
					$data[$key]['q_name']=  $n_array[$k]['qname'];
				}
			}
			$stmt1->free_result();
			$stmt1->close();
		}
		
		$re = array('code' => 0, 'data' => $data);
		
	}
	else
	{
		$data = array();
	}
	
	$stmt->free_result();
	$stmt->close();
	
	return $re;
}

?>