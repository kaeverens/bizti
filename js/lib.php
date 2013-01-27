<?php
header('Content-Type: text/javascript; charset=utf8');
header('Expires-Active: On');
header('Cache-Control: max-age = 99999999');
header('Expires: '. date('r', time()+9999999));
header('Pragma:');

session_start();
$latest=0;
$level=0;
if (isset($_SESSION['userdata']['level'])) {
	$level=(int)$_SESSION['userdata']['level'];
}
$mainfile=$mainfile.'.'.$level;
foreach ($files as $k=>$file) {
	if (file_exists($file.'.'.$level.'.js')) {
		$file=$file.'.'.$level.'.js';
	}
	else {
		for ($i=$level-1;$i>0;--$i) {
			if (file_exists($file.'.'.$i.'.js')) {
				$file=$file.'.'.$i.'.js';
				break;
			}
		}
		if (!file_exists($file.'.'.$level.'.js')) {
			$file=$file.'.js';
		}
	}
	$files[$k]=$file;
	$t=filemtime($file);
	if ($t>$latest) {
		$latest=$t;
	}
}
if (!file_exists('../cache/'.$mainfile)
	|| filemtime('../cache/'.$mainfile)<$latest
) {
	@unlink('../cache/'.$mainfile);
	$html='';
	require_once 'Minifier.php';
	foreach ($files as $file) {
		$html.=(strpos($file, '.min.')===false)
			?str_replace(
				"'\n+'",
				'',
				JShrink\Minifier::minify(file_get_contents($file))
			)
			:file_get_contents($file);
	}
	file_put_contents(
		'../cache/'.$mainfile,
		$html
	);
}
$fp=fopen('../cache/'.$mainfile, 'r');
header('Content-Length: '.filesize('../cache/'.$mainfile));
fpassthru($fp);
