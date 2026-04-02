<?php

include("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['mybook_userid']);

$USER = $user_data;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {

    $profile = new Profile();
    $profile_data = $profile->get_profile($_GET['id']);

    if (is_array($profile_data)) {
        $user_data = $profile_data[0];
    }
}

$Post = new Post();

$ERROR = "";
if (isset($_GET['id'])) {

    $ROW = $Post->get_one_post($_GET['id']);

    if (!$ROW) {

        $ERROR = "No such post was found!";
    } else {

        if ($ROW['userid'] != $_SESSION['mybook_userid']) {

            $ERROR = "Access denied! You cannot edit this post.";
        }
    }

} else {

    $ERROR = "No such post was found!";
}

if (isset($_SERVER['HTTP_REFERER']) && !strstr($_SERVER['HTTP_REFERER'], "edit.php")) {

    $_SESSION['return_to'] = $_SERVER['HTTP_REFERER'];
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if (!csrf_validate_request()) {
        http_response_code(403);
        die("Invalid request token.");
    }

    $Post->edit_post($_POST, $_FILES);

    header("Location: " . $_SESSION['return_to']);
    die;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Post | HopeSpring</title>
</head>
<body>

<?php include("app/partials/header.php"); ?>

<div class="page-wrapper" style="max-width:860px;">
    <section class="card settings-section">
        <h2>Edit Post</h2>

        <form method="post" enctype="multipart/form-data">
            <?php echo csrf_input(); ?>
            <?php
            if ($ERROR != "") {
                echo '<div class="alert alert-error">' . htmlspecialchars($ERROR) . '</div>';
            } else {
                echo '<div class="form-group"><label>Post Text</label><textarea class="form-control" style="min-height:140px;" name="post" placeholder="What is on your mind?">' . htmlspecialchars($ROW['post']) . '</textarea></div>';
                echo '<div class="form-group"><label>Replace image (optional)</label><input class="form-control" type="file" name="file"></div>';
                echo '<input type="hidden" name="postid" value="' . (int)$ROW['postid'] . '">';
                echo '<button class="btn btn-primary" type="submit">Save Changes</button>';

                if (file_exists($ROW['image'])) {
                    $image_class = new Image();
                    $post_image = $image_class->get_thumb_post($ROW['image']);
                    echo '<div style="margin-top:18px;"><img src="' . htmlspecialchars($post_image) . '" style="max-width:320px;border-radius:12px;" alt="Current post image"></div>';
                }
            }
            ?>
        </form>
    </section>
</div>

</body>
</html>
