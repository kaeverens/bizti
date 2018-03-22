<?php
/*
 * login_with_facebook.php
 *
 * @(#) $Id: login_with_facebook.php,v 1.2 2012/10/05 09:22:40 mlemos Exp $
 *
 */

require('http.php');
require('oauth_client.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/config.php';

$client = new oauth_client_class;
$client->server = 'Facebook';
$client->redirect_uri = 'https://'.$_SERVER['HTTP_HOST'].
	dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/login_with_facebook.php';

$client->client_id =$facebookClientId;
$application_line = __LINE__;
$client->client_secret =$facebookSecret;

if(strlen($client->client_id) == 0
|| strlen($client->client_secret) == 0)
	die('Please go to Facebook Apps page https://developers.facebook.com/apps , '.
		'create an application, and in the line '.$application_line.
		' set the client_id to App ID/API Key and client_secret with App Secret');

/* API permissions
 */
$client->scope = 'email';
if(($success = $client->Initialize()))
{
	if(($success = $client->Process()))
	{
		error_log(__FILE__.'|'.__LINE__.'|'.json_encode($success));
		if(strlen($client->access_token))
		{
			$success = $client->CallAPI(
				'https://graph.facebook.com/me', 
				'GET', array(), array('FailOnAccessError'=>true), $user);
			error_log(__FILE__.'|'.__LINE__.'|'.json_encode($success));
		}
	}
	$success = $client->Finalize($success);
	error_log(__FILE__.'|'.__LINE__.'|'.json_encode($success));
}
if($client->exit)
	exit;
if($success)
{
	error_log(json_encode($user));
	$user_profile=(array)$user;
	if (!isset($user_profile['email'])) {
		$user_profile['email']=$user_profile['id'].'@facebook';
	}
	require_once '../login-with-userprofile.php';
}
else
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>OAuth client error</title>
</head>
<body>
<h1>OAuth client error</h1>
<pre>Error: <?php echo HtmlSpecialChars($client->error); ?></pre>
</body>
</html>
<?php
}

?>
