<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])
	&& !isset($_SESSION['uid-invoice-visibility'])
) {
	exit;
}

$uid=isset($_SESSION['userdata']['id'])
	?$_SESSION['userdata']['id']:$_SESSION['uid-invoice-visibility'];

$imgsrc='web';
require_once 'invoice-html.php';

echo $template.'<script>window.print();</script>';
