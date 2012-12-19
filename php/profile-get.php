<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$user=dbRow(
	'select currency_symbol,meta from users where id='.$user_id
);

$meta=json_decode($user['meta'], true);
$profile=array(
	'company-phone'=>isset($meta['company-phone'])?$meta['company-phone']:'',
	'company-email'=>isset($meta['company-email'])?$meta['company-email']:'',
	'company-name'=>isset($meta['company-name'])?$meta['company-name']:'',
	'company-address'=>isset($meta['company-address'])?$meta['company-address']:'',
	'payment-details'=>isset($meta['payment-details'])?$meta['payment-details']:'',
	'currency-symbol'=>$user['currency_symbol'],
	'logo-num'=>isset($meta['logo-num'])?$meta['logo-num']:''
);

echo json_encode($profile);
