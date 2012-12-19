<?php
require_once dirname(__FILE__).'/basics.php';
$email=$user_profile['email'];
$user=dbRow(
	'select * from users where email="'.addslashes($email).'" and active'
);
if (!$user) {
	$sql='insert into users set active=1, email="'.addslashes($email).'"'
		.', currency_symbol="€", cdate=now()';
	dbQuery($sql);
	$user=dbRow(
		'select * from users where email="'.addslashes($email).'"'
	);
}
$_SESSION['userdata']=array(
	'id'=>$user['id'],
	'email'=>$user['email'],
	'level'=>$user['level'],
	'currency'=>$user['currency_symbol']
);
header('Location: /');
exit;
