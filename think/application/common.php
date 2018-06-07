<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * @param $url
 * @param string $type
 * @param string $data
 * @return mixed
 * 自定义封装的url请求的函数
 */
function url_request($url,$type='GET',$data=''){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
    curl_setopt($ch,CURLOPT_HEADER,0);
    $type=strtolower($type);
    switch ($type){
        case 'get';
            break;
        case 'post';
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            break;
    }
    $result = curl_exec($ch);

    curl_close($ch);
    return $result;
}

/**
 * @param int $code
 * @param int $count
 * @param array $data
 * @return \think\response\Json
 * 配合layui返回数据的函数
 */
function  res($code=0,$count=0,$data=array()){

    return json(
        array(
            'code'=>$code,
            'count'=>$count,
            'data'=>$data
        )
    );


}

/**
 * @param int $code
 * @param string $message
 * @param array $data
 * @return \think\response\Json
 * 返回信息
 */
function resMes($code=0,$message='',$data=array()){
    return json(
        array(
            'code'=>$code,
            'mes'=>$message,
            'data'=>$data
        )
    );

}
