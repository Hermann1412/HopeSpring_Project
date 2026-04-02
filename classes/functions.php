<?php
//let users move between pages when there are many psosts
function pagination_link(){
	
	$page_number = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  	$page_number = ($page_number < 1) ? 1 : $page_number;

	$arr['next_page'] = "";
	$arr['prev_page'] = "";

	//get current url
	$url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
	$url .= "?";

	$next_page_link = $url;
	$prev_page_link = $url;
	$page_found = false;

	$num = 0;
	foreach ($_GET as $key => $value) {
		# code...
		$num++;
		
		if($num == 1){
			if($key == "page"){
				
				$next_page_link .= $key ."=" . ($page_number + 1);
				$prev_page_link .= $key ."=" . ($page_number - 1);
				$page_found = true;
			}else{
				$next_page_link .= $key ."=" . $value;
				$prev_page_link .= $key ."=" . $value;
			}

		}else{
			if($key == "page"){
				
				$next_page_link .= "&" . $key ."=" . ($page_number + 1);
				$prev_page_link .= "&" . $key ."=" . ($page_number - 1);
				$page_found = true;

			}else{
				$next_page_link .= "&" . $key ."=" . $value;
				$prev_page_link .= "&" . $key ."=" . $value;
			}
		}
		
	}

	$arr['next_page'] = $next_page_link;
	$arr['prev_page'] = $prev_page_link;

	if(!$page_found){

		$arr['next_page'] = $next_page_link . "&page=2";
		$arr['prev_page'] = $prev_page_link . "&page=1";
	}
	
	return $arr;
}
// check if the comment or the post belong to the logged in user
function i_own_content($row){

	$myid = $_SESSION['mybook_userid'];
	//profiles
	if(isset($row['gender']) && $myid == $row['userid']){

		return true;
	}

	//comments and posts
	if(isset($row['postid'])){

		if($myid == $row['userid']){
			return true;
		}else{

			$Post = new Post();
			$one_post = $Post->get_one_post($row['parent']);

			if($myid == $one_post['userid']){
				return true;
			}

		}
	}
 
	return false;
}
// allow tagging other users with the @ sympol 
function tag($postid,$new_post_text = "")
{

	$DB = new Database();
	$sql = "select * from posts where postid = ? limit 1";
	$mypost = $DB->read_prepared($sql, "i", [$postid]);

	if(is_array($mypost)){
		$mypost = $mypost[0];

		if($new_post_text != ""){
			$old_post = $mypost;
			$mypost['post'] = $new_post_text;
		}

		$tags = get_tags($mypost['post']);
		foreach ($tags as $tag) {
			# code...
			$sql = "select * from users where tag_name = ? limit 1";
			$tagged_user = $DB->read_prepared($sql, "s", [$tag]);
			if(is_array($tagged_user)){

				$tagged_user = $tagged_user[0];

				if($new_post_text != ""){
					$old_tags = get_tags($old_post['post']);
					if(!in_array($tagged_user['tag_name'], $old_tags)){
						add_notification($_SESSION['mybook_userid'],"tag",$mypost,$tagged_user['userid']);
					}
				}else{
					
					//tag
					add_notification($_SESSION['mybook_userid'],"tag",$mypost,$tagged_user['userid']);
 				}

			}
		}
	}
}
//creats a notification when someone like, comments and tag other users
function add_notification($userid,$activity,$row,$tagged_user = '')
{

	$row = (object)$row;
	$userid = esc($userid);
	$activity = esc($activity);
	$content_owner = $row->userid;

		if($tagged_user != ""){
			$content_owner = $tagged_user;
		}

	$date = date("Y-m-d H:i:s");
	$contentid = 0;
	$content_type = "";

	if(isset($row->postid)){
		$contentid = $row->postid;
		$content_type = "post";

		if($row->parent > 0){
			$content_type = "comment";
		}
	}
	
	if(isset($row->gender)){
		$content_type = "profile";
		$contentid = $row->userid;
	}

	$query = "insert into notifications (userid,activity,content_owner,date,contentid,content_type) 
	values (?,?,?,?,?,?)";
	$DB = new Database();
	$DB->save_prepared($query, "ississ", [$userid, $activity, $content_owner, $date, $contentid, $content_type]);

}
// save posts or profiles that a uesr follows 
function content_i_follow($userid,$row)
{

	$row = (object)$row;

	$userid = esc($userid);
 	$date = date("Y-m-d H:i:s");
	$contentid = 0;
	$content_type = "";

	if(isset($row->postid)){
		$contentid = $row->postid;
		$content_type = "post";

		if($row->parent > 0){
			$content_type = "comment";
		}
	}
	
	if(isset($row->gender)){
		$content_type = "profile";
	}

	$query = "insert into content_i_follow (userid,date,contentid,content_type) 
	values (?,?,?,?)";
	$DB = new Database();
	$DB->save_prepared($query, "isis", [$userid, $date, $contentid, $content_type]);
}

function esc($value)
{

	return addslashes($value);
}

function csrf_token()
{
	if (empty($_SESSION['csrf_token'])) {
		try {
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		} catch (Exception $e) {
			$_SESSION['csrf_token'] = sha1(uniqid((string)mt_rand(), true));
		}
	}

	return $_SESSION['csrf_token'];
}

function csrf_input()
{
	return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_validate_request($token = null)
{
	if ($token === null) {
		$token = $_POST['csrf_token'] ?? ($_GET['csrf_token'] ?? '');
	}

	$session_token = $_SESSION['csrf_token'] ?? '';

	if (!is_string($token) || $token === '' || !is_string($session_token) || $session_token === '') {
		return false;
	}

	return hash_equals($session_token, $token);
}
// help to track new notification 
function notification_seen($id)
{

	$notification_id = addslashes($id);
	$userid = $_SESSION['mybook_userid'];
	$DB = new Database();

	$query = "select * from notification_seen where userid = ? && notification_id = ? limit 1";
	$check = $DB->read_prepared($query, "ii", [$userid, $notification_id]);

	if(!is_array($check)){

		$query = "insert into notification_seen (userid,notification_id) 
		values (?,?)";
		
		$DB->save_prepared($query, "ii", [$userid, $notification_id]);
	}
}

function check_notifications()
{
	$number = 0;

	$userid = $_SESSION['mybook_userid'];
	$DB = new Database();

	$follow = array();

	//check content i follow
	$sql = "select * from content_i_follow where disabled = 0 && userid = ? limit 100";
	$i_follow = $DB->read_prepared($sql, "i", [$userid]);
	if(is_array($i_follow)){
		$follow = array_column($i_follow, "contentid");
	}

	if(count($follow) > 0){

		$placeholders = implode(",", array_fill(0, count($follow), "?"));
		$types = str_repeat("i", count($follow));
		$query = "select * from notifications where (userid != ? && content_owner = ?) || (contentid in ($placeholders)) order by id desc limit 30";
		$data = $DB->read_prepared($query, "ii" . $types, array_merge([$userid, $userid], $follow));
	}else{

		$query = "select * from notifications where userid != ? && content_owner = ? order by id desc limit 30";
		$data = $DB->read_prepared($query, "ii", [$userid, $userid]);
	}
 							
 	if(is_array($data)){

 		foreach ($data as $row) {
 			# code...

	 		$query = "select * from notification_seen where userid = ? && notification_id = ? limit 1";
			$check = $DB->read_prepared($query, "ii", [$userid, $row['id']]);

			if(!is_array($check)){

				$number++;
			}
		}
	}

	return $number;

}
//change text tags into profile links
function check_tags($text)
{
	$str = "";
	$words = explode(" ", $text);
	if(is_array($words) && count($words)>0)
	{
		$DB = new Database();
		foreach ($words as $word) {

			if(preg_match("/@[a-zA-Z_0-9\Q,.\E]+/", $word)){
				
				$word = trim($word,'@');
				$word = trim($word,',');
				$tag_name = esc(trim($word,'.'));

				$query = "select * from users where tag_name = ? limit 1";
				$user_row = $DB->read_prepared($query, "s", [$tag_name]);

				if(is_array($user_row)){
					$user_row = $user_row[0];
					$str .= "<a href='profile.php?id=$user_row[userid]'>@" . $word . "</a> ";
				}else{

					$str .= htmlspecialchars($word) . " ";
				}
 			
			}else{
				$str .= htmlspecialchars($word) . " ";
			}
		}

	}

	if($str != ""){
		return $str;
	}

	return htmlspecialchars($text);
}

function get_tags($text)
{
	$tags = array();
	$words = explode(" ", $text);
	if(is_array($words) && count($words)>0)
	{
		$DB = new Database();
		foreach ($words as $word) {

			if(preg_match("/@[a-zA-Z_0-9\Q,.\E]+/", $word)){
				
				$word = trim($word,'@');
				$word = trim($word,',');
				$tag_name = esc(trim($word,'.'));

				$query = "select * from users where tag_name = ? limit 1";
				$user_row = $DB->read_prepared($query, "s", [$tag_name]);

				if(is_array($user_row)){
					
					$tags[] = $word;
				}
 			
			}
		}

	}
 
	return $tags;
}

if(isset($_SESSION['mybook_userid'])){
 set_online($_SESSION['mybook_userid']);
}

function set_online($id){
	
	if(!is_numeric($id))
	{
		return;
	}

	$online = time();
	$query = "update users set online = ? where userid = ? limit 1";
	$DB = new Database();
	$DB->save_prepared($query, "ii", [$online, $id]);
}