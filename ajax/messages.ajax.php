<?php

if (!isset($_SESSION['mybook_userid'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    die;
}

$myid = (int)$_SESSION['mybook_userid'];
$message_class = new Message();
$user_class = new User();

header('Content-Type: application/json');

// Get unread messages (DM)
$conversations = $message_class->get_conversations($myid);
if (!is_array($conversations)) {
    $conversations = [];
}

$unread_messages = [];
foreach ($conversations as $conv) {
    $unread_count = (int)($conv['unread_count'] ?? 0);
    if ($unread_count > 0) {
        $peer_id = (int)$conv['peer_id'];
        $peer = $user_class->get_user($peer_id);
        if (is_array($peer)) {
            $unread_messages[] = [
                'type' => 'direct',
                'peer_id' => $peer_id,
                'peer_name' => $peer['first_name'] . ' ' . $peer['last_name'],
                'unread_count' => $unread_count,
                'last_message' => $conv['message'] ?? '',
                'date' => $conv['date'] ?? ''
            ];
        }
    }
}

// Get group messages
$groups = $message_class->get_groups_for_user($myid);
if (!is_array($groups)) {
    $groups = [];
}

$unread_group_messages = [];
foreach ($groups as $group) {
    $group_id = (int)$group['groupid'];
    // Count unread group messages
    $conn = $message_class->connect();
    if ($conn) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages WHERE group_id = '$group_id' AND seen = 0 AND sender != '$myid'");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $unread_count = (int)$row['cnt'];
            if ($unread_count > 0) {
                $unread_group_messages[] = [
                    'type' => 'group',
                    'group_id' => $group_id,
                    'group_name' => $group['group_name'],
                    'unread_count' => $unread_count
                ];
            }
        }
    }
}

$total_unread = count($unread_messages) + count($unread_group_messages);

echo json_encode([
    'total_unread' => $total_unread,
    'direct_messages' => $unread_messages,
    'group_messages' => $unread_group_messages
]);
