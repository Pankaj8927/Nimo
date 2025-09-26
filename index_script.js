
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
    btn.addEventListener('click', function () {
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
    btn.addEventListener('click', function () {
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
function showReel(id, videoPath, name, avatarPath) {
    console.log(`Playing reel ${id}: ${videoPath}`);
    console.log(`User: ${name}, Avatar: ${avatarPath}`);
    // Add logic to play video (e.g., open modal with video player)
    alert(`Playing reel by ${name}`);
}

// Fetch reels (replace with real API call if available)
function fetchReels() {
    // Simulate API call with mock data
    setTimeout(() => {
        populateReels(mockReels);
    }, 500);
}

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
        const response = await fetch(window.location.href, {
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
                    messageDiv.classList.add(msg.sender_id == window.currentUserId ? 'sent' : 'received');
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
    if (msg.sender_id == window.currentUserId) {
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
                .catch (error => Swal.fire('Error', 'Network error occurred', 'error'));
        }

// Ensure Bootstrap dropdown works with the attachment button
document.addEventListener('DOMContentLoaded', function () {
    const attachmentBtn = document.getElementById('attachmentBtn');
    const attachmentMenu = document.getElementById('attachmentMenu');

    if (attachmentBtn && attachmentMenu) {
        // Bootstrap dropdown initialization
        new bootstrap.Dropdown(attachmentBtn);

        // Close dropdown when an item is clicked
        attachmentMenu.querySelectorAll('.btn-whatsapp-dropdown').forEach(item => {
            item.addEventListener('click', function () {
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
window.addEventListener('resize', function () {
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
const storiesByUser = JSON.parse('<?= json_encode($storiesByUserJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>');
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

searchInput.addEventListener('input', function () {
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

document.addEventListener('click', function (event) {
    if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
        searchResults.style.display = 'none';
    }
});

searchInput.addEventListener('focus', function () {
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
        reader.onload = function (e) {
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
document.getElementById('postForm').addEventListener('submit', async function (event) {
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
        const response = await fetch(window.location.href, {
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
    button.addEventListener('click', function () {
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
        const response = await fetch(window.location.href, {
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
document.getElementById('storyMedia').addEventListener('change', function (event) {
    const file = event.target.files[0];
    const previewDiv = document.getElementById('storyPreview');
    previewDiv.innerHTML = '';
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
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

// Post Media Preview Handler
document.getElementById('postMedia').addEventListener('change', function (event) {
    const file = event.target.files[0];
    const previewDiv = document.getElementById('mediaPreview');
    previewDiv.innerHTML = '';
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
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
document.getElementById('storyViewerModal').addEventListener('hidden.bs.modal', function () {
    clearInterval(storyTimer);
    currentUserId = null;
    currentStoryIndex = 0;
    currentStoryId = null;
});

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}