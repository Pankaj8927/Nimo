<?php
session_start();

// Debugging log file
define('DEBUG_LOG', __DIR__ . '/debug.log');
function debug_log($message) {
    file_put_contents(DEBUG_LOG, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    debug_log("No user_id in session, redirecting to index.php");
    header("Location: index.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
debug_log("User ID: $user_id");

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Database connection
try {
    $db = new PDO("mysql:host=localhost;dbname=socialauth_db", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    debug_log("Database connection successful");
} catch (PDOException $e) {
    debug_log("Database connection error: " . $e->getMessage());
    die("Sorry, we're having trouble connecting to the database.");
}

// Create necessary tables
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS user_profiles (
            user_id INT PRIMARY KEY,
            name VARCHAR(100),
            profession VARCHAR(100),
            interests TEXT,
            bio TEXT,
            location VARCHAR(100),
            avatar_path VARCHAR(255) DEFAULT 'Uploads/avatars/default.jpg',
            cover_path VARCHAR(255) DEFAULT 'Uploads/covers/default.jpg'
        );
        CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            content TEXT,
            media_path VARCHAR(255),
            media_type VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS post_likes (
            post_id INT,
            user_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (post_id, user_id)
        );
        CREATE TABLE IF NOT EXISTS friend_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
    debug_log("Tables created successfully");
} catch (PDOException $e) {
    debug_log("Table creation error: " . $e->getMessage());
    die("Error setting up database tables: " . htmlspecialchars($e->getMessage()));
}

// Verify user exists
try {
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    if (!$stmt->fetch()) {
        debug_log("User ID $user_id does not exist in users table");
        die("Invalid user ID. Please log in again.");
    }
} catch (PDOException $e) {
    debug_log("User verification error: " . $e->getMessage());
    die("Error verifying user: " . htmlspecialchars($e->getMessage()));
}

// Validate file uploads
function validateFile($file, $allowed_types, $max_size) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File size exceeds limit (max 5MB).',
            UPLOAD_ERR_PARTIAL => 'File upload was interrupted.',
            UPLOAD_ERR_NO_FILE => 'No file uploaded.',
            default => 'File upload error.',
        };
        debug_log("File validation error: $error");
        return ['valid' => false, 'error' => $error];
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed_types)) {
        debug_log("Invalid file type: $mime");
        return ['valid' => false, 'error' => 'Invalid file type. Only JPEG, PNG, or GIF allowed.'];
    }
    if ($file['size'] > $max_size) {
        debug_log("File size exceeds limit: {$file['size']} bytes");
        return ['valid' => false, 'error' => 'File size exceeds 5MB limit.'];
    }
    return ['valid' => true];
}

// Handle profile update
$error_message = '';
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'], $_POST['csrf_token']) && $_POST['csrf_token'] === $csrf_token) {
    header('Content-Type: application/json'); // Return JSON for AJAX
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) ?: '';
    $profession = filter_input(INPUT_POST, 'profession', FILTER_SANITIZE_STRING) ?: '';
    $interests = filter_input(INPUT_POST, 'interests', FILTER_SANITIZE_STRING) ?: '';
    $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING) ?: '';
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING) ?: '';

    debug_log("Profile update attempt: name=$name, profession=$profession, interests=$interests, bio=$bio, location=$location");

    $avatar_path = null;
    $cover_path = null;
    $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_file_size = 5 * 1024 * 1024; // 5MB

    // Fetch existing paths
    try {
        $stmt = $db->prepare("SELECT avatar_path, cover_path FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $existing = $stmt->fetch() ?: ['avatar_path' => 'Uploads/avatars/default.jpg', 'cover_path' => 'Uploads/covers/default.jpg'];
        debug_log("Existing paths: avatar={$existing['avatar_path']}, cover={$existing['cover_path']}");
    } catch (PDOException $e) {
        debug_log("Fetch existing paths error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error fetching existing profile data']);
        exit;
    }

    // Avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $validation = validateFile($_FILES['avatar'], $allowed_image_types, $max_file_size);
        if ($validation['valid']) {
            $upload_dir = 'Uploads/avatars/';
            if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
                debug_log("Failed to create avatar upload directory");
                echo json_encode(['success' => false, 'error' => 'Failed to create avatar upload directory']);
                exit;
            }
            if (!is_writable($upload_dir)) {
                debug_log("Avatar upload directory is not writable");
                echo json_encode(['success' => false, 'error' => 'Avatar upload directory is not writable']);
                exit;
            }
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $avatar_path = $upload_dir . $user_id . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                debug_log("Failed to upload avatar image");
                echo json_encode(['success' => false, 'error' => 'Failed to upload avatar image']);
                exit;
            }
            if (!file_exists($avatar_path)) {
                debug_log("Avatar file was not saved correctly");
                echo json_encode(['success' => false, 'error' => 'Avatar file was not saved correctly']);
                exit;
            }
            debug_log("Avatar uploaded to: $avatar_path");
        } else {
            debug_log("Avatar validation failed: " . $validation['error']);
            echo json_encode(['success' => false, 'error' => $validation['error']]);
            exit;
        }
    }

    // Cover upload
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
        $validation = validateFile($_FILES['cover'], $allowed_image_types, $max_file_size);
        if ($validation['valid']) {
            $upload_dir = 'Uploads/covers/';
            if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
                debug_log("Failed to create cover upload directory");
                echo json_encode(['success' => false, 'error' => 'Failed to create cover upload directory']);
                exit;
            }
            if (!is_writable($upload_dir)) {
                debug_log("Cover upload directory is not writable");
                echo json_encode(['success' => false, 'error' => 'Cover upload directory is not writable']);
                exit;
            }
            $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
            $cover_path = $upload_dir . $user_id . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES['cover']['tmp_name'], $cover_path)) {
                debug_log("Failed to upload cover image");
                echo json_encode(['success' => false, 'error' => 'Failed to upload cover image']);
                exit;
            }
            if (!file_exists($cover_path)) {
                debug_log("Cover file was not saved correctly");
                echo json_encode(['success' => false, 'error' => 'Cover file was not saved correctly']);
                exit;
            }
            debug_log("Cover uploaded to: $cover_path");
        } else {
            debug_log("Cover validation failed: " . $validation['error']);
            echo json_encode(['success' => false, 'error' => $validation['error']]);
            exit;
        }
    }

    // Use existing paths if no new uploads
    $avatar_path = $avatar_path ?: $existing['avatar_path'];
    $cover_path = $cover_path ?: $existing['cover_path'];

    // Update profile
    try {
        $db->beginTransaction();
        $stmt = $db->prepare("
            INSERT INTO user_profiles (user_id, name, profession, interests, bio, location, avatar_path, cover_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                profession = VALUES(profession),
                interests = VALUES(interests),
                bio = VALUES(bio),
                location = VALUES(location),
                avatar_path = VALUES(avatar_path),
                cover_path = VALUES(cover_path)
        ");
        $stmt->execute([
            $user_id, $name, $profession, $interests, $bio, $location, $avatar_path, $cover_path
        ]);
        $db->commit();
        debug_log("Profile updated for user_id=$user_id");
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } catch (PDOException $e) {
        $db->rollBack();
        debug_log("Profile update error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to update profile: ' . htmlspecialchars($e->getMessage())]);
    }
    exit;
}

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_status'], $_POST['csrf_token']) && $_POST['csrf_token'] === $csrf_token) {
    header('Content-Type: application/json');
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING) ?: '';
    $media_path = '';
    $media_type = '';
    $allowed_media_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
    $max_file_size = 5 * 1024 * 1024;

    debug_log("Post submission attempt: content=$content");

    if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
        $validation = validateFile($_FILES['media'], $allowed_media_types, $max_file_size);
        if ($validation['valid']) {
            $upload_dir = 'Uploads/posts/';
            if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
                debug_log("Failed to create posts upload directory");
                echo json_encode(['success' => false, 'error' => 'Failed to create posts upload directory']);
                exit;
            }
            if (!is_writable($upload_dir)) {
                debug_log("Posts upload directory is not writable");
                echo json_encode(['success' => false, 'error' => 'Posts upload directory is not writable']);
                exit;
            }
            $ext = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
            $media_path = $upload_dir . $user_id . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['media']['tmp_name'], $media_path)) {
                $media_type = mime_content_type($media_path);
                $media_type = strpos($media_type, 'image') === 0 ? 'image' : (strpos($media_type, 'video') === 0 ? 'video' : '');
                debug_log("Media uploaded to: $media_path, type: $media_type");
            } else {
                debug_log("Failed to upload media file");
                echo json_encode(['success' => false, 'error' => 'Failed to upload media file']);
                exit;
            }
        } else {
            debug_log("Media validation failed: " . $validation['error']);
            echo json_encode(['success' => false, 'error' => $validation['error']]);
            exit;
        }
    }

    if ($content || $media_path) {
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("INSERT INTO posts (user_id, content, media_path, media_type, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $content, $media_path, $media_type]);
            $db->commit();
            debug_log("Post created: user_id=$user_id");
            echo json_encode(['success' => true, 'message' => 'Post created successfully!']);
        } catch (PDOException $e) {
            $db->rollBack();
            debug_log("Post creation error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to create post: ' . htmlspecialchars($e->getMessage())]);
        }
    } else {
        debug_log("Post submission error: Content or media required");
        echo json_encode(['success' => false, 'error' => 'Post content or media is required']);
    }
    exit;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'], $_POST['comment_content'], $_POST['csrf_token']) && $_POST['csrf_token'] === $csrf_token) {
    header('Content-Type: application/json');
    $post_id = (int)$_POST['post_id'];
    $content = filter_input(INPUT_POST, 'comment_content', FILTER_SANITIZE_STRING) ?: '';
    debug_log("Comment submission attempt: post_id=$post_id, content=$content");

    if ($content) {
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$post_id, $user_id, $content]);
            $comment_id = $db->lastInsertId();

            // Fetch the new comment
            $stmt = $db->prepare("
                SELECT c.id, c.post_id, c.content, c.created_at, u.username, up.name, up.avatar_path, c.user_id
                FROM comments c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE c.id = ?
            ");
            $stmt->execute([$comment_id]);
            $comment = $stmt->fetch();

            $db->commit();
            debug_log("Comment created: comment_id=$comment_id, post_id=$post_id");
            echo json_encode([
                'success' => true,
                'comment' => [
                    'id' => $comment['id'],
                    'post_id' => $comment['post_id'],
                    'user_id' => $comment['user_id'],
                    'name' => $comment['name'] ?: $comment['username'],
                    'username' => $comment['username'],
                    'avatar_path' => $comment['avatar_path'] ?: 'Uploads/avatars/default.jpg',
                    'content' => htmlspecialchars($comment['content']),
                    'created_at' => $comment['created_at']
                ]
            ]);
        } catch (PDOException $e) {
            $db->rollBack();
            debug_log("Comment submission error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to add comment: ' . htmlspecialchars($e->getMessage())]);
        }
    } else {
        debug_log("Comment submission error: Comment is empty");
        echo json_encode(['success' => false, 'error' => 'Comment cannot be empty']);
    }
    exit;
}

// Handle AJAX requests (likes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['csrf_token']) && $_POST['csrf_token'] === $csrf_token && $_POST['action'] === 'toggle_like') {
    header('Content-Type: application/json');
    $post_id = (int)($_POST['post_id'] ?? 0);
    debug_log("Like toggle attempt: post_id=$post_id, user_id=$user_id");

    try {
        $db->beginTransaction();
        $stmt = $db->prepare("SELECT EXISTS(SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?) AS liked");
        $stmt->execute([$post_id, $user_id]);
        $liked = $stmt->fetchColumn();

        if ($liked) {
            $stmt = $db->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$post_id, $user_id]);
            debug_log("Like removed: post_id=$post_id, user_id=$user_id");
        } else {
            $stmt = $db->prepare("INSERT INTO post_likes (post_id, user_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$post_id, $user_id]);
            debug_log("Like added: post_id=$post_id, user_id=$user_id");
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM post_likes WHERE post_id = ?");
        $stmt->execute([$post_id]);
        $like_count = $stmt->fetchColumn();

        $db->commit();
        echo json_encode(['success' => true, 'liked' => !$liked, 'like_count' => $like_count]);
    } catch (PDOException $e) {
        $db->rollBack();
        debug_log("Like toggle error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to update like: ' . htmlspecialchars($e->getMessage())]);
    }
    exit;
}

// Fetch profile data
try {
    $stmt = $db->prepare("
        SELECT u.id, u.username, up.name, up.profession, up.interests, up.bio, up.location, up.avatar_path, up.cover_path
        FROM users u
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch() ?: [
        'id' => $user_id,
        'username' => 'Unknown',
        'name' => 'Default User',
        'profession' => '',
        'interests' => '',
        'bio' => 'No bio yet',
        'location' => '',
        'avatar_path' => 'Uploads/avatars/default.jpg',
        'cover_path' => 'Uploads/covers/default.jpg'
    ];
    debug_log("Profile fetched: user_id=$user_id, username={$profile['username']}");
} catch (PDOException $e) {
    debug_log("Profile fetch error: " . $e->getMessage());
    $error_message = "Error loading profile: " . htmlspecialchars($e->getMessage());
}

// Fetch counts
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM friend_requests WHERE receiver_id = ? AND status = 'accepted'");
    $stmt->execute([$user_id]);
    $follower_count = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM friend_requests WHERE sender_id = ? AND status = 'accepted'");
    $stmt->execute([$user_id]);
    $following_count = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $post_count = $stmt->fetchColumn();
    debug_log("Counts fetched: followers=$follower_count, following=$following_count, posts=$post_count");
} catch (PDOException $e) {
    debug_log("Counts fetch error: " . $e->getMessage());
    $follower_count = $following_count = $post_count = 0;
}

// Fetch friends
try {
    $stmt = $db->prepare("
        SELECT u.id, u.username, up.name, up.avatar_path
        FROM friend_requests fr
        JOIN users u ON (u.id = fr.sender_id OR u.id = fr.receiver_id) AND u.id != ?
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE (fr.sender_id = ? OR fr.receiver_id = ?) AND fr.status = 'accepted'
        LIMIT 8
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $friends = $stmt->fetchAll();
    debug_log("Friends fetched: count=" . count($friends));
} catch (PDOException $e) {
    debug_log("Friends fetch error: " . $e->getMessage());
    $friends = [];
}

// Fetch posts
try {
    $stmt = $db->prepare("
        SELECT p.id, p.content, p.media_path, p.media_type, p.created_at, p.user_id,
               u.username, up.name, up.avatar_path,
               (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id) AS like_count,
               EXISTS(SELECT 1 FROM post_likes pl WHERE pl.post_id = p.id AND pl.user_id = ?) AS user_liked,
               (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$user_id, $user_id]);
    $posts = $stmt->fetchAll();
    debug_log("Posts fetched: count=" . count($posts));
} catch (PDOException $e) {
    debug_log("Post fetch error: " . $e->getMessage());
    $posts = [];
}

// Fetch comments
$comments = [];
if ($posts) {
    $post_ids = array_column($posts, 'id');
    try {
        $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
        $stmt = $db->prepare("
            SELECT c.id, c.post_id, c.content, c.created_at, u.username, up.name, up.avatar_path, c.user_id
            FROM comments c
            JOIN users u ON c.user_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            WHERE c.post_id IN ($placeholders)
            ORDER BY c.created_at ASC
        ");
        $stmt->execute($post_ids);
        $all_comments = $stmt->fetchAll();
        foreach ($all_comments as $comment) {
            $comments[$comment['post_id']][] = $comment;
        }
        debug_log("Comments fetched: count=" . count($all_comments));
    } catch (PDOException $e) {
        debug_log("Comment fetch error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['name'] ?: $profile['username']); ?>'s Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f7fb;
        }
        .profile-header {
            height: 300px;
            background-size: cover;
            background-position: center;
            position: relative;
            border-radius: 10px;
        }
        .profile-picture {
            position: absolute;
            bottom: -50px;
            left: 50px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid white;
        }
        .profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .btn-edit-profile {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
        }
        .profile-stats {
            margin-top: 60px;
        }
        .profile-bio {
            text-align: center;
            margin: 20px 0;
        }
        .post-input {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 10px;
            display: flex;
            align-items: center;
        }
        .profile-icon {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
        .post-input-field {
            background: transparent;
            border: none;
            outline: none;
            font-size: 1rem;
            color: #333;
            flex-grow: 1;
            margin: 0 10px;
        }
        .post-input-field::placeholder {
            color: #6c757d;
        }
        .photo-btn {
            background: none;
            border: none;
            color: #6c757d;
        }
        .photo-btn:hover {
            color: #1e90ff;
        }
        .post-item {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .post-media {
            max-width: 100%;
            max-height: 300px;
            object-fit: cover;
            margin-top: 10px;
            border-radius: 8px;
        }
        .friend-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
        }
        .post-actions .btn-link {
            color: #555;
            text-decoration: none;
        }
        .like-btn.liked i.fa-heart {
            color: #ff0000;
        }
        .comment-form textarea {
            resize: none;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h1><a href="index.php" class="text-decoration-none">SocialFusion</a></h1>
            <div>
                <a href="logout.php" class="btn btn-outline-primary">Logout</a>
            </div>
        </header>

        <div class="profile-header" id="profileHeader" style="background-image: url('<?php echo htmlspecialchars($profile['cover_path']); ?>');">
            <button class="btn btn-edit-profile" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </button>
            <div class="profile-picture">
                <img src="<?php echo htmlspecialchars($profile['avatar_path']); ?>" alt="Profile Picture" id="profileAvatar">
            </div>
        </div>

        <div class="profile-stats row text-center">
            <div class="col-md-4 stat">
                <h5 data-target="<?php echo $follower_count; ?>"><?php echo $follower_count; ?></h5>
                <p>Followers</p>
            </div>
            <div class="col-md-4 stat">
                <h5 data-target="<?php echo $following_count; ?>"><?php echo $following_count; ?></h5>
                <p>Following</p>
            </div>
            <div class="col-md-4 stat">
                <h5 data-target="<?php echo $post_count; ?>"><?php echo $post_count; ?></h5>
                <p>Posts</p>
            </div>
        </div>

        <div class="profile-bio">
            <h4 id="profileNameDisplay"><?php echo htmlspecialchars($profile['name'] ?: $profile['username']); ?></h4>
            <p id="profileDetailsDisplay">
                <?php echo implode(' | ', array_filter([
                    $profile['location'] ? "📍 " . htmlspecialchars($profile['location']) : '',
                    $profile['profession'] ? "🎨 " . htmlspecialchars($profile['profession']) : '',
                    $profile['interests'] ? "🌍 " . htmlspecialchars($profile['interests']) : ''
                ])); ?>
            </p>
            <p id="profileBioDisplay"><?php echo htmlspecialchars($profile['bio']); ?></p>
        </div>

        <div class="friends-section mb-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Friends</h5>
                    <a href="#" class="text-decoration-none see-all-link">See all</a>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" id="friendCount"><?php echo count($friends); ?> friends</p>
                    <?php if (empty($friends)): ?>
                        <p class="text-muted">No friends yet.</p>
                    <?php else: ?>
                        <div class="friends-grid row row-cols-2 row-cols-md-4 g-3" id="friendsGrid">
                            <?php foreach ($friends as $friend): ?>
                                <div class="col">
                                    <div class="friend-item text-center">
                                        <a href="profile.php?user_id=<?php echo $friend['id']; ?>">
                                            <img src="<?php echo htmlspecialchars($friend['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="friend-image" alt="<?php echo htmlspecialchars($friend['name'] ?: $friend['username']); ?>">
                                            <p class="mb-0 friend-name"><?php echo htmlspecialchars($friend['name'] ?: $friend['username']); ?></p>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="posts-section mb-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Posts</h5>
                </div>
                <div class="card-body">
                    <div class="post-input">
                        <img src="<?php echo htmlspecialchars($profile['avatar_path']); ?>" class="profile-icon rounded-circle" alt="Profile Icon">
                        <input type="text" class="post-input-field" placeholder="Post a status update" readonly data-bs-toggle="modal" data-bs-target="#postModal">
                        <button class="photo-btn" data-bs-toggle="modal" data-bs-target="#postModal"><i class="fas fa-camera"></i></button>
                    </div>
                    <div class="post-options row g-3 mt-2">
                        <div class="col-6 col-md-3">
                            <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#postModal">
                                <i class="fas fa-feather me-2"></i>Post a status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="profile-activity mt-4">
                    <h4>All Posts</h4>
                    <div id="postsContainer">
                        <?php if (empty($posts)): ?>
                            <p class="text-muted">No posts yet.</p>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <div class="post-item" data-post-id="<?php echo $post['id']; ?>">
                                    <div class="d-flex align-items-center mb-2">
                                        <a href="profile.php?user_id=<?php echo $post['user_id']; ?>">
                                            <img src="<?php echo htmlspecialchars($post['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="profile-icon rounded-circle me-2" alt="Profile">
                                        </a>
                                        <div>
                                            <strong><?php echo htmlspecialchars($post['name'] ?: $post['username']); ?></strong>
                                            <small class="text-muted d-block"><?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?></small>
                                        </div>
                                    </div>
                                    <?php if ($post['content']): ?>
                                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($post['media_path']): ?>
                                        <?php if ($post['media_type'] === 'image'): ?>
                                            <img src="<?php echo htmlspecialchars($post['media_path']); ?>" class="post-media" alt="Post Media">
                                        <?php elseif ($post['media_type'] === 'video'): ?>
                                            <video controls class="post-media">
                                                <source src="<?php echo htmlspecialchars($post['media_path']); ?>" type="video/mp4">
                                            </video>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <div class="post-actions d-flex justify-content-between mt-2">
                                        <button class="btn btn-link like-btn <?php echo $post['user_liked'] ? 'liked' : ''; ?>" onclick="toggleLike(<?php echo $post['id']; ?>, this)">
                                            <i class="fa-heart <?php echo $post['user_liked'] ? 'fas text-danger' : 'far'; ?>"></i>
                                            <span class="like-count"><?php echo $post['like_count']; ?></span>
                                        </button>
                                        <span><i class="far fa-comment"></i> <?php echo $post['comment_count']; ?></span>
                                        <span><i class="fas fa-share"></i> 0</span>
                                    </div>
                                    <div class="mt-3">
                                        <h6>Comments</h6>
                                        <?php if (!empty($comments[$post['id']])): ?>
                                            <?php foreach ($comments[$post['id']] as $comment): ?>
                                                <div class="d-flex mb-2">
                                                    <a href="profile.php?user_id=<?php echo htmlspecialchars($comment['user_id']); ?>">
                                                        <img src="<?php echo htmlspecialchars($comment['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="profile-icon rounded-circle me-2" alt="Commenter">
                                                    </a>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($comment['name'] ?: $comment['username']); ?></strong>
                                                        <p class="mb-0"><?php echo htmlspecialchars($comment['content']); ?></p>
                                                        <small class="text-muted"><?php echo date('F j, Y, g:i a', strtotime($comment['created_at'])); ?></small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-muted">No comments yet.</p>
                                        <?php endif; ?>
                                        <form class="comment-form mt-2" onsubmit="postComment(<?php echo $post['id']; ?>, this, event)">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <div class="input-group">
                                                <textarea name="comment_content" class="form-control" rows="2" placeholder="Add a comment..." required></textarea>
                                                <button type="submit" class="btn btn-primary">Comment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        <form id="editProfileForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="update_profile" value="1">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <div class="mb-4 text-center">
                                <label class="form-label fw-bold">Profile Picture</label>
                                <input type="file" name="avatar" id="modalAvatarInput" accept="image/jpeg,image/png,image/gif" class="form-control">
                                <img src="<?php echo htmlspecialchars($profile['avatar_path']); ?>" id="avatarPreview" class="mt-2 rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <div class="mb-4 text-center">
                                <label class="form-label fw-bold">Cover Photo</label>
                                <input type="file" name="cover" id="coverInput" accept="image/jpeg,image/png,image/gif" class="form-control">
                                <img src="<?php echo htmlspecialchars($profile['cover_path']); ?>" id="coverPreview" class="mt-2" style="width: 100%; height: 200px; object-fit: cover;">
                            </div>
                            <div class="mb-4">
                                <label for="name" class="form-label fw-bold">Name</label>
                                <input type="text" class="form-control" name="name" id="name" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="profession" class="form-label fw-bold">Profession</label>
                                <input type="text" class="form-control" name="profession" id="profession" value="<?php echo htmlspecialchars($profile['profession']); ?>">
                            </div>
                            <div class="mb-4">
                                <label for="interests" class="form-label fw-bold">Interests</label>
                                <input type="text" class="form-control" name="interests" id="interests" value="<?php echo htmlspecialchars($profile['interests']); ?>">
                            </div>
                            <div class="mb-4">
                                <label for="bio" class="form-label fw-bold">Bio</label>
                                <textarea class="form-control" name="bio" id="bio" rows="3"><?php echo htmlspecialchars($profile['bio']); ?></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="location" class="form-label fw-bold">Location</label>
                                <input type="text" class="form-control" name="location" id="location" value="<?php echo htmlspecialchars($profile['location']); ?>">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="saveChangesBtn">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

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
                        <form id="postForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="post_status" value="1">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <div class="mb-3">
                                <textarea class="form-control" id="postContent" name="content" rows="3" placeholder="What's on your mind?"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="postMedia" class="form-label">Upload Photo or Video</label>
                                <input type="file" class="form-control" id="postMedia" name="media" accept="image/jpeg,image/png,image/gif,video/mp4,video/webm">
                                <div id="mediaPreview" class="mt-2"></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Post</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Image previews
        function previewImage(input, previewId, profileElementId = null) {
            const file = input.files[0];
            const preview = document.getElementById(previewId);
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (profileElementId) {
                        if (profileElementId === 'profileHeader') {
                            document.getElementById(profileElementId).style.backgroundImage = `url(${e.target.result})`;
                        } else {
                            document.getElementById(profileElementId).src = e.target.result;
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        }

        document.getElementById('modalAvatarInput').addEventListener('change', e => previewImage(e.target, 'avatarPreview', 'profileAvatar'));
        document.getElementById('coverInput').addEventListener('change', e => previewImage(e.target, 'coverPreview', 'profileHeader'));

        document.getElementById('postMedia').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('mediaPreview');
            preview.innerHTML = '';
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const media = file.type.startsWith('image/') ? document.createElement('img') : document.createElement('video');
                    media.src = e.target.result;
                    media.className = 'img-fluid';
                    media.style.maxHeight = '200px';
                    if (media.tagName === 'VIDEO') media.controls = true;
                    preview.appendChild(media);
                };
                reader.readAsDataURL(file);
            }
        });

        // Like toggle
        function toggleLike(postId, button) {
            const heartIcon = button.querySelector('i.fa-heart');
            const countSpan = button.querySelector('.like-count');
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=toggle_like&post_id=${postId}&csrf_token=<?php echo $csrf_token; ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    heartIcon.classList.toggle('far', !data.liked);
                    heartIcon.classList.toggle('fas', data.liked);
                    heartIcon.classList.toggle('text-danger', data.liked);
                    button.classList.toggle('liked', data.liked);
                    countSpan.textContent = data.like_count;
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Network error', 'error'));
        }

        // Post comment
        function postComment(postId, form, event) {
            event.preventDefault();
            const content = form.querySelector('textarea[name="comment_content"]').value.trim();
            if (!content) {
                Swal.fire('Error', 'Comment cannot be empty', 'error');
                return;
            }

            const formData = new FormData(form);
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let commentList = form.previousElementSibling;
                    if (commentList.classList.contains('text-muted')) {
                        commentList.remove();
                        commentList = document.createElement('div');
                        form.parentNode.insertBefore(commentList, form);
                    }
                    const comment = data.comment;
                    const div = document.createElement('div');
                    div.className = 'd-flex mb-2';
                    div.innerHTML = `
                        <a href="profile.php?user_id=${comment.user_id}">
                            <img src="${comment.avatar_path}" class="profile-icon rounded-circle me-2" alt="Commenter">
                        </a>
                        <div>
                            <strong>${comment.name}</strong>
                            <p class="mb-0">${comment.content}</p>
                            <small class="text-muted">${new Date(comment.created_at).toLocaleString()}</small>
                        </div>
                    `;
                    commentList.appendChild(div);

                    const countSpan = document.querySelector(`.post-item[data-post-id="${postId}"] .post-actions span:nth-child(2)`);
                    countSpan.textContent = ` ${parseInt(countSpan.textContent.split(' ')[1]) + 1}`;

                    form.reset();
                    Swal.fire('Success', 'Comment posted!', 'success');
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(error => Swal.fire('Error', 'Network error: ' + error.message, 'error'));
        }

        // Stats animation
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-target'));
                    let current = 0;
                    const increment = Math.ceil(target / 50);
                    const update = () => {
                        current += increment;
                        if (current >= target) {
                            el.textContent = target;
                        } else {
                            el.textContent = current;
                            requestAnimationFrame(update);
                        }
                    };
                    update();
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.stat h5').forEach(h5 => observer.observe(h5));

        // Form submissions
        document.getElementById('editProfileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('profileNameDisplay').textContent = document.getElementById('name').value || document.getElementById('profileNameDisplay').textContent;
                    document.getElementById('profileDetailsDisplay').textContent = [
                        document.getElementById('location').value ? `📍 ${document.getElementById('location').value}` : '',
                        document.getElementById('profession').value ? `🎨 ${document.getElementById('profession').value}` : '',
                        document.getElementById('interests').value ? `🌍 ${document.getElementById('interests').value}` : ''
                    ].filter(Boolean).join(' | ');
                    document.getElementById('profileBioDisplay').textContent = document.getElementById('bio').value || 'No bio yet';
                    bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
                    Swal.fire('Success', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(error => Swal.fire('Error', 'Failed to update profile: ' + error.message, 'error'));
        });

        document.getElementById('postForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('postModal')).hide();
                    document.getElementById('postContent').value = '';
                    document.getElementById('postMedia').value = '';
                    document.getElementById('mediaPreview').innerHTML = '';
                    Swal.fire('Success', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(error => Swal.fire('Error', 'Failed to create post: ' + error.message, 'error'));
        });
    </script>
</body>
</html>