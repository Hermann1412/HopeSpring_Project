<section class="card settings-section">
    <h2>Profile Settings</h2>
    <form method="post" enctype="multipart/form-data">
        <?php echo csrf_input(); ?>
        <?php
        $settings_class = new Settings();
        $settings       = $settings_class->get_settings($_SESSION['mybook_userid']);

        if (is_array($settings)) {
            echo "<div class='form-group'><label>First Name</label><input type='text' class='form-control' name='first_name' value='" . htmlspecialchars($settings['first_name']) . "' placeholder='First name'></div>";
            echo "<div class='form-group'><label>Last Name</label><input type='text' class='form-control' name='last_name' value='" . htmlspecialchars($settings['last_name']) . "' placeholder='Last name'></div>";
            echo "<div class='form-group'><label>Gender</label><select class='form-control' name='gender'><option value='Male'" . (($settings['gender'] == 'Male') ? ' selected' : '') . ">Male</option><option value='Female'" . (($settings['gender'] == 'Female') ? ' selected' : '') . ">Female</option></select></div>";
            echo "<div class='form-group'><label>Email</label><input type='email' class='form-control' name='email' value='" . htmlspecialchars($settings['email']) . "' placeholder='Email'></div>";
            echo "<div class='form-group'><label>New Password</label><input type='password' class='form-control' name='password' value='' placeholder='Leave blank to keep current password' autocomplete='new-password'></div>";
            echo "<div class='form-group'><label>Confirm Password</label><input type='password' class='form-control' name='password2' value='' placeholder='Confirm new password' autocomplete='new-password'></div>";
            echo "<div class='form-group'><label>About</label><textarea class='form-control' style='min-height:160px;' name='about'>" . htmlspecialchars($settings['about']) . "</textarea></div>";
            echo "<button class='btn btn-primary' type='submit'>Save Settings</button>";
        }
        ?>
    </form>
</section>