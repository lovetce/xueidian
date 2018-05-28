<?php
interface process{
    function process($type,$reply_id,$weObj,$database,$domains,$appid);
}
class defaultProcess implements process{
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
                $row['Url'] = str_replace("{openid}",$weObj->getRevFrom(),$row['Url']);
            }
            $weObj->news($result)->reply();
        }else if($type=='image'){//回复图片消息

        }else if($type=='voice'){//回复语音消息

        }else if($type=='video'){//回复视频消息

        }else if($type=='music'){//回复音乐消息

        }
    }
}

class teacherProcess implements process{
    function process($type,$reply_id,$weObj,$database,$domains,$appid){
        $msgtype = $weObj->getRev()->getRevType();
        if($type=='text'){//回复文本消息
            $result = $database->query("SELECT * FROM `iweite_huace_wx_reply_$type` WHERE id ='$reply_id' AND appid = '$appid' ")->fetchAll();

            if(count($result)==1) {
                $content = $result[0]['content'];
                //替换openid标签
                $content = str_replace("{openid}",$weObj->getRevFrom(),$content);
                $domain = $domains[array_rand($domains)];
                //替换域名标签
                $content = str_replace("{domain}",$domain,$content);
                //先找出用户信息
                $user = $database->get("openid","*",array('openid'=>$weObj->getRevFrom()));
                $uid = $user['tid'];
                //根据用户ID和appid找出记录，判断老师学生关系是否存在
                $record = $database->query("SELECT b.* FROM `iweite_huace_bs_teacher` a LEFT JOIN `iweite_huace_bs_focus` b ON a.`tid` = b.`id` WHERE a.uid = '$uid' AND b.`app_id`='$appid'")->fetchAll();

                if(count($record)==1){
                    $record = $record[0];
                    $content = str_replace("{username}",$user['username'],$content);
                    $content = str_replace("{teachername}",$record['wx_name'],$content);
                    $content = str_replace("{teacherurl}","http://".$_SERVER['HTTP_HOST']."/daihao1/guwen.php?tid=".$record['id'],$content);
                }elseif (count($record)==0){

                    $minteacher = $database->query("SELECT COUNT(b.`uid`) mins,a.* FROM `iweite_huace_bs_focus` a LEFT JOIN `iweite_huace_bs_teacher` b ON b.`tid` = a.`id` WHERE a.`app_id` = '$appid' GROUP BY b.tid ORDER BY mins ASC LIMIT 1")->fetchAll();

                    if(count($minteacher)==1){
                        $database->insert("bs_teacher",array('uid'=>$uid,'tid'=>$minteacher[0]['id'],'ctime'=>time()));
                        $content = str_replace("{username}",$user['username'],$content);
                        $content = str_replace("{teachername}",$minteacher[0]['wx_name'],$content);
                        $content = str_replace("{teacherurl}","http://".$_SERVER['HTTP_HOST']."/daihao1/guwen.php?tid=".$minteacher[0]['id'],$content);
                    }
                }
                $weObj->text($content)->reply();
            }
        }else if($type=='news'){//回复图文消息
            $result = $database->query("SELECT item.`Title`,item.`Description`,item.`PicUrl`,item.`Url` 
                                    FROM `iweite_huace_wx_reply_news_item` item  
                                        WHERE  ( SELECT FIND_IN_SET( item.id, news.`Articles` ) FROM `iweite_huace_wx_reply_news` news WHERE news.`id`=$reply_id)")->fetchAll();
            foreach ($result as &$row){
                $row['Url'] = str_replace("{openid}",$weObj->getRevFrom(),$row['Url']);
            }
            $weObj->news($result)->reply();
        }else if($type=='image'){//回复图片消息

        }else if($type=='voice'){//回复语音消息

        }else if($type=='video'){//回复视频消息

        }else if($type=='music'){//回复音乐消息

        }
    }
}
?>