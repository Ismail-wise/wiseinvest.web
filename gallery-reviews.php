<?php
$dir = __DIR__ . '/uploads/reviews/';
$files = [];

if (is_dir($dir)) {
    foreach (scandir($dir) as $file) {
        if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)) {
            $files[] = $file;
        }
    }
}

sort($files);

foreach ($files as $file) {
    echo '
    <div class="review-card">
        <img src="uploads/reviews/'.$file.'" alt="Client Review">
    </div>';
}
