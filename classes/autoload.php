<?php 
	
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	include("classes/connect.php");
	include("classes/functions.php");
	include("classes/login.php");
	include("classes/user.php");
	include("classes/post.php");
	include("classes/message.php");
 	include("classes/image.php");
 	include("classes/profile.php");
 	include("classes/settings.php");
 	include("classes/time.php");
