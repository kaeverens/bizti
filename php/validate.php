<?php
require_once 'basics.php';

$id=(int)$_REQUEST['id'];
$md5=$_REQUEST['md5'];

echo file_get_contents('../html/header.html');
echo '<div class="page-header"><h1>User Validation</h1></div>';

$r=dbRow('select id,email,meta from users where id='.$id);
if (!$r) {
	echo '<p>That user doesn\'t appear to exist!</p>';
}
else {
	$meta=json_decode($r['meta']);
	if (isset($meta->validation) && $meta->validation==$md5) {
		unset($meta->validation);
		dbQuery(
			'update users set active=1,meta="'.addslashes(json_encode($meta)).'"'
			.' where id='.$id
		);
		echo '<p>Welcome!</p>'
			.'<p>Your account has been validated, and you are now logged in.</p>'
			.'<p>Please <a href="/">click here</a> to start using bizti.</p>'
			.'<script>document.location="/";</script>';
		$_SESSION['userdata']=array(
			'id'=>$r['id'],
			'email'=>$r['email']
		);
	}
	else {
		echo '<p>Whoops!</p><p>Either you have already validated this account,'
			.' or you have the wrong validation code.</p>';
	}
}

echo file_get_contents('../html/footer.html');
