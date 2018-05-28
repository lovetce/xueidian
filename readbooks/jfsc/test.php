<?php

require_once '../include/common_new.inc.php';
require_once 'jfscUtil.php';	

    if (!isset($db)){
        $db = new mysqli($dbhost,$dbuser,$dbpw,$dbname);
        if(mysqli_connect_errno())
        {
            $returnArray['code']=1;
            $returnArray['err_msg']="数据库连接失败";
            echo json_encode($returnArray);
            exit();
        }
    }
    
    $site_appid = $site_conf['appid'];    
    $canShow = 1;	
    $sql = "select appid,ewm_url from iweite_huace_ewm where is_show = ? and appid = ? limit 1" ;
    $stmt = $db->prepare($sql);
	$stmt->bind_param('is', $canShow , $site_appid);	
    $stmt->bind_result($appid,$pic);
    $stmt->execute();
    $stmt->store_result();
    $num = $stmt->num_rows;
    if($num>0)
    {
    	while ($stmt->fetch())
    	{
    		$re = array('appid' => $appid, 'pic' => $pic);
    	}
    }
    else
    {
        $re = array('appid' => '0', 'pic' => '');
    }
    $stmt->free_result();
    $stmt->close();
	
	print_r($re);

?>