<!--top bar-->
<?php

$corner_image = "images/user_male.jpg";
$msg_unread = 0;
if (isset($USER)) {
    if (file_exists($USER['profile_image'])) {
        $image_class = new Image();
        $corner_image = $image_class->get_thumb_profile($USER['profile_image']);
    } else {
        if ($USER['gender'] == "Female") {
            $corner_image = "images/user_female.jpg";
        }
    }

    if (class_exists('Message')) {
        $message_class = new Message();
        $msg_unread = $message_class->count_unread($USER['userid']);
    }
}
?>
<link rel="stylesheet" href="assets/style.css">
<script>
(function () {
    const head = document.head || document.getElementsByTagName("head")[0];
    if (!head || document.querySelector('link[data-app-favicon]')) {
        return;
    }

    const iconHref = "favicon.ico";

    const favicon = document.createElement("link");
    favicon.rel = "icon";
    favicon.type = "image/x-icon";
    favicon.href = iconHref;
    favicon.setAttribute("data-app-favicon", "1");
    head.appendChild(favicon);

    const shortcut = document.createElement("link");
    shortcut.rel = "shortcut icon";
    shortcut.type = "image/x-icon";
    shortcut.href = iconHref;
    shortcut.setAttribute("data-app-favicon", "1");
    head.appendChild(shortcut);

    const apple = document.createElement("link");
    apple.rel = "apple-touch-icon";
    apple.href = iconHref;
    apple.setAttribute("data-app-favicon", "1");
    head.appendChild(apple);
})();
</script>
<nav id="navbar">
    <div class="navbar-inner">

        <!-- Brand -->
        <a href="<?php echo isset($USER) ? 'index.php' : 'login.php'; ?>" class="navbar-brand">
            &#9889; HopeSpring
        </a>

        <!-- Search -->
        <?php if (isset($USER)): ?>
        <form method="get" action="search.php" class="navbar-search" style="margin-left:12px;">
            <span class="search-icon">&#128269;</span>
            <input type="text" name="find" placeholder="Search people..." autocomplete="off"
                   value="<?php echo htmlspecialchars($_GET['find'] ?? ''); ?>">
        </form>
        <?php endif; ?>

        <!-- Actions -->
        <div class="navbar-actions">
            <?php if (isset($USER)): ?>

                <!-- Home -->
                <a href="index.php" class="nav-icon-btn" title="Home">&#127968;</a>

                <!-- Notifications -->
                <a href="notifications.php" class="nav-icon-btn" title="Notifications">
                    &#128276;
                    <?php
                        $notif = check_notifications();
                        if ($notif > 0):
                    ?>
                        <span class="nav-badge"><?php echo $notif; ?></span>
                    <?php endif; ?>
                </a>

                <!-- Church Map -->
                <a href="church_map.php" class="nav-icon-btn" title="Church Map">
                    &#9962;
                </a>

                <!-- Messages -->
                <a href="messages.php" class="nav-icon-btn" title="Messages">
                    &#128172;
                    <?php if ($msg_unread > 0): ?>
                        <span class="nav-badge" data-message-count><?php echo $msg_unread; ?></span>
                    <?php else: ?>
                        <span class="nav-badge" data-message-count style="display:none;"></span>
                    <?php endif; ?>
                </a>

                <!-- Profile avatar -->
                <a href="profile.php" title="My Profile">
                    <img src="<?php echo htmlspecialchars($corner_image); ?>" class="nav-avatar" alt="Profile">
                </a>

                <!-- Logout -->
                <a href="logout.php" class="nav-logout">Log out</a>

            <?php else: ?>
                <a href="login.php"  class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.6);font-size:13px;">Log in</a>
                <a href="signup.php" class="btn btn-grey"    style="font-size:13px;">Sign up</a>
            <?php endif; ?>
        </div>

    </div>
</nav>

<?php if (isset($USER)): ?>
<div id="globalNotificationContainer" style="position:fixed;top:80px;right:16px;z-index:999;display:flex;flex-direction:column;gap:10px;max-width:380px;"></div>

<script>
(function() {
    const container = document.getElementById("globalNotificationContainer");
    let lastShownNotifications = JSON.parse(localStorage.getItem("hopespring_shown_notifs") || "[]");
    const GLOBAL_CHECK_INTERVAL = 5000; // Check every 5 seconds

    function showNotificationToast(title, message, type = "info") {
        if (!container) return;
        const toast = document.createElement("div");
        toast.className = "notification-toast notification-" + type;
        toast.innerHTML = `
            <div class="notification-content">
                <strong>${title}</strong>
                <p>${message}</p>
            </div>
            <button class="notification-close" type="button">✕</button>
        `;
        const closeBtn = toast.querySelector(".notification-close");
        const removeToast = function () {
            toast.style.opacity = "0";
            setTimeout(function () { toast.remove(); }, 300);
        };
        closeBtn.addEventListener("click", removeToast);
        container.appendChild(toast);
        setTimeout(removeToast, 5000);
    }

    function requestBrowserNotification(title, message) {
        if ("Notification" in window && Notification.permission === "granted") {
            const notif = new Notification(title, {
                body: message,
                icon: "images/logo.jpg"
            });
            try {
                const audio = new Audio("data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAB9AAACABAAZGF0YQIAAAAAAA==");
                audio.volume = 0.3;
                audio.play().catch(function() {});
            } catch (e) {}
        }
    }

    function checkGlobalNotifications() {
        const xhr = new XMLHttpRequest();
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const messages = JSON.parse(xhr.responseText);
                    if (messages.direct_messages && messages.direct_messages.length > 0) {
                        messages.direct_messages.forEach(function(msg) {
                            const notifKey = "msg_" + msg.peer_id;
                            if (!lastShownNotifications.includes(notifKey)) {
                                lastShownNotifications.push(notifKey);
                                showNotificationToast(msg.peer_name, "Sent you a message", "message");
                                requestBrowserNotification("New message", msg.peer_name + ": " + msg.last_message.substring(0, 45));
                            }
                        });
                    }
                } catch (e) {}
            }
        };
        xhr.open("GET", "ajax.php?action=check_messages", true);
        xhr.send();

        // Also check for new posts
        const xhr2 = new XMLHttpRequest();
        xhr2.onload = function() {
            if (xhr2.status === 200) {
                try {
                    const posts = JSON.parse(xhr2.responseText);
                    if (posts.posts && posts.posts.length > 0) {
                        posts.posts.forEach(function(post) {
                            const notifKey = "post_" + post.postid;
                            if (!lastShownNotifications.includes(notifKey)) {
                                lastShownNotifications.push(notifKey);
                                showNotificationToast(post.poster_name + " posted", post.post_preview, "post");
                                requestBrowserNotification("New post", post.poster_name + " posted");
                            }
                        });
                    }
                } catch (e) {}
            }
        };
        xhr2.open("GET", "ajax.php?action=check_posts", true);
        xhr2.send();
    }

    // Request notification permission
    if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission();
    }

    // Check every 5 seconds
    setInterval(checkGlobalNotifications, GLOBAL_CHECK_INTERVAL);

    // Initial check after 2 seconds
    setTimeout(checkGlobalNotifications, 2000);

    // Save shown notifications to localStorage
    setInterval(function() {
        localStorage.setItem("hopespring_shown_notifs", JSON.stringify(lastShownNotifications.slice(-50)));
    }, 10000);
})();
</script>
<?php endif; ?>
