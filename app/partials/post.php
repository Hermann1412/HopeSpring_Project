<?php
// post.php - Post Card Component
// Required variables: $ROW, $ROW_USER, $image_class

$_p_avatar = ($ROW_USER['gender'] == 'Female') ? 'images/user_female.jpg' : 'images/user_male.jpg';
if (!empty($ROW_USER['profile_image']) && file_exists($ROW_USER['profile_image'])) {
    $_p_avatar = $image_class->get_thumb_profile($ROW_USER['profile_image']);
}

$_p_pronoun = ($ROW_USER['gender'] == 'Female') ? 'her' : 'his';

$_p_liked = false;
if (isset($_SESSION['mybook_userid'])) {
    $_pDB  = new Database();
    $_pSQL = "SELECT likes FROM likes WHERE type = ? AND contentid = ? LIMIT 1";
    $_pRes = $_pDB->read_prepared($_pSQL, "si", ["post", (int)$ROW['postid']]);
    if (is_array($_pRes)) {
        $_pLikes = json_decode($_pRes[0]['likes'], true);
        if (is_array($_pLikes) && in_array($_SESSION['mybook_userid'], array_column($_pLikes, 'userid'))) {
            $_p_liked = true;
        }
    }
}

$_p_owns = false;
$_pPost  = new Post();
if ($_pPost->i_own_post($ROW['postid'], $_SESSION['mybook_userid'])) {
    $_p_owns = true;
}
?>

<div class="post-card">

    <!-- Header -->
    <div class="post-header">
        <a href="profile.php?id=<?php echo $ROW_USER['userid']; ?>">
            <img src="<?php echo htmlspecialchars($_p_avatar); ?>" class="post-avatar" alt="avatar">
        </a>
        <div class="post-meta">
            <div class="post-author">
                <a href="profile.php?id=<?php echo $ROW_USER['userid']; ?>">
                    <?php echo htmlspecialchars($ROW_USER['first_name'] . ' ' . $ROW_USER['last_name']); ?>
                </a>
                <?php if ($ROW['is_profile_image']): ?>
                    <span> updated <?php echo $_p_pronoun; ?> profile image</span>
                <?php elseif ($ROW['is_cover_image']): ?>
                    <span> updated <?php echo $_p_pronoun; ?> cover image</span>
                <?php endif; ?>
            </div>
            <div class="post-time"><?php echo Time::get_time($ROW['date']); ?></div>
        </div>
        <?php if ($_p_owns): ?>
        <div class="post-actions-top">
            <a href="edit.php?id=<?php echo $ROW['postid']; ?>">&#9998; Edit</a>
            <a href="delete.php?id=<?php echo $ROW['postid']; ?>"
               onclick="return confirm('Delete this post?');"
               style="color:var(--danger);">&#128465; Delete</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Body text -->
    <?php if (!empty($ROW['post'])): ?>
    <div class="post-body"><?php echo check_tags($ROW['post']); ?></div>
    <?php endif; ?>

    <!-- Image -->
    <?php if (!empty($ROW['image']) && file_exists($ROW['image'])): ?>
    <div class="post-img-wrap">
        <a href="<?php echo $ROW['has_image'] ? 'image_view.php?id='.$ROW['postid'] : 'single_post.php?id='.$ROW['postid']; ?>">
            <img src="<?php echo $image_class->get_thumb_post($ROW['image']); ?>" class="post-img" alt="Post image">
        </a>
    </div>
    <?php endif; ?>

    <!-- Like info row -->
    <?php if ($ROW['likes'] > 0): ?>
    <div class="post-like-info">
        <a id="info_<?php echo $ROW['postid']; ?>" href="likes.php?type=post&id=<?php echo $ROW['postid']; ?>">
        <?php
            if ($_p_liked) {
                $others = $ROW['likes'] - 1;
                echo $others > 0 ? "&#10084; You and $others other" . ($others != 1 ? 's' : '') . " liked this" : "&#10084; You liked this";
            } else {
                echo "&#10084; " . $ROW['likes'] . " " . ($ROW['likes'] == 1 ? 'person' : 'people') . " liked this";
            }
        ?>
        </a>
    </div>
    <?php endif; ?>

    <!-- Footer actions -->
    <div class="post-footer">
        <a onclick="like_post(event)" href="like.php?type=post&id=<?php echo $ROW['postid']; ?>&csrf_token=<?php echo urlencode(csrf_token()); ?>"
           class="<?php echo $_p_liked ? 'liked' : ''; ?>" id="like_btn_<?php echo $ROW['postid']; ?>">
            <?php echo $_p_liked ? '&#10084;' : '&#9825;'; ?> Like<?php echo $ROW['likes'] > 0 ? ' ('.$ROW['likes'].')' : ''; ?>
        </a>
        <a href="single_post.php?id=<?php echo $ROW['postid']; ?>">
            &#128172; Comment<?php echo $ROW['comments'] > 0 ? ' ('.$ROW['comments'].')' : ''; ?>
        </a>
    </div>

</div>

<script>
(function(){
    function ajax_send(data, btn) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200 && xhr.responseText) {
                try {
                    var obj = JSON.parse(xhr.responseText);
                    if (obj.action === 'like_post') {
                        var likeCount = parseInt(obj.likes) || 0;
                        btn.innerHTML = (btn.classList.contains('liked') ? '&#9825; Like' : '&#10084; Like') +
                            (likeCount > 0 ? ' (' + likeCount + ')' : '');
                        btn.classList.toggle('liked');
                        var infoEl = document.getElementById(obj.id);
                        if (infoEl && obj.info !== undefined) infoEl.innerHTML = obj.info;
                    }
                } catch(e) {}
            }
        };
        xhr.open('POST', 'ajax.php', true);
        xhr.send(JSON.stringify(data));
    }

    function like_post(e) {
        e.preventDefault();
        ajax_send({ link: e.currentTarget.href, action: 'like_post' }, e.currentTarget);
    }

    // attach to all like buttons on this post
    var btn = document.getElementById('like_btn_<?php echo $ROW['postid']; ?>');
    if (btn) btn.addEventListener('click', like_post);
})();
</script>
