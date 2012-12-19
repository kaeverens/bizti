<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$pid=(int)$_REQUEST['pid'];
$p=dbRow(
	'select amt, customer_id, invoice_id from payments, invoices'
	.' where payments.id='.$pid.' and invoices.id=invoice_id'
);
dbQuery(
	'update customers set paid=paid-'.$p['amt'].' where id='.$p['customer_id']
);
dbQuery(
	'update invoices set paid=paid-'.$p['amt'].' where id='.$p['invoice_id']
);
dbQuery('delete from payments where id='.$pid);
echo json_encode(array('ok'=>1));
