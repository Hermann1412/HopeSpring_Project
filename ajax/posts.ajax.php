<?php

if (!isset($_SESSION['mybook_userid'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    die;
}

$myid = (int)$_SESSION['mybook_userid'];
$user_class = new User();

header('Content-Type: application/json');

// Get list of users that this user is following
$following = $user_class->get_following($myid, "user");
if (!is_array($following)) {
    $following = [];
}

$following_ids = array_column($following, "userid");
if (empty($following_ids)) {
    echo json_encode(['total_new_posts' => 0, 'posts' => []]);
    die;
}

// Get recent posts from followed users (last 20 posts within 24 hours)
$following_ids = array_values(array_filter(array_map('intval', $following_ids), function ($uid) {
    return $uid > 0;
}));

if (empty($following_ids)) {
    echo json_encode(['total_new_posts' => 0, 'posts' => []]);
    die;
}

$placeholders = implode(',', array_fill(0, count($following_ids), '?'));

// Use Database class to query
$db_class = new Database();
$query = "SELECT postid, userid, post, image, date FROM posts 
          WHERE userid IN ($placeholders) 
          AND parent = 0 
          AND date > DATE_SUB(NOW(), INTERVAL 24 HOUR)
          ORDER BY date DESC 
          LIMIT 15";

$result = $db_class->read_prepared($query, str_repeat('i', count($following_ids)), $following_ids);
$new_posts = [];

if (is_array($result)) {
    foreach ($result as $row) {
        $poster = $user_class->get_user((int)$row['userid']);
        if (is_array($poster)) {
            $new_posts[] = [
                'postid' => (int)$row['postid'],
                'poster_id' => (int)$row['userid'],
                'poster_name' => $poster['first_name'] . ' ' . $poster['last_name'],
                'post_preview' => substr($row['post'], 0, 80),
                'has_image' => !empty($row['image']) ? 1 : 0,
                'date' => $row['date']
            ];
        }
    }
}

echo json_encode([
    'total_new_posts' => count($new_posts),
    'posts' => $new_posts
]);
