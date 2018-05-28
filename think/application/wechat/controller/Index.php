<?php
/**
 * Created by PhpStorm.
 * User: parasol
 * Date: 2018/5/28 0028
 * Time: 下午 5:16
 */

namespace app\wechat\controller;


use think\Controller;

class Index extends Controller
{
    private $appid='wx56424b21bc251b3a';
    private $appsecret='8b50d9c7582168a25ca619d305bbf3aa';

    public function index(){
        echo 123;
        die;
    }

    /**
     * 先不急
     */
    public function create_menu(){




    }

}