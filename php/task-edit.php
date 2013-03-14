<?php
require_once 'basics.php';
/*
mysql> describe tasks;
+-------------+-------------+------+-----+-----------+----------------+
| Field       | Type        | Null | Key | Default | Extra          |
+-------------+-------------+------+-----+---------+----------------+
| id          | int(11)     | NO   | PRI | NULL    | auto_increment |
| cdate       | date        | YES  |     | NULL    |                |
| user_id     | int(11)     | YES  |     | 0       |                |
| status      | smallint(6) | YES  |     | 0       |                |
| meta        | text        | YES  |     | NULL    |                |
| description | text        | YES  |     | NULL    |                |
| priority    | int(11)     | YES  |     | 0       |                |
| customer_id | int(11)     | YES  |     | 0       |                |
| counter     | timestamp   | NO   |     | 0       |                |
| active      | int(1)      | YES  |     | 0       |                |
+-------------+-------------+------+-----+---------+----------------+
*/

if (!isset($_SESSION['userdata']['id'])) {
	exit;
}
$user_id=(int)$_SESSION['userdata']['id'];
header('Content-Type: text/json; charset=utf8');

$id=(int)$_REQUEST['id'];
$description=trim($_REQUEST['description']);
$priority=(int)$_REQUEST['priority'];
$status=(int)$_REQUEST['status'];
$customer_id=(int)$_REQUEST['customer_id'];

$sql='tasks set description="'.addslashes($description).'", priority='.$priority
	.', status='.$status.', customer_id='.$customer_id;
if ($id) {
	$meta=json_decode(dbOne('select meta from tasks where id='.$id, 'meta'));
	$meta->notes=$_REQUEST['notes'];
	$sql='update '.$sql.', meta="'.addslashes(json_encode($meta)).'"'
		.' where user_id='.$user_id.' and id='.$id;
}
else {
	$meta=array(
		'notes'=>$_REQUEST['notes']
	);
	$sql='insert into '.$sql.', cdate=now(), user_id='.$user_id
		.', meta="'.addslashes(json_encode($meta)).'"';
}
dbQuery($sql);

echo json_encode(array('ok'=>1));
