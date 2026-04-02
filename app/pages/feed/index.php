<?php

include("classes/autoload.php");

$login     = new Login();
$user_data = $login->check_login($_SESSION['mybook_userid']);
$USER      = $user_data;

$post_error = "";

// posting
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if (!csrf_validate_request()) {
		$post_error = "Invalid request token. Please refresh and try again.";
	} else {
    $post   = new Post();
    $id     = $_SESSION['mybook_userid'];
    $result = $post->create_post($id, $_POST, $_FILES);

    if ($result == "") {
        header("Location: index.php");
        die;
    } else {
        $post_error = $result;
    }
	}
}

$image_class = new Image();

// current user avatar
$my_image = "images/user_male.jpg";
if ($user_data['gender'] == "Female") $my_image = "images/user_female.jpg";
if (file_exists($user_data['profile_image'])) {
    $my_image = $image_class->get_thumb_profile($user_data['profile_image']);
}

// people the user follows (for sidebar)
$user_class = new User();
$following  = $user_class->get_following($user_data['userid'], "user");
$suggested_friends = $user_class->get_friend_suggestions($user_data['userid'], 6);

// build feed
$page_number  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit        = 10;
$offset       = ($page_number - 1) * $limit;
$DB           = new Database();
$posts        = [];

$followers    = $user_class->get_following($_SESSION['mybook_userid'], "user");
if (is_array($followers) && count($followers)) {
    $follow_ids = array_values(array_unique(array_map('intval', array_column($followers, "userid"))));
    $follow_ids = array_filter($follow_ids, function ($uid) { return $uid > 0; });

    if (count($follow_ids) > 0) {
        $placeholders = implode(",", array_fill(0, count($follow_ids), "?"));
        $types = "i" . str_repeat("i", count($follow_ids)) . "ii";
        $params = array_merge([(int)$_SESSION['mybook_userid']], $follow_ids, [$limit, $offset]);

        $sql = "SELECT * FROM posts WHERE parent = 0
                AND (userid = ? OR userid IN ($placeholders))
                ORDER BY id DESC LIMIT ? OFFSET ?";
        $posts = $DB->read_prepared($sql, $types, $params) ?: [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home &mdash; HopeSpring</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include("app/partials/header.php"); ?>

<div class="page-wrapper">
    <div class="two-col">

        <!-- LEFT SIDEBAR -->
        <aside class="col-left">
            <div class="sidebar-card">
                <a href="profile.php">
                    <img src="<?php echo htmlspecialchars($my_image); ?>" class="sidebar-avatar" alt="avatar">
                    <div class="sidebar-name"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></div>
                    <div style="font-size:12px;color:var(--muted);">@<?php echo htmlspecialchars($user_data['tag_name']); ?></div>
                </a>
                <a href="profile.php" class="btn btn-outline btn-sm btn-full" style="margin-top:12px;">View Profile</a>
            </div>

            <?php if (is_array($following) && count($following)): ?>
            <div class="sidebar-card" style="text-align:left;">
                <div class="sidebar-section-title">Following</div>
                <?php foreach (array_slice($following, 0, 6) as $friend):
                    $FR = $user_class->get_user($friend['userid']);
                    if (!$FR) continue;
                    $fr_img = ($FR['gender'] == 'Female') ? 'images/user_female.jpg' : 'images/user_male.jpg';
                    if (file_exists($FR['profile_image'])) $fr_img = $image_class->get_thumb_profile($FR['profile_image']);
                ?>
                <a href="profile.php?id=<?php echo $FR['userid']; ?>" class="user-card" style="display:flex;margin-bottom:4px;">
                    <img src="<?php echo htmlspecialchars($fr_img); ?>" class="uc-avatar" alt="">
                    <div class="uc-info">
                        <div class="uc-name"><?php echo htmlspecialchars($FR['first_name'] . ' ' . $FR['last_name']); ?></div>
                        <div class="uc-tag">@<?php echo htmlspecialchars($FR['tag_name']); ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="sidebar-card" style="text-align:left;">
                <div class="sidebar-section-title">Suggested Friends</div>
                <?php if (is_array($suggested_friends) && count($suggested_friends)): ?>
                    <?php foreach ($suggested_friends as $SUG):
                        $sug_img = ($SUG['gender'] == 'Female') ? 'images/user_female.jpg' : 'images/user_male.jpg';
                        if (!empty($SUG['profile_image']) && file_exists($SUG['profile_image'])) {
                            $sug_img = $image_class->get_thumb_profile($SUG['profile_image']);
                        }
                    ?>
                    <div style="margin-bottom:10px;">
                        <a href="profile.php?id=<?php echo (int)$SUG['userid']; ?>" class="user-card" style="display:flex;margin-bottom:6px;">
                            <img src="<?php echo htmlspecialchars($sug_img); ?>" class="uc-avatar" alt="">
                            <div class="uc-info">
                                <div class="uc-name"><?php echo htmlspecialchars($SUG['first_name'] . ' ' . $SUG['last_name']); ?></div>
                                <div class="uc-tag">@<?php echo htmlspecialchars($SUG['tag_name']); ?></div>
                            </div>
                        </a>
                        <a href="like.php?type=user&id=<?php echo (int)$SUG['userid']; ?>&csrf_token=<?php echo urlencode(csrf_token()); ?>" class="btn btn-outline btn-sm btn-full">Follow</a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state" style="font-size:13px;color:var(--muted);">No suggestions right now.</div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- MAIN FEED -->
        <main class="col-main">

            <?php if ($post_error): ?>
                <div class="alert alert-error"><?php echo $post_error; ?></div>
            <?php endif; ?>

            <!-- Composer -->
            <div class="composer">
                <form method="post" enctype="multipart/form-data" id="composer-form">
                    <?php echo csrf_input(); ?>
                    <div class="composer-top">
                        <img src="<?php echo htmlspecialchars($my_image); ?>" class="composer-avatar" alt="">
                        <textarea name="post" id="compose-text" placeholder="What's on your mind, <?php echo htmlspecialchars($user_data['first_name']); ?>?"
                                  oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>
                    </div>
                    <div class="composer-actions">
                        <label for="compose-file">
                            &#128247; Photo
                            <input type="file" id="compose-file" name="file" accept="image/*"
                                   onchange="document.getElementById('compose-file-name').textContent = this.files[0]?.name || ''">
                        </label>
                        <span id="compose-file-name" style="font-size:12px;color:var(--muted);flex:1;margin-left:8px;"></span>
                        <button type="submit" class="btn btn-primary btn-sm">Post</button>
                    </div>
                </form>
            </div>

            <!-- Posts -->
            <?php if (count($posts)): ?>
                <?php foreach ($posts as $ROW):
                    $user      = new User();
                    $ROW_USER  = $user->get_user($ROW['userid']);
                    include("app/partials/post.php");
                endforeach; ?>
            <?php else: ?>
                <div class="empty-state card" style="padding:48px 20px;">
                    <div class="empty-icon">&#128444;</div>
                    <p>Your feed is empty. Follow people to see their posts here.</p>
                    <a href="search.php" class="btn btn-primary" style="margin-top:14px;">Find People</a>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php $pg = pagination_link(); ?>
            <div class="pagination">
                <?php if ($page_number > 1): ?>
                    <a href="<?php echo $pg['prev_page']; ?>" class="btn btn-grey btn-sm">&#8592; Prev</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
                <span style="font-size:13px;color:var(--muted);">Page <?php echo $page_number; ?></span>
                <?php if (count($posts) == $limit): ?>
                    <a href="<?php echo $pg['next_page']; ?>" class="btn btn-grey btn-sm">Next &#8594;</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>

        </main>

    </div>
</div>

</body>
</html>

