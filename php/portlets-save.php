<?php
require_once 'basics.php';

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$page=$_REQUEST['page'];
$portlets=$_REQUEST['portlets'];

dbQuery(
	'delete from portlets where page="'.addslashes($page).'"'
	.' and user_id='.$user_id
);
dbQuery(
	'insert into portlets set page="'.addslashes($page).'"'
	.', portlets="'.addslashes($portlets).'", user_id='.$user_id
);

echo '{"ok":1}';
