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
	
	$Post = new Post();
	$User = new User();
 	$image_class = new Image();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifications | HopeSpring</title>
</head>
<body>

<?php include("app/partials/header.php"); ?>

<div class="page-wrapper" style="max-width:860px;">
    <section class="card">
        <h2 style="margin-top:0;">Notifications</h2>

        <?php
        $DB     = new Database();
        $id     = (int)$_SESSION['mybook_userid'];
        $follow = array();

        $sql      = "select * from content_i_follow where disabled = 0 && userid = ? limit 100";
        $i_follow = $DB->read_prepared($sql, "i", [$id]);
        if (is_array($i_follow)) {
            $follow = array_values(array_filter(array_map('intval', array_column($i_follow, "contentid")), function ($cid) {
                return $cid > 0;
            }));
        }

        if (count($follow) > 0) {
            $placeholders = implode(",", array_fill(0, count($follow), "?"));
            $query = "select * from notifications where (userid != ? && content_owner = ?) || (contentid in ($placeholders)) order by id desc limit 30";
            $data = $DB->read_prepared($query, "ii" . str_repeat("i", count($follow)), array_merge([$id, $id], $follow));
        } else {
            $query = "select * from notifications where userid != ? && content_owner = ? order by id desc limit 30";
            $data = $DB->read_prepared($query, "ii", [$id, $id]);
        }
        ?>

        <?php if (is_array($data)): ?>
            <?php foreach ($data as $notif_row): ?>
                <?php include("app/partials/single_notification.php"); ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">No notifications yet.</div>
        <?php endif; ?>
    </section>
</div>

</body>
</html>