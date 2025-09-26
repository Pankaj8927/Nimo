
// Existing JavaScript functions (assumed from your codebase)
function toggleStatusLike(postId, button) {
    // Implement like toggle logic (e.g., AJAX to update like status)
    button.classList.toggle('liked');
    const icon = button.querySelector('i.fa-heart');
    icon.classList.toggle('far');
    icon.classList.toggle('fas');
    icon.classList.toggle('text-danger');
    // Update like count via AJAX
}

function toggleCommentSection(postId, button) {
    const section = document.getElementById(`comment-section-${postId}`);
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
}

function postComment(postId, event) {
    event.preventDefault();
    // Implement comment posting logic (e.g., AJAX)
    const input = event.target.querySelector('.comment-input-field');
    input.value = '';
}

function savePost(postId, mediaPath, mediaType) {
    // Implement save post logic
    console.log(`Saving post ${postId}`);
}

function sharePost(postId, mediaPath, mediaType) {
    // Implement share post logic
    console.log(`Sharing post ${postId}`);
}

// New JavaScript for Reels
function openReel(videoUrl) {
    try {
        const modal = document.getElementById('reelModal');
        const reelVideo = document.getElementById('reelVideo');
        const reelSource = document.getElementById('reelSource');

        reelSource.src = videoUrl;
        reelVideo.load();
        reelVideo.play().catch(error => {
            console.error('Video playback error:', error);
            alert('Error playing the reel. Please try again.');
        });
        modal.style.display = 'flex';
    } catch (error) {
        console.error('Error opening reel:', error);
        alert('Failed to open the reel. Please try again.');
    }
}

function closeReelModal() {
    const modal = document.getElementById('reelModal');
    const reelVideo = document.getElementById('reelVideo');
    modal.style.display = 'none';
    reelVideo.pause();
    reelVideo.currentTime = 0;
}

function toggleReelLike(reelId, button) {
    // Implement reel like toggle logic (similar to toggleStatusLike)
    button.classList.toggle('liked');
    const icon = button.querySelector('i.fa-heart');
    icon.classList.toggle('far');
    icon.classList.toggle('fas');
    icon.classList.toggle('text-danger');
    // Update like count via AJAX
}

function toggleReelCommentSection(reelId, button) {
    const section = document.getElementById(`reel-comment-section-${reelId}`);
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
}

function postReelComment(reelId, event) {
    event.preventDefault();
    // Implement reel comment posting logic
    const input = event.target.querySelector('.comment-input-field');
    input.value = '';
}

// Attach click events to reel thumbnails
document.querySelectorAll('.reel-item').forEach(item => {
    item.addEventListener('click', (e) => {
        // Prevent triggering if clicking on like/comment buttons
        if (e.target.closest('.reel-actions')) return;
        const videoUrl = item.getAttribute('data-video-url');
        openReel(videoUrl);
    });
});

// Close modal when clicking outside
document.getElementById('reelModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('reelModal')) {
        closeReelModal();
    }
});
