<?php
/**
 * Created by PhpStorm.
 * User: parasol
 * Date: 2018/5/29 0029
 * Time: 下午 4:47
 */

namespace app\redis\controller;


use think\Cache;

class Index
{
    public function index(){

//        phpinfo();
//        die;
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        // $redis->auth('password'); # 如果没有密码则不需要这行

//        echo $redis->get('123213');
        //列表
        //存储数据到列表中
//        $redis->lpush('list', 'html');
//        $redis->lpush('list', 'css');
//        $redis->lpush('list', 'php');

        //获取列表中所有的值
        $list = $redis->lrange('list', 0, -1);
//        print_r($list);echo '<br>';

        //从右侧加入一个
//        $redis->rpush('list', 'mysql');
        $list = $redis->lrange('list', 0, -1);
//        dump($list);
//        die;
//
//        //从左侧弹出一个
//        $redis->lpop('list');
//        $list = $redis->lrange('list', 0, -1);
//        print_r($list);echo '<br>';
//
//        //从右侧弹出一个
//        $redis->rpop('list');
//        $list = $redis->lrange('list', 0, -1);
//        print_r($list);echo '<br>';

        // 结果
        // Array ( [0] => php [1] => css [2] => html )
        // Array ( [0] => php [1] => css [2] => html [3] => mysql )
        // Array ( [0] => css [1] => html [2] => mysql )
        // Array ( [0] => css [1] => html )

    }


}