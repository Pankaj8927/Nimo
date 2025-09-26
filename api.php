<?php
session_start();
require 'db_connect.php';
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'upload_movie' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $uploader_name = $_POST['uploader_name'] ?? '';

    // Validate inputs
    if (empty($title) || empty($genre) || empty($uploader_name)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    // Validate files
    $thumbnail = $_FILES['thumbnail'] ?? null;
    $video = $_FILES['video'] ?? null;
    $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
    $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg'];
    $max_file_size = 100 * 1024 * 1024; // 100MB

    if (!$thumbnail || !$video) {
        echo json_encode(['success' => false, 'message' => 'Thumbnail and video files are required.']);
        exit();
    }

    if (!in_array($thumbnail['type'], $allowed_image_types) || $thumbnail['size'] > $max_file_size) {
        echo json_encode(['success' => false, 'message' => 'Invalid thumbnail file type or size.']);
        exit();
    }

    if (!in_array($video['type'], $allowed_video_types) || $video['size'] > $max_file_size) {
        echo json_encode(['success' => false, 'message' => 'Invalid video file type or size.']);
        exit();
    }

    // Handle file uploads
    $thumbnail_dir = 'Uploads/thumbnails/';
    $video_dir = 'Uploads/videos/';
    if (!is_dir($thumbnail_dir)) mkdir($thumbnail_dir, 0777, true);
    if (!is_dir($video_dir)) mkdir($video_dir, 0777, true);

    $thumbnail_path = $thumbnail_dir . uniqid() . '_' . basename($thumbnail['name']);
    $video_path = $video_dir . uniqid() . '_' . basename($video['name']);

    if (move_uploaded_file($thumbnail['tmp_name'], $thumbnail_path) && move_uploaded_file($video['tmp_name'], $video_path)) {
        $stmt = $pdo->prepare("INSERT INTO movies (uploader_name, title, genre, thumbnail_path, video_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$uploader_name, $title, $genre, $thumbnail_path, $video_path]);
        echo json_encode(['success' => true, 'message' => 'Movie uploaded successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload files.']);
    }
    exit();
}

if ($action === 'get_movies') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 8;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $genre = isset($_GET['genre']) ? $_GET['genre'] : '';

    $query = "SELECT * FROM movies WHERE 1=1";
    $params = [];
    if ($search) {
        $query .= " AND title LIKE ?";
        $params[] = "%$search%";
    }
    if ($genre) {
        $query .= " AND genre = ?";
        $params[] = $genre;
    }
    $query .= " ORDER BY upload_date DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'movies' => $movies]);
    exit();
}

if ($action === 'like_movie' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = $_POST['movie_id'] ?? 0;

    if ($movie_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid movie ID.']);
        exit();
    }

    // Prevent duplicate likes in the same session
    if (!isset($_SESSION['liked_movies'])) {
        $_SESSION['liked_movies'] = [];
    }
    if (in_array($movie_id, $_SESSION['liked_movies'])) {
        echo json_encode(['success' => false, 'message' => 'You have already liked this movie.']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE movies SET likes = likes + 1 WHERE movie_id = ?");
    $stmt->execute([$movie_id]);

    $_SESSION['liked_movies'][] = $movie_id;
    echo json_encode(['success' => true, 'message' => 'Liked successfully.']);
    exit();
}
?>