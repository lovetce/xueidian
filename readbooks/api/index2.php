<?php
header("Content-type: text/html; charset=utf-8");
require_once '../include/common.inc.php';
require_once '../include/wechat.class.php';
require_once '../include/medoo.php';
/*require_once '../include/XiaoiBot.php';*/
require_once './process.php';

$options = array(
    'token' => $_WEITE['token'], //
    'encodingaeskey' => $_WEITE['encodingaeskey'], // ncodingAESKey
    'appid' => $_WEITE['appid'], //
    'appsecret' => $_WEITE['appsecret'],
    'database' => $database
);

$weObj = new Wechat($options);
$weObj->valid();
$token = $weObj->checkAuth();
$type = $weObj->getRev()->getRevType();
$from_openid = $weObj->getRevFrom();
$myappid = $_WEITE['appid'];
global  $rand_domains ;
$rand_domains = $site_conf['rand_domains'];
$user = $database->query("select * from `iweite_huace_openid` where openid='$from_openid' and appid='$myappid' order by tid desc limit 1")->fetchAll();
if(count($user)>0)
    $user = $user[0];
if(!$user){
    $user_info = $weObj->getUserInfo($from_openid);
    $nickname = $user_info['nickname'];
    $headimgurl = $user_info['headimgurl'];
    $sex = intval($user_info['sex']);
    $province = $user_info['province'];
    $city = $user_info['city'];    
    $database->query("insert into `iweite_huace_openid`(appid, openid,dateline,username,face, province, city, sex, subscribe) value ('$myappid', '$from_openid','$timestamp','$nickname','$headimgurl', '$province', '$city', $sex, 1)");
}
else
{
    if (($user['username'] == '匿名') || ($user['username'] == ''))
    {
        $user_info = $weObj->getUserInfo($from_openid);
        $nickname = addslashes($user_info['nickname']);
        $headimgurl = $user_info['headimgurl'];
        $sex = intval($user_info['sex']);
        $province = $user_info['province'];
        $city = $user_info['city'];
        $db->query("update `iweite_huace_openid` set username= '$nickname',face = '$headimgurl',province='$province',city='$city',sex=$sex where openid= '$from_openid' and appid = '$myappid' ");
    }
}
$domains= $_WEITE['domains'];
switch ($type) {
    case Wechat::MSGTYPE_TEXT://文本消息
        $keycode = $weObj->getRevContent();
        //首先查找关联表,找出这个要回复的类型
        $result = $database->query("SELECT umr.`reply_id`,umr.`reply_type` FROM `iweite_huace_wx_union_menu_reply` umr
                            WHERE 
                            umr.`appid` = '".$_WEITE['appid']."' 
                            AND umr.`type` = 'text' 
                            AND LOCATE(umr.`keycode`,'$keycode')>0 ")->fetchAll();
                            //AND umr.`keycode` like '%".$keycode."%' ")->fetchAll();
        if(count($result)>=1){
            $table_suffix = $result[0]['reply_type'];
            $reply_id = $result[0]['reply_id'];
            //通过关联表找出匹配关键词的回复类型，再根据类型去对应的表里找回复内容
            process($table_suffix,$reply_id,$weObj,$database,$domains,$_WEITE['appid']);
        }else{
            //$bot_flag = 1;//机器人开关
            /*if($bot_flag){
                $askResult = callBot($keycode,$bot_app_key,$bot_app_secret);
                if($askResult[0]==200)
                    $weObj->text($askResult[1])->reply();
            }*/
            //没有匹配到关键字的情况下的回复
            if($auto_reply_flag){
                $keycode = "自动回复";
                //首先查找关联表,找出这个要回复的类型
                $result = $database->query("SELECT umr.`reply_id`,umr.`reply_type` FROM `iweite_huace_wx_union_menu_reply` umr
                            WHERE 
                            umr.`appid` = '".$_WEITE['appid']."' 
                            AND umr.`type` = 'text' 
                            AND umr.`keycode` = '$keycode' ")->fetchAll();
                if(count($result)==1){
                    $table_suffix = $result[0]['reply_type'];
                    $reply_id = $result[0]['reply_id'];
                    //通过关联表找出匹配关键词的回复类型，再根据类型去对应的表里找回复内容
                    process($table_suffix,$reply_id,$weObj,$database,$domains,$_WEITE['appid']);
                }
            }
        }
        exit();
        break;
    case Wechat::MSGTYPE_EVENT://事件
        $res = $weObj->getRevEvent();

        //关注事件
        if (strtolower($res['event']) == 'subscribe')
        {
            //置关注标志
            $db->query("update `iweite_huace_openid` set subscribe = 1  where openid= '$from_openid' and appid = '$myappid' ");
        }
        else if (strtolower($res['event']) == 'unsubscribe')
        {
            $db->query("update `iweite_huace_openid` set subscribe = 0  where openid= '$from_openid' and appid = '$myappid' ");
        }
        if(strtolower($res['event'])=='click' || strtolower($res['event'])=='subscribe')
        {
            $sql = "SELECT umr.`reply_id`,umr.`reply_type` FROM `iweite_huace_wx_union_menu_reply` umr
                                WHERE umr.`appid` = '".$_WEITE['appid']."' 
                                AND umr.`type` = '".strtolower($res['event'])."'" ;
             if(strtolower($res['event'])=='click' )
                 $sql = $sql."AND umr.`keycode` = '".$res['key']."' ";

            //首先查找关联表
            $result = $database->query($sql)->fetchAll();

            if(count($result)==1){
                $table_suffix = $result[0]['reply_type'];
                $reply_id = $result[0]['reply_id'];
                //通过关联表找出匹配关键词的回复类型，再根据类型去对应的表里找回复内容
                process($table_suffix,$reply_id,$weObj,$database,$domains,$_WEITE['appid']);
            }
        }else if (strtoupper($res['event'])=='unsubscribe'){
            //取消关注后的操作，逻辑删除用户信息
        }
        exit();
        break;
    case Wechat::MSGTYPE_IMAGE://图片消息
        if($auto_reply_flag){
            $keycode = "自动回复";
            //首先查找关联表,找出这个要回复的类型
            $result = $database->query("SELECT umr.`reply_id`,umr.`reply_type` FROM `iweite_huace_wx_union_menu_reply` umr
                            WHERE 
                            umr.`appid` = '".$_WEITE['appid']."' 
                            AND umr.`type` = 'text' 
                            AND umr.`keycode` = '$keycode' ")->fetchAll();
            if(count($result)==1){
                $table_suffix = $result[0]['reply_type'];
                $reply_id = $result[0]['reply_id'];
                //通过关联表找出匹配关键词的回复类型，再根据类型去对应的表里找回复内容
                process($table_suffix,$reply_id,$weObj,$database,$domains,$_WEITE['appid']);
            }
        }
        exit();
        break;
    default:
        $weObj->text("")->reply();
}

//消息处理
function process($type,$reply_id,$weObj,$database,$domains,$appid){
    if($type=='text'){//回复文本消息
        $result = $database->query("SELECT * FROM `iweite_huace_wx_reply_$type` WHERE id ='$reply_id' AND appid = '$appid' ")->fetchAll();

        if(count($result)==1) {
            $content = $result[0]['content'];
            //替换openid标签
            $content = str_replace("{openid}",$weObj->getRevFrom(),$content);
            $domain = $domains[array_rand($domains)];
            //替换域名标签
            $content = str_replace("{domain}",$domain,$content);
            $weObj->text($content)->reply();
        }
    }else if($type=='news'){//回复图文消息
        $result = $database->query("SELECT item.`Title`,item.`Description`,item.`PicUrl`,item.`Url` 
                                    FROM `iweite_huace_wx_reply_news_item` item  
                                        WHERE  ( SELECT FIND_IN_SET( item.id, news.`Articles` ) FROM `iweite_huace_wx_reply_news` news WHERE news.`id`=$reply_id)")->fetchAll();
        foreach ($result as &$row){
            //替换openid标签
            $row['Url'] = str_replace("{openid}",$weObj->getRevFrom(),$row['Url']);
            //替换随机主域名标签
            $rnd = mt_rand(0, count($GLOBALS['rand_domains']) - 1);
            $tzurl = $GLOBALS['rand_domains'][$rnd];
            $row['Url'] = str_replace("{rand_domains}",$tzurl,$row['Url']);
        }
        $weObj->news($result)->reply();
    }else if($type=='image'){//回复图片消息

    }else if($type=='voice'){//回复语音消息

    }else if($type=='video'){//回复视频消息

    }else if($type=='music'){//回复音乐消息

    }
}
/*function callBot($keyword,$bot_app_key,$bot_app_secret){
    $bot = new XiaoiBot( array( 'app_key' => $bot_app_key, 'app_secret' => $bot_app_secret ) );
    return $bot->ask($keyword);
}*/
?>