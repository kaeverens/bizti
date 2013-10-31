<?php
$iid=(int)$_REQUEST['id'];

$inv=dbRow('select * from invoices where id='.$iid.' and user_id='.$uid);
$invoice_meta=json_decode($inv['meta'], true);
if (!$inv) {
	exit;
}
$invDesc=$inv['type']?'quote':'invoice';

$profile=dbRow('select * from users where id='.$uid);
$meta=json_decode($profile['meta'], true);

if (file_exists($_SERVER['DOCUMENT_ROOT'].'/userdata/'.$uid.'/'.$invDesc.'.html')) {
	$template=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/userdata/'.$uid.'/'.$invDesc.'.html');
}
else {
	$template=file_get_contents($_SERVER['DOCUMENT_ROOT'].'/html/'.$invDesc.'.html');
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
	.' where id='.$inv['customer_id'].' and user_id='.$uid
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
	($imgsrc=='local'?$_SERVER['DOCUMENT_ROOT']:'').'/userdata/'.$uid.'/logo.png',
	$template
);
$template=str_replace('{{$invoice_number}}', $inv['num'], $template);
$template=str_replace('{{$invoice_date}}', date('jS M Y', strtotime($inv['cdate'])), $template);
$template=str_replace('{{$invoice_total}}', price($inv['total']), $template);
$template=str_replace('{{$invoice_total_tax}}', price($inv['tax']), $template);
$template=str_replace('{{$invoice_total_net}}', price($inv['total']-$inv['tax']), $template);
$template=str_replace('{{$invoice_paid}}', price($inv['paid']), $template);
$template=str_replace('{{$invoice_owing}}', price($inv['total']-$inv['paid']), $template);
$template=str_replace(
	'{{$company_payment_details}}', $meta['payment-details'], $template
);
preg_match_all('/{{\$meta\.(.*?)}}/', $template, $matches);
foreach ($matches[1] as $v) {
	$template=str_replace('{{$meta.'.$v.'}}', $invoice_meta[$v], $template);
}
// { invoice table
$headers=array(
	'qty'=>'Qty',
	'item'=>'Item',
	'unit_cost'=>'Unit Cost',
	'price'=>'Price'
);
preg_match_all('/{{\$invoice_table.*?}}/', $template, $matches);
$match=$matches[0][0];
if (preg_match('/ headers=\[/', $match)) {
	$headers=array();
	$v1=preg_replace('/.* headers=\[\'(.*?)\'\].*/', '\1', $match);
	foreach (explode("', '", $v1) as $v2) {
		list($k, $v)=explode("'=>'", $v2);
		$headers[$k]=$v;
	}
}
$table='<table id="invoice-table" class="block" style="width:100%">'
	.'<thead><tr>';
$tdsCount=0;
foreach ($headers as $k=>$v) {
	$tdsCount++;
	if ($k=='unit_cost' || $k=='price' || $k=='tax' || $k=='tax_percent') {
		$table.='<th class="right">'.$v.'</th>';
	}
	else {
		$table.='<th>'.$v.'</th>';
	}
}
$table.='</tr></thead>'
	.'<tbody>';
$products=json_decode($inv['products'], true);
$ps=array();
$totals=array();
$total=0;
$taxes=array();
foreach (dbAll('select * from taxes where user_id='.$uid) as $t) {
	$taxes[(int)$t['id']]=$t;
}
foreach ($products as $p) {
	$pid=(int)$p['product'];
	if (!isset($ps[$pid])) {
		$ps[$pid]=dbRow('select * from products where id='.$pid.' and user_id='.$uid);
	}
	if (!isset($totals[$ps[$pid]['tax']])) {
		$totals[$ps[$pid]['tax']]=0;
	}
	$ptotal=$p['quantity']*$p['price'];
	$totals[$ps[$pid]['tax']]+=$ptotal;
	$total+=$ptotal;
	$table.='<tr>';
	foreach ($headers as $k=>$v) {
		switch($k) {
			case 'qty':
				$table.='<td>'.$p['quantity'].'</td>';
			break;
			case 'item':
				$table.='<td>'.htmlspecialchars($ps[$pid]['name']).'</td>';
			break;
			case 'unit_cost':
				$table.='<td class="right">'.price($p['price']).'</td>';
			break;
			case 'price':
				$table.='<td class="right">'.price($ptotal).'</td>';
			break;
			case 'tax_percent':
				$table.='<td class="right">'
					.(float)$taxes[(int)$p['tax']]['percentage'].'%</td>';
			break;
			case 'tax':
				$table.='<td class="right">'.price($ptotal*$taxes[(int)$p['tax']]['percentage']/100).'</td>';
			break;
			break;
			default: $table.='<td>&nbsp;</td>';
		}
	}
	$table.='</tr>';
}
if ($inv['notes']) {
	$table.='<tr><th colspan="'.$tdsCount.'">Notes:</th></tr>'
		.'<tr><td colspan="'.$tdsCount.'">'.$inv['notes'].'</td></tr>';
}
if (strpos($match, 'noTotals')===false) {
	if ($total!=$inv['total']) {
		$table.='<tr><td class="right" colspan="'.($tdsCount-1).'">Sub total</td><td class="right">'
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
	$table.='<tr><th colspan="'.($tdsCount-1).'" class="right">'.ucfirst($invDesc).' Amount</th>'
		.'<th class="right">'.price($inv['total']).'</th></tr>';
}
$table.='</tbody></table>';
// }
$template=str_replace($match, $table, $template);
