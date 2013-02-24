<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$id=(int)$_REQUEST['id'];

$task=dbRow(
	'select * from tasks where id='.$id.' and user_id='.$user_id
);

echo json_encode($task);
