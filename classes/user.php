<?php

class User
{
    public function get_data($id)
    {
        $DB = new Database();
        $query = "SELECT * FROM users WHERE userid = ? LIMIT 1";
        $result = $DB->read_prepared($query, "i", [$id]);

        return ($result) ? $result[0] : false;
    }

    public function get_user($id)
    {
        $DB = new Database();
        $query = "SELECT * FROM users WHERE userid = ? LIMIT 1";
        $result = $DB->read_prepared($query, "i", [$id]);

        return ($result) ? $result[0] : false;
    }

    public function get_friends($id)
    {
        $DB = new Database();
        $query = "SELECT * FROM users WHERE userid != ?";
        $result = $DB->read_prepared($query, "i", [$id]);

        return ($result) ? $result : false;
    }

    public function get_following($id, $type)
    {
        $DB = new Database();

        if (is_numeric($id)) {
            $query = "SELECT following FROM likes WHERE type = ? AND contentid = ? LIMIT 1";
            $result = $DB->read_prepared($query, "si", [$type, $id]);

            if (is_array($result) && !empty($result)) {
                $following = json_decode($result[0]['following'], true);
                if (!is_array($following)) {
                    $following = [];
                }
                return $following;
            }
        }

        return false;
    }

    public function get_followers($id, $type)
    {
        $DB = new Database();
        $id = (int)$id;

        if ($id <= 0) {
            return false;
        }

        $query = "SELECT contentid, following FROM likes WHERE type = ?";
        $result = $DB->read_prepared($query, "s", [$type]);

        if (!is_array($result) || empty($result)) {
            return false;
        }

        $followers = array();
        foreach ($result as $row) {
            $contentid = isset($row['contentid']) ? (int)$row['contentid'] : 0;
            if ($contentid <= 0 || $contentid === $id) {
                continue;
            }

            $following = json_decode($row['following'], true);
            if (!is_array($following)) {
                continue;
            }

            $follow_ids = array_column($following, 'userid');
            if (in_array($id, $follow_ids)) {
                $followers[] = array(
                    'userid' => $contentid,
                    'date' => date('Y-m-d H:i:s')
                );
            }
        }

        return !empty($followers) ? $followers : false;
    }

    public function get_friend_suggestions($id, $limit = 6)
    {
        $DB = new Database();

        $id = (int)$id;
        $limit = (int)$limit;

        if ($id <= 0) {
            return false;
        }

        if ($limit <= 0) {
            $limit = 6;
        }

        $exclude_ids = array($id);
        $following = $this->get_following($id, "user");

        if (is_array($following)) {
            foreach ($following as $row) {
                if (!isset($row['userid'])) {
                    continue;
                }
                $followed_userid = (int)$row['userid'];
                if ($followed_userid > 0) {
                    $exclude_ids[] = $followed_userid;
                }
            }
        }

        $exclude_ids = array_values(array_unique($exclude_ids));
        $placeholders = implode(',', array_fill(0, count($exclude_ids), '?'));
        $types = str_repeat('i', count($exclude_ids)) . 'i';
        $params = array_merge($exclude_ids, array($limit));

        $query = "SELECT userid, first_name, last_name, gender, profile_image, tag_name, online
                  FROM users
                  WHERE userid NOT IN ($placeholders)
                  ORDER BY userid DESC
                  LIMIT ?";

        $result = $DB->read_prepared($query, $types, $params);
        return (is_array($result) && !empty($result)) ? $result : false;
    }

    public function follow_user($id, $type, $mybook_userid)
    {
        if ($id == $mybook_userid && $type == 'user') {
            return;
        }

        $DB = new Database();

        // Get existing likes/following
        $query = "SELECT following FROM likes WHERE type = ? AND contentid = ? LIMIT 1";
        $result = $DB->read_prepared($query, "si", [$type, $mybook_userid]);

        if (is_array($result) && !empty($result)) {
            $likes = json_decode($result[0]['following'], true);

            // Ensure $likes is always an array
            if (!is_array($likes)) {
                $likes = [];
            }

            $user_ids = array_column($likes, "userid");

            if (!in_array($id, $user_ids)) {
                // Add new follower
                $arr = [
                    "userid" => $id,
                    "date" => date("Y-m-d H:i:s")
                ];

                $likes[] = $arr;
                $likes_string = json_encode($likes);
                $query = "UPDATE likes SET following = ? WHERE type = ? AND contentid = ? LIMIT 1";
                $DB->save_prepared($query, "ssi", [$likes_string, $type, $mybook_userid]);

                $single_post = $this->get_user($id);
                add_notification($_SESSION['mybook_userid'], "follow", $single_post);
            } else {
                // Remove follower (unfollow)
                $key = array_search($id, $user_ids);
                unset($likes[$key]);

                // Reindex array to avoid gaps in numeric keys
                $likes = array_values($likes);

                $likes_string = json_encode($likes);
                $query = "UPDATE likes SET following = ? WHERE type = ? AND contentid = ? LIMIT 1";
                $DB->save_prepared($query, "ssi", [$likes_string, $type, $mybook_userid]);
            }
        } else {
            // No previous likes/following, create new
            $arr = [
                "userid" => $id,
                "date" => date("Y-m-d H:i:s")
            ];

            $following = json_encode([$arr]);
            $query = "INSERT INTO likes (type, contentid, following) VALUES (?, ?, ?)";
            $DB->save_prepared($query, "sis", [$type, $mybook_userid, $following]);

            $single_post = $this->get_user($id);
            add_notification($_SESSION['mybook_userid'], "follow", $single_post);
        }
    }
}
