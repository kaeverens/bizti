<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$pid=(int)$_REQUEST['pid'];

$cust=dbRow(
	'select id,name,price,tax,meta from products'
	.' where id='.$pid.' and user_id='.$user_id
);
$prod['meta']=is_null($prod['meta'])?(object)array():json_decode($prod['meta']);

echo json_encode($cust);
