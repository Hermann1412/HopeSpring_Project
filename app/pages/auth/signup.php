<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . "/../../../classes/connect.php");
include(__DIR__ . "/../../../classes/functions.php");
include(__DIR__ . "/../../../classes/signup.php");

$first_name = "";
$last_name  = "";
$gender     = "Male";
$email      = "";
$error      = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!csrf_validate_request()) {
        $error = "Invalid request token. Please refresh and try again.";
    } else {

    $signup = new Signup();
    $result = $signup->evaluate($_POST);

    if ($result != "") {
        $error      = $result;
        $first_name = htmlspecialchars($_POST['first_name'] ?? "");
        $last_name  = htmlspecialchars($_POST['last_name']  ?? "");
        $gender     = htmlspecialchars($_POST['gender']     ?? "Male");
        $email      = htmlspecialchars($_POST['email']      ?? "");
    } else {
        header("Location: login.php");
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
    <title>HopeSpring &mdash; Sign Up</title>
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
            <h2 class="auth-title">Create your account</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" autocomplete="on">
                <?php echo csrf_input(); ?>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="form-group">
                    <div>
                        <label for="first_name">First name</label>
                        <input class="form-control" type="text" id="first_name" name="first_name"
                               value="<?php echo $first_name; ?>" placeholder="Jane" required autofocus>
                    </div>
                    <div>
                        <label for="last_name">Last name</label>
                        <input class="form-control" type="text" id="last_name" name="last_name"
                               value="<?php echo $last_name; ?>" placeholder="Doe" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select class="form-control" id="gender" name="gender">
                        <option value="Male"   <?php echo ($gender == 'Male'   ? 'selected' : ''); ?>>Male</option>
                        <option value="Female" <?php echo ($gender == 'Female' ? 'selected' : ''); ?>>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input class="form-control" type="email" id="email" name="email"
                           value="<?php echo $email; ?>" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input class="form-control" type="password" id="password" name="password"
                               placeholder="Minimum 6 characters" required autocomplete="new-password">
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

                <div class="form-group">
                    <label for="password2">Confirm password</label>
                    <div class="password-field">
                        <input class="form-control" type="password" id="password2" name="password2"
                               placeholder="Retype password" required autocomplete="new-password">
                        <button type="button" class="password-toggle" data-toggle-password="password2" aria-label="Show password confirmation" aria-pressed="false">
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
                    Create Account
                </button>
            </form>

            <div class="auth-footer" style="margin-top:20px;">
                Already have an account? <a href="login.php">Log in</a>
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
