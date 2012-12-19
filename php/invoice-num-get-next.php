<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$num=(int)dbOne(
	'select num from invoices where user_id='.$user_id
	.' order by num desc limit 1', 'num'
);

echo json_encode(array('num'=>$num+1));
