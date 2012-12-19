<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$cid=(int)$_REQUEST['cid'];

$inv=dbRow(
	'select * from customers'
	.' where id='.$cid.' and user_id='.$user_id
);
if (!$inv) {
	die(json_encode(array('error'=>'no such customer')));
}
if ($inv['invoices']) {
	die(
		json_encode(
			array('error'=>'cannot delete this customer as it has invoice data')
		)
	);
}
dbQuery('delete from customers where id='.$cid);
echo json_encode(array('ok'=>1));
