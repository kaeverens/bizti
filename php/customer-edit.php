<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$id=(int)$_REQUEST['id'];
$address=trim($_REQUEST['address']);
$notes=trim($_REQUEST['notes']);
// { email
$email=trim($_REQUEST['email']);
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
	exit(json_encode(array('error'=>'Invalid email address')));
}
// }
$phone=trim($_REQUEST['phone']);
$name=$_REQUEST['name'];

$sql='customers set name="'.addslashes($name).'"';
if ($id) {
	$cust=dbRow('select * from customers where id='.$id.' and user_id='.$user_id);
	if (!$cust) {
		exit;
	}
	$meta=is_null($cust['meta'])?(object)array():json_decode($cust['meta']);
	$meta->address=$address;
	$meta->email=$email;
	$meta->phone=$phone;
	$meta->notes=$notes;
	$sql='update '.$sql.', meta="'.addslashes(json_encode($meta)).'"'
		.' where user_id='.$user_id.' and id='.$id;
}
else {
	$meta=(object)array();
	$meta->address=$address;
	$meta->email=$email;
	$meta->notes=$notes;
	$meta->phone=$phone;
	$sql='insert into '.$sql.', meta="'.addslashes(json_encode($meta)).'"'
		.', user_id='.$user_id;
}
dbQuery($sql);

$rs=dbAll(
	'select id,name from customers where user_id='.$user_id.' order by name'
);
echo json_encode(
	array(
		'ok'=>1,
		'customerNames'=>$rs
	)
);
