<?php
#http_cache_etag();
$mainfile='style.css';
$files=array(
	$mainfile
);

header('Content-Type: text/css; charset=utf8');
header('Expires-Active: On');
header('Cache-Control: max-age = 99999999');
header('Expires: '. date('r', time()+9999999));
header('Pragma:');

$latest=0;
foreach ($files as $file) {
	$t=filemtime($file);
	if ($t>$latest) {
		$latest=$t;
	}
}
if (!file_exists('../cache/'.$mainfile)
	|| filemtime('../cache/'.$mainfile)<$latest
) {
	require_once 'cssmin.php';
	@unlink('../cache/'.$mainfile);
	$css='';
	foreach ($files as $file) {
		$css.=file_get_contents($file);
	}
	$result=CssMin::minify($css);
	file_put_contents('../cache/'.$mainfile, $result);
}
$fp=fopen('../cache/'.$mainfile, 'r');
header('Content-Length: '.filesize('../cache/'.$mainfile));
fpassthru($fp);
