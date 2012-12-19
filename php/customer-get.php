<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$cid=(int)$_REQUEST['cid'];

$cust=dbRow(
	'select * from customers where id='.$cid.' and user_id='.$user_id
);
$cust['meta']=is_null($cust['meta'])?(object)array():json_decode($cust['meta']);

echo json_encode($cust);
