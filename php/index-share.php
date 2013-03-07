<script
	src="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<?php
require_once 'basics.php';

$bits=explode('/', $_REQUEST['share']);

$uid=((int)$bits[1]);
$type=$bits[0];
$r=dbRow(
	'select * from shares where user_id='.($uid)
	.' and type="'.addslashes($type).'"'
	.' and md5="'.addslashes($bits[2]).'"'
);
if (!$r) {
	echo '<em>This share is no longer valid.'
		.' Please contact the account holder and ask for a new link.</em>';
}
else {
	require_once 'php/index-share-'.$type.'.php';
	echo '<script src="/js/indexShare.php"></script>';
}
