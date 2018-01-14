<?php
$conn = pg_connect("dbname='XXXX' user='XXXX' password='XXXX' host='XXXX'");

$file = 'logs/access.log';
/*
$keys = array(
				"1a90a89f5751" => "master"
			);
if(!isset($_GET['key']) && !isset($keys[$_GET['key']])) {
	echo "Access Denied. Please contact administrators to get access to the platform.";
	die;
}
else {
	$access = $_GET['key'] .",". $keys[$_GET['key']] .",'". date("F j, Y, g:i:s a") ."',". basename($_SERVER["REQUEST_URI"], ".php") ."\r\n";
	file_put_contents($file, $access, FILE_APPEND | LOCK_EX);
}
*/

$access = getIp() .",". $_SERVER['REMOTE_ADDR'] .",'". date("F j, Y, g:i:s a") ."',". basename($_SERVER["REQUEST_URI"], ".php") ."\r\n";
file_put_contents($file, $access, FILE_APPEND | LOCK_EX);


function getIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    else
      $ip=$_SERVER['REMOTE_ADDR'];
  
    return $ip;
}

function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
  $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
  $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
  $result = str_replace($escapers, $replacements, $value);
  return $result;
}
?>