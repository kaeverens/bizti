<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$id=(int)$_REQUEST['id'];
$action=$_REQUEST['action'];
$task=dbRow(
	'select UNIX_TIMESTAMP(counter) from tasks where id='.$id
);
$time=$task['UNIX_TIMESTAMP(counter)'];
if($action=='start'){
	$sql='tasks set active=1';
	if($time==0) $sql.=',counter=now()';
	else{
		$time=time()-$time;
		$sql.=',counter=FROM_UNIXTIME("'.$time.'")';
	}
}
else{
	$time=time()-$time;
	$sql='tasks set counter=FROM_UNIXTIME("'.$time.'"), active=0';
}
$sql='update '.$sql.' where user_id='.$user_id.' and id='.$id;
dbQuery($sql);
echo json_encode(array('ok'=>1));
