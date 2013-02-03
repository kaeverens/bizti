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
	case '2':
		$orderby='num_invoices';
	break;
	case '3':
		$orderby='total';
	break;
	case '4':
		$orderby='owed';
	break;
	default:
		$orderby='name';
}

$sql='select id, name, num_invoices, total, paid, total-paid as owed'
	.' from customers'
	.' where user_id='.$user_id.' order by '.$orderby.' '.$orderdesc
	.' limit '.$start.', '.$length;

$result=array();
$result['sEcho']=intval($_GET['sEcho']);
$result['iTotalRecords']=dbOne(
	'select count(id) as ids from customers where user_id='.$user_id,
	'ids'
);
$filter='';
$result['iTotalDisplayRecords']=dbOne(
	'select count(id) as ids from customers'
	.' where user_id='.$user_id.$filter,
	'ids'
);
$arr=array();
$rs=dbAll($sql);
foreach ($rs as $r) {
	if ($r['paid']==='null') {
		dbQuery('update customers set paid=0 where id='.$r['id']);
	}
	$row=array(
		$r['id'], $r['name'], $r['num_invoices'], $r['total'],
		$r['owed'], 0
	);
	$arr[]=$row;
}
$result['aaData']=$arr;

echo json_encode($result);
