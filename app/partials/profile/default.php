<div class="two-col">

	<aside class="col-left">
		<div class="sidebar-card card">
			<h3>Following</h3>
			<?php
			if ($friends) {
				foreach ($friends as $friend) {
					$FRIEND_ROW = $user->get_user($friend['userid']);
					if (is_array($FRIEND_ROW)) {
						include("user.php");
					}
				}
			} else {
				echo '<div class="empty-state">Not following anyone yet.</div>';
			}
			?>
		</div>

		<?php $suggested_friends = $user->get_friend_suggestions($_SESSION['mybook_userid'], 5); ?>
		<div class="sidebar-card card">
			<h3>Suggested Friends</h3>
			<?php if (is_array($suggested_friends) && count($suggested_friends)): ?>
				<?php foreach ($suggested_friends as $SUGGESTED_ROW):
					$FRIEND_ROW = $SUGGESTED_ROW;
					include("user.php");
				?>
					<div style="padding:0 8px 10px;">
						<a href="like.php?type=user&id=<?php echo (int)$SUGGESTED_ROW['userid']; ?>&csrf_token=<?php echo urlencode(csrf_token()); ?>" class="btn btn-outline btn-sm btn-full">Follow</a>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="empty-state">No suggestions right now.</div>
			<?php endif; ?>
		</div>
	</aside>

	<section class="col-main">
		<?php if ($user_data['userid'] == $_SESSION['mybook_userid']): ?>
			<div class="composer card">
				<form method="post" enctype="multipart/form-data">
					<?php echo csrf_input(); ?>
					<textarea name="post" class="form-control" placeholder="What is on your mind?"></textarea>
					<div class="composer-actions">
						<label class="btn btn-outline" style="cursor:pointer;">
							Add Photo
							<input type="file" name="file" style="display:none;">
						</label>
						<button type="submit" class="btn btn-primary">Post</button>
					</div>
				</form>
			</div>
		<?php endif; ?>

		<?php
		if ($posts) {
			foreach ($posts as $ROW) {
				$user     = new User();
				$ROW_USER = $user->get_user($ROW['userid']);
				include("post.php");
			}
		} else {
			echo '<div class="card empty-state">No posts yet.</div>';
		}

		$pg = pagination_link();
		?>

		<div class="feed-pagination">
			<?php if (!empty($pg['prev_page']) && $pg['prev_page'] !== '#'): ?>
				<a href="<?= $pg['prev_page'] ?>" class="btn btn-grey">Previous</a>
			<?php endif; ?>
			<?php if (!empty($pg['next_page']) && $pg['next_page'] !== '#'): ?>
				<a href="<?= $pg['next_page'] ?>" class="btn btn-grey">Next</a>
			<?php endif; ?>
		</div>
	</section>

</div>