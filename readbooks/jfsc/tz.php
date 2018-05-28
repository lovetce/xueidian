<?php
require_once '../include/common_new.inc.php';
$maindomain = $site_conf['maindomain'];
$tzdomain = $site_conf['tzdomain'];
$login = isset($_GET['login'])? $_GET['login'] : '';
$sopenid = isset($_GET['sopenid'])? $_GET['sopenid'] : '';
$group = isset($_GET['group'])? $_GET['group'] : '';
$tzurl = 'http://'. $maindomain . '/jfsc/html/guanzhu.php?login=' . $login."&sopenid=".$sopenid."&group=".$group;
//exit($tzurl);
header("Location: $tzurl");
exit;
?>