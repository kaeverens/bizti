<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$iid=(int)$_REQUEST['iid'];

$inv=dbRow(
	'select id, num, customer_id, cdate, notes, products from invoices'
	.' where id='.$iid.' and user_id='.$user_id
);

$inv['date']=$inv['cdate'];
unset($inv['cdate']);
$inv['products']=json_decode($inv['products'], true);
echo json_encode($inv);
