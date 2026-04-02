<section class="card settings-section">
	<h2>About</h2>
	<?php
	$settings_class = new Settings();
	$settings       = $settings_class->get_settings($user_data['userid']);
	$about_text     = "";
	if (is_array($settings) && !empty($settings['about'])) {
		$about_text = $settings['about'];
	}
	?>

	<?php if (!empty($about_text)): ?>
		<p style="white-space:pre-wrap;line-height:1.6;"><?php echo htmlspecialchars($about_text); ?></p>
	<?php else: ?>
		<div class="empty-state">No bio added yet.</div>
	<?php endif; ?>
</section>