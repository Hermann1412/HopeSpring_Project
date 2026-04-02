<section class="card">
    <h2 style="margin:0 0 14px 0;">Followers</h2>
    <?php

    $image_class = new Image();
    $post_class  = new Post();
    $user_class  = new User();

    $followers = $post_class->get_likes($user_data['userid'], "user");

    if (is_array($followers)) {
        foreach ($followers as $follower) {
            $FRIEND_ROW = $user_class->get_user($follower['userid']);
            if (is_array($FRIEND_ROW)) {
                include("user.php");
            }
        }
    } else {
        echo '<div class="empty-state">No followers found.</div>';
    }

    ?>
</section>