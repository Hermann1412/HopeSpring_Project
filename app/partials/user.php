<?php
// user.php - User Card Component
// Required: $FRIEND_ROW, $image_class

$_u_img = ($FRIEND_ROW['gender'] == 'Female') ? 'images/user_female.jpg' : 'images/user_male.jpg';
if (!empty($FRIEND_ROW['profile_image']) && file_exists($FRIEND_ROW['profile_image'])) {
    $_u_img = $image_class->get_thumb_profile($FRIEND_ROW['profile_image']);
}

$_u_status = 'Last seen: Unknown';
if (!empty($FRIEND_ROW['online']) && $FRIEND_ROW['online'] > 0) {
    $current_time = time();
    $threshold    = 60 * 2;
    if (($current_time - $FRIEND_ROW['online']) < $threshold) {
        $_u_status = '<span style="color:var(--success);font-weight:600;">&#9679; Online</span>';
    } else {
        $_u_status = 'Last seen: ' . Time::get_time(date('Y-m-d H:i:s', $FRIEND_ROW['online']));
    }
}
?>
<a href="profile.php?id=<?php echo $FRIEND_ROW['userid']; ?>" class="user-card" style="display:flex;">
    <img src="<?php echo htmlspecialchars($_u_img); ?>" class="uc-avatar" alt="">
    <div class="uc-info">
        <div class="uc-name"><?php echo htmlspecialchars($FRIEND_ROW['first_name'] . ' ' . $FRIEND_ROW['last_name']); ?></div>
        <div class="uc-tag">@<?php echo htmlspecialchars($FRIEND_ROW['tag_name']); ?></div>
        <div class="uc-status"><?php echo $_u_status; ?></div>
    </div>
</a>
