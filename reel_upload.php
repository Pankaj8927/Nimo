<?php
session_start();
require_once "db_connect.php";
$db = Database::getInstance();
// $db = Database::getInstance()->getConnection();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';
$uploaded_reel = null;

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle POST request for creating a reel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_reel') {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error_message = 'Invalid CSRF token';
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $error_message]);
            exit;
        }

        $caption = isset($_POST['caption']) ? trim($_POST['caption']) : null;

        // Validate file upload
        if (!isset($_FILES['media']) || $_FILES['media']['error'] === UPLOAD_ERR_NO_FILE) {
            $error_message = 'No video file uploaded';
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $error_message]);
            exit;
        }

        $file = $_FILES['media'];
        $max_size = 100 * 1024 * 1024; // 100MB
        if ($file['size'] > $max_size) {
            $error_message = 'File size exceeds 100MB limit';
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $error_message]);
            exit;
        }

        // Validate file type (only mp4 for reels)
        $allowed_types = ['video/mp4'];
        $file_type = mime_content_type($file['tmp_name']);
        if (!in_array($file_type, $allowed_types)) {
            $error_message = 'Only MP4 videos are allowed';
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $error_message]);
            exit;
        }

        // Generate unique file name
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_ext;
        $upload_dir = 'Uploads/videos/';
        $file_path = $upload_dir . $file_name;

        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            $error_message = 'Failed to upload video';
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $error_message]);
            exit;
        }

        // Sanitize caption to prevent XSS
        $caption = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');

        // Insert reel into database
        $stmt = $db->prepare("INSERT INTO reels (user_id, video_path, caption, created_at, like_count) VALUES (:user_id, :video_path, :caption, NOW(), 0)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':video_path' => $file_path,
            ':caption' => $caption
        ]);

        $reel_id = $db->lastInsertId();
        $success_message = 'Reel uploaded successfully!';
        $uploaded_reel = [
            'id' => $reel_id,
            'user_id' => $user_id,
            'video_path' => $file_path,
            'caption' => $caption,
            'created_at' => date('Y-m-d H:i:s'),
            'like_count' => 0
        ];

        // Return JSON for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'reel' => $uploaded_reel]);
        exit;
    } catch (PDOException $e) {
        $error_message = 'Database error: ' . $e->getMessage();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $error_message]);
        exit;
    } catch (Exception $e) {
        $error_message = 'Server error: ' . $e->getMessage();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $error_message]);
        exit;
    }
}

// Fetch user profile for display
// $stmt = $db->prepare("SELECT username, name, avatar_path FROM users WHERE id = :user_id");
// $stmt->execute([':user_id' => $user_id]);
// $profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Reel - SocialFusion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="./profile.css">
    <style>
        .upload-animation {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            border-radius: 10px;
            display: none;
            z-index: 10000;
            text-align: center;
        }
        .upload-animation i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .preview-video, .uploaded-video {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
        }
        .upload-section, .uploaded-reel {
            max-width: 600px;
            margin: 2rem auto;
        }
        .uploaded-reel {
            display: none;
        }
        .uploaded-reel.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Upload Form -->
        <div class="upload-section">
            <h2 class="text-center mb-4">Upload a Reel</h2>
            <form id="reelForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_reel">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="mb-3">
                    <label for="reelMedia" class="form-label">Upload Video</label>
                    <input type="file" class="form-control" id="reelMedia" name="media" accept="video/mp4" required>
                    <small id="mediaHelp" class="form-text text-muted">Upload a short video (max 100MB).</small>
                </div>
                <div class="mb-3">
                    <label for="reelCaption" class="form-label">Caption</label>
                    <textarea class="form-control" id="reelCaption" name="caption" rows="3" placeholder="Add a caption..."></textarea>
                </div>
                <div id="reelPreview" class="mb-3"></div>
                <button type="submit" class="btn btn-primary" id="reelSubmitBtn">Post Reel</button>
            </form>
        </div>

        <!-- Display Uploaded Reel -->
        <div class="uploaded-reel <?php echo $uploaded_reel ? 'show' : ''; ?>" id="uploadedReel">
            <h3 class="text-center mb-3">Your Uploaded Reel</h3>
            <div class="card">
                <div class="card-body">
                    <video class="uploaded-video" controls>
                        <source id="uploadedVideoSource" src="<?php echo htmlspecialchars($uploaded_reel['video_path'] ?? ''); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <p class="card-text mt-2"><?php echo htmlspecialchars($uploaded_reel['caption'] ?? ''); ?></p>
                    <small class="text-muted">Posted by <?php echo htmlspecialchars($profile['name'] ?? $profile['username'] ?? 'Unknown'); ?> on <?php echo $uploaded_reel ? date('M d, Y H:i', strtotime($uploaded_reel['created_at'])) : ''; ?></small>
                </div>
            </div>
        </div>

        <!-- Upload Animation -->
        <div class="upload-animation" id="uploadAnimation">
            <div id="uploadSpinner"><i class="fas fa-spinner fa-spin"></i></div>
            <div id="successCheck" style="display: none;"><i class="fas fa-check-circle text-success"></i></div>
            <div id="uploadText">Uploading...</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview Video
        document.getElementById('reelMedia').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const previewDiv = document.getElementById('reelPreview');
            previewDiv.innerHTML = '';

            if (file) {
                const maxSize = 100 * 1024 * 1024; // 100MB
                if (file.size > maxSize) {
                    Swal.fire('Error', 'File size exceeds 100MB limit', 'error');
                    event.target.value = '';
                    return;
                }

                if (!file.type.startsWith('video/mp4')) {
                    Swal.fire('Error', 'Only MP4 videos are allowed', 'error');
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const video = document.createElement('video');
                    video.src = e.target.result;
                    video.controls = true;
                    video.className = 'preview-video';
                    previewDiv.appendChild(video);
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle Reel Form Submission
        document.getElementById('reelForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const form = event.target;
            const submitBtn = form.querySelector('#reelSubmitBtn');
            const uploadAnimation = document.getElementById('uploadAnimation');
            const uploadSpinner = document.getElementById('uploadSpinner');
            const successCheck = document.getElementById('successCheck');
            const uploadText = document.getElementById('uploadText');
            const uploadedReel = document.getElementById('uploadedReel');
            const uploadedVideoSource = document.getElementById('uploadedVideoSource');
            const uploadedCaption = uploadedReel.querySelector('.card-text');
            const uploadedMeta = uploadedReel.querySelector('.text-muted');

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
                        form.reset();
                        document.getElementById('reelPreview').innerHTML = '';
                        // Update uploaded reel section
                        uploadedVideoSource.src = result.reel.video_path;
                        uploadedCaption.textContent = result.reel.caption || '';
                        uploadedMeta.textContent = `Posted by <?php echo htmlspecialchars($profile['name'] ?? $profile['username'] ?? 'Unknown'); ?> on ${new Date(result.reel.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric' })}`;
                        uploadedReel.classList.add('show');
                        uploadedReel.scrollIntoView({ behavior: 'smooth' });
                    }, 1000);

                    Swal.fire('Success', 'Reel posted successfully!', 'success');
                } else {
                    uploadAnimation.style.display = 'none';
                    Swal.fire('Error', result.error || 'Failed to post reel', 'error');
                }
            } catch (error) {
                console.error('Error posting reel:', error);
                uploadAnimation.style.display = 'none';
                Swal.fire('Error', 'An error occurred while posting the reel: ' + error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Post Reel';
            }
        });
    </script>
</body>
</html>