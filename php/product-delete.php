<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$pid=(int)$_REQUEST['pid'];
$items=dbOne(
	'select count(id) as ids from invoices where user_id='.$user_id
	.' and products like "%\\"product\\":\\"'.$pid.'\\"%"', 'ids'
);
if ($items) {
	exit(
		json_encode(
			array(
				'error'=>'This product cannot be deleted, as it is referred to in at'
				.' least one invoice'
			)
		)
	);
}

dbQuery('delete from products where id='.$pid);
echo json_encode(array('ok'=>1));
