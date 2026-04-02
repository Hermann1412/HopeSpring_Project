<section class="card">
    <h2 style="margin:0 0 14px 0;">Following</h2>
    <?php

    $image_class = new Image();
    $post_class  = new Post();
    $user_class  = new User();

    $following = $user_class->get_following($user_data['userid'], "user");

    if (is_array($following)) {
        foreach ($following as $follower) {
            $FRIEND_ROW = $user_class->get_user($follower['userid']);
            if (is_array($FRIEND_ROW)) {
                include("user.php");
            }
        }
    } else {
        echo '<div class="empty-state">This user is not following anyone yet.</div>';
    }

    ?>
</section>