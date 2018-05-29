<?php
/**
 * Created by PhpStorm.
 * User: parasol
 * Date: 2018/5/29 0029
 * Time: 下午 2:02
 */

namespace app\layui\controller;
use app\layui\model\music;


/**
 * Class Index
 * @package app\layui\controller
 * 后台管理类
 * 完成简单的增删改查
 * 使用框架的model来完成
 */
class Index
{
    public function index(){


        return view('index');


//    dump($data);
//    die;




    }
    public function getindex(){
        return view('getindex');
    }

    /**
     * @return \think\response\Json
     * 获取数据并返回
     */
    public function getlist(){
        $page=input('page');
        $limit=input('limit');
        $music = new  music();
        $data= $music->getListPage('','',$page,$limit);
        $dataCount=$music->getListCount();
         return  res(0,$dataCount,$data);

    }


}