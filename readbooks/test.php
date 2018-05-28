<?php

    $host = "r-wz95c747eda6f9d4.redis.rds.aliyuncs.com";
    $port = 6379;
    $pwd ="Bstosb6vuIA";
    $redis = new Redis();    
    if ($redis->connect($host, $port) == false) {
        die($redis->getLastError());
    }
    if ($redis->auth($pwd) == false) {
        die($redis->getLastError());
    }
    $key = "WX_CONFIG:OriginalId:gh_e17aaab22fe3";		
    $data = json_decode($redis->get($key),true);
    print_r($data['access_token']);
?>