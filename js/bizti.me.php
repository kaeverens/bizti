<?php
$mainfile='bizti.me';
$files=array(
	'uploadify-3.2/jquery.uploadify.min',
	$mainfile
);

$expires = 60*60*24*14;
header("Pragma: public");
header("Cache-Control: maxage=".$expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
session_cache_limiter('none');
require_once 'lib.php';
