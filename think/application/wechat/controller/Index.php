<?php
/**
 * Created by PhpStorm.
 * User: parasol
 * Date: 2018/5/28 0028
 * Time: 下午 5:16
 */

namespace app\wechat\controller;


use org\wechat\Wechat;
use think\Controller;

class Index extends Controller
{
    private $appid='wx56424b21bc251b3a';
    private $appsecret='8b50d9c7582168a25ca619d305bbf3aa';
    private $encodingaeskey='';
    private $token='token';
    private $weObj;
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        // $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        //$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        //file_put_contents('test11.txt',$postObj);

        $options = array('token' => $this->token,  'appid' => $this->appid, 'appsecret' => $this->appsecret);
        $this->weObj = new Wechat($options);
        //$this->weObj->valid();

    }

    public function index(){
        $this->type = $this->weObj->getRev()->getRevType();

        $this->content = $this->weObj->getRevContent(); //获取消息的正文内容

        $this->openid = $this->weObj->getRevFrom();
        $reveventData = $this->weObj->getRevEvent();
        /*获取的ID*/
        $newOpenid = $this->weObj->getRevData()['FromUserName'];
        //$openid='oz3r1vhKAEc4YoJVVbZWWNvH_k6E';
        /*发送消息*/
        $this->send($newOpenid,'哈哈哈哈哈');

    }

    /**
     * 创建菜单
     */
    public function createMenu(){






    }

    /**
     * 发送模板消息
     */
    public function sendTemplate(){
        $temid='8emOZ6DCpMGK2Ij902XinwUbxnZi192TYAROrHUcULI';
        $access_token = $this->weObj->checkAuth();





    }
    /**
     * @param $oldOpenid
     * @param $text
     * 发送消息
     */
    public function send($oldOpenid, $text)
    {
        $data = array(
            'touser' => $oldOpenid,
            'msgtype' => "text",
            "text" => array(
                "content" => $text
            )
        );
        $access_token = $this->weObj->checkAuth();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
        url_request($url, 'post', json_encode($data, JSON_UNESCAPED_UNICODE));

    }

    /**
     * @param $openid
     * @param $media_id
     * 发送图片
     */
    public function sendPhoto($openid,$media_id){


        $data=array(
            'touser'=>$openid,
            'msgtype'=>'image',
            'image'=>array(
                'media_id'=>$media_id
            )
        );
        $access_token = $this->weObj->checkAuth();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=$access_token";
        url_request($url, 'post', json_encode($data, JSON_UNESCAPED_UNICODE));

    }
    //获取线上图片
    function getjpg($imgurl){

        $header = array(
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
            'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate',);
        $url=$imgurl;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($curl);

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($code == 200) {//把URL格式的图片转成base64_encode格式的！
            $imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
            $imagefile=$this->base64_upload($imgBase64Code);
            return $imagefile;
//        var_dump($imagefile);
//        die;
        }
    }
    function base64_upload($base64) {
        $base64_image = str_replace(' ', '+', $base64);

        //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
            //匹配成功
            if($result[2] == 'jpeg'){
                $image_name = time().uniqid().'.png';
//            $image_name = 'xuanyao.png';
                //纯粹是看jpeg不爽才替换的
            }else{
                $image_name = time().uniqid().$result[2];
            }

            $imagefile = "upload/";
//        $imagefile = "upload/";

            if(!file_exists($imagefile))
            {
                mkdir($imagefile,0777,true);
            }


            $imagefile=$imagefile.$image_name;




            //服务器文件存储路径
            if (file_put_contents($imagefile, base64_decode(str_replace($result[1], '', $base64_image)))){

                return $imagefile;


            }else{
                return false;
            }
        }else{
            return false;
        }
    }



}