<?php

require_once 'php/basics.php';
echo file_get_contents('html/header.html');

if (isset($_SESSION['userdata']['id'])) {
	$user_id=$_SESSION['userdata']['id'];
	echo '<script>window.userdata='.json_encode($_SESSION['userdata']).';'
		.'window.session_id="'.session_id().'";';
	// { customerNames
	$rs=dbAll(
		'select id,name from customers where user_id='.$user_id.' order by name'
	);
	echo 'window.customerNames='.($rs?json_encode($rs):'[]').';';
	// }
	// { taxes
	$rs=dbAll(
		'select id,name,percentage from taxes where user_id='.$user_id
		.' order by name'
	);
	echo 'window.taxes='.($rs?json_encode($rs):'[]').';';
	// }
	// { products
	$rs=dbAll(
		'select id,name,price,tax from products where user_id='.$user_id
		.' order by name'
	);
	echo 'window.products='.($rs?json_encode($rs):'[]').';';
	// }
	echo '</script>';
	require_once 'html/scripts-bizti.me.html';
	$ftime=filemtime('js/bizti.me.js');
	echo '<script async="async"'
		.' src="/j/'.$ftime.'/bizti.me.php"></script>';
}
else {
	echo '<div class="page-header">'
		.'<h1><img src="/images/logo.png" style="vertical-align:top;" height="48" width="132"/>'
		.' <small>invoice and time-tracking tools for small'
		.' businesses</small></h1></div>';

	echo '<iframe style="float:right" width="420" height="315" src="http://www.youtube.com/embed/v-N4RXDyOLI?rel=0" frameborder="0" allowfullscreen></iframe>';
	echo '<p>bizti is a suite of applications for small businesses.'
		.' It\'s designed to be simple and quick to use.</p>'
		.'<p>&nbsp;</p>'
		.'<p><strong>To register or login, please click the button on the top right of the page</strong></p>'
		.'<p>&nbsp;</p>'
		.'<p>The applications currently included are:</p>'
		.'<ul><li>Invoicing</li></ul>';

	require_once 'html/scripts-not-logged-in.html';
	$ftime=filemtime('js/bizti.me.js');
	echo '<script async="async"'
		.' src="/j/'.$ftime.'/not-logged-in.php"></script>';
}

echo file_get_contents('html/footer.html');
