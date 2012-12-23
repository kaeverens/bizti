<?php
if (isset($_REQUEST['session_id'])) {
	session_id($_REQUEST['session_id']);
}
require_once 'basics.php';

header('Content-Type: text/json; charset=utf8');
if (!isset($_SESSION['userdata']['id'])) {
	exit(json_encode(array(
		'error'=>'not logged in'
	)));
}
$user_id=(int)$_SESSION['userdata']['id'];

$dir='../userdata/'.$_SESSION['userdata']['id'];
@mkdir($dir, 0777, true);
$dir=realpath($dir);
$zip=new ZipArchive;
$res=$zip->open($_FILES['Filedata']['tmp_name']);
if ($res!==true) {
	exit(json_encode(array(
		'error'=>'failed to open zip file'
	)));
}

$accepted=array(
	'dump/', 'dump/si_user_domain.txt' , 'dump/si_invoice_items.txt'
	, 'dump/si_invoice_type.txt' , 'dump/si_payment.txt' , 'dump/si_cron_log.txt'
	, 'dump/si_invoices.txt', 'dump/si_user.txt', 'dump/si_user_role.txt'
	, 'dump/si_sql_patchmanager.txt', 'dump/si_preferences.txt', 'dump/si_log.txt'
	, 'dump/si_cron.txt', 'dump/si_customers.txt', 'dump/si_invoice_item_tax.txt'
	, 'dump/si_payment_types.txt', 'dump/si_tax.txt', 'dump/si_products.txt'
	, 'dump/si_custom_fields.txt', 'dump/si_system_defaults.txt'
	, 'dump/si_inventory.txt', 'dump/si_index.txt', 'dump/si_extensions.txt'
	, 'dump/si_biller.txt'
);
$data=array();
for ($i=0;$stat=$zip->statIndex($i);++$i) {
	if (!in_array($stat['name'], $accepted)) {
		$str=json_encode(array(
			'error'=>'unknown file in zip file: '.$stat['name']
		));
		echo $str;
		exit;
	}
	$zip->extractTo($dir, $stat['name']);
	$data[]=$stat;
}
$zip->close();
echo '1';
