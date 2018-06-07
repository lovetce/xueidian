<?php
/**
 * Created by PhpStorm.
 * User: parasol
 * Date: 2018/5/29 0029
 * Time: 下午 2:02
 */

namespace app\layui\controller;
use app\layui\model\music;
use think\Db;


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

    }
    public function getindex(){
        return view('getindex');
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 返回数据
     */
    public function getlist(){
        $page=input('page');
        $limit=input('limit');
        $music = new  music();
        $data= $music->getListPage('','',$page,$limit);
        $dataCount=$music->getListCount();
         return  res(0,$dataCount,$data);

    }

    /**
     * @return \think\response\View
     * 修改页面参数
     */
    public function edit_info(){
        if (request()->isGet()){
            /*如果id为空就不返回*/
            $id=request()->get('id');
            if (empty($id)){
                /*返回错误信息*/
                resMes('-1','数据错误哦');
            }
            $music = new  music();
            $musicOneData=$music->getOne(array('id'=>$id));
         return view('edit_info',array('musicOneData'=>$musicOneData));
        }


    }

    /**
     * 感觉效果还可以
     */
    public function test(){


    }



}