<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}

require_once 'invoice-html.php';
require_once 'dompdf/dompdf_config.inc.php';

$dompdf=new DOMPDF();
$dompdf->load_html($template);
$dompdf->render();
$dompdf->stream('invoice-'.$inv['num'].'.pdf');
