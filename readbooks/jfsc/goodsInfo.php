<?php
session_start();
header("Content-type: text/html; charset=utf-8");

require_once '../include/common_new.inc.php';
global $site_conf;

//接受的方法参数

// 接受的方法参数
$method         = isset($_GET["method"])        ? $_GET["method"]               : 'isMyshare';
$id            = isset($_GET['id'])           ? intval($_GET['id'])          : 1;
$openid         = isset($_GET['openid'])        ? $_GET['openid'] : '123';
$page           = isset($_GET['page'])        ? $_GET['page'] : 1;

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8");

if(!$db)
{
    echo json_encode(array('status'=>-1,'msg'=>'数据库链接失败'));
    exit;
}


//小说项目 PHP版本为 5.3

/**
 * 展示接口所对应的方法， 防止后续代码过长查看不方便。
 *
 * 获取用户对应的数据信息         -   getUidUkey
 */

if(!$openid)
{
    echo json_encode(array('status'=>-1,'msg'=>'获取用户信息失败'));
    exit;
}


if($method == "")
{
	$returnArray = array('code' => 1);
	echo json_encode($returnArray);
	$db->close();
	exit;
}



//接受交易状态
else if($method == "getGoodsList")
{

	
 	$re = getGoodsList($db,$page);
	echo json_encode($re);
	$db->close();
	exit;
}
//接受交易状态
else if($method == "getOneGoods")
{
    $re = getOneGoods($db,$id);
    echo json_encode($re);
    $db->close();
    exit;
}








//判断是否本人
function getGoodsList($db,$page)
{
$pageCount = 10;
$limit = ($page-1) * $pageCount .",". $pageCount;

$list       =  array();


$query      = $db->query("select * from iweite_huace_list where  is_display=1 order by order_num desc,score asc limit $limit");
while ($rs  = $db->fetch_array($query))
{
	$list[] = array(
	           'id'   => $rs['id'],
                'name'   => $rs['name'],
                'info'    => $rs['info'],
	            'score'    => $rs['score'],
                'banner'    => $rs['banner'],
                'exchange'   => $rs['exchange'],
	            'stock'   => $rs['stock'],
        );

   
}
return array('code'=>0,'data'=>$list);
}

//获取商品详情
function getOneGoods($db, $id)
{
    $sql="select name,info,score,banner,exchange from iweite_huace_list where id=$id limit 1";
    $query = $db->query($sql);
    while ($rs = $db->fetch_array($query))
    {
        $data = array(
            'name' => $rs['name'],
            'info' => $rs['info'],
            'score'  => $rs['score'],
            'banner'  => $rs['banner'],
            'exchange'  => $rs['exchange'],
        );

    }
    return array('code'=>0,'data'=>$data);

}



?>