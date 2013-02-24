<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$notes=trim($_REQUEST['notes']);
$customer_id=(int)$_REQUEST['customer_id'];
$cdate=$_REQUEST['cdate'];
$products=$_REQUEST['products'];
$id=(int)$_REQUEST['id'];

require_once 'invoice-calculate-totals.php';
list($total, $tax_total)=Invoice_calculateTotals($products);
$sql='invoices set customer_id='.$customer_id.', cdate="'.addslashes($cdate).'"'
	.', notes="'.addslashes($notes).'", total='.$total
	.', products="'.addslashes(json_encode($products)).'"'
	.', num='.((int)$_REQUEST['num'])
	.', tax='.$tax_total
	.', user_id='.$user_id;
if ($id) {
	$inv=dbRow('select customer_id, total from invoices where id='.$id);
	$oldCid=(int)$inv['customer_id'];
	$oldTotal=(float)$inv['total'];
	if ($oldCid!=$customer_id || $oldTotal!=$total) {
		dbQuery(
			'update customers set num_invoices=num_invoices+1, total=total+'.$total
			.' where id='.$customer_id
		);
		dbQuery(
			'update customers set num_invoices=num_invoices-1, total=total-'.$oldTotal
			.' where id='.$oldCid
		);
	}
	$sql='update '.$sql.' where user_id='.$user_id.' and id='.$id;
}
else {
	$sql='insert into '.$sql;
	dbQuery(
		'update customers set num_invoices=num_invoices+1, total=total+'.$total
		.' where id='.$customer_id
	);
}
dbQuery($sql);

echo json_encode(array('ok'=>1));
