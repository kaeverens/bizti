<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$start=(int)$_REQUEST['iDisplayStart'];
$length=(int)$_REQUEST['iDisplayLength'];
$search=$_REQUEST['sSearch'];
$orderbyint=(int)$_REQUEST['iSortCol_0'];
$orderdesc=$_REQUEST['sSortDir_0']=='asc'?'asc':'desc';

switch((int)$_REQUEST['iSortCol_0']) {
	default:
		$orderby='num';
}

$sql='select payments.id as id, num, invoices.customer_id as customer_id'
	.', amt, payments.cdate as cdate'
	.' from payments, invoices'
	.' where payments.invoice_id=invoices.id'
	.' and payments.user_id='.$user_id.' order by '.$orderby.' '.$orderdesc
	.' limit '.$start.', '.$length;

$result=array();
$result['sEcho']=intval($_GET['sEcho']);
$result['iTotalRecords']=dbOne(
	'select count(id) as ids from products where user_id='.$user_id,
	'ids'
);
$filter='';
$result['iTotalDisplayRecords']=dbOne(
	'select count(id) as ids from products'
	.' where user_id='.$user_id.$filter,
	'ids'
);
$arr=array();
$rs=dbAll($sql);
foreach ($rs as $r) {
	$row=array(
		$r['id'], $r['cdate'], $r['num'], $r['customer_id'], $r['amt'], 0
	);
	$arr[]=$row;
}
$result['aaData']=$arr;

echo json_encode($result);
