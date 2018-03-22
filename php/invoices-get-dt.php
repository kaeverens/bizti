<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$start=(int)$_REQUEST['iDisplayStart'];
$length=(int)$_REQUEST['iDisplayLength'];
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
$filters=array(
	'invoices.user_id='.$user_id
);
if ($_REQUEST['sSearch_0']) { // invoice number
	$filters[]='num='.((int)$_REQUEST['sSearch_0']);
}
if ($_REQUEST['sSearch_1']) { // customer name
	$filters[]='name like "%'.addslashes($_REQUEST['sSearch_1']).'%"';
}
if ($_REQUEST['sSearch_2']) { // date
	$filters[]='cdate="'.addslashes($_REQUEST['sSearch_2']).'"';
}
if ($_REQUEST['sSearch_3']) { // total
	$filters[]='invoices.total='.((float)$_REQUEST['sSearch_3']);
}
if ($_REQUEST['sSearch_4']) { // paid
	$filters[]='invoices.paid='.((float)$_REQUEST['sSearch_4']);
}
if ($_REQUEST['sSearch_5']) { // owing
	$filters[]='owing='.((float)$_REQUEST['sSearch_5']);
}

$sql='select invoices.id as id, num, customer_id, cdate, invoices.total'
	.', invoices.paid,(invoices.total-invoices.paid) as owing, name'
	.', datediff(now(), cdate) as date_diff'
	.' from invoices left join customers on customers.id=customer_id'
	.' where '.join(' and ', $filters).' order by '.$orderby.' '.$orderdesc
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
	'select count(invoices.id) as ids from invoices left join customers on customers.id=customer_id'
	.' where '.join(' and ', $filters),
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
