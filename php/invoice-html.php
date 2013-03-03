<?php
$user_id=(int)$_SESSION['userdata']['id'];

$iid=(int)$_REQUEST['id'];

$inv=dbRow('select * from invoices where id='.$iid.' and user_id='.$user_id);
if (!$inv) {
	exit;
}
$invDesc=$inv['type']?'quote':'invoice';

$profile=dbRow('select * from users where id='.$user_id);
$meta=json_decode($profile['meta'], true);

if (file_exists('../userdata/'.$user_id.'/'.$invDesc.'.html')) {
	$template=file_get_contents('../userdata/'.$user_id.'/'.$invDesc.'.html');
}
else {
	$template=file_get_contents('../html/'.$invDesc.'.html');
}

// { company
$template=str_replace('{{$company_name}}', $meta['company-name'], $template);
$template=str_replace('{{$company_address}}', nl2br($meta['company-address']), $template);
$template=str_replace('{{$company_phone}}', $meta['company-phone'], $template);
$template=str_replace('{{$company_email}}', $meta['company-email'], $template);
// }
// { customer
$cust=dbRow(
	'select * from customers'
	.' where id='.$inv['customer_id'].' and user_id='.$user_id
);
$template=str_replace('{{$customer_name}}', $cust['name'], $template);
$cmeta=json_decode($cust['meta'], true);
$template=str_replace('{{$customer_address}}', nl2br($cmeta['address']), $template);
$template=str_replace('{{$customer_phone}}', $cmeta['phone'], $template);
$template=str_replace('{{$customer_email}}', $cmeta['email'], $template);
// }

function price($num) {
	return $_SESSION['userdata']['currency'].sprintf('%0.2f', $num);
}

$template=str_replace(
	'{{$logo_url}}',
	($imgsrc=='local'?$_SERVER['DOCUMENT_ROOT']:'').'/userdata/'.$user_id.'/logo.png',
	$template
);
$template=str_replace('{{$invoice_number}}', $inv['num'], $template);
$template=str_replace('{{$invoice_date}}', $inv['cdate'], $template);
$template=str_replace('{{$invoice_total}}', price($inv['total']), $template);
$template=str_replace('{{$invoice_paid}}', price($inv['paid']), $template);
$template=str_replace('{{$invoice_owing}}', price($inv['total']-$inv['paid']), $template);
$template=str_replace(
	'{{$company_payment_details}}', $meta['payment-details'], $template
);
// { invoice table
$table='<table class="block" style="width:100%">'
	.'<thead><tr><th>Qty</th><th>Item</th><th class="right">Unit Cost</th>'
	.'<th class="right">Price</th></tr></thead>'
	.'<tbody>';
$products=json_decode($inv['products'], true);
$ps=array();
$totals=array();
$total=0;
foreach ($products as $p) {
	$pid=(int)$p['product'];
	if (!isset($ps[$pid])) {
		$ps[$pid]=dbRow('select * from products where id='.$pid.' and user_id='.$user_id);
	}
	if (!isset($totals[$ps[$pid]['tax']])) {
		$totals[$ps[$pid]['tax']]=0;
	}
	$ptotal=$p['quantity']*$p['price'];
	$totals[$ps[$pid]['tax']]+=$ptotal;
	$total+=$ptotal;
	$table.='<tr><td>'.$p['quantity'].'</td>'
		.'<td>'.htmlspecialchars($ps[$pid]['name']).'</td>'
		.'<td class="right">'.price($p['price']).'</td>'
		.'<td class="right">'.price($ptotal).'</td></tr>';
}
if ($inv['notes']) {
	$table.='<tr><th colspan="4">Notes:</th></tr>'
		.'<tr><td colspan="4">'.$inv['notes'].'</td></tr>';
}
if ($total!=$inv['total']) {
	$table.='<tr><td class="right" colspan="3">Sub total</td><td class="right">'
		.price($total).'</td></tr>';
	foreach ($totals as $k=>$v) {
		$tax=dbRow('select * from taxes where id='.$k);
		if ($tax['percentage']) {
			$table.='<tr><td class="right" colspan="3">'.htmlspecialchars($tax['name'])
				.' ('.$tax['percentage'].'%)</td><td class="right">'
				.price($tax['percentage']*$v/100).'</td></tr>';
		}
	}
}
$table.='<tr><th colspan="3" class="right">'.ucfirst($invDesc).' Amount</th>'
	.'<th class="right">'.price($inv['total']).'</th></tr>';
$table.='</tbody></table>';
// }
$template=str_replace('{{$invoice_table}}', $table, $template);
