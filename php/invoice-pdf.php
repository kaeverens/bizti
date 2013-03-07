<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])
	&& !isset($_SESSION['uid-invoice-visibility'])
) {
	exit;
}

$uid=isset($_SESSION['userdata']['id'])
	?$_SESSION['userdata']['id']:$_SESSION['uid-invoice-visibility'];

$imgsrc='local';
require_once 'invoice-html.php';
require_once 'dompdf/dompdf_config.inc.php';

$dompdf=new DOMPDF();
$dompdf->load_html($template);
$dompdf->render();
$dompdf->stream('invoice-'.$inv['num'].'.pdf');
