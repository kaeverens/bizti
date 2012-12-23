<?php
if (isset($_REQUEST['session_id'])) {
	session_id($_REQUEST['session_id']);
}
require_once 'basics.php';

header('Content-Type: text/json; charset=utf8');
if (!isset($_SESSION['userdata']['id'])) {
	exit(json_encode(array(
		'error'=>'not logged in'
	)));
}
$user_id=(int)$_SESSION['userdata']['id'];

require_once 'invoice-calculate-totals.php';
$rs=dbAll(
	'select id,products from invoices where user_id='.$_SESSION['userdata']['id']
);
foreach ($rs as $inv) {
	$products=json_decode($inv['products'], true);
	list($total, $tax_total)=Invoice_calculateTotals($products);
	dbQuery(
		'update invoices set total='.$total.' where id='.$inv['id']
	);
}
dbQuery(
	'update invoices set'
	.' paid=(select sum(amt) from payments where invoice_id=invoices.id)'
	.' where user_id='.$_SESSION['userdata']['id']
);
dbQuery(
	'update customers set'
	.' num_invoices=(select count(id) from invoices where customer_id=customers.id)'
	.', total=(select sum(total) from invoices where customer_id=customers.id)'
	.', paid=(select sum(paid) from invoices where customer_id=customers.id)'
	.' where user_id='.$_SESSION['userdata']['id']
);
dbQuery(
	'update invoices set paid=0'
	.' where paid is null and user_id='.$_SESSION['userdata']['id']
);
