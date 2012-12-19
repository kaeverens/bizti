<?php
if (isset($_REQUEST['PHPSESSID'])) {
	@session_id($_REQUEST['PHPSESSID']);
}
@session_start();

// { dbAll

/**
  * run a database query and return all resulting rows
  *
  * @param string $query the query to run
	* @param string $key   if supplied, use this field for the row keys
  *
  * @return array the results
  */
function dbAll($query, $key='') {
	$q = dbQuery($query);
	if ($q === false) {
		return false;
	}
	$results=array();
	while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
		$results[]=$r;
	}
	if (!$key) {
		return $results;
	}
	$arr=array();
	foreach ($results as $r) {
		if (!isset($r[$key])) {
			return false;
		}
		$arr[$r[$key]]=$r;
	}
	return $arr;
}

// }
// { dbInit

/**
  * initialise the database
  *
  * @return object the database object
  */
function dbInit() {
	if (isset($GLOBALS['db'])) {
		return $GLOBALS['db'];
	}
	try {
		require_once $_SERVER['DOCUMENT_ROOT'].'/config.php';
		$db=new PDO(
			'mysql:host='.$dbhost.';dbname='.$dbname,
			$dbuser,
			$dbpass,
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		);
		$GLOBALS['db']=$db;
		return $db;
	}
	catch (Exception $e) {
		die($e->getMessage());
	}
}

// }
// { dbLastInsertId

/**
  * get the id from the last database insert query
  *
  * @return int last insert id
  */
function dbLastInsertId() {
	return $GLOBALS['db']->lastInsertId();
}

// }
// { dbOne

/**
  * run a database query and return a single field
  *
  * @param string $query the query to run
  * @param string $field the field to return
  *
  * @return mixed false if it failed, or the requested field if successful
  */
function dbOne($query, $field='') {
	$r = dbRow($query);
	if ($r === false) {
		return false;
	}
	return $r[$field];
}

// }
// { dbQuery

/**
  * run a database query
  *
  * @param string $query the query to run
  *
  * @return mixed false if it failed, or the database resource if successful
  */
function dbQuery($query) {
	$db=dbInit();
	$q=$db->query($query);
	if ($q === false) { // failed
		return false;
	}
	return $q;
}

// }
// { dbRow

/**
  * run a database query and return a single row
  *
  * @param string $query the query to run
  *
  * @return array the returned row
  */
function dbRow($query) {
	$q = dbQuery($query);
	if ($q === false) {
		return false;
	}
	return $q->fetch(PDO::FETCH_ASSOC);
}

// }
