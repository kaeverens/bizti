<?php

require_once 'php/basics.php';
if (isset($canonical_domain) && $canonical_domain
	&& $canonical_domain!=$_SERVER['HTTP_HOST']
) {
	$http=isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']?'https':'http';
	header('Location: '.$http.'//'.$canonical_domain.'/');
	exit;
}
echo file_get_contents('html/header.html');

if (isset($_REQUEST['share'])) {
	require_once 'php/index-share.php';
}
else {
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
	require_once 'html/scripts-not-logged-in.html';
	$ftime=filemtime('js/bizti.me.js');
	echo '<script async="async"'
		.' src="/j/'.$ftime.'/not-logged-in.php"></script>';
}
}

echo file_get_contents('html/footer.html');
