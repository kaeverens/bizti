<?php
session_id($_REQUEST['session_id']);
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$dir='../userdata/'.$_SESSION['userdata']['id'];
@mkdir($dir, 0777, true);
$dir=realpath($dir);
$str='convert '.$_FILES['Filedata']['tmp_name']
	.' -resize 256x256 '.$dir.'/logo.png';
`$str`;

$meta=dbOne(
	'select meta from users where id='.$_SESSION['userdata']['id'],
	'meta'
);
$meta=json_decode($meta, true);
$meta['logo-num']=isset($meta['logo-num'])?(int)$meta['logo-num']+1:1;

dbQuery(
	'update users set meta="'.addslashes(json_encode($meta)).'"'
	.' where id='.$_SESSION['userdata']['id']
);
