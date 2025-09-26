<?php
// Set session cookie lifetime to 6 months (180 days)
$six_months_in_seconds = 180 * 24 * 60 * 60; // 180 days * 24 hours * 60 minutes * 60 seconds
session_set_cookie_params([
    'lifetime' => $six_months_in_seconds,
    'path' => '/',
    'domain' => '', // Leave empty for current domain
    'secure' => true, // Use true if HTTPS is enabled
    'httponly' => true, // Prevent JavaScript access to session cookie
    'samesite' => 'Lax' // Helps prevent CSRF attacks
]);

// Start the session
session_start();
require_once "db_connect.php";

$db = Database::getInstance();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

try {
    // Fetch logged-in user's profile
    $stmt = $db->prepare("SELECT up.*, u.username FROM user_profiles up LEFT JOIN users u ON up.user_id = u.id WHERE up.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'user_id' => $user_id,
        'username' => 'DefaultUser',
        'name' => 'Default User',
        'avatar_path' => 'uploads/avatars/default.jpg',
        'bio' => 'No bio yet',
        'location' => 'Not specified'
    ];

    // Fetch pending friend requests received
    $stmt = $db->prepare("
        SELECT fr.id, fr.sender_id, u.username, up.avatar_path, up.name
        FROM friend_requests fr
        JOIN users u ON fr.sender_id = u.id
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE fr.receiver_id = :user_id AND fr.status = 'pending'
    ");
    $stmt->execute(['user_id' => $user_id]);
    $friend_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch friends (accepted friend requests)
    $stmt = $db->prepare("
        SELECT u.id, u.username, up.avatar_path, up.name
        FROM users u
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE u.id IN (
            SELECT receiver_id FROM friend_requests WHERE sender_id = :user_id AND status = 'accepted'
            UNION
            SELECT sender_id FROM friend_requests WHERE receiver_id = :user_id AND status = 'accepted'
        )
    ");
    $stmt->execute(['user_id' => $user_id]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch friend suggestions (users not yet friends)
    $stmt = $db->prepare("
        SELECT u.id, u.username, up.avatar_path, up.name
        FROM users u
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE u.id NOT IN (
            SELECT receiver_id FROM friend_requests WHERE sender_id = :user_id AND status IN ('pending', 'accepted')
            UNION
            SELECT sender_id FROM friend_requests WHERE receiver_id = :user_id AND status IN ('pending', 'accepted')
        ) AND u.id != :user_id
        LIMIT 5
    ");
    $stmt->execute(['user_id' => $user_id]);
    $friend_suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch notifications
    $stmt = $db->prepare("
        SELECT n.id, n.type, n.message, n.is_read, n.created_at
        FROM notifications n
        WHERE n.user_id = :user_id
        ORDER BY n.created_at DESC
        LIMIT 10
    ");
    $stmt->execute(['user_id' => $user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $unread_notifications = array_filter($notifications, fn($n) => !$n['is_read']);

    // Fetch story previews (latest story per user)
    $stmt = $db->prepare("
        SELECT s.id, s.user_id, s.media_path, s.media_type, s.created_at, up.name, up.avatar_path, u.username
        FROM stories s
        LEFT JOIN user_profiles up ON s.user_id = up.user_id
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.created_at > NOW() - INTERVAL 24 HOUR
        AND s.id IN (
            SELECT MAX(id)
            FROM stories
            WHERE created_at > NOW() - INTERVAL 24 HOUR
            GROUP BY user_id
        )
        ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    $storyPreviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all stories with like status
    $stmt = $db->prepare("
        SELECT s.id, s.user_id, s.media_path, s.media_type, s.created_at, up.name, up.avatar_path, u.username,
               (SELECT COUNT(*) FROM story_likes WHERE story_id = s.id) as like_count,
               EXISTS(SELECT 1 FROM story_likes WHERE story_id = s.id AND user_id = :user_id) as user_liked
        FROM stories s 
        LEFT JOIN user_profiles up ON s.user_id = up.user_id 
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.created_at > NOW() - INTERVAL 24 HOUR 
        ORDER BY s.user_id, s.created_at ASC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $allStories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group stories by user
    $storiesByUser = [];
    foreach ($allStories as $story) {
        $storiesByUser[$story['user_id']][] = [
            'id' => (string)$story['id'],
            'media_path' => $story['media_path'],
            'media_type' => $story['media_type'],
            'name' => $story['name'] ?: $story['username'] ?: 'Unknown',
            'avatar_path' => $story['avatar_path'] ?: 'uploads/avatars/default.jpg',
            'like_count' => $story['like_count'],
            'user_liked' => $story['user_liked']
        ];
    }
    $storiesByUserJson = json_encode($storiesByUser);
    // Fetch all posts with like counts, user like status, and comment counts
    $stmt = $db->prepare("
    SELECT p.*, up.name, up.avatar_path, u.username,
           (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
           EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.id AND user_id = :user_id) as user_liked,
           (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
    FROM posts p 
    LEFT JOIN user_profiles up ON p.user_id = up.user_id 
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
");
    $stmt->execute(['user_id' => $user_id]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all reels with like counts and user like status
    $stmt = $db->prepare("
    SELECT r.id, r.user_id, r.video_path, r.caption, r.created_at, r.like_count,
           up.name, up.avatar_path, u.username,
           EXISTS(SELECT 1 FROM reel_likes WHERE reel_id = r.id AND user_id = :user_id) as user_liked
    FROM reels r 
    LEFT JOIN user_profiles up ON r.user_id = up.user_id 
    LEFT JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT 20
");
    $stmt->execute(['user_id' => $user_id]);
    $reels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Fetch posts
    $stmt = $db->prepare("
   SELECT p.*, up.name, up.avatar_path, u.username,
          (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
          EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.id AND user_id = ?) as user_liked,
          (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
   FROM posts p 
   LEFT JOIN user_profiles up ON p.user_id = up.user_id 
   LEFT JOIN users u ON p.user_id = u.id
   ORDER BY p.created_at DESC
   LIMIT 20
");
    $stmt->execute([$user_id]);
    $posts = $stmt->fetchAll();

    // Fetch comments
    $comments = [];
    foreach ($posts as $post) {
        $stmt = $db->prepare("
       SELECT c.id, c.content, c.created_at, u.username, up.name, up.avatar_path, c.user_id
       FROM comments c
       JOIN users u ON c.user_id = u.id
       LEFT JOIN user_profiles up ON c.user_id = up.user_id
       WHERE c.post_id = ?
       ORDER BY c.created_at ASC
   ");
        $stmt->execute([$post['id']]);
        $comments[$post['id']] = $stmt->fetchAll();
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $error_message = "An error occurred. Please try again later.";
}
// Centralized POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        switch ($_POST['action'] ?? '') {
            case 'search_users':
                $search_query = filter_input(INPUT_POST, 'query', FILTER_SANITIZE_STRING);
                $stmt = $db->prepare("
                    SELECT u.id, u.username, up.name, up.avatar_path
                    FROM users u
                    LEFT JOIN user_profiles up ON u.id = up.user_id
                    WHERE (u.username LIKE :query OR up.name LIKE :query)
                    AND u.id != :current_user_id
                    LIMIT 10
                ");
                $stmt->execute(['query' => "%$search_query%", 'current_user_id' => $user_id]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'results' => $results]);
                exit;
            case 'toggle_reel_like':
                $reel_id = filter_input(INPUT_POST, 'reel_id', FILTER_SANITIZE_NUMBER_INT);
                $stmt = $db->prepare("SELECT COUNT(*) FROM reel_likes WHERE reel_id = :reel_id AND user_id = :user_id");
                $stmt->execute(['reel_id' => $reel_id, 'user_id' => $user_id]);
                $hasLiked = $stmt->fetchColumn();

                if ($hasLiked) {
                    $stmt = $db->prepare("DELETE FROM reel_likes WHERE reel_id = :reel_id AND user_id = :user_id");
                    $stmt->execute(['reel_id' => $reel_id, 'user_id' => $user_id]);
                    $stmt = $db->prepare("UPDATE reels SET like_count = GREATEST(like_count - 1, 0) WHERE id = :reel_id");
                    $stmt->execute(['reel_id' => $reel_id]);
                    $isLiked = false;
                } else {
                    $stmt = $db->prepare("INSERT INTO reel_likes (reel_id, user_id, created_at) VALUES (:reel_id, :user_id, NOW())");
                    $stmt->execute(['reel_id' => $reel_id, 'user_id' => $user_id]);
                    $stmt = $db->prepare("UPDATE reels SET like_count = like_count + 1 WHERE id = :reel_id");
                    $stmt->execute(['reel_id' => $reel_id]);
                    $isLiked = true;
                }
                $stmt = $db->prepare("SELECT like_count FROM reels WHERE id = :reel_id");
                $stmt->execute(['reel_id' => $reel_id]);
                $new_likes = $stmt->fetchColumn();
                echo json_encode(['success' => true, 'likes' => $new_likes, 'isLiked' => $isLiked]);
                exit;
            case 'create_reel':
                $response = ['success' => false, 'message' => ''];

                // Validate file upload
                if (!isset($_FILES['reel_media']) || $_FILES['reel_media']['error'] == UPLOAD_ERR_NO_FILE) {
                    $response['message'] = 'No video uploaded';
                    echo json_encode($response);
                    exit;
                }

                $file = $_FILES['reel_media'];
                $maxFileSize = 100 * 1024 * 1024; // 100MB

                // Validate file size
                if ($file['size'] > $maxFileSize) {
                    $response['message'] = 'Video file too large. Maximum size is 100MB';
                    echo json_encode($response);
                    exit;
                }

                // Validate file type and extension
                $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
                $allowedExtensions = ['mp4', 'webm', 'ogg'];
                $fileType = mime_content_type($file['tmp_name']);
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
                    $response['message'] = 'Invalid video format. Only MP4, WebM, and OGG are allowed';
                    echo json_encode($response);
                    exit;
                }

                // Prepare upload directory
                $uploadDir = 'uploads/reels/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Sanitize and generate unique filename
                $fileName = uniqid('reel_') . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;

                // Move uploaded file
                if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                    $response['message'] = 'Failed to upload video';
                    echo json_encode($response);
                    exit;
                }

                // Validate and sanitize caption
                $caption = isset($_POST['reel_text']) ? trim($_POST['reel_text']) : '';
                $caption = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');
                $caption = substr($caption, 0, 255); // Limit caption length

                try {
                    // Insert into database
                    $stmt = $db->prepare("INSERT INTO reels (user_id, video_path, caption, created_at, like_count) VALUES (:user_id, :video_path, :caption, NOW(), 0)");
                    $stmt->execute([
                        'user_id' => $user_id,
                        'video_path' => $filePath,
                        'caption' => $caption
                    ]);

                    // Fetch the new reel for UI update
                    $reel_id = $db->lastInsertId();
                    $stmt = $db->prepare("
            SELECT r.id, r.user_id, r.video_path, r.caption, r.created_at, r.like_count,
                   up.name, up.avatar_path, u.username,
                   0 as user_liked
            FROM reels r 
            LEFT JOIN user_profiles up ON r.user_id = up.user_id 
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.id = :reel_id
        ");
                    $stmt->execute(['reel_id' => $reel_id]);
                    $new_reel = $stmt->fetch(PDO::FETCH_ASSOC);

                    $response['success'] = true;
                    $response['message'] = 'Reel posted successfully';
                    $response['reel'] = $new_reel;
                } catch (PDOException $e) {
                    error_log("Reel creation error: " . $e->getMessage());
                    unlink($filePath); // Remove uploaded file on error
                    $response['message'] = 'Failed to save reel to database';
                }

                echo json_encode($response);
                exit;

            case 'get_reel_info':
                $reel_id = filter_input(INPUT_POST, 'reel_id', FILTER_SANITIZE_NUMBER_INT);
                $stmt = $db->prepare("
        SELECT like_count, 
               EXISTS(SELECT 1 FROM reel_likes WHERE reel_id = :reel_id AND user_id = :user_id) as user_liked
        FROM reels WHERE id = :reel_id
    ");
                $stmt->execute(['reel_id' => $reel_id, 'user_id' => $user_id]);
                $reel_info = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'like_count' => $reel_info['like_count'], 'user_liked' => $reel_info['user_liked']]);
                exit;
                // reel end
            case 'get_friends':
                $stmt = $db->prepare("
                        SELECT u.id, u.username, up.avatar_path, up.name
                        FROM users u
                        LEFT JOIN user_profiles up ON u.id = up.user_id
                        WHERE u.id IN (
                            SELECT receiver_id FROM friend_requests WHERE sender_id = :user_id AND status = 'accepted'
                            UNION
                            SELECT sender_id FROM friend_requests WHERE receiver_id = :user_id AND status = 'accepted'
                        )
                    ");
                $stmt->execute(['user_id' => $user_id]);
                $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['success' => true, 'friends' => $friends]);
                exit;
            case 'add_friend':
                $receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_SANITIZE_NUMBER_INT);

                // Verify receiver exists
                $stmt = $db->prepare("SELECT id FROM users WHERE id = :receiver_id");
                $stmt->execute(['receiver_id' => $receiver_id]);
                if (!$stmt->fetch()) {
                    echo json_encode(['success' => false, 'error' => 'User not found']);
                    exit;
                }

                // Check if request already exists
                $stmt = $db->prepare("
                        SELECT status FROM friend_requests 
                        WHERE sender_id = :sender_id AND receiver_id = :receiver_id
                    ");
                $stmt->execute(['sender_id' => $user_id, 'receiver_id' => $receiver_id]);
                $existing = $stmt->fetch();

                if ($existing && $existing['status'] === 'pending') {
                    echo json_encode(['success' => false, 'error' => 'Friend request already sent']);
                    exit;
                } elseif ($existing && $existing['status'] === 'accepted') {
                    echo json_encode(['success' => false, 'error' => 'Already friends']);
                    exit;
                }

                // Insert friend request
                $stmt = $db->prepare("
                        INSERT INTO friend_requests (sender_id, receiver_id, status, created_at)
                        VALUES (:sender_id, :receiver_id, 'pending', NOW())
                        ON DUPLICATE KEY UPDATE status = 'pending', updated_at = NOW()
                    ");
                $stmt->execute(['sender_id' => $user_id, 'receiver_id' => $receiver_id]);
                $request_id = $db->lastInsertId();

                // Create notification for receiver
                $stmt = $db->prepare("
                        INSERT INTO notifications (user_id, type, related_id, message, created_at)
                        VALUES (:user_id, 'friend_request', :related_id, :message, NOW())
                    ");
                $stmt->execute([
                    'user_id' => $receiver_id,
                    'related_id' => $request_id,
                    'message' => htmlspecialchars($profile['name'] ?: $profile['username']) . " sent you a friend request."
                ]);

                echo json_encode(['success' => true, 'message' => 'Friend request sent']);
                exit;
            case 'create_post':
                $post_text = filter_input(INPUT_POST, 'post_text', FILTER_SANITIZE_STRING);
                $media = $_FILES['post_media'] ?? null;
                $media_path = null;
                $media_type = null;

                // Create upload directories if they don't exist
                $photo_dir = 'Uploads/photos/';
                $video_dir = 'Uploads/videos/';
                if (!file_exists($photo_dir)) mkdir($photo_dir, 0777, true);
                if (!file_exists($video_dir)) mkdir($video_dir, 0777, true);

                if ($media && $media['error'] === UPLOAD_ERR_OK) {
                    $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg'];
                    $max_file_size = 100 * 1024 * 1024; // 100MB
                    $mime_type = mime_content_type($media['tmp_name']);
                    $ext = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
                    $filename = uniqid() . '.' . $ext;

                    if (in_array($mime_type, $allowed_image_types) && $media['size'] <= $max_file_size) {
                        $media_path = $photo_dir . $filename;
                        $media_type = 'image';
                    } elseif (in_array($mime_type, $allowed_video_types) && $media['size'] <= $max_file_size) {
                        $media_path = $video_dir . $filename;
                        $media_type = 'video';
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Invalid file type or size exceeds 100MB']);
                        exit;
                    }
                    if (!move_uploaded_file($media['tmp_name'], $media_path)) {
                        echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
                        exit;
                    }
                }
                if (empty($post_text) && !$media_path) {
                    echo json_encode(['success' => false, 'error' => 'Post content or media is required']);
                    exit;
                }
                $stmt = $db->prepare("
                        INSERT INTO posts (user_id, content, media_path, media_type, created_at)
                        VALUES (:user_id, :content, :media_path, :media_type, NOW())
                    ");
                $stmt->execute([
                    'user_id' => $user_id,
                    'content' => $post_text ?: null,
                    'media_path' => $media_path,
                    'media_type' => $media_type
                ]);
                $post_id = $db->lastInsertId();
                // Fetch the new post for UI update
                $stmt = $db->prepare("
                        SELECT p.*, up.name, up.avatar_path, u.username,
                               0 as like_count,
                               0 as user_liked,
                               0 as comment_count
                        FROM posts p 
                        LEFT JOIN user_profiles up ON p.user_id = up.user_id 
                        LEFT JOIN users u ON p.user_id = u.id
                        WHERE p.id = :post_id
                    ");
                $stmt->execute(['post_id' => $post_id]);
                $new_post = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'post' => $new_post]);
                exit;
            case 'accept_friend':
                $request_id = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);

                // Verify request exists and is pending
                $stmt = $db->prepare("
                        SELECT sender_id 
                        FROM friend_requests 
                        WHERE id = :request_id AND receiver_id = :user_id AND status = 'pending'
                    ");
                $stmt->execute(['request_id' => $request_id, 'user_id' => $user_id]);
                $sender_id = $stmt->fetchColumn();

                if (!$sender_id) {
                    echo json_encode(['success' => false, 'error' => 'Invalid or non-pending request']);
                    exit;
                }

                // Update friend request to accepted
                $stmt = $db->prepare("
                        UPDATE friend_requests 
                        SET status = 'accepted', updated_at = NOW()
                        WHERE id = :request_id AND receiver_id = :user_id
                    ");
                $stmt->execute(['request_id' => $request_id, 'user_id' => $user_id]);

                // Create notification for sender
                $stmt = $db->prepare("
                        INSERT INTO notifications (user_id, type, related_id, message, created_at)
                        VALUES (:user_id, 'friend_accepted', :related_id, :message, NOW())
                    ");
                $stmt->execute([
                    'user_id' => $sender_id,
                    'related_id' => $request_id,
                    'message' => htmlspecialchars($profile['name'] ?: $profile['username']) . " accepted your friend request."
                ]);

                echo json_encode(['success' => true, 'message' => 'Friend request accepted']);
                exit;
            case 'reject_friend':
                $request_id = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);
                $stmt = $db->prepare("
                    UPDATE friend_requests SET status = 'rejected', updated_at = NOW()
                    WHERE id = :request_id AND receiver_id = :user_id AND status = 'pending'
                ");
                $stmt->execute(['request_id' => $request_id, 'user_id' => $user_id]);
                echo json_encode(['success' => true]);
                exit;

            case 'toggle_story_like':
                $story_id = filter_input(INPUT_POST, 'story_id', FILTER_SANITIZE_NUMBER_INT);
                $stmt = $db->prepare("SELECT COUNT(*) FROM story_likes WHERE story_id = :story_id AND user_id = :user_id");
                $stmt->execute(['story_id' => $story_id, 'user_id' => $user_id]);
                $hasLiked = $stmt->fetchColumn();

                if ($hasLiked) {
                    $stmt = $db->prepare("DELETE FROM story_likes WHERE story_id = :story_id AND user_id = :user_id");
                    $stmt->execute(['story_id' => $story_id, 'user_id' => $user_id]);
                    $isLiked = false;
                } else {
                    $stmt = $db->prepare("INSERT INTO story_likes (story_id, user_id, created_at) VALUES (:story_id, :user_id, NOW())");
                    $stmt->execute(['story_id' => $story_id, 'user_id' => $user_id]);
                    $isLiked = true;
                }
                $stmt = $db->prepare("SELECT COUNT(*) FROM story_likes WHERE story_id = :story_id");
                $stmt->execute(['story_id' => $story_id]);
                $new_count = $stmt->fetchColumn();
                echo json_encode(['success' => true, 'likes' => $new_count, 'isLiked' => $isLiked]);
                exit;

            case 'send_story_message':
                $story_id = filter_input(INPUT_POST, 'story_id', FILTER_SANITIZE_NUMBER_INT);
                $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
                $stmt = $db->prepare("
                    INSERT INTO story_messages (story_id, sender_id, message, created_at)
                    VALUES (:story_id, :sender_id, :message, NOW())
                ");
                $stmt->execute(['story_id' => $story_id, 'sender_id' => $user_id, 'message' => $message]);
                echo json_encode(['success' => true]);
                exit;

            case 'toggle_like':
                $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
                $stmt = $db->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = :post_id AND user_id = :user_id");
                $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
                $hasLiked = $stmt->fetchColumn();

                if ($hasLiked) {
                    $stmt = $db->prepare("DELETE FROM post_likes WHERE post_id = :post_id AND user_id = :user_id");
                    $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
                    $stmt = $db->prepare("UPDATE posts SET likes = GREATEST(likes - 1, 0) WHERE id = :post_id");
                    $stmt->execute(['post_id' => $post_id]);
                    $isLiked = false;
                } else {
                    $stmt = $db->prepare("INSERT INTO post_likes (post_id, user_id, created_at) VALUES (:post_id, :user_id, NOW())");
                    $stmt->execute(['post_id' => $post_id, 'user_id' => $user_id]);
                    $stmt = $db->prepare("UPDATE posts SET likes = likes + 1 WHERE id = :post_id");
                    $stmt->execute(['post_id' => $post_id]);
                    $isLiked = true;
                }
                $stmt = $db->prepare("SELECT likes FROM posts WHERE id = :post_id");
                $stmt->execute(['post_id' => $post_id]);
                $new_likes = $stmt->fetchColumn();
                echo json_encode(['success' => true, 'likes' => $new_likes, 'isLiked' => $isLiked]);
                exit;
                // Add these cases within the existing switch statement in the POST handler
            case 'delete_message':
                $message_id = filter_input(INPUT_POST, 'message_id', FILTER_SANITIZE_NUMBER_INT);
                $stmt = $db->prepare("
                        DELETE FROM messages 
                        WHERE id = :message_id AND sender_id = :user_id
                    ");
                $stmt->execute(['message_id' => $message_id, 'user_id' => $user_id]);
                $affected = $stmt->rowCount();
                echo json_encode(['success' => $affected > 0]);
                exit;
            case 'get_messages':
                $friend_id = filter_input(INPUT_POST, 'friend_id', FILTER_SANITIZE_NUMBER_INT);
                $stmt = $db->prepare("
                    SELECT m.*, 
                        (SELECT up.avatar_path FROM user_profiles up WHERE up.user_id = m.sender_id) as sender_avatar
                    FROM messages m
                    WHERE (m.sender_id = :user_id AND m.receiver_id = :friend_id)
                    OR (m.sender_id = :friend_id AND m.receiver_id = :user_id)
                    ORDER BY m.created_at ASC
                ");
                $stmt->execute(['user_id' => $user_id, 'friend_id' => $friend_id]);
                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Mark messages as read
                $stmt = $db->prepare("
                    UPDATE messages 
                    SET is_read = 1 
                    WHERE receiver_id = :user_id AND sender_id = :friend_id AND is_read = 0
                ");
                $stmt->execute(['user_id' => $user_id, 'friend_id' => $friend_id]);

                echo json_encode(['success' => true, 'messages' => $messages]);
                exit;
            case 'get_unread_notifications_count':
                $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
                $stmt->execute(['user_id' => $user_id]);
                $count = $stmt->fetchColumn();
                echo json_encode(['success' => true, 'count' => $count]);
                exit;
            case 'mark_notifications_read':
                $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
                $stmt->execute(['user_id' => $user_id]);
                echo json_encode(['success' => true]);
                exit;
            case 'post_comment':
                $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
                $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_SPECIAL_CHARS);
                if (!$post_id || empty($content)) throw new Exception('Invalid post ID or comment');
                $stmt = $db->prepare("
                        INSERT INTO comments (post_id, user_id, content, created_at)
                        VALUES (?, ?, ?, NOW())
                    ");
                $stmt->execute([$post_id, $user_id, $content]);
                $comment_id = $db->lastInsertId();

                $stmt = $db->prepare("
                        SELECT c.id, c.content, c.created_at, u.username, up.name, up.avatar_path
                        FROM comments c
                        JOIN users u ON c.user_id = u.id
                        LEFT JOIN user_profiles up ON c.user_id = up.user_id
                        WHERE c.id = ?
                    ");
                $stmt->execute([$comment_id]);
                $new_comment = $stmt->fetch();

                $stmt = $db->prepare("SELECT user_id FROM posts WHERE id = ?");
                $stmt->execute([$post_id]);
                $post_owner_id = $stmt->fetchColumn();

                if ($post_owner_id != $user_id) {
                    $stmt = $db->prepare("
                            INSERT INTO notifications (user_id, type, related_id, message, created_at)
                            VALUES (?, 'new_comment', ?, ?, NOW())
                        ");
                    $stmt->execute([
                        $post_owner_id,
                        $comment_id,
                        htmlspecialchars($profile['name'] ?: $profile['username']) . " commented on your post."
                    ]);
                }

                echo json_encode(['success' => true, 'comment' => $new_comment]);
                exit;
            case 'send_message':
                $friend_id = filter_input(INPUT_POST, 'friend_id', FILTER_SANITIZE_NUMBER_INT);
                $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
                $media_path = '';
                $media_type = '';

                if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
                    $upload_dir = "uploads/messages/";
                    if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

                    $file = $_FILES['media'];
                    $media_path = $upload_dir . time() . "_" . basename($file['name']);
                    $mime_type = mime_content_type($file['tmp_name']);
                    $media_type = strpos($mime_type, 'image') === 0 ? 'image' : (strpos($mime_type, 'video') === 0 ? 'video' : 'file');
                    move_uploaded_file($file['tmp_name'], $media_path);
                }

                $stmt = $db->prepare("
                        INSERT INTO messages (sender_id, receiver_id, message, media_path, media_type, created_at)
                        VALUES (:sender_id, :receiver_id, :message, :media_path, :media_type, NOW())
                    ");
                $stmt->execute([
                    'sender_id' => $user_id,
                    'receiver_id' => $friend_id,
                    'message' => $message ?: '',
                    'media_path' => $media_path,
                    'media_type' => $media_type
                ]);

                // Add notification
                $stmt = $db->prepare("
                        INSERT INTO notifications (user_id, type, related_id, message, created_at)
                        VALUES (:user_id, 'new_message', :related_id, :message, NOW())
                    ");
                $stmt->execute([
                    'user_id' => $friend_id,
                    'related_id' => $db->lastInsertId(),
                    'message' => htmlspecialchars($profile['name'] ?: $profile['username']) . " sent you a message."
                ]);

                echo json_encode(['success' => true]);
                exit;
        }

        // Handle story and post uploads
        if (isset($_POST['upload_story']) || isset($_POST['post_status'])) {
            $upload_dir = isset($_POST['upload_story']) ? "uploads/stories/" : "uploads/posts/";
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

            $media_path = '';
            $media_type = '';
            if (isset($_FILES['story_media']) && $_FILES['story_media']['error'] == 0) {
                $file = $_FILES['story_media'];
            } elseif (isset($_FILES['post_media']) && $_FILES['post_media']['error'] == 0) {
                $file = $_FILES['post_media'];
            }

            if (isset($file)) {
                $media_path = $upload_dir . time() . "_" . basename($file['name']);
                $mime_type = mime_content_type($file['tmp_name']);
                $media_type = strpos($mime_type, 'image') === 0 ? 'image' : (strpos($mime_type, 'video') === 0 ? 'video' : '');
                move_uploaded_file($file['tmp_name'], $media_path);
            }

            if (isset($_POST['upload_story'])) {
                $stmt = $db->prepare("
                    INSERT INTO stories (user_id, media_path, media_type, created_at)
                    VALUES (:user_id, :media_path, :media_type, NOW())
                ");
                $stmt->execute(['user_id' => $user_id, 'media_path' => $media_path, 'media_type' => $media_type]);
                $success_message = "Story uploaded successfully!";
            } elseif (isset($_POST['post_status'])) {
                $content = filter_input(INPUT_POST, 'post_text', FILTER_SANITIZE_STRING);
                $stmt = $db->prepare("
                    INSERT INTO posts (user_id, content, media_path, media_type, created_at, likes)
                    VALUES (:user_id, :content, :media_path, :media_type, NOW(), 0)
                ");
                $stmt->execute([
                    'user_id' => $user_id,
                    'content' => $content,
                    'media_path' => $media_path,
                    'media_type' => $media_type
                ]);
                $success_message = "Post created successfully!";
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialFusion - Social Media Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="./profile.css">
    <style>
        /* Reels Section */
        .reels-section {
            margin-bottom: 30px;
        }

        .reel-grid {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding-bottom: 10px;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .reel-grid::-webkit-scrollbar {
            height: 6px;
        }

        .reel-grid::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .reel-item {
            flex: 0 0 120px;
            cursor: pointer;
            text-align: center;
        }

        .reel-image-container {
            position: relative;
            width: 120px;
            height: 160px;
            overflow: hidden;
            border-radius: 10px;
            background: #000;
        }

        .reel-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.9;
        }

        .play-icon {
            transition: opacity 0.2s ease;
        }

        .reel-item:hover .play-icon {
            opacity: 1;
        }

        .reel-info p {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 2px;
        }

        .reel-info small {
            font-size: 0.75rem;
        }

        /* Reel Viewer Modal */
        #reelViewerModal .modal-content {
            background: #000;
            color: #fff;
            border: none;
        }

        #reelViewerModal .modal-header {
            border-bottom: none;
        }

        #reelViewerModal .modal-body {
            height: 90vh;
            padding: 0;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #reelViewerModal .reel-content {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .reel-actions {
            z-index: 10;
        }

        .reel-actions .btn {
            transition: transform 0.2s ease;
        }

        .reel-actions .btn:hover {
            transform: scale(1.1);
        }

        .btn-outline-primary {
            background-color: white;
            color: #007bff;
            border-radius: 8px;
            border: 1px solid #007bff;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .btn-outline-primary:hover {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
            transform: translateY(-1px);
        }

        .btn-outline-primary:active {
            transform: translateY(0);
        }

        @media (max-width: 576px) {
            .btn-outline-primary {
                font-size: 12px;
                padding: 6px 8px;
            }
        }

        .add-friend-btn.btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            cursor: not-allowed;
        }

        .friend-item {
            transition: opacity 0.5s ease;
        }

        /* Upload Animation Styles */
        .upload-animation-container {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2000;
            text-align: center;
        }

        .upload-circle {
            width: 80px;
            height: 80px;
            border: 6px solid #e0e4e8;
            border-top: 6px solid #1e90ff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        .upload-text {
            margin-top: 15px;
            font-size: 1.2rem;
            color: #333;
            font-family: 'Poppins', sans-serif;
        }

        .success-check {
            display: none;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #28a745;
            color: white;
            font-size: 40px;
            line-height: 80px;
            margin: 0 auto;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Your CSS remains unchanged */
        .friend-list::-webkit-scrollbar {
            width: 6px;
        }

        .friend-list::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .friend {
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .friend:hover {
            background: #f1f7ff;
        }

        .status-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            background: #28a745;
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .status-indicator.online {
            background: #28a745;
        }

        .status-indicator.offline {
            background: #6c757d;
        }

        .chat-window {
            transition: all 0.3s ease;
        }

        .chat-header {
            background: #0084ff;
        }

        .chat-body {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 10px;
            height: 380px;
            overflow-y: auto;
            background: #ECE5DD;
            /* WhatsApp-like background */
        }

        .chat-message {
            position: relative;
            max-width: 70%;
            margin: 5px 0;
            border-radius: 7.5px;
            font-size: 0.9rem;
            word-wrap: break-word;
            display: flex;
            clear: both;
            padding: 0;
            /* Remove padding from outer div */
        }

        .chat-message.sent {
            background: #DCF8C6;
            /* WhatsApp's light green for sent */
            color: #000;
            justify-content: flex-end;
            margin-left: auto;
            margin-right: 10px;
        }

        .chat-message.received {
            background: #FFFFFF;
            color: #000;
            margin-left: 10px;
            box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.13);
        }

        .message-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            /* Padding moved to wrapper */
            width: 100%;
        }

        .message-content {
            flex-grow: 1;
            padding-right: 20px;
            /* Space for three-dot menu */
        }

        .message-meta {
            position: absolute;
            bottom: 2px;
            right: 8px;
        }

        .message-meta small {
            font-size: 0.65rem;
            color: #666;
            display: flex;
            align-items: center;
        }

        .message-meta .ticks {
            margin-left: 3px;
            font-size: 0.8rem;
            vertical-align: middle;
        }

        .message-meta .ticks i {
            margin-left: 1px;
        }

        .message-meta .ticks.sent {
            color: #999;
            /* Grey for sent but unread */
        }

        .message-meta .ticks.read {
            color: #00AFEF;
            /* WhatsApp blue for read */
        }

        .message-options {
            position: absolute;
            top: 5px;
            /* Position at the top */
            right: 5px;
        }

        .three-dot-btn {
            background: none;
            border: none;
            font-size: 0.9rem;
            color: #666;
            padding: 0 5px;
            line-height: 1;
            cursor: pointer;
            opacity: 0;
            /* Hidden by default */
            transition: opacity 0.2s ease;
        }

        .chat-message:hover .three-dot-btn {
            opacity: 1;
            /* Show on hover */
        }

        .three-dot-btn:hover {
            color: #075E54;
            /* WhatsApp teal */
        }

        .dropdown-menu {
            background: #FFFFFF;
            border: 1px solid #E8E8E8;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 5px 0;
            min-width: 120px;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 5px 10px;
            font-size: 0.9rem;
            color: #333;
        }

        .dropdown-item i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .dropdown-item:hover {
            background: #F0F0F0;
            color: #075E54;
        }


        /* Chat Header */
        .chat-header {
            background: #075E54;
            /* WhatsApp's dark teal header color */
        }

        /* Chat Footer */
        .chat-footer {
            padding: 10px;
            background: #F0F0F0;
            /* WhatsApp-like light grey */
            border-radius: 0 0 15px 15px;
            border-top: 1px solid #D9D9D9;
        }

        /* WhatsApp-style Input */
        .whatsapp-input {
            border-radius: 20px;
            background: #FFFFFF;
            border: 1px solid #E8E8E8;
            padding: 8px 15px;
            font-size: 0.9rem;
            box-shadow: none;
            flex-grow: 1;
        }

        .whatsapp-input:focus {
            border-color: #075E54;
            /* WhatsApp teal */
            box-shadow: 0 0 5px rgba(7, 94, 84, 0.3);
            outline: none;
        }

        /* Icon Button Styling */
        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .btn-whatsapp {
            background: #075E54;
            /* WhatsApp teal */
            color: #FFFFFF;
            border: none;
        }

        .btn-whatsapp:hover {
            background: #064D45;
            /* Darker teal on hover */
            transform: scale(1.1);
        }

        /* Dropdown Menu Styling */
        .dropdown-menu {
            background: #FFFFFF;
            border: 1px solid #E8E8E8;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 5px 0;
            min-width: 150px;
        }

        .btn-whatsapp-dropdown {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 8px 15px;
            background: none;
            border: none;
            color: #333;
            font-size: 0.9rem;
            text-align: left;
            transition: background-color 0.2s ease;
        }

        .btn-whatsapp-dropdown:hover {
            background: #F0F0F0;
            color: #075E54;
        }

        .btn-whatsapp-dropdown i {
            margin-right: 10px;
            font-size: 1.1rem;
            color: #075E54;
            /* WhatsApp teal for icons */
        }

        /* Icon Styling */
        .btn-whatsapp i,
        .btn-whatsapp-dropdown i {
            font-size: 1.2rem;
            transition: color 0.2s ease;
        }

        .btn-whatsapp:hover i {
            color: #FFFFFF;
        }

        .btn-whatsapp-dropdown:hover i {
            color: #064D45;
            /* Darker teal on hover */
        }

        /* Media Preview */
        #mediaPreview {
            max-width: 100%;
            overflow-x: auto;
        }

        /* Emoji Picker Positioning */
        .emoji-picker {
            position: absolute;
            bottom: 60px;
            right: 10px;
            z-index: 1000;
            background: #FFFFFF;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .search-bar {
            position: relative;
        }

        /* .chat-footer input {
            border-radius: 20px;
        }

        .chat-footer button {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            padding: 0;
        } */

        .bg-gradient {
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
        }

        .story-actions {
            z-index: 10;
        }

        .story-grid {
            overflow-x: auto;
            white-space: nowrap;
        }

        .story-item {
            display: inline-block;
            width: 80px;
            text-align: center;
            cursor: pointer;
        }

        .story-image-container {
            position: relative;
            width: 60px;
            height: 60px;
            margin: 0 auto;
            overflow: hidden;
        }

        .story-image-container img,
        .story-image-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #1e90ff;
        }

        .upload-story-btn {
            width: 24px;
            height: 24px;
            font-size: 14px;
            line-height: 1;
        }

        #storyViewerModal .modal-content {
            background: #000;
            color: #fff;
            border: none;
        }

        #storyViewerModal .modal-header {
            border-bottom: none;
        }

        #storyViewerModal .modal-body {
            height: 90vh;
            padding: 0;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #storyViewerModal .story-content {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        #storyViewerModal .progress-container {
            display: flex;
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            gap: 5px;
            z-index: 10;
        }

        #storyViewerModal .progress-bar {
            flex: 1;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
            overflow: hidden;
        }

        #storyViewerModal .progress {
            height: 100%;
            background: #fff;
            width: 0;
            border-radius: 2px;
            transition: width linear;
        }

        #prevStoryBtn,
        #nextStoryBtn {
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        #prevStoryBtn:hover,
        #nextStoryBtn:hover {
            opacity: 1;
        }

        .story-actions .btn {
            transition: transform 0.2s ease;
        }

        .story-actions .btn:hover {
            transform: scale(1.1);
        }

        .like-btn {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
        }

        .like-btn i.fa-heart {
            transition: color 0.3s ease;
            margin-right: 5px;
        }

        .like-btn:hover i.fa-heart:not(.fas) {
            color: #ff4444;
        }

        .like-btn.liked i.fa-heart {
            color: #ff0000;
        }

        .like-btn.liked:hover i.fa-heart {
            color: #ff0000;
        }

        .like-btn.liked::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            background: rgba(255, 0, 0, 0.5);
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            animation: likeRipple 0.6s ease-out;
            pointer-events: none;
        }

        .like-btn .confirmation-icon {
            margin-left: 5px;
            color: #ff0000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .like-btn.liked .confirmation-icon {
            opacity: 1;
        }

        @keyframes likeRipple {
            0% {
                transform: translate(-50%, -50%) scale(0);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(2);
                opacity: 0;
            }
        }

        .post-header {
            position: relative;
        }

        .three-dot-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #666;
            padding: 0;
            line-height: 1;
        }

        .three-dot-btn:hover {
            color: #1e90ff;
        }

        .dropdown-menu {
            min-width: 100px;
        }

        .dropdown-item i {
            margin-right: 5px;
        }

        .profile-pic-link {
            text-decoration: none;
            color: inherit;
            display: inline-block;
        }

        .profile-pic-link:hover .profile-icon {
            border-color: #1e90ff;
        }

        .search-bar {
            position: relative;
        }

        .search-bar input {
            padding-left: 35px;
            width: 250px;
        }

        .search-bar i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        #searchResults {
            position: absolute;
            top: 100%;
            left: 0;
            background: #fff;
            border: 1px solid #e0e4e8;
            border-radius: 5px;
            width: 250px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .search-result-item {
            padding: 10px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eceef1;
            text-decoration: none;
            color: #333;
        }

        .search-result-item:hover {
            background: #f8f9fa;
        }

        .search-result-item img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .search-result-item span {
            font-size: 0.9rem;
        }

        .friend-request-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eceef1;
        }

        .friend-request-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .friend-request-actions {
            margin-left: auto;
        }

        .notification-dropdown .dropdown-item {
            padding: 8px 15px;
            font-size: 0.9rem;
        }

        .notification-dropdown .dropdown-item.unread {
            background: #f1f7ff;
        }

        .friend-section {
            margin-bottom: 20px;
        }

        .friend-grid {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding-bottom: 10px;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        .friend-grid::-webkit-scrollbar {
            height: 6px;
        }

        .friend-grid::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        .friend-item {
            background-color: rgba(230, 238, 243, 0.81);
            border-radius: 10px;
            flex: 0 0 auto;
            width: 100px;
            text-align: center;
            position: relative;
        }

        .friend-image-container {
            position: relative;
            width: 70px;
            height: 70px;
            margin: 0 auto;
            transition: transform 0.2s ease;
        }

        .friend-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .friend-item:hover .friend-image-container {
            transform: scale(1.05);
        }

        .friend-item p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #333;
        }

        .friend-item .add-friend-btn {
            display: block;
            margin: 5px auto 0;
            padding: 4px 8px;
            font-size: 0.75rem;
            background-color: #1e90ff;
            border: none;
            border-radius: 12px;
            color: white;
            transition: background-color 0.2s ease;
        }

        .friend-item .add-friend-btn:hover {
            background-color: #187bcd;
        }

        .friend-item .close-btn {
            position: absolute;
            top: 0;
            right: -5px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.8rem;
            color: #666;
            transition: all 0.2s ease;
        }

        .friend-item .close-btn:hover {
            background: rgba(255, 0, 0, 0.9);
            color: white;
        }

        /* new post 3 dot */

        /* post 3 dot end */
        .post-input-container {
            margin-bottom: 30px;
            max-width: 650px;
            margin-left: auto;
            margin-right: auto;
        }

        .post-input {
            background: #ffffff;
            border: 1px solid #e0e4e8;
            border-radius: 30px;
            padding: 14px 18px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .post-input:hover,
        .post-input:focus-within {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            border-color: #1e90ff;
        }

        .profile-icon {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
            transition: border-color 0.2s ease;
        }

        .post-input-field {
            background: transparent;
            border: none;
            outline: none;
            font-size: 1.05rem;
            color: #333;
            padding: 0 12px;
            flex-grow: 1;
            font-family: 'Poppins', sans-serif;
        }

        .post-input-field::placeholder {
            color: #999;
            font-style: italic;
        }

        .photo-btn {
            background: none;
            border: none;
            color: #6c757d;
            font-size: 1.3rem;
            padding: 0 5px;
            transition: color 0.2s ease;
        }

        .photo-btn:hover {
            color: #1e90ff;
        }

        .post-item {
            background: #ffffff;
            border: 1px solid #e0e4e8;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            max-width: 650px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        .post-item:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .post-media {
            width: 100%;
            max-height: 600px;
            object-fit: cover;
            margin-top: 20px;
            border-radius: 12px;
            display: block;
            overflow: hidden;
        }

        .post-actions {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #eceef1;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .post-actions .btn-link {
            color: #555;
            text-decoration: none;
            padding: 6px 12px;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .post-actions .btn-link:hover {
            color: #1e90ff;
        }

        .post-actions span {
            margin-left: 20px;
            color: #666;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .post-actions span i {
            margin-right: 5px;
        }

        .comment-section {
            border-top: 1px solid #eceef1;
            padding-top: 15px;
        }

        .comment-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .comment-item {
            align-items: flex-start;
        }

        .comment-content {
            border-radius: 15px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .comment-content strong {
            font-size: 0.95rem;
            color: #333;
        }

        .comment-content p {
            font-size: 0.9rem;
            margin-bottom: 2px;
            color: #333;
        }

        .comment-content small {
            font-size: 0.75rem;
        }

        .comment-input {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 8px 12px;
        }

        .comment-input-field {
            border: none;
            background: transparent;
            font-size: 0.9rem;
            flex-grow: 1;
            outline: none;
        }

        .comment-input-field:focus {
            outline: none;
            box-shadow: none;
        }

        .comment-input-field::placeholder {
            color: #999;
        }

        .comment-btn {
            color: #555;
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .comment-btn:hover {
            color: #1e90ff;
        }

        .comment-btn i {
            margin-right: 5px;
        }

        /* Scrollbar for comment list */
        .comment-list::-webkit-scrollbar {
            width: 6px;
        }

        .comment-list::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 3px;
        }

        @media (max-width: 767px) {

            .messages.fullscreen,
            .chat-window.fullscreen {
                position: fixed !important;
                width: 100vw !important;
                height: 100vh !important;
                top: 0 !important;
                left: 0 !important;
                margin: 0 !important;
                border-radius: 0 !important;
                z-index: 2000;
                box-shadow: none !important;
            }


        }

        @media (min-width: 768px) {
            .feed-area {
                padding: 35px;
            }

            .post-input-container,
            .post-item {
                width: 100%;
                max-width: 650px;
            }

            .post-item {
                margin-left: auto;
                margin-right: auto;
            }
        }

        .post-item p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #333;
            margin-bottom: 0;
            word-wrap: break-word;
        }

        .post-item .d-flex strong {
            font-size: 1.15rem;
            color: #222;
        }

        .post-item .d-flex small {
            font-size: 0.85rem;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <!-- Header -->
        <header class="header bg-primary text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="logo">SocialFusion</h1>
                <div class="d-flex align-items-center">
                    <div class="search-bar d-flex align-items-center position-relative">
                        <i class="uil uil-search"></i>
                        <input type="search" class="form-control" id="searchInput" placeholder="Search for creators...">
                        <div id="searchResults" class="position-absolute w-100 bg-white shadow" style="display: none;"></div>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="notification-section position-relative me-3 dropdown" style="cursor: pointer;">
                        <i class="uil uil-bell" style="font-size: 1.5rem;" data-bs-toggle="dropdown"></i>
                        <span class="notification-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo count($unread_notifications); ?></span>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <li class="dropdown-item-text fw-bold">Notifications</li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <?php foreach ($notifications as $notification): ?>
                                <li><a class="dropdown-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" href="#"><?php echo htmlspecialchars($notification['message']); ?></a></li>
                            <?php endforeach; ?>
                            <?php if (empty($notifications)): ?>
                                <li><a class="dropdown-item text-muted" href="#">No new notifications</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="profile-section dropdown">
                        <img src="<?php echo htmlspecialchars($profile['avatar_path']); ?>" class="rounded-circle me-2 profile-pic" alt="Profile" data-bs-toggle="dropdown" style="cursor: pointer;">
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="./profile.php?user_id=<?php echo htmlspecialchars($user_id); ?>">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="./login.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <div class="row g-0 main-content">
            <!-- Left Sidebar -->
            <div class="col-md-3 p-3 sidebar-left bg-light">
                <h5>Shortcuts</h5>
                <ul class="list-group">
                    <li class="list-group-item"><i class="fas fa-home me-2"></i> Home</li>
                    <li class="list-group-item"><i class="fas fa-users me-2"></i> Friends</li>
                    <a href="./videos.php">
                        <li class="list-group-item"><i class="fas fa-video me-2"></i> Videos</li>
                    </a>
                    <li class="list-group-item"><i class="fas fa-camera me-2"></i> Photos</li>
                </ul>
                <h5 class="mt-3">Groups</h5>
                <ul class="list-group">
                    <li class="list-group-item"><i class="fas fa-users me-2"></i> Design Enthusiasts</li>
                    <li class="list-group-item"><i class="fas fa-users me-2"></i> Travel Lovers</li>
                </ul>
            </div>

            <!-- Feed Area -->
            <div class="col-md-6 p-3 feed-area">
                <!-- Stories Section -->
                <div class="stories mb-3">
                    <h6>Stories</h6>
                    <div class="story-grid d-flex gap-2 overflow-auto">
                        <div class="story-item">
                            <div class="story-image-container position-relative">
                                <img src="<?php echo htmlspecialchars($profile['avatar_path']); ?>" class="rounded-circle" alt="Your Story">
                                <button class="upload-story-btn position-absolute bottom-0 end-0 bg-primary text-white rounded-circle border-0" data-bs-toggle="modal" data-bs-target="#storyModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <p class="text-center small">Add Story</p>
                        </div>
                        <?php foreach ($storyPreviews as $story): ?>
                            <div class="story-item" data-user-id="<?php echo htmlspecialchars($story['user_id']); ?>" data-story-id="<?php echo htmlspecialchars($story['id']); ?>">
                                <div class="story-image-container position-relative">
                                    <?php if ($story['media_type'] === 'image'): ?>
                                        <img src="<?php echo htmlspecialchars($story['media_path']); ?>" class="rounded-circle" alt="<?php echo htmlspecialchars($story['name'] ?: $story['username'] ?: 'Unknown'); ?> Story" onclick="showFullStory('<?php echo htmlspecialchars($story['user_id']); ?>', '<?php echo htmlspecialchars($story['id']); ?>')">
                                    <?php elseif ($story['media_type'] === 'video'): ?>
                                        <video muted class="rounded-circle" onclick="showFullStory('<?php echo htmlspecialchars($story['user_id']); ?>', '<?php echo htmlspecialchars($story['id']); ?>')">
                                            <source src="<?php echo htmlspecialchars($story['media_path']); ?>" type="<?php echo mime_content_type($story['media_path']); ?>">
                                        </video>
                                    <?php endif; ?>
                                </div>
                                <p class="text-center small"><?php echo htmlspecialchars($story['name'] ?: $story['username'] ?: 'Unknown'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Post Input -->
                <div class="posts-section mb-4">
                    <div class="post-input d-flex align-items-center bg-light p-2 rounded">
                        <a href="profile.php?user_id=<?php echo htmlspecialchars($user_id); ?>" class="profile-pic-link">
                            <img src="<?php echo htmlspecialchars($profile['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="profile-icon rounded-circle me-2" style="width: 40px; height: 40px;" alt="Profile">
                        </a>
                        <input type="text" class="post-input-field flex-grow-1 border-0" placeholder="What's on your mind, <?php echo htmlspecialchars($profile['name'] ?: $profile['username']); ?>?" data-bs-toggle="modal" data-bs-target="#postModal" data-post-type="post" readonly>
                        <button class="photo-btn btn p-0" data-bs-toggle="modal" data-bs-target="#postModal" data-post-type="post">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <div class="post-options row g-2 mt-2">
                        <div class="col-6 col-md-3">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#postReel" data-post-type="reel">
                                <a href="./reel_upload.php"> <i class="fa-solid fa-play me-2"></i>Post Reels </a>
                            </button>
                        </div>
                        <div class="col-6 col-md-3">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#postModal" data-post-type="video">
                                <i class="fa-solid fa-video me-2"></i>Post Video
                            </button>
                        </div>
                        <div class="col-6 col-md-3">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#postModal" data-post-type="special">
                                <i class="fa-solid fa-hands-asl-interpreting me-2"></i>Post Special
                            </button>
                        </div>
                        <div class="col-6 col-md-3">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#postModal" data-post-type="live">
                                <i class="fas fa-feather me-2"></i>Post Live
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reels Section -->
                <div class="reels-section mb-4">
                    <h5 class="mb-3">Reels</h5>
                    <div class="reel-grid d-flex gap-3" style="overflow-x: auto; padding-bottom: 10px;">
                        <?php foreach ($reels as $reel): ?>
                            <div class="reel-item" data-reel-id="<?php echo $reel['id']; ?>" onclick="showReel(<?php echo $reel['id']; ?>, '<?php echo htmlspecialchars($reel['video_path']); ?>', '<?php echo htmlspecialchars($reel['name'] ?: $reel['username']); ?>', '<?php echo htmlspecialchars($reel['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>', '<?php echo htmlspecialchars($reel['caption']); ?>')">
                                <div class="reel-image-container position-relative">
                                    <video class="reel-thumbnail" muted poster="<?php echo htmlspecialchars(str_replace('.mp4', '_thumb.jpg', $reel['video_path'])); ?>">
                                        <source src="<?php echo htmlspecialchars($reel['video_path']); ?>" type="video/mp4">
                                    </video>
                                    <div class="play-icon position-absolute top-50 start-50 translate-middle">
                                        <i class="fas fa-play-circle fa-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="reel-info text-center mt-2">
                                    <p class="small mb-0"><?php echo htmlspecialchars($reel['name'] ?: $reel['username'] ?: 'Unknown'); ?></p>
                                    <?php if ($reel['caption']): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($reel['caption'], 0, 20)) . (strlen($reel['caption']) > 20 ? '...' : ''); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted"><?php echo date('M d', strtotime($reel['created_at'])); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($reels)): ?>
                            <p class="text-muted text-center w-100">No reels available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Posts Section -->
                <div id="postsContainer">
                    <?php foreach ($posts as $post): ?>
                        <div class="post-item" data-post-id="<?php echo $post['id']; ?>">
                            <div class="post-header d-flex align-items-center mb-2 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <a href="othersprofile.php?user_id=<?php echo htmlspecialchars($post['user_id']); ?>" class="profile-pic-link">
                                        <img src="<?php echo htmlspecialchars($post['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="profile-icon rounded-circle me-2" style="width: 40px; height: 40px;" alt="Profile">
                                    </a>
                                    <div>
                                        <strong><?php echo htmlspecialchars($post['name'] ?: $post['username']); ?></strong>
                                        <small class="text-muted d-block time-ago" data-timestamp="<?php echo $post['created_at']; ?>"></small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="post-menu-btn btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="savePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['media_path']); ?>', '<?php echo $post['media_type']; ?>')"><i class="fas fa-save"></i> Save</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="sharePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['media_path']); ?>', '<?php echo $post['media_type']; ?>')"><i class="fas fa-share"></i> Share</a></li>
                                    </ul>
                                </div>
                            </div>
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                            <?php if ($post['media_path']): ?>
                                <?php if ($post['media_type'] === 'image'): ?>
                                    <img src="<?php echo htmlspecialchars($post['media_path']); ?>" class="post-media" alt="Post Media">
                                <?php elseif ($post['media_type'] === 'video'): ?>
                                    <video controls class="post-media">
                                        <source src="<?php echo htmlspecialchars($post['media_path']); ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="post-actions d-flex justify-content-between">
                                <button class="btn btn-link text-decoration-none like-btn <?php echo $post['user_liked'] ? 'liked' : ''; ?>" onclick="toggleStatusLike(<?php echo $post['id']; ?>, this)">
                                    <i class="fa-heart <?php echo $post['user_liked'] ? 'fas' : 'far'; ?>"></i>
                                    <span class="like-count"><?php echo $post['like_count']; ?></span>
                                    <i class="fas fa-check confirmation-icon"></i>
                                </button>
                                <button class="btn btn-link text-decoration-none comment-btn" onclick="toggleCommentSection(<?php echo $post['id']; ?>, this)">
                                    <span class="comment-count"><i class="far fa-comment"></i> <?php echo $post['comment_count']; ?></span>
                                </button>
                                <button class="btn btn-link text-decoration-none share-btn" onclick="sharePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['media_path']); ?>', '<?php echo $post['media_type']; ?>')">
                                    <span class="share-count"><i class="fas fa-share"></i></span>
                                </button>
                            </div>
                            <div class="comment-section mt-3" id="comment-section-<?php echo $post['id']; ?>" style="display: none;">
                                <div class="comment-list mb-3" id="comment-list-<?php echo $post['id']; ?>">
                                    <?php if (empty($comments[$post['id']])): ?>
                                        <p class="text-muted small">No comments yet.</p>
                                    <?php else: ?>
                                        <?php foreach ($comments[$post['id']] as $comment): ?>
                                            <div class="comment-item d-flex mb-2" data-comment-id="<?php echo $comment['id']; ?>">
                                                <a href="othersprofile.php?user_id=<?php echo htmlspecialchars($comment['user_id']); ?>">
                                                    <img src="<?php echo htmlspecialchars($comment['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="rounded-circle me-2" style="width: 32px; height: 32px;" alt="Profile">
                                                </a>
                                                <div class="comment-content bg-light p-2 rounded">
                                                    <strong><?php echo htmlspecialchars($comment['name'] ?: $comment['username']); ?></strong>
                                                    <p class="mb-0"><?php echo htmlspecialchars($comment['content']); ?></p>
                                                    <small class="text-muted time-ago" data-timestamp="<?php echo $comment['created_at']; ?>"></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="comment-input d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($profile['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="rounded-circle me-2" style="width: 32px; height: 32px;" alt="Profile">
                                    <form class="flex-grow-1 d-flex" onsubmit="postComment(<?php echo $post['id']; ?>, event)">
                                        <input type="text" class="form-control comment-input-field" placeholder="Write a comment..." required>
                                        <button type="submit" class="btn btn-link text-primary ms-2 p-0"><i class="fas fa-paper-plane"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Friend Suggestions -->
                <div class="add-friend card">
                    <div class="heading d-flex justify-content-between align-items-center p-3">
                        <h4 class="mb-0">People You May Know</h4>
                        <a href="#" class="text-primary small">See All</a>
                    </div>
                    <div class="friend-section p-3">
                        <div class="friend-grid">
                            <?php foreach ($friend_suggestions as $friend): ?>
                                <div class="friend-item" data-user-id="<?php echo $friend['id']; ?>">
                                    <button class="close-btn" onclick="removeFriend(this)"><i class="fas fa-times"></i></button>
                                    <div class="friend-image-container">
                                        <a href="othersprofile.php?user_id=<?php echo htmlspecialchars($friend['id']); ?>">
                                            <img src="<?php echo htmlspecialchars($friend['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" alt="<?php echo htmlspecialchars($friend['name'] ?: $friend['username']); ?>">
                                        </a>
                                    </div>
                                    <p><?php echo htmlspecialchars($friend['name'] ?: $friend['username']); ?></p>
                                    <button class="add-friend-btn btn btn-primary" data-user-id="<?php echo $friend['id']; ?>">Add Friend</button>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($friend_suggestions)): ?>
                                <p class="text-muted text-center w-100">No suggestions available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-md-3 p-3 sidebar-right bg-light position-sticky" style="top: 60px; z-index: 900;">
                <div class="messages card mb-3" id="NewMessage">
                    <div class="heading d-flex justify-content-between align-items-center p-3">
                        <h4 class="mb-0">Messages</h4>
                        <i class="uil uil-edit text-primary" style="cursor: pointer;" data-bs-toggle="tooltip" title="New Message"></i>
                    </div>
                    <div class="search-bar p-2">
                        <i class="uil uil-search text-muted"></i>
                        <input type="search" placeholder="Search friends" id="friend-search" class="form-control ps-4" onkeyup="filterFriends()">
                    </div>
                    <div class="friend-list p-2" style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($friends as $friend): ?>
                            <div class="friend d-flex align-items-center p-2" data-friend-id="<?php echo $friend['id']; ?>" onclick="openChat(<?php echo $friend['id']; ?>, '<?php echo htmlspecialchars($friend['name'] ?: $friend['username']); ?>', '<?php echo htmlspecialchars($friend['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>', true)">
                                <div class="profile-icon me-2 position-relative">
                                    <img src="<?php echo htmlspecialchars($friend['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="rounded-circle" alt="<?php echo htmlspecialchars($friend['name'] ?: $friend['username']); ?>">
                                    <div class="status-indicator online"></div>
                                </div>
                                <div class="friend-body flex-grow-1">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($friend['name'] ?: $friend['username']); ?></h5>
                                    <p class="text-muted small mb-0">Click to chat</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($friends)): ?>
                            <p class="text-muted text-center">No friends yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="friend-requests card mb-3">
                    <div class="heading d-flex justify-content-between align-items-center p-3">
                        <h4 class="mb-0">Friend Requests</h4>
                        <a href="#" class="text-primary small">See All</a>
                    </div>
                    <div class="request-list p-3">
                        <?php foreach ($friend_requests as $request): ?>
                            <div class="friend-request-item" data-request-id="<?php echo $request['id']; ?>">
                                <a href="othersprofile.php?user_id=<?php echo htmlspecialchars($request['sender_id']); ?>">
                                    <img src="<?php echo htmlspecialchars($request['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" alt="<?php echo htmlspecialchars($request['name'] ?: $request['username']); ?>">
                                </a>
                                <div>
                                    <strong><?php echo htmlspecialchars($request['name'] ?: $request['username']); ?></strong>
                                </div>
                                <div class="friend-request-actions">
                                    <button class="btn btn-sm btn-primary accept-friend-btn" data-request-id="<?php echo $request['id']; ?>">Accept</button>
                                    <button class="btn btn-sm btn-outline-secondary reject-friend-btn" data-request-id="<?php echo $request['id']; ?>">Reject</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($friend_requests)): ?>
                            <p class="text-muted text-center">No pending friend requests.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <!-- Story Upload Modal -->
        <div class="modal fade" id="storyModal" tabindex="-1" aria-labelledby="storyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="storyModalLabel">Add a Story</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        <form id="storyForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="upload_story" value="1">
                            <div class="mb-3">
                                <label for="storyMedia" class="form-label">Upload Story (Image or Video)</label>
                                <input type="file" class="form-control" id="storyMedia" name="story_media" accept="image/*,video/*" required>
                                <div id="storyPreview" class="mt-2"></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Story Viewer Modal -->
        <div class="modal fade" id="storyViewerModal" tabindex="-1" aria-labelledby="storyViewerLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content bg-black">
                    <div class="modal-header border-0">
                        <div class="d-flex align-items-center w-100">
                            <img id="storyUserAvatar" class="rounded-circle me-2" style="width: 40px; height: 40px;" alt="User">
                            <h5 class="modal-title text-white" id="storyViewerLabel"></h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="progress-container" id="progressContainer"></div>
                    <div class="modal-body">
                        <button id="prevStoryBtn" class="btn btn-outline-light position-absolute start-0 m-3" style="z-index: 11;" onclick="prevStory()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div id="storyContent" class="w-100 h-100 d-flex justify-content-center align-items-center"></div>
                        <button id="nextStoryBtn" class="btn btn-outline-light position-absolute end-0 m-3" style="z-index: 11;" onclick="nextStory()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <div class="story-actions position-absolute bottom-0 w-100 p-3 bg-gradient text-white">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm btn-outline-light p-1 like-btn" id="storyLikeBtn" onclick="toggleLike(currentStoryId, this)">
                                    <i class="far fa-heart"></i> <span class="like-count">0</span>
                                    <i class="fas fa-check confirmation-icon"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-light p-1" id="storyMessageBtn" data-bs-toggle="modal" data-bs-target="#messageModal" title="Send a message">
                                    <i class="far fa-comment"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-light p-1" id="storyShareBtn" onclick="shareStory(currentStoryId)">
                                    <i class="fas fa-share"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Modal -->
        <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="messageModalLabel">Send Message</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="storyMessageForm" onsubmit="sendStoryMessage(currentStoryId, event)">
                            <div class="mb-3">
                                <textarea class="form-control" id="messageText" rows="3" placeholder="Type your message..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reel Viewer Modal -->
        <div class="modal fade" id="reelViewerModal" tabindex="-1" aria-labelledby="reelViewerLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-white" id="reelViewerLabel">Reel's Title</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="reel-viewer text-center">
                            <video id="reelVideo" class="w-100" controls autoplay>
                                <source id="reelVideoSource" src="" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="reel-overlay position-absolute bottom-0 w-100 p-3 bg-gradient">
                                <div class="d-flex align-items-center">
                                    <img id="reelUserAvatar" src="Uploads/avatars/default.jpg" class="rounded-circle me-2" style="width: 40px; height: 40px;" alt="User Avatar">
                                    <div class="text-white text-start">
                                        <strong id="reelUsername"></strong>
                                    </div>
                                </div>
                                <div class="reel-actions d-flex justify-content-between mt-2">
                                    <button id="reelLikeBtn" class="btn btn-link text-white" onclick="toggleReelLike(currentReelId, this)">
                                        <i class="far fa-heart"></i> <span class="like-count">0</span>
                                    </button>
                                    <button class="btn btn-link text-white" onclick="openReelComments(currentReelId)">
                                        <i class="far fa-comment"></i> Comments
                                    </button>
                                    <button class="btn btn-link text-white" onclick="shareReel(currentReelId)">
                                        <i class="fas fa-share"></i> Share
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Post/Reel Modal -->
        <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="postModalLabel">Create a Post</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        <form id="postForm" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="create_post">
                            <div class="mb-3" id="postTextContainer">
                                <textarea class="form-control" id="postText" name="post_text" rows="3" placeholder="What's on your mind?"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="postMedia" class="form-label" id="mediaLabel">Upload Media</label>
                                <input type="file" class="form-control" id="postMedia" name="post_media" accept="image/*,video/*">
                                <small class="text-muted" id="mediaHelp">Supports images and videos (max 100MB).</small>
                                <div id="mediaPreview" class="mt-2"></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="postSubmitBtn">Post</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Camera Modal -->
        <div class="modal fade" id="cameraModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Capture Photo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <video id="cameraFeed" autoplay style="width: 100%;"></video>
                        <canvas id="cameraCanvas" style="display: none;"></canvas>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" onclick="capturePhoto()">Capture</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Window -->
        <div class="chat-window position-fixed bottom-0 end-0 m-3" id="chatWindow" style="display: none; width: 350px; height: 500px; background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); z-index: 1000;">
            <div class="chat-header d-flex align-items-center justify-content-between p-2 bg-primary text-white rounded-top" style="cursor: grab;">
                <div class="d-flex align-items-center">
                    <img id="chatAvatar" src="" class="rounded-circle me-2" style="width: 32px; height: 32px;" alt="Friend">
                    <h5 id="chatName" class="mb-0"></h5>
                </div>
                <div>
                    <button class="btn btn-sm text-white" onclick="startCall('audio')"><i class="fas fa-phone"></i></button>
                    <button class="btn btn-sm text-white" onclick="startCall('video')"><i class="fas fa-video"></i></button>
                    <button class="btn btn-sm text-white" onclick="closeChat()"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="chat-body p-3" id="chatMessages" style="height: 380px; overflow-y: auto; background: #ECE5DD;"></div>
            <div class="chat-footer p-2 border-top">
                <form id="chatForm" class="d-flex align-items-center gap-2" onsubmit="sendMessage(event)">
                    <div class="position-relative">
                        <button type="button" class="btn btn-icon btn-whatsapp" id="attachmentBtn" data-bs-toggle="dropdown">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-start" id="attachmentMenu">
                            <li><button type="button" class="dropdown-item btn-whatsapp-dropdown" onclick="document.getElementById('chatMedia').click();"><i class="fas fa-paperclip"></i> Attach File</button></li>
                            <li><button type="button" class="dropdown-item btn-whatsapp-dropdown" onclick="toggleEmojiPicker()"><i class="fas fa-smile"></i> Emoji</button></li>
                            <li><button type="button" class="dropdown-item btn-whatsapp-dropdown" onclick="startRecording()"><i class="fas fa-microphone"></i> Voice Message</button></li>
                            <li><button type="button" class="dropdown-item btn-whatsapp-dropdown" onclick="openCamera()"><i class="fas fa-camera"></i> Camera</button></li>
                            <li><button type="button" class="dropdown-item btn-whatsapp-dropdown" onclick="shareLocation()"><i class="fas fa-map-marker-alt"></i> Location</button></li>
                        </ul>
                    </div>
                    <input type="text" id="chatInput" class="form-control whatsapp-input" placeholder="Type a message...">
                    <button type="submit" class="btn btn-icon btn-whatsapp">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                    <input type="file" id="chatMedia" name="media" accept="image/*,video/*,audio/*" style="display: none;" onchange="previewMedia(event)">
                </form>
                <div id="mediaPreview" class="mt-2"></div>
                <div id="emojiPicker" class="emoji-picker" style="display: none;"></div>
            </div>
        </div>

        <!-- Mobile Footer Navbar -->
        <nav class="navbar fixed-bottom navbar-light bg-light d-md-none mobile-footer-navbar">
            <div class="container-fluid justify-content-around align-items-center">
                <a href="#index.php" class="nav-link text-center"><i class="fas fa-home fa-lg"></i><span class="d-block small">Home</span></a>
                <a href="./videos.php" class="nav-link text-center"><i class="fas fa-video fa-lg"></i><span class="d-block small">Videos</span></a>
                <a href="#" class="nav-link text-center post-icon" data-bs-toggle="modal" data-bs-target="#postModal" data-post-type="post"><i class="fas fa-plus-circle fa-2x text-primary"></i><span class="d-block small">Post</span></a>
                <a href="./othersprofile.php" class="nav-link text-center"><i class="fas fa-users fa-lg"></i><span class="d-block small">Friends</span></a>
                <a href="#" class="nav-link text-center" onclick="openMessagesFullScreen(event)"><i class="fas fa-envelope fa-lg"></i><span class="d-block small">Messages</span></a>
            </div>
        </nav>

        <!-- Upload Animation -->
        <div class="upload-animation-container" id="uploadAnimation">
            <div class="upload-circle" id="uploadSpinner"></div>
            <div class="success-check" id="successCheck"><i class="fas fa-check"></i></div>
            <div class="upload-text" id="uploadText">Uploading...</div>
        </div>
    </div>

    <!-- Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="scripts.js"></script>

    <script>
        let currentFriendId = null;
        let messagePolling = null;
        let mediaRecorder = null;
        let audioChunks = [];
        let emojiPicker = null;
        let isDragging = false;
        let currentX, currentY, initialX, initialY;
        let isMobileFullScreen = false;
        // Add Friend Button Handler for Friend Suggestions
        document.querySelectorAll('.add-friend-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                const friendItem = this.closest('.friend-item');
                const button = this;

                // Disable button to prevent multiple clicks
                button.disabled = true;
                button.textContent = 'Sending...';

                fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=add_friend&receiver_id=${userId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update button to show "Request Sent"
                            button.textContent = 'Request Sent';
                            button.classList.remove('btn-primary');
                            button.classList.add('btn-secondary');
                            button.disabled = true;

                            // Optionally, remove the suggestion after a delay
                            setTimeout(() => {
                                friendItem.style.transition = 'opacity 0.5s';
                                friendItem.style.opacity = '0';
                                setTimeout(() => friendItem.remove(), 500);
                            }, 2000);

                            Swal.fire('Success', 'Friend request sent!', 'success');

                            // Update notification count
                            updateNotificationCount();
                        } else {
                            button.textContent = 'Add Friend';
                            button.disabled = false;
                            Swal.fire('Error', data.error || 'Failed to send friend request', 'error');
                        }
                    })
                    .catch(error => {
                        button.textContent = 'Add Friend';
                        button.disabled = false;
                        Swal.fire('Error', 'Network error occurred', 'error');
                    });
            });
        });
        document.querySelectorAll('.accept-friend-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const requestId = this.getAttribute('data-request-id');
                const requestItem = this.closest('.friend-request-item');

                fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=accept_friend&request_id=${requestId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            requestItem.remove();
                            Swal.fire('Success', 'Friend request accepted!', 'success');

                            // Dynamically add new friend to the friend list
                            fetchFriendList();

                            // Update notification count
                            updateNotificationCount();
                        } else {
                            Swal.fire('Error', data.error || 'Failed to accept request', 'error');
                        }
                    })
                    .catch(error => Swal.fire('Error', 'Network error occurred', 'error'));
            });
        });

        // Mock data (simulating API response)
        const mockReels = [{
                id: 1,
                video_path: "https://sample-videos.com/video321/mp4/720/big_buck_bunny_720p_1mb.mp4",
                name: "John Doe",
                username: "johndoe",
                avatar_path: "https://via.placeholder.com/50",
                created_at: "2025-07-01T10:00:00Z"
            },
            {
                id: 2,
                video_path: "https://sample-videos.com/video321/mp4/720/big_buck_bunny_720p_2mb.mp4",
                name: "Jane Smith",
                username: "janesmith",
                avatar_path: "https://via.placeholder.com/50",
                created_at: "2025-07-02T12:00:00Z"
            },
            {
                id: 3,
                video_path: "https://sample-videos.com/video321/mp4/720/big_buck_bunny_720p_5mb.mp4",
                username: "cooluser",
                avatar_path: "https://via.placeholder.com/50",
                created_at: "2025-07-03T15:00:00Z"
            }
        ];

        // Function to format date as "Mmm dd"
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            });
        }

        // Function to populate reels
        function populateReels(reels) {
            const reelGrid = document.querySelector('.reel-grid');
            reelGrid.innerHTML = ''; // Clear existing content

            if (reels.length === 0) {
                reelGrid.innerHTML = '<p class="text-muted text-center w-100">No reels available.</p>';
                return;
            }

            reels.forEach(reel => {
                const reelItem = document.createElement('div');
                reelItem.className = 'reel-item';
                reelItem.dataset.reelId = reel.id;
                reelItem.onclick = () => showReel(
                    reel.id,
                    reel.video_path,
                    reel.name || reel.username || 'Unknown',
                    reel.avatar_path || 'https://via.placeholder.com/50'
                );

                reelItem.innerHTML = `
            <div class="reel-image-container position-relative">
                <video class="reel-thumbnail" muted>
                    <source src="${reel.video_path}" type="video/mp4">
                </video>
                <div class="play-icon position-absolute top-50 start-50 translate-middle">
                    <i class="fas fa-play-circle fa-2x text-white"></i>
                </div>
            </div>
            <div class="reel-info text-center mt-2">
                <p class="small mb-0">${reel.name || reel.username || 'Unknown'}</p>
                <small class="text-muted">${formatDate(reel.created_at)}</small>
            </div>
        `;

                reelGrid.appendChild(reelItem);
            });
        }

        // Function to handle reel click
        // function showReel(id, videoPath, name, avatarPath) {
        //     console.log(`Playing reel ${id}: ${videoPath}`);
        //     console.log(`User: ${name}, Avatar: ${avatarPath}`);
        //     // Add logic to play video (e.g., open modal with video player)
        //     alert(`Playing reel by ${name}`);
        // }

        // // Fetch reels (replace with real API call if available)
        // function fetchReels() {
        //     // Simulate API call with mock data
        //     setTimeout(() => {
        //         populateReels(mockReels);
        //     }, 500);
        // }
        function showReel(reelId, videoPath, username, avatarPath, caption) {
            currentReelId = reelId;
            document.getElementById('reelVideoSource').src = videoPath;
            document.getElementById('reelVideo').load();
            document.getElementById('reelUsername').textContent = username;
            document.getElementById('reelUserAvatar').src = avatarPath;
            document.getElementById('reelViewerLabel').textContent = caption || username + "'s Reel";
            // Fetch like count and status
            fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=get_reel_info&reel_id=${reelId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('reelLikeBtn').querySelector('.like-count').textContent = data.like_count;
                        document.getElementById('reelLikeBtn').classList.toggle('liked', data.user_liked);
                        document.getElementById('reelLikeBtn').querySelector('i').classList.toggle('fas', data.user_liked);
                        document.getElementById('reelLikeBtn').querySelector('i').classList.toggle('far', !data.user_liked);
                    }
                });
            new bootstrap.Modal(document.getElementById('reelViewerModal')).show();
        }
        // reel end

        // Initialize
        document.addEventListener('DOMContentLoaded', fetchReels);
        // Format timestamps as "X minutes ago"
        function timeAgo(date) {
            const now = new Date();
            const seconds = Math.floor((now - new Date(date)) / 1000);
            if (seconds < 60) return 'Just now';
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return `${minutes} minute${minutes === 1 ? '' : 's'} ago`;
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `${hours} hour${hours === 1 ? '' : 's'} ago`;
            const days = Math.floor(hours / 24);
            return `${days} day${days === 1 ? '' : 's'} ago`;
        }

        // Update all timestamps on page load
        document.querySelectorAll('.time-ago').forEach(element => {
            const timestamp = element.getAttribute('data-timestamp');
            element.textContent = timeAgo(timestamp);
        });
        // post comment toggle
        function toggleCommentSection(postId, button) {
            const commentSection = document.getElementById(`comment-section-${postId}`);
            const commentInput = commentSection.querySelector('.comment-input-field');

            // Toggle the visibility of the comment section
            if (commentSection.style.display === 'none' || commentSection.style.display === '') {
                commentSection.style.display = 'block';
                // Focus the comment input field
                commentInput.focus();
            } else {
                commentSection.style.display = 'none';
            }
        }

        function sharePost(postId, mediaPath, mediaType) {
            // Generate a shareable link (replace with your actual URL structure)
            const shareUrl = `${window.location.origin}/post.php?id=${postId}`;

            // Copy the link to the clipboard
            navigator.clipboard.writeText(shareUrl).then(() => {
                // Update share count (client-side, for demo purposes)
                const shareCountElement = document.querySelector(`.post-item[data-post-id="${postId}"] .share-count`);
                if (shareCountElement) {
                    let currentCount = parseInt(shareCountElement.textContent) || 0;
                    shareCountElement.textContent = currentCount + 1;
                }
                // Show a confirmation (e.g., alert or toast)
                alert('Link copied to clipboard! You can share it anywhere.');
            }).catch(err => {
                console.error('Failed to copy link:', err);
                alert('Failed to copy link. Please try again.');
            });
        }

        // Post a comment
        async function postComment(postId, event) {
            event.preventDefault();
            const form = event.target;
            const input = form.querySelector('.comment-input-field');
            const content = input.value.trim();
            if (!content) return;

            try {
                const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'post_comment',
                        post_id: postId,
                        content: content
                    })
                });
                const result = await response.json();
                if (result.success) {
                    const comment = result.comment;
                    const commentList = document.getElementById(`comment-list-${postId}`);
                    const noComments = commentList.querySelector('.text-muted');
                    if (noComments) noComments.remove();

                    const commentItem = document.createElement('div');
                    commentItem.className = 'comment-item d-flex mb-2';
                    commentItem.setAttribute('data-comment-id', comment.id);
                    commentItem.innerHTML = `
                        <a href="othersprofile.php?user_id=${comment.user_id}">
                            <img src="${comment.avatar_path || 'Uploads/avatars/default.jpg'}" class="rounded-circle me-2" style="width: 32px; height: 32px;" alt="Profile">
                        </a>
                        <div class="comment-content bg-light p-2 rounded">
                            <strong>${comment.name || comment.username}</strong>
                            <p class="mb-0">${comment.content}</p>
                            <small class="text-muted time-ago" data-timestamp="${comment.created_at}">${timeAgo(comment.created_at)}</small>
                        </div>
                    `;
                    commentList.insertBefore(commentItem, commentList.firstChild);
                    input.value = '';

                    // Update comment count
                    const commentCountSpan = document.querySelector(`.post-item[data-post-id="${postId}"] .comment-count`);
                    if (commentCountSpan) {
                        const currentCount = parseInt(commentCountSpan.textContent.match(/\d+/)[0]);
                        commentCountSpan.innerHTML = `<i class="far fa-comment"></i> ${currentCount + 1}`;
                    }
                } else {
                    console.error('Comment error:', result.error);
                    alert(result.error || 'Failed to post comment');
                }
            } catch (error) {
                console.error('Error posting comment:', error);
                alert('An error occurred while posting the comment: ' + error.message);
            }
        }

        // Open Messages in full-screen mode on mobile
        function openMessagesFullScreen(event) {
            event.preventDefault();
            const messagesSection = document.getElementById('NewMessage');
            const chatWindow = document.getElementById('chatWindow');

            if (window.innerWidth < 768) { // Mobile view
                messagesSection.classList.add('fullscreen');
                document.querySelector('.main-content').style.display = 'none';
                messagesSection.style.display = 'block';
                isMobileFullScreen = true;

                // Ensure chat window is hidden until a friend is selected
                chatWindow.style.display = 'none';
                chatWindow.classList.remove('fullscreen');
            }
        }

        // Open chat (modified to handle mobile full-screen)
        function openChat(friendId, friendName, friendAvatar, fromMobile = false) {
            currentFriendId = friendId;
            const chatWindow = document.getElementById('chatWindow');
            const messagesSection = document.getElementById('NewMessage');
            document.getElementById('chatName').textContent = friendName;
            document.getElementById('chatAvatar').src = friendAvatar;

            if (fromMobile && window.innerWidth < 768) {
                // Full-screen chat on mobile
                chatWindow.classList.add('fullscreen');
                messagesSection.classList.remove('fullscreen');
                messagesSection.style.display = 'none';
                document.querySelector('.main-content').style.display = 'none';
                chatWindow.style.display = 'block';
                isMobileFullScreen = true;
            } else {
                // Desktop view
                chatWindow.classList.remove('fullscreen');
                messagesSection.style.display = 'block';
                chatWindow.style.display = 'block';
                resetChatPosition();
                isMobileFullScreen = false;
            }

            loadMessages(friendId);
            if (messagePolling) clearInterval(messagePolling);
            messagePolling = setInterval(() => loadMessages(friendId), 2000);
        }

        function minimizeChat() {
            const chatWindow = document.getElementById('chatWindow');
            if (isMobileFullScreen) {
                closeChat(); // On mobile, minimize acts as close
            } else {
                chatWindow.style.height = '50px';
                chatWindow.querySelector('.chat-body').style.display = 'none';
                chatWindow.querySelector('.chat-footer').style.display = 'none';
            }
        }

        function closeChat() {
            const chatWindow = document.getElementById('chatWindow');
            const messagesSection = document.getElementById('NewMessage');
            chatWindow.style.display = 'none';
            chatWindow.style.height = '500px';
            chatWindow.querySelector('.chat-body').style.display = 'block';
            chatWindow.querySelector('.chat-footer').style.display = 'block';
            chatWindow.classList.remove('fullscreen');

            if (isMobileFullScreen) {
                messagesSection.classList.add('fullscreen');
                messagesSection.style.display = 'block';
            } else {
                document.querySelector('.main-content').style.display = 'flex';
                messagesSection.style.display = 'block';
            }

            currentFriendId = null;
            document.getElementById('chatMessages').innerHTML = '';
            document.getElementById('mediaPreview').innerHTML = '';
            if (messagePolling) clearInterval(messagePolling);
            // Do not reset isMobileFullScreen here to maintain state
        }

        function loadMessages(friendId) {
            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=get_messages&friend_id=${friendId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const chatBody = document.getElementById('chatMessages');
                        const lastScrollHeight = chatBody.scrollHeight;
                        chatBody.innerHTML = '';

                        data.messages.forEach(msg => {
                            const messageDiv = document.createElement('div');
                            messageDiv.classList.add('chat-message');
                            messageDiv.classList.add(msg.sender_id == <?php echo $user_id; ?> ? 'sent' : 'received');
                            messageDiv.dataset.messageId = msg.id;

                            let content = `
                    <div class="message-wrapper">
                        <div class="message-content">
                `;
                            if (msg.message) content += `<div>${msg.message}</div>`;
                            if (msg.media_path) {
                                if (msg.media_type === 'image') {
                                    content += `<img src="${msg.media_path}" style="max-width: 200px; border-radius: 10px; margin-top: 5px;" onclick="viewFullMedia('${msg.media_path}')">`;
                                } else if (msg.media_type === 'video') {
                                    content += `<video src="${msg.media_path}" controls style="max-width: 200px; border-radius: 10px; margin-top: 5px;"></video>`;
                                } else {
                                    content += `<a href="${msg.media_path}" target="_blank" class="d-flex align-items-center" style="margin-top: 5px; text-decoration: none;">
                                    <i class="fas fa-file me-2"></i> Attached File
                                </a>`;
                                }
                            }
                            content += `</div>`;

                            // Timestamp and ticks
                            const timestamp = new Date(msg.created_at).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: true
                            });
                            content += `<div class="message-meta">`;
                            content += `<small>${timestamp}`;
                            if (msg.sender_id == <?php echo $user_id; ?>) {
                                const tickClass = msg.is_read ? 'read' : 'sent';
                                content += `<span class="ticks ${tickClass}">`;
                                content += msg.is_read ?
                                    `<i class="fas fa-check"></i>` :
                                    `<i class="fas fa-check"></i>`;
                                content += `</span>`;
                            }
                            content += `</small>`;
                            content += `</div>`;

                            // Three-dot menu at the top-right
                            content += `
                    <div class="dropdown message-options">
                        <button class="three-dot-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="deleteMessage(${msg.id}, this); return false;"><i class="fas fa-trash"></i> Delete</a></li>
                            <li><a class="dropdown-item" href="#" onclick="shareMessage('${msg.media_path || msg.message}', '${msg.media_type || 'text'}'); return false;"><i class="fas fa-share"></i> Share</a></li>
                            <li><a class="dropdown-item" href="#" onclick="saveMessage('${msg.media_path}', '${msg.media_type}'); return false;"><i class="fas fa-download"></i> Save</a></li>
                        </ul>
                    </div>
                    </div>
                `;

                            messageDiv.innerHTML = content;
                            chatBody.appendChild(messageDiv);
                        });

                        if (chatBody.scrollTop + chatBody.clientHeight >= lastScrollHeight - 10) {
                            chatBody.scrollTop = chatBody.scrollHeight;
                        }
                    } else {
                        Swal.fire('Error', data.error || 'Failed to load messages', 'error');
                    }
                })
                .catch(error => Swal.fire('Error', 'Network error occurred', 'error'));
        }

        // Ensure Bootstrap dropdown works with the attachment button
        document.addEventListener('DOMContentLoaded', function() {
            const attachmentBtn = document.getElementById('attachmentBtn');
            const attachmentMenu = document.getElementById('attachmentMenu');

            if (attachmentBtn && attachmentMenu) {
                // Bootstrap dropdown initialization
                new bootstrap.Dropdown(attachmentBtn);

                // Close dropdown when an item is clicked
                attachmentMenu.querySelectorAll('.btn-whatsapp-dropdown').forEach(item => {
                    item.addEventListener('click', function() {
                        bootstrap.Dropdown.getInstance(attachmentBtn).hide();
                    });
                });
            }
        });

        function deleteMessage(messageId, element) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This message will be deleted permanently.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=delete_message&message_id=${messageId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const messageDiv = element.closest('.chat-message');
                                messageDiv.remove();
                                Swal.fire('Deleted!', 'The message has been deleted.', 'success');
                            } else {
                                Swal.fire('Error', data.error || 'Failed to delete message', 'error');
                            }
                        })
                        .catch(error => Swal.fire('Error', 'Network error occurred', 'error'));
                }
            });
        }

        function shareMessage(content, type) {
            if (!content) return Swal.fire('Error', 'No content to share', 'error');

            if (type === 'text') {
                navigator.clipboard.writeText(content)
                    .then(() => Swal.fire('Success', 'Message copied to clipboard!', 'success'))
                    .catch(() => Swal.fire('Error', 'Failed to copy message', 'error'));
            } else {
                fetch(content)
                    .then(response => response.blob())
                    .then(blob => {
                        const file = new File([blob], `shared.${type === 'image' ? 'jpg' : type === 'video' ? 'mp4' : 'webm'}`, {
                            type: blob.type
                        });
                        const shareData = {
                            files: [file],
                            title: 'Shared from SocialFusion Chat'
                        };
                        if (navigator.canShare && navigator.canShare({
                                files: [file]
                            })) {
                            navigator.share(shareData)
                                .catch(() => fallbackShare(content));
                        } else {
                            fallbackShare(content);
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Failed to fetch media', 'error'));
            }
        }

        function saveMessage(mediaPath, mediaType) {
            if (!mediaPath) return Swal.fire('Error', 'No media to save', 'error');

            fetch(mediaPath)
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `message.${mediaType === 'image' ? 'jpg' : mediaType === 'video' ? 'mp4' : 'webm'}`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                    Swal.fire('Success', 'Media saved!', 'success');
                })
                .catch(() => Swal.fire('Error', 'Failed to save media', 'error'));
        }

        function fallbackShare(content) {
            navigator.clipboard.writeText(content)
                .then(() => Swal.fire('Success', 'Media URL copied!', 'success'))
                .catch(() => Swal.fire('Error', 'Failed to copy URL', 'error'));
        }

        function sendMessage(event) {
            event.preventDefault();
            if (!currentFriendId) return;

            const messageInput = document.getElementById('chatInput');
            const mediaInput = document.getElementById('chatMedia');
            const message = messageInput.value.trim();
            const formData = new FormData();

            formData.append('action', 'send_message');
            formData.append('friend_id', currentFriendId);
            if (message) formData.append('message', message);
            if (mediaInput.files[0]) formData.append('media', mediaInput.files[0]);

            if (!message && !mediaInput.files[0]) return;

            fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        mediaInput.value = '';
                        document.getElementById('mediaPreview').innerHTML = '';
                        loadMessages(currentFriendId);
                    } else {
                        Swal.fire('Error', data.error || 'Failed to send message', 'error');
                    }
                })
                .catch(error => Swal.fire('Error', 'Network error occurred', 'error'));
        }

        function previewMedia(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('mediaPreview');
            preview.innerHTML = '';
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    if (file.type.startsWith('image/')) preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100px;">`;
                    else if (file.type.startsWith('video/')) preview.innerHTML = `<video src="${e.target.result}" controls style="max-width: 100px;"></video>`;
                    else if (file.type.startsWith('audio/')) preview.innerHTML = `<audio src="${e.target.result}" controls></audio>`;
                };
                reader.readAsDataURL(file);
            }
        }

        function startCall(type) {
            Swal.fire('Info', `${type === 'audio' ? 'Audio' : 'Video'} call requires WebRTC integration.`, 'info');
        }

        function shareLocation() {
            navigator.geolocation.getCurrentPosition(pos => {
                const location = {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude
                };
                fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `action=send_message&friend_id=${currentFriendId}&location=${encodeURIComponent(JSON.stringify(location))}`
                    })
                    .then(response => response.json())
                    .then(data => data.success && loadMessages(currentFriendId));
            }, () => Swal.fire('Error', 'Location access denied', 'error'));
        }

        function openMap(lat, lng) {
            window.open(`https://maps.google.com/?q=${lat},${lng}`, '_blank');
        }

        function startRecording() {
            navigator.mediaDevices.getUserMedia({
                    audio: true
                })
                .then(stream => {
                    mediaRecorder = new MediaRecorder(stream);
                    audioChunks = [];
                    mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
                    mediaRecorder.onstop = () => {
                        const audioBlob = new Blob(audioChunks, {
                            type: 'audio/webm'
                        });
                        const formData = new FormData();
                        formData.append('action', 'send_message');
                        formData.append('friend_id', currentFriendId);
                        formData.append('media', audioBlob, 'voice_message.webm');
                        fetch(window.location.href, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => data.success && loadMessages(currentFriendId));
                        stream.getTracks().forEach(track => track.stop());
                    };
                    mediaRecorder.start();
                    Swal.fire({
                        title: 'Recording...',
                        text: 'Click OK to stop',
                        showConfirmButton: true
                    }).then(() => mediaRecorder.stop());
                });
        }

        function openCamera() {
            const video = document.getElementById('cameraFeed');
            navigator.mediaDevices.getUserMedia({
                    video: true
                })
                .then(stream => {
                    video.srcObject = stream;
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('cameraModal')).show();
                });
        }

        function capturePhoto() {
            const video = document.getElementById('cameraFeed');
            const canvas = document.getElementById('cameraCanvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob(blob => {
                const formData = new FormData();
                formData.append('action', 'send_message');
                formData.append('friend_id', currentFriendId);
                formData.append('media', blob, 'photo.jpg');
                fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) loadMessages(currentFriendId);
                        bootstrap.Modal.getInstance(document.getElementById('cameraModal')).hide();
                        video.srcObject.getTracks().forEach(track => track.stop());
                    });
            }, 'image/jpeg');
        }

        function likeMessage(messageId, element) {
            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=like_message&message_id=${messageId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) element.closest('.chat-message').classList.add('liked');
                });
        }

        function toggleEmojiPicker() {
            const pickerDiv = document.getElementById('emojiPicker');
            if (!emojiPicker) {
                emojiPicker = new EmojiMart.Picker({
                    onEmojiSelect: emoji => {
                        document.getElementById('chatInput').value += emoji.native;
                        pickerDiv.style.display = 'none';
                    }
                });
                pickerDiv.appendChild(emojiPicker);
            }
            pickerDiv.style.display = pickerDiv.style.display === 'none' ? 'block' : 'none';
        }

        function viewFullMedia(mediaPath) {
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-black">
                        <div class="modal-header border-0">
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${mediaPath}" style="max-width: 100%; max-height: 80vh;">
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            modal.addEventListener('hidden.bs.modal', () => modal.remove());
        }
        // Dragging functionality (desktop only)
        const chatHeader = document.querySelector('.chat-header');
        const chatWindow = document.getElementById('chatWindow');

        chatHeader.addEventListener('mousedown', startDragging);
        document.addEventListener('mousemove', drag);
        document.addEventListener('mouseup', stopDragging);

        function startDragging(e) {
            if (window.innerWidth >= 768 && !isMobileFullScreen) {
                initialX = e.clientX - currentX;
                initialY = e.clientY - currentY;
                isDragging = true;
                chatHeader.style.cursor = 'grabbing';
            }
        }

        function drag(e) {
            if (isDragging) {
                e.preventDefault();
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;
                chatWindow.style.right = 'unset';
                chatWindow.style.bottom = 'unset';
                chatWindow.style.left = `${currentX}px`;
                chatWindow.style.top = `${currentY}px`;
            }
        }

        function stopDragging() {
            isDragging = false;
            chatHeader.style.cursor = 'grab';
        }

        function resetChatPosition() {
            currentX = window.innerWidth - chatWindow.offsetWidth - 20;
            currentY = window.innerHeight - chatWindow.offsetHeight - 20;
            chatWindow.style.left = `${currentX}px`;
            chatWindow.style.top = `${currentY}px`;
            chatWindow.style.right = 'unset';
            chatWindow.style.bottom = 'unset';
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth < 768 && isMobileFullScreen) {
                const messagesSection = document.getElementById('NewMessage');
                const chatWindow = document.getElementById('chatWindow');
                if (chatWindow.style.display === 'block') {
                    chatWindow.classList.add('fullscreen');
                    messagesSection.style.display = 'none';
                } else {
                    messagesSection.classList.add('fullscreen');
                    messagesSection.style.display = 'block';
                }
                document.querySelector('.main-content').style.display = 'none';
            } else {
                const messagesSection = document.getElementById('NewMessage');
                messagesSection.classList.remove('fullscreen');
                chatWindow.classList.remove('fullscreen');
                chatWindow.style.width = '350px';
                chatWindow.style.height = '500px';
                resetChatPosition();
                document.querySelector('.main-content').style.display = 'flex';
                isMobileFullScreen = false;
            }
        });

        // Initial chat position
        resetChatPosition();

        // Filter friends
        function filterFriends() {
            const searchValue = document.getElementById('friend-search').value.toLowerCase();
            const friends = document.querySelectorAll('.friend-list .friend');
            friends.forEach(friend => {
                const friendName = friend.querySelector('h5').textContent.toLowerCase();
                friend.style.display = friendName.includes(searchValue) ? 'flex' : 'none';
            });
        }
        // close message
        const storiesByUser = <?php echo $storiesByUserJson; ?>;
        let currentUserId = null;
        let currentStoryIndex = 0;
        let currentStoryId = null;
        let storyTimer = null;
        const STORY_DURATION = 5000;

        const likedStories = new Set(<?php echo json_encode(array_column(array_filter($allStories, fn($s) => $s['user_liked']), 'id')); ?>);
        const likedPosts = new Set(<?php echo json_encode(array_column(array_filter($posts, fn($p) => $p['user_liked']), 'id')); ?>);

        // Search Functionality
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length < 1) {
                searchResults.style.display = 'none';
                searchResults.innerHTML = '';
                return;
            }

            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=search_users&query=${encodeURIComponent(query)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        searchResults.innerHTML = '';
                        if (data.results.length > 0) {
                            data.results.forEach(user => {
                                const resultItem = document.createElement('a');
                                resultItem.href = `othersprofile.php?user_id=${user.id}`;
                                resultItem.classList.add('search-result-item');
                                resultItem.innerHTML = `
                                <img src="${user.avatar_path || 'uploads/avatars/default.jpg'}" alt="${user.name || user.username}">
                                <span>${user.name || user.username}</span>
                            `;
                                searchResults.appendChild(resultItem);
                            });
                            searchResults.style.display = 'block';
                        } else {
                            searchResults.innerHTML = '<div class="p-2 text-muted">No results found</div>';
                            searchResults.style.display = 'block';
                        }
                    } else {
                        Swal.fire('Error', data.error || 'Failed to search', 'error');
                    }
                })
                .catch(error => Swal.fire('Error', 'Network error occurred', 'error'));
        });

        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
                searchResults.style.display = 'none';
            }
        });

        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length > 0) {
                searchResults.style.display = 'block';
            }
        });

        // Story Functions
        function showFullStory(userId, storyId) {
            currentUserId = userId;
            const userStories = storiesByUser[userId];
            if (!userStories || userStories.length === 0) {
                Swal.fire('Error', 'No stories found for this user', 'error');
                return;
            }

            currentStoryIndex = userStories.findIndex(s => s.id === storyId.toString());
            if (currentStoryIndex === -1) currentStoryIndex = 0;

            displayStory();
            const modal = new bootstrap.Modal(document.getElementById('storyViewerModal'));
            modal.show();
        }

        function displayStory() {
            const userStories = storiesByUser[currentUserId];
            if (!userStories || currentStoryIndex < 0 || currentStoryIndex >= userStories.length) {
                bootstrap.Modal.getInstance(document.getElementById('storyViewerModal')).hide();
                return;
            }

            const story = userStories[currentStoryIndex];
            currentStoryId = story.id;

            document.getElementById('storyViewerLabel').textContent = `${story.name}'s Story`;
            document.getElementById('storyUserAvatar').src = story.avatar_path;

            const contentDiv = document.getElementById('storyContent');
            contentDiv.innerHTML = '';
            if (story.media_type === 'image') {
                const img = document.createElement('img');
                img.src = story.media_path;
                img.classList.add('story-content');
                img.onerror = () => Swal.fire('Error', 'Failed to load image', 'error');
                contentDiv.appendChild(img);
            } else if (story.media_type === 'video') {
                const video = document.createElement('video');
                video.src = story.media_path;
                video.classList.add('story-content');
                video.autoplay = true;
                video.controls = false;
                video.onerror = () => Swal.fire('Error', 'Failed to load video', 'error');
                video.onended = () => nextStory();
                contentDiv.appendChild(video);
            }

            const likeBtn = document.getElementById('storyLikeBtn');
            const countSpan = likeBtn.querySelector('.like-count');
            const heartIcon = likeBtn.querySelector('i.fa-heart');
            countSpan.textContent = story.like_count;
            if (likedStories.has(story.id)) {
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas', 'text-danger');
                likeBtn.classList.add('liked');
            } else {
                heartIcon.classList.remove('fas', 'text-danger');
                heartIcon.classList.add('far');
                likeBtn.classList.remove('liked');
            }

            setupProgressBars(userStories.length);
            startProgress();
        }

        function setupProgressBars(storyCount) {
            const container = document.getElementById('progressContainer');
            container.innerHTML = '';
            for (let i = 0; i < storyCount; i++) {
                const bar = document.createElement('div');
                bar.classList.add('progress-bar');
                const progress = document.createElement('div');
                progress.classList.add('progress');
                bar.appendChild(progress);
                container.appendChild(bar);
            }
        }

        function startProgress() {
            clearInterval(storyTimer);
            const progressBars = document.querySelectorAll('#progressContainer .progress');
            progressBars.forEach((bar, index) => {
                bar.style.width = index < currentStoryIndex ? '100%' : '0%';
                bar.style.transition = 'none';
            });

            const currentBar = progressBars[currentStoryIndex];
            currentBar.style.transition = `width ${STORY_DURATION}ms linear`;
            currentBar.style.width = '100%';

            storyTimer = setTimeout(() => nextStory(), STORY_DURATION);
        }

        function prevStory() {
            clearInterval(storyTimer);
            currentStoryIndex--;
            if (currentStoryIndex < 0) currentStoryIndex = 0;
            displayStory();
        }

        function nextStory() {
            clearInterval(storyTimer);
            currentStoryIndex++;
            displayStory();
        }

        function toggleLike(storyId, button) {
            if (!storyId) return;

            const heartIcon = button.querySelector('i.fa-heart');
            const countSpan = button.querySelector('.like-count');
            const isLiked = likedStories.has(storyId);

            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=toggle_story_like&story_id=${storyId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.isLiked) {
                            likedStories.add(storyId);
                            heartIcon.classList.remove('far');
                            heartIcon.classList.add('fas', 'text-danger');
                            button.classList.add('liked');
                        } else {
                            likedStories.delete(storyId);
                            heartIcon.classList.remove('fas', 'text-danger');
                            heartIcon.classList.add('far');
                            button.classList.remove('liked');
                        }
                        countSpan.textContent = data.likes;
                        storiesByUser[currentUserId][currentStoryIndex].like_count = data.likes;
                    } else {
                        Swal.fire('Error', data.error || 'Failed to update like', 'error');
                    }
                })
                .catch(error => Swal.fire('Error', 'Network error occurred', 'error'));
        }

        function toggleStatusLike(postId, button) {
            if (!postId) return;

            const heartIcon = button.querySelector('i.fa-heart');
            const countSpan = button.querySelector('.like-count');
            const isLiked = likedPosts.has(postId);

            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=toggle_like&post_id=${postId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.isLiked) {
                            likedPosts.add(postId);
                            heartIcon.classList.remove('far');
                            heartIcon.classList.add('fas', 'text-danger');
                            button.classList.add('liked');
                        } else {
                            likedPosts.delete(postId);
                            heartIcon.classList.remove('fas', 'text-danger');
                            heartIcon.classList.add('far');
                            button.classList.remove('liked');
                        }
                        countSpan.textContent = data.likes;
                    } else {
                        Swal.fire('Error', data.error || 'Failed to update like', 'error');
                    }
                })
                .catch(error => Swal.fire('Error', 'Network error occurred', 'error'));
        }

        function sendStoryMessage(storyId, event) {
            event.preventDefault();
            if (!storyId) return;

            const messageText = document.getElementById('messageText').value.trim();
            if (!messageText) return;

            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=send_story_message&story_id=${storyId}&message=${encodeURIComponent(messageText)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('messageText').value = '';
                        bootstrap.Modal.getInstance(document.getElementById('messageModal')).hide();
                        Swal.fire('Success', 'Message sent!', 'success');
                    } else {
                        Swal.fire('Error', data.error || 'Failed to send message', 'error');
                    }
                })
                .catch(error => Swal.fire('Error', 'Network error occurred', 'error'));
        }

        function shareStory(storyId) {
            if (!storyId) return;

            const storyUrl = `${window.location.origin}/story.php?id=${storyId}`;
            navigator.clipboard.writeText(storyUrl)
                .then(() => Swal.fire('Success', 'Story link copied to clipboard!', 'success'))
                .catch(() => Swal.fire('Error', 'Failed to copy link', 'error'));
        }

        function savePost(postId, mediaPath, mediaType) {
            if (!mediaPath) return Swal.fire('Error', 'No media to save', 'error');

            fetch(mediaPath)
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `${postId}.${mediaType === 'image' ? 'jpg' : 'mp4'}`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                    Swal.fire('Success', 'Media saved!', 'success');
                })
                .catch(() => Swal.fire('Error', 'Failed to save media', 'error'));
        }

        function sharePost(postId, mediaPath, mediaType) {
            if (!mediaPath) return Swal.fire('Error', 'No media to share', 'error');

            fetch(mediaPath)
                .then(response => response.blob())
                .then(blob => {
                    const file = new File([blob], `${postId}.${mediaType === 'image' ? 'jpg' : 'mp4'}`, {
                        type: blob.type
                    });
                    const shareData = {
                        files: [file],
                        title: 'Check out this post from SocialFusion!',
                        url: `${window.location.origin}/post.php?id=${postId}`
                    };

                    if (navigator.canShare && navigator.canShare({
                            files: [file]
                        })) {
                        navigator.share(shareData)
                            .catch(() => fallbackShare(mediaPath));
                    } else {
                        fallbackShare(mediaPath);
                    }
                })
                .catch(() => Swal.fire('Error', 'Failed to fetch media', 'error'));
        }

        function fallbackShare(mediaPath) {
            navigator.clipboard.writeText(mediaPath)
                .then(() => Swal.fire('Success', 'Media URL copied!', 'success'))
                .catch(() => Swal.fire('Error', 'Failed to copy URL', 'error'));
        }
        // updateNotificationCount
        function updateNotificationCount() {
            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=get_unread_notifications_count'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.querySelector('.notification-badge');
                        badge.textContent = data.count;
                        badge.style.display = data.count > 0 ? 'inline-block' : 'none'; // Hide if count is 0
                    }
                })
                .catch(error => console.error('Error updating notification count:', error));
        }

        // Media Preview Function
        function previewMedia(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('mediaPreview');
            preview.innerHTML = ''; // Clear previous preview

            if (file) {
                const maxSize = 100 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    Swal.fire('Error', 'File size exceeds 10MB limit', 'error');
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100%';
                        img.style.borderRadius = '10px';
                        preview.appendChild(img);
                    } else if (file.type.startsWith('video/')) {
                        const video = document.createElement('video');
                        video.src = e.target.result;
                        video.controls = true;
                        video.style.maxWidth = '100%';
                        video.style.borderRadius = '10px';
                        preview.appendChild(video);
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        // Show Upload Animation
        function showUploadAnimation() {
            const animationContainer = document.getElementById('uploadAnimation');
            const spinner = document.getElementById('uploadSpinner');
            const successCheck = document.getElementById('successCheck');
            const uploadText = document.getElementById('uploadText');

            animationContainer.style.display = 'block';
            spinner.style.display = 'block';
            successCheck.style.display = 'none';
            uploadText.textContent = 'Uploading...';
        }

        // Show Success Animation
        function showSuccessAnimation() {
            const spinner = document.getElementById('uploadSpinner');
            const successCheck = document.getElementById('successCheck');
            const uploadText = document.getElementById('uploadText');

            spinner.style.display = 'none';
            successCheck.style.display = 'block';
            uploadText.textContent = 'Uploaded!';
        }

        // Hide Upload Animation
        function hideUploadAnimation() {
            const animationContainer = document.getElementById('uploadAnimation');
            animationContainer.style.display = 'none';
        }
        // Handle post form submission
        document.getElementById('postForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const form = event.target;
            const submitBtn = form.querySelector('#postSubmitBtn');
            const uploadAnimation = document.getElementById('uploadAnimation');
            const uploadSpinner = document.getElementById('uploadSpinner');
            const successCheck = document.getElementById('successCheck');
            const uploadText = document.getElementById('uploadText');

            // Show upload animation
            uploadAnimation.style.display = 'block';
            uploadSpinner.style.display = 'block';
            successCheck.style.display = 'none';
            uploadText.textContent = 'Uploading...';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Posting...';

            try {
                const formData = new FormData(form);
                const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    // Show success animation
                    uploadSpinner.style.display = 'none';
                    successCheck.style.display = 'block';
                    uploadText.textContent = 'Posted!';
                    setTimeout(() => {
                        uploadAnimation.style.display = 'none';
                    }, 1000);

                    // Append new post to UI
                    const post = result.post;
                    const postsContainer = document.getElementById('postsContainer');
                    const postItem = document.createElement('div');
                    postItem.className = 'post-item';
                    postItem.setAttribute('data-post-id', post.id);
                    postItem.innerHTML = `
                            <div class="post-header d-flex align-items-center mb-2 justify-content-between">
                                <div class="d-flex align-items-center">
                                    <a href="othersprofile.php?user_id=${post.user_id}" class="profile-pic-link">
                                        <img src="${post.avatar_path || 'Uploads/avatars/default.jpg'}" class="profile-icon rounded-circle me-2" style="width: 40px; height: 40px;" alt="Profile">
                                    </a>
                                    <div>
                                        <strong>${post.name || post.username}</strong>
                                        <small class="text-muted d-block time-ago" data-timestamp="${post.created_at}">${timeAgo(post.created_at)}</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="post-menu-btn btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" onclick="savePost(${post.id}, '${post.media_path}', '${post.media_type}')"><i class="fas fa-save"></i> Save</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="sharePost(${post.id}, '${post.media_path}', '${post.media_type}')"><i class="fas fa-share"></i> Share</a></li>
                                    </ul>
                                </div>
                            </div>
                            <p>${post.content || ''}</p>
                            ${post.media_path ? (post.media_type === 'image' ? 
                                `<img src="${post.media_path}" class="post-media" alt="Post Media">` : 
                                `<video controls class="post-media"><source src="${post.media_path}" type="video/mp4"></video>`) : ''}
                            <div class="post-actions d-flex justify-content-between">
                                <button class="btn btn-link text-decoration-none like-btn" onclick="toggleStatusLike(${post.id}, this)">
                                    <i class="far fa-heart"></i>
                                    <span class="like-count">${post.like_count}</span>
                                    <i class="fas fa-check confirmation-icon"></i>
                                </button>
                                <button class="btn btn-link text-decoration-none comment-btn" onclick="toggleCommentSection(${post.id}, this)">
                                    <span class="comment-count"><i class="far fa-comment"></i> ${post.comment_count}</span>
                                </button>
                                <span><i class="fas fa-share"></i> 0</span>
                            </div>
                            <div class="comment-section mt-3" id="comment-section-${post.id}" style="display: none;">
                                <div class="comment-list mb-3" id="comment-list-${post.id}">
                                    <p class="text-muted small">No comments yet.</p>
                                </div>
                                <div class="comment-input d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($profile['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="rounded-circle me-2" style="width: 32px; height: 32px;" alt="Profile">
                                    <form class="flex-grow-1 d-flex" onsubmit="postComment(${post.id}, event)">
                                        <input type="text" class="form-control comment-input-field" placeholder="Write a comment..." required>
                                        <button type="submit" class="btn btn-link text-primary ms-2 p-0"><i class="fas fa-paper-plane"></i></button>
                                    </form>
                                </div>
                            </div>
                        `;
                    postsContainer.insertBefore(postItem, postsContainer.firstChild);
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('postModal')).hide();
                } else {
                    uploadAnimation.style.display = 'none';
                    alert(result.error || 'Failed to create post');
                }
            } catch (error) {
                console.error('Error creating post:', error);
                uploadAnimation.style.display = 'none';
                alert('An error occurred while creating the post: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Post';
            }
        });

        // Handle post type changes (e.g., reel, video, special, live)
        document.querySelectorAll('[data-bs-target="#postModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const postType = this.getAttribute('data-post-type');
                const modalTitle = document.getElementById('postModalLabel');
                const postTextContainer = document.getElementById('postTextContainer');
                const mediaLabel = document.getElementById('mediaLabel');
                const mediaHelp = document.getElementById('mediaHelp');
                const postAction = document.getElementById('postAction');

                switch (postType) {
                    case 'reel':
                        modalTitle.textContent = 'Create a Reel';
                        postTextContainer.style.display = 'none';
                        mediaLabel.textContent = 'Upload Video';
                        mediaHelp.textContent = 'Upload a short video (max 100MB).';
                        postAction.value = 'create_reel';
                        break;
                    case 'video':
                        modalTitle.textContent = 'Create a Video Post';
                        postTextContainer.style.display = 'block';
                        mediaLabel.textContent = 'Upload Video';
                        mediaHelp.textContent = 'Upload a video (max 100MB).';
                        postAction.value = 'create_post';
                        break;
                    case 'special':
                        modalTitle.textContent = 'Create a Special Post';
                        postTextContainer.style.display = 'block';
                        mediaLabel.textContent = 'Upload Media (Optional)';
                        mediaHelp.textContent = 'Supports images and videos (max 100MB).';
                        postAction.value = 'create_post';
                        break;
                    case 'live':
                        modalTitle.textContent = 'Go Live';
                        postTextContainer.style.display = 'none';
                        mediaLabel.textContent = 'Live Stream';
                        mediaHelp.textContent = 'Start a live stream.';
                        postAction.value = 'create_post'; // Placeholder
                        break;
                    default:
                        modalTitle.textContent = 'Create a Post';
                        postTextContainer.style.display = 'block';
                        mediaLabel.textContent = 'Upload Media';
                        mediaHelp.textContent = 'Supports images and videos (max 100MB).';
                        postAction.value = 'create_post';
                }
            });
        });

        // Post a comment
        async function postComment(postId, event) {
            event.preventDefault();
            const form = event.target;
            const input = form.querySelector('.comment-input-field');
            const content = input.value.trim();
            if (!content) return;

            try {
                const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'post_comment',
                        post_id: postId,
                        content: content
                    })
                });
                const result = await response.json();
                if (result.success) {
                    const comment = result.comment;
                    const commentList = document.getElementById(`comment-list-${postId}`);
                    const noComments = commentList.querySelector('.text-muted');
                    if (noComments) noComments.remove();

                    const commentItem = document.createElement('div');
                    commentItem.className = 'comment-item d-flex mb-2';
                    commentItem.setAttribute('data-comment-id', comment.id);
                    commentItem.innerHTML = `
                            <a href="othersprofile.php?user_id=${comment.user_id}">
                                <img src="${comment.avatar_path || 'Uploads/avatars/default.jpg'}" class="rounded-circle me-2" style="width: 32px; height: 32px;" alt="Profile">
                            </a>
                            <div class="comment-content bg-light p-2 rounded">
                                <strong>${comment.name || comment.username}</strong>
                                <p class="mb-0">${comment.content}</p>
                                <small class="text-muted time-ago" data-timestamp="${comment.created_at}">${timeAgo(comment.created_at)}</small>
                            </div>
                        `;
                    commentList.insertBefore(commentItem, commentList.firstChild);
                    input.value = '';

                    // Update comment count
                    const commentCountSpan = document.querySelector(`.post-item[data-post-id="${postId}"] .comment-count`);
                    if (commentCountSpan) {
                        const currentCount = parseInt(commentCountSpan.textContent.match(/\d+/)[0]);
                        commentCountSpan.innerHTML = `<i class="far fa-comment"></i> ${currentCount + 1}`;
                    }
                } else {
                    console.error('Comment error:', result.error);
                    alert(result.error || 'Failed to post comment');
                }
            } catch (error) {
                console.error('Error posting comment:', error);
                alert('An error occurred while posting the comment: ' + error.message);
            }
        }
        // End Post Submission Function

        // Remove friend suggestion from UI
        function removeFriend(button) {
            const friendItem = button.closest('.friend-item');
            friendItem.remove();
        }

        // Story Preview Handler
        document.getElementById('storyMedia').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const previewDiv = document.getElementById('storyPreview');
            previewDiv.innerHTML = '';
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100%';
                        img.style.borderRadius = '10px';
                        previewDiv.appendChild(img);
                    } else if (file.type.startsWith('video/')) {
                        const video = document.createElement('video');
                        video.src = e.target.result;
                        video.controls = true;
                        video.style.maxWidth = '100%';
                        video.style.borderRadius = '10px';
                        previewDiv.appendChild(video);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
        // User interaction tracking object
        let userInteractions = {
            categories: {} // e.g., { "travel": { likes: 10, comments: 5, shares: 2, views: 20 }, "food": {...} }
        };

        // Weights for different interactions
        const INTERACTION_WEIGHTS = {
            like: 3,
            comment: 5,
            share: 7,
            view: 1
        };

        // Initialize user interactions (fetch from backend or local storage)
        function initializeUserInteractions() {
            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=get_user_interactions'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        userInteractions.categories = data.interactions || {};
                    }
                })
                .catch(error => console.error('Error fetching interactions:', error));
        }

        // Track user interaction (like, comment, share, view)
        function trackInteraction(contentId, contentType, category, action) {
            if (!userInteractions.categories[category]) {
                userInteractions.categories[category] = {
                    likes: 0,
                    comments: 0,
                    shares: 0,
                    views: 0
                };
            }
            userInteractions.categories[category][action] += 1;

            // Send interaction to backend
            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=track_interaction&content_id=${contentId}&content_type=${contentType}&category=${category}&interaction=${action}`
                })
                .catch(error => console.error('Error tracking interaction:', error));
        }

        // Calculate category scores based on interactions
        function calculateCategoryScores() {
            const scores = {};
            for (const [category, interactions] of Object.entries(userInteractions.categories)) {
                scores[category] = (
                    (interactions.likes || 0) * INTERACTION_WEIGHTS.like +
                    (interactions.comments || 0) * INTERACTION_WEIGHTS.comment +
                    (interactions.shares || 0) * INTERACTION_WEIGHTS.share +
                    (interactions.views || 0) * INTERACTION_WEIGHTS.view
                );
            }
            return scores;
        }

        // Sort content by user preference
        function sortContentByPreference(contentItems) {
            const scores = calculateCategoryScores();
            return contentItems.sort((a, b) => {
                const scoreA = scores[a.category] || 0;
                const scoreB = scores[b.category] || 0;
                return scoreB - scoreA; // Higher score first
            });
        }

        // Modified populateReels to prioritize user-preferred content
        function populateReels(reels) {
            const reelGrid = document.querySelector('.reel-grid');
            reelGrid.innerHTML = ''; // Clear existing content

            if (reels.length === 0) {
                reelGrid.innerHTML = '<p class="text-muted text-center w-100">No reels available.</p>';
                return;
            }

            // Sort reels by user preference
            const sortedReels = sortContentByPreference(reels);

            sortedReels.forEach(reel => {
                const reelItem = document.createElement('div');
                reelItem.className = 'reel-item';
                reelItem.dataset.reelId = reel.id;
                reelItem.onclick = () => {
                    showReel(reel.id, reel.video_path, reel.name || reel.username || 'Unknown', reel.avatar_path || 'https://via.placeholder.com/50', reel.caption);
                    trackInteraction(reel.id, 'reel', reel.category, 'view'); // Track view
                };

                reelItem.innerHTML = `
            <div class="reel-image-container position-relative">
                <video class="reel-thumbnail" muted>
                    <source src="${reel.video_path}" type="video/mp4">
                </video>
                <div class="play-icon position-absolute top-50 start-50 translate-middle">
                    <i class="fas fa-play-circle fa-2x text-white"></i>
                </div>
            </div>
            <div class="reel-info text-center mt-2">
                <p class="small mb-0">${reel.name || reel.username || 'Unknown'}</p>
                <small class="text-muted">${formatDate(reel.created_at)}</small>
            </div>
        `;

                reelGrid.appendChild(reelItem);
            });
        }

        // Modified showReel to track views
        function showReel(reelId, videoPath, username, avatarPath, caption) {
            currentReelId = reelId;
            document.getElementById('reelVideoSource').src = videoPath;
            document.getElementById('reelVideo').load();
            document.getElementById('reelUsername').textContent = username;
            document.getElementById('reelUserAvatar').src = avatarPath;
            document.getElementById('reelViewerLabel').textContent = caption || username + "'s Reel";

            // Fetch like count and status
            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=get_reel_info&reel_id=${reelId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('reelLikeBtn').querySelector('.like-count').textContent = data.like_count;
                        document.getElementById('reelLikeBtn').classList.toggle('liked', data.user_liked);
                        document.getElementById('reelLikeBtn').querySelector('i').classList.toggle('fas', data.user_liked);
                        document.getElementById('reelLikeBtn').querySelector('i').classList.toggle('far', !data.user_liked);
                        trackInteraction(reelId, 'reel', data.category, 'view'); // Track view
                    }
                });

            new bootstrap.Modal(document.getElementById('reelViewerModal')).show();
        }

        // Modified toggleStatusLike to track likes
        function toggleStatusLike(postId, button) {
            if (!postId) return;

            const heartIcon = button.querySelector('i.fa-heart');
            const countSpan = button.querySelector('.like-count');
            const isLiked = likedPosts.has(postId);

            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=toggle_like&post_id=${postId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.isLiked) {
                            likedPosts.add(postId);
                            heartIcon.classList.remove('far');
                            heartIcon.classList.add('fas', 'text-danger');
                            button.classList.add('liked');
                            trackInteraction(postId, 'post', data.category, 'like'); // Track like
                        } else {
                            likedPosts.delete(postId);
                            heartIcon.classList.remove('fas', 'text-danger');
                            heartIcon.classList.add('far');
                            button.classList.remove('liked');
                        }
                        countSpan.textContent = data.likes;
                    } else {
                        Swal.fire('Error', data.error || 'Failed to update like', 'error');
                    }
                })
                .catch(error => Swal.fire('Error', 'Network error occurred', 'error'));
        }

        // Modified postComment to track comments
        async function postComment(postId, event) {
            event.preventDefault();
            const form = event.target;
            const input = form.querySelector('.comment-input-field');
            const content = input.value.trim();
            if (!content) return;

            try {
                const response = await fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        action: 'post_comment',
                        post_id: postId,
                        content: content
                    })
                });
                const result = await response.json();
                if (result.success) {
                    const comment = result.comment;
                    const commentList = document.getElementById(`comment-list-${postId}`);
                    const noComments = commentList.querySelector('.text-muted');
                    if (noComments) noComments.remove();

                    const commentItem = document.createElement('div');
                    commentItem.className = 'comment-item d-flex mb-2';
                    commentItem.setAttribute('data-comment-id', comment.id);
                    commentItem.innerHTML = `
                <a href="othersprofile.php?user_id=${comment.user_id}">
                    <img src="${comment.avatar_path || 'Uploads/avatars/default.jpg'}" class="rounded-circle me-2" style="width: 32px; height: 32px;" alt="Profile">
                </a>
                <div class="comment-content bg-light p-2 rounded">
                    <strong>${comment.name || comment.username}</strong>
                    <p class="mb-0">${comment.content}</p>
                    <small class="text-muted time-ago" data-timestamp="${comment.created_at}">${timeAgo(comment.created_at)}</small>
                </div>
            `;
                    commentList.insertBefore(commentItem, commentList.firstChild);
                    input.value = '';

                    // Update comment count
                    const commentCountSpan = document.querySelector(`.post-item[data-post-id="${postId}"] .comment-count`);
                    if (commentCountSpan) {
                        const currentCount = parseInt(commentCountSpan.textContent.match(/\d+/)[0]);
                        commentCountSpan.innerHTML = `<i class="far fa-comment"></i> ${currentCount + 1}`;
                    }

                    // Track comment
                    trackInteraction(postId, 'post', comment.category, 'comment');
                } else {
                    console.error('Comment error:', result.error);
                    alert(result.error || 'Failed to post comment');
                }
            } catch (error) {
                console.error('Error posting comment:', error);
                alert('An error occurred while posting the comment: ' + error.message);
            }
        }

        // Modified sharePost to track shares
        function sharePost(postId, mediaPath, mediaType) {
            if (!mediaPath) return Swal.fire('Error', 'No media to share', 'error');

            fetch(mediaPath)
                .then(response => response.blob())
                .then(blob => {
                    const file = new File([blob], `${postId}.${mediaType === 'image' ? 'jpg' : 'mp4'}`, {
                        type: blob.type
                    });
                    const shareData = {
                        files: [file],
                        title: 'Check out this post from SocialFusion!',
                        url: `${window.location.origin}/post.php?id=${postId}`
                    };

                    if (navigator.canShare && navigator.canShare({
                            files: [file]
                        })) {
                        navigator.share(shareData)
                            .then(() => {
                                // Track share
                                fetch(window.location.href, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded'
                                        },
                                        body: `action=get_post_info&post_id=${postId}`
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            trackInteraction(postId, 'post', data.category, 'share');
                                        }
                                    });
                            })
                            .catch(() => fallbackShare(mediaPath));
                    } else {
                        fallbackShare(mediaPath);
                    }
                })
                .catch(() => Swal.fire('Error', 'Failed to fetch media', 'error'));
        }

        // Fetch posts with preference-based sorting
        function fetchPosts() {
            fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'action=get_posts'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const sortedPosts = sortContentByPreference(data.posts);
                        const postsContainer = document.getElementById('postsContainer');
                        postsContainer.innerHTML = '';
                        sortedPosts.forEach(post => {
                            const postItem = document.createElement('div');
                            postItem.className = 'post-item';
                            postItem.setAttribute('data-post-id', post.id);
                            postItem.innerHTML = `
                    <div class="post-header d-flex align-items-center mb-2 justify-content-between">
                        <div class="d-flex align-items-center">
                            <a href="othersprofile.php?user_id=${post.user_id}" class="profile-pic-link">
                                <img src="${post.avatar_path || 'Uploads/avatars/default.jpg'}" class="profile-icon rounded-circle me-2" style="width: 40px; height: 40px;" alt="Profile">
                            </a>
                            <div>
                                <strong>${post.name || post.username}</strong>
                                <small class="text-muted d-block time-ago" data-timestamp="${post.created_at}">${timeAgo(post.created_at)}</small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="post-menu-btn btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="savePost(${post.id}, '${post.media_path}', '${post.media_type}')"><i class="fas fa-save"></i> Save</a></li>
                                <li><a class="dropdown-item" href="#" onclick="sharePost(${post.id}, '${post.media_path}', '${post.media_type}')"><i class="fas fa-share"></i> Share</a></li>
                            </ul>
                        </div>
                    </div>
                    <p>${post.content || ''}</p>
                    ${post.media_path ? (post.media_type === 'image' ? 
                        `<img src="${post.media_path}" class="post-media" alt="Post Media">` : 
                        `<video controls class="post-media"><source src="${post.media_path}" type="video/mp4"></video>`) : ''}
                    <div class="post-actions d-flex justify-content-between">
                        <button class="btn btn-link text-decoration-none like-btn" onclick="toggleStatusLike(${post.id}, this)">
                            <i class="${likedPosts.has(post.id.toString()) ? 'fas fa-heart text-danger' : 'far fa-heart'}"></i>
                            <span class="like-count">${post.like_count}</span>
                            <i class="fas fa-check confirmation-icon"></i>
                        </button>
                        <button class="btn btn-link text-decoration-none comment-btn" onclick="toggleCommentSection(${post.id}, this)">
                            <span class="comment-count"><i class="far fa-comment"></i> ${post.comment_count}</span>
                        </button>
                        <span><i class="fas fa-share"></i> 0</span>
                    </div>
                    <div class="comment-section mt-3" id="comment-section-${post.id}" style="display: none;">
                        <div class="comment-list mb-3" id="comment-list-${post.id}">
                            <p class="text-muted small">No comments yet.</p>
                        </div>
                        <div class="comment-input d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($profile['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="rounded-circle me-2" style="width: 32px; height: 32px;" alt="Profile">
                            <form class="flex-grow-1 d-flex" onsubmit="postComment(${post.id}, event)">
                                <input type="text" class="form-control comment-input-field" placeholder="Write a comment..." required>
                                <button type="submit" class="btn btn-link text-primary ms-2 p-0"><i class="fas fa-paper-plane"></i></button>
                            </form>
                        </div>
                    </div>
                `;
                            postsContainer.appendChild(postItem);
                        });
                    } else {
                        Swal.fire('Error', data.error || 'Failed to load posts', 'error');
                    }
                })
                .catch(error => Swal.fire('Error', 'Network error occurred', 'error'));
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            initializeUserInteractions();
            fetchPosts();
            fetchReels();
        });

        // Post Media Preview Handler
        document.getElementById('postMedia').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const previewDiv = document.getElementById('mediaPreview');
            previewDiv.innerHTML = '';
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100%';
                        img.style.borderRadius = '10px';
                        previewDiv.appendChild(img);
                    } else if (file.type.startsWith('video/')) {
                        const video = document.createElement('video');
                        video.src = e.target.result;
                        video.controls = true;
                        video.style.maxWidth = '100%';
                        video.style.borderRadius = '10px';
                        previewDiv.appendChild(video);
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Initialize tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Handle modal close to reset progress
        document.getElementById('storyViewerModal').addEventListener('hidden.bs.modal', function() {
            clearInterval(storyTimer);
            currentUserId = null;
            currentStoryIndex = 0;
            currentStoryId = null;
        });

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>

</html>