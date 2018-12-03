<?php
	include('config.php');
	if(!isset($_SESSION['fb_access_token'])){
		header('location:index.php');
	}
	if(!empty($_POST["post"])) {
    $photoPath = "";
    if(!empty($_FILES["post_photo"]["name"])) {
        $uploadFile = "uploads/" . basename($_FILES["post_photo"]["name"]);
        if (move_uploaded_file($_FILES["post_photo"]["tmp_name"], $uploadFile)) {
            $photoPath = $uploadFile;
        }
    }
    $myFB->uploadPost($_POST["content"],$photoPath,$_POST["postOn"]);
	
	// header("location:dashboard.php");
} ?>
<a href='dashboard.php'>Dashboard</a>
