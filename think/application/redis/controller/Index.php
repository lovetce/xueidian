<?php
/**
 * Created by PhpStorm.
 * User: parasol
 * Date: 2018/5/29 0029
 * Time: 下午 4:47
 */

namespace app\redis\controller;


use think\Cache;
use redis\RedisCluster;

class Index
{

    





/**
 * 处理json
 */
//    public function index(){
//
//
//
////        $json='{"id":"6044","title":"\u4f60\u662f16\u578b\u4eba\u683c\u4e2d\u7684\u54ea\u4e00\u4e2a\uff1f","logo":"quce\/15180835093zMk4.jpg","desc":"\u4f60\u662f16\u578b\u4eba\u683c\u4e2d\u7684\u54ea\u4e00\u4e2a\uff1f\u8010\u5fc3\u505a\u5b8c\u8fd9\u4e2a\u6d4b\u8bd5\u4f60\u4e00\u5b9a\u4f1a\u5bf9\u81ea\u5df1\u6709\u4e2a\u65b0\u7684\u8ba4\u8bc6\uff0c\u66f4\u4e86\u89e3\u81ea\u5df1\uff0c\u5bf9\u81ea\u5df1\u7684\u5b9a\u4f4d\u66f4\u660e\u786e\u3002\u5feb\u6765\u6d4b\u6d4b\u5427\uff01","question":"[{\"img\":\"\",\"question\":\"\u4e0b\u9762\u54ea\u4e00\u4ef6\u4e8b\u542c\u8d77\u6765\u6bd4\u8f83\u5438\u5f15\u4f60\uff1f\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u51fa\u53bb\u805a\u4f1a\uff0c\u7ed3\u8bc6\u65b0\u670b\u53cb\uff0c\u627e\u70b9\u4e50\u5b50\",\"weight\":\"0\",\"img\":\"\"},\"b\":{\"title\":\"\u5f85\u5728\u5bb6\u91cc\u770b\u4e00\u90e8\u6709\u8da3\u7684\u7535\u5f71\u5e76\u4eab\u53d7\u4f60\u559c\u6b22\u7684\u5916\u5356\",\"weight\":\"2\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u5728\u793e\u4ea4\u573a\u666f\u4e2d\uff0c\u4f60\u901a\u5e38\uff1a\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u6574\u4f53\u6765\u8bf4\u5f88\u5065\u8c08\",\"weight\":\"1\",\"img\":\"\"},\"b\":{\"title\":\"\u6bd4\u8f83\u5b89\u9759\uff0c\u6bd4\u8f83\u4fdd\u7559\",\"weight\":\"4\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u4ec0\u4e48\u6837\u7684\u76f8\u5904\u4f1a\u8ba9\u4f60\u89c9\u5f97\u66f4\u8212\u670d\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u548c\u4ed6\u4eba\u805a\u4f1a\u7684\u65f6\u5019\u89c9\u5f97\u81ea\u5df1\u66f4\u6709\u80fd\u91cf\",\"weight\":\"0\",\"img\":\"\"},\"b\":{\"title\":\"\u72ec\u5904\u7684\u65f6\u5019\u5c31\u662f\u81ea\u5df1\u7ed9\u81ea\u5df1\u5145\u7535\u7684\u65f6\u5019\",\"weight\":\"2\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u4f60\u66f4\u503e\u5411\u4e8e\u76f8\u4fe1\uff1a\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u4f60\u7684\u76f4\u89c9\",\"weight\":\"1\",\"img\":\"\"},\"b\":{\"title\":\"\u770b\u5230\u7684\u4e8b\u5b9e\u548c\u4ee5\u5f80\u7684\u7ecf\u9a8c\",\"weight\":\"3\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u4e0b\u9762\u8fd9\u4e24\u4e2a\u9999\u6c34\u7684\u540d\u5b57\u4f60\u66f4\u559c\u6b22\u54ea\u4e2a\uff1f\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u6df1\u6e0a\u4e66\u7b80\",\"weight\":\"1\",\"img\":\"\"},\"b\":{\"title\":\"\u65e0\u4eba\u533a\u7684\u73ab\u7470\",\"weight\":\"4\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u51e1\u4e8b\u4f60\u559c\u6b22\uff1a\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u5148\u7eb5\u89c2\u5168\u5c40\uff0c\u4e86\u89e3\u6574\u4e2a\u5927\u80cc\u666f\",\"weight\":\"0\",\"img\":\"\"},\"b\":{\"title\":\"\u5148\u638c\u63e1\u7ec6\u8282\uff0c\u4e86\u89e3\u5177\u4f53\u60c5\u51b5\",\"weight\":\"3\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u901a\u5e38\u4f60\u505a\u51b3\u5b9a\u65f6\u7684\u7b2c\u4e00\u611f\u89c9\u662f\uff1a\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u5148\u7b26\u5408\u81ea\u5df1\u7684\u5fc3\u610f\",\"weight\":\"1\",\"img\":\"\"},\"b\":{\"title\":\"\u5148\u4f9d\u636e\u81ea\u5df1\u7684\u903b\u8f91\",\"weight\":\"2\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u5f53\u4f60\u4e0d\u540c\u610f\u4ed6\u4eba\u7684\u60f3\u6cd5\u65f6\uff1a\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u4f60\u5c3d\u53ef\u80fd\u5730\u907f\u514d\u4f24\u5bb3\u5bf9\u65b9\u7684\u611f\u60c5\uff0c\u5047\u5982\u4f1a\u7ed9\u5bf9\u65b9\u9020\u6210\u4f24\u5bb3\uff0c\u4f60\u503e\u5411\u4e8e\u5c3d\u91cf\u4e0d\u8bf4\",\"weight\":\"4\",\"img\":\"\"},\"b\":{\"title\":\"\u4f60\u901a\u5e38\u4f1a\u6beb\u65e0\u4fdd\u7559\u5730\u76f4\u8a00\u4e0d\u8bb3\uff0c\u559c\u6b22\u5c31\u4e8b\u8bba\u4e8b\uff0c\u56e0\u4e3a\u5bf9\u7684\u5c31\u662f\u5bf9\u7684\",\"weight\":\"1\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u8ba4\u8bc6\u4f60\u7684\u4eba\u503e\u5411\u4e8e\u5f62\u5bb9\u4f60\u4e3a\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u70ed\u60c5\u7684\uff0c\u968f\u548c\u7684\",\"weight\":\"1\",\"img\":\"\"},\"b\":{\"title\":\"\u903b\u8f91\u7684\uff0c\u51b7\u9759\u7684\",\"weight\":\"3\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u82e5\u4f60\u6709\u65f6\u95f4\u548c\u91d1\u94b1\uff0c\u4f60\u7684\u670b\u53cb\u9080\u8bf7\u4f60\u5230\u8fdc\u65b9\u5ea6\u5047\uff0c\u5e76\u4e14\u5728\u524d\u4e00\u5929\u624d\u901a\u77e5\uff0c\u4f60\u4f1a\uff1a\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u5fc5\u987b\u5148\u68c0\u67e5\u4f60\u7684\u65f6\u95f4\u8868\uff0c\u5373\u4f7f\u6ca1\u6709\u91cd\u8981\u5b89\u6392\uff0c\u4e5f\u4f1a\u56e0\u4e3a\u7a81\u7136\u901a\u77e5\u6253\u4e71\u4e86\u4f60\u539f\u6709\u6b65\u8c03\u800c\u7565\u6709\u4e0d\u5feb\",\"weight\":\"2\",\"img\":\"\"},\"b\":{\"title\":\"\u7acb\u5373\u6536\u62fe\u884c\u88c5\uff0c\u8fd8\u7b49\u5565\u5462\",\"weight\":\"0\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u4f60\u81ea\u5df1\u66f4\u559c\u6b22\u4ec0\u4e48\u6837\u7684\u751f\u6d3b\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u5404\u65b9\u9762\u90fd\u4e95\u4e95\u6709\u6761\uff0c\u9884\u5148\u5b89\u6392\",\"weight\":\"4\",\"img\":\"\"},\"b\":{\"title\":\"\u7a81\u5982\u5176\u6765\uff0c\u4ee4\u4eba\u60ca\u559c\",\"weight\":\"1\",\"img\":\"\"}},\"weight\":\"1\"},{\"img\":\"\",\"question\":\"\u5982\u679c\u6709\u4e00\u4e2a\u7ea6\u4f1a\uff0c\u4f60\u66f4\u503e\u5411\u4e8e\",\"audio\":\"\",\"answer\":{\"a\":{\"title\":\"\u4e8b\u5148\u77e5\u9053\u884c\u7a0b\uff1a\u8981\u53bb\u54ea\u91cc\uff0c\u6709\u8c01\u53c2\u52a0\uff0c\u4f60\u4f1a\u5728\u90a3\u91cc\u591a\u4e45\uff0c\u8be5\u5982\u4f55\u6253\u626e\",\"weight\":\"0\",\"img\":\"\"},\"b\":{\"title\":\"\u4e00\u5207\u987a\u5176\u81ea\u7136\uff0c\u66f4\u559c\u6b22\u4e0d\u671f\u800c\u9047\",\"weight\":\"3\",\"img\":\"\"}},\"weight\":\"1\"}]","original":"0","type":"2","cid":"6","img":"quce\/1518079850Ib0vS.jpg","tpl_id":"10","is_game":"0","share_url":"http:\/\/mp.weixin.qq.com\/s\/dn3WUfBP8PipE0udOLTvcQ","view":"428400","like_num":"0","utime":"1518084601","subscribe":"16","new_plan":"2","author":"86","status":"1"}';
////
////
////        $data=json_decode($json,true);
////        $quest=$data['question'];
//////        unset()
////        $quest= json_decode($quest,true);
////        $data['question']=$quest;
////      return json($data);
//
////      $json=json_encode($json);
////      dump($json);
////        $redis=new \Redis();
//
//
//    }


//    public function index(){
//       // 只有一台 Redis 的应用
//        $redis = new RedisCluster();
//        $redis->connect(array('host'=>'127.0.0.1','port'=>6379));
//        $cron_id = 10001;
//        $CRON_KEY = 'CRON_LIST';
//        $PHONE_KEY = 'PHONE_LIST:'.$cron_id;
//        $cron = $redis->hget($CRON_KEY,$cron_id);
//        if(empty($cron)){
//            $cron = array('id'=>10,'name'=>'jackluo');//mysql data
//            $redis->hset($CRON_KEY,$cron_id,$cron); // set redis
//        }
//        $phone_list = $redis->lrange($PHONE_KEY,0,-1);
////        var_dump($phone_list);
////        die;
//        if(empty($phone_list)){
//            $phone_list =explode(',','13228191831,18608041585');    //mysql data
//            if($phone_list){
//                $redis->multi();
//                foreach ($phone_list as $phone) {
//                    $redis->lpush($PHONE_KEY,$phone);
//                }
//                $redis->exec();
//            }
//        }
//    }


}