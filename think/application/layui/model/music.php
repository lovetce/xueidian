<?php
/**
 * Created by PhpStorm.
 * User: parasol
 * Date: 2018/5/29 0029
 * Time: 下午 3:03
 */

namespace app\layui\model;


use think\Model;

class music extends base


{
    /**
     * @param string $where
     * @param string $feild
     * @param int $page
     * @param int $limit
     * @param array $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 根据分页来做
     */
    public function getListPage($where = "", $feild = "",$page=1, $limit = 30, $order = ['id' => 'desc'])
    {

       return $this->where($where)->field($feild)->limit(($page - 1 ) * $limit, $limit)->order($order)->select();

    }
    public function getListCount($where = "",$feild = ""){
        return $this->where($where)->field($feild)->count();
    }



}