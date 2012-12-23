<?php
function Invoice_calculateTotals($products) {
	$user_id=(int)$_SESSION['userdata']['id'];
	$total=0;
	$tax_total=0;
	$taxes=array();
	foreach ($products as $product) {
		$subtotal=(float)$product['quantity']*(float)$product['price'];
		$tax_id=(int)$product['tax'];
		if (!isset($taxes[$tax_id])) {
			$taxes[$tax_id]=(float)dbOne(
				'select percentage from taxes where user_id='.$user_id.' and id='.$tax_id,
				'percentage'
			);
		}
		$tax=$subtotal*$taxes[$tax_id]/100;
		$total+=$subtotal+$tax;
		$tax_total+=$tax;
	}
	return array($total, $tax_total);
}
