<?php include 'includes/header.php'; ?>

<section class="gallery-page clean-gallery">

<header class="gallery-header">
    <h1>Video Gallery</h1>
    <p>Client reviews & highlights</p>
</header>

<?php
$json = __DIR__ . '/uploads/gallery/videos/youtube.json';

if (!file_exists($json)) {
    echo "<p class='gallery-empty'>No videos found.</p>";
} else {

    $videos = json_decode(file_get_contents($json), true);

    if (!$videos) {
        echo "<p class='gallery-empty'>No videos uploaded.</p>";
    } else {

        echo "<div class='gallery-grid'>";

        foreach ($videos as $v) {
            $id = htmlspecialchars($v['youtube_id']);
            $title = htmlspecialchars($v['title']);
            $thumb = "https://img.youtube.com/vi/$id/hqdefault.jpg";

            echo "
            <div class='gallery-card video-card'
                 onclick=\"this.innerHTML='<iframe src=https://www.youtube.com/embed/$id?autoplay=1 allowfullscreen></iframe>'\">
                <img src='$thumb' alt='$title'>
                <span class='play-btn'>▶</span>
            </div>";
        }

        echo "</div>";
    }
}
?>
</section>

<style>
/* ===== CLEAN GALLERY UI ===== */
.clean-gallery {
    padding: 60px 20px;
    background: #020617;
    min-height: 100vh;
}

.gallery-header {
    text-align: center;
    margin-bottom: 40px;
}

.gallery-header h1 {
    font-size: 2.4rem;
    color: #fff;
    margin-bottom: 6px;
}

.gallery-header p {
    color: #94a3b8;
}

/* GRID */
.gallery-grid {
    max-width: 1300px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px,1fr));
    gap: 26px;
}

/* CARD */
.gallery-card {
    position: relative;
    background: #020617;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 55px rgba(0,0,0,.55);
    cursor: pointer;
    transition: transform .3s ease, box-shadow .3s ease;
}

.gallery-card img,
.gallery-card iframe {
    width: 100%;
    aspect-ratio: 16/9;
    object-fit: cover;
    border: none;
}

.gallery-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 35px 70px rgba(250,204,21,.45);
}

/* PLAY ICON */
.video-card .play-btn {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3.5rem;
    color: #facc15;
    background: rgba(0,0,0,.4);
}

/* EMPTY */
.gallery-empty {
    text-align: center;
    color: #94a3b8;
    margin-top: 60px;
}
</style>

<?php include 'includes/footer.php'; ?>