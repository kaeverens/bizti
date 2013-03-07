<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$type=$_REQUEST['type'];

dbQuery(
	'delete from shares where user_id='.$user_id
	.' and type="'.addslashes($type).'"'
);
$md5=md5(microtime(true).rand());
dbQuery(
	'insert into shares set user_id='.$user_id
	.', type="'.addslashes($type).'", md5="'.$md5.'"'
);
