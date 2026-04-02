<section class="card">
    <h2 style="margin:0 0 14px 0;">Photos</h2>
    <?php
    $DB    = new Database();
    $sql   = "select image,postid from posts where has_image = 1 && userid = ? order by id desc limit 30";
    $images = $DB->read_prepared($sql, "i", [(int)$user_data['userid']]);

    $image_class = new Image();

    if (is_array($images)) {
        echo '<div class="photos-grid">';
        foreach ($images as $image_row) {
            $thumb = $image_class->get_thumb_post($image_row['image']);
            echo "<a href='single_post.php?id={$image_row['postid']}'><img src='" . htmlspecialchars($thumb) . "' alt='Post image'></a>";
        }
        echo '</div>';
    } else {
        echo '<div class="empty-state">No images found.</div>';
    }
    ?>
</section>