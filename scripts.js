//############## add post Start


// Handle post form submission and display in main content
document.getElementById('postForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Prevent default form submission

    const postText = document.getElementById('postText').value;
    const postMedia = document.getElementById('postMedia').files[0];
    const postContainer = document.getElementById('postContainer');
    const currentDate = new Date().toLocaleString(); // Get current date and time

    if (postText.trim() || postMedia) {
        // Create post element
        const postDiv = document.createElement('div');
        postDiv.classList.add('post', 'card', 'mb-3', 'p-3');

        // Post header (profile picture, name, and date)
        const postHeader = document.createElement('div');
        postHeader.classList.add('d-flex', 'align-items-center', 'justify-content-between', 'mb-2');
        const profileDiv = document.createElement('div');
        profileDiv.classList.add('d-flex', 'align-items-center');
        const profileImg = document.createElement('img');
        profileImg.src = './images/Pankaj_image.jpg'; // Assuming Pankaj is the current user
        profileImg.classList.add('rounded-circle', 'me-2');
        profileImg.style.width = '40px';
        profileImg.style.height = '40px';
        profileImg.alt = 'Pankaj';
        const profileName = document.createElement('h6');
        profileName.classList.add('mb-0');
        profileName.textContent = 'Pankaj';
        profileDiv.appendChild(profileImg);
        profileDiv.appendChild(profileName);
        const postDate = document.createElement('small');
        postDate.classList.add('text-muted');
        postDate.textContent = currentDate;
        postHeader.appendChild(profileDiv);
        postHeader.appendChild(postDate);

        // Post content (text)
        const postContent = document.createElement('p');
        postContent.textContent = postText || '';

        // Post media (if uploaded)
        let mediaElement = null;
        if (postMedia) {
            const reader = new FileReader();
            reader.onload = function (event) {
                if (postMedia.type.startsWith('image/')) {
                    mediaElement = document.createElement('img');
                    mediaElement.src = event.target.result;
                    mediaElement.classList.add('img-fluid', 'rounded', 'mt-2');
                    mediaElement.style.maxHeight = '300px';
                } else if (postMedia.type.startsWith('video/')) {
                    mediaElement = document.createElement('video');
                    mediaElement.src = event.target.result;
                    mediaElement.classList.add('img-fluid', 'rounded', 'mt-2');
                    mediaElement.controls = true;
                    mediaElement.style.maxHeight = '300px';
                }
                postDiv.insertBefore(mediaElement, postActions); // Insert before actions
            };
            reader.readAsDataURL(postMedia);
        }

        // Post actions (Like, Comment, Share)
        const postActions = document.createElement('div');
        postActions.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'mt-2', 'post-actions');

        const likeButton = document.createElement('button');
        likeButton.classList.add('btn', 'btn-link', 'text-decoration-none');
        likeButton.innerHTML = '<i class="far fa-heart"></i> 0'; // Start with 0 likes
        likeButton.onclick = function () { handleLike(this); };

        const commentSpan = document.createElement('span');
        commentSpan.innerHTML = '<i class="far fa-comment"></i> 0'; // Start with 0 comments

        const shareSpan = document.createElement('span');
        shareSpan.innerHTML = '<i class="fas fa-share"></i> 0'; // Start with 0 shares

        postActions.appendChild(likeButton);
        postActions.appendChild(commentSpan);
        postActions.appendChild(shareSpan);

        // Append elements to post
        postDiv.appendChild(postHeader);
        postDiv.appendChild(postContent);
        postDiv.appendChild(postActions);

        // Prepend post to container (newest post appears at the top)
        postContainer.insertBefore(postDiv, postContainer.firstChild);

        // Clear form
        document.getElementById('postText').value = '';
        document.getElementById('postMedia').value = '';

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('postModal'));
        modal.hide();
    } else {
        alert('Please add some text or media to post.');
    }
});

// Handle like button click
function handleLike(button) {
    const icon = button.querySelector('i');
    const countText = button.textContent.trim().split(' ')[1];
    let likeCount = parseInt(countText) || 0;

    if (icon.classList.contains('far')) {
        // Like
        icon.classList.remove('far');
        icon.classList.add('fas', 'text-danger'); // Filled heart, red color
        likeCount++;
    } else {
        // Unlike
        icon.classList.remove('fas', 'text-danger');
        icon.classList.add('far');
        likeCount--;
    }

    button.innerHTML = `<i class="${icon.className}"></i> ${likeCount}`;
}

// Existing message filtering functions (unchanged)
function filterMessages() {
    const searchTerm = document.getElementById('message-search').value.toLowerCase().trim();
    const messages = document.querySelectorAll('.message');
    let visibleMessages = 0;

    messages.forEach(message => {
        const name = message.querySelector('h5').textContent.toLowerCase();
        const text = message.querySelector('p').textContent.toLowerCase();
        const matchesSearch = name.includes(searchTerm) || text.includes(searchTerm);

        if (searchTerm === '') {
            const category = document.querySelector('.category-tab.active').getAttribute('data-category');
            if (category === 'all' || message.getAttribute('data-category') === category) {
                message.style.display = 'flex';
                visibleMessages++;
            } else {
                message.style.display = 'none';
            }
        } else {
            if (matchesSearch) {
                message.style.display = 'flex';
                visibleMessages++;
            } else {
                message.style.display = 'none';
            }
        }
    });

    const messageList = document.querySelector('.message-list');
    if (visibleMessages === 0 && searchTerm !== '') {
        messageList.innerHTML = '<p class="text-muted p-2 small">No results found</p>';
    } else if (visibleMessages > 0 && messageList.querySelector('p.text-muted')) {
        messageList.innerHTML = '';
        messages.forEach(msg => messageList.appendChild(msg));
    }
}

document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelector('.category-tab.active').classList.remove('active', 'text-primary');
        tab.classList.add('active', 'text-primary');
        const category = tab.getAttribute('data-category');
        const messages = document.querySelectorAll('.message');
        const searchTerm = document.getElementById('message-search').value.toLowerCase().trim();

        messages.forEach(message => {
            const isCorrectCategory = category === 'all' || message.getAttribute('data-category') === category;
            const name = message.querySelector('h5').textContent.toLowerCase();
            const text = message.querySelector('p').textContent.toLowerCase();
            const matchesSearch = searchTerm === '' || name.includes(searchTerm) || text.includes(searchTerm);

            if (isCorrectCategory && matchesSearch) {
                message.style.display = 'flex';
            } else {
                message.style.display = 'none';
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const primaryMessages = document.querySelectorAll('.message[data-category="primary"]');
    const otherMessages = document.querySelectorAll('.message:not([data-category="primary"])');
    otherMessages.forEach(msg => msg.style.display = 'none');
    document.getElementById('message-search').value = '';
});


    //############## add post end


    // Select elements
    const notificationBadge = document.querySelector('.notification-badge');
    const notificationDropdown = document.querySelector('.notification-dropdown');
    const notificationItems = notificationDropdown.querySelectorAll('.notification-item');

    // Function to update notification count
    function updateNotificationCount() {
        const count = notificationDropdown.querySelectorAll('.notification-item').length;
        notificationBadge.textContent = count;
        notificationBadge.style.display = count > 0 ? 'inline-block' : 'none'; // Show/hide badge
    }

    // Initial count update
    updateNotificationCount();

    // Simulate adding a new notification after 5 seconds (example)
    setTimeout(() => {
        const newNotification = document.createElement('li');
        newNotification.classList.add('notification-item');
        newNotification.innerHTML = '<a class="dropdown-item" href="#">Pankaj shared a new story</a>';
        notificationDropdown.insertBefore(newNotification, notificationDropdown.querySelector('hr').nextSibling); // Add after divider
        updateNotificationCount(); // Update count after adding
    }, 5000); // 5 seconds delay

    // Optional: Clear notifications on click and update count
    document.querySelector('.notification-section').addEventListener('click', function() {
        // Uncomment to enable clearing (example: remove last notification)
        
        const lastNotification = notificationDropdown.querySelector('.notification-item:last-child');
        if (lastNotification) {
            lastNotification.remove();
            updateNotificationCount();
        }
        
    });
//search for message start


//search for message end
// Posts Data (Combining Facebook, Instagram, and YouTube-like posts)
const posts = [
    {
        image: "https://picsum.photos/400/300?random=1",
        text: "Beautiful sunset view 🌇",
        likes: 245,
        comments: 36,
        shares: 12,
        video: false // No video for this post
    },
    {
        image: "https://via.placeholder.com/400x225",
        text: "Weekend hiking adventure 🏔️ Check out my latest vlog!",
        likes: 189,
        comments: 28,
        shares: 8,
        video: true // YouTube-like video post
    },
    {
        image: "https://picsum.photos/400/300?random=3",
        text: "New art project completed! 🎨",
        likes: 356,
        comments: 45,
        shares: 19,
        video: false // No video for this post
    }
];

function renderPosts() {
    const container = document.getElementById('postContainer');
    container.innerHTML = posts.map(post => `
<div class="card">
    <img src="${post.image}" class="card-img-top" alt="Post image">
    ${post.video ? `<div class="ratio ratio-16x9"><iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="YouTube video" allowfullscreen></iframe></div>` : ''}
    <div class="card-body">
        <p class="card-text">${post.text}</p>
        <div class="d-flex justify-content-between align-items-center">
            <button class="btn btn-link text-decoration-none" onclick="handleLike(this)">
                <i class="far fa-heart"></i> ${post.likes}
            </button>
            <span><i class="far fa-comment"></i> ${post.comments}</span>
            <span><i class="fas fa-share"></i> ${post.shares}</span>
        </div>
    </div>
</div>
`).join('');
}

function handleLike(button) {
    const currentLikes = parseInt(button.innerHTML.match(/\d+/)[0]);
    const icon = button.querySelector('i');

    if (icon.classList.contains('far')) {
        button.innerHTML = `<i class="fas fa-heart text-danger"></i> ${currentLikes + 1}`;
    } else {
        button.innerHTML = `<i class="far fa-heart"></i> ${currentLikes - 1}`;
    }
}

// Initialize posts on page load
document.addEventListener('DOMContentLoaded', renderPosts);
// ============== MESSAGES ============== 


