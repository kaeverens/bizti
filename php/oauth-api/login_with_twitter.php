<?php
/*
 * login_with_twitter.php
 *
 * @(#) $Id: login_with_twitter.php,v 1.2 2012/10/05 09:22:40 mlemos Exp $
 *
 */

require('http.php');
require('oauth_client.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/config.php';

$client = new oauth_client_class;
$client->debug = 1;
$client->server = 'Twitter';
$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
	dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/login_with_twitter.php';
$client->client_id=$twitterClientId;
$client->client_secret=$twitterSecret;
$application_line = __LINE__;

if(strlen($client->client_id) == 0
|| strlen($client->client_secret) == 0)
	die('Please go to Twitter Apps page https://dev.twitter.com/apps/new , '.
		'create an application, and in the line '.$application_line.
		' set the client_id to Consumer key and client_secret with Consumer secret. '.
		'The Callback URL must be '.$client->redirect_uri);

$client->scope = 'email';
if(($success = $client->Initialize()))
{
	if(($success = $client->Process()))
	{
		if(strlen($client->access_token))
		{
			$success = $client->CallAPI(
				'https://api.twitter.com/1.1/account/verify_credentials.json', 
				'GET', array(), array('FailOnAccessError'=>true), $user);
		}
	}
	$success = $client->Finalize($success);
}
if($client->exit)
	exit;
if($success)
{
	$user_profile=(array)$user;
	$user_profile['email']=$user_profile['screen_name'].'@twitter';
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
