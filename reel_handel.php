<?php
session_start();

// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "socialauth_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_reel') {
    $response = ['success' => false, 'message' => ''];

    // Validate file upload
    if (!isset($_FILES['reel_media']) || $_FILES['reel_media']['error'] == UPLOAD_ERR_NO_FILE) {
        $response['message'] = 'No video uploaded';
        echo json_encode($response);
        exit;
    }

    $file = $_FILES['reel_media'];
    $maxFileSize = 100 * 1024 * 1024; // 100MB in bytes

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        $response['message'] = 'Video file too large. Maximum size is 100MB';
        echo json_encode($response);
        exit;
    }

    // Validate file type
    $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        $response['message'] = 'Invalid video format. Only MP4, WebM, and OGG are allowed';
        echo json_encode($response);
        exit;
    }

    // Prepare upload directory
    $uploadDir = 'uploads/reels/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('reel_') . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        $response['message'] = 'Failed to upload video';
        echo json_encode($response);
        exit;
    }

    // Prepare data for database
    $userId = $_SESSION['user_id'];
    $caption = isset($_POST['reel_text']) ? trim($_POST['reel_text']) : '';
    $caption = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');

    // Insert into database
    try {
        $stmt = $conn->prepare("INSERT INTO reels (user_id, video_path, caption, created_at) VALUES (:user_id, :video_path, :caption, NOW())");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':video_path', $filePath);
        $stmt->bindParam(':caption', $caption);
        $stmt->execute();

        $response['success'] = true;
        $response['message'] = 'Reel posted successfully';
    } catch(PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        // Clean up uploaded file if database insertion fails
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    echo json_encode($response);
    exit;
}
?>