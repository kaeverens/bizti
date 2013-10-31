<?php
require_once 'basics.php';
header('Content-Type: text/json; charset=utf8');

$email=$_REQUEST['email'];
$password=$_REQUEST['password'];

$user=dbRow(
	'select id,email,level,currency_symbol,options'
	.' from users where email="'.addslashes($email).'"'
	.' and password="'.md5($password).'" and active'
);

if (!$user) {
	die(json_encode(array('error'=>'failed to login')));
}
$_SESSION['userdata']=array(
	'id'=>$user['id'],
	'email'=>$user['email'],
	'level'=>$user['level'],
	'currency'=>$user['currency_symbol'],
	'options'=>$user['options']
);
echo json_encode(array('ok'=>1));
