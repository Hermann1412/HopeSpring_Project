<?php
// get actor (who did the action) and owner (whose content was affected)
$actor = $User->get_user($notif_row['userid']);
$owner = $User->get_user($notif_row['content_owner']);
$id    = (int)$_SESSION['mybook_userid'];

// fallback values if users no longer exist
$actor = is_array($actor) ? $actor : [
    'userid'=>0,'first_name'=>'Someone','last_name'=>'','gender'=>'Male','profile_image'=>''
];
$owner = is_array($owner) ? $owner : [
    'userid'=>0,'first_name'=>'','last_name'=>'','gender'=>'Male','profile_image'=>''
];

// determine notification link
$link = "#"; // default
if ($notif_row['content_type'] == "post") {
    $link = "single_post.php?id=" . urlencode($notif_row['contentid']) . "&notif=" . urlencode($notif_row['id']);
} elseif ($notif_row['content_type'] == "profile") {
    $link = "profile.php?id=" . urlencode($notif_row['userid']) . "&notif=" . urlencode($notif_row['id']);
} elseif ($notif_row['content_type'] == "comment") {
    $link = "single_post.php?id=" . urlencode($notif_row['contentid']) . "&notif=" . urlencode($notif_row['id']);
}

// check if notification was seen
$query = "SELECT * FROM notification_seen 
          WHERE userid = ? 
          AND notification_id = ? 
          LIMIT 1";
$seen = $DB->read_prepared($query, "ii", [$id, (int)$notif_row['id']]);
$is_unseen = !is_array($seen);
?>

<a href="<?php echo htmlspecialchars($link); ?>" style="text-decoration: none; color: inherit;">
<div class="notif-item <?php echo $is_unseen ? 'unseen' : ''; ?>">

<?php
// actor profile image
$image = ($actor['gender'] == "Female") ? "images/user_female.jpg" : "images/user_male.jpg";
if (!empty($actor['profile_image']) && file_exists($actor['profile_image'])) {
    $image = $image_class->get_thumb_profile($actor['profile_image']);
}
echo "<img src='" . htmlspecialchars($image) . "' style='width:40px;height:40px;border-radius:50%;object-fit:cover;' />";

// actor name
echo ($actor['userid'] != $id) ? $actor['first_name'] . " " . $actor['last_name'] : "You ";

// action
switch ($notif_row['activity']) {
    case "like":   echo " liked "; break;
    case "follow": echo " followed "; break;
    case "comment":echo " commented "; break;
    case "tag":    echo " tagged "; break;
    default:       echo " did something on "; break;
}

// owner reference
if ($owner['userid'] != $id && $notif_row['activity'] != "tag") {
    echo $owner['first_name'] . " " . $owner['last_name'] . "'s ";
} elseif ($notif_row['activity'] == "tag") {
    echo " you in a ";
} else {
    echo " your ";
}

// content details (post or profile)
$content_row = $Post->get_one_post($notif_row['contentid']);
if (!is_array($content_row)) {
    $content_row = ['has_image'=>0, 'image'=>'', 'post'=>''];
}

if ($notif_row['content_type'] == "post") {
    if ($content_row['has_image'] && !empty($content_row['image']) && file_exists($content_row['image'])) {
        echo "image";
        $post_image = $image_class->get_thumb_post($content_row['image']);
        echo "<img src='$post_image' style='width:40px;height:40px;border-radius:4px;float:right;' />";
    } else {
        echo "post";
        if (!empty($content_row['post'])) {
                echo "<span style='font-size:11px;color:#888;margin-left:auto;'>'"
               . htmlspecialchars(substr($content_row['post'],0,50)) . "'</span>";
        }
    }
} else {
    echo $notif_row['content_type'];
    if (!empty($content_row['post'])) {
        echo "<span style='font-size:11px;color:#888;margin-left:auto;'>'"
           . htmlspecialchars(substr($content_row['post'],0,50)) . "'</span>";
    }
}

// date
$date = date("jS M Y H:i:s a", strtotime($notif_row['date']));
echo "<span style='display:block;font-size:11px;color:#888;margin-top:4px;'>$date</span>";
?>

</div>
</a>
