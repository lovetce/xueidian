<?php
session_start();
header("Content-type: text/html; charset=utf-8");

require_once '../include/common_new.inc.php';
global $site_conf;

//接受的方法参数

// 接受的方法参数
$method         = isset($_GET["method"])        ? $_GET["method"]               : 'addScoreByShare';
$uid            = isset($_GET['uid'])           ? intval($_GET['uid'])          : 1;
$openid         = isset($_GET['openid'])        ? $_GET['openid'] : '123';
$sub         = isset($_GET['sub'])        ? $_GET['sub'] : 'xg';
$page       = isset($_GET['page'])        ? $_GET['page'] : 1;

$db = new dbstuff;
 $db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8");
//$db->connect('rm-wz90046qx6i6vu3cp0o.mysql.rds.aliyuncs.com', 'jscommon', 'jscommon_123', 'fxmall', $pconnectz, true, "utf8");

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
else if($method == "isMyshare")
{

	
 	$re = isMyshare($db,$uid, $openid,$site_conf['needtimes']);
	echo json_encode($re);
	$db->close();
	exit;
}
//积分列表
else if($method == "getMyScoreList")
{
    $re = getMyScoreList($db,$uid,$page);
    echo json_encode($re);
    $db->close();
    exit;
}
//邀请好友送积分
else if($method == "addScoreByShare")
{
    $re = addScoreByShare($db,$uid, $openid,$site_conf['hyscore']);
    echo json_encode($re);
    $db->close();
    exit;
}
//关注送积分
else if($method == "addScoreByGz")
{
    $re = addScoreByGz($db,$uid,$sub,100);
    echo json_encode($re);
    $db->close();
    exit;
}




//判断是否本人
function getMyScoreList($db,$uid,$page)
{
    $pageCount = 10;
    $limit = ($page-1) * $pageCount .",". $pageCount;

    $list       =  array();


    $query      = $db->query("select score,add_time,score_type from iweite_huace_score  where  uid = $uid order by id desc limit $limit");
    while ($rs  = $db->fetch_array($query))
    {
        $list[] = array(
            'score'    => $rs['score'],
            'type'    => $rs['score_type'],
            'crtime'   => date("Y-m-d H:i:s",$rs['add_time'])
        );

         
    }
    return array('code'=>0,'data'=>$list);
}



//判断是否本人
function isMyshare($db,  $uid, $openid,$needtimes)
{
    //获取用户是否参与
    $sql="select openid,helptimes,username,face  from iweite_huace_openid where tid=$uid";
    $userInfo = $db->fetch_first($sql);
    if(empty($userInfo)){
        return array('code'=>1,'msg'=>'用户信息获取失败');
    }

    if($userInfo['openid']==$openid){
        return array('code'=>0,'msg'=>'是本人','status'=>1,'helptimes'=>$userInfo['helptimes'],'need'=>$needtimes,'username'=>$userInfo['username'],'face'=>$userInfo['face']);
    }else{
        return array('code'=>0,'msg'=>'不是本人','status'=>0,'helptimes'=>$userInfo['helptimes'],'need'=>$needtimes,'username'=>$userInfo['username'],'face'=>$userInfo['face']);
    }
}

//邀请好友加积分
function addScoreByShare($db, $uid, $openid,$score)
{
    //获取用户是否参与
    $sql="select count(*) as count  from iweite_huace_score where openid='$openid' and uid=$uid";
    $count = $db->fetch_first($sql);

    if(!empty($count)&&$count['count']>0){
        return array('code'=>-2,'msg'=>'您已经为他添加过');
    }
    $sql="select tid  from iweite_huace_openid where openid='$openid'";
    $tid = $db->fetch_first($sql);
    if(empty($tid)&&$tid['tid']<=0){
        return array('code'=>-1,'msg'=>'没有此用户信息');
    }elseif ($tid['tid']==$uid){
        return array('code'=>-1,'msg'=>'您不能为自己助力');
    }
    $crtime=time();
    $sql="insert into iweite_huace_score (openid,uid,add_time,score,type) values('$openid',$uid,$crtime,$score,1)";
    $query = $db->query($sql);
    if($query){
        $sql="update  iweite_huace_openid set score =score+$score where tid=$uid";
        $query = $db->query($sql);
        if($query){
            return array('code'=>0);
        }else{
            return array('code'=>-3);
        }
    }
}

//关注加积分
function addScoreByGz($db, $uid,$sub,$score)
{
    //获取用户是否参与
    $sql="select count(*) as count  from iweite_huace_score where uid=$uid and type=2 and sub='$sub'";
    $count = $db->fetch_first($sql);

    if(!empty($count)&&$count['count']>0){
        return array('code'=>-2,'msg'=>'您已经添加过');
    }

    $crtime=time();
    $sql="insert into iweite_huace_score (uid,add_time,score,type,sub) values($uid,$crtime,$score,2,'$sub')";
    $query = $db->query($sql);
    if($query){
        $sql="update  iweite_huace_openid set score =score+$score where tid=$uid";
        $query = $db->query($sql);
        if($query){
            return array('code'=>0);
        }else{
            return array('code'=>-1);
        }
    }
}



?>