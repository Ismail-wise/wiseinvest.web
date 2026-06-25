<?php
include '../includes/db.php';
include '../includes/header.php';

/* SIMPLE ADMIN CHECK (adjust later if needed) */
if (!isset($_SESSION['user_id'])) {
    exit("Access denied");
}

/* HANDLE UPLOAD */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type = $_POST['type'];

    if ($type === 'image' || $type === 'video') {

        $target_dir = "../uploads/gallery/";
        $file_name = time() . "_" . basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $path = "uploads/gallery/" . $file_name;
            $stmt = $conn->prepare("INSERT INTO gallery (type, file_path) VALUES (?, ?)");
            $stmt->bind_param("ss", $type, $path);
            $stmt->execute();
        }

    } elseif ($type === 'youtube') {

        $url = $_POST['video_url'];
        $stmt = $conn->prepare("INSERT INTO gallery (type, video_url) VALUES ('youtube', ?)");
        $stmt->bind_param("s", $url);
        $stmt->execute();
    }

    header("Location: gallery.php");
    exit();
}
?>

<h2>Gallery Upload</h2>

<form method="POST" enctype="multipart/form-data">
    <select name="type" required>
        <option value="image">Image</option>
        <option value="video">Video (mp4)</option>
        <option value="youtube">YouTube Link</option>
    </select>

    <input type="file" name="file">
    <input type="text" name="video_url" placeholder="YouTube URL">

    <button type="submit">Upload</button>
</form>
