<?php
/**
 * Created by PhpStorm.
 * User: parasol
 * Date: 2018/6/7 0007
 * Time: 下午 2:06
 */

namespace app\regular\controller;


class Index
{
    /**
     * 正则表达式学习
     */
    public function index(){
        /*满足前面是字母  后面是数字 ^表示字符的起始位置  $表示字符的结束位置  重要*/
        /**
         * 1. ^从字符的位置开始配置
         * 2. $表示从字符的结束位置开始配置
         * 3.*表示前面的字符可以匹配多个
         * 4.{n}表示匹配前面的子表达式n次,默认应该就是1次(只是猜想)
         * 5.{1,3}表示最少匹配一次  最多匹配三次
         */
        $pattern = '/[a-z]{2}[0-9]/';
        $subject = 'a1fdddd3asfs4asf6asf199';
        $m1= $m2 = array();

//        preg_match($pattern, $subject, $m1);
        preg_match_all($pattern, $subject, $m2);

//
//        dump($m1);
//        echo '<hr>';
        dump($m2);

    }

}