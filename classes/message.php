<?php

class Message extends Database
{
    private $table_ready = false;
    private $group_table_ready = false;

    private function ensure_table()
    {
        if ($this->table_ready) {
            return;
        }

        $query = "CREATE TABLE IF NOT EXISTS messages (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            messageid BIGINT UNSIGNED NOT NULL,
            sender BIGINT UNSIGNED NOT NULL,
            receiver BIGINT UNSIGNED NOT NULL,
            group_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            message TEXT NOT NULL,
            message_type VARCHAR(20) NOT NULL DEFAULT 'text',
            file_path VARCHAR(255) NOT NULL DEFAULT '',
            file_name VARCHAR(255) NOT NULL DEFAULT '',
            mime_type VARCHAR(120) NOT NULL DEFAULT '',
            seen TINYINT(1) NOT NULL DEFAULT 0,
            deleted_sender TINYINT(1) NOT NULL DEFAULT 0,
            deleted_receiver TINYINT(1) NOT NULL DEFAULT 0,
            date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY messageid_unique (messageid),
            KEY sender_idx (sender),
            KEY receiver_idx (receiver),
            KEY seen_idx (seen),
            KEY date_idx (date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->save($query);
        $this->ensure_message_columns();
        $this->table_ready = true;
    }

    private function ensure_group_tables()
    {
        if ($this->group_table_ready) {
            return;
        }

        $query_groups = "CREATE TABLE IF NOT EXISTS message_groups (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            groupid BIGINT UNSIGNED NOT NULL,
            group_name VARCHAR(120) NOT NULL,
            group_profile VARCHAR(255) NOT NULL DEFAULT '',
            group_description TEXT,
            created_by BIGINT UNSIGNED NOT NULL,
            date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY groupid_unique (groupid),
            KEY created_by_idx (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $query_members = "CREATE TABLE IF NOT EXISTS message_group_members (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            groupid BIGINT UNSIGNED NOT NULL,
            userid BIGINT UNSIGNED NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'member',
            joined_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY group_user_unique (groupid, userid),
            KEY groupid_idx (groupid),
            KEY userid_idx (userid)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $query_invites = "CREATE TABLE IF NOT EXISTS group_invitations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            inviteid BIGINT UNSIGNED NOT NULL,
            groupid BIGINT UNSIGNED NOT NULL,
            invited_by BIGINT UNSIGNED NOT NULL,
            invited_user BIGINT UNSIGNED NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            message TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            responded_at DATETIME,
            PRIMARY KEY (id),
            UNIQUE KEY invite_unique (groupid, invited_user),
            KEY groupid_idx (groupid),
            KEY invited_user_idx (invited_user),
            KEY status_idx (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->save($query_groups);
        $this->save($query_members);
        $this->save($query_invites);
        $this->ensure_group_columns();
        $this->group_table_ready = true;
    }

    private function ensure_group_columns()
    {
        $conn = $this->get_connection();
        if (!$conn) {
            return;
        }

        $columns = array(
            'group_profile' => "ALTER TABLE message_groups ADD COLUMN group_profile VARCHAR(255) NOT NULL DEFAULT ''",
            'group_description' => "ALTER TABLE message_groups ADD COLUMN group_description TEXT",
            'updated_at' => "ALTER TABLE message_groups ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            'role' => "ALTER TABLE message_group_members ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'member'"
        );

        foreach ($columns as $name => $sql) {
            $check = mysqli_query($conn, "SHOW COLUMNS FROM " . ($name === 'role' ? '`message_group_members`' : '`message_groups`') . " WHERE `Field` = '$name'");
            if ($check && mysqli_num_rows($check) == 0) {
                mysqli_query($conn, $sql);
            }
        }
    }

    private function ensure_message_columns()
    {
        $conn = $this->get_connection();
        if (!$conn) {
            return;
        }

        $columns = array(
            'group_id' => "ALTER TABLE messages ADD COLUMN group_id BIGINT UNSIGNED NOT NULL DEFAULT 0",
            'message_type' => "ALTER TABLE messages ADD COLUMN message_type VARCHAR(20) NOT NULL DEFAULT 'text'",
            'file_path' => "ALTER TABLE messages ADD COLUMN file_path VARCHAR(255) NOT NULL DEFAULT ''",
            'file_name' => "ALTER TABLE messages ADD COLUMN file_name VARCHAR(255) NOT NULL DEFAULT ''",
            'mime_type' => "ALTER TABLE messages ADD COLUMN mime_type VARCHAR(120) NOT NULL DEFAULT ''"
        );

        foreach ($columns as $name => $sql) {
            // Use backticks for identifiers (safer than LIKE with variables)
            $check = mysqli_query($conn, "SHOW COLUMNS FROM `messages` WHERE `Field` = '$name'");
            if ($check && mysqli_num_rows($check) == 0) {
                mysqli_query($conn, $sql);
            }
        }
    }

    private function create_messageid()
    {
        return rand(1000000000, 9999999999) . rand(1000000000, 9999999999);
    }

    public function send_message($sender, $receiver, $message)
    {
        $this->ensure_table();

        $sender = (int)$sender;
        $receiver = (int)$receiver;
        $message = trim($message);

        if ($sender <= 0 || $receiver <= 0 || $sender === $receiver || $message === "") {
            return false;
        }

        $messageid = $this->create_messageid();

        $query = "INSERT INTO messages (messageid, sender, receiver, message, seen, deleted_sender, deleted_receiver)
                  VALUES (?, ?, ?, ?, 0, 0, 0)";

        return $this->save_prepared($query, "iiis", [$messageid, $sender, $receiver, $message]);
    }

    public function send_attachment($sender, $receiver, $message, $file_path, $file_name, $mime_type)
    {
        $this->ensure_table();

        $sender = (int)$sender;
        $receiver = (int)$receiver;
        $message = trim((string)$message);
        $file_path = trim((string)$file_path);
        $file_name = trim((string)$file_name);
        $mime_type = trim((string)$mime_type);

        if ($sender <= 0 || $receiver <= 0 || $sender === $receiver || $file_path === "") {
            return false;
        }

        $messageid = $this->create_messageid();
        $type = (stripos($mime_type, 'image/') === 0) ? 'image' : 'file';

        $query = "INSERT INTO messages (messageid, sender, receiver, message, message_type, file_path, file_name, mime_type, seen, deleted_sender, deleted_receiver)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0)";

        return $this->save_prepared($query, "iiisssss", [$messageid, $sender, $receiver, $message, $type, $file_path, $file_name, $mime_type]);
    }

    public function create_group($creator, $group_name, $member_ids)
    {
        $this->ensure_group_tables();
        $this->ensure_table();

        $creator = (int)$creator;
        $group_name = trim((string)$group_name);
        if ($creator <= 0 || $group_name === "") {
            return false;
        }

        if (!is_array($member_ids)) {
            $member_ids = array();
        }

        $groupid = $this->create_messageid();

        $insert_group = "INSERT INTO message_groups (groupid, group_name, created_by) VALUES (?, ?, ?)";
        if (!$this->save_prepared($insert_group, "isi", [$groupid, $group_name, $creator])) {
            return false;
        }

        $members = array_unique(array_map('intval', $member_ids));
        $members[] = $creator;
        $members = array_unique($members);

        foreach ($members as $uid) {
            if ($uid <= 0) {
                continue;
            }
            $this->save_prepared("INSERT IGNORE INTO message_group_members (groupid, userid) VALUES (?, ?)", "ii", [$groupid, $uid]);
        }

        return $groupid;
    }

    public function get_groups_for_user($userid)
    {
        $this->ensure_group_tables();
        $userid = (int)$userid;

        $query = "SELECT g.groupid, g.group_name, g.group_profile, g.group_description, g.date,
                         (SELECT COUNT(*) FROM message_group_members WHERE groupid = g.groupid) as member_count
                  FROM message_groups g
                  JOIN message_group_members m ON m.groupid = g.groupid
                  WHERE m.userid = ?
                  ORDER BY g.id DESC";

        return $this->read_prepared($query, "i", [$userid]);
    }

    public function send_group_message($sender, $groupid, $message)
    {
        $this->ensure_group_tables();
        $this->ensure_table();

        $sender = (int)$sender;
        $groupid = (int)$groupid;
        $message = trim((string)$message);

        if ($sender <= 0 || $groupid <= 0 || $message === "") {
            return false;
        }

        $check = $this->read_prepared("SELECT id FROM message_group_members WHERE groupid = ? AND userid = ? LIMIT 1", "ii", [$groupid, $sender]);
        if (!is_array($check)) {
            return false;
        }

        $messageid = $this->create_messageid();

        $query = "INSERT INTO messages (messageid, sender, receiver, group_id, message, message_type, seen, deleted_sender, deleted_receiver)
                  VALUES (?, ?, 0, ?, ?, 'text', 0, 0, 0)";

        return $this->save_prepared($query, "iiis", [$messageid, $sender, $groupid, $message]);
    }

    public function send_group_attachment($sender, $groupid, $message, $file_path, $file_name, $mime_type)
    {
        $this->ensure_group_tables();
        $this->ensure_table();

        $sender = (int)$sender;
        $groupid = (int)$groupid;
        $message = trim((string)$message);
        $file_path = trim((string)$file_path);
        $file_name = trim((string)$file_name);
        $mime_type = trim((string)$mime_type);

        if ($sender <= 0 || $groupid <= 0 || $file_path === "") {
            return false;
        }

        $check = $this->read_prepared("SELECT id FROM message_group_members WHERE groupid = ? AND userid = ? LIMIT 1", "ii", [$groupid, $sender]);
        if (!is_array($check)) {
            return false;
        }

        $messageid = $this->create_messageid();
        $type = (stripos($mime_type, 'image/') === 0) ? 'image' : 'file';

        $query = "INSERT INTO messages (messageid, sender, receiver, group_id, message, message_type, file_path, file_name, mime_type, seen, deleted_sender, deleted_receiver)
                  VALUES (?, ?, 0, ?, ?, ?, ?, ?, ?, 0, 0, 0)";

        return $this->save_prepared($query, "iiisssss", [$messageid, $sender, $groupid, $message, $type, $file_path, $file_name, $mime_type]);
    }

    public function get_group_thread($groupid, $limit = 200)
    {
        $this->ensure_table();
        $groupid = (int)$groupid;
        $limit = (int)$limit;
        if ($limit < 1) {
            $limit = 100;
        }

        $query = "SELECT * FROM messages WHERE group_id = ? ORDER BY id ASC LIMIT ?";
        return $this->read_prepared($query, "ii", [$groupid, $limit]);
    }

    public function get_thread($viewer, $peer, $limit = 100)
    {
        $this->ensure_table();

        $viewer = (int)$viewer;
        $peer = (int)$peer;
        $limit = (int)$limit;
        if ($limit < 1) {
            $limit = 50;
        }

        $query = "SELECT * FROM messages
                  WHERE
                    ((sender = ? AND receiver = ? AND deleted_sender = 0)
                    OR
                    (sender = ? AND receiver = ? AND deleted_receiver = 0))
                  ORDER BY id ASC
                  LIMIT ?";

        return $this->read_prepared($query, "iiiii", [$viewer, $peer, $peer, $viewer, $limit]);
    }

    public function mark_seen($viewer, $peer)
    {
        $this->ensure_table();

        $viewer = (int)$viewer;
        $peer = (int)$peer;

        $query = "UPDATE messages
                  SET seen = 1
                  WHERE receiver = ? AND sender = ? AND seen = 0";

        return $this->save_prepared($query, "ii", [$viewer, $peer]);
    }

    public function count_unread($userid)
    {
        $this->ensure_table();

        $userid = (int)$userid;
        $query = "SELECT COUNT(*) AS total
              FROM messages
              WHERE receiver = ? AND seen = 0 AND deleted_receiver = 0 AND group_id = 0";

        $res = $this->read_prepared($query, "i", [$userid]);
        if (is_array($res) && isset($res[0]['total'])) {
            return (int)$res[0]['total'];
        }

        return 0;
    }

    public function get_conversations($userid)
    {
        $this->ensure_table();

        $userid = (int)$userid;

        $query = "SELECT convo.peer_id, convo.last_id, convo.unread_count, m.message, m.date, m.message_type, m.file_name
                  FROM (
                    SELECT
                      CASE WHEN sender = ? THEN receiver ELSE sender END AS peer_id,
                      MAX(id) AS last_id,
                      SUM(CASE WHEN receiver = ? AND seen = 0 AND deleted_receiver = 0 THEN 1 ELSE 0 END) AS unread_count
                    FROM messages
                    WHERE
                      (group_id = 0 AND ((sender = ? AND deleted_sender = 0)
                      OR
                      (receiver = ? AND deleted_receiver = 0)))
                    GROUP BY peer_id
                  ) AS convo
                  JOIN messages m ON m.id = convo.last_id
                  ORDER BY convo.last_id DESC";

        return $this->read_prepared($query, "iiii", [$userid, $userid, $userid, $userid]);
    }

    // ==================== GROUP MANAGEMENT ====================

    /**
     * Get group information
     */
    public function get_group_info($groupid)
    {
        $this->ensure_group_tables();
        $groupid = (int)$groupid;

        $query = "SELECT * FROM message_groups WHERE groupid = ? LIMIT 1";
        $result = $this->read_prepared($query, "i", [$groupid]);

        return (is_array($result) && !empty($result)) ? $result[0] : false;
    }

    /**
     * Get all members of a group
     */
    public function get_group_members($groupid)
    {
        $this->ensure_group_tables();
        $groupid = (int)$groupid;

        $query = "SELECT m.userid, m.role, m.joined_date, u.first_name, u.last_name, u.tag_name
                  FROM message_group_members m
                  JOIN users u ON u.userid = m.userid
                  WHERE m.groupid = ?
                  ORDER BY m.role DESC, m.joined_date ASC";

        return $this->read_prepared($query, "i", [$groupid]);
    }

    /**
     * Update group name
     */
    public function update_group_name($groupid, $group_name, $userid)
    {
        $this->ensure_group_tables();
        
        $groupid = (int)$groupid;
        $userid = (int)$userid;
        $group_name = trim((string)$group_name);

        if ($groupid <= 0 || $userid <= 0 || $group_name === "" || strlen($group_name) > 120) {
            return false;
        }

        // Check if user is admin
        if (!$this->is_group_admin($groupid, $userid)) {
            return false;
        }

        $query = "UPDATE message_groups SET group_name = ? WHERE groupid = ?";
        return $this->save_prepared($query, "si", [$group_name, $groupid]);
    }

    /**
     * Update group profile/picture
     */
    public function update_group_profile($groupid, $profile_path, $userid)
    {
        $this->ensure_group_tables();
        
        $groupid = (int)$groupid;
        $userid = (int)$userid;
        $profile_path = trim((string)$profile_path);

        if ($groupid <= 0 || $userid <= 0 || $profile_path === "") {
            return false;
        }

        // Check if user is admin
        if (!$this->is_group_admin($groupid, $userid)) {
            return false;
        }

        $query = "UPDATE message_groups SET group_profile = ? WHERE groupid = ?";
        return $this->save_prepared($query, "si", [$profile_path, $groupid]);
    }

    /**
     * Update group description
     */
    public function update_group_description($groupid, $description, $userid)
    {
        $this->ensure_group_tables();
        
        $groupid = (int)$groupid;
        $userid = (int)$userid;
        $description = trim((string)$description);

        if ($groupid <= 0 || $userid <= 0) {
            return false;
        }

        // Check if user is admin
        if (!$this->is_group_admin($groupid, $userid)) {
            return false;
        }

        $query = "UPDATE message_groups SET group_description = ? WHERE groupid = ?";
        return $this->save_prepared($query, "si", [$description, $groupid]);
    }

    /**
     * Send group invitation
     */
    public function send_group_invitation($groupid, $invited_user, $invited_by, $message = "")
    {
        $this->ensure_group_tables();

        $groupid = (int)$groupid;
        $invited_user = (int)$invited_user;
        $invited_by = (int)$invited_by;
        $message = trim((string)$message);

        if ($groupid <= 0 || $invited_user <= 0 || $invited_by <= 0 || $invited_user === $invited_by) {
            return false;
        }

        // Check if inviter is group member/admin
        $check = $this->read_prepared(
            "SELECT id FROM message_group_members WHERE groupid = ? AND userid = ?",
            "ii",
            [$groupid, $invited_by]
        );
        if (!is_array($check)) {
            return false; // Inviter is not a group member
        }

        $existing_member = $this->read_prepared(
            "SELECT id FROM message_group_members WHERE groupid = ? AND userid = ?",
            "ii",
            [$groupid, $invited_user]
        );
        if (is_array($existing_member)) {
            return false; // User already in group
        }

        $inviteid = $this->create_messageid();

        $query = "INSERT INTO group_invitations (inviteid, groupid, invited_by, invited_user, message, status)
                  VALUES (?, ?, ?, ?, ?, 'pending')
                  ON DUPLICATE KEY UPDATE status = 'pending', message = ?";

        return $this->save_prepared($query, "iiiiss", [$inviteid, $groupid, $invited_by, $invited_user, $message, $message]);
    }

    /**
     * Get pending invitations for a user
     */
    public function get_pending_invitations($userid)
    {
        $this->ensure_group_tables();
        $userid = (int)$userid;

        $query = "SELECT i.inviteid, i.groupid, i.invited_by, i.message, i.created_at, 
                         g.group_name, g.group_profile, u.first_name, u.last_name
                  FROM group_invitations i
                  JOIN message_groups g ON g.groupid = i.groupid
                  JOIN users u ON u.userid = i.invited_by
                  WHERE i.invited_user = ? AND i.status = 'pending'
                  ORDER BY i.created_at DESC";

        return $this->read_prepared($query, "i", [$userid]);
    }

    /**
     * Accept group invitation
     */
    public function accept_invitation($inviteid, $userid)
    {
        $this->ensure_group_tables();

        $inviteid = (int)$inviteid;
        $userid = (int)$userid;

        if ($inviteid <= 0 || $userid <= 0) {
            return false;
        }

        // Get invitation details
        $query = "SELECT * FROM group_invitations WHERE inviteid = ? AND invited_user = ? LIMIT 1";
        $invite = $this->read_prepared($query, "ii", [$inviteid, $userid]);

        if (!is_array($invite) || empty($invite)) {
            return false;
        }

        $invite = $invite[0];
        $groupid = $invite['groupid'];

        // Add user to group
        $add_member = "INSERT IGNORE INTO message_group_members (groupid, userid, role) VALUES (?, ?, 'member')";
        if (!$this->save_prepared($add_member, "ii", [$groupid, $userid])) {
            return false;
        }

        // Update invitation status
        $update = "UPDATE group_invitations SET status = 'accepted', responded_at = NOW() WHERE inviteid = ?";
        return $this->save_prepared($update, "i", [$inviteid]);
    }

    /**
     * Reject group invitation
     */
    public function reject_invitation($inviteid, $userid)
    {
        $this->ensure_group_tables();

        $inviteid = (int)$inviteid;
        $userid = (int)$userid;

        if ($inviteid <= 0 || $userid <= 0) {
            return false;
        }

        $query = "UPDATE group_invitations SET status = 'rejected', responded_at = NOW() 
                  WHERE inviteid = ? AND invited_user = ?";

        return $this->save_prepared($query, "ii", [$inviteid, $userid]);
    }

    /**
     * Remove member from group
     */
    public function remove_group_member($groupid, $userid, $removed_by)
    {
        $this->ensure_group_tables();

        $groupid = (int)$groupid;
        $userid = (int)$userid;
        $removed_by = (int)$removed_by;

        if ($groupid <= 0 || $userid <= 0 || $removed_by <= 0) {
            return false;
        }

        // Check if remover is admin
        if (!$this->is_group_admin($groupid, $removed_by)) {
            return false;
        }

        // Can't remove the group creator
        $group = $this->get_group_info($groupid);
        if ($group && $group['created_by'] == $userid) {
            return false;
        }

        $query = "DELETE FROM message_group_members WHERE groupid = ? AND userid = ?";
        return $this->save_prepared($query, "ii", [$groupid, $userid]);
    }

    /**
     * Leave group
     */
    public function leave_group($groupid, $userid)
    {
        $this->ensure_group_tables();

        $groupid = (int)$groupid;
        $userid = (int)$userid;

        if ($groupid <= 0 || $userid <= 0) {
            return false;
        }

        // Can't leave if you're the creator
        $group = $this->get_group_info($groupid);
        if ($group && $group['created_by'] == $userid) {
            return false;
        }

        $query = "DELETE FROM message_group_members WHERE groupid = ? AND userid = ?";
        return $this->save_prepared($query, "ii", [$groupid, $userid]);
    }

    /**
     * Promote member to admin
     */
    public function promote_to_admin($groupid, $userid, $promoted_by)
    {
        $this->ensure_group_tables();

        $groupid = (int)$groupid;
        $userid = (int)$userid;
        $promoted_by = (int)$promoted_by;

        if ($groupid <= 0 || $userid <= 0 || $promoted_by <= 0) {
            return false;
        }

        // Check if promoter is admin
        if (!$this->is_group_admin($groupid, $promoted_by)) {
            return false;
        }

        $query = "UPDATE message_group_members SET role = 'admin' WHERE groupid = ? AND userid = ?";
        return $this->save_prepared($query, "ii", [$groupid, $userid]);
    }

    /**
     * Demote admin to member
     */
    public function demote_to_member($groupid, $userid, $demoted_by)
    {
        $this->ensure_group_tables();

        $groupid = (int)$groupid;
        $userid = (int)$userid;
        $demoted_by = (int)$demoted_by;

        if ($groupid <= 0 || $userid <= 0 || $demoted_by <= 0) {
            return false;
        }

        // Check if demoter is admin
        if (!$this->is_group_admin($groupid, $demoted_by)) {
            return false;
        }

        // Can't demote the creator
        $group = $this->get_group_info($groupid);
        if ($group && $group['created_by'] == $userid) {
            return false;
        }

        $query = "UPDATE message_group_members SET role = 'member' WHERE groupid = ? AND userid = ?";
        return $this->save_prepared($query, "ii", [$groupid, $userid]);
    }

    /**
     * Check if user is group admin
     */
    public function is_group_admin($groupid, $userid)
    {
        $this->ensure_group_tables();

        $groupid = (int)$groupid;
        $userid = (int)$userid;

        if ($groupid <= 0 || $userid <= 0) {
            return false;
        }

        // Creator is always admin
        $group = $this->get_group_info($groupid);
        if ($group && $group['created_by'] == $userid) {
            return true;
        }

        // Check if user has admin role
        $query = "SELECT id FROM message_group_members 
                  WHERE groupid = ? AND userid = ? AND role = 'admin' LIMIT 1";
        $result = $this->read_prepared($query, "ii", [$groupid, $userid]);

        return is_array($result) && !empty($result);
    }

    /**
     * Check if user is group member
     */
    public function is_group_member($groupid, $userid)
    {
        $this->ensure_group_tables();

        $groupid = (int)$groupid;
        $userid = (int)$userid;

        if ($groupid <= 0 || $userid <= 0) {
            return false;
        }

        $query = "SELECT id FROM message_group_members WHERE groupid = ? AND userid = ? LIMIT 1";
        $result = $this->read_prepared($query, "ii", [$groupid, $userid]);

        return is_array($result) && !empty($result);
    }

    /**
     * Delete group (only creator can)
     */
    public function delete_group($groupid, $userid)
    {
        $this->ensure_group_tables();

        $groupid = (int)$groupid;
        $userid = (int)$userid;

        if ($groupid <= 0 || $userid <= 0) {
            return false;
        }

        // Only creator can delete
        $group = $this->get_group_info($groupid);
        if (!$group || $group['created_by'] != $userid) {
            return false;
        }

        // Delete messages
        $delete_messages = "DELETE FROM messages WHERE group_id = ?";
        $this->save_prepared($delete_messages, "i", [$groupid]);

        // Delete members
        $delete_members = "DELETE FROM message_group_members WHERE groupid = ?";
        $this->save_prepared($delete_members, "i", [$groupid]);

        // Delete invitations
        $delete_invites = "DELETE FROM group_invitations WHERE groupid = ?";
        $this->save_prepared($delete_invites, "i", [$groupid]);

        // Delete group
        $query = "DELETE FROM message_groups WHERE groupid = ?";
        return $this->save_prepared($query, "i", [$groupid]);
    }

    /**
     * Get group member count
     */
    public function get_group_member_count($groupid)
    {
        $this->ensure_group_tables();
        $groupid = (int)$groupid;

        $query = "SELECT COUNT(*) AS total FROM message_group_members WHERE groupid = ?";
        $result = $this->read_prepared($query, "i", [$groupid]);

        if (is_array($result) && isset($result[0]['total'])) {
            return (int)$result[0]['total'];
        }

        return 0;
    }
}
