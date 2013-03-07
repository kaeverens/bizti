<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$shares=dbAll(
	'select * from shares where user_id='.$user_id
);

echo json_encode($shares);
