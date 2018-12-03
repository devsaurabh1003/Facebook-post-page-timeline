<?php 
	$app_id = 'xxxxxxxxxxx';     // app id from facebook app
	$app_secret = 'xxxxxxxxxxxxxxxxxxxxx';     // app secret from facebook app
	include('class.FacebookMyApp.php');
	if(!session_id()) {
		session_start();
	}
	$myFB = new facebookMyApp($app_id,$app_secret);
?>