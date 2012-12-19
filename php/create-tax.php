<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$name=trim($_REQUEST['name']);
$percentage=(float)$_REQUEST['percentage'];
if (!$name) {
	die(json_encode(array('error'=>'no name supplied')));
}
$r=dbRow(
	'select id from taxes where name="'.addslashes($name).'"'
	.' and user_id='.$user_id
);
if ($r) {
	die(json_encode(array('error'=>'that tax already exists')));
}
dbQuery(
	'insert into taxes set name="'.addslashes($name).'", percentage='.$percentage
	.', user_id='.$user_id
);
$r=dbRow(
	'select id,name,percentage from taxes where name="'.addslashes($name).'"'
	.' and user_id='.$user_id
);

echo json_encode($r);
