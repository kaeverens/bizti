<?php

require_once 'basics.php';
header('Content-type: text/json; charset=utf8');

$email=$_REQUEST['email'];
$password=$_REQUEST['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	die(json_encode(array('error'=>'invalid email')));
}
if (strlen($password)<8) {
	die(json_encode(array('error'=>'password too short')));
}
if (dbRow('select id from users where email="'.addslashes($email).'"')) {
	die(json_encode(array('error'=>'email already registered')));
}

$validation=md5(microtime(true));
$password=md5($password);
$meta=json_encode(array('validation'=>$validation));

$sql='insert into users set email="'.addslashes($email).'"'
	.', password="'.$password.'"'
	.', active=0'
	.', cdate=now()'
	.', meta="'.addslashes($meta).'"';
dbQuery($sql);
$id=dbLastInsertId();

mail(
	$email,
	'[bizti] User Verification',
	"Hi!\n\nYou, or someone claiming to be you, have registered"
	." at bizti.me.\n\nIn order to complete the registration, please click the"
	." following link.\n\nhttp://bizti.me/validate/$id/$validation\n\n"
	."Thanks,\nKae Verens",
	"From: kae@bizti.me\nReply-to: kae@bizti.me"
);

die(json_encode(array('ok'=>1)));
