<?php 

	include("classes/autoload.php");
 
	$login = new Login();
	$user_data = $login->check_login($_SESSION['mybook_userid']);
 
 	$USER = $user_data;
 	
 	if(isset($_GET['id']) && is_numeric($_GET['id'])){

	 	$profile = new Profile();
	 	$profile_data = $profile->get_profile($_GET['id']);

	 	if(is_array($profile_data)){
	 		$user_data = $profile_data[0];
	 	}

 	}
 	
	//posting starts here
	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		if(!csrf_validate_request()){
			http_response_code(403);
			die("Invalid request token.");
		}
 
			$post = new Post();
			$id = $_SESSION['mybook_userid'];
			$result = $post->create_post($id, $_POST,$_FILES);
			
			if($result == "")
			{
				header("Location: single_post.php?id=$_GET[id]");
				die;
			}else
			{

				echo "<div style='text-align:center;font-size:12px;color:white;background-color:grey;'>";
				echo "<br>The following errors occured:<br><br>";
				echo $result;
				echo "</div>";
			}
 			
	}

	$Post = new Post();
	$ROW = false;

	$ERROR = "";
	if(isset($_GET['id'])){

		$ROW = $Post->get_one_post($_GET['id']);
	}else{

		$ERROR = "No post was found!";
	}

	$image_class = new Image();
	$user = new User();
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Single Post | HopeSpring</title>
</head>
<body>

	<?php include("app/partials/header.php"); ?>

	<div class="page-wrapper" style="max-width:860px;">
		<section class="card">
			<?php
			if (isset($_GET['notif'])) {
				notification_seen($_GET['notif']);
			}

			if (!empty($ERROR)) {
				echo '<div class="alert alert-error">' . htmlspecialchars($ERROR) . '</div>';
			} elseif (is_array($ROW)) {
				$ROW_USER = $user->get_user($ROW['userid']);
				if ($ROW['parent'] == 0) {
					include("app/partials/post.php");
				} else {
					$COMMENT = $ROW;
					include("app/partials/comment.php");
					echo '<a href="single_post.php?id=' . (int)$ROW['parent'] . '" class="btn btn-grey" style="margin-top:10px;">Back to main post</a>';
				}
			}
			?>
		</section>

		<?php if (is_array($ROW) && $ROW['parent'] == 0): ?>
			<section class="composer card">
				<form method="post" enctype="multipart/form-data">
					<?php echo csrf_input(); ?>
					<textarea name="post" class="form-control" placeholder="Write a comment..."></textarea>
					<input type="hidden" name="parent" value="<?php echo (int)$ROW['postid']; ?>">
					<div class="composer-actions">
						<label class="btn btn-outline" style="cursor:pointer;">
							Add Photo
							<input type="file" name="file" style="display:none;">
						</label>
						<button type="submit" class="btn btn-primary">Post Comment</button>
					</div>
				</form>
			</section>
		<?php endif; ?>

		<section class="card">
			<h3 style="margin-top:0;">Comments</h3>
			<?php
			if (is_array($ROW)) {
				$comments = $Post->get_comments($ROW['postid']);
				if (is_array($comments)) {
					foreach ($comments as $COMMENT) {
						$ROW_USER = $user->get_user($COMMENT['userid']);
						include("app/partials/comment.php");
					}
				} else {
					echo '<div class="empty-state">No comments yet.</div>';
				}
			}
			?>
		</section>
	</div>

</body>
</html>