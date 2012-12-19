<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$name=trim($_REQUEST['name']);
$price=(float)$_REQUEST['price'];
$tax=(int)$_REQUEST['tax'];

if (!$name) {
	die(json_encode(array('error'=>'no product name supplied')));
}
$r=dbRow(
	'select id from products where name="'.addslashes($name).'"'
	.' and user_id='.$user_id
);
if ($r) {
	die(json_encode(array('error'=>'product already exists')));
}
dbQuery(
	'insert into products set name="'.addslashes($name).'", price='.$price
	.', tax='.$tax.', user_id='.$user_id
);
$r=dbRow(
	'select id, name, price, tax from products where name="'.addslashes($name).'"'
	.' and user_id='.$user_id
);

echo json_encode($r);
