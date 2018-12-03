<?php 
	include('config.php');
	if(!isset($_SESSION['fb_access_token'])){
		header('location:index.php');
	}
	$data = array('id','name');  // user info to be fetched via graph api eg: id,name,email,gender,photos,age_range,birthday,friends 
	$info = $myFB->dashboard($data);
	extract($info);
?>
<h3>Welcome , <?php echo $name. ". You can upload posts to your profile and pages managed by you"; ?></h3>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div id="post-box">
        <form method="post" action='post.php' enctype="multipart/form-data">
            <textarea name="content" class="photo-caption"></textarea>
            <div id="post-box-footer">
                <input type="file" name="post_photo"id="media-file-input" />
                <div class="photo-icon">
                    <img src="photo.png" />
				</div>
				<div class='postOn'> Post To<select name='postOn'>
					<option value=''>Your Own Timeline</option>
					 <optgroup label="Pages">
					<?php
						foreach($pages as $page){
							echo "<option value='{$page['id']}--{$page['accesstoken']}'>{$page['name']}</option>";
						}
					?>
					</optgroup>
				</select>
				</div>
				<div class=''>
				<input type="submit" name="post" class="photo_publish">
				</div>
			</div>
		</form>
	</div>
	
	<a href='logout.php'>Logout</a>
		