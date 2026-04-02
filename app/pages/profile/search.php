<?php 

	include("classes/autoload.php");

	$login = new Login();
	$user_data = $login->check_login($_SESSION['mybook_userid']);
	
	if(isset($_GET['find'])){

        $find = trim((string)$_GET['find']);
        $find_like = "%" . $find . "%";

        $sql = "select * from users where first_name like ? || last_name like ? limit 30";
		$DB = new Database();
        $results = $DB->read_prepared($sql, "ss", [$find_like, $find_like]);


	}
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search | HopeSpring</title>
</head>
<body>

<?php include("app/partials/header.php"); ?>

<div class="page-wrapper" style="max-width:860px;">
    <section class="card">
        <h2 style="margin:0 0 14px 0;">Search Results</h2>

        <?php
        $User        = new User();
        $image_class = new Image();

        if (isset($results) && is_array($results) && count($results) > 0) {
            foreach ($results as $row) {
                $FRIEND_ROW = $User->get_user($row['userid']);
                if (is_array($FRIEND_ROW)) {
                    include("app/partials/user.php");
                }
            }
        } else {
            echo '<div class="empty-state">No results found.</div>';
        }
        ?>
    </section>
</div>

</body>
</html>