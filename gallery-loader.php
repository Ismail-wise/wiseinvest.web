<?php
$type = $_GET['type'] ?? '';

/* ===============================
   IMAGE GALLERY (WITH THUMBNAILS)
   =============================== */
if ($type === 'images') {

    // FULL IMAGES FOLDER
    $fullDir = __DIR__ . '/uploads/gallery/img/';
    // THUMBNAILS FOLDER
    $thumbDir = 'uploads/gallery/thumbs/';

    if (!is_dir($fullDir)) {
        echo "<p style='color:#94a3b8;text-align:center;'>No images folder.</p>";
        exit;
    }

    $images = array_diff(scandir($fullDir), ['.', '..']);

    if (empty($images)) {
        echo "<p style='color:#94a3b8;text-align:center;'>No images uploaded.</p>";
        exit;
    }

    echo "<div class='gallery-grid'>";

    foreach ($images as $img) {
        $fullSrc  = "uploads/gallery/img/$img";
        $thumbSrc = $thumbDir . $img;

        // Fallback: if thumbnail missing, use full image
        if (!file_exists(__DIR__ . '/' . $thumbSrc)) {
            $thumbSrc = $fullSrc;
        }

        echo "
        <a href='{$fullSrc}' class='gallery-item' target='_blank'>
            <img src='{$thumbSrc}' loading='lazy' alt='Gallery image'>
        </a>";
    }

    echo "</div>";
}

/* ===============================
   VIDEO GALLERY (YOUTUBE)
   =============================== */
if ($type === 'videos') {

    $json = __DIR__ . '/uploads/gallery/videos/youtube.json';

    if (!file_exists($json)) {
        echo "<p style='color:#94a3b8;text-align:center;'>youtube.json not found.</p>";
        exit;
    }

    $videos = json_decode(file_get_contents($json), true);

    if (!$videos) {
        echo "<p style='color:#94a3b8;text-align:center;'>No videos yet.</p>";
        exit;
    }

    echo "<div class='gallery-grid'>";

    foreach ($videos as $v) {
        $id = htmlspecialchars($v['youtube_id']);
        $title = htmlspecialchars($v['title']);
        $thumb = "https://img.youtube.com/vi/$id/hqdefault.jpg";

        echo "
        <div class='gallery-item video-thumb' onclick=\"openVideo('$id')\">
            <img src='$thumb' alt='$title' loading='lazy'>
            <span class='play-btn'>▶</span>
        </div>";
    }

    echo "</div>";
}