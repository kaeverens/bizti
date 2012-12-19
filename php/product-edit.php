<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$id=(int)$_REQUEST['id'];
$name=trim($_REQUEST['name']);
$price=(float)$_REQUEST['price'];
$tax=(int)$_REQUEST['tax'];

$sql='products set name="'.addslashes($name).'", price='.$price
	.', tax='.$tax;
if ($id) {
	$sql='update '.$sql
		.' where user_id='.$user_id.' and id='.$id;
}
else {
	$sql='insert into '.$sql.', user_id='.$user_id;
}
dbQuery($sql);

echo json_encode(array('ok'=>1));
