<?php
header("content-type:image/png");
require_once '../include/fun_img.php';
require_once '../include/common.inc.php';

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8");

if ($db)
{
    $str = "select username, face from {$tablepre}openid o where openid = '$openid'";
    $tmp = $db->fetch_first($str);
    if ($tmp)
    {
        $username = $tmp['username'];
        $headimg = $tmp['face'];
    }
}
function scerweima($url=''){
    require_once 'phpqrcode.php';

    $value = $url;                  //二维码内容

    $errorCorrectionLevel = 'L';    //容错级别
    $matrixPointSize = 5;           //生成图片大小

    //生成二维码图片
    if(!is_dir("images")) {
        mkdir("images", 0777, true);
    }
    $filename = uniqid().'.png';
    QRcode::png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 2);
    // unlink($filename); //删除源文件
    //  $QR = $filename;                //已经生成的原始二维码图片文件


    // $QR = imagecreatefromstring(file_get_contents($QR));

    //输出图片
    // imagepng($QR, 'qrcode.png');
    // imagedestroy($QR);
    return $filename;
}
$tzdomain = $site_conf['tzdomain'];
$ewmUrl = "http://".$tzdomain."/jfsc/tz.php?sopenid=".$openid."&group=a";
//调用查看结果
$ewm = scerweima($ewmUrl);



$rname = mt_rand(1111111,99999999);      
$outname =  mt_rand(1111111,99999999);   

//$img = file_get_contents($headimg);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $headimg);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$output = curl_exec($ch);
curl_close($ch);
file_put_contents("$rname.jpg",$output);

$arr_picinfo = array(
    //这是头像
    array(
        'path' => "$rname.jpg",
        'posx' => 158,
        'posy' => 655,
        'width' => 100,
        'height' => 100,
    ),

    //这是背景
    array(
        'path' => "bg7.png",
        'posx' => 0,
        'posy' => 0,
        'width' => 640,
        'height' => 863,
    ),
  
    //这是二维码
    array(
        'path' => $ewm,
        'posx' => 428,
        'posy' => 668,
        'width' => 185,
        'height' => 185,
    ),
);

$arrtextinfo = array(

    array(
        'text' => $username,
        'font' => '../sc/font/wryh.ttf',
        'rgb' => array(223,80,77),
        'size' => 17,
        'inclination' => 0,
        'posx' => 120,
        'posy' => 875,
    ),
);
    
$im = createpic("./bg.png", $arr_picinfo, null,null);
imagepng($im,"./images/$outname.png");
imagedestroy($im);
unlink("$rname.jpg");
unlink($ewm);
// exit;
?>