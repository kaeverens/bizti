<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

/*
id: 97
user_id: 12
name: Smart Tanks
meta: {"address":"Billis\nGlaslough\nMonaghan \n\n","email":"info@smarttanksireland.com","phone":"72020, 0873639290","notes":"attn: Nicola Hackett\n","SI_id":"57"}
num_invoices: 1
paid: 492
total: 492
*/

$name=trim($_REQUEST['name']);
if (!$name) {
	die(json_encode(array('error'=>'no customer name supplied')));
}
$meta=array(
	'address'=>trim($_REQUEST['address']),
	'email'=>trim($_REQUEST['email']),
	'phone'=>trim($_REQUEST['phone'])
);

$r=dbRow(
	'select id from customers where name="'.addslashes($name).'"'
	.' and user_id='.$user_id
);
if ($r) {
	die(json_encode(array('error'=>'customer already exists')));
}
dbQuery(
	'insert into customers set name="'.addslashes($name).'", user_id='.$user_id
	.', meta="'.addslashes(json_encode($meta)).'"'
	.', num_invoices=0, paid=0, total=0'
);
$r=dbRow(
	'select id,name from customers where name="'.addslashes($name).'"'
	.' and user_id='.$user_id
);

echo json_encode($r);
