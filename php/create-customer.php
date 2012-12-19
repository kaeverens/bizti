<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$name=trim($_REQUEST['name']);
if (!$name) {
	die(json_encode(array('error'=>'no customer name supplied')));
}
$r=dbRow(
	'select id from customers where name="'.addslashes($name).'"'
	.' and user_id='.$user_id
);
if ($r) {
	die(json_encode(array('error'=>'customer already exists')));
}
dbQuery(
	'insert into customers set name="'.addslashes($name).'", user_id='.$user_id
	.', num_invoices=0, paid=0, total=0'
);
$r=dbRow(
	'select id,name from customers where name="'.addslashes($name).'"'
	.' and user_id='.$user_id
);

echo json_encode($r);
