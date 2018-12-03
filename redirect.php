<?php 
include('config.php');
if(isset($_SESSION['fb_access_token'])){
		header('location:dashboard.php');
	}else{
	header('location:index.php');
	}
$redUrl = $myFB->fbRedirect();


$_SESSION['fb_access_token'] = $redUrl;

// echo $_SESSION['fb_access_token'];
header('Location: http://localhost/fb-login-post/dashboard.php');
