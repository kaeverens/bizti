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
	case 3:
		$orderby='cdate';
		break;
	case 4:
		$orderby='total';
		break;
	case 5:
		$orderby='paid';
		break;
	case 6:
		$orderby='owing';
		break;
	case 7:
		$orderby='cdate';
		break;
	default:
		$orderby='num';
}

$sql='select id,num, customer_id,cdate,total,paid,(total-paid) as owing'
	.', datediff(now(), cdate) as date_diff'
	.' from invoices'
	.' where user_id='.$user_id.' order by '.$orderby.' '.$orderdesc
	.' limit '.$start.', '.$length;

$result=array();
$result['sEcho']=intval($_GET['sEcho']);
$result['iTotalRecords']=dbOne(
	'select count(id) as ids from invoices'
	.' where user_id='.$user_id,
	'ids'
);
$filter='';
$result['iTotalDisplayRecords']=dbOne(
	'select count(id) as ids from invoices'
	.' where user_id='.$user_id.$filter,
	'ids'
);
$arr=array();
$rs=dbAll($sql);
foreach ($rs as $r) {
	$row=array(
		$r['id'], $r['num'], $r['customer_id'], $r['cdate'], $r['total']
		, $r['paid'], 0, $r['date_diff'], 0
	);
	$arr[]=$row;
}
$result['aaData']=$arr;

echo json_encode($result);
