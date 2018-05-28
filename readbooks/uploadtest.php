<?php
    $file = ROOT_PATH.'1.jpg';
    $access_token = "8_Po0EPqKBSTOp64wBkPcADFmLTN2AnFaj9W9IdkusbTxnG2cQtjXIzd_bgxiNoE67QcQfGcu2Wfa7J_6PlGfiOo7poZhYp5hLq3xFgy5gnzvYHJrNTBWgn2TL4BmSG_L_xw8SuoXaIhYPbE1TJBWiACAXPK";
    $yuyinurl = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=".$access_token."&type=image";
    $res = $Wecallback->curl_post_file($file,$yuyinurl);
    $res = json_decode($res,true);
    print_r($res);
    exit;
?>