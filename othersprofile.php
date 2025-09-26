<?php
// Secure session configuration
session_set_cookie_params([
    'lifetime' => 180 * 24 * 60 * 60,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
session_regenerate_id(true);

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$current_user_id = (int)$_SESSION['user_id'];

// Get target user ID from query parameter
$target_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($target_user_id <= 0) {
    header("Location: index.php");
    exit();
}

// Database connection
try {
    $db = new PDO("mysql:host=localhost;dbname=socialauth_db", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Sorry, we're having trouble connecting to the database.");
}

// Fetch target user's profile data
try {
    $stmt = $db->prepare("
        SELECT u.id, u.username, up.name, up.profession, up.interests, up.bio, up.location, up.avatar_path, up.cover_path
        FROM users u
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$target_user_id]);
    $profile = $stmt->fetch();
    if (!$profile) {
        header("Location: index.php");
        exit();
    }
    $profile = array_merge([
        'id' => $target_user_id,
        'username' => 'Unknown',
        'name' => 'Default User',
        'profession' => '',
        'interests' => '',
        'bio' => 'No bio yet',
        'location' => '',
        'avatar_path' => 'Uploads/avatars/default.jpg',
        'cover_path' => 'Uploads/covers/default.jpg'
    ], $profile);
} catch (PDOException $e) {
    error_log("Profile fetch error: " . $e->getMessage());
    $error_message = "Error loading profile.";
}

// Check friendship status
try {
    $stmt = $db->prepare("
        SELECT status
        FROM friend_requests
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ");
    $stmt->execute([$current_user_id, $target_user_id, $target_user_id, $current_user_id]);
    $friendship = $stmt->fetch();
    $friendship_status = $friendship ? $friendship['status'] : 'none';
} catch (PDOException $e) {
    error_log("Friendship status fetch error: " . $e->getMessage());
    $friendship_status = 'none';
}

// Fetch counts
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM friend_requests WHERE receiver_id = ? AND status = 'accepted'");
    $stmt->execute([$target_user_id]);
    $follower_count = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM friend_requests WHERE sender_id = ? AND status = 'accepted'");
    $stmt->execute([$target_user_id]);
    $following_count = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
    $stmt->execute([$target_user_id]);
    $post_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Counts fetch error: " . $e->getMessage());
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
    $stmt->execute([$target_user_id, $target_user_id, $target_user_id]);
    $friends = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Friends fetch error: " . $e->getMessage());
    $friends = [];
}

// Fetch posts with likes and comments
try {
    $stmt = $db->prepare("
        SELECT p.id, p.content, p.media_path, p.media_type, p.created_at,
               u.id AS user_id, u.username, up.name, up.avatar_path,
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
    $stmt->execute([$current_user_id, $target_user_id]);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Post fetch error: " . $e->getMessage());
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
            LEFT JOIN user_profiles up ON c.user_id = up.user_id
            WHERE c.post_id IN ($placeholders)
            ORDER BY c.created_at ASC
        ");
        $stmt->execute($post_ids);
        $all_comments = $stmt->fetchAll();
        foreach ($all_comments as $comment) {
            $comments[$comment['post_id']][] = $comment;
        }
    } catch (PDOException $e) {
        error_log("Comment fetch error: " . $e->getMessage());
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
    <link rel="stylesheet" href="profile.css">
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
        .friend-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .profile-stats {
            margin-top: 60px;
        }
        .profile-bio {
            text-align: center;
            margin: 20px 0;
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
        .comment-section {
            display: none;
        }
        .comment-item {
            align-items: flex-start;
        }
        .comment-content {
            flex-grow: 1;
        }
        .comment-input-field {
            border-radius: 20px;
        }
        .confirmation-icon {
            display: none;
            color: green;
        }
        .like-btn.liked .confirmation-icon {
            display: inline;
        }
        .post-menu-btn i {
            font-size: 1.2rem;
            color: #555;
        }
        .share-count, .like-count, .comment-count {
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <!-- Header -->
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h1><a href="index.php" class="text-decoration-none">SocialFusion</a></h1>
            <div>
                <a href="profile.php" class="btn btn-outline-primary me-2">My Profile</a>
                <a href="logout.php" class="btn btn-outline-primary">Logout</a>
            </div>
        </header>

        <!-- Profile Header -->
        <div class="profile-header" style="background-image: url('<?php echo htmlspecialchars($profile['cover_path']); ?>');">
            <?php if ($friendship_status === 'none'): ?>
                <a href="friend_request.php?action=send&user_id=<?php echo $target_user_id; ?>" class="btn btn-primary friend-btn">Add Friend</a>
            <?php elseif ($friendship_status === 'pending'): ?>
                <button class="btn btn-secondary friend-btn" disabled>Request Pending</button>
            <?php elseif ($friendship_status === 'accepted'): ?>
                <a href="friend_request.php?action=unfriend&user_id=<?php echo $target_user_id; ?>" class="btn btn-danger friend-btn">Unfriend</a>
            <?php endif; ?>
            <div class="profile-picture">
                <img src="<?php echo htmlspecialchars($profile['avatar_path']); ?>" alt="Profile Picture">
            </div>
        </div>

        <!-- Profile Stats -->
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

        <!-- Profile Bio -->
        <div class="profile-bio">
            <h4><?php echo htmlspecialchars($profile['name'] ?: $profile['username']); ?></h4>
            <p>
                <?php echo implode(' | ', array_filter([
                    $profile['location'] ? "📍 " . htmlspecialchars($profile['location']) : '',
                    $profile['profession'] ? "🎨 " . htmlspecialchars($profile['profession']) : '',
                    $profile['interests'] ? "🌍 " . htmlspecialchars($profile['interests']) : ''
                ])); ?>
            </p>
            <p><?php echo htmlspecialchars($profile['bio']); ?></p>
        </div>

        <!-- Friends Section -->
        <div class="friends-section mb-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Friends</h5>
                    <a href="#" class="text-decoration-none">See all</a>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3"><?php echo count($friends); ?> friends</p>
                    <?php if (empty($friends)): ?>
                        <p class="text-muted">No friends yet.</p>
                    <?php else: ?>
                        <div class="row row-cols-2 row-cols-md-4 g-3">
                            <?php foreach ($friends as $friend): ?>
                                <div class="col">
                                    <div class="friend-item text-center">
                                        <a href="othersprofile.php?user_id=<?php echo $friend['id']; ?>">
                                            <img src="<?php echo htmlspecialchars($friend['avatar_path'] ?: 'Uploads/avatars/default.jpg'); ?>" class="friend-image" alt="Friend">
                                            <p class="mb-0"><?php echo htmlspecialchars($friend['name'] ?: $friend['username']); ?></p>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Posts Display -->
        <div class="profile-activity">
            <h4>All Posts</h4>
            <div id="postsContainer">
                <?php if (empty($posts)): ?>
                    <p class="text-muted">No posts yet.</p>
                <?php else: ?>
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
                                    <i class="fas fa-share"></i> <span class="share-count">0</span>
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Time ago formatting
        function timeAgo(timestamp) {
            const now = new Date();
            const time = new Date(timestamp);
            const seconds = Math.floor((now - time) / 1000);
            if (seconds < 60) return `${seconds}s ago`;
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return `${minutes}m ago`;
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `${hours}h ago`;
            const days = Math.floor(hours / 24);
            return `${days}d ago`;
        }

        document.querySelectorAll('.time-ago').forEach(el => {
            el.textContent = timeAgo(el.getAttribute('data-timestamp'));
        });

        // Toggle comment section
        function toggleCommentSection(postId, button) {
            const commentSection = document.getElementById(`comment-section-${postId}`);
            const commentInput = commentSection.querySelector('.comment-input-field');
            if (commentSection.style.display === 'none' || commentSection.style.display === '') {
                commentSection.style.display = 'block';
                commentInput.focus();
            } else {
                commentSection.style.display = 'none';
            }
        }

        // Share post
        function sharePost(postId, mediaPath, mediaType) {
            const shareUrl = `${window.location.origin}/post.php?id=${postId}`;
            navigator.clipboard.writeText(shareUrl).then(() => {
                const shareCountElement = document.querySelector(`.post-item[data-post-id="${postId}"] .share-count`);
                if (shareCountElement) {
                    let currentCount = parseInt(shareCountElement.textContent) || 0;
                    shareCountElement.textContent = currentCount + 1;
                }
                alert('Link copied to clipboard! You can share it anywhere.');
            }).catch(err => {
                console.error('Failed to copy link:', err);
                alert('Failed to copy link. Please try again.');
            });
        }

        // Toggle like status
        function toggleStatusLike(postId, button) {
            const isLiked = button.classList.contains('liked');
            const likeCountElement = button.querySelector('.like-count');
            const heartIcon = button.querySelector('.fa-heart');
            const confirmationIcon = button.querySelector('.confirmation-icon');
            let likeCount = parseInt(likeCountElement.textContent) || 0;

            fetch('toggle_like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&action=${isLiked ? 'unlike' : 'like'}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.classList.toggle('liked');
                    heartIcon.classList.toggle('far');
                    heartIcon.classList.toggle('fas');
                    likeCountElement.textContent = data.like_count;
                    confirmationIcon.style.display = isLiked ? 'none' : 'inline';
                    setTimeout(() => { confirmationIcon.style.display = 'none'; }, 1000);
                } else {
                    alert('Failed to update like status.');
                }
            })
            .catch(err => {
                console.error('Like toggle error:', err);
                alert('An error occurred. Please try again.');
            });
        }

        // Post comment
        function postComment(postId, event) {
            event.preventDefault();
            const form = event.target;
            const input = form.querySelector('.comment-input-field');
            const content = input.value.trim();
            if (!content) return;

            fetch('post_comment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `post_id=${postId}&content=${encodeURIComponent(content)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const commentList = document.getElementById(`comment-list-${postId}`);
                    const commentItem = document.createElement('div');
                    commentItem.classList.add('comment-item', 'd-flex', 'mb-2');
                    commentItem.setAttribute('data-comment-id', data.comment_id);
                    commentItem.innerHTML = `
                        <a href="othersprofile.php?user_id=${data.user_id}">
                            <img src="${data.user_image || 'Uploads/avatars/default.jpg'}" class="rounded-circle me-2" style="width: 32px; height: 32px;" alt="Profile">
                        </a>
                        <div class="comment-content bg-light p-2 rounded">
                            <strong>${data.user_name || 'User'}</strong>
                            <p class="mb-0">${content}</p>
                            <small class="text-muted time-ago" data-timestamp="${new Date().toISOString()}">just now</small>
                        </div>
                    `;
                    commentList.appendChild(commentItem);
                    input.value = '';
                    const commentCountElement = document.querySelector(`.post-item[data-post-id="${postId}"] .comment-count`);
                    let currentCount = parseInt(commentCountElement.textContent) || 0;
                    commentCountElement.textContent = currentCount + 1;
                } else {
                    alert('Failed to post comment.');
                }
            })
            .catch(err => {
                console.error('Comment post error:', err);
                alert('An error occurred. Please try again.');
            });
        }

        // Save post (placeholder)
        function savePost(postId, mediaPath, mediaType) {
            alert('Save post functionality not implemented yet.');
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
    </script>
</body>
</html>