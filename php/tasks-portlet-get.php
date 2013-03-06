<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$sql='select tasks.id id, name, description'
	.' from tasks'
	.' left join customers on customers.id=customer_id'
	.' where status<1'
	.' and tasks.user_id='.$user_id.' order by priority desc, cdate';
echo json_encode(dbAll($sql));
