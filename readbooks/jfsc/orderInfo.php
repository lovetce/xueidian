<?php
session_start ();
header ( "Content-type: text/html; charset=utf-8" );

require_once '../include/common_new.inc.php';
global $site_conf;


// 接受的方法参数
$method 		= isset ( $_GET ["method"] ) 	? $_GET ["method"] 			: 'addOrder';
$subject_id 	= isset ( $_GET ['subject_id']) ? $_GET ['subject_id'] 	    : 1;
$uid 			= isset ( $_GET ['uid'] ) 		? $_GET ['uid'] 			: 1;
$name 			= isset ( $_GET ['name'] ) 		? $_GET ['name'] 			: '123';
$mobile 		= isset ( $_GET ['mobile'] )	? $_GET ['mobile'] 			: '123';
$provance 		= isset ( $_GET ['provance'] )	? $_GET ['provance'] 		: '123';
$city 			= isset ( $_GET ['city'] )		? $_GET ['city'] 			: '123';
$area 			= isset ( $_GET ['area'] )		? $_GET ['area'] 			: '123';
$address 		= isset ( $_GET ['address'] )	? $_GET ['address'] 		: '123';
$remarks 		= isset ( $_GET ['remarks'] ) 	? $_GET ['remarks'] 		: '123';
$appid 			= isset ( $_GET ['appid'] ) 	? $_GET ['appid'] 			: '123';
$unionid 		= isset ( $_GET ['unionid'] ) 	? $_GET ['unionid'] 		: '123';


$db = new dbstuff ();
$db->connect ( $dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8" );
// $db->connect('rm-wz90046qx6i6vu3cp0o.mysql.rds.aliyuncs.com', 'jscommon', 'jscommon_123', 'fxmall', $pconnectz, true, "utf8");

if (! $db)
{
	echo json_encode ( array (
		'status' => - 1,
		'msg' => '数据库链接失败'
	) );
	exit ();
}

// 项目 PHP版本为 5.3
/**
 * 展示接口所对应的方法， 防止后续代码过长查看不方便。
 *
 * 添加订单 - addOrder
 */

if ($method == "")
{
	$returnArray = array (
			'code' => 1
	);
	echo json_encode ($returnArray);
	$db->close ();
	exit ();
}

// 获取本人的交易订单
else if ($method == "getMyOrderList")
{
	$re = getMyOrderList($db,$uid,$appid,$page);
	echo json_encode ($re);
	$db->close();
	exit ();
}

//产生交易订单
else if ($method == "addOrder")
{
	$re = addOrder($db, $subject_id, $uid, $unionid, $name, $mobile, $provance, $city, $area, $address, $remarks, $appid);
	echo json_encode($re);
	$db->close();
	exit();
}

// 获取本人的交易订单
function getMyOrderList($db, $uid, $appid, $page)
{
	$pageCount = 3;
	$limit = ($page - 1) * $pageCount . "," . $pageCount;
	
	$list = array ();
	
	$query = $db->query ( "select o.id,o.crtime,o.status,o.score,o.wlno,o.wlname,l.banner,l.name,l.info from iweite_huace_order o left join iweite_huace_list l on o.lid=l.id  where  o.uid = $uid and o.appid='$appid' order by o.crtime  desc limit $limit" );
	while ( $rs = $db->fetch_array ( $query ) )
	{
		$list [] = array (
				'oid' => $rs ['id'],
				'name' => $rs ['name'],
				'info' => $rs ['info'],
				'score' => $rs ['score'],
				'banner' => $rs ['banner'],
				'status' => $rs ['status'],
		        'wlno' => $rs ['wlno'],
		        'wlname' => $rs ['wlname'],
				'crtime' => date ( "Y-m-d H:i:s", $rs ['crtime'] ) 
		);
	}
	return array (
			'code' => 0,
			'data' => $list 
	);
}

// 添加订单详情
function addOrder($db, $subject_id, $uid, $unionid, $name, $mobile, $provance, $city, $area, $address, $remarks, $appid)
{	
	$subject_id = intval($subject_id);
	$uid = intval($uid);
	if (empty($subject_id) || empty ($uid) || empty ($unionid) || empty ($name) || empty ($mobile) || empty($city) || empty($area) || empty($address) || empty($appid))
	{
		return array (
			'code' => -2,
			'msg' => '缺少必要参数'
		);
	}
	//1.判断是否已经提交
	$orderinfo = $orders = $db->fetch_first("select count(1) stock from iweite_huace_order where subject_id=$subject_id and unionid='$unionid' limit 1");
	if($orderinfo){
		return array (
			'code' => -5,
			'msg' => '请不要重复提交' 
		);
	}

	//1.先判断库存	
	$orders = $db->fetch_first("select count(1) stock from iweite_huace_order where subject_id=$subject_id and appid='$appid' limit 1");
	$subject_info = $db->fetch_first("select appid,stock from iweite_huace_subject where id=$subject_id");		
	if (intval($subject_info['stock']) - intval($orders['stock']) <=0)
	{
		return array (
			'code' => -4,
			'msg' => '库存不足' 
		);
	}
		
	//2.判断自己的的任务是否完成
	$tasks = $db->fetch_first("select count(1) tasks from iweite_huace_openid where rel_id= $uid and appid = '$appid'");	
	if ($tasks < intval($subject_info['tasks']))
	{
		return array (
			'code' => -1,
			'msg' => '您的任务未完成'
		);
	}	
	
	// 3.写入订单库
	$crtime = time();		
	$query = $db->query("insert into iweite_huace_order (subject_id,uid,unionid,appid,name,mobile,provance,city,area,address,remarks,crtime,updatetime) value($subject_id,$uid,'$unionid','$appid','$name','$mobile','$provance','$city','$area','$address','$remarks',$crtime,$crtime)");
	if($query){
		return array (
			'code' => 0
		);
	}else
	{
		return array (
			'code' => -3,
			'msg' => '失败'
		);
	}
}

?>