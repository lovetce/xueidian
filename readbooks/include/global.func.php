<?php
//created by chenhl,2016-01-20,解码后的代码

if(!defined('IN_IWEITE')) {
	exit('Access Denied');
}

function customError($errno, $errstr, $errfile, $errline)
{
    echo "<b>Error number:</b> [{$errno}],error on line {$errline} in {$errfile}<br />";
    die;
}
set_error_handler('customError', E_ERROR);
$getfilter = '\'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)';
$postfilter = '\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)';
$cookiefilter = '\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)';
function StopAttack($StrFiltKey, $StrFiltValue, $ArrFiltReq)
{
    if (is_array($StrFiltValue)) {
        $StrFiltValue = implode($StrFiltValue);
    }
    if (preg_match('/' . $ArrFiltReq . '/is', $StrFiltValue) == 1) {
        print 'Whats Your Name?';
        die;
    }
}


function create_editor($input_name, $input_value = '')
{
$editor ="<link rel=\"stylesheet\" href=\"../../include/kindeditor/themes/default/default.css\" />";
$editor .="<link rel=\"stylesheet\" href=\"../../include/kindeditor/plugins/code/prettify.css\" />";
$editor .="<script charset=\"utf-8\" src=\"../../include/kindeditor/kindeditor.js\"></script>";
$editor .="<script charset=\"utf-8\" src=\"../../include/kindeditor/lang/zh_CN.js\"></script>";
$editor.="<script charset=\"utf-8\" src=\"../../include/kindeditor/plugins/code/prettify.js\"></script>";
		
$editor.= "<script>
		KindEditor.ready(function(K) {
			var editor1 = K.create('textarea[id=".$input_name."]', {
				cssPath : '../../include/kindeditor/plugins/code/prettify.css',
				uploadJson : '../../include/kindeditor/php/upload_ajson.php',
				fileManagerJson : '../../include/kindeditor/php/file_amanager_json.php',
				allowFileManager : true,
				afterCreate : function() {
					var self = this;
					K.ctrl(document, 13, function() {
						self.sync();
						K('form[name=myform]')[0].submit();
					});
					K.ctrl(self.edit.doc, 13, function() {
						self.sync();
						K('form[name=myform]')[0].submit();
					});
				}
			});
			prettyPrint();
		});
	</script>";
	
$editor.="<textarea name=".$input_name." id=".$input_name." style=\"width:98%;height:400px;visibility:hidden;\">".htmlspecialchars($input_value)."</textarea>";
 echo $editor;
}

function create_html_editor($input_name, $input_value = '')
{

	$editor ="<link rel=\"stylesheet\" href=\"../../include/kindeditor/themes/default/default.css\" />";
$editor .="<link rel=\"stylesheet\" href=\"../../include/kindeditor/plugins/code/prettify.css\" />";
$editor .="<script charset=\"utf-8\" src=\"../../include/kindeditor/kindeditor.js\"></script>";
$editor .="<script charset=\"utf-8\" src=\"../../include/kindeditor/lang/zh_CN.js\"></script>";
$editor.="<script charset=\"utf-8\" src=\"../../include/kindeditor/plugins/code/prettify.js\"></script>";
	$editor.="<script>
			var editor;
			KindEditor.ready(function(K) {
				editor = K.create('textarea[id=".$input_name."]', {
					resizeType : 1,
					allowPreviewEmoticons : false,
					allowImageUpload : false,
					items : [
						'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
						'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
						'insertunorderedlist', '|', 'emoticons', 'link']
				});
			});
		</script>";

	$editor.= "<textarea name=".$input_name." id=".$input_name." style=\"width:100%;height:200px;visibility:hidden;\">".htmlspecialchars($input_value)."</textarea>";
 echo $editor;
}

function clearcookies() {
	global $iweite_uid, $discuz_user, $discuz_pw, $discuz_secques, $adminid, $credits;
	foreach(array('sid', 'auth', 'visitedfid', 'onlinedetail', 'loginuser', 'activationauth') as $k) {
		dsetcookie($k);
	}
	$discuz_uid = $adminid = $credits = 0;
	$discuz_user = $discuz_pw = $discuz_secques = '';
}

function getRand($proArr,$total) { 
    $result = ''; 
	$randNum = mt_rand(1, $total); 
	foreach ($proArr as $k => $v) {
		 if ($v['v']>0){
		    if ($randNum>$v['start']&&$randNum<=$v['end']){
		    			$result=$k;
		    			break;
		    	}
		    	}
		  }
	 return $result; 
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	global $iweite_auth_key;
    $ckey_length = 4;
    $key = md5($key ? $key : $iweite_auth_key); 
    $keya = md5(substr($key, 0, 16));
  
    $keyb = md5(substr($key, 16, 16));
  
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
 
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
   
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
   
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
  
    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
   
    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
     
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {
     
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
       
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}


function cutstr($string, $length, $dot = ' ...') {
	global $charset;

	if(strlen($string) <= $length) {
		return $string;
	}

	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);

	$strcut = '';
	if(strtolower($charset) == 'utf-8') {

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);

	} else {
		for($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}

	$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	return $strcut.$dot;
}

function geturl($domain) {
    $re_domain = '';
    $domain_postfix_cn_array = array("com", "net", "org", "gov", "edu", "com.cn", "cn");
    $array_domain = explode(".", $domain);
    $array_num = count($array_domain) - 1;
    if ($array_domain[$array_num] == 'cn') {
        if (in_array($array_domain[$array_num - 1], $domain_postfix_cn_array)) {
            $re_domain = $array_domain[$array_num - 2] . "." . $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
        } else {
            $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
        }
    } else {
        $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
    }
    return $re_domain;
}

function daddslashes($string, $force = 0) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force);
			}
		} else {
			$string = addslashes($string);
		}
	}
	return $string;
}


function fileext($filename) {
	return trim(substr(strrchr($filename, '.'), 1, 10));
}

function getrobot() {
	if(!defined('IS_ROBOT')) {
		$kw_spiders = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla';
		$kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';
		if(!strexists($_SERVER['HTTP_USER_AGENT'], 'http://') && preg_match("/($kw_browsers)/i", $_SERVER['HTTP_USER_AGENT'])) {
			define('IS_ROBOT', FALSE);
		} elseif(preg_match("/($kw_spiders)/i", $_SERVER['HTTP_USER_AGENT'])) {
			define('IS_ROBOT', TRUE);
		} else {
			define('IS_ROBOT', FALSE);
		}
	}
	return IS_ROBOT;
}


function isemail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}




function multi($num,$perpage,$curpage,$mpurl,$maxpages = 0, $page = 8,$simple = 0 ) {
$ajaxtarget = !empty($_GET['ajaxtarget']) ? " ajaxtarget=\"".dhtmlspecialchars($_GET['ajaxtarget'])."\" " : '';
	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
	$realpages = 1;
	if($num > $perpage) {
		$offset = 2;

		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}

		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a   href="'.$mpurl.'page=1" class="downPage" '.$ajaxtarget.'>1 ...</a>' : '').
			($curpage > 1 && !$simple ? '<a href="'.$mpurl."page=".($curpage - 1).'" '.$ajaxtarget.' class="downPage">上一页</a>' : '');
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? '<a herf="#" class=current>'.$i.'</a>' :
				'<a href="'.$mpurl."page=".$i.'"'.$ajaxtarget.'>'.$i.'</a>';
		}

		$multipage .= ($to < $pages ? '<a href="'.$mpurl."page=".$pages.'" class="downPage"'.$ajaxtarget.'>... '.$realpages.'</a>' : '').
			($curpage < $pages && !$simple ? '<a href="'.$mpurl."page=".($curpage + 1).'" class="downPage"'.$ajaxtarget.'>下一页</a>' : '');

		
	}
	$maxpage = $realpages;
	return $multipage;
} 






function ajaxmulti($num,$perpage,$curpage,$mpurl,$maxpages = 0, $page = 8,$simple = 0 ) {
$ajaxtarget = !empty($_GET['ajaxtarget']) ? " ajaxtarget=\"".dhtmlspecialchars($_GET['ajaxtarget'])."\" " : '';
	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';
	$realpages = 1;
	if($num > $perpage) {
		$offset = 2;

		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}

		$multipage = ($curpage - $offset > 1 && $pages > $page ? "<li class='disabled'><a  class='pre' onclick='showPage(1)' >1 ...</a></li>" : '').
			($curpage > 1 && !$simple ? "<li class='disabled'><a  class='pre' onclick='showPage(".($curpage - 1).")'>上一页</a></li>" :'');
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? "<li class='active'><a herf='#'>".$i."</a></li>" :
				"<li  class='disabled'><a onclick='showPage(".($i).")'>".$i."</a></li>";
		}

		$multipage .= ($to < $pages ? "<li class='disabled'><a onclick='showPage(".$pages.")'>... ".$realpages."</a></li>" : '').
			($curpage < $pages && !$simple ? "<li class='disabled'><a onclick='showPage(".($curpage + 1).")'  class='next'>下一页</a></li>": '');

	}
	$maxpage = $realpages;
	return $multipage;
} 


function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' ? mt_srand((double)microtime() * 1000000) : mt_srand();
	$seed = base_convert(md5(print_r($_SERVER, 1).microtime()), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed[mt_rand(0, $max)];
	}
	return $hash;
}

function site() {
	return $_SERVER['HTTP_HOST'];
}

function formhash($specialadd = '') {
	global $iweite_user, $iweite_uid, $iweite_pw, $timestamp, $iweite_auth_key;
	$hashadd ="iweite.com";
	return substr(md5(substr($timestamp, 0, -7).$iweite_user.$iweite_uid.$iweite_pw.$iweite_auth_key.$hashadd.$specialadd), 8, 8);
}

function strexists($haystack, $needle) {
	return !(strpos($haystack, $needle) === FALSE);
}

function dhtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

function dsetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly =true) {
	global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
	$var = ($prefix ? $cookiepre : '').$var;
	if($value == '' || $life < 0) {
		$value = '';
		$life = -1;
	}
	$life = $life > 0 ? $timestamp + $life : ($life < 0 ? $timestamp - 31536000 : 0);
	$path = $httponly && PHP_VERSION < '5.2.0' ? "$cookiepath; HttpOnly" : $cookiepath;
	$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	
	if(PHP_VERSION < '5.2.0') {
		setcookie($var, $value, $life, $path, $cookiedomain, $secure);
	} else {
		setcookie($var, $value, $life, $path, $cookiedomain, $secure, $httponly);
	}
}


function msbox($strMS){
	echo "<script language=javascript>alert('".$strMS."');</script>";
	exit;
}

function msboxback($strMS){
	echo "<script language=javascript>alert('".$strMS."');history.back(-1);</script>";
	exit;
}

function msboxurl($strMS,$strUrl){
	echo "<script language=javascript>alert('".$strMS."');location.href='".$strUrl."';</script>";
	exit;
}
function msurl($strUrl){
	echo "<script language=javascript>location.href='".$strUrl."';</script>";
	exit;
}

function msboxconfirm($strMS,$strUrl,$strUrl2){
	echo "<SCRIPT Language = \"JavaScript\">";  
	echo "if(confirm('".$strMS."'))"; 
	echo "{"; 
	echo "location.href='".$strUrl."'";  
	echo "}"; 
	echo "else";  
	echo "{";  
	echo "location.href='".$strUrl2."'";  
	echo "}";  
	echo "</SCRIPT>"; 
	exit;
}


function filterhtml($str)
{
 $str=stripslashes($str);
 $str=strip_tags($str);
 $str=preg_replace("/\s+/", ' ', $str); //过滤多余回车 
 $str=preg_replace("/<[ ]+/si",'<',$str); //过滤<__("<"号后面带空格
 $str=preg_replace("/<\!--.*?-->/si",'',$str); //注释 
 $str=preg_replace("/<(\!.*?)>/si",'',$str); //过滤DOCTYPE 
 $str=preg_replace("/<(\/?html.*?)>/si",'',$str); //过滤html标签 
 $str=preg_replace("/<(\/?head.*?)>/si",'',$str); //过滤head标签 
 $str=preg_replace("/<(\/?meta.*?)>/si",'',$str); //过滤meta标签 
 $str=preg_replace("/<(\/?body.*?)>/si",'',$str); //过滤body标签 
 $str=preg_replace("/<(\/?link.*?)>/si",'',$str); //过滤link标签 
 $str=preg_replace("/<(\/?form.*?)>/si",'',$str); //过滤form标签 
 $str=preg_replace("/cookie/si","COOKIE",$str); //过滤COOKIE标签
 $str=preg_replace("/<(applet.*?)>(.*?)<(\/applet.*?)>/si",'',$str); //过滤applet标签 
 $str=preg_replace("/<(\/?applet.*?)>/si",'',$str); //过滤applet标签
 $str=preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si",'',$str); //过滤style标签 
 $str=preg_replace("/<(\/?style.*?)>/si",'',$str); //过滤style标签 
 $str=preg_replace("/<(title.*?)>(.*?)<(\/title.*?)>/si",'',$str); //过滤title标签 
 $str=preg_replace("/<(\/?title.*?)>/si",'',$str); //过滤title标签 
 $str=preg_replace("/<(object.*?)>(.*?)<(\/object.*?)>/si",'',$str); //过滤object标签 
 $str=preg_replace("/<(\/?objec.*?)>/si",'',$str); //过滤object标签 
 $str=preg_replace("/<(noframes.*?)>(.*?)<(\/noframes.*?)>/si",'',$str); //过滤noframes标签 
 $str=preg_replace("/<(\/?noframes.*?)>/si",'',$str); //过滤noframes标签
 $str=preg_replace("/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/si",'',$str); //过滤frame标签 
 $str=preg_replace("/<(\/?i?frame.*?)>/si",'',$str); //过滤frame标签
 $str=preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si",'',$str); //过滤script标签 
 $str=preg_replace("/<(\/?script.*?)>/si",'',$str); //过滤script标签 
 $str=preg_replace("/javascript/si","JAVASCRIPT",$str); //过滤script标签 
 $str=preg_replace("/vbscript/si","VBSCRIPT",$str); //过滤script标签 
 $str=preg_replace("/on([a-z]+)\s*=/si","ON\\1=",$str); //过滤script标签 
 $str=preg_replace("/&#/si","&＃",$str); //过滤script标签，如javAsCript:alert('aabb)
  
 $str=daddslashes($str);
 return($str);
} 

function htmlmd5($filename){
	return md5_file($filename);
}

function htmlfilter($input)
{
	$input = str_replace(' ', '', $input);
	$input = str_replace('\t', '', $input);
	$input = str_replace(chr(13).chr(10), '<br>', $input);
	$input = str_replace('&nbsp;', '', $input);
	return $input;
}


function hex2rgb($colour) {   
    if ($colour [0] == '#') {   
        $colour = substr ( $colour, 1 );   
    }   
    if (strlen ( $colour ) == 6) {   
        list ( $r, $g, $b ) = array ($colour [0] . $colour [1], $colour [2] . $colour [3], $colour [4] . $colour [5] );   
    } elseif (strlen ( $colour ) == 3) {   
        list ( $r, $g, $b ) = array ($colour [0] . $colour [0], $colour [1] . $colour [1], $colour [2] . $colour [2] );   
    } else {   
        return false;   
    }   
    $r = hexdec ( $r );   
    $g = hexdec ( $g );   
    $b = hexdec ( $b );   
    return array ('red' => $r, 'green' => $g, 'blue' => $b );   
}   


function delDirAndFile($dirName)
{
	if ( $handle = opendir( "$dirName" ) ) {
	   while ( false !== ( $item = readdir( $handle ) ) ) {
	   if ( $item != "." && $item != ".." ) {
	   if ( is_dir( "$dirName/$item" ) ) {
	   delDirAndFile( "$dirName/$item" );
	   } else {
	   if( unlink( "$dirName/$item" ) ) echo "";
	   }
	   }
	   }
	   closedir( $handle );
	   if( rmdir( $dirName ) ) echo "";
	}
}

function deldir($dirName)
{
	if ( $handle = opendir( "$dirName" ) ) {
	   while ( false !== ( $item = readdir( $handle ) ) ) {
	   if ( $item != "." && $item != ".." ) {
	   if ( is_dir( "$dirName/$item" ) ) {
	   delDirAndFile( "$dirName/$item" );
	   } else {
	   if( unlink( "$dirName/$item" ) ) echo "";
	   }
	   }
	   }
	   closedir( $handle );
	}
}

	
function getpic($fpic){
 	$domain = strpos($fpic,"attachments/");
	if($domain){
		$fpic_arr=explode("attachments",$fpic);
		$getpic="attachments".$fpic_arr[1];
		
	}else{
		$getpic=daddslashes($fpic);
	}
 
	   return $getpic;
  }

function sizecount($filesize) {
     if ($filesize >= 1073741824) {
         $filesize = round($filesize / 1073741824 * 100) / 100 .' GB';
     } elseif ($filesize >= 1048576) {
         $filesize = round($filesize / 1048576 * 100) / 100 .' MB';
     } elseif($filesize >= 1024) {
         $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
     } else {
         $filesize = $filesize.' Bytes';
     }
     return $filesize;
 }

function xml_to_json($source) { 
		if(is_file($source)){ 
		$xml_array=simplexml_load_file($source); 
		}else{ 
		$xml_array=simplexml_load_string($source); 
		} 
		$json = json_encode($xml_array);
	return $json; 
} 

function my_get_browser(){
	if(empty($_SERVER['HTTP_USER_AGENT'])){
		return '本系统仅支持谷哥浏览器';
	}
	if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 9.0')){
		return 'Internet Explorer 9.0';
	}
	if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 8.0')){
		return 'Internet Explorer 8.0';
	}
	if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 7.0')){
		return 'Internet Explorer 7.0';
	}
	if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 6.0')){
		return 'Internet Explorer 6.0';
	}
	if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'Firefox')){
		return 'Firefox';
	}
	if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'Chrome')){
		return 'Chrome';
	}
	if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'Safari')){
		return 'Safari';
	}
	if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'Opera')){
		return 'Opera';
	}
	if(false!==strpos($_SERVER['HTTP_USER_AGENT'],'360SE')){
		return '360SE';
	}
}

function is_mobile() { 
    $user_agent = $_SERVER['HTTP_USER_AGENT']; 
    $mobile_agents = array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi", 
    "android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio", 
    "au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu", 
    "cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ", 
    "fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi", 
    "htc","huawei","hutchison","inno","ipad","ipaq","iphone","ipod","jbrowser","kddi", 
    "kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo", 
    "mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-", 
    "moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia", 
    "nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-", 
    "playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo", 
    "samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank", 
    "sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit", 
    "tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin", 
    "vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce", 
    "wireless","xda","xde","zte"); 
    $is_mobile = false; 
    foreach ($mobile_agents as $device) { 
        if (stristr($user_agent, $device)) { 
            $is_mobile = true; 
            break; 
        } 
    } 
    return $is_mobile; 
} 

function getfile($url)
	{
	if(function_exists('file_get_contents')) {
	$file_contents = @file_get_contents($url);
	}else {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt ($ch,CURLOPT_URL,$url);
	curl_setopt ($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt ($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	$file_contents = curl_exec($ch);
	curl_close($ch);
	}
	return $file_contents;
}


function get_url_contents($url)
{
    if (ini_get("allow_url_fopen") == "1")
        return file_get_contents($url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result =  curl_exec($ch);
    curl_close($ch);

    return $result;
}

function https_request($url, $data = null)
{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
}
function getwxpic($url,$w=80){
	global $iweite_url;
	if(!$url){
		return 'public/images/none.png';
	}
	
	if(false!==strpos($url,'mmbiz.qpic.cn')){
		return 'http://img02.store.sogou.com/net/a/01/link?appid=100520033&w='.$w.'&url='.$url;
	}else{
	  return $iweite_url.$url;
	}
}
function iweitelog($msg){
	$file = fopen("1.txt","w");
	fwrite($file,$msg);
	fclose($file);
}
function getRandom($type, $param){
    $str1="0123456789";
    $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    if ($type == 1)
    {
        $str = $str1;
    }
    $len = strlen($str);
    $key = "";
    for($i=0;$i<$param;$i++)
    {
        $key .= $str{mt_rand(0,$len - 1)};    //生成php随机数
    }
    return $key;
}
/**
 * param    $db 
 * param    groups  第几线
 * param    type    域名类型 0-落地域1-分享域2-中转域
 * param    ldomain 落地域名（获取分享域名的时候传此参数）
 * 
 * return   code             0 正常  1错误
 * return   domain           域名
 * return   appid,appsecret  type = 1的时候返回
 */

function getDomainForMve($db,$groups,$type,$ldomain){
    if($groups==''){
        return array('code'=>1);
    }
    $sql='';
    if($type==0||$type==2){
        $sql = "select domain from iweite_huace_domain where status =1 and type=$type and groups = $groups ORDER BY  RAND() limit 1";
         
    }else if($type==1 && !empty($ldomain)){
        $sql = "select domain,appId,appSecret from iweite_huace_domain where FIND_IN_SET(id,(SELECT shareId FROM iweite_huace_domain WHERE domain = '$ldomain' )) AND TYPE =1 AND STATUS=1 ORDER BY RAND()  LIMIT 1";
    }else{
        return   array('code'=>1);
         
    }
    $domain = $db->fetch_first($sql);

    if(empty($domain)){
        return array('code'=>1);
    }
    $re = array('code'=>0,'domain'=>$domain['domain']);
    if($type==1){
        $re['appid']=$domain['appId'];
        $re['appsecret']=$domain['appSecret'];
    }

    return $re;


}

?>