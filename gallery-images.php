<?php include 'includes/header.php'; ?>

<section class="gallery-page clean-gallery">

    <header class="gallery-header">
        <h1>Image Gallery</h1>
        <p>Events • Activities • Moments</p>
    </header>

<?php
$dir = __DIR__ . '/uploads/gallery/images/';
$images = is_dir($dir) ? array_diff(scandir($dir), ['.','..']) : [];

if (!$images) {
    echo "<p class='gallery-empty'>No images uploaded.</p>";
} else {
    echo "<div class='gallery-grid'>";
    foreach ($images as $img) {
        $src = "uploads/gallery/images/$img";
        echo "
        <div class='gallery-card'>
            <img src='$src' loading='lazy'>
        </div>";
    }
    echo "</div>";
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
    grid-template-columns: repeat(auto-fill, minmax(260px,1fr));
    gap: 22px;
}

/* CARD */
.gallery-card {
    background: #020617;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 20px 45px rgba(0,0,0,.5);
    transition: transform .3s ease, box-shadow .3s ease;
}

.gallery-card img {
    width: 100%;
    aspect-ratio: 4/3;
    object-fit: cover;
    display: block;
}

.gallery-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 30px 60px rgba(250,204,21,.35);
}

/* EMPTY */
.gallery-empty {
    text-align: center;
    color: #94a3b8;
    margin-top: 60px;
}
</style>

<?php include 'includes/footer.php'; ?>