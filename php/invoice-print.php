<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}

$imgsrc='web';
require_once 'invoice-html.php';

echo $template.'<script>window.print();</script>';
