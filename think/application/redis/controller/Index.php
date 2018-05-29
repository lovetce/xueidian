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
    public function index(){
       // 只有一台 Redis 的应用
        $redis = new RedisCluster();
        $redis->connect(array('host'=>'127.0.0.1','port'=>6379));
        $cron_id = 10001;
        $CRON_KEY = 'CRON_LIST';
        $PHONE_KEY = 'PHONE_LIST:'.$cron_id;
        $cron = $redis->hget($CRON_KEY,$cron_id);
        if(empty($cron)){
            $cron = array('id'=>10,'name'=>'jackluo');//mysql data
            $redis->hset($CRON_KEY,$cron_id,$cron); // set redis
        }
        $phone_list = $redis->lrange($PHONE_KEY,0,-1);
        if(empty($phone_list)){
            $phone_list =explode(',','13228191831,18608041585');    //mysql data
            if($phone_list){
                $redis->multi();
                foreach ($phone_list as $phone) {
                    $redis->lpush($PHONE_KEY,$phone);
                }
                $redis->exec();
            }
        }
    }


}