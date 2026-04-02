<?php

class Post
{
    private $error = "";

    /**
     * Create a post. $files should be the $_FILES array (or similar).
     */
    public function create_post($userid, $data, $files)
    {
        // If $userid is missing, try session (defensive)
        if (empty($userid) && isset($_SESSION['mybook_userid'])) {
            $userid = $_SESSION['mybook_userid'];
        }

        $has_upload_array = is_array($files) && !empty($files['file']['name']);
        $has_image_path = is_string($files) && $files !== "";

        if (!empty($data['post']) || $has_upload_array || $has_image_path || isset($data['is_profile_image']) || isset($data['is_cover_image'])) {

            $myimage = "";
            $has_image = 0;
            $is_cover_image = 0;
            $is_profile_image = 0;

            // If it's a profile/cover image flag, treat upload similarly to normal file upload.
            if (isset($data['is_profile_image']) || isset($data['is_cover_image'])) {

                if (isset($data['is_cover_image'])) {
                    $is_cover_image = 1;
                }

                if (isset($data['is_profile_image'])) {
                    $is_profile_image = 1;
                }

                // If a file path is already provided by caller, reuse it.
                if ($has_image_path) {
                    $myimage = $files;
                    $has_image = 1;
                } elseif ($has_upload_array) {
                    $upload = $this->process_upload($userid, $files);
                    if ($upload['success']) {
                        $myimage = $upload['path'];
                        $has_image = 1;
                    } else {
                        $this->error .= $upload['error'];
                    }
                } else {
                    // If no file provided for profile/cover, that's fine — some flows may expect that.
                }

            } else {

                if ($has_upload_array) {
                    $upload = $this->process_upload($userid, $files);
                    if ($upload['success']) {
                        $myimage = $upload['path'];
                        $has_image = 1;
                    } else {
                        $this->error .= $upload['error'];
                    }
                }
            }

            $post = "";
            if (isset($data['post'])) {
                $post = addslashes($data['post']);
            }

            // add tagged users
            $tags = array();
            $tags = get_tags($post);
            $tags = json_encode($tags);

            if ($this->error == "") {

                $postid = $this->create_postid();
                $parent = 0;
                $DB = new Database();

                if (isset($data['parent']) && is_numeric($data['parent'])) {

                    $parent = $data['parent'];
                    $mypost = $this->get_one_post($data['parent']);

                    if (is_array($mypost) && $mypost['userid'] != $userid) {

                        // follow this item
                        content_i_follow($userid, $mypost);

                        // add notification
                        add_notification($_SESSION['mybook_userid'], "comment", $mypost);
                    }

                    $sql = "update posts set comments = comments + 1 where postid = ? limit 1";
                    $DB->save_prepared($sql, "i", [$parent]);
                }

                $query = "insert into posts (userid,postid,post,image,has_image,is_profile_image,is_cover_image,parent,tags) 
                          values (?,?,?,?,?,?,?,?,?)";
                $DB->save_prepared($query, "isssiiiii", [$userid, $postid, $post, $myimage, $has_image, $is_profile_image, $is_cover_image, $parent, $tags]);

                // notify those that were tagged
                tag($postid);
            }
        } else {
            $this->error .= "Please type something to post!<br>";
        }

        return $this->error;
    }

    /**
     * Edit a post.
     * Accepts ($data, $files) OR ($userid, $data, $files).
     * Backwards-compatible: if first arg is array it assumes old signature.
     */
    public function edit_post($arg1, $arg2 = null, $arg3 = null)
    {
        // Normalize arguments to ($userid, $data, $files)
        if (is_array($arg1) && $arg2 === null && $arg3 === null) {
            // old signature: edit_post($data, $files)
            $userid = isset($_SESSION['mybook_userid']) ? $_SESSION['mybook_userid'] : null;
            $data = $arg1;
            $files = $arg2 ?? [];
        } elseif (!is_array($arg1) && is_array($arg2)) {
            // new signature: edit_post($userid, $data, $files)
            $userid = $arg1;
            $data = $arg2;
            $files = $arg3 ?? [];
        } else {
            // fallback
            $userid = isset($_SESSION['mybook_userid']) ? $_SESSION['mybook_userid'] : null;
            $data = is_array($arg1) ? $arg1 : [];
            $files = is_array($arg2) ? $arg2 : [];
        }

        if (!empty($data['post']) || (!empty($files['file']['name']) ?? false)) {

            $myimage = "";
            $has_image = 0;

            if (!empty($files['file']['name'])) {
                $upload = $this->process_upload($userid, $files);
                if ($upload['success']) {
                    $myimage = $upload['path'];
                    $has_image = 1;
                } else {
                    $this->error .= $upload['error'];
                }
            }

            $post = "";
            if (isset($data['post'])) {
                $post = $data['post'];
            }

            $postid = $data['postid'];

            $DB = new Database();
            
            if ($has_image) {
                $query = "update posts set post = ?, image = ? where postid = ? limit 1";
                $DB->save_prepared($query, "ssi", [$post, $myimage, $postid]);
            } else {
                $query = "update posts set post = ? where postid = ? limit 1";
                $DB->save_prepared($query, "si", [$post, $postid]);
            }

            // notify those that were tagged
            tag($postid, $post);

        } else {
            $this->error .= "Please type something to post!<br>";
        }

        return $this->error;
    }

    /**
     * Process an uploaded image file.
     * Returns ['success'=>bool, 'path'=>string, 'error'=>string]
     */
    private function process_upload($userid, $files)
    {
        $response = ['success' => false, 'path' => '', 'error' => ''];

        if (empty($userid)) {
            $response['error'] = "Missing user id for upload.<br>";
            return $response;
        }

        if (empty($files['file']['name'])) {
            $response['error'] = "No file uploaded.<br>";
            return $response;
        }

        $folder = "uploads/" . $userid . "/";

        // create folder
        if (!file_exists($folder)) {
            if (!mkdir($folder, 0777, true) && !is_dir($folder)) {
                $response['error'] = "Failed to create upload folder.<br>";
                return $response;
            }
            file_put_contents($folder . "index.php", "");
        }

        // Allowed MIME types (you can add more later)
        $allowed = ["image/jpeg", "image/pjpeg"];

        if (!in_array($files['file']['type'], $allowed)) {
            $response['error'] = "The selected image is not a valid type. only jpegs allowed!<br>";
            return $response;
        }

        $image_class = new Image();

        $target_path = $folder . $image_class->generate_filename(15) . ".jpg";

        if (!move_uploaded_file($files['file']['tmp_name'], $target_path)) {
            $response['error'] = "Failed to move uploaded file.<br>";
            return $response;
        }

        // try resizing (Image class should handle errors internally)
        try {
            $image_class->resize_image($target_path, $target_path, 1500, 1500);
        } catch (Throwable $e) {
            // If resizing fails, we still return success but include a small note — adapt as needed.
            // Optionally delete the file if you prefer strictness.
            $response['error'] .= "Image resize failed: " . $e->getMessage() . "<br>";
        }

        $response['success'] = true;
        $response['path'] = $target_path;
        return $response;
    }

    public function get_posts($id)
    {
        $page_number = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page_number = ($page_number < 1) ? 1 : $page_number;

        $limit = 10;
        $offset = ($page_number - 1) * $limit;

        $query = "select * from posts where parent = 0 and userid = ? order by id desc limit ? offset ?";

        $DB = new Database();
        $result = $DB->read_prepared($query, "iii", [$id, $limit, $offset]);

        // return array or empty array
        return is_array($result) ? $result : [];
    }

    public function get_comments($id)
    {
        $page_number = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page_number = ($page_number < 1) ? 1 : $page_number;

        $limit = 10;
        $offset = ($page_number - 1) * $limit;

        $query = "select * from posts where parent = ? order by id asc limit ? offset ?";

        $DB = new Database();
        $result = $DB->read_prepared($query, "iii", [$id, $limit, $offset]);

        return is_array($result) ? $result : [];
    }

    public function get_one_post($postid)
    {
        if (!is_numeric($postid)) {
            return false;
        }

        $query = "select * from posts where postid = ? limit 1";

        $DB = new Database();
        $result = $DB->read_prepared($query, "i", [$postid]);

        if (is_array($result) && isset($result[0])) {
            return $result[0];
        } else {
            return false;
        }
    }

    public function delete_post($postid)
    {
        if (!is_numeric($postid)) {
            return false;
        }

        $Post = new Post();
        $one_post = $Post->get_one_post($postid);

        $DB = new Database();
        $sql = "select parent from posts where postid = ? limit 1";
        $result = $DB->read_prepared($sql, "i", [$postid]);

        if (is_array($result) && isset($result[0])) {
            if ($result[0]['parent'] > 0) {
                $parent = $result[0]['parent'];
                $sql = "update posts set comments = comments - 1 where postid = ? limit 1";
                $DB->save_prepared($sql, "i", [$parent]);
            }
        }

        $query = "delete from posts where postid = ? limit 1";
        $DB->save_prepared($query, "i", [$postid]);

        // delete any images and thumbnails (check if $one_post exists)
        if (is_array($one_post) && isset($one_post['image']) && $one_post['image'] != "") {
            if (file_exists($one_post['image'])) {
                unlink($one_post['image']);
            }

            if (file_exists($one_post['image'] . "_post_thumb")) {
                unlink($one_post['image'] . "_post_thumb");
            }

            if (file_exists($one_post['image'] . "_cover_thumb")) {
                unlink($one_post['image'] . "_cover_thumb");
            }
        }

        // delete all comments
        $query = "delete from posts where parent = ?";
        $DB->save_prepared($query, "i", [$postid]);
    }

    public function i_own_post($postid, $mybook_userid)
    {
        if (!is_numeric($postid)) {
            return false;
        }

        $query = "select * from posts where postid = ? limit 1";

        $DB = new Database();
        $result = $DB->read_prepared($query, "i", [$postid]);

        if (is_array($result) && isset($result[0])) {
            if ($result[0]['userid'] == $mybook_userid) {
                return true;
            }
        }

        return false;
    }

    // FIXED get_likes: always return array
    public function get_likes($id, $type)
    {
        $DB = new Database();

        if (is_numeric($id)) {
            $sql = "select likes from likes where type = ? && contentid = ? limit 1";
            $result = $DB->read_prepared($sql, "si", [$type, $id]);

            if (is_array($result) && isset($result[0])) {
                $likes = json_decode($result[0]['likes'], true);
                return is_array($likes) ? $likes : [];
            }
        }
        return [];
    }

    // FIXED like_post: no crash when likes is null
    public function like_post($id, $type, $mybook_userid)
    {
        $DB = new Database();

        $sql = "select likes from likes where type = ? && contentid = ? limit 1";
        $result = $DB->read_prepared($sql, "si", [$type, $id]);

        if (is_array($result) && isset($result[0])) {

            $likes = json_decode($result[0]['likes'], true);
            if (!is_array($likes)) {
                $likes = [];
            }

            $user_ids = array_column($likes, "userid");

            if (!in_array($mybook_userid, $user_ids)) {

                $arr = [
                    "userid" => $mybook_userid,
                    "date" => date("Y-m-d H:i:s")
                ];

                $likes[] = $arr;

                $likes_string = json_encode($likes);
                $sql = "update likes set likes = ? where type = ? && contentid = ? limit 1";
                $DB->save_prepared($sql, "ssi", [$likes_string, $type, $id]);

                $sql = "update {$type}s set likes = likes + 1 where {$type}id = ? limit 1";
                $DB->save_prepared($sql, "i", [$id]);

                if ($type != "user") {
                    $post = new Post();
                    $single_post = $post->get_one_post($id);
                    add_notification($_SESSION['mybook_userid'], "like", $single_post);
                }
            } else {
                $key = array_search($mybook_userid, $user_ids);
                if ($key !== false) {
                    unset($likes[$key]);
                }

                // reindex array so JSON is clean
                $likes = array_values($likes);

                $likes_string = json_encode($likes);
                $sql = "update likes set likes = ? where type = ? && contentid = ? limit 1";
                $DB->save_prepared($sql, "ssi", [$likes_string, $type, $id]);

                $sql = "update {$type}s set likes = likes - 1 where {$type}id = ? limit 1";
                $DB->save_prepared($sql, "i", [$id]);
            }
        } else {
            // no likes row yet: create
            $arr = [
                "userid" => $mybook_userid,
                "date" => date("Y-m-d H:i:s")
            ];
            $arr2 = [$arr];

            $likes = json_encode($arr2);
            $sql = "insert into likes (type,contentid,likes) values (?,?,?)";
            $DB->save_prepared($sql, "ssi", [$type, $id, $likes]);

            $sql = "update {$type}s set likes = likes + 1 where {$type}id = ? limit 1";
            $DB->save_prepared($sql, "i", [$id]);

            if ($type != "user") {
                $post = new Post();
                $single_post = $post->get_one_post($id);
                add_notification($_SESSION['mybook_userid'], "like", $single_post);
            }
        }
    }

    private function create_postid()
    {
        $length = rand(4, 19);
        $number = "";
        for ($i = 0; $i < $length; $i++) {
            $new_rand = rand(0, 9);
            $number = $number . $new_rand;
        }
        return $number;
    }
}
