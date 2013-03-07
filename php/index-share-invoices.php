<?php

$_SESSION['uid-invoice-visibility']=$uid;

echo '<table id="invoices"><thead>'
	.'<tr><th>Invoice Number</th><th>Date</th><th>PDF</th></tr>'
	.'</thead><tbody>';

$rs=dbAll(
	'select id,num,cdate from invoices where user_id='.$uid
	.' and type=0'
);
foreach ($rs as $r) {
	echo '<tr><td>'.$r['num'].'</td><td>'.$r['cdate'].'</td><td>'
		.'<a href="/php/invoice-pdf.php?id='.$r['id'].'">Download PDF</a>'
		.'</td></tr>';
}
echo '</thead></table>';

echo '<script src="/js/index-shares.js"></script>';
