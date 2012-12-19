<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$iid=(int)$_REQUEST['iid'];

$inv=dbRow(
	'select * from invoices where id='.$iid.' and user_id='.$user_id
);

if (!$inv) {
	exit;
}

dbQuery('delete from invoices where id='.$iid);
dbQuery(
	'update customers set num_invoices=num_invoices-1, total=total-'.$inv['total']
	.' where id='.$inv['customer_id']
);
echo json_encode(array('ok'=>1));
