<?php
if (isset($_REQUEST['session_id'])) {
	session_id($_REQUEST['session_id']);
}
require_once 'basics.php';

header('Content-Type: text/json; charset=utf8');
if (!isset($_SESSION['userdata']['id'])) {
	exit(json_encode(array(
		'error'=>'not logged in'
	)));
}
$user_id=(int)$_SESSION['userdata']['id'];

$dir='../userdata/'.$_SESSION['userdata']['id'].'/dump';

$accepted=array(
	'customers', 'user_domain', 'invoice_items', 'invoice_type', 'payment'
	, 'invoices', 'user', 'user_role', 'sql_patchmanager', 'preferences'
	, 'log', 'cron', 'invoice_item_tax', 'payment_types', 'tax', 'cron_log'
	, 'products', 'custom_fields', 'system_defaults', 'inventory', 'index'
	, 'extensions', 'biller'
);
$table=$_REQUEST['table'];
if (!in_array($table, $accepted)) {
	$str=json_encode(array(
		'error'=>'no hacking, please'
	));
	echo $str;
	exit;
}
$fname=$dir.'/si_'.$table.'.txt';
if (!file_exists($fname)) {
	$str=json_encode(array(
		'error'=>'missing table '.$_REQUEST['table']
	));
	echo $str;
	exit;
}

$str=1;
switch($table) {
	case 'log': case 'inventory': case 'user_domain': case 'user_role':
	case 'index': case 'payment_types': case 'invoice_type': case 'extensions':
	case 'biller': case 'cron_log': case 'custom_fields': case 'system_defaults':
	case 'preferences': case 'sql_patchmanager': case 'cron': case 'user':
	// { we don't import these
	break; // }
	case 'customers': // {
		$f=file($fname);
		foreach ($f as $line) {
			$r=explode('	', $line);
			$meta=array(
				'address'=>$r[5]."\n".$r[6]."\n".$r[7].' '.$r[9]."\n".$r[8]."\n".$r[10],
				'email'=>$r[14],
				'phone'=>$r[11].', '.$r[12],
				'notes'=>'attn: '.$r[2]."\n".$r[20],
				'SI_id'=>(string)$r[0]
			);
			$sql=' set user_id='.$_SESSION['userdata']['id']
				.', name="'.addslashes($r[3]).'"'
				.', meta="'.addslashes(json_encode($meta)).'"';
			$id=dbOne(
				'select id from customers'
				.' where user_id='.$_SESSION['userdata']['id']
				.' and meta like \'%"SI_id":"'.((int)$r[0]).'"%\'',
				'id'
			);
			if ($id) {
				dbQuery(
					'update customers'.$sql.' where id='.$id
				);
			}
			else {
				dbQuery(
					'insert into customers'.$sql
				);
			}
		}
	break; // }
	case 'invoices': // {
		$f=str_replace("\n", 'biztiNitzib', file_get_contents($fname));
		$f=preg_replace('/biztiNitzib([0-9]+	)/', "\n".'\1', $f);
		$f=explode("\n", $f);
		// { get list of SI customers
		$rs=dbAll(
			'select id,meta from customers'
			.' where user_id='.$_SESSION['userdata']['id']
			.' and meta like \'%"SI_id"%\''
		);
		$customers=array();
		foreach ($rs as $r) {
			$SI_id=(int)preg_replace('/.*"SI_id":"([0-9]+)".*/', '\1', $r['meta']);
			$customers[$SI_id]=$r['id'];
		}
		// }
		foreach ($f as $line) {
			$r=explode('	', str_replace('biztiNitzib', "\n", $line));
			$meta=array(
				'SI_id'=>(string)$r[0],
				'SI_cid'=>(string)$r[4]
			);
			$sql=' set cdate="'.addslashes($r[7]).'"'
				.', customer_id="'.$customers[(int)$r[4]].'"'
				.', user_id='.$_SESSION['userdata']['id']
				.', products="[]"'
				.', notes="'.addslashes($r[12]).'"'
				.', num="'.addslashes($r[1]).'"'
				.', meta="'.addslashes(json_encode($meta)).'"';
			$id=dbOne(
				'select id from invoices'
				.' where user_id='.$_SESSION['userdata']['id']
				.' and meta like \'%"SI_id":"'.((int)$r[0]).'"%\'',
				'id'
			);
			if ($id) {
				$sql='update invoices'.$sql.' where id='.$id;
			}
			else {
				$sql='insert into invoices'.$sql;
			}
			dbQuery($sql);
		}
	break; // }
	case 'invoice_items': // {
		$f=str_replace("\n", 'biztiNitzib', file_get_contents($fname));
		$f=preg_replace('/biztiNitzib([0-9]+	)/', "\n".'\1', $f);
		$f=explode("\n", $f);
		// { get list of SI invoices and clear the product data
		dbQuery(
			'update invoices set products="[]"'
			.' where user_id='.$_SESSION['userdata']['id']
			.' and meta like \'%"SI_id"%\''
		);
		$rs=dbAll(
			'select id,meta from invoices'
			.' where user_id='.$_SESSION['userdata']['id']
			.' and meta like \'%"SI_id"%\''
		);
		$invoices=array();
		foreach ($rs as $r) {
			$SI_id=(int)preg_replace('/.*"SI_id":"([0-9]+)".*/', '\1', $r['meta']);
			$invoices[$SI_id]=$r['id'];
		}
		// }
		// { get list of SI products
		$rs=dbAll(
			'select id,meta from products'
			.' where user_id='.$_SESSION['userdata']['id']
			.' and meta like \'%"SI_id"%\''
		);
		$products=array();
		foreach ($rs as $r) {
			$SI_id=(int)preg_replace('/.*"SI_id":"([0-9]+)".*/', '\1', $r['meta']);
			$products[$SI_id]=$r['id'];
		}
		// }
		foreach ($f as $line) {
			$r=explode('	', str_replace('biztiNitzib', "\n", $line));
			$inv=dbRow(
				'select id,products from invoices'
				.' where user_id='.$_SESSION['userdata']['id']
				.' and id='.((int)$invoices[$r[1]])
			);
			if (!$inv) {
				continue;
			}
			$invProds=json_decode($inv['products']);
			$invProds[]=array(
				'quantity'=>((float)$r[2]),
				'product'=>((int)$products[$r[3]]),
				'tax'=>0,
				'SI_id'=>((string)$r[0]),
				'price'=>((float)$r[4])
			);
			$id=$inv['id'];
			$sql=' set products="'.addslashes(json_encode($invProds)).'"';
			$sql='update invoices'.$sql.' where id='.$id;
			dbQuery($sql);
		}
	break; // }
	case 'invoice_item_tax': // {
		$f=str_replace("\n", 'biztiNitzib', file_get_contents($fname));
		$f=preg_replace('/biztiNitzib([0-9]+	)/', "\n".'\1', $f);
		$f=explode("\n", $f);
		// { get list of SI taxes
		$rs=dbAll(
			'select id,meta from taxes'
			.' where user_id='.$_SESSION['userdata']['id']
			.' and meta like \'%"SI_id"%\''
		);
		$taxes=array();
		foreach ($rs as $r) {
			$SI_id=(int)preg_replace('/.*"SI_id":"([0-9]+)".*/', '\1', $r['meta']);
			$taxes[$SI_id]=$r['id'];
		}
		// }
		foreach ($f as $line) {
			$r=explode('	', str_replace('biztiNitzib', "\n", $line));
			$prod=dbRow(
				'select id,products from invoices'
				.' where user_id='.$_SESSION['userdata']['id']
				.' and products like \'%"SI_id":"'.((int)$r[1]).'"%\''
			);
			if (!$prod) {
				continue;
			}
			$products=json_decode($prod['products'], true);
			foreach ($products as $k=>$p) {
				if ($p['SI_id']==$r[1]) {
					$products[$k]['tax']=((int)$taxes[$r[2]]);
				}
			}
			$sql='update invoices set products="'.addslashes(json_encode($products)).'"'
				.' where id='.$prod['id'];
			dbQuery($sql);
		}
	break; // }
	case 'payment': // {
		$f=str_replace("\n", 'biztiNitzib', file_get_contents($fname));
		$f=preg_replace('/biztiNitzib([0-9]+	)/', "\n".'\1', $f);
		$f=explode("\n", $f);
		// { get list of SI invoices
		$rs=dbAll(
			'select id,meta from invoices'
			.' where user_id='.$_SESSION['userdata']['id']
			.' and meta like \'%"SI_id"%\''
		);
		$invoices=array();
		foreach ($rs as $r) {
			$SI_id=(int)preg_replace('/.*"SI_id":"([0-9]+)".*/', '\1', $r['meta']);
			$invoices[$SI_id]=$r['id'];
		}
		// }
		foreach ($f as $line) {
			$r=explode('	', str_replace('biztiNitzib', "\n", $line));
			$meta=array(
				'SI_id'=>(string)$r[0]
			);
			$sql=' set cdate="'.addslashes($r[4]).'"'
				.', invoice_id="'.$invoices[(int)$r[1]].'"'
				.', user_id='.$_SESSION['userdata']['id']
				.', amt='.((float)$r[2])
				.', meta="'.addslashes(json_encode($meta)).'"';
			$id=dbOne(
				'select id from payments'
				.' where user_id='.$_SESSION['userdata']['id']
				.' and meta like \'%"SI_id":"'.((int)$r[0]).'"%\'',
				'id'
			);
			if ($id) {
				$sql='update payments'.$sql.' where id='.$id;
			}
			else {
				$sql='insert into payments'.$sql;
			}
			dbQuery($sql);
		}
	break; // }
	case 'products': // {
		$f=file($fname);
		// { get list of SI taxes
		$rs=dbAll('select id,meta from taxes where meta like \'%"SI_id"%\'');
		$taxes=array();
		foreach ($rs as $r) {
			$SI_id=(int)preg_replace('/.*"SI_id":"([0-9]+)".*/', '\1', $r['meta']);
			$taxes[$SI_id]=$r['id'];
		}
		// }
		foreach ($f as $line) {
			$r=explode('	', $line);
			$meta=array(
				'SI_id'=>(string)$r[0]
			);
			$sql=' set user_id='.$_SESSION['userdata']['id']
				.', name="'.addslashes($r[2]).'"'
				.', price='.((float)$r[4])
				.', tax='.((int)$taxes[(int)$r[5]])
				.', meta="'.addslashes(json_encode($meta)).'"';
			$id=dbOne(
				'select id from products'
				.' where user_id='.$_SESSION['userdata']['id']
				.' and meta like \'%"SI_id":"'.((int)$r[0]).'"%\'',
				'id'
			);
			if ($id) {
				$sql='update products '.$sql.' where id='.$id;
			}
			else {
				$sql='insert into products '.$sql;
			}
			dbQuery($sql);
		}
	break; // }
	case 'tax': // {
		$f=file($fname);
		foreach ($f as $line) {
			$r=explode('	', $line);
			$meta=array(
				'SI_id'=>(string)$r[0]
			);
			$r[2]=(float)$r[2];
			$sql=' set user_id='.$_SESSION['userdata']['id']
				.', name="'.addslashes($r[1]).'"'
				.', percentage='.$r[2]
				.', meta="'.addslashes(json_encode($meta)).'"';
			$id=dbOne(
				'select id from taxes'
				.' where user_id='.$_SESSION['userdata']['id']
				.' and percentage='.$r[2],
				'id'
			);
			if ($id) {
				$sql='update taxes'.$sql.' where id='.$id;
			}
			else {
				$sql='insert into taxes'.$sql;
			}
			dbQuery($sql);
		}
	break; // }
	default: // {
		$str=json_encode(array(
			'error'=>'unhandled import: '.$table
		));
		echo $str;
		exit;
	break; // }
}
echo $str;

unlink($fname);
