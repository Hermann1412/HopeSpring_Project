<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . "/../../../classes/connect.php");
include(__DIR__ . "/../../../classes/functions.php");
include(__DIR__ . "/../../../classes/login.php");

$email  = "";
$error  = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!csrf_validate_request()) {
        $error = "Invalid request token. Please refresh and try again.";
    } else {

    $login  = new Login();
    $result = $login->evaluate($_POST);

    if ($result != "") {
        $error = $result;
        $email = htmlspecialchars($_POST['email'] ?? "");
    } else {
        header("Location: profile.php");
        die;
    }

    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HopeSpring &mdash; Log In</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="favicon.ico">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-box">

        <div class="auth-header">
            <div class="auth-logo">&#9889; HopeSpring</div>
            <div class="auth-tagline">Connect. Testify. Grow.</div>
        </div>

        <div class="auth-body">
            <h2 class="auth-title">Welcome back</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" autocomplete="on">
                <?php echo csrf_input(); ?>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input class="form-control" type="email" id="email" name="email"
                           value="<?php echo $email; ?>" placeholder="you@example.com" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input class="form-control" type="password" id="password" name="password"
                               placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required autocomplete="current-password">
                        <button type="button" class="password-toggle" data-toggle-password="password" aria-label="Show password" aria-pressed="false">
                            <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M3 3l18 18"></path>
                                <path d="M10.58 10.58a2 2 0 0 0 2.83 2.83"></path>
                                <path d="M9.88 5.09A9.77 9.77 0 0 1 12 5c7 0 11 7 11 7a21.79 21.79 0 0 1-5.17 5.94"></path>
                                <path d="M6.61 6.61A21.75 21.75 0 0 0 1 12s4 7 11 7a10.76 10.76 0 0 0 5.39-1.39"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
                    Log In
                </button>
            </form>

            <div class="auth-footer" style="margin-top:20px;">
                Don&rsquo;t have an account? <a href="signup.php">Sign up</a>
            </div>
        </div>

    </div>
</div>
<script>
(function () {
    var toggles = document.querySelectorAll('[data-toggle-password]');
    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var input = document.getElementById(toggle.getAttribute('data-toggle-password'));
            if (!input) {
                return;
            }

            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            toggle.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            toggle.setAttribute('aria-pressed', showing ? 'false' : 'true');
            toggle.classList.toggle('is-visible', !showing);
        });
    });
})();
</script>
</body>
</html>
