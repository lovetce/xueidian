<?php
session_start();
header("Content-type: text/html; charset=utf-8");
require_once '../include/common.inc.php';
require_once '../include/wx_jsdk_class.php';


$cookietime=3600*24*30;
$string= file_get_contents('php://input');
$obj=json_decode($string);
$action=$obj->action;
$openid=$obj->openid;
$original_id=$obj->original_id;
// $gender= $obj->sex;   //性别
if(!$openid)  exit('{"errcode":"0","errmsg":"参数错误"}');

$nickname="匿名";
$sex = 0;
$province = '';
$city = '';
$country = '';

$returnaccess = $jssdk->getToken($original_id);

//根据用户openid获取用户信息
$wxuser = getwuserinfo($openid, $returnaccess);
$headimgurl=$_WEITE['web_weburl']."assets/images/logo.jpg";

//$openid = 'oZQ_Ws_vsWQQN8_xXbcaMx5MaFcI';
$o= $db->fetch_first("select * from {$tablepre}openid where openid='$openid' order by tid desc limit 1");
$ap_id = $site_conf['appid'];
$mn_ym = $site_conf['maindomain'];

file_put_contents("log.txt", 'returnaccess='.$returnaccess."\n", FILE_APPEND);

// 由于人为数据删除  操作逻辑异常   补充条件 ->  动作为取消关注  不执行   
if(!$o && $action != "unsubscribe")
{
    if($wxuser['nickname'])
    {
        $nickname = str_replace("'","",addslashes($wxuser['nickname']));
        $headimgurl = $wxuser['headimgurl'];
        $sex = intval($wxuser['sex']);
        $province = str_replace("'","",$wxuser['province']);
        $city = str_replace("'","",$wxuser['city']);
        $country = str_replace("'","",$wxuser['country']);
        $unionid = str_replace("'","",$wxuser['unionid']);
    }

    //插入微信用户信息    
    $db->query("insert into {$tablepre}openid(openid,dateline,username,face, province, city,country,sex,appid,crtime,unionid) value ('$openid','$timestamp','$nickname','$headimgurl', '$province', '$city','$country', $sex,'$ap_id','$timestamp','$unionid')");
}

//关注
if($action=="subscribe"){
    if ($site_conf['subscribe']=="fxmall"){
        $access_token = $returnaccess;
        $type = "image";
        $task_url = "http://".$mn_ym."/jfsc/html/task.php";
        $ntime = time();
        //查询是否通过海报扫码关注进来的
        $unionid = str_replace("'","",$wxuser['unionid']);
        $nickname = str_replace("'","",addslashes($wxuser['nickname']));

        //查询当前活动信息
        $hdrs = $db->fetch_first("SELECT * FROM {$tablepre}subject WHERE appid='$ap_id' and ishidden=0 LIMIT 1");
        $subject_id = $hdrs['id'];

        $rs = $db->fetch_first("SELECT * FROM {$tablepre}share_contact WHERE unionid = '$unionid' AND appid = '$ap_id' AND issend = 0 ORDER BY crtime DESC LIMIT 1");
        $sid = 0; $sopenid = "";
        

        if($rs){
            //更新好友关系            
            $sid = $rs['sid'];    
            $sopenid = $rs['sopenid'];
            $db->query("update {$tablepre}openid set rel_id=$sid,rel_time=$ntime where openid='$openid'");
            $t1 = "您通过".$rs['sname'].'的海报成为我们的书友！';
        }else{
            $t1 = "欢迎您成为我们的书友！";
        }

        //1、发送欢迎语句        
        sendMsg(array(
            'touser'  => $openid,
            'msgtype' => 'text',
            'text' => array(
                'content' => $t1
            )
        ),$returnaccess);                    
        
        //2、提示用户海报已生成
        $nickname = addslashes($wxuser['nickname']);        
        $txt = "@".$nickname."您的专属读书海报已生成！\\n\\n没有谁是一坐孤岛，每本书都是一个世界，加入我们一起读书吧！\\n\\n<a href='$task_url'>点击参与本期包邮赠书活动</a>";
        sendMsg(array(
            'touser'  => $openid,
            'msgtype' => 'text',
            'text' => array(
                'content' => $txt
            )
        ),$returnaccess);
        
        $mdid = "";
        $tid=0;        
        $rs=$db->fetch_first("select media_id,tid,uid,hb_ctime from {$tablepre}openid where openid='$openid' order by tid desc limit 1");
        if($rs){
            $mdid = $rs['media_id'];            
            $tid = $rs['tid'];
        }
        //3、发送海报，判断用户海报media_id是否为空
        $expire_time = $rs['hb_ctime'] + 2.5*24*3600;
        if(empty($mdid) || $expire_time < $ntime){
            require_once '../include/fun_img.php';
            include_once './scimg.php';      //引入图片
             
            $filepath = "./images/$outname.png";    //此处为图片链接
            
            $filedata = array("media"=>"@".$filepath);
            $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$access_token&type=$type";
            
            $result = json_decode(https_request_test($url,$filedata),true);
            $media_id = $result['media_id'];
            
            $db->query("update {$tablepre}openid set media_id='$media_id',hb_ctime='$ntime' where openid='$openid'");

        }else{
            $media_id = $mdid;
            $filepath = "./images/1.png";
        }

        sendMsg(array(
            'touser'  => $openid,
            'msgtype' => 'image',
            'image' => array(
                'media_id' => $media_id
            )
        ),$returnaccess);

        //echo '{"errcode":"2","media_id":"'.$media_id.'"}';
        unlink($filepath);  // 删除文件        

        if($sid>0){
            
            //查询邀请好友个数
            $rsc = $db->fetch_first("select count(1) total from {$tablepre}openid where rel_id=$sid");
            $total = intval($rsc['total']);
            $taskrs = intval($hdrs['tasks']);
            $fnum = intval($hdrs['stock']);
            //差多少人完成任务
            if($total < $taskrs)
                $cha = $taskrs - $total;
            else
                $cha = 0;

            //查询已完成任务人数
            $rst = $db->fetch_first("SELECT COUNT(1) gs FROM {$tablepre}order where subject_id=$subject_id");
            // file_put_contents("log.txt", 'title='.$hdrs['title']."\n", FILE_APPEND);
            // file_put_contents("log.txt", 'nickname='.$nickname."\n", FILE_APPEND);            
            // file_put_contents("log.txt", 'taskrs='.$taskrs."\n", FILE_APPEND);
            // file_put_contents("log.txt", 'total='.$total."\n", FILE_APPEND);
            // file_put_contents("log.txt", 'task_url='.$task_url."\n", FILE_APPEND);
            // file_put_contents("log.txt", 'cha='.$cha."\n", FILE_APPEND);
            // file_put_contents("log.txt", 'fnum='.$fnum."\n", FILE_APPEND);            
            
            $gs = intval($rst['gs']);
            $yu = $fnum - $gs;
            // file_put_contents("log.txt", '22222=\n', FILE_APPEND);
            // file_put_contents("log.txt", 'yu='.$yu."\n", FILE_APPEND);
            // file_put_contents("log.txt", 'gs='.$gs."\n", FILE_APPEND);

            //判断是否完成任务            
            $query = $db->query("select * from {$tablepre}openid where rel_id=$sid and appid='$ap_id' order by tid desc");            

            $rslt = $db->fetch_array($query);
            $lttotal = $db->num_rows($query);
            
            if($lttotal >= $taskrs){
                $t2 = "任务完成进度通知\\n任务名称：".$hdrs['title']."\\n处理类型：".$nickname."已通过海报成为您的书友\\n处理结果：\\n剩余书籍：".$yu."份\\n任务目标：".$taskrs."人\\n已经完成：".$total."人\\n<a href='".$task_url."'>您已获得免费领取资格，点击领取！</a>";
            }else{
                $t2 = "任务完成进度通知\\n任务名称：".$hdrs['title']."\\n处理类型：".$nickname."已通过海报成为您的书友\\n处理结果：\\n剩余书籍：".$yu."份\\n任务目标：".$taskrs."人\\n已经完成：".$total."人\\n<a href='".$task_url."'>您还差".$cha."位小伙伴的支持，点击查看活动规则！</a>";
            }
            
            sendMsg(array(
                'touser'  => $sopenid,
                'msgtype' => 'text',
                'text' => array(
                    'content' => $t2
                )
            ),$returnaccess);
                       
            if($lttotal == $taskrs){
                //完成28个邀请任务
                while($dt = $db->fetch_array($query)){
                    $t3 = "任务完成进度通知\\n任务名称：第1期 世界读书月包邮赠书活动\\n处理类型：您的书友 ".$dt['sname']." 已完成任务，获得免费领取书籍资格！\\n处理结果：\\n<a href='".$task_url."'>点击查看剩余数据，您加油哦！</a>";
                    //file_put_contents("log.txt", 'openid='.$dt['openid'].'t3='.$t3.'|\n', FILE_APPEND);
                    sendMsg(array(
                        'touser'  => addslashes($dt['openid']),
                        'msgtype' => 'text',
                        'text' => array(
                            'content' => $t3
                        )
                    ),$returnaccess);
                }
            }
        }

        $db->free_result($query);
        $db->close();
        exit;
    }
}
else if($action=="unsubscribe"){//取消关注
    $app_id = $site_conf['appid'];
    $os= $db->fetch_first("select * from {$tablepre}openid where openid='$openid' order by tid desc limit 1");
    if($os){//是否存在 存在继续执行
        $db->query("update {$tablepre}openid set subscribe=0 where openid='$openid'"); //取关 更新对应appid关注状态
        $q_uid = $os['uid']; //获取unionid表对应uid
        $username = $os['username'];
        
        //查询积分表是否有对应的因此人关注而产生的积分  有 扣积分
        $jf_rs = $db->fetch_first("select * from {$tablepre}score where q_uid=$q_uid and score_type=1   and sub='$app_id' order by id desc limit 1");
        if($jf_rs){
            $o_openid = $jf_rs['openid'];//取关人员 上级openid
            $jf = $jf_rs['score'];
            $o_rs = $db->fetch_first("select * from {$tablepre}openid where openid='$o_openid' order by tid desc limit 1");
            if($o_rs){ //存在对应信息 且积分大于对应积分
                $old_jf = $o_rs['score'];
                $tid = $o_rs['tid'];
                if($old_jf>=$jf){//大于等会对应积分 减积分  写入积分记录 发送消息
                    $qg_rs = $db->fetch_first("select * from {$tablepre}score where q_uid=$q_uid and score_type=-2   and sub='$app_id' and openid='$o_openid' order by id desc limit 1");
                    if(!$qg_rs){
                        $db->query("update {$tablepre}openid set score=score-10 where openid='$o_openid'");
                        $db->query("insert into {$tablepre}score(uid,openid,score,score_type,add_time,sub,q_uid) value($tid,'$o_openid',-10,-2,'$timestamp','$app_id',$q_uid) ");
                         
                        $dq_jf = $old_jf - $jf;
                        $content .= "积分变动提醒\n";
                        $content .= date("Y-m-d H:i:s")."\\n\\n";
                        $content .= "变动原因：好友取关($username)\n";
                        $content .= "变动数额：-10 \n";
                        $content .= "当前积分：$dq_jf \\n\\n";
                        sendMsg(array(
                            'touser'  => $o_openid,
                            'msgtype' => 'text',
                            'text' => array(
                                'content' => $content
                            )
                        ),$returnaccess);
                    }
                   
                }
            }
        }
    }   
    exit;
}
else if($action=="score_qd"){//每日签到
    $scrs=$db->fetch_first("select id from {$tablepre}score where  openid='$openid' and score_type=3 and FROM_UNIXTIME(add_time,'%Y-%m-%d')=DATE_FORMAT(NOW(),'%Y-%m-%d') order by id desc limit 1");
    if(!$scrs){
        $mdid = "";
        $tid=0;
        $score = 0;
        $q_uid =0;
        $rs=$db->fetch_first("select media_id,score,tid,uid from {$tablepre}openid where  openid='$openid'  order by tid desc limit 1");
        if($rs){
            $mdid = $rs['media_id'];
            $score = $rs['score'];
            $tid = $rs['tid'];
            $q_uid = $rs['uid'];
        }
        
        $db->query("update {$tablepre}openid set score=score+5 where openid='$openid'");
        $app_id = $site_conf['appid'];
        $db->query("insert into {$tablepre}score(uid,openid,score,score_type,add_time,sub,q_uid) value($tid,'$openid',5,3,'$timestamp','$app_id',$q_uid) ");
        $msg = "签到成功,积分+5";
    }else{
        $msg = "今日已签到";
    }
    echo '{"errcode":"0","errmsg":"'.$msg.'"}';
    exit;
}
else if($action=="score_my"){//我的积分
    $score = 0;
    $mdid = "";
    $tid=0;
    $rs=$db->fetch_first("select media_id,score,tid from {$tablepre}openid where  openid='$openid'  order by tid desc limit 1");
    if($rs){
        $mdid = $rs['media_id'];
        $score = $rs['score'];
        $tid = $rs['tid'];
    }
    $msg = "我的积分：".$score;
    echo '{"errcode":"0","errmsg":"'.$msg.'"}';
    exit;
}
else if($action=="haibao_jf"){//海报积分
    $access_token = $returnaccess;
    $type = "image";
    $nickname = addslashes($wxuser['nickname']);
    $ntime = time();
    $task_url = "http://".$mn_ym."/jfsc/html/task.php";
    $txt = "@".$nickname."您的专属读书海报已生成！\\n\\n没有谁是一坐孤岛，每本书都是一个世界，加入我们一起读书吧！\\n\\n<a href='$task_url'>点击参与本期包邮赠书活动</a>";
    sendMsg(array(
        'touser'  => $openid,
        'msgtype' => 'text',
        'text' => array(
            'content' => $txt
        )
    ),$returnaccess);
    $score = 0;
    $mdid = "";
    $tid=0;
    $rs=$db->fetch_first("select media_id,score,tid,hb_ctime from {$tablepre}openid where openid='$openid' and appid='$ap_id' order by tid desc limit 1");
    if($rs){
        $mdid = $rs['media_id'];
        $score = $rs['score'];
        $tid = $rs['tid'];
    }
    $expire_time = $rs['hb_ctime'] + 2.5*24*3600;
    if(empty($mdid) || $expire_time < $ntime){        
        // file_put_contents("log.txt", 'mdid='.$mdid."\n", FILE_APPEND);
        // file_put_contents("log.txt", 'expire_time='.$expire_time."\n", FILE_APPEND);

        require_once '../include/fun_img.php';
        include_once './scimg.php';      //引入图片
         
        $filepath = "./images/$outname.png";    //此处为图片链接
    
        $filedata = array("media"=>"@".$filepath);
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$access_token&type=$type";
        $result = json_decode(https_request_test($url,$filedata),true);
        $media_id = $result['media_id'];        
        $db->query("update {$tablepre}openid set media_id='$media_id',hb_ctime='$ntime' where openid='$openid'");
        
    }else{
        $media_id = $mdid;
        $filepath = "./images/1.png";
    }
    echo '{"errcode":"0","media_id":"'.$media_id.'"}';
    exit;
}
else if($action=="search_wl"){//查询快递
    $unionid = str_replace("'","",$wxuser['unionid']);
    $hdrs = $db->fetch_first("SELECT * FROM {$tablepre}subject WHERE appid='$ap_id' and ishidden=0 LIMIT 1");
    $subject_id = $hdrs['id'];
    $rsu = $db->fetch_first("select * from {$tablepre}order where appid ='$ap_id' and unionid ='$unionid' and subject_id=$subject_id limit 1");    
    if(!$rsu){
        $task_url = "http://".$mn_ym."/jfsc/html/yqji.php";
        echo '{"errcode":"0","errmsg":"您没有完成过任务，<a href=\"'.$task_url.'\">点击查看详情</a>"}';
    }
    else if($rsu && ($rsu['status']==2 || $rsu['status']==3)){
        echo '{"errcode":"0","errmsg":"快递名称：'.$rsu['wlname'].'  快递单号：'.$rsu['wlno'].'"}';
    }else{
        echo '{"errcode":"0","errmsg":"正在为您发货，请耐心等待！"}';
    }
    $db->close();
    exit;
}

function https_request_test($url,$data = null)
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

function getwuserinfo($openid, $returnaccess) {
    $access_token = $returnaccess;
    $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
    $wuser = https_request($url);
    $wuser = json_decode($wuser, true);
    return $wuser;
}

function u2g($a) {
    return is_array($a) ? array_map('u2g', $a) : diconv($a, 'UTF-8', CHARSET);
}

/**
 * 模拟提交参数，支持https提交 可用于各类api请求
 * @param string $url ： 提交的地址
 * @param array $data :POST数组
 * @param string $method : POST/GET，默认GET方式
 * @return mixed
 */
function callWebApi($url, $data='', $method='GET'){
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    if($method=='POST'){
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        if ($data != ''){
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        }
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作
    curl_close($curl); // 关闭CURL会话
}

function sendMsg($data,$returnaccess) {
    callWebApi('https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$returnaccess, toJson($data),'POST');
}

function toJson($data) {
    $data = json_encode(urlencodeAry($data));
    return urldecode($data);
}

/**
 * 将数据进行urlencode
 * @param array & string $data
 */
function urlencodeAry($data) {
    if(is_array($data)) {
        foreach($data as $key=>$val) {
            $data[$key] = urlencodeAry($val);
        }
        return $data;
    } else {
        return urlencode($data);
    }
}

?>