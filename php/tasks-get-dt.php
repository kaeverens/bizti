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

$orderby='cdate';

$filters=array(
	'tasks.user_id='.$user_id
);

$sql='select tasks.id as id, description, customer_id, priority, status, name, UNIX_TIMESTAMP(counter), active'
	.' from tasks'
	.' left join customers on customers.id=customer_id'
	.' where '.join(' and ', $filters)
	.' order by '.$orderby.' '.$orderdesc
	.' limit '.$start.', '.$length;

$result=array();
$result['sEcho']=intval($_GET['sEcho']);
$result['iTotalRecords']=dbOne(
	'select count(id) as ids from tasks'
	.' where user_id='.$user_id,
	'ids'
);
$filter='';
$result['iTotalDisplayRecords']=dbOne(
	'select count(tasks.id) as ids from tasks, customers'
	.' where '.join(' and ', $filters),
	'ids'
);
$arr=array();
$rs=dbAll($sql);
foreach ($rs as $r) {
	$time=($r[ 'active' ]==1)?time()-$r['UNIX_TIMESTAMP(counter)']:$r['UNIX_TIMESTAMP(counter)'];
	$row=array(
		$r['id'], $r['description'], $r['name'], $r['priority']
		, $r['status'], $r['customer_id'],
		$time,$r[ 'active' ]
	);
	$arr[]=$row;
}
$result['aaData']=$arr;

echo json_encode($result);
