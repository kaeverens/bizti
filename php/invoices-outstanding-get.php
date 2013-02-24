<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$sql='select cdate, invoices.id as id, name, num'
	.', (invoices.total-invoices.paid) as amt'
	.' from invoices, customers'
	.' where customers.id=customer_id and (invoices.total-invoices.paid)>0.01'
	.' and invoices.user_id='.$user_id.' order by amt desc';
echo json_encode(dbAll($sql));
