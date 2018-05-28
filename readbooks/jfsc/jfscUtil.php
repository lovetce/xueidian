<?php

function httpRequest_php($url)
{
	$url = "http://".$url;
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($curl);
	curl_close($curl);
	$data = json_decode($data, true);
	return $data;
}


