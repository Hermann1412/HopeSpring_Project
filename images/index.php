<?php

class User
{
    public function get_data($id)
    {
        $query = "SELECT * FROM users WHERE userid = ? LIMIT 1";
        $DB = new Database();
        $result = $DB->read_prepared($query, "i", [(int)$id]);

        return ($result) ? $result[0] : false;
    }

    public function get_user($id)
    {
        $query = "SELECT * FROM users WHERE userid = ? LIMIT 1";
        $DB = new Database();
        $result = $DB->read_prepared($query, "i", [(int)$id]);

        return ($result) ? $result[0] : false;
    }

    public function get_friends($id)
    {
        $query = "SELECT * FROM users WHERE userid != ?";
        $DB = new Database();
        $result = $DB->read_prepared($query, "i", [(int)$id]);

        return ($result) ? $result : false;
    }

    public function get_following($id, $type)
    {
        $DB = new Database();
        $type = (string)$type;
        $id = (int)$id;

        if ($id > 0) {
            $sql = "SELECT following FROM likes WHERE type = ? AND contentid = ? LIMIT 1";
            $result = $DB->read_prepared($sql, "si", [$type, $id]);

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

    public function follow_user($id, $type, $mybook_userid)
    {
        if ($id == $mybook_userid && $type == 'user') {
            return;
        }

        $DB = new Database();
        $type = (string)$type;
        $id = (int)$id;
        $mybook_userid = (int)$mybook_userid;

        // Get existing likes/following
        $sql = "SELECT following FROM likes WHERE type = ? AND contentid = ? LIMIT 1";
        $result = $DB->read_prepared($sql, "si", [$type, $mybook_userid]);

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
                $sql = "UPDATE likes SET following = ? WHERE type = ? AND contentid = ? LIMIT 1";
                $DB->save_prepared($sql, "ssi", [$likes_string, $type, $mybook_userid]);

                $single_post = $this->get_user($id);
                add_notification($_SESSION['mybook_userid'], "follow", $single_post);
            } else {
                // Remove follower (unfollow)
                $key = array_search($id, $user_ids);
                unset($likes[$key]);

                // Reindex array to avoid gaps in numeric keys
                $likes = array_values($likes);

                $likes_string = json_encode($likes);
                $sql = "UPDATE likes SET following = ? WHERE type = ? AND contentid = ? LIMIT 1";
                $DB->save_prepared($sql, "ssi", [$likes_string, $type, $mybook_userid]);
            }
        } else {
            // No previous likes/following, create new
            $arr = [
                "userid" => $id,
                "date" => date("Y-m-d H:i:s")
            ];

            $following = json_encode([$arr]);
            $sql = "INSERT INTO likes (type, contentid, following) VALUES (?, ?, ?)";
            $DB->save_prepared($sql, "sis", [$type, $mybook_userid, $following]);

            $single_post = $this->get_user($id);
            add_notification($_SESSION['mybook_userid'], "follow", $single_post);
        }
    }
}
