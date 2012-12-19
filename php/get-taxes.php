<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$rs=dbAll(
	'select id,name,percentage from taxes where user_id='.$user_id
	.' order by name'
);

echo json_encode($rs?$rs:array());
