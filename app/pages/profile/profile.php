<?php

include("classes/autoload.php");

$login   = new Login();
$_SESSION['mybook_userid'] = isset($_SESSION['mybook_userid']) ? $_SESSION['mybook_userid'] : 0;
$user_data = $login->check_login($_SESSION['mybook_userid'], false);
$USER      = $user_data;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profile      = new Profile();
    $profile_data = $profile->get_profile($_GET['id']);
    if (is_array($profile_data)) {
        $user_data = $profile_data[0];
    }
}

$_post_error = "";

// posting/settings
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if (!csrf_validate_request()) {
        $_post_error = "Invalid request token. Please refresh and try again.";
    } else {

    include("change_image.php");

    if (isset($_POST['first_name'])) {
        $settings_class = new Settings();
        $settings_class->save_settings($_POST, $_SESSION['mybook_userid']);
    } else {
        $post   = new Post();
        $id     = $_SESSION['mybook_userid'];
        $result = $post->create_post($id, $_POST, $_FILES);
        if ($result == "") {
            header("Location: profile.php");
            die;
        } else {
            $_post_error = $result;
        }
    }

    }
}

	//collect posts
	$post = new Post();
	$id = $user_data['userid'];
	
	$posts = $post->get_posts($id);

	//collect friends
	$user = new User();
 	
	$friends = $user->get_following($user_data['userid'],"user");

	$image_class = new Image();

	//check if this is from a notification
	if(isset($_GET['notif'])){
		notification_seen($_GET['notif']);
	}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile | HopeSpring</title>
</head>
<body>

<?php include("app/partials/header.php"); ?>

<?php
$cover_image = "images/cover_image.jpg";
$has_custom_cover = false;
if (!empty($user_data['cover_image']) && file_exists($user_data['cover_image'])) {
    $cover_image = $image_class->get_thumb_cover($user_data['cover_image']);
    $has_custom_cover = true;
}

$profile_image = ($user_data['gender'] == "Female") ? "images/user_female.jpg" : "images/user_male.jpg";
$profile_image_full = $profile_image;
if (!empty($user_data['profile_image']) && file_exists($user_data['profile_image'])) {
    $profile_image = $image_class->get_thumb_profile($user_data['profile_image']);
    $profile_image_full = $user_data['profile_image'];
}

$section = isset($_GET['section']) ? $_GET['section'] : "default";

$cover_verses = array(
    array('text' => 'The Lord is my shepherd; I shall not want.', 'ref' => 'Psalm 23:1'),
    array('text' => 'Be strong and courageous. Do not fear, for the Lord your God is with you wherever you go.', 'ref' => 'Joshua 1:9'),
    array('text' => 'Trust in the Lord with all your heart, and do not lean on your own understanding.', 'ref' => 'Proverbs 3:5'),
    array('text' => 'Your word is a lamp to my feet and a light to my path.', 'ref' => 'Psalm 119:105'),
    array('text' => 'Peace I leave with you; my peace I give to you.', 'ref' => 'John 14:27')
);
$cover_verse = $cover_verses[array_rand($cover_verses)];
?>

<div class="page-wrapper">

    <?php if (!empty($_post_error)): ?>
        <div class="alert alert-error"><?php echo nl2br(htmlspecialchars($_post_error, ENT_QUOTES, 'UTF-8')); ?></div>
    <?php endif; ?>

    <div class="profile-cover-wrap card">
        <button type="button" class="profile-cover-btn" id="openCoverPreview" aria-label="Open cover image">
            <img src="<?php echo htmlspecialchars($cover_image); ?>" class="profile-cover" alt="Cover image">
        </button>

        <?php if (i_own_content($user_data)): ?>
            <div class="profile-cover-actions">
                <a href="change_profile_image.php?change=profile" class="btn btn-outline btn-sm" id="openProfileModal">Change profile image</a>
                <a href="change_profile_image.php?change=cover" class="btn btn-outline btn-sm" id="openCoverModal">Change cover image</a>
            </div>
        <?php endif; ?>

        <div class="profile-info-bar">
            <button type="button" class="profile-avatar-btn" id="openProfilePreview" aria-label="Open profile picture">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" class="profile-avatar" alt="Profile image">
            </button>
            <?php if (!$has_custom_cover): ?>
                <div class="name-side-verse">
                    <div class="name-side-verse-title">Verse</div>
                    <p class="name-side-verse-text">"<?php echo htmlspecialchars($cover_verse['text']); ?>"</p>
                    <span class="name-side-verse-ref"><?php echo htmlspecialchars($cover_verse['ref']); ?></span>
                </div>
            <?php endif; ?>
            <div class="profile-main-info">
                <h1><?php echo htmlspecialchars($user_data['first_name'] . " " . $user_data['last_name']); ?></h1>
                <p>@<?php echo htmlspecialchars($user_data['tag_name']); ?></p>
                <div class="profile-stats">
                    <span><strong><?php echo number_format((int)$user_data['likes']); ?></strong> Followers</span>
                </div>
            </div>
            <div class="profile-cta">
                <?php if ($user_data['userid'] != $_SESSION['mybook_userid']): ?>
                    <a href="messages.php?user=<?php echo (int)$user_data['userid']; ?>" class="btn btn-outline">Message</a>
                    <a href="like.php?type=user&id=<?php echo (int)$user_data['userid']; ?>&csrf_token=<?php echo urlencode(csrf_token()); ?>" class="btn btn-primary">Follow</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="profile-nav card">
        <a href="profile.php?id=<?php echo (int)$user_data['userid']; ?>" class="<?php echo ($section == 'default') ? 'active' : ''; ?>">Timeline</a>
        <a href="profile.php?section=about&id=<?php echo (int)$user_data['userid']; ?>" class="<?php echo ($section == 'about') ? 'active' : ''; ?>">About</a>
        <a href="profile.php?section=followers&id=<?php echo (int)$user_data['userid']; ?>" class="<?php echo ($section == 'followers') ? 'active' : ''; ?>">Followers</a>
        <a href="profile.php?section=following&id=<?php echo (int)$user_data['userid']; ?>" class="<?php echo ($section == 'following') ? 'active' : ''; ?>">Following</a>
        <a href="profile.php?section=photos&id=<?php echo (int)$user_data['userid']; ?>" class="<?php echo ($section == 'photos') ? 'active' : ''; ?>">Photos</a>
        <?php if ($user_data['userid'] == $_SESSION['mybook_userid']): ?>
            <a href="profile.php?section=settings&id=<?php echo (int)$user_data['userid']; ?>" class="<?php echo ($section == 'settings') ? 'active' : ''; ?>">Settings</a>
        <?php endif; ?>
    </nav>

    <?php
    if ($section == "default") {
        include("app/partials/profile/default.php");
    } elseif ($section == "following") {
        include("app/partials/profile/following.php");
    } elseif ($section == "followers") {
        include("app/partials/profile/followers.php");
    } elseif ($section == "about") {
        include("app/partials/profile/about.php");
    } elseif ($section == "settings") {
        include("app/partials/profile/settings.php");
    } elseif ($section == "photos") {
        include("app/partials/profile/photos.php");
    }
    ?>

</div>

<div class="modal-overlay" id="profilePreviewModal">
    <div class="modal-box profile-preview-modal">
        <h3>Profile Picture</h3>
        <div class="profile-preview-body">
            <img src="<?php echo htmlspecialchars($profile_image_full); ?>" class="profile-preview-image" alt="Full profile picture">
            <?php if (i_own_content($user_data)): ?>
                <p class="profile-preview-note">Do you want to change your profile picture?</p>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="btn btn-grey" data-close="#profilePreviewModal">Close</button>
                    <button type="button" class="btn btn-primary" id="previewChangeProfileBtn">Change profile picture</button>
                </div>
            <?php else: ?>
                <div style="display:flex;justify-content:flex-end;">
                    <button type="button" class="btn btn-grey" data-close="#profilePreviewModal">Close</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal-overlay" id="coverPreviewModal">
    <div class="modal-box profile-preview-modal">
        <h3>Cover Photo</h3>
        <div class="profile-preview-body">
            <img src="<?php echo htmlspecialchars($cover_image); ?>" class="profile-preview-image" alt="Full cover image">
            <?php if (i_own_content($user_data)): ?>
                <p class="profile-preview-note">Do you want to change your cover photo?</p>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="btn btn-grey" data-close="#coverPreviewModal">Close</button>
                    <button type="button" class="btn btn-primary" id="previewChangeCoverBtn">Change cover photo</button>
                </div>
            <?php else: ?>
                <div style="display:flex;justify-content:flex-end;">
                    <button type="button" class="btn btn-grey" data-close="#coverPreviewModal">Close</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (i_own_content($user_data)): ?>
    <div class="modal-overlay" id="profileModal">
        <div class="modal-box">
            <h3>Change profile image</h3>
            <form method="post" action="profile.php?change=profile" enctype="multipart/form-data">
                <?php echo csrf_input(); ?>
                <input type="file" name="file" class="form-control" required>
                <div style="margin-top:12px;display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="btn btn-grey" data-close="#profileModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="coverModal">
        <div class="modal-box">
            <h3>Change cover image</h3>
            <form method="post" action="profile.php?change=cover" enctype="multipart/form-data">
                <?php echo csrf_input(); ?>
                <input type="file" name="file" class="form-control" required>
                <div style="margin-top:12px;display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="btn btn-grey" data-close="#coverModal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
(function () {
    const coverPreviewBtn = document.getElementById("openCoverPreview");
    const coverPreviewMd  = document.getElementById("coverPreviewModal");
    const previewChangeCoverBtn = document.getElementById("previewChangeCoverBtn");
    const previewBtn = document.getElementById("openProfilePreview");
    const previewMd  = document.getElementById("profilePreviewModal");
    const previewChangeBtn = document.getElementById("previewChangeProfileBtn");
    const profileBtn = document.getElementById("openProfileModal");
    const coverBtn   = document.getElementById("openCoverModal");
    const profileMd  = document.getElementById("profileModal");
    const coverMd    = document.getElementById("coverModal");

    function openModal(modal) {
        if (modal) modal.classList.add("open");
    }

    function closeModal(modal) {
        if (modal) modal.classList.remove("open");
    }

    if (previewBtn && previewMd) {
        previewBtn.addEventListener("click", function () {
            openModal(previewMd);
        });
    }

    if (coverPreviewBtn && coverPreviewMd) {
        coverPreviewBtn.addEventListener("click", function () {
            openModal(coverPreviewMd);
        });
    }

    if (profileBtn && profileMd) {
        profileBtn.addEventListener("click", function (e) {
            e.preventDefault();
            openModal(profileMd);
        });
    }

    if (coverBtn && coverMd) {
        coverBtn.addEventListener("click", function (e) {
            e.preventDefault();
            openModal(coverMd);
        });
    }

    if (previewChangeBtn && profileMd) {
        previewChangeBtn.addEventListener("click", function () {
            closeModal(previewMd);
            openModal(profileMd);
        });
    }

    if (previewChangeCoverBtn && coverMd) {
        previewChangeCoverBtn.addEventListener("click", function () {
            closeModal(coverPreviewMd);
            openModal(coverMd);
        });
    }

    [previewMd, coverPreviewMd, profileMd, coverMd].forEach(function (modal) {
        if (!modal) return;
        modal.addEventListener("click", function (e) {
            if (e.target === modal) closeModal(modal);
        });
    });

    document.querySelectorAll("[data-close]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const sel = btn.getAttribute("data-close");
            closeModal(document.querySelector(sel));
        });
    });

    window.addEventListener("keydown", function (e) {
        if (e.key === "Escape") {
            closeModal(previewMd);
            closeModal(coverPreviewMd);
            closeModal(profileMd);
            closeModal(coverMd);
        }
    });
})();
</script>

</body>
</html>