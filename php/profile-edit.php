<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$user=dbRow(
	'select meta from users where id='.$user_id
);
$meta=json_decode($user['meta'], true);
$meta['company-name']=$_REQUEST['company-name'];
$meta['company-phone']=$_REQUEST['company-phone'];
$meta['company-email']=$_REQUEST['company-email'];
$meta['company-address']=$_REQUEST['company-address'];
$meta['payment-details']=$_REQUEST['payment-details'];
$options=isset($_REQUEST['options'])?(int)$_REQUEST['options']:0;

dbQuery(
	'update users set meta="'.addslashes(json_encode($meta)).'"'
	.', currency_symbol="'.addslashes($_REQUEST['currency-symbol']).'"'
	.', options='.$options
	.' where id='.$user_id
);

echo json_encode(array('ok'=>1));
