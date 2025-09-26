<?php
session_start();
require_once "db_connect.php";

$db = Database::getInstance();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch profile data
$stmt = $db->prepare("SELECT * FROM user_profiles WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$current_user = $stmt->fetch() ?: [
    'name' => 'Default User',
    'avatar_path' => 'uploads/avatars/default.jpg'
];

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_SANITIZE_NUMBER_INT);
    $content = filter_input(INPUT_POST, 'message_text', FILTER_SANITIZE_STRING);
    $media_path = '';
    $media_type = '';

    if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == 0) {
        $upload_dir = "uploads/messages/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $media_path = $upload_dir . time() . "_" . basename($_FILES['media_file']['name']);
        move_uploaded_file($_FILES['media_file']['tmp_name'], $media_path);
        $media_mime = mime_content_type($media_path);
        $media_type = strpos($media_mime, 'image') === 0 ? 'image' : (strpos($media_mime, 'video') === 0 ? 'video' : '');
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO messages (sender_id, receiver_id, content, media_path, media_type, created_at)
            VALUES (:sender_id, :receiver_id, :content, :media_path, :media_type, NOW())
        ");
        $stmt->execute([
            'sender_id' => $user_id,
            'receiver_id' => $receiver_id,
            'content' => $content,
            'media_path' => $media_path,
            'media_type' => $media_type
        ]);
        // Redirect to the same conversation
        header("Location: messenger.php?chat=" . $receiver_id);
        exit;
    } catch(PDOException $e) {
        $error_message = "Error sending message: " . $e->getMessage();
    }
}

// Fetch conversations (users who have messaged or been messaged by current user)
$stmt = $db->prepare("
    SELECT DISTINCT u.id, up.name, up.avatar_path
    FROM users u
    JOIN user_profiles up ON u.id = up.user_id
    WHERE u.id IN (
        SELECT sender_id FROM messages WHERE receiver_id = :user_id
        UNION
        SELECT receiver_id FROM messages WHERE sender_id = :user_id
    ) AND u.id != :user_id
");
$stmt->execute(['user_id' => $user_id]);
$conversations = $stmt->fetchAll();

// Fetch messages for selected conversation
$selected_user_id = isset($_GET['chat']) ? filter_input(INPUT_GET, 'chat', FILTER_SANITIZE_NUMBER_INT) : 0;
$messages = [];
$selected_user = null;
if ($selected_user_id) {
    $stmt = $db->prepare("
        SELECT m.*, up.name, up.avatar_path
        FROM messages m
        JOIN user_profiles up ON m.sender_id = up.user_id
        WHERE (m.sender_id = :user_id AND m.receiver_id = :selected_user_id)
        OR (m.sender_id = :selected_user_id AND m.receiver_id = :user_id)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute(['user_id' => $user_id, 'selected_user_id' => $selected_user_id]);
    $messages = $stmt->fetchAll();

    // Get selected user's info
    $stmt = $db->prepare("SELECT name, avatar_path FROM user_profiles WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $selected_user_id]);
    $selected_user = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messenger - SocialConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root {
            --primary-color: #1e90ff;
            --secondary-color: #f1f3f5;
            --background-color: #e9ecef;
        }
        body {
            background: var(--background-color);
            font-family: 'Poppins', sans-serif;
        }
        .messenger-container {
            display: flex;
            height: 80vh;
            margin-top: 20px;
        }
        .conversation-list {
            width: 300px;
            background: white;
            border-radius: 10px;
            overflow-y: auto;
            margin-right: 20px;
        }
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.2s;
        }
        .conversation-item:hover {
            background: var(--secondary-color);
        }
        .conversation-item.active {
            background: var(--primary-color);
            color: white;
        }
        .chat-area {
            flex: 1;
            background: white;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        .message {
            margin-bottom: 15px;
            max-width: 70%;
        }
        .message.sent {
            margin-left: auto;
            text-align: right;
        }
        .message.received {
            margin-right: auto;
        }
        .message-content {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 15px;
            background: #f0f0f0;
        }
        .message.sent .message-content {
            background: var(--primary-color);
            color: white;
        }
        .message-media {
            max-width: 100%;
            max-height: 300px;
            margin-top: 10px;
            border-radius: 10px;
        }
        .message-input {
            padding: 15px;
            border-top: 1px solid #eee;
        }
        .profile-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="messenger-container">
            <!-- Conversation List -->
            <div class="conversation-list">
                <?php foreach ($conversations as $conv): ?>
                    <div class="conversation-item <?php echo $selected_user_id == $conv['id'] ? 'active' : ''; ?>" 
                         onclick="window.location.href='messenger.php?chat=<?php echo $conv['id']; ?>'">
                        <img src="<?php echo $conv['avatar_path']; ?>" class="profile-icon" alt="<?php echo htmlspecialchars($conv['name']); ?>">
                        <?php echo htmlspecialchars($conv['name']); ?>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($conversations)): ?>
                    <div class="p-3 text-center text-muted">No conversations yet</div>
                <?php endif; ?>
            </div>

            <!-- Chat Area -->
            <div class="chat-area">
                <?php if ($selected_user_id && $selected_user): ?>
                    <div class="chat-header d-flex align-items-center">
                        <img src="<?php echo $selected_user['avatar_path']; ?>" class="profile-icon" alt="<?php echo htmlspecialchars($selected_user['name']); ?>">
                        <h5 class="mb-0"><?php echo htmlspecialchars($selected_user['name']); ?></h5>
                    </div>
                    <div class="messages-container" id="messagesContainer">
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                <div class="message-content">
                                    <?php echo htmlspecialchars($msg['content']); ?>
                                </div>
                                <?php if ($msg['media_path']): ?>
                                    <?php if ($msg['media_type'] === 'image'): ?>
                                        <img src="<?php echo $msg['media_path']; ?>" class="message-media" alt="Message Media">
                                    <?php elseif ($msg['media_type'] === 'video'): ?>
                                        <video controls class="message-media">
                                            <source src="<?php echo $msg['media_path']; ?>" type="<?php echo mime_content_type($msg['media_path']); ?>">
                                        </video>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <small class="text-muted"><?php echo $msg['created_at']; ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="message-input">
                        <form id="messageForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="send_message" value="1">
                            <input type="hidden" name="receiver_id" value="<?php echo $selected_user_id; ?>">
                            <div class="input-group">
                                <input type="text" class="form-control" name="message_text" id="messageText" 
                                       placeholder="Type a message..." autocomplete="off">
                                <input type="file" name="media_file" id="mediaFile" accept="image/*,video/*" 
                                       style="display: none;">
                                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('mediaFile').click()">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <div id="mediaPreview" class="mt-2"></div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-center align-items-center h-100 text-muted">
                        Select a conversation to start messaging
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // Media preview
        document.getElementById('mediaFile')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('mediaPreview');
            preview.innerHTML = '';

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100px';
                        img.style.maxHeight = '100px';
                        img.style.borderRadius = '5px';
                        preview.appendChild(img);
                    } else if (file.type.startsWith('video/')) {
                        const video = document.createElement('video');
                        video.src = e.target.result;
                        video.controls = true;
                        video.style.maxWidth = '100px';
                        video.style.maxHeight = '100px';
                        video.style.borderRadius = '5px';
                        preview.appendChild(video);
                    }
                }
                reader.readAsDataURL(file);
            }
        });

        // Form submission
        document.getElementById('messageForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const messagesContainer = document.getElementById('messagesContainer');
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    // Add message to chat
                    const content = document.getElementById('messageText').value;
                    const mediaFile = document.getElementById('mediaFile').files[0];
                    let mediaHtml = '';

                    if (mediaFile) {
                        const fileType = mediaFile.type;
                        if (fileType.startsWith('image/')) {
                            mediaHtml = `<img src="${URL.createObjectURL(mediaFile)}" class="message-media" alt="Message Media">`;
                        } else if (fileType.startsWith('video/')) {
                            mediaHtml = `<video controls class="message-media"><source src="${URL.createObjectURL(mediaFile)}" type="${fileType}"></video>`;
                        }
                    }

                    const newMessage = document.createElement('div');
                    newMessage.className = 'message sent';
                    newMessage.innerHTML = `
                        <div class="message-content">${content}</div>
                        ${mediaHtml}
                        <small class="text-muted">${new Date().toLocaleString()}</small>
                    `;
                    messagesContainer.appendChild(newMessage);
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;

                    // Clear form
                    document.getElementById('messageText').value = '';
                    document.getElementById('mediaFile').value = '';
                    document.getElementById('mediaPreview').innerHTML = '';

                    Swal.fire({
                        icon: 'success',
                        title: 'Sent',
                        text: 'Message sent successfully!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error('Failed to send message');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to send message!'
                });
            });
        });
    </script>
</body>
</html>