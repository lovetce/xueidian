<?php
header("content-type:image/png");
require_once '../include/common.inc.php';
require_once '../source/include/fun_img.php';

 $db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnectz, true, "utf8"); 
$username = '君未';
$headimg = './test.jpg';
//$openid = 'o-O5mwhR5mcWfDGGZ6O5o2jKr99I';
$sex =1;
if($sex == 1){
    $picname = mt_rand(1,62);
  $pathpic = "http://www.xgwawa.com/gjc2016/man/l$picname.png";
}else{
    $picname = mt_rand(1,79);
    $pathpic = "http://www.xgwawa.com/gjc2016/woman/n$picname.png";
}
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
$rname = mt_rand(1111111,99999999);      
$outname =  mt_rand(1111111,99999999);   

//$img = file_get_contents($headimg);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $headimg);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
$output = curl_exec($ch);
curl_close($ch);
file_put_contents("./images/$rname.jpg",$output);

$arr_picinfo = array(
    //这是头像
    array(
        'path' => "./images/$rname.jpg",
        'posx' => 292,
        'posy' => 586,
        'width' => 165,
        'height' => 165,
    ),
    //这是前景图，任兰P完后放到39服务器上，随机选图
    array(
        'path' => $pathpic,
        'posx' => 0,
        'posy' => 0,
        'width' => 750,
        'height' => 1389,
    ),
    //这是二维码
    array(
        'path' => "./ewm.png",
        'posx' => 300,
        'posy' => 1150,
        'width' => 140,
        'height' => 140,
    ),
);

$arrtextinfo = array(
    //这是昵称
    array(
        'text' => $username,
        'font' => '../source/font/fzhtj.ttf',
        'rgb' => array(14,14,14),
        'size' => 50,
        'inclination' => 0,
        'posx' => 310,
        'posy' => 120,
    ),
    
    array(
        'text' => "长按识别我也要测",
        'font' => '../source/font/fzhtj.ttf',
        'rgb' => array(14,14,14),
        'size' => 20,
        'inclination' => 0,
        'posx' => 260,
        'posy' => 1320,
    )
);
    
$im = createpic("./bg.jpg", $arr_picinfo, $arrtextinfo,null);
imagepng($im);
imagedestroy($im);
unlink("./images/$rname.jpg");
// exit;
?>