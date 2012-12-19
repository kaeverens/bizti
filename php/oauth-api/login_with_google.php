<?php
/*
 * login_with_google.php
 *
 * @(#) $Id: login_with_google.php,v 1.5 2012/10/10 07:59:36 mlemos Exp $
 *
 */

require('http.php');
require('oauth_client.php');
require_once $_SERVER['DOCUMENT_ROOT'].'/config.php';

$client = new oauth_client_class;
$client->server = 'Google';
$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
	dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/login_with_google.php';

$client->client_id =$googleClientId;
$application_line = __LINE__;
$client->client_secret =$googleSecret;

if(strlen($client->client_id) == 0
|| strlen($client->client_secret) == 0)
	die('Please go to Google APIs console page '.
		'http://code.google.com/apis/console in the API access tab, '.
		'create a new client ID, and in the line '.$application_line.
		' set the client_id to Client ID and client_secret with Client Secret. '.
		'The callback URL must be '.$client->redirect_uri.' but make sure '.
		'the domain is valid and can be resolved by a public DNS.');

/* API permissions
 */
$client->scope = 'https://www.googleapis.com/auth/userinfo.email '.
	'https://www.googleapis.com/auth/userinfo.profile';
if(($success = $client->Initialize()))
{
	if(($success = $client->Process()))
	{
		if(strlen($client->authorization_error))
		{
			$client->error = $client->authorization_error;
			$success = false;
		}
		elseif(strlen($client->access_token))
		{
			$success = $client->CallAPI(
				'https://www.googleapis.com/oauth2/v1/userinfo',
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
