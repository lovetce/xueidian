<?php  
/**
 * 将字符串参数变为数组
 * @param $query
 * @return array array (size=10)
 'm' => string 'content' (length=7)
 'c' => string 'index' (length=5)
 'a' => string 'lists' (length=5)
 'catid' => string '6' (length=1)
 'area' => string '0' (length=1)
 'author' => string '0' (length=1)
 'h' => string '0' (length=1)
 'region' => string '0' (length=1)
 's' => string '1' (length=1)
 'page' => string '1' (length=1)
 */
function convertUrlQuery($query)
{
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}

/**
 * 将参数变为字符串
 * @param $array_query
 * @return string string 'm=content&c=index&a=lists&catid=6&area=0&author=0&h=0&region=0&s=1&page=1' (length=73)
 */
function getUrlQuery($array_query)
{
    if (empty($array_query))
    {
        return "";
    }
    $tmp = array();
    foreach($array_query as $k=>$param)
    {
        $tmp[] = $k.'='.$param;
    }
    $params = implode('&',$tmp);
    return $params;
}
?>