<?php 
	include('config.php');
	if(isset($_SESSION['fb_access_token'])){
		header('location:dashboard.php');
	}

// $helper = $fb->getRedirectLoginHelper();
$redUrl='http://localhost/fb-login-post/redirect.php';
$permissions = array('email','manage_pages','user_gender','user_photos','user_age_range','user_birthday','user_friends','publish_pages'); // Optional permissions  
/* Note :  publish_actions permissions is not allowed by fb due to privacy policy changes without that we cannot post on own timeline*/
$loginUrl = $myFB->fbLoginReq($redUrl, $permissions);

echo  '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';